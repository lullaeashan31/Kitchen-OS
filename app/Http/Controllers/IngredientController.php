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

    public function index(Request $request, string $kitchen_slug)
    {
        $query = Ingredient::query()->withCount('recipes');

        // Filter based on role: Staff see only their own ingredients (including pending), Admins see all
        if ($request->user()->isStaff()) {
            $query->where('created_by', $request->user()->id);
        }


        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $ingredients = $query->orderBy('name')->paginate(15);

        return view('ingredients.index', compact('ingredients'));
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
        return response()->json(
            $this->ingredientService->search($query)
        );
    }
    public function storeQuick(QuickCreateIngredientRequest $request, string $kitchen_slug)
    {
        // $this->authorize('create', Ingredient::class); // Optional based on specific permission needs

        $user = auth()->user();
        $status = $user->isAdmin() ? 'approved' : 'pending';

        $ingredientData = [
            'name' => $request->name,
            'category_id' => $request->category_id,
            'storage_location' => $request->storage_location,
            'measurement_unit' => $request->measurement_unit,
            'status' => $status,
            'price' => 0,
            'kitchen_id' => app('current_kitchen')->id,
        ];

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
}
