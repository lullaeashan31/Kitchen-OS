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
        User::updateOrCreate(['phone' => '0000000000'], [
            'name' => 'Super Admin',
            'email' => 'superadmin@kitchen.com',
            'password' => Hash::make('admin123'),
            'role' => UserRole::SuperAdmin,
            'is_password_changed' => true,
        ]);

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
            Category::firstOrCreate(['name' => $cat]);
        }

        // Ingredients
        $ingredients = ['Salt', 'Pepper', 'Olive Oil', 'Garlic', 'Onion', 'Tomato', 'Chicken Breast', 'Rice', 'Flour', 'Sugar', 'Milk', 'Eggs'];
        foreach ($ingredients as $ing) {
            Ingredient::firstOrCreate(['name' => $ing]);
        }
        // Shifts
        $shifts = [
            ['name' => 'Morning', 'start_time' => '09:00:00', 'end_time' => '17:00:00', 'is_active' => true],
            ['name' => 'Evening', 'start_time' => '14:00:00', 'end_time' => '22:00:00', 'is_active' => true],
            ['name' => 'Night', 'start_time' => '22:00:00', 'end_time' => '06:00:00', 'is_active' => true],
        ];
        foreach ($shifts as $shift) {
            \App\Models\Shift::firstOrCreate(['name' => $shift['name']], $shift);
        }
    }
}
