<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplate;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Services\AuditLogger;
use App\Services\DocumentVariantResolver;
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
            ->first();

        AuditLogger::log('document_viewed', $version, meta: ['employee_id' => $employee->id]);

        return view('employee-documents.show', compact('employee', 'documentTemplate', 'variant', 'version', 'record'));
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

        $record = EmployeeDocument::updateOrCreate(
            ['employee_id' => $employee->id, 'document_template_id' => $documentTemplate->id],
            [
                'document_template_version_id' => $version->id,
                'language' => 'en',
                'status' => 'signed',
                'field_values' => $request->input('fields'),
                'signer_typed_name' => $validated['signer_typed_name'],
                'signed_at' => now(),
                'signed_ip' => $request->ip(),
                'signed_user_agent' => substr((string) $request->userAgent(), 0, 255),
                'recorded_by' => $request->user()->id,
            ]
        );

        AuditLogger::log('document_signed', $record, meta: [
            'employee_id' => $employee->id,
            'document_template_id' => $documentTemplate->id,
            'document_template_version_id' => $version->id,
            'signer_typed_name' => $validated['signer_typed_name'],
        ]);

        return redirect()->route('employees.documents.index', $employee)
            ->with('status', "\"{$documentTemplate->name}\" accepted and recorded.");
    }
}
