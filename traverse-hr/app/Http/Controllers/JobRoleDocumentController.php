<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Models\JobRole;
use App\Models\JobRoleDocumentVariant;
use Illuminate\Http\Request;

/**
 * The screen the owner asked for: "when I am putting in a role, I can
 * just, with a dropdown, choose which document policy has to be given to
 * that person." One dropdown per active document type; leaving it on
 * "Default" means no override row is stored and the type's default
 * variant is used (see DocumentVariantResolver).
 */
class JobRoleDocumentController extends Controller
{
    public function edit(Request $request, JobRole $jobRole)
    {
        abort_unless($request->user()->can('admin.settings.manage'), 403);

        $documentTemplates = DocumentTemplate::with('variants')->where('active', true)->orderBy('category')->orderBy('name')->get();

        $current = JobRoleDocumentVariant::where('job_role_id', $jobRole->id)
            ->pluck('document_template_variant_id', 'document_template_id');

        return view('job-roles.documents', compact('jobRole', 'documentTemplates', 'current'));
    }

    public function update(Request $request, JobRole $jobRole)
    {
        abort_unless($request->user()->can('admin.settings.manage'), 403);

        $selections = $request->input('variant', []); // [document_template_id => document_template_variant_id|'']

        foreach ($selections as $documentTemplateId => $variantId) {
            if ($variantId === '' || $variantId === null) {
                JobRoleDocumentVariant::where('job_role_id', $jobRole->id)
                    ->where('document_template_id', $documentTemplateId)
                    ->delete();

                continue;
            }

            JobRoleDocumentVariant::updateOrCreate(
                ['job_role_id' => $jobRole->id, 'document_template_id' => $documentTemplateId],
                ['document_template_variant_id' => $variantId]
            );
        }

        return redirect()->route('job-roles.documents.edit', $jobRole)->with('status', 'Document assignments saved.');
    }
}
