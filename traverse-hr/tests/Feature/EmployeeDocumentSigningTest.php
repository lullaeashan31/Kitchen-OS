<?php

namespace Tests\Feature;

use App\Models\DocumentTemplate;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\JobRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class EmployeeDocumentSigningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    private function admin(): User
    {
        $user = User::factory()->create(['two_factor_enabled_at' => now()]);
        $user->assignRole('super_admin');
        $this->withSession(['2fa_verified' => true]);

        return $user;
    }

    public function test_typing_a_name_records_acceptance_without_any_signature_pad(): void
    {
        $admin = $this->admin();
        $role = JobRole::factory()->create();
        $employee = Employee::factory()->create(['job_role_id' => $role->id, 'name' => 'Priya Shah']);

        $template = DocumentTemplate::factory()->create(['kind' => 'acknowledge_only', 'active' => true]);
        $variant = $template->variants()->create(['label' => 'Default', 'is_default' => true]);
        $variant->versions()->create([
            'language' => 'en',
            'version' => 1,
            'body_html' => '<p>Please read this policy.</p>',
            'active' => true,
        ]);

        $response = $this->actingAs($admin)->post(
            "/employees/{$employee->id}/documents/{$template->id}/accept",
            ['signer_typed_name' => 'Priya Shah']
        );

        $response->assertRedirect(route('employees.documents.index', $employee));

        $this->assertDatabaseHas('employee_documents', [
            'employee_id' => $employee->id,
            'document_template_id' => $template->id,
            'status' => 'signed',
            'signer_typed_name' => 'Priya Shah',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'document_signed',
        ]);
    }

    /**
     * Superseded by design: this originally asserted that re-accepting
     * OVERWROTE the first signature. That was a defect — an updated policy
     * would silently erase what the employee previously agreed to. The
     * prior acceptance is now retained and marked superseded.
     */
    public function test_accepting_twice_retains_the_first_signature_as_superseded(): void
    {
        $admin = $this->admin();
        $role = JobRole::factory()->create();
        $employee = Employee::factory()->create(['job_role_id' => $role->id]);

        $template = DocumentTemplate::factory()->create(['kind' => 'acknowledge_only']);
        $variant = $template->variants()->create(['label' => 'Default', 'is_default' => true]);
        $variant->versions()->create(['language' => 'en', 'version' => 1, 'body_html' => '<p>Policy</p>', 'active' => true]);

        $this->actingAs($admin)->post("/employees/{$employee->id}/documents/{$template->id}/accept", ['signer_typed_name' => 'First Try']);
        $this->actingAs($admin)->post("/employees/{$employee->id}/documents/{$template->id}/accept", ['signer_typed_name' => 'Corrected Name']);

        $this->assertDatabaseCount('employee_documents', 2);

        $first = EmployeeDocument::orderBy('id')->first();
        $second = EmployeeDocument::orderByDesc('id')->first();

        $this->assertSame('First Try', $first->signer_typed_name);
        $this->assertNotNull($first->superseded_at, 'The earlier acceptance must be kept, not deleted.');
        $this->assertSame('Corrected Name', $second->signer_typed_name);
        $this->assertNull($second->superseded_at);
    }
}
