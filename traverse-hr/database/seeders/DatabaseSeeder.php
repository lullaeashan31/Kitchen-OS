<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database. No default users are seeded with a
     * known password — create the first Super Admin via
     * `php artisan traverse:create-admin` (§6: no shared accounts).
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            OutletAndJobRoleSeeder::class,
            RetentionPolicySeeder::class,
            DocumentTemplateSeeder::class,
        ]);
    }
}
