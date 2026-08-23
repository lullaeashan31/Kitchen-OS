<?php

namespace Tests\Feature;

use App\Models\DocumentTemplate;
use App\Models\JobRole;
use App\Models\JobRoleDocumentVariant;
use App\Models\User;
use App\Services\DocumentVariantResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTemplateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Artisan::call('db:seed', ['--class' => 'RolesAndPermissionsSeeder']);
        Storage::fake('local');
    }

    private function admin(): User
    {
        $user = User::factory()->create(['two_factor_enabled_at' => now()]);
        $user->assignRole('super_admin');
        $this->withSession(['2fa_verified' => true]);

        return $user;
    }

    public function test_creating_a_document_type_gets_a_default_variant(): void
    {
        $response = $this->actingAs($this->admin())->post('/document-templates', [
            'name' => 'POSH Policy',
            'kind' => 'acknowledge_only',
            'active' => 1,
        ]);

        $template = DocumentTemplate::where('name', 'POSH Policy')->firstOrFail();
        $response->assertRedirect(route('document-templates.edit', $template));
        $this->assertCount(1, $template->variants);
        $this->assertTrue($template->variants->first()->is_default);
        $this->assertEquals('Default', $template->variants->first()->label);
    }

    public function test_uploading_a_new_file_creates_a_new_version_not_an_overwrite(): void
    {
        $admin = $this->admin();
        $template = DocumentTemplate::factory()->create();
        $variant = $template->variants()->create(['label' => 'Default', 'is_default' => true]);

        $this->actingAs($admin)->post("/document-template-variants/{$variant->id}/versions", [
            'language' => 'en',
            'file' => UploadedFile::fake()->create('policy.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $this->actingAs($admin)->post("/document-template-variants/{$variant->id}/versions", [
            'language' => 'en',
            'file' => UploadedFile::fake()->create('policy-v2.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $versions = $variant->versions()->where('language', 'en')->orderBy('version')->get();
        $this->assertCount(2, $versions);
        $this->assertEquals(1, $versions[0]->version);
        $this->assertEquals(2, $versions[1]->version);
    }

    public function test_editing_text_content_creates_a_new_version_and_keeps_the_old_one(): void
    {
        $admin = $this->admin();
        $template = DocumentTemplate::factory()->create();
        $variant = $template->variants()->create(['label' => 'Default', 'is_default' => true]);

        $this->actingAs($admin)->post("/document-template-variants/{$variant->id}/text-versions", [
            'language' => 'en',
            'body_html' => '<p>First draft of the policy.</p>',
        ])->assertRedirect();

        $this->actingAs($admin)->post("/document-template-variants/{$variant->id}/text-versions", [
            'language' => 'en',
            'body_html' => '<p>Updated policy text.</p>',
        ])->assertRedirect();

        $versions = $variant->versions()->where('language', 'en')->orderBy('version')->get();
        $this->assertCount(2, $versions);
        $this->assertEquals('<p>First draft of the policy.</p>', $versions[0]->body_html);
        $this->assertEquals('<p>Updated policy text.</p>', $versions[1]->body_html);
        $this->assertNull($versions[1]->source_file_path);
    }

    public function test_job_role_gets_default_variant_when_no_override_is_set(): void
    {
        $template = DocumentTemplate::factory()->create();
        $default = $template->variants()->create(['label' => 'Default', 'is_default' => true]);
        $manager = $template->variants()->create(['label' => 'Manager', 'is_default' => false]);

        $role = JobRole::factory()->create();

        $resolved = DocumentVariantResolver::resolve($role, $template);

        $this->assertEquals($default->id, $resolved->id);
    }

    public function test_the_dropdown_override_wins_over_the_default_variant(): void
    {
        $template = DocumentTemplate::factory()->create();
        $template->variants()->create(['label' => 'Default', 'is_default' => true]);
        $manager = $template->variants()->create(['label' => 'Manager', 'is_default' => false]);

        $role = JobRole::factory()->create();

        JobRoleDocumentVariant::create([
            'job_role_id' => $role->id,
            'document_template_id' => $template->id,
            'document_template_variant_id' => $manager->id,
        ]);

        $resolved = DocumentVariantResolver::resolve($role, $template);

        $this->assertEquals($manager->id, $resolved->id);
    }

    public function test_the_job_role_documents_screen_saves_selections(): void
    {
        $admin = $this->admin();
        $template = DocumentTemplate::factory()->create();
        $template->variants()->create(['label' => 'Default', 'is_default' => true]);
        $manager = $template->variants()->create(['label' => 'Manager', 'is_default' => false]);
        $role = JobRole::factory()->create();

        $this->actingAs($admin)->put("/job-roles/{$role->id}/documents", [
            'variant' => [$template->id => $manager->id],
        ])->assertRedirect();

        $this->assertDatabaseHas('job_role_document_variant_map', [
            'job_role_id' => $role->id,
            'document_template_id' => $template->id,
            'document_template_variant_id' => $manager->id,
        ]);
    }
}
