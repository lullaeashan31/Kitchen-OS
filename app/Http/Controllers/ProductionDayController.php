<?php

namespace App\Http\Controllers;

use App\Models\ProductionDay;
use App\Models\Recipe;
use App\Services\ProductionService;
use App\Http\Requests\StoreProductionDayRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProductionDayController extends Controller
{
    use AuthorizesRequests;

    protected $productionService;

    public function __construct(ProductionService $productionService)
    {
        $this->productionService = $productionService;
    }

    public function index()
    {
        $productionDays = ProductionDay::with('creator')->latest('date')->paginate(10);
        return view('production.index', compact('productionDays'));
    }

    public function create()
    {
        $this->authorize('create', ProductionDay::class);
        return view('production.create');
    }

    public function store(StoreProductionDayRequest $request)
    {
        $this->authorize('create', ProductionDay::class);

        $productionDay = $this->productionService->createProductionDay(
            $request->date,
            $request->notes,
            $request->user()
        );

        return redirect()->route('production.show', $productionDay)
            ->with('success', 'Production day initialized.');
    }

    public function show(ProductionDay $productionDay)
    {
        $productionDay->load(['items.recipe', 'tasks.assignedUser', 'driveFiles']);
        $totalIngredients = $this->productionService->calculateTotalIngredients($productionDay);
        $recipes = Recipe::where('status', 'permanent')->orderBy('name')->get(); // For adding items

        return view('production.show', compact('productionDay', 'totalIngredients', 'recipes'));
    }

    public function addItem(Request $request, ProductionDay $productionDay)
    {
        $this->authorize('update', $productionDay);

        $request->validate([
            'recipe_id' => 'required|exists:recipes,id',
            'portions' => 'required|integer|min:1',
        ]);

        $this->productionService->addRecipe(
            $productionDay,
            $request->recipe_id,
            $request->portions
        );

        return back()->with('success', 'Recipe added to production.');
    }

    public function toggleItemStatus(Request $request, \App\Models\ProductionItem $item)
    {
        // Check if user has access to the production day of this item
        $this->authorize('update', $item->productionDay);

        $newStatus = $request->input('status');
        // Validate enum or simple logic
        // Ideally use Enum validation

        $item->update(['status' => $newStatus]);

        return back()->with('success', 'Item status updated.');
    }

    public function print(ProductionDay $productionDay)
    {
        $productionDay->load(['items.recipe', 'tasks']);
        $totalIngredients = $this->productionService->calculateTotalIngredients($productionDay);

        return view('production.print', compact('productionDay', 'totalIngredients'));
    }

    public function destroy(ProductionDay $productionDay)
    {
        $this->authorize('delete', $productionDay);
        $productionDay->delete();
        return redirect()->route('production.index')->with('success', 'Production Day deleted.');
    }
}
