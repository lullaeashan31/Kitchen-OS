<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Recipe;

class PopulateRecipeCosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recipes:populate-costs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and populate total_cost and cost_per_portion for all recipes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting recipe cost population with unit conversion...');

        $recipes = Recipe::with(['recipeIngredients.ingredient.purchases' => function($q) {
            $q->approved()->orderBy('purchase_date', 'desc')->orderBy('id', 'desc');
        }])->get();
        
        $bar = $this->output->createProgressBar($recipes->count());

        foreach ($recipes as $recipe) {
            $totalRecipeCost = 0;

            foreach ($recipe->recipeIngredients as $ri) {
                $ingredient = $ri->ingredient;
                if (!$ingredient) continue;

                // Get latest purchase to determine the unit price and its associated unit
                $latestPurchase = $ingredient->purchases->first();
                $purchaseUnitValue = $latestPurchase ? $latestPurchase->unit : $ingredient->measurement_unit;
                $unitCost = $ingredient->latest_price;
                
                $recipeUnit = \App\Enums\Unit::tryFrom($ri->unit);
                $purchaseUnit = \App\Enums\Unit::tryFrom($purchaseUnitValue);

                if ($recipeUnit && $purchaseUnit && $recipeUnit->canConvertTo($purchaseUnit)) {
                    $quantityInPurchaseUnit = $recipeUnit->convertTo((float)$ri->quantity, $purchaseUnit);
                    $ingredientCost = $quantityInPurchaseUnit * $unitCost;
                } else {
                    $ingredientCost = (float)$ri->quantity * $unitCost;
                }

                $ri->update(['cost' => $ingredientCost]);
                $totalRecipeCost += $ingredientCost;
            }

            $costPerPortion = $recipe->yields > 0 ? ($totalRecipeCost / $recipe->yields) : 0;

            Recipe::withoutEvents(function () use ($recipe, $totalRecipeCost, $costPerPortion) {
                $recipe->update([
                    'total_cost' => $totalRecipeCost,
                    'cost_per_portion' => $costPerPortion
                ]);
            });

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('All recipes updated successfully.');
    }
}
