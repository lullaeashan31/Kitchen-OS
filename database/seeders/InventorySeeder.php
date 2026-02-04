<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ingredient;
use App\Models\Category;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure categories exist
        $vegetables = Category::firstOrCreate(['name' => 'Vegetables']);
        $dairy = Category::firstOrCreate(['name' => 'Dairy']);
        $pantry = Category::firstOrCreate(['name' => 'Pantry']);

        // User's specific example: Onion
        Ingredient::updateOrCreate(
            ['name' => 'Onion'],
            [
                'category_id' => $vegetables->id,
                'measurement_unit' => 'kg',
                'purchase_unit' => 'kg',
                'price' => 40.00,
                'current_stock' => 20.00,
                'alert_threshold' => 5.00,
            ]
        );

        // Additional testing data
        Ingredient::updateOrCreate(
            ['name' => 'Tomato'],
            [
                'category_id' => $vegetables->id,
                'measurement_unit' => 'kg',
                'price' => 30.00,
                'current_stock' => 15.00,
                'alert_threshold' => 5.00,
            ]
        );

        Ingredient::updateOrCreate(
            ['name' => 'Milk'],
            [
                'category_id' => $dairy->id,
                'measurement_unit' => 'l',
                'price' => 60.00,
                'current_stock' => 50.00,
                'alert_threshold' => 10.00,
            ]
        );

        Ingredient::updateOrCreate(
            ['name' => 'Rice'],
            [
                'category_id' => $pantry->id,
                'measurement_unit' => 'kg',
                'price' => 50.00,
                'current_stock' => 100.00,
                'alert_threshold' => 20.00,
            ]
        );

        $this->command->info('Inventory seeding completed with Onion and other test data.');
    }
}
