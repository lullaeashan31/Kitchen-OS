<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * A *system user* of the Traverse HR software (Super Admin / HR-Manager /
 * Accounts / Outlet Manager). Staff members are never rows in this table —
 * they have no login in this phase and reach their documents only through
 * tokenised links (§3.6.2). See PERMISSION_MATRIX.md.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'outlet_id',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_enabled_at' => 'datetime',
            'last_login_at' => 'datetime',
            'locked_until' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    /** Whether this user is *obliged* to use 2FA (see config/security.php). */
    public function requiresTwoFactor(): bool
    {
        return config('security.require_two_factor', false)
            && $this->hasAnyRole(config('security.two_factor_roles', []));
    }

    public function twoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_enabled_at);
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }
}
