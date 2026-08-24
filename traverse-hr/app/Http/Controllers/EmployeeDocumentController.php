<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Services\AuditLogger;
use App\Services\DocumentVariantResolver;
use App\Services\SignedDocumentGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

/**
 * The "classic session" signing flow: HR sits with the employee at
 * onboarding, opens each document in turn, the employee reads it and
 * types their name to accept. Per the owner's explicit simplification —
 * no signature pad, canvas, or tokenised remote link required for this
 * in-person path.
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

        return view('employee-documents.show', compact('employee', 'documentTemplate', 'variant', 'version', 'record', 'history'));
    }

    /** The employee types their name in front of HR; that's the acceptance. */
    public function accept(Request $request, Employee $employee, DocumentTemplate $documentTemplate)
    {
        abort_unless($request->user()->can('document.manage'), 403);

        $variant = DocumentVariantResolver::resolve($employee->jobRole, $documentTemplate);
        abort_unless($variant, 404);
        $version = $variant->currentVersion('en');
        abort_unless($version, 404);

        $rules = ['signer_typed_name' => ['required', 'string', 'max:255']];
        foreach ($version->field_schema ?? [] as $field) {
            $rules['fields.'.$field['name']] = $field['type'] === 'boolean' ? ['nullable'] : ['nullable', 'string', 'max:2000'];
        }
        $validated = $request->validate($rules);

        [$record, $supersededPrevious] = DB::transaction(function () use ($request, $employee, $documentTemplate, $version, $validated) {
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
            ]);

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
            'signed_pdf_sha256' => $record->signed_pdf_sha256,
            'superseded_previous' => $supersededPrevious,
        ]);

        return redirect()->route('employees.documents.index', $employee)
            ->with('status', "\"{$documentTemplate->name}\" accepted and recorded.");
    }
}
