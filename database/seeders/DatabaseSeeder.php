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
        // Users
        User::create([
            'name' => 'Admin Chef',
            'email' => 'admin@kitchen.com',
            'phone' => '9999999999',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
            'is_password_changed' => true,
        ]);

        User::create([
            'name' => 'Manager John',
            'email' => 'manager@kitchen.com',
            'phone' => '8888888888',
            'password' => Hash::make('password'),
            'role' => UserRole::Manager,
            'is_password_changed' => true,
        ]);

        User::create([
            'name' => 'Staff Alice',
            'email' => 'staff@kitchen.com',
            'phone' => '7777777777',
            'password' => Hash::make('password'),
            'role' => UserRole::Staff,
            'staff_code' => '123456',
            'target_latitude' => 28.6139,
            'target_longitude' => 77.2090, // Example location
            'target_location_name' => 'Main Kitchen',
            'is_password_changed' => false, // Force change for staff
        ]);

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
