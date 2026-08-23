<?php

namespace Tests\Feature;

use App\Models\DocumentTemplate;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\JobRole;
use App\Models\User;
use App\Services\EmployeeLockerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class EmployeeLockerTest extends TestCase
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

    private function signedDocumentFor(Employee $employee): EmployeeDocument
    {
        $template = DocumentTemplate::factory()->create(['kind' => 'acknowledge_only']);
        $variant = $template->variants()->create(['label' => 'Default', 'is_default' => true]);
        $version = $variant->versions()->create(['language' => 'en', 'version' => 1, 'body_html' => '<p>Policy text.</p>', 'active' => true]);

        return EmployeeDocument::create([
            'employee_id' => $employee->id,
            'document_template_id' => $template->id,
            'document_template_version_id' => $version->id,
            'language' => 'en',
            'status' => 'signed',
            'signer_typed_name' => $employee->name,
            'signed_at' => now(),
        ]);
    }

    public function test_scanning_the_link_shows_only_signed_documents_for_that_employee(): void
    {
        $role = JobRole::factory()->create();
        $employee = Employee::factory()->create(['job_role_id' => $role->id]);
        $doc = $this->signedDocumentFor($employee);

        $token = EmployeeLockerService::getOrCreate($employee);

        $response = $this->get("/d/{$token->token}");

        $response->assertOk();
        $response->assertSee($doc->documentTemplate->name);
    }

    public function test_an_unknown_token_returns_404(): void
    {
        $this->get('/d/does-not-exist')->assertNotFound();
    }

    public function test_regenerating_invalidates_the_old_link(): void
    {
        $role = JobRole::factory()->create();
        $employee = Employee::factory()->create(['job_role_id' => $role->id]);
        $old = EmployeeLockerService::getOrCreate($employee);

        $new = EmployeeLockerService::regenerate($employee);

        $this->get("/d/{$old->token}")->assertNotFound();
        $this->get("/d/{$new->token}")->assertOk();
    }

    public function test_a_token_cannot_be_used_to_read_another_employees_document(): void
    {
        $role = JobRole::factory()->create();
        $employeeA = Employee::factory()->create(['job_role_id' => $role->id]);
        $employeeB = Employee::factory()->create(['job_role_id' => $role->id]);

        $docB = $this->signedDocumentFor($employeeB);
        $tokenA = EmployeeLockerService::getOrCreate($employeeA);

        $this->get("/d/{$tokenA->token}/documents/{$docB->id}")->assertNotFound();
    }

    public function test_admin_can_view_the_locker_page_and_get_a_qr_code(): void
    {
        $admin = $this->admin();
        $role = JobRole::factory()->create();
        $employee = Employee::factory()->create(['job_role_id' => $role->id]);

        $response = $this->actingAs($admin)->get("/employees/{$employee->id}/locker");

        $response->assertOk();
        $response->assertSee('<svg', false);
    }
}
