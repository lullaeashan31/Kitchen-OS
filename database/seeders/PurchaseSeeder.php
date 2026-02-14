<?php

namespace Database\Seeders;

use App\Models\Purchase;
use App\Models\Vendor;
use App\Models\Ingredient;
use Illuminate\Database\Seeder;

class PurchaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create test vendor
        $vendor = Vendor::firstOrCreate(
            ['name' => 'ABC Suppliers'],
            [
                'contact_person' => 'Rajesh Kumar',
                'phone' => '9876543210',
                'email' => 'contact@abcsuppliers.com',
            ]
        );

        // Get some ingredients
        $ingredients = Ingredient::take(3)->get();

        if ($ingredients->count() === 0) {
            $this->command->warn('No ingredients found. Please run ingredient seeder first.');
            return;
        }

        // Create sample purchases
        foreach ($ingredients as $index => $ingredient) {
            Purchase::create([
                'ingredient_id' => $ingredient->id,
                'quantity' => (10 + $index) * 5, // 50, 55, 60
                'unit_price' => 25.00 + ($index * 5), // 25, 30, 35
                'total_price' => ((10 + $index) * 5) * (25.00 + ($index * 5)),
                'purchase_date' => now()->subDays($index),
                'vendor_id' => $vendor->id,
                'vendor' => $vendor->name,
                'created_by' => 1, // Admin user
                'invoice_photo_path' => 'purchases/invoices/sample.jpg',
                'goods_photo_path' => 'purchases/goods/sample.jpg',
                'status' => $index === 0 ? 'pending' : ($index === 1 ? 'approved' : 'pending'),
                'approved_by' => $index === 1 ? 1 : null,
                'approved_at' => $index === 1 ? now() : null,
            ]);
        }

        $this->command->info('Created ' . $ingredients->count() . ' sample purchases.');
    }
}
