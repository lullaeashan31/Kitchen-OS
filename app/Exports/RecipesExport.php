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
                    'yields' => $recipe->yields,
                    'status' => $recipe->status->value,
                    'ingredient_name' => '',
                    'quantity' => '',
                    'unit' => '',
                    'cost' => '',
                ]);
            } else {
                foreach ($recipe->recipeIngredients as $ri) {
                    $rows->push([
                        'recipe_name' => $recipe->name,
                        'category' => $recipe->category->name ?? '',
                        'method' => $recipe->method,
                        'yields' => $recipe->yields,
                        'status' => $recipe->status->value,
                        'ingredient_name' => $ri->ingredient->name,
                        'quantity' => $ri->quantity,
                        'unit' => $ri->unit,
                        'cost' => $ri->cost,
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
            'Yields',
            'Status',
            'Ingredient Name',
            'Quantity',
            'Unit',
            'Cost'
        ];
    }
}
