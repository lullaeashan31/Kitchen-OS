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
use Illuminate\Support\Collection;

class CostBreakdownDetailSheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $recipes;

    public function __construct(Collection $recipes)
    {
        $this->recipes = $recipes->load(['recipeIngredients.ingredient', 'stages.ingredients.ingredient']);
    }

    public function title(): string
    {
        return 'Per-Recipe Costing';
    }

    public function headings(): array
    {
        return [
            ['PER-RECIPE COST BREAKDOWN'],
            ['Generated', now()->format('Y-m-d H:i:s')],
            [],
            ['Recipe', 'Ingredient', 'Quantity', 'Unit', 'Unit Cost', 'Total Cost'],
        ];
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->recipes as $recipe) {
            // Add recipe header
            $data[] = [
                $recipe->name . ' (Total: ₹' . number_format($recipe->total_cost ?? 0, 2) . ')',
                '',
                '',
                '',
                '',
                '',
            ];

            // Add ingredients
            foreach ($recipe->recipeIngredients as $recipeIngredient) {
                $ingredient = $recipeIngredient->ingredient;
                $unitCost = $ingredient->latest_price ?? $ingredient->price ?? 0;
                $totalCost = $recipeIngredient->cost ?? ($recipeIngredient->quantity * $unitCost);

                $data[] = [
                    '', // Empty recipe name column
                    $ingredient->name ?? 'Unknown',
                    number_format($recipeIngredient->quantity, 3),
                    $recipeIngredient->unit,
                    '₹' . number_format($unitCost, 2),
                    '₹' . number_format($totalCost, 2),
                ];
            }

            $data[] = []; // Empty row between recipes
        }

        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 30,
            'C' => 15,
            'D' => 10,
            'E' => 15,
            'F' => 15,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '7030A0'],
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']],
            ],
            4 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E7E6E6'],
                ],
            ],
        ];
    }
}
