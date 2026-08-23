<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Services\AuditLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuditLoggerTest extends TestCase
{
    use RefreshDatabase;

    public function test_unmask_actions_always_record_a_reason(): void
    {
        $employee = Employee::factory()->create(['pan_encrypted' => 'ABCDE1234F']);

        AuditLogger::logUnmask($employee, 'pan', 'Confirming PAN before bank transfer setup');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'unmask',
            'auditable_type' => $employee->getMorphClass(),
            'auditable_id' => $employee->id,
            'reason' => 'Confirming PAN before bank transfer setup',
        ]);
    }

    public function test_statutory_identifiers_are_encrypted_at_rest(): void
    {
        $employee = Employee::factory()->create(['pan_encrypted' => 'ABCDE1234F']);

        $raw = DB::table('employees')->where('id', $employee->id)->value('pan_encrypted');

        $this->assertNotEquals('ABCDE1234F', $raw);
        $this->assertEquals('ABCDE1234F', $employee->fresh()->pan_encrypted);
    }
}
