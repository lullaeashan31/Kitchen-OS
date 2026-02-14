<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesReportTemplateExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect([
            [
                'item_name' => 'Tomato',
                'quantity_sold' => '2.5',
            ],
            [
                'item_name' => 'Chicken Breast',
                'quantity_sold' => '1.2',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'item_name',
            'quantity_sold'
        ];
    }
}
