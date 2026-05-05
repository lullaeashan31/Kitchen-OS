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
    protected $fifoService;

    public function __construct(IngredientService $ingredientService, \App\Services\FIFOInventoryService $fifoService)
    {
        $this->ingredientService = $ingredientService;
        $this->fifoService = $fifoService;
    }

    public function index(Request $request, string $kitchen_slug)
    {
        $query = Ingredient::query()->withCount('recipes');

        // Filter based on role: Staff see only their own ingredients unless they have module access
        if ($request->user()->isStaff() && !$request->user()->hasPermissionTo('module_inventory')) {
            $query->where('created_by', $request->user()->id);
        }


        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $ingredients = $query->orderBy('name')->paginate(15);

        return view('ingredients.index', compact('ingredients'));
    }

    public function show(string $kitchen_slug, Ingredient $ingredient)
    {
        if (!auth()->user()->isStaff() || auth()->user()->hasPermissionTo('module_inventory')) {
            return redirect()->route('admin.inventory.show', [$kitchen_slug, $ingredient->id]);
        }
        
        return view('ingredients.show', compact('ingredient'));
    }

    public function create(string $kitchen_slug)
    {
        $this->authorize('create', Ingredient::class);
        $categories = Category::forIngredients()->where('status', 'active')->orderBy('name')->get();
        $units = \App\Enums\Unit::cases();
        return view('ingredients.create', compact('categories', 'units'));
    }

    public function store(StoreIngredientRequest $request, string $kitchen_slug)
    {
        $this->authorize('create', Ingredient::class);

        try {
            \Log::info('Ingredient store payload:', $request->all());
            $data = $request->validated();
            $kitchen = app('current_kitchen');
            $data['kitchen_id'] = $kitchen->id;
            if (\Illuminate\Support\Facades\Schema::hasColumn('ingredients', 'created_by')) {
                $data['created_by'] = auth()->id();
            }

            
            $user = auth()->user();
            if ($user->isAdmin()) {
                $data['status'] = 'approved';
                $message = 'Ingredient "' . $data['name'] . '" created and approved.';
            } else {
                $data['status'] = 'pending';
                $message = 'Ingredient "' . $data['name'] . '" submitted for admin approval.';
            }

            $data['purchase_quantity'] = (float)($data['purchase_quantity'] ?? 1);
            $data['purchase_price'] = (float)($data['purchase_price'] ?? 0);
            $data['price'] = $data['purchase_price'];

            $ingredient = Ingredient::create($data);

            return redirect()->route('ingredients.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Ingredient creation CRASHED: ' . $e->getMessage(), [
                'payload' => $request->all(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'ERROR: ' . $e->getMessage()]);
        }
    }

    public function edit(string $kitchen_slug, Ingredient $ingredient)
    {
        $this->authorize('update', $ingredient);
        $categories = Category::forIngredients()->where('status', 'active')->orderBy('name')->get();
        $units = \App\Enums\Unit::cases();
        return view('ingredients.edit', compact('ingredient', 'categories', 'units'));
    }

    public function update(StoreIngredientRequest $request, string $kitchen_slug, Ingredient $ingredient)
    {
        $this->authorize('update', $ingredient);

        $this->ingredientService->update($ingredient, $request->validated());

        return redirect()->route('ingredients.index')
            ->with('success', 'Ingredient updated successfully.');
    }

    public function destroy(string $kitchen_slug, Ingredient $ingredient)
    {
        $this->authorize('delete', $ingredient);
        $ingredient->delete();
        return redirect()->route('ingredients.index')
            ->with('success', 'Ingredient deleted successfully.');
    }

    public function search(Request $request, string $kitchen_slug)
    {
        $query = $request->get('q');
        $ingredients = $this->ingredientService->search($query);
        
        return response()->json($ingredients->map(function($ing) {
            return [
                'id' => $ing->id,
                'name' => $ing->name,
                'unit' => $ing->measurement_unit,
                'price' => $ing->latest_price ?? $ing->price,
            ];
        }));
    }
    public function storeQuick(QuickCreateIngredientRequest $request, string $kitchen_slug)
    {
        // $this->authorize('create', Ingredient::class); // Optional based on specific permission needs

        $user = auth()->user();
        
        // FORCED: All requests from the recipe creation page go through the pending queue for verification
        // as requested by the user, even if created by an Admin.
        $status = 'pending';

        $ingredientData = array_merge($request->validated(), [
            'status' => $status,
            'kitchen_id' => app('current_kitchen')->id,
            'price' => $request->purchase_price ?? 0,
        ]);

        // Add created_by only if the column exists
        try {
            if (\Illuminate\Support\Facades\Schema::hasColumn('ingredients', 'created_by')) {
                $ingredientData['created_by'] = $user->id;
            }
        } catch (\Exception $e) {}

        $ingredient = Ingredient::create($ingredientData);



        // Notify Admin if created by staff
        if ($status === 'pending') {
            try {
                \App\Models\User::withoutGlobalScopes()
                    ->where('kitchen_id', app('current_kitchen')->id)
                    ->get()
                    ->filter(fn($u) => $u->isAdmin())
                    ->each(fn($admin) => $admin->notify(new \App\Notifications\NewIngredientCreated($ingredient)));
            } catch (\Exception $e) {
                // Notification failure should not block ingredient creation
                \Illuminate\Support\Facades\Log::warning('Failed to send ingredient notification: ' . $e->getMessage());
            }
        }



        return response()->json([
            'success' => true,
            'status' => $ingredient->status,
            'ingredient' => [
                'id' => $ingredient->id,
                'name' => $ingredient->name,
                'unit' => $ingredient->measurement_unit,
                'price' => $ingredient->price,
            ],
            'message' => $status === 'approved'
                ? 'Ingredient created and approved.'
                : 'Ingredient submitted for admin approval.'
        ]);

    }

    public function getFIFOCost(Request $request, string $kitchen_slug, Ingredient $ingredient)
    {
        $quantity = (float) $request->get('quantity', 0);
        $unitParam = $request->get('unit');
        
        if ($quantity <= 0) {
            return response()->json(['cost' => 0]);
        }

        try {
            // Use the service's resolveUnit for consistency
            $baseUnit = $this->fifoService->resolveUnit($ingredient->measurement_unit);
            $inputUnit = $this->fifoService->resolveUnit($unitParam);
            
            $quantityInBaseUnit = $quantity;
            if ($inputUnit && $baseUnit && $inputUnit->canConvertTo($baseUnit)) {
                $quantityInBaseUnit = $inputUnit->convertTo($quantity, $baseUnit);
            }

            $cost = $this->fifoService->calculateFIFOCost($ingredient, $quantityInBaseUnit);
            return response()->json(['cost' => $cost]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
