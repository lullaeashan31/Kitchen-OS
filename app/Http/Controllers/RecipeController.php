<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Category;
use App\Services\RecipeService;
use App\Services\CostCalculationService;
use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Enums\Unit;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

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

    public function index(Request $request)
    {
        $query = Recipe::with(['category', 'creator']);

        // RESTRICTION: Staff can only see their own recipes
        if ($request->user()->isStaff()) {
            $query->where('created_by', $request->user()->id);
        }

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $recipes = $query->latest()->paginate(10);
        $categories = Category::all();

        return view('recipes.index', compact('recipes', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        $units = Unit::cases();
        $ingredients = \App\Models\Ingredient::where('status', 'approved')->orderBy('name')->get();
        return view('recipes.create', compact('categories', 'units', 'ingredients'));
    }

    public function store(StoreRecipeRequest $request)
    {
        $recipe = $this->recipeService->createRecipe($request->validated(), $request->user());

        return redirect()->route('recipes.show', $recipe)
            ->with('success', 'Recipe draft created successfully.');
    }

    public function show(Recipe $recipe)
    {
        $recipe->load(['category', 'ingredients', 'versions', 'driveFiles']);
        $costPerPortion = $this->costService->calculateCostPerPortion($recipe);

        return view('recipes.show', compact('recipe', 'costPerPortion'));
    }

    public function edit(Recipe $recipe)
    {
        $this->authorize('update', $recipe);

        $categories = Category::all();
        $units = Unit::cases();
        $recipe->load('ingredients');

        return view('recipes.edit', compact('recipe', 'categories', 'units'));
    }

    public function update(UpdateRecipeRequest $request, Recipe $recipe)
    {
        $this->authorize('update', $recipe);

        $this->recipeService->updateRecipe($recipe, $request->validated(), $request->user());

        return redirect()->route('recipes.show', $recipe)
            ->with('success', 'Recipe updated successfully.');
    }

    public function destroy(Recipe $recipe)
    {
        $this->authorize('delete', $recipe);

        $recipe->delete();

        return redirect()->route('recipes.index')
            ->with('success', 'Recipe deleted successfully.');
    }

    public function approve(Recipe $recipe)
    {
        $this->authorize('approve', $recipe);

        $this->recipeService->approve($recipe, auth()->user());

        return back()->with('success', 'Recipe approved as permanent version.');
    }

    public function reject(Recipe $recipe)
    {
        $this->authorize('approve', $recipe); // Admins only

        $recipe->update(['status' => \App\Enums\RecipeStatus::Rejected]);

        return back()->with('success', 'Recipe rejected.');
    }
}
