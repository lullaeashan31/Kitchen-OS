<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Models\Employee;
use App\Models\CompanySignatory;
use App\Models\EmployeeDocument;
use App\Services\AuditLogger;
use App\Services\DocumentVariantResolver;
use App\Services\SignatureImage;
use App\Services\SignedDocumentGenerator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The in-person signing session: HR sits with the employee at onboarding,
 * opens each document in turn, the employee reads it and signs on a
 * signature pad (any USB pad, stylus, touchscreen or mouse — the capture
 * is plain Pointer Events, no vendor SDK). The drawn signature is stamped
 * onto every page of the resulting PDF alongside the countersignature of
 * whoever signs for Traverse Inc. No tokenised remote link is involved.
 */
class EmployeeDocumentController extends Controller
{
    /** All active document types resolved to this employee's job role, with sign status. */
    public function index(Request $request, Employee $employee)
    {
        abort_unless($request->user()->can('document.manage'), 403);

        $templates = DocumentTemplate::with('variants.versions')
            ->where('active', true)
            ->orderBy('category')->orderBy('name')
            ->get();

        $existing = EmployeeDocument::where('employee_id', $employee->id)
            ->current()
            ->get()
            ->keyBy('document_template_id');

        $rows = $templates->map(function (DocumentTemplate $template) use ($employee, $existing) {
            $variant = DocumentVariantResolver::resolve($employee->jobRole, $template);
            $version = $variant?->currentVersion('en');

            return [
                'template' => $template,
                'variant' => $variant,
                'version' => $version,
                'record' => $existing->get($template->id),
            ];
        })->filter(fn ($row) => $row['version'] !== null)->values();

        return view('employee-documents.index', compact('employee', 'rows'));
    }

    public function show(Request $request, Employee $employee, DocumentTemplate $documentTemplate)
    {
        abort_unless($request->user()->can('document.manage'), 403);

        $variant = DocumentVariantResolver::resolve($employee->jobRole, $documentTemplate);
        abort_unless($variant, 404);
        $version = $variant->currentVersion('en');
        abort_unless($version, 404, 'No content has been uploaded or written for this document yet.');

        $record = EmployeeDocument::where('employee_id', $employee->id)
            ->where('document_template_id', $documentTemplate->id)
            ->current()
            ->latest('id')
            ->first();

        $history = EmployeeDocument::where('employee_id', $employee->id)
            ->where('document_template_id', $documentTemplate->id)
            ->whereNotNull('superseded_at')
            ->latest('signed_at')
            ->get();

        AuditLogger::log('document_viewed', $version, meta: ['employee_id' => $employee->id]);

        $signatories = CompanySignatory::active()->orderByDesc('is_default')->orderBy('name')->get();

        return view('employee-documents.show', compact(
            'employee', 'documentTemplate', 'variant', 'version', 'record', 'history', 'signatories'
        ));
    }

