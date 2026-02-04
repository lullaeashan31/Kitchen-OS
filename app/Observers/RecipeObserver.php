<?php

namespace App\Observers;

use App\Models\Recipe;
use App\Models\RecipeVersion;

class RecipeObserver
{
    /**
     * Handle the Recipe "updated" event.
     */
    public function updated(Recipe $recipe): void
    {
        // Create a version snapshot whenever recipe is updated
        $this->createVersionSnapshot($recipe);
    }

    /**
     * Create a version snapshot of the recipe
     */
    protected function createVersionSnapshot(Recipe $recipe): void
    {
        // Increment version number
        // Increment version number silently to prevent infinite loop
        $recipe->version++;
        $recipe->saveQuietly();

        // Prepare snapshot data
        $snapshotData = [
            'name' => $recipe->name,
            'category_id' => $recipe->category_id,
            'method' => $recipe->method,
            'yields' => $recipe->yields,
            'status' => $recipe->status->value,
            'ingredients' => $recipe->recipeIngredients->map(function ($recipeIngredient) {
                return [
                    'ingredient_id' => $recipeIngredient->ingredient_id,
                    'ingredient_name' => $recipeIngredient->ingredient->name,
                    'quantity' => $recipeIngredient->quantity,
                    'unit' => $recipeIngredient->unit,
                    'cost' => $recipeIngredient->cost,
                ];
            })->toArray(),
        ];

        // Create version record
        RecipeVersion::create([
            'recipe_id' => $recipe->id,
            'data' => $snapshotData,
            'version' => $recipe->version,
            'edited_by' => auth()->id() ?? $recipe->created_by,
            'created_at' => now(),
        ]);
    }
}
