<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Single entry point for writing to the append-only audit_logs table.
 * Every view of an unmasked statutory identifier, every document
 * view/field-entry/signature/download, and every pipeline stage change
 * goes through here so "whose data was exposed, and when" stays a single
 * query (§9 breach-readiness requirement).
 */
class AuditLogger
{
    public static function log(string $action, ?Model $auditable = null, ?string $reason = null, array $meta = [], ?int $actorId = null): AuditLog
    {
        return AuditLog::create([
            'user_id' => $actorId ?? Auth::id(),
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'ip_address' => Request::ip(),
            'user_agent' => substr((string) Request::userAgent(), 0, 255),
            'reason' => $reason,
            'meta' => $meta,
        ]);
    }

    /**
     * Convenience wrapper for the mandatory-reason unmask actions
     * (§3.3, §6): viewing a statutory identifier in full requires a reason
     * on every single call, no exceptions.
     */
    public static function logUnmask(Model $auditable, string $field, string $reason): AuditLog
    {
        return static::log('unmask', $auditable, $reason, ['field' => $field]);
    }
}
