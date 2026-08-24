<?php

namespace Tests\Feature;

use App\Models\DocumentTemplate;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\JobRole;
use App\Models\Outlet;
use App\Models\User;
use App\Services\EmployeeLockerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SignedDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    private function admin(): User
    {
        $u = User::factory()->create(['two_factor_enabled_at' => now()]);
        $u->assignRole('super_admin');
        $this->withSession(['2fa_verified' => true]);

        return $u;
    }

    private function scenario(): array
    {
        $outlet = Outlet::factory()->create(['name' => 'Alinea']);
        $role = JobRole::factory()->create(['outlet_id' => $outlet->id]);
        $employee = Employee::factory()->create([
            'outlet_id' => $outlet->id, 'job_role_id' => $role->id, 'name' => 'Rajesh Kumar',
        ]);
        $template = DocumentTemplate::factory()->create(['kind' => 'acknowledge_only']);
        $variant = $template->variants()->create(['label' => 'Default', 'is_default' => true]);
        $variant->versions()->create([
            'language' => 'en', 'version' => 1, 'active' => true,
            'body_html' => '<p>Policy body.</p>',
        ]);

        return [$employee, $template];
    }

    public function test_accepting_produces_a_signed_pdf_containing_the_signature_and_timestamp(): void
    {
        [$employee, $template] = $this->scenario();

        $this->actingAs($this->admin())
            ->post("/employees/{$employee->id}/documents/{$template->id}/accept", $this->signingPayload('Rajesh Kumar'))->assertRedirect();

        $doc = EmployeeDocument::firstOrFail();

        $this->assertNotNull($doc->signed_pdf_path, 'A signed PDF must be produced on acceptance.');
        $this->assertTrue(Storage::disk('local')->exists($doc->signed_pdf_path));

        $bytes = Storage::disk('local')->get($doc->signed_pdf_path);
        $this->assertSame('%PDF', substr($bytes, 0, 4));
        $this->assertSame(hash('sha256', $bytes), $doc->signed_pdf_sha256, 'Stored hash must match the file.');
        $this->assertNotNull($doc->signing_place, 'Where the signing happened must be recorded.');
    }

    public function test_re_accepting_supersedes_rather_than_destroying_the_first_signature(): void
    {
        [$employee, $template] = $this->scenario();
        $admin = $this->admin();

        $this->actingAs($admin)->post("/employees/{$employee->id}/documents/{$template->id}/accept",
            $this->signingPayload('Rajesh Kumar'));
        $this->actingAs($admin)->post("/employees/{$employee->id}/documents/{$template->id}/accept",
            $this->signingPayload('Rajesh K Kumar'));

        $this->assertSame(2, EmployeeDocument::count(), 'The original acceptance must be retained.');

        $first = EmployeeDocument::orderBy('id')->first();
        $second = EmployeeDocument::orderByDesc('id')->first();

        $this->assertNotNull($first->superseded_at);
        $this->assertSame($second->id, $first->superseded_by_id);
        $this->assertNull($second->superseded_at);
        $this->assertNotSame($first->signed_pdf_path, $second->signed_pdf_path, 'Each signature keeps its own PDF.');
    }

    public function test_locker_serves_the_signed_pdf_not_the_blank_original(): void
    {
        [$employee, $template] = $this->scenario();
        $this->actingAs($this->admin())
            ->post("/employees/{$employee->id}/documents/{$template->id}/accept", $this->signingPayload('Rajesh Kumar'));

        $doc = EmployeeDocument::firstOrFail();
        $token = EmployeeLockerService::getOrCreate($employee);

        $response = $this->get("/d/{$token->token}/documents/{$doc->id}");
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_locker_refuses_to_serve_a_document_that_has_no_signed_pdf(): void
    {
        [$employee, $template] = $this->scenario();
        $this->actingAs($this->admin())
            ->post("/employees/{$employee->id}/documents/{$template->id}/accept", $this->signingPayload('Rajesh Kumar'));

        $doc = EmployeeDocument::firstOrFail();
        $doc->forceFill(['signed_pdf_path' => null])->save();

        $token = EmployeeLockerService::getOrCreate($employee);

        // Must never quietly hand back the unsigned source as if it were signed.
        $this->get("/d/{$token->token}/documents/{$doc->id}")->assertStatus(503);
    }

    public function test_backfill_generates_pdfs_for_pre_existing_acceptances(): void
    {
        [$employee, $template] = $this->scenario();
        $this->actingAs($this->admin())
            ->post("/employees/{$employee->id}/documents/{$template->id}/accept", $this->signingPayload('Rajesh Kumar'));

        EmployeeDocument::query()->update(['signed_pdf_path' => null, 'signed_pdf_sha256' => null]);

        Artisan::call('traverse:backfill-signed-documents');

        $this->assertNotNull(EmployeeDocument::firstOrFail()->signed_pdf_path);
    }

    public function test_soft_deleted_outlet_does_not_break_the_employee_list(): void
    {
        [$employee] = $this->scenario();
        $employee->outlet->delete();

        $this->actingAs($this->admin())->get('/employees')->assertOk()->assertSee($employee->name);
    }
}
