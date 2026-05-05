<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InventoryTemplateExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return collect([
            [
                'inventory_id' => '',
                'item_name' => 'Onion',
                'category' => 'Vegetables',
                'measurement_unit' => 'kg',
                'purchase_unit' => 'bag',
                'purchase_quantity' => '50',
                'usage_quantity' => '1',
                'price_per_unit' => '2.50',
                'vendor' => 'Fresh Farms',
                'minimum_stock_level' => '10',
                'current_stock' => '50',
            ],
            [
                'inventory_id' => '',
                'item_name' => 'Milk',
                'category' => 'Dairy',
                'measurement_unit' => 'liter',
                'purchase_unit' => 'crate',
                'purchase_quantity' => '12',
                'usage_quantity' => '1',
                'price_per_unit' => '1.20',
                'vendor' => 'Dairy Best',
                'minimum_stock_level' => '5',
                'current_stock' => '20',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'inventory_id',
            'item_name',
            'category',
            'measurement_unit',
            'purchase_unit',
            'purchase_quantity',
            'usage_quantity',
            'price_per_unit',
            'vendor',
            'minimum_stock_level',
            'current_stock'
        ];
    }
}
