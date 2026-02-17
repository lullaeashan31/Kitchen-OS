<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Ingredient;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Users - All with password: admin123
        User::updateOrCreate(['email' => 'admin@kitchen.com'], [
            'name' => 'Admin User',
            'phone' => '9999999999',
            'password' => Hash::make('admin123'),
            'role' => UserRole::Admin,
            'is_password_changed' => true,
        ]);

        User::updateOrCreate(['email' => 'manager@kitchen.com'], [
            'name' => 'Manager User',
            'phone' => '8888888888',
            'password' => Hash::make('admin123'),
            'role' => UserRole::Manager,
            'is_password_changed' => true,
        ]);

        User::updateOrCreate(['email' => 'staff@kitchen.com'], [
            'name' => 'Staff User',
            'phone' => '7777777777',
            'password' => Hash::make('admin123'),
            'role' => UserRole::Staff,
            'staff_code' => '123456',
            'target_latitude' => 28.6139,
            'target_longitude' => 77.2090, // Example location
            'target_location_name' => 'Main Kitchen',
            'is_password_changed' => true,
        ]);

        // Permissions
        $permissions = [
            ['name' => 'HR & Payroll', 'slug' => 'module_hr_payroll'],
            ['name' => 'Inventory Management', 'slug' => 'module_inventory'],
            ['name' => 'Recipe Management', 'slug' => 'module_recipes'],
            ['name' => 'SOP Management', 'slug' => 'module_sops'],
            ['name' => 'Production Management', 'slug' => 'module_production'],
            ['name' => 'Vendor Management', 'slug' => 'module_vendors'],
            ['name' => 'Audit Logs', 'slug' => 'module_audit_logs'],
            ['name' => 'Staff Management', 'slug' => 'module_staff_management'],
        ];

        foreach ($permissions as $perm) {
            \App\Models\Permission::updateOrCreate(['slug' => $perm['slug']], $perm);
        }

        // Categories
        $categories = ['Starters', 'Mains', 'Desserts', 'Beverages', 'Sauces', 'Sides'];
        foreach ($categories as $cat) {
            Category::create(['name' => $cat]);
        }

        // Ingredients
        $ingredients = ['Salt', 'Pepper', 'Olive Oil', 'Garlic', 'Onion', 'Tomato', 'Chicken Breast', 'Rice', 'Flour', 'Sugar', 'Milk', 'Eggs'];
        foreach ($ingredients as $ing) {
            Ingredient::create(['name' => $ing]);
        }
    }
}
