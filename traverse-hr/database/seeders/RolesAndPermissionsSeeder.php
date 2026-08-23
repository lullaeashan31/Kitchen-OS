<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeds the four system-user permission profiles from PERMISSION_MATRIX.md.
 * Granular permission strings, not hardcoded role-name checks — Super Admin
 * can regrant/rearrange these later from Admin → Permissions.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'user.manage', 'audit-log.view', 'admin.settings.manage',
            'recruitment.manage', 'recruitment.view',
            'offer.manage', 'offer.approve', 'offer.view',
            'employee.manage', 'employee.view', 'employee.view-unmasked',
            'document.manage', 'document.view',
            'salary-structure.manage', 'salary-structure.publish',
            'payroll.manage', 'payroll.lock', 'payroll.mark-paid', 'payroll.view',
            'statutory-report.view',
            'bypass-outlet-scope',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $hrManager = Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'web']);
        $hrManager->syncPermissions([
            'recruitment.manage', 'recruitment.view',
            'offer.manage', 'offer.approve', 'offer.view',
            'employee.manage', 'employee.view', 'employee.view-unmasked',
            'document.manage', 'document.view',
            'salary-structure.manage',
            'payroll.manage', 'payroll.view',
            'statutory-report.view',
            'bypass-outlet-scope',
        ]);

        $accounts = Role::firstOrCreate(['name' => 'accounts', 'guard_name' => 'web']);
        $accounts->syncPermissions([
            'recruitment.view',
            'employee.view', 'employee.view-unmasked',
            'document.view',
            'salary-structure.manage', 'salary-structure.publish',
            'payroll.manage', 'payroll.lock', 'payroll.mark-paid', 'payroll.view',
            'statutory-report.view',
            'bypass-outlet-scope',
        ]);

        $outletManager = Role::firstOrCreate(['name' => 'outlet_manager', 'guard_name' => 'web']);
        $outletManager->syncPermissions([
            'recruitment.manage', 'recruitment.view',
            'offer.view',
            'employee.view',
            'document.manage', 'document.view',
            'payroll.view',
        ]);
        // Deliberately NOT given bypass-outlet-scope — OutletScope applies to them.
    }
}
