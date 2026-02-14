<?php

namespace App\Exports;

use App\Models\Recipe;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class FullRecipeCardSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $recipe;

    public function __construct(Recipe $recipe)
    {
        $this->recipe = $recipe->load(['category', 'stages.ingredients.ingredient', 'recipeIngredients.ingredient']);
    }

    public function title(): string
    {
        // Excel sheet names are limited to 31 characters
        return substr($this->recipe->name, 0, 31);
    }

    public function headings(): array
    {
        return [
            ['RECIPE INFORMATION'],
            [],
            ['Recipe Name', $this->recipe->name],
            ['Category', $this->recipe->category->name ?? 'Uncategorized'],
            ['Version', $this->recipe->version],
            ['Yield (Portions)', $this->recipe->yield_portions ?? $this->recipe->yields],
            ['Yield (Batches)', $this->recipe->yield_batches ?? 'N/A'],
            ['Status', ucfirst($this->recipe->status->value)],
            ['Created By', $this->recipe->creator->name ?? 'Unknown'],
            ['Created At', $this->recipe->created_at->format('Y-m-d H:i:s')],
            [],
            ['INGREDIENT SETS'],
            [],
            ['Set Name', 'Ingredient', 'Quantity', 'Unit', 'Group/Note'],
        ];
    }

    public function array(): array
    {
        $data = [];
        
        // Add ingredient sets
        foreach ($this->recipe->stages as $stage) {
            if ($stage->name === 'Sub-Recipes') {
                continue; // Skip sub-recipes stage, handled separately
            }
            
            foreach ($stage->ingredients as $index => $ingredient) {
                $data[] = [
                    $index === 0 ? $stage->name : '', // Only show stage name for first ingredient
                    $ingredient->ingredient->name ?? 'Unknown',
                    $ingredient->quantity,
                    $ingredient->unit,
                    $ingredient->ingredient_group ?? '',
                ];
            }
            
            // Add method/instructions if available
            if (!empty($stage->method)) {
                $data[] = ['', 'Method:', $stage->method, '', ''];
            }
            
            $data[] = []; // Empty row between sets
        }

        // Add Sub-Recipes section
        $subRecipeStage = $this->recipe->stages->where('name', 'Sub-Recipes')->first();
        if ($subRecipeStage && $subRecipeStage->ingredients->count() > 0) {
            $data[] = ['SUB-RECIPES USED'];
            $data[] = ['Sub-Recipe', 'Quantity', 'Unit', '', ''];
            
            foreach ($subRecipeStage->ingredients as $ingredient) {
                $data[] = [
                    $ingredient->ingredient->name ?? 'Unknown',
                    $ingredient->quantity,
                    $ingredient->unit,
                    '',
                    '',
                ];
            }
            $data[] = [];
        }

        // Add Method/Instructions
        if (!empty($this->recipe->method)) {
            $data[] = ['RECIPE METHOD/INSTRUCTIONS'];
            $data[] = [$this->recipe->method];
            $data[] = [];
        }

        // Add Allergens (if available in recipe model)
        if (isset($this->recipe->allergens) && !empty($this->recipe->allergens)) {
            $data[] = ['ALLERGENS'];
            $data[] = [is_array($this->recipe->allergens) ? implode(', ', $this->recipe->allergens) : $this->recipe->allergens];
        }

        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 30,
            'C' => 15,
            'D' => 10,
            'E' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']],
            ],
            12 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E7E6E6'],
                ],
            ],
            13 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9'],
                ],
            ],
        ];
    }
}
