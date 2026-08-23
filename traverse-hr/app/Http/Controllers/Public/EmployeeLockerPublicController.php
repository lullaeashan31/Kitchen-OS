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

        $documents = EmployeeDocument::with('documentTemplate', 'version')
            ->where('employee_id', $lockerToken->employee_id)
            ->where('status', 'signed')
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

        // The token only ever unlocks documents actually signed by ITS
        // employee — never any other employee's, never a pending one.
        abort_unless(
            $employeeDocument->employee_id === $lockerToken->employee_id
                && $employeeDocument->status === 'signed',
            404
        );

        $version = $employeeDocument->version;

        AuditLogger::log('locker_document_viewed', $employeeDocument, meta: ['employee_id' => $lockerToken->employee_id]);

        if ($version->source_file_path) {
            abort_unless(Storage::disk('local')->exists($version->source_file_path), 404);

            return Storage::disk('local')->response(
                $version->source_file_path,
                $version->source_file_original_name ?? 'document.pdf'
            );
        }

        return view('public.locker.document', [
            'employeeDocument' => $employeeDocument,
            'version' => $version,
            'token' => $token,
        ]);
    }

    private function resolveToken(string $token): EmployeeDocumentLockerToken
    {
        $lockerToken = EmployeeDocumentLockerToken::where('token', $token)->first();

        abort_if(! $lockerToken || ! $lockerToken->isValid(), 404);

        return $lockerToken;
    }
}