    /**
     * The in-person signing session: the employee signs on the pad in front
     * of HR, and the document is countersigned for Traverse Inc. by the
     * chosen signatory. The drawn signature is the acceptance.
     */
    public function accept(Request $request, Employee $employee, DocumentTemplate $documentTemplate)
    {
        abort_unless($request->user()->can('document.manage'), 403);

        $variant = DocumentVariantResolver::resolve($employee->jobRole, $documentTemplate);
        abort_unless($variant, 404);
        $version = $variant->currentVersion('en');
        abort_unless($version, 404);

        $rules = [
            'signer_typed_name' => ['required', 'string', 'max:255'],
            'signature_capture' => ['required', 'string'],
            'signature_strokes' => ['nullable', 'string'],
            // Only a signatory who is still active may countersign — a
            // retired signatory's image must not reappear on new documents.
            'company_signatory_id' => ['nullable', Rule::exists('company_signatories', 'id')->where('active', true)],
        ];
        foreach ($version->field_schema ?? [] as $field) {
            $rules['fields.'.$field['name']] = $field['type'] === 'boolean' ? ['nullable'] : ['nullable', 'string', 'max:2000'];
        }
        $validated = $request->validate($rules, [
            'signature_capture.required' => 'The employee must sign in the signature box before this can be recorded.',
        ]);

        // The drawn signature is the acceptance — refuse an empty pad
        // rather than record a document as signed with nothing on it.
        $signatureBinary = SignatureImage::fromRequest($validated['signature_capture'], null);
        if ($signatureBinary === null) {
            return back()->withInput()->withErrors([
                'signature_capture' => 'That signature looked empty. Please sign in the box and try again.',
            ]);
        }

        $signatory = $request->filled('company_signatory_id')
            ? CompanySignatory::active()->find($request->input('company_signatory_id'))
            : CompanySignatory::default();

        [$record, $supersededPrevious] = DB::transaction(function () use ($request, $employee, $documentTemplate, $version, $validated, $signatureBinary, $signatory) {
            // Re-acknowledgement (e.g. the HR policy was updated) must never
            // erase what the employee previously agreed to. The prior
            // signature is retained and marked superseded instead.
            $previous = EmployeeDocument::where('employee_id', $employee->id)
                ->where('document_template_id', $documentTemplate->id)
                ->current()
                ->latest('id')
                ->first();

            $record = EmployeeDocument::create([
                'employee_id' => $employee->id,
                'document_template_id' => $documentTemplate->id,
                'document_template_version_id' => $version->id,
                'language' => 'en',
                'status' => 'signed',
                'field_values' => $request->input('fields'),
                'signer_typed_name' => $validated['signer_typed_name'],
                'signed_at' => now(),
                'signed_ip' => $request->ip(),
                'signed_user_agent' => substr((string) $request->userAgent(), 0, 255),
                'recorded_by' => $request->user()->id,
                'signing_outlet_id' => $employee->outlet_id,
                'signing_place' => $employee->outlet?->name,
                'signature_type' => 'drawn',
                'signature_strokes' => json_decode($request->input('signature_strokes') ?: 'null', true),
                // Snapshot the countersignatory: renaming them later must
                // never rewrite what an already-signed document says.
                'company_signatory_id' => $signatory?->id,
                'company_signatory_name' => $signatory?->name,
                'company_signatory_designation' => $signatory?->designation,
                'company_signature_image_path' => $signatory?->signature_image_path,
            ]);

            $signaturePath = 'employee-signatures/'.$employee->id.'/'.$record->id.'-'.\Illuminate\Support\Str::random(10).'.png';
            Storage::disk('local')->put($signaturePath, $signatureBinary);
            $record->forceFill(['signature_image_path' => $signaturePath])->save();

            $previous?->forceFill([
                'superseded_at' => now(),
                'superseded_by_id' => $record->id,
            ])->save();

            return [$record, $previous !== null];
        });

        // The signed artefact itself — the original document with the
        // signature stamped on every page and a certificate appended.
        try {
            SignedDocumentGenerator::generate($record);
        } catch (\Throwable $e) {
            // The acceptance is already recorded and legally captured; a
            // rendering failure must not lose it. Surface it loudly instead.
            report($e);
            AuditLogger::log('document_pdf_generation_failed', $record, meta: ['error' => $e->getMessage()]);

            return redirect()->route('employees.documents.index', $employee)
                ->with('status', "\"{$documentTemplate->name}\" was accepted and recorded, but the signed PDF could not be generated. The acceptance is safe — contact support before relying on the download.");
        }

        AuditLogger::log('document_signed', $record, meta: [
            'employee_id' => $employee->id,
            'document_template_id' => $documentTemplate->id,
            'document_template_version_id' => $version->id,
            'signer_typed_name' => $validated['signer_typed_name'],
            'signature_type' => 'drawn',
            'company_signatory' => $signatory?->name,
            'signed_pdf_sha256' => $record->signed_pdf_sha256,
            'superseded_previous' => $supersededPrevious,
        ]);

        return redirect()->route('employees.documents.index', $employee)
            ->with('status', "\"{$documentTemplate->name}\" accepted and recorded.");
    }
}
