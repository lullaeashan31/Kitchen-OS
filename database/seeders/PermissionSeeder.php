<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'View Dashboard', 'slug' => 'view_dashboard'],
            ['name' => 'Manage Staff', 'slug' => 'manage_staff'],
            ['name' => 'Manage Attendance', 'slug' => 'manage_attendance'],
            ['name' => 'Approve Recipes', 'slug' => 'approve_recipes'],
            ['name' => 'Create Recipes', 'slug' => 'create_recipes'],
            ['name' => 'View Reports', 'slug' => 'view_reports'],
            ['name' => 'Manage Ingredients', 'slug' => 'manage_ingredients'],
            ['name' => 'Manage Production', 'slug' => 'manage_production'],
            ['name' => 'System Settings', 'slug' => 'system_settings'],
            ['name' => 'View SOP', 'slug' => 'sop_view'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['slug' => $permission['slug']], $permission);
        }
    }
}
