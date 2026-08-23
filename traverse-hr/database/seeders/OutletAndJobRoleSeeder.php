<?php

namespace Database\Seeders;

use App\Models\JobRole;
use App\Models\Outlet;
use Illuminate\Database\Seeder;

/**
 * PLACEHOLDER DATA — see DECISIONS.md. Only "Alinea" is a real outlet name
 * from the brief; the job-role list is copied verbatim from the brief's
 * §3.6.1 examples (Commis, CDP, Sous Chef, Server, Captain, Bartender,
 * Host, Steward, Manager). Replace/extend once the owner confirms the
 * real outlet and role list.
 */
class OutletAndJobRoleSeeder extends Seeder
{
    public function run(): void
    {
        $alinea = Outlet::firstOrCreate(
            ['code' => 'ALN'],
            ['name' => 'Alinea', 'timezone' => 'Asia/Kolkata', 'payroll_divisor_setting' => 'calendar', 'active' => true]
        );

        $roles = ['Commis', 'CDP', 'Sous Chef', 'Server', 'Captain', 'Bartender', 'Host', 'Steward', 'Manager'];

        foreach ($roles as $i => $name) {
            JobRole::firstOrCreate(
                ['outlet_id' => $alinea->id, 'name' => $name],
                ['sort_order' => $i, 'probation_months' => 3, 'notice_period_days' => 30, 'active' => true]
            );
        }
    }
}
