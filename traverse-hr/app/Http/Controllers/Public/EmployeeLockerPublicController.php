<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDocument;
use App\Models\EmployeeDocumentLockerToken;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Unauthenticated, token-gated. This is what a staff member reaches by
 * scanning their QR code or opening their link on WhatsApp — every
 * document THEY signed, permanently readable, per §3.4. No login exists
 * for staff in this phase (§3.6.2), so this route is the only access path.
 */
class EmployeeLockerPublicController extends Controller
{
    public function show(Request $request, string $token)
    {
        $lockerToken = $this->resolveToken($token);

        // Superseded signatures stay visible to the employee — they should
        // be able to see what they previously agreed to, not just the latest.
        $documents = EmployeeDocument::with('documentTemplate', 'version')
            ->where('employee_id', $lockerToken->employee_id)
            ->where('status', 'signed')
            ->orderByDesc('signed_at')
            ->get();

        AuditLogger::log('locker_viewed', $lockerToken, meta: ['employee_id' => $lockerToken->employee_id]);

        return view('public.locker.show', [
            'employee' => $lockerToken->employee,
            'documents' => $documents,
            'token' => $token,
        ]);
    }

    public function document(Request $request, string $token, EmployeeDocument $employeeDocument)
    {
        $lockerToken = $this->resolveToken($token);
        $this->assertBelongsTo($employeeDocument, $lockerToken);

        AuditLogger::log('locker_document_viewed', $employeeDocument, meta: ['employee_id' => $lockerToken->employee_id]);

        // Always serve the SIGNED artefact — the original with the signature
        // stamped on every page and the certificate appended. Serving the
        // blank source file here was the original defect.
        if ($employeeDocument->signed_pdf_path && Storage::disk('local')->exists($employeeDocument->signed_pdf_path)) {
            return Storage::disk('local')->response(
                $employeeDocument->signed_pdf_path,
                $employeeDocument->downloadFilename(),
                ['Content-Type' => 'application/pdf']
            );
        }

        // Signed before signed-PDF generation existed, or generation failed.
        // Never quietly hand back an unsigned file as if it were signed.
        return response()->view('public.locker.unavailable', [
            'employeeDocument' => $employeeDocument,
            'token' => $token,
        ], 503);
    }

    /** Public integrity check — anyone holding the document can verify it. */
    public function verify(Request $request, string $token, EmployeeDocument $employeeDocument)
    {
        $lockerToken = $this->resolveToken($token);
        $this->assertBelongsTo($employeeDocument, $lockerToken);

        return view('public.locker.verify', [
            'employeeDocument' => $employeeDocument,
            'token' => $token,
        ]);
    }

    private function assertBelongsTo(EmployeeDocument $doc, EmployeeDocumentLockerToken $lockerToken): void
    {
        // The token only ever unlocks documents actually signed by ITS
        // employee — never any other employee's, never a pending one.
        abort_unless(
            $doc->employee_id === $lockerToken->employee_id && $doc->status === 'signed',
            404
        );
    }

    private function resolveToken(string $token): EmployeeDocumentLockerToken
    {
        $lockerToken = EmployeeDocumentLockerToken::with('employee')->where('token', $token)->first();

        abort_if(! $lockerToken || ! $lockerToken->isValid(), 404);

        // An employee record removed from the system must not 500 the page
        // the staff member has bookmarked.
        abort_if($lockerToken->employee === null, 410);

        return $lockerToken;
    }
}
