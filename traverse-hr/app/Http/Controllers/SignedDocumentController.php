<?php

namespace App\Http\Controllers;

use App\Models\EmployeeDocument;
use App\Services\AuditLogger;
use App\Services\SignedDocumentGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SignedDocumentController extends Controller
{
    public function download(Request $request, EmployeeDocument $employeeDocument)
    {
        abort_unless($request->user()->can('document.view'), 403);
        abort_unless($employeeDocument->status === 'signed', 404);
        abort_unless(
            $employeeDocument->signed_pdf_path && Storage::disk('local')->exists($employeeDocument->signed_pdf_path),
            404,
            'The signed PDF for this document has not been generated. Use "Regenerate signed PDF" on the document page.'
        );

        AuditLogger::log('signed_document_downloaded', $employeeDocument, meta: [
            'employee_id' => $employeeDocument->employee_id,
        ]);

        return Storage::disk('local')->response(
            $employeeDocument->signed_pdf_path,
            $employeeDocument->downloadFilename(),
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Rebuilds the signed artefact from the pinned version + stored
     * signature data. Used for records signed before PDF generation
     * existed, and as a recovery path if generation ever failed.
     * It never alters the signature itself — only re-renders it.
     */
    public function regenerate(Request $request, EmployeeDocument $employeeDocument)
    {
        abort_unless($request->user()->can('document.manage'), 403);
        abort_unless($employeeDocument->status === 'signed', 404);

        SignedDocumentGenerator::generate($employeeDocument);

        AuditLogger::log('signed_document_regenerated', $employeeDocument, meta: [
            'employee_id' => $employeeDocument->employee_id,
            'signed_pdf_sha256' => $employeeDocument->fresh()->signed_pdf_sha256,
        ]);

        return back()->with('status', 'Signed PDF regenerated.');
    }
}
