<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Category;
use App\Models\Purchase;
use App\Services\RecipeService;
use App\Services\CostCalculationService;
use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Enums\Unit;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;

class RecipeController extends Controller
{
    use AuthorizesRequests;

    protected $recipeService;
    protected $costService;

    public function __construct(RecipeService $recipeService, CostCalculationService $costService)
    {
        $this->recipeService = $recipeService;
        $this->costService = $costService;
    }

    public function index(Request $request, string $kitchen_slug)
    {
        $query = Recipe::with(['category', 'creator']);

        // RESTRICTION: Staff can only see their own recipes unless they have explicit module access
        if ($request->user()->isStaff() && !$request->user()->hasPermissionTo('module_recipes')) {
            $query->where('created_by', $request->user()->id);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->boolean('is_sub_recipe')) {
            $query->where('is_sub_recipe', true);
        }

        $recipes = $query->latest()->paginate(10);
        $categories = Category::forRecipes()->where('status', 'active')->orderBy('name')->get();

        return view('recipes.index', compact('recipes', 'categories'));
    }

    public function create(string $kitchen_slug)
    {
        try {
            $categories = Category::forRecipes()->where('status', 'active')->orderBy('name')->get();
            $ingredientCategories = Category::forIngredients()->where('status', 'active')->orderBy('name')->get();




            $units = Unit::cases();


            // Fetch Sub-Recipes: Show all recipes that produce an ingredient
            $subRecipes = Recipe::where(function ($query) {
                $query->where('is_sub_recipe', true)
                    ->orWhereNotNull('produces_ingredient_id');
            })
                ->whereNotNull('produces_ingredient_id')
                ->with('producesIngredient')
                ->orderBy('name')
                ->get();
            $producedIngredientIds = $subRecipes->pluck('produces_ingredient_id')->filter()->toArray();

            $ingredients = \App\Models\Ingredient::approved()
                ->orderBy('name')
                ->get();

            return view('recipes.create', compact('categories', 'ingredientCategories', 'units', 'ingredients', 'subRecipes'));

        } catch (\Throwable $e) {
            Log::error('Recipe create page error: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function store(StoreRecipeRequest $request, string $kitchen_slug)
    {
        try {
            $recipe = $this->recipeService->createRecipe($request->validated(), $request->user());

            return redirect()->route('recipes.show', $recipe)
                ->with('success', 'Recipe draft created successfully and saved to Google Drive.');
        } catch (\Exception $e) {
            Log::error('Recipe creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->except(['_token']),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create recipe: ' . $e->getMessage()]);
        }
    }

    public function show(string $kitchen_slug, Recipe $recipe)
    {
        $recipe->load([
            'category',
            'ingredients.producedByRecipes',
            'versions',
            'driveFiles',
            'producesIngredient',
            'stages' => function ($query) {
                $query->orderBy('sort_order');
            },
            'stages.ingredients.ingredient'
        ]);
        $costPerPortion = $this->costService->calculateCostPerPortion($recipe);

        return view('recipes.show', compact('recipe', 'costPerPortion'));
    }

    public function edit(string $kitchen_slug, Recipe $recipe)
    {
        $this->authorize('update', $recipe);

        try {
            $categories = Category::forRecipes()->where('status', 'active')->orderBy('name')->get();
            $ingredientCategories = Category::forIngredients()->where('status', 'active')->orderBy('name')->get();


            $units = Unit::cases();


            // Fetch Sub-Recipes: Show all recipes that produce an ingredient
            // This includes recipes marked as sub-recipes OR recipes that have produces_ingredient_id
            $subRecipes = Recipe::where(function ($query) {
                $query->where('is_sub_recipe', true)
                    ->orWhereNotNull('produces_ingredient_id');
            })
                ->whereNotNull('produces_ingredient_id')
                ->with('producesIngredient')
                ->orderBy('name')
                ->get();
            $producedIngredientIds = $subRecipes->pluck('produces_ingredient_id')->toArray();

            // Approved Ingredients: Only ingredients approved by admin and NOT produced by any sub-recipe
            $ingredients = \App\Models\Ingredient::approved()
                ->orderBy('name')
                ->get();

            // Load relationships safely
            $recipe->load([
                'category',
                'stages' => function ($query) {
                    $query->orderBy('sort_order');
                },
                'stages.ingredients' => function ($query) {
                    $query->orderBy('id');
                },
                'stages.ingredients.ingredient',
                'recipeIngredients.ingredient'
            ]);

            return view('recipes.edit', compact('recipe', 'categories', 'ingredientCategories', 'units', 'ingredients', 'subRecipes'));

        } catch (\Exception $e) {
            \Log::error('Recipe edit failed: ' . $e->getMessage(), [
                'recipe_id' => $recipe->id,
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('recipes.index')
                ->with('error', 'Failed to load recipe for editing: ' . $e->getMessage());
        }
    }

    public function update(UpdateRecipeRequest $request, string $kitchen_slug, Recipe $recipe)
    {
        $this->authorize('update', $recipe);

        $this->recipeService->updateRecipe($recipe, $request->validated(), $request->user());

        return redirect()->route('recipes.show', $recipe)
            ->with('success', 'Recipe updated successfully and saved to Google Drive.');
    }

    public function destroy(string $kitchen_slug, Recipe $recipe)
    {
        $this->authorize('delete', $recipe);

        $recipe->delete();

        return redirect()->route('recipes.index')
            ->with('success', 'Recipe deleted successfully.');
    }

    public function approve(string $kitchen_slug, Recipe $recipe)
    {
        $this->authorize('approve', $recipe);

        $this->recipeService->approve($recipe, auth()->user());

        return back()->with('success', 'Recipe approved as permanent version.');
    }

    public function reject(string $kitchen_slug, Recipe $recipe)
    {
        $this->authorize('approve', $recipe); // Admins only

        $recipe->update(['status' => \App\Enums\RecipeStatus::Rejected]);

        return back()->with('success', 'Recipe rejected.');
    }

    public function print(string $kitchen_slug, Recipe $recipe, Request $request)
    {
        // Load necessary relationships
        $recipe->load(['category', 'ingredients', 'stages.ingredients.ingredient', 'recipeIngredients.ingredient']);

        if ($request->has('download') && $request->download == 'pdf') {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('recipes.print', compact('recipe'));
            return $pdf->download('recipe-' . $recipe->id . '.pdf');
        }

        return view('recipes.print', compact('recipe'));
    }

    public function export(Request $request, string $kitchen_slug, $type)
    {
        // Get filtered recipes based on current filters
        $query = Recipe::with(['category', 'creator', 'stages.ingredients.ingredient', 'recipeIngredients.ingredient']);

        // RESTRICTION: Staff can only see their own recipes unless they have explicit module access
        if ($request->user()->isStaff() && !$request->user()->hasPermissionTo('module_recipes')) {
            $query->where('created_by', $request->user()->id);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $recipes = $query->latest()->get();

        switch ($type) {
            case 'full-cards':
                return \App\Exports\FullRecipeCardsExport::export($recipes);
            case 'procurement':
                return \App\Exports\ProcurementListExport::export($recipes);
            case 'cost-breakdown':
                return \App\Exports\CostBreakdownExport::export($recipes);
            default:
                return redirect()->route('recipes.index')->with('error', 'Invalid export type.');
        }
    }
}
