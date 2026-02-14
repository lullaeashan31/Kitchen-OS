<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PurchaseTemplateExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect([
            [
                'item_name' => 'Tomato',
                'quantity' => '10',
                'unit' => 'kg',
                'price' => '30.50',
                'vendor' => 'Local Market',
            ],
            [
                'item_name' => 'Chicken Breast',
                'quantity' => '5',
                'unit' => 'kg',
                'price' => '250.00',
                'vendor' => 'Meat Supply Co',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'item_name',
            'quantity',
            'unit',
            'price',
            'vendor'
        ];
    }
}
