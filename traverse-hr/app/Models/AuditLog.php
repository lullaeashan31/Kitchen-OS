<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Append-only. No update/delete route is ever built against this model —
 * do not add one. Retained even after the record it audits is deleted
 * (auditable_id/type are plain columns, not an enforced FK).
 */
class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'action', 'auditable_type', 'auditable_id',
        'ip_address', 'user_agent', 'reason', 'meta', 'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (AuditLog $log) {
            $log->created_at ??= now();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auditable()
    {
        return $this->morphTo();
    }
}
