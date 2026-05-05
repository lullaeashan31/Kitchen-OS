<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\InventoryDeduction;
use App\Models\Ingredient;
use App\Models\InventoryLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Enums\Unit;

class FIFOInventoryService
{
    /**
     * Deduct stock using FIFO logic
     * 
     * @param Ingredient $ingredient
     * @param float $quantityToDeduct In ingredient base unit
     * @param string|null $sourceType Production, Adjustment, etc
     * @param int|null $sourceId
     * @return float Total cost of deducted stock
     */
    public function deductStock(Ingredient $ingredient, float $quantityToDeduct, ?string $sourceType = null, ?int $sourceId = null): float
    {
        if ($quantityToDeduct <= 0) return 0;

        return DB::transaction(function () use ($ingredient, $quantityToDeduct, $sourceType, $sourceId) {
            $remainingToDeduct = $quantityToDeduct;
            $totalCost = 0;

            // 1. Get available batches (approved purchases with remaining quantity)
            // Sorted by created_at (FIFO)
            $batches = \App\Models\PurchaseBatch::where('ingredient_id', $ingredient->id)
                ->where('quantity_remaining', '>', 0)
                ->orderBy('created_at', 'asc')
                ->lockForUpdate()
                ->get();
 
            foreach ($batches as $batch) {
                if ($remainingToDeduct <= 0) break;
 
                // Price is already normalized to base unit in PurchaseBatch
                $pricePerBaseUnit = (float) $batch->price_per_unit;
 
                $take = min($remainingToDeduct, $batch->quantity_remaining);
                
                // Strictly process batch independently using exact per-unit price
                $batchCost = $take * $pricePerBaseUnit;
                $totalCost += $batchCost;
 
                // Create deduction record
                InventoryDeduction::create([
                    'kitchen_id' => $ingredient->kitchen_id,
                    'ingredient_id' => $ingredient->id,
                    'purchase_id' => $batch->purchase_id,
                    'quantity_used' => $take,
                    'unit_price' => $pricePerBaseUnit,
                    'total_cost' => $batchCost,
                    'source_type' => $sourceType,
                    'source_id' => $sourceId,
                ]);
 
                // Update batch remaining quantity
                $batch->decrement('quantity_remaining', $take);
                
                $remainingToDeduct -= $take;
            }

            // 2. Update Ingredient current_stock to stay in sync with FIFO
            $ingredient->decrement('current_stock', $quantityToDeduct);

            // 2. Handle insufficient stock
            if ($remainingToDeduct > 0.0001) {
                Log::warning("FIFO: Insufficient stock for {$ingredient->name}. Missing: " . $remainingToDeduct, [
                    'ingredient_id' => $ingredient->id,
                    'requested' => $quantityToDeduct,
                    'missing' => $remainingToDeduct
                ]);
                throw new \Exception("Insufficient stock for {$ingredient->name}. Missing: " . number_format($remainingToDeduct, 3));
            }

            return $totalCost;
        });
    }

    /**
     * Helper to resolve a unit string to a Unit enum case
     */
    public function resolveUnit($unit): ?Unit
    {
        if ($unit instanceof Unit) return $unit;
        if (empty($unit)) return null;

        // Try direct value match (e.g. 'g', 'kg')
        $resolved = Unit::tryFrom($unit);
        if ($resolved) return $resolved;

        // Try case name match (e.g. 'Gram', 'Kilogram')
        foreach (Unit::cases() as $case) {
            if (strcasecmp($case->name, $unit) === 0) return $case;
        }

        // Try label match (e.g. 'Gram (g)', 'Piece (pcs)')
        foreach (Unit::cases() as $case) {
            if (stripos($case->label(), $unit) !== false) return $case;
        }

        return null;
    }

    /**
     * Calculate current inventory value/cost for a specific quantity
     */
    public function calculateFIFOCost(Ingredient $ingredient, float $quantity): float
    {
        if ($quantity <= 0) return 0;

        $remaining = $quantity;
        $totalCost = 0;

        // 1. Fetch available batches
        $batches = \App\Models\PurchaseBatch::where('ingredient_id', $ingredient->id)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();
 
        if ($batches->isNotEmpty()) {
            foreach ($batches as $batch) {
                if ($remaining <= 0) break;
 
                $pricePerBaseUnit = (float) $batch->price_per_unit;
                $take = min($remaining, $batch->quantity_remaining);
                $batchCost = $take * $pricePerBaseUnit;
                $totalCost += $batchCost;
                $remaining -= $take;
            }
        }

        // 2. Handle Scenarios for missing physical stock
        if ($remaining > 0.0001) {
            // Check for sub-recipe production
            $producingRecipe = \App\Models\Recipe::where('produces_ingredient_id', $ingredient->id)
                ->where('status', \App\Enums\RecipeStatus::Permanent->value)
                ->first();

            if ($producingRecipe) {
                $subRecipeTotalCost = (float) $producingRecipe->total_cost;
                $costPerUnit = 0;

                $recipeYieldGrams = (float) $producingRecipe->yield_weight_grams;
                $recipeYieldPortions = (float) ($producingRecipe->yield_portions ?: $producingRecipe->yields);

                // Priority 1: Weight-based conversion (Grams/KG)
                if ($recipeYieldGrams > 0 && ($ingredient->measurement_unit === 'g' || $ingredient->measurement_unit === 'kg')) {
                    $costPerGram = $subRecipeTotalCost / $recipeYieldGrams;
                    $costPerUnit = ($ingredient->measurement_unit === 'kg') ? ($costPerGram * 1000) : $costPerGram;
                } 
                // Priority 2: Portion-based conversion
                elseif ($recipeYieldPortions > 0) {
                    $costPerUnit = $subRecipeTotalCost / $recipeYieldPortions;
                }
                // Fallback: Legacy output_quantity
                elseif ($producingRecipe->output_quantity > 0) {
                    $costPerUnit = $subRecipeTotalCost / $producingRecipe->output_quantity;
                }

                if ($costPerUnit > 0) {
                    $totalCost += $remaining * $costPerUnit;
                    $remaining = 0;
                } else {
                    throw new \Exception("Cannot calculate cost for sub-recipe ingredient '{$ingredient->name}'. No yield/output information available on the producing recipe.");
                }
            } else {
                // STRICT: No fallback to average or latest price.
                throw new \Exception("Insufficient stock for {$ingredient->name}. Missing: " . number_format($remaining, 3) . " " . $ingredient->measurement_unit);
            }
        }

        return $totalCost;
    }
}
