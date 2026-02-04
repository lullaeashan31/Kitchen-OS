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
        $item->status = $status;
        $saved = $item->save();

        if ($saved) {
            \App\Models\AuditLog::log('Updated Production Item Status', $item->productionDay, ['status' => $oldStatus], ['status' => $status, 'item_id' => $item->id]);
        }

        return $saved;
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
