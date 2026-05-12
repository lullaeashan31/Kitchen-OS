<?php

namespace App\Services;

use App\Models\ProductionDay;
use App\Models\ProductionItem;
use App\Models\Recipe;
use App\Models\User;
use App\Enums\ProductionStatus;
use Illuminate\Support\Facades\DB;

class ProductionService
{
    protected $fifoService;

    public function __construct(FIFOInventoryService $fifoService)
    {
        $this->fifoService = $fifoService;
    }
    public function createProductionDay(string $date, ?string $notes, User $user): ProductionDay
    {
        $pd = ProductionDay::create([
            'date' => $date,
            'notes' => $notes,
            'created_by' => $user->id,
        ]);

        \App\Models\AuditLog::log('Created Production Day', $pd);

        return $pd;
    }

    public function addRecipe(ProductionDay $productionDay, int $recipeId, int $portions): ProductionItem
    {
        $item = $productionDay->items()->create([
            'recipe_id' => $recipeId,
            'portions' => $portions,
            'status' => ProductionStatus::Pending,
        ]);

        \App\Models\AuditLog::log('Added Recipe to Production', $productionDay, null, ['item_id' => $item->id, 'recipe_id' => $recipeId, 'portions' => $portions]);

        return $item;
    }

    public function updateItemStatus(ProductionItem $item, ProductionStatus $status): bool
    {
        $oldStatus = $item->status;
        if ($oldStatus === $status) return true;

        return DB::transaction(function () use ($item, $status, $oldStatus) {
            $item->status = $status;
            $saved = $item->save();

            if ($saved && $status === ProductionStatus::Completed && $oldStatus !== ProductionStatus::Completed) {
                // 1. Deduct Ingredients (FIFO)
                $this->deductIngredientsForProduction($item);
                
                // 2. Add Produced Stock (if sub-recipe)
                $this->addProducedStock($item);
            }

            if ($saved) {
                \App\Models\AuditLog::log('Updated Production Item Status', $item->productionDay, 
                    ['status' => $oldStatus->value], 
                    ['status' => $status->value, 'item_id' => $item->id]
                );
            }

            return $saved;
        });
    }

    protected function deductIngredientsForProduction(ProductionItem $item)
    {
        $recipe = $item->recipe;
        $portions = $item->portions;
        
        $recipe->load('recipeIngredients.ingredient');

        foreach ($recipe->recipeIngredients as $recipeIngredient) {
            $ingredient = $recipeIngredient->ingredient;
            if (!$ingredient) continue;

            // Calculate exact quantity to deduct based on scaled recipe
            $qtyPerPortion = (float) ($recipeIngredient->quantity / ($recipe->yields ?: 1));
            $totalToDeduct = $qtyPerPortion * $portions;

            // Convert to ingredient base unit
            $recipeUnit = $this->fifoService->resolveUnit($recipeIngredient->unit);
            $baseUnit = $this->fifoService->resolveUnit($ingredient->measurement_unit);
            
            $deductQty = $totalToDeduct;
            if ($recipeUnit && $baseUnit && $recipeUnit->canConvertTo($baseUnit)) {
                $deductQty = $recipeUnit->convertTo($totalToDeduct, $baseUnit);
            }

            try {
                $this->fifoService->deductStock($ingredient, $deductQty, 'RECIPE_PRODUCTION', $item->id);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Production Deduction Failed: ' . $e->getMessage(), [
                    'item_id' => $item->id,
                    'ingredient' => $ingredient->name
                ]);
                // We let the exception bubble up to rollback the transaction
                throw $e;
            }
        }
    }

    protected function addProducedStock(ProductionItem $item)
    {
        $recipe = $item->recipe;
        if (!$recipe->is_sub_recipe || !$recipe->produces_ingredient_id) return;

        $producedIngredient = $recipe->producesIngredient;
        if (!$producedIngredient) return;

        // Create a special "Production Purchase" batch for FIFO
        // This makes the produced item available for other recipes
        $quantity = $item->portions; // Output in portions
        $unitPrice = $recipe->cost_per_portion;
        $totalPrice = $quantity * $unitPrice;

        \App\Models\Purchase::create([
            'kitchen_id' => $item->kitchen_id,
            'ingredient_id' => $producedIngredient->id,
            'quantity' => $quantity,
            'unit' => $recipe->output_unit ?? 'pcs',
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'purchase_date' => $item->productionDay->date,
            'status' => 'approved',
            'remaining_quantity' => $quantity,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'vendor' => 'IN-HOUSE PRODUCTION',
        ]);

        // Also update the ingredient's main stock table for general visibility
        $stockBefore = $producedIngredient->current_stock;
        $producedIngredient->increment('current_stock', $quantity);
        $producedIngredient->refresh();
        $stockAfter = $producedIngredient->current_stock;

        // Log the addition
        \App\Models\InventoryLog::create([
            'kitchen_id' => $item->kitchen_id,
            'ingredient_id' => $producedIngredient->id,
            'user_id' => auth()->id(),
            'quantity_change' => $quantity,
            'action' => 'RECIPE_PRODUCTION',
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reason' => "Production of sub-recipe: {$recipe->name}",
        ]);
    }

    /**
     * Aggregate all ingredients needed for a production day.
     * Scales recipes based on portions and sums up total ingredient requirements.
     */
    public function calculateTotalIngredients(ProductionDay $productionDay): array
    {
        $totalIngredients = [];
        $productionDay->load('items.recipe.recipeIngredients.ingredient');

        foreach ($productionDay->items as $item) {
            $scaled = $item->scaled_ingredients; // Uses accessor from model

            foreach ($scaled as $component) {
                $ingredientId = $component['ingredient']->id;

                if (!isset($totalIngredients[$ingredientId])) {
                    $totalIngredients[$ingredientId] = [
                        'name' => $component['ingredient']->name,
                        'total_quantity' => 0,
                        'unit' => $component['unit'], // Assuming same unit for same ingredient across recipes
                        'total_cost' => 0,
                    ];
                }

                $totalIngredients[$ingredientId]['total_quantity'] += $component['quantity'];
                $totalIngredients[$ingredientId]['total_cost'] += $component['cost'];
            }
        }

        return $totalIngredients;
    }
}
