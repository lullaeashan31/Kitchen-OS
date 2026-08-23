<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeDocumentLockerToken;
use Illuminate\Support\Str;

/**
 * One standing token per employee — "scan the QR, see everything you've
 * signed." Get-or-create is idempotent so re-visiting the admin page
 * never silently invalidates a link already sent on WhatsApp; use
 * regenerate() to deliberately rotate it (old link stops working).
 */
class EmployeeLockerService
{
    public static function getOrCreate(Employee $employee, ?int $createdBy = null): EmployeeDocumentLockerToken
    {
        $active = EmployeeDocumentLockerToken::where('employee_id', $employee->id)
            ->whereNull('revoked_at')
            ->latest()
            ->first();

        if ($active && $active->isValid()) {
            return $active;
        }

        return EmployeeDocumentLockerToken::create([
            'employee_id' => $employee->id,
            'token' => self::generateToken(),
            'created_by' => $createdBy,
        ]);
    }

    public static function regenerate(Employee $employee, ?int $createdBy = null): EmployeeDocumentLockerToken
    {
        EmployeeDocumentLockerToken::where('employee_id', $employee->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);

        return EmployeeDocumentLockerToken::create([
            'employee_id' => $employee->id,
            'token' => self::generateToken(),
            'created_by' => $createdBy,
        ]);
    }

    private static function generateToken(): string
    {
        // 40 random bytes, base62-ish via Str::random -> well over the
        // 32-byte minimum from §6, URL-safe, not a guessable ID.
        return Str::random(48);
    }
}
