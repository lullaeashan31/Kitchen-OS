<?php

namespace App\Exports;

use App\Models\Recipe;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Collection;

class ProcurementListExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    protected $recipes;
    protected $ingredients;

    public function __construct(Collection $recipes)
    {
        $this->recipes = $recipes->load(['stages.ingredients.ingredient', 'recipeIngredients.ingredient']);
        $this->ingredients = $this->consolidateIngredients();
    }

    protected function consolidateIngredients()
    {
        $consolidated = [];

        foreach ($this->recipes as $recipe) {
            // Process ingredient sets
            foreach ($recipe->stages as $stage) {
                if ($stage->name === 'Sub-Recipes') {
                    continue; // Skip sub-recipes
                }

                foreach ($stage->ingredients as $ingredient) {
                    $ingName = $ingredient->ingredient->name ?? 'Unknown';
                    $unit = $ingredient->unit;
                    $key = $ingName . '|' . $unit;

                    if (!isset($consolidated[$key])) {
                        $consolidated[$key] = [
                            'name' => $ingName,
                            'unit' => $unit,
                            'quantity' => 0,
                            'recipes' => [],
                        ];
                    }

                    $consolidated[$key]['quantity'] += $ingredient->quantity;
                    if (!in_array($recipe->name, $consolidated[$key]['recipes'])) {
                        $consolidated[$key]['recipes'][] = $recipe->name;
                    }
                }
            }
        }

        return collect($consolidated)->sortBy('name')->values();
    }

    public function title(): string
    {
        return 'Procurement List';
    }

    public function headings(): array
    {
        return [
            ['PROCUREMENT LIST'],
            ['Generated', now()->format('Y-m-d H:i:s')],
            [],
            ['Ingredient Name', 'Total Quantity', 'Unit', 'Recipes Used In'],
        ];
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->ingredients as $ingredient) {
            $data[] = [
                $ingredient['name'],
                number_format($ingredient['quantity'], 3),
                $ingredient['unit'],
                implode(', ', $ingredient['recipes']),
            ];
        }

        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 18,
            'C' => 12,
            'D' => 50,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 14],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '70AD47'],
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

    public static function export(Collection $recipes)
    {
        $export = new self($recipes);
        return \Maatwebsite\Excel\Facades\Excel::download($export, 'procurement-list-' . date('Y-m-d') . '.xlsx');
    }
}
