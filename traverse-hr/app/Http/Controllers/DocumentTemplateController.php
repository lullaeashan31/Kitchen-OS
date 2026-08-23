<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentTemplateRequest;
use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateVariant;
use App\Models\DocumentTemplateVersion;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Admin → Document Types. This is where the owner uploads a document
 * ("I can upload multiple types, like one for managers, one for
 * housekeepers") as named variants of one logical document type.
 * Which variant a given job role actually gets is decided separately —
 * see JobRoleController::documents / JobRoleDocumentController.
 */
class DocumentTemplateController extends Controller
{
    public function index()
    {
        return view('document-templates.index', [
            'documentTemplates' => DocumentTemplate::with('variants.versions')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('document-templates.form', ['documentTemplate' => new DocumentTemplate]);
    }

    public function store(DocumentTemplateRequest $request)
    {
        $template = DocumentTemplate::create($request->validated() + ['created_by' => $request->user()->id]);

        // Every document type ships with a "Default" variant so it's
        // immediately usable; the owner renames/adds more as needed.
        $template->variants()->create(['label' => 'Default', 'is_default' => true]);

        return redirect()->route('document-templates.edit', $template)->with('status', 'Document type created. Upload its content below.');
    }

    public function edit(DocumentTemplate $documentTemplate)
    {
        $documentTemplate->load('variants.versions');

        return view('document-templates.form', ['documentTemplate' => $documentTemplate]);
    }

    public function update(DocumentTemplateRequest $request, DocumentTemplate $documentTemplate)
    {
        $documentTemplate->update($request->validated());

        return redirect()->route('document-templates.edit', $documentTemplate)->with('status', 'Saved.');
    }

    public function destroy(DocumentTemplate $documentTemplate)
    {
        $documentTemplate->update(['active' => false]);
        $documentTemplate->delete();

        return redirect()->route('document-templates.index')->with('status', 'Document type deactivated.');
    }

    /** Add a named variant, e.g. "Manager", "Housekeeping". */
    public function storeVariant(Request $request, DocumentTemplate $documentTemplate)
    {
        abort_unless($request->user()->can('document.manage'), 403);

        $request->validate([
            'label' => ['required', 'string', 'max:255', Rule::unique('document_template_variants', 'label')->where('document_template_id', $documentTemplate->id)],
        ]);

        $documentTemplate->variants()->create(['label' => $request->input('label'), 'is_default' => false]);

        return back()->with('status', 'Variant added.');
    }

    public function makeVariantDefault(Request $request, DocumentTemplateVariant $variant)
    {
        abort_unless($request->user()->can('document.manage'), 403);

        $variant->documentTemplate->variants()->update(['is_default' => false]);
        $variant->update(['is_default' => true]);

        return back()->with('status', "\"{$variant->label}\" is now the default variant.");
    }

    /**
     * Upload a new source file (PDF/DOCX) for a variant — this becomes a
     * new version, never overwrites the old one, so a document already
     * sent to an employee keeps pointing at the exact content they saw.
     */
    public function storeVersion(Request $request, DocumentTemplateVariant $variant)
    {
        abort_unless($request->user()->can('document.manage'), 403);

        $request->validate([
            'language' => ['required', Rule::in(['en', 'hi', 'mr'])],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:20480'],
        ]);

        $file = $request->file('file');
        // Restricted disk: storage/app/private, outside the public web root,
        // served only through DocumentTemplateVersionController@download (§6).
        $path = $file->store('document-template-versions', 'local');

        $nextVersion = 1 + (int) $variant->versions()->where('language', $request->input('language'))->max('version');

        $version = DocumentTemplateVersion::create([
            'document_template_variant_id' => $variant->id,
            'language' => $request->input('language'),
            'version' => $nextVersion,
            'source_file_path' => $path,
            'source_file_original_name' => $file->getClientOriginalName(),
            'active' => true,
            'uploaded_by' => $request->user()->id,
        ]);

        AuditLogger::log('document_template_version_uploaded', $version);

        return back()->with('status', 'New version uploaded.');
    }
}
