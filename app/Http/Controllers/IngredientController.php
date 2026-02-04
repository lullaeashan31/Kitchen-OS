<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Category;
use App\Services\IngredientService;
use App\Http\Requests\StoreIngredientRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class IngredientController extends Controller
{
    use AuthorizesRequests;

    protected $ingredientService;

    public function __construct(IngredientService $ingredientService)
    {
        $this->ingredientService = $ingredientService;
    }

    public function index(Request $request)
    {
        $query = Ingredient::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $ingredients = $query->orderBy('name')->paginate(15);

        return view('ingredients.index', compact('ingredients'));
    }

    public function create()
    {
        $this->authorize('create', Ingredient::class);
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $units = \App\Enums\Unit::cases();
        return view('ingredients.create', compact('categories', 'units'));
    }

    public function store(StoreIngredientRequest $request)
    {
        $this->authorize('create', Ingredient::class);

        Ingredient::create($request->validated());

        return redirect()->route('ingredients.index')
            ->with('success', 'Ingredient created successfully.');
    }

    public function edit(Ingredient $ingredient)
    {
        $this->authorize('update', $ingredient);
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $units = \App\Enums\Unit::cases();
        return view('ingredients.edit', compact('ingredient', 'categories', 'units'));
    }

    public function update(StoreIngredientRequest $request, Ingredient $ingredient)
    {
        $this->authorize('update', $ingredient);

        $this->ingredientService->update($ingredient, $request->validated());

        return redirect()->route('ingredients.index')
            ->with('success', 'Ingredient updated successfully.');
    }

    public function destroy(Ingredient $ingredient)
    {
        $this->authorize('delete', $ingredient);
        $ingredient->delete();
        return redirect()->route('ingredients.index')
            ->with('success', 'Ingredient deleted successfully.');
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        return response()->json(
            $this->ingredientService->search($query)
        );
    }
}
