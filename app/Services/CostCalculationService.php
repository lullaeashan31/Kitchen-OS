<?php

namespace App\Services;

use App\Models\Recipe;

class CostCalculationService
{
    /**
     * Calculate the total cost of a recipe based on its ingredients.
     */
    public function calculateRecipeCost(Recipe $recipe): float
    {
        // Force refresh relations to ensure we have latest data
        $recipe->load('recipeIngredients');

        return $recipe->recipeIngredients->sum('cost');
    }

    /**
     * Calculate cost per single portion for a recipe.
     */
    public function calculateCostPerPortion(Recipe $recipe): float
    {
        $totalCost = $this->calculateRecipeCost($recipe);
        $yields = $recipe->yields > 0 ? $recipe->yields : 1;

        return $totalCost / $yields;
    }

    /**
     * Calculate cost for specific number of portions.
     */
    public function calculateCostForPortions(Recipe $recipe, int $portions): float
    {
        return $this->calculateCostPerPortion($recipe) * $portions;
    }
}
