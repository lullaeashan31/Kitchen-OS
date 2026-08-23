<?php

namespace Database\Seeders;

use App\Models\RetentionPolicy;
use Illuminate\Database\Seeder;

/**
 * Defaults per the owner's answer recorded in DECISIONS.md. Editable later
 * from Admin settings — these are starting values, not fixed policy.
 */
class RetentionPolicySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['record_type' => 'payroll_record', 'retention_months' => 96],
            ['record_type' => 'employee_document_signed', 'retention_months' => 96],
            ['record_type' => 'applicant_rejected', 'retention_months' => 12],
            ['record_type' => 'employee_exit_locker', 'retention_months' => 12],
        ];

        foreach ($defaults as $policy) {
            RetentionPolicy::firstOrCreate(
                ['record_type' => $policy['record_type']],
                ['retention_months' => $policy['retention_months'], 'action' => 'flag_for_review']
            );
        }
    }
}
