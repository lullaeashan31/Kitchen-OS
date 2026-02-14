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

class CostBreakdownSummarySheet implements FromArray, WithTitle, WithHeadings, WithStyles, WithColumnWidths
{
    protected $recipes;

    public function __construct(Collection $recipes)
    {
        $this->recipes = $recipes;
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function headings(): array
    {
        return [
            ['COST BREAKDOWN SUMMARY'],
            ['Generated', now()->format('Y-m-d H:i:s')],
            [],
            ['Recipe Name', 'Category', 'Total Cost', 'Yield (Portions)', 'Cost per Portion', 'Margin %', 'Selling Price (30% Margin)'],
        ];
    }

    public function array(): array
    {
        $data = [];
        $totalCost = 0;
        $totalYield = 0;

        foreach ($this->recipes as $recipe) {
            $cost = $recipe->total_cost ?? 0;
            $yield = $recipe->yield_portions ?? $recipe->yields ?? 1;
            $costPerPortion = $yield > 0 ? $cost / $yield : 0;
            
            // Calculate selling price with 30% margin
            $sellingPrice = $costPerPortion / 0.7; // If cost is 70%, selling price is cost/0.7
            $margin = $yield > 0 ? (($sellingPrice - $costPerPortion) / $sellingPrice) * 100 : 0;

            $data[] = [
                $recipe->name,
                $recipe->category->name ?? 'Uncategorized',
                '₹' . number_format($cost, 2),
                $yield,
                '₹' . number_format($costPerPortion, 2),
                number_format($margin, 1) . '%',
                '₹' . number_format($sellingPrice, 2),
            ];

            $totalCost += $cost;
            $totalYield += $yield;
        }

        // Add totals row
        $data[] = [];
        $data[] = [
            'TOTAL',
            '',
            '₹' . number_format($totalCost, 2),
            $totalYield,
            $totalYield > 0 ? '₹' . number_format($totalCost / $totalYield, 2) : '₹0.00',
            '',
            '',
        ];

        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30,
            'B' => 20,
            'C' => 15,
            'D' => 15,
            'E' => 18,
            'F' => 15,
            'G' => 25,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->recipes->count() + 5; // Headers + data + empty + total

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
            $lastRow => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9'],
                ],
            ],
        ];
    }
}
