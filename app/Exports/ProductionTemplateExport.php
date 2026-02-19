<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ProductionTemplateExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return collect([
            [
                'recipe_name' => 'Example Recipe Name',
                'quantity' => '2',
                'unit_type' => 'batches',
            ],
            [
                'recipe_name' => 'Another Recipe',
                'quantity' => '10',
                'unit_type' => 'portions',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Recipe Name',
            'Quantity',
            'Unit Type (batches/portions)'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFE5B4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }
}
