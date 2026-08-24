<?php

namespace Tests\Feature;

use App\Models\CompanySignatory;
use App\Models\DocumentTemplate;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\JobRole;
use App\Models\Outlet;
use App\Models\User;
use App\Services\SignatureImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The signing session as the owner asked for it: the employee signs on a
 * pad with a stylus, that signature is replicated onto the file, and the
 * document is countersigned on Traverse Inc.'s behalf by whichever
 * signatory the Super Admin has configured.
 */
class DrawnSignatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
    }

    private function admin(): User
    {
        $u = User::factory()->create(['name' => 'Eashan Lulla']);
        $u->assignRole('super_admin');

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

    private function signatory(array $attrs = []): CompanySignatory
    {
        $path = 'company-signatures/'.uniqid().'.png';
        Storage::disk('local')->put($path, base64_decode(explode(',', $this->signatureCapture())[1]));

        return CompanySignatory::create(array_merge([
            'name' => 'Eashan Lulla',
            'designation' => 'Founder',
            'signature_image_path' => $path,
            'is_default' => true,
            'active' => true,
        ], $attrs));
    }

    public function test_the_drawn_signature_is_stored_and_marked_as_drawn(): void
    {
        [$employee, $template] = $this->scenario();
        $this->signatory();

        $this->actingAs($this->admin())
            ->post("/employees/{$employee->id}/documents/{$template->id}/accept",
                $this->signingPayload('Rajesh Kumar'))
            ->assertRedirect();

        $doc = EmployeeDocument::firstOrFail();

        $this->assertSame('drawn', $doc->signature_type);
        $this->assertNotNull($doc->signature_image_path, 'The pad capture must be kept as evidence.');
        $this->assertTrue(Storage::disk('local')->exists($doc->signature_image_path));
        $this->assertSame(3, $doc->strokeCount(), 'The pen strokes are recorded for the certificate.');
    }

    public function test_an_empty_pad_is_refused(): void
    {
        [$employee, $template] = $this->scenario();
        $this->signatory();

        $this->actingAs($this->admin())
            ->post("/employees/{$employee->id}/documents/{$template->id}/accept",
                $this->signingPayload('Rajesh Kumar', withInk: false))
            ->assertSessionHasErrors('signature_capture');

        $this->assertSame(0, EmployeeDocument::count(), 'Nothing may be recorded as signed from a blank pad.');
    }

    public function test_signing_with_no_signature_at_all_is_refused(): void
    {
        [$employee, $template] = $this->scenario();

        $this->actingAs($this->admin())
            ->post("/employees/{$employee->id}/documents/{$template->id}/accept",
                ['signer_typed_name' => 'Rajesh Kumar'])
            ->assertSessionHasErrors('signature_capture');

        $this->assertSame(0, EmployeeDocument::count());
    }

    public function test_the_default_signatory_countersigns_when_none_is_chosen(): void
    {
        [$employee, $template] = $this->scenario();
        $this->signatory(['name' => 'Eashan Lulla', 'designation' => 'Founder']);

        $this->actingAs($this->admin())
            ->post("/employees/{$employee->id}/documents/{$template->id}/accept",
                $this->signingPayload('Rajesh Kumar'));

        $doc = EmployeeDocument::firstOrFail();
        $this->assertSame('Eashan Lulla', $doc->company_signatory_name);
        $this->assertSame('Founder', $doc->company_signatory_designation);
        $this->assertNotNull($doc->company_signature_image_path);
    }

    public function test_a_named_signatory_can_countersign_instead_of_the_default(): void
    {
        [$employee, $template] = $this->scenario();
        $this->signatory();
        $gm = $this->signatory([
            'name' => 'Saumyaa Lulla', 'designation' => 'General Manager', 'is_default' => false,
        ]);

        $this->actingAs($this->admin())
            ->post("/employees/{$employee->id}/documents/{$template->id}/accept",
                $this->signingPayload('Rajesh Kumar') + ['company_signatory_id' => $gm->id]);

        $doc = EmployeeDocument::firstOrFail();
        $this->assertSame('Saumyaa Lulla', $doc->company_signatory_name);
        $this->assertSame('General Manager', $doc->company_signatory_designation);
    }

    /**
     * The countersignature is snapshotted onto the document. Renaming or
     * retiring a signatory afterwards must not rewrite history on documents
     * they already signed.
     */
    public function test_the_countersignature_is_snapshotted_not_looked_up_later(): void
    {
        [$employee, $template] = $this->scenario();
        $signatory = $this->signatory(['name' => 'Eashan Lulla', 'designation' => 'Founder']);

        $this->actingAs($this->admin())
            ->post("/employees/{$employee->id}/documents/{$template->id}/accept",
                $this->signingPayload('Rajesh Kumar'));

        $signatory->update(['name' => 'Someone Else', 'designation' => 'Director', 'active' => false]);

        $doc = EmployeeDocument::firstOrFail();
        $this->assertSame('Eashan Lulla', $doc->company_signatory_name);
        $this->assertSame('Founder', $doc->company_signatory_designation);
    }

    public function test_a_retired_signatory_cannot_be_used_to_countersign(): void
    {
        [$employee, $template] = $this->scenario();
        $retired = $this->signatory(['name' => 'Old Signatory', 'is_default' => false, 'active' => false]);
        $this->signatory();

        $this->actingAs($this->admin())
            ->post("/employees/{$employee->id}/documents/{$template->id}/accept",
                $this->signingPayload('Rajesh Kumar') + ['company_signatory_id' => $retired->id])
            ->assertSessionHasErrors('company_signatory_id');

        $this->assertSame(0, EmployeeDocument::count());
    }

    public function test_signing_works_before_any_signatory_has_been_configured(): void
    {
        [$employee, $template] = $this->scenario();

        $this->actingAs($this->admin())
            ->post("/employees/{$employee->id}/documents/{$template->id}/accept",
                $this->signingPayload('Rajesh Kumar'))
            ->assertRedirect();

        $doc = EmployeeDocument::firstOrFail();
        $this->assertNull($doc->company_signatory_name);
        $this->assertNotNull($doc->signed_pdf_path, 'A missing countersignature must not block the employee signing.');
    }

    public function test_only_a_super_admin_can_manage_signatories(): void
    {
        $hr = User::factory()->create();
        $hr->assignRole('hr_manager');

        $this->actingAs($hr)->get('/company-signatories')->assertForbidden();
        $this->actingAs($this->admin())->get('/company-signatories')->assertOk();
    }

    /** Both PDF renderers read this one label, so it must read correctly. */
    public function test_the_method_label_describes_the_signature_honestly(): void
    {
        $drawn = new EmployeeDocument(['signature_type' => 'drawn']);

        $drawn->signature_strokes = ['strokes' => [['points' => []]]];
        $this->assertSame(
            'Handwritten electronic signature captured in person (1 pen stroke recorded)',
            $drawn->signatureMethodLabel()
        );

        $drawn->signature_strokes = ['strokes' => [['points' => []], ['points' => []]]];
        $this->assertSame(
            'Handwritten electronic signature captured in person (2 pen strokes recorded)',
            $drawn->signatureMethodLabel()
        );

        // A record accepted under the old typed-name flow must never be
        // described as handwritten.
        $typed = new EmployeeDocument(['signature_type' => 'typed']);
        $this->assertSame('Typed-name electronic signature, in person', $typed->signatureMethodLabel());
    }

    public function test_a_blank_canvas_is_detected_as_having_no_ink(): void
    {
        $this->assertNull(SignatureImage::fromRequest($this->signatureCapture(withInk: false), null));
        $this->assertNotNull(SignatureImage::fromRequest($this->signatureCapture(), null));
    }
}
