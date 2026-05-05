<?php

namespace App\Exports;

use App\Models\Recipe;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RecipesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $recipes = Recipe::with(['category', 'ingredients'])->get();
        $rows = collect();

        foreach ($recipes as $recipe) {
            if ($recipe->recipeIngredients->isEmpty()) {
                $rows->push([
                    'recipe_name' => $recipe->name,
                    'category' => $recipe->category->name ?? '',
                    'method' => $recipe->method,
                    'yield_portions' => $recipe->yield_portions ?? $recipe->yields,
                    'yield_weight_grams' => $recipe->yield_weight_grams,
                    'status' => $recipe->status->value,
                    'ingredient_name' => '',
                    'quantity' => '',
                    'unit' => '',
                    'cost' => '',
                    'purchase_quantity' => '',
                    'usage_quantity' => '',
                ]);
            } else {
                foreach ($recipe->recipeIngredients as $ri) {
                    $rows->push([
                        'recipe_name' => $recipe->name,
                        'category' => $recipe->category->name ?? '',
                        'method' => $recipe->method,
                        'yield_portions' => $recipe->yield_portions ?? $recipe->yields,
                        'yield_weight_grams' => $recipe->yield_weight_grams,
                        'status' => $recipe->status->value,
                        'ingredient_name' => $ri->ingredient->name,
                        'quantity' => $ri->quantity,
                        'unit' => $ri->unit,
                        'cost' => $ri->cost,
                        'purchase_quantity' => $ri->ingredient->purchase_quantity,
                        'usage_quantity' => $ri->ingredient->usage_quantity,
                    ]);
                }
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Recipe Name',
            'Category',
            'Method',
            'Yield (Portions)',
            'Yield (Weight in Grams)',
            'Status',
            'Ingredient Name',
            'Quantity',
            'Unit',
            'Cost',
            'Purchase Quantity',
            'Usage Quantity'
        ];
    }
}
