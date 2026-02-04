<?php

namespace App\Exports;

use App\Models\Recipe;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RecipeCostExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $recipes = Recipe::with('recipeIngredients')->get();

        return $recipes->map(function ($recipe) {
            $totalCost = $recipe->recipeIngredients->sum('cost');
            $costPerPortion = $recipe->yields > 0 ? $totalCost / $recipe->yields : 0;

            return [
                'name' => $recipe->name,
                'category' => $recipe->category->name ?? '',
                'yields' => $recipe->yields,
                'ingredients_count' => $recipe->recipeIngredients->count(),
                'total_cost' => number_format($totalCost, 2, '.', ''),
                'cost_per_portion' => number_format($costPerPortion, 2, '.', ''),
                'status' => $recipe->status->label(),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Recipe Name',
            'Category',
            'Yields (Portions)',
            'Ingredients Count',
            'Total Cost',
            'Cost Per Portion',
            'Status'
        ];
    }
}
