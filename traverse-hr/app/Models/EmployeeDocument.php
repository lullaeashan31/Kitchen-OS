<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id', 'document_template_id', 'document_template_version_id',
        'language', 'status', 'field_values', 'signer_typed_name', 'signed_at',
        'signed_ip', 'signed_user_agent', 'recorded_by',
        'signed_pdf_path', 'signed_pdf_sha256', 'signing_outlet_id', 'signing_place',
        'superseded_at', 'superseded_by_id',
    ];

    protected $casts = [
        'field_values' => 'array',
        'signed_at' => 'datetime',
        'superseded_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function documentTemplate()
    {
        return $this->belongsTo(DocumentTemplate::class);
    }

    public function version()
    {
        return $this->belongsTo(DocumentTemplateVersion::class, 'document_template_version_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function signingOutlet()
    {
        return $this->belongsTo(Outlet::class, 'signing_outlet_id');
    }

    /** The signature that replaced this one, when a document was re-acknowledged. */
    public function supersededBy()
    {
        return $this->belongsTo(EmployeeDocument::class, 'superseded_by_id');
    }

    /** Current (non-superseded) signatures only — what the UI shows by default. */
    public function scopeCurrent($query)
    {
        return $query->whereNull('superseded_at');
    }

    public function downloadFilename(): string
    {
        return \Illuminate\Support\Str::slug(
            ($this->employee->name ?? 'employee').'-'.$this->documentTemplate->name.'-signed'
        ).'.pdf';
    }

    public function isSuperseded(): bool
    {
        return $this->superseded_at !== null;
    }
}
