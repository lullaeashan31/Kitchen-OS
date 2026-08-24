<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocumentLockerToken extends Model
{
    protected $fillable = ['employee_id', 'token', 'expires_at', 'revoked_at', 'created_by'];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class)->withoutGlobalScopes();
    }

    public function isValid(): bool
    {
        if ($this->revoked_at) {
            return false;
        }

        return ! $this->expires_at || $this->expires_at->isFuture();
    }
}
