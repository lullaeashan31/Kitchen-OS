<?php

namespace App\Models;

use App\Models\Scopes\OutletScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Statutory identifiers (pan/uan/esic/bank/ifsc) use Laravel's `encrypted`
 * cast — encrypted at rest automatically. They are intentionally NOT in
 * $fillable under their real names to make accidental mass-assignment from
 * a request harder; write them through dedicated form-request-validated
 * actions instead. Masking for list views is a presentation concern
 * (see resources/views + the accessor helpers below), not something this
 * model enforces on its own — the controller/policy layer decides who may
 * call unmasked().
 */
class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_code', 'applicant_id', 'name', 'photo_path', 'phone',
        'emergency_contact_name', 'emergency_contact_phone', 'address', 'dob',
        'date_of_joining', 'job_role_id', 'designation', 'department_id', 'outlet_id',
        'reporting_manager_id', 'employment_type', 'probation_end_date',
        'confirmation_date', 'status', 'exit_date', 'exit_reason',
        'aadhaar_last_four', 'aadhaar_verified_at', 'aadhaar_verified_by',
        'aadhaar_scan_document_id',
    ];

    protected $casts = [
        'dob' => 'date',
        'date_of_joining' => 'date',
        'probation_end_date' => 'date',
        'confirmation_date' => 'date',
        'exit_date' => 'date',
        'aadhaar_verified_at' => 'datetime',
        'pan_encrypted' => 'encrypted',
        'uan_encrypted' => 'encrypted',
        'esic_number_encrypted' => 'encrypted',
        'bank_account_encrypted' => 'encrypted',
        'ifsc_encrypted' => 'encrypted',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new OutletScope);
    }

    /**
     * withTrashed: deactivating an outlet or job role must never break the
     * employee list or any historical record that points at it. Losing the
     * name of the outlet someone worked at is a data-integrity failure, not
     * a tidy-up.
     */
    public function outlet()
    {
        return $this->belongsTo(Outlet::class)->withTrashed();
    }

    public function jobRole()
    {
        return $this->belongsTo(JobRole::class)->withTrashed();
    }

    public function department()
    {
        return $this->belongsTo(Department::class)->withTrashed();
    }

    public function reportingManager()
    {
        return $this->belongsTo(Employee::class, 'reporting_manager_id');
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function aadhaarVerifiedBy()
    {
        return $this->belongsTo(User::class, 'aadhaar_verified_by');
    }

    /** Masked PAN for list views, e.g. "ABCDE****F". Never logged by this call. */
    public function maskedPan(): ?string
    {
        return static::maskMiddle($this->pan_encrypted);
    }

    public function maskedBankAccount(): ?string
    {
        $v = $this->bank_account_encrypted;

        return $v ? str_repeat('*', max(strlen($v) - 4, 0)).substr($v, -4) : null;
    }

    protected static function maskMiddle(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        $len = strlen($value);
        if ($len <= 2) {
            return str_repeat('*', $len);
        }

        return $value[0].str_repeat('*', $len - 2).$value[$len - 1];
    }
}
