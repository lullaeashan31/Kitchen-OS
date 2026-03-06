<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\Category;
use App\Services\IngredientService;
use App\Http\Requests\StoreIngredientRequest;
use App\Http\Requests\QuickCreateIngredientRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;

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
        $query = Ingredient::query()->withCount('recipes');

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

        try {
            $data = $request->validated();

            // Set default values for optional fields that might be empty
            $data['current_stock'] = isset($data['current_stock']) ? $data['current_stock'] : 0;
            $data['alert_threshold'] = isset($data['alert_threshold']) && $data['alert_threshold'] !== '' ? $data['alert_threshold'] : 0;
            $data['status'] = $data['status'] ?? 'pending';
            $data['price'] = $data['price'] ?? 0;
            $data['purchase_unit'] = $data['purchase_unit'] ?? null;
            $data['vendor'] = $data['vendor'] ?? null;
            $data['storage_location'] = $data['storage_location'] ?? null;

            // Handle allergen_tags - ensure it's properly formatted as array or null
            if (isset($data['allergen_tags']) && is_array($data['allergen_tags']) && !empty($data['allergen_tags'])) {
                $data['allergen_tags'] = array_values(array_filter($data['allergen_tags']));
            } else {
                $data['allergen_tags'] = null;
            }

            Ingredient::create($data);

            return redirect()->route('ingredients.index')
                ->with('success', 'Ingredient created successfully.');
        } catch (\Exception $e) {
            \Log::error('Ingredient creation failed: ' . $e->getMessage(), [
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create ingredient. Please check all required fields are filled.']);
        }
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
    public function storeQuick(QuickCreateIngredientRequest $request)
    {
        // $this->authorize('create', Ingredient::class); // Optional based on specific permission needs

        $ingredient = Ingredient::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'storage_location' => $request->storage_location,
            'measurement_unit' => $request->measurement_unit,
            'status' => 'active', // Default status
            'price' => 0, // Default price
        ]);

        // Notify Admin
        \App\Models\User::all()->filter(function ($user) {
            return $user->isAdmin();
        })->each(function ($admin) use ($ingredient) {
            $admin->notify(new \App\Notifications\NewIngredientCreated($ingredient));
        });

        return response()->json([
            'success' => true,
            'ingredient' => [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'unit' => $ingredient->measurement_unit,
                'price' => $ingredient->price,
            ],
            'message' => 'Ingredient created successfully.'
        ]);
    }
}
