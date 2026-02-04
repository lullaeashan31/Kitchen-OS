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
        $this->info('Starting recipe cost population...');

        $recipes = Recipe::with('recipeIngredients')->get();
        $bar = $this->output->createProgressBar($recipes->count());

        foreach ($recipes as $recipe) {
            $totalCost = $recipe->recipeIngredients->sum('cost');
            $costPerPortion = $recipe->yields > 0 ? ($totalCost / $recipe->yields) : 0;

            Recipe::withoutEvents(function () use ($recipe, $totalCost, $costPerPortion) {
                $recipe->update([
                    'total_cost' => $totalCost,
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
