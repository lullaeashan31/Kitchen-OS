<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Models\Ingredient;
use App\Models\ProductionLog;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    protected $unitService;

    public function __construct(\App\Services\UnitConversionService $unitService)
    {
        $this->unitService = $unitService;
    }

    /**
     * Show the production/cooking page.
     */
    public function create()
    {
        // Only show approved/permanent recipes for production
        $recipes = Recipe::where('status', \App\Enums\RecipeStatus::Permanent)
            ->orderBy('name')
            ->get();

        return view('production.create', compact('recipes'));
    }

    /**
     * Record a cooking session (deduct stock).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipe_id' => 'required|exists:recipes,id',
            'portions' => 'required|numeric|min:0.1',
        ]);

        $recipe = Recipe::with('ingredients')->findOrFail($validated['recipe_id']);
        $portions = $validated['portions'];

        // Check Stock Availability First
        $missingStock = [];
        foreach ($recipe->ingredients as $ingredient) {
            $requiredQtyRaw = $ingredient->pivot->quantity * $portions;
            // Normalize recipe yield 
            $requiredQty = $requiredQtyRaw / ($recipe->yields ?: 1);


            // Check if units are set before conversion
            if (!empty($ingredient->pivot->unit) && !empty($ingredient->measurement_unit)) {
                try {
                    $convertedQty = $this->unitService->convert(
                        $requiredQty,
                        $ingredient->pivot->unit, // From
                        $ingredient->measurement_unit // To (Stock Unit)
                    );
                } catch (\Exception $e) {
                    // Fallback if conversion fails
                    $convertedQty = $requiredQty;
                }
            } else {
                // No unit conversion needed if units are not set
                $convertedQty = $requiredQty;
            }

            if ($ingredient->current_stock < $convertedQty) {
                $missingStock[] = $ingredient->name . " (Need: " . number_format($convertedQty, 3) . " " . $ingredient->measurement_unit . ", Have: " . number_format($ingredient->current_stock, 3) . ")";
            }
        }

        if (!empty($missingStock)) {
            return back()->with('error', 'Insufficient Stock: ' . implode(', ', $missingStock));
        }

        DB::transaction(function () use ($recipe, $portions, $request) {
            // 1. Create Production Log
            // Use Approved Standard Cost from Recipe
            $costPerPortion = $recipe->cost_per_portion;
            $totalProductionCost = $costPerPortion * $portions;

            $log = ProductionLog::create([
                'recipe_id' => $recipe->id,
                'user_id' => auth()->id(),
                'portions' => $portions,
                'cost_per_portion' => $costPerPortion,
                'total_cost' => $totalProductionCost,
            ]);

            // 2. Deduct Stock & Create Inventory Logs
            foreach ($recipe->ingredients as $ingredient) {
                $qtyPerPortion = $ingredient->pivot->quantity / ($recipe->yields ?: 1);
                $requiredQty = $qtyPerPortion * $portions; // Total required for this batch


                // Check if units are set before conversion
                if (!empty($ingredient->pivot->unit) && !empty($ingredient->measurement_unit)) {
                    try {
                        $deductAmount = $this->unitService->convert(
                            $requiredQty,
                            $ingredient->pivot->unit,
                            $ingredient->measurement_unit
                        );
                    } catch (\Exception $e) {
                        $deductAmount = $requiredQty;
                    }
                } else {
                    // No unit conversion needed if units are not set
                    $deductAmount = $requiredQty;
                }

                $before = $ingredient->current_stock;
                $after = $before - $deductAmount;

                $ingredient->update(['current_stock' => $after]);

                InventoryLog::create([
                    'ingredient_id' => $ingredient->id,
                    'user_id' => auth()->id(),
                    'quantity_change' => -$deductAmount,
                    'action' => 'RECIPE_USE',
                    'production_log_id' => $log->id,
                    'recipe_id' => $recipe->id,
                    'stock_before' => $before,
                    'stock_after' => $after,
                ]);
            }

            // 3. Add Output Stock (if this is a sub-recipe that produces an ingredient)
            if ($recipe->isSubRecipe()) {
                $producedIngredient = $recipe->producesIngredient;

                // Calculate output amount
                $outputPerYield = $recipe->output_quantity;
                $totalOutput = $outputPerYield * $portions;


                // Convert to ingredient's measurement unit if needed
                if (!empty($recipe->output_unit) && !empty($producedIngredient->measurement_unit)) {
                    try {
                        $addAmount = $this->unitService->convert(
                            $totalOutput,
                            $recipe->output_unit,
                            $producedIngredient->measurement_unit
                        );
                    } catch (\Exception $e) {
                        $addAmount = $totalOutput;
                    }
                } else {
                    // No unit conversion needed if units are not set
                    $addAmount = $totalOutput;
                }

                $before = $producedIngredient->current_stock;
                $after = $before + $addAmount;

                $producedIngredient->update(['current_stock' => $after]);

                InventoryLog::create([
                    'ingredient_id' => $producedIngredient->id,
                    'user_id' => auth()->id(),
                    'quantity_change' => $addAmount,
                    'action' => 'RECIPE_PRODUCTION',
                    'production_log_id' => $log->id,
                    'recipe_id' => $recipe->id,
                    'stock_before' => $before,
                    'stock_after' => $after,
                ]);
            }
        });

        return redirect()->route('production.create')->with('success', "Production recorded! {$portions} portions of {$recipe->name} cooked.");
    }
}
