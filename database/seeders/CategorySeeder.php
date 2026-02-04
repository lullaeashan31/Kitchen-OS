<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Vegetables',
            'Meat',
            'Seafood',
            'Dairy',
            'Dry Goods',
            'Spices',
            'Oils',
            'Bakery',
            'Frozen',
            'Beverages',
            'Other',
        ];

        foreach ($categories as $category) {
            \App\Models\Category::firstOrCreate(
                ['name' => $category],
                ['status' => 'active']
            );
        }
    }
}
