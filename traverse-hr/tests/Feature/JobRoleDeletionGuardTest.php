<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\JobRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class JobRoleDeletionGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_job_role_with_employees_attached_cannot_be_deleted(): void
    {
        $role = JobRole::factory()->create();
        Employee::factory()->create(['job_role_id' => $role->id]);

        $this->expectException(RuntimeException::class);

        $role->delete();
    }

    public function test_a_job_role_with_no_employees_can_be_soft_deleted(): void
    {
        $role = JobRole::factory()->create();

        $role->delete();

        $this->assertSoftDeleted($role);
    }
}
