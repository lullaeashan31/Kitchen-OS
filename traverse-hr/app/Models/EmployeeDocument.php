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
        'signature_type', 'signature_image_path', 'signature_strokes',
        'company_signatory_id', 'company_signatory_name',
        'company_signatory_designation', 'company_signature_image_path',
    ];

    protected $casts = [
        'field_values' => 'array',
        'signed_at' => 'datetime',
        'superseded_at' => 'datetime',
        'signature_strokes' => 'array',
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

    public function companySignatory()
    {
        return $this->belongsTo(CompanySignatory::class, 'company_signatory_id');
    }

    /** How many pen strokes were captured — evidence the pad was actually used. */
    public function strokeCount(): int
    {
        return count($this->signature_strokes['strokes'] ?? []);
    }

    /**
     * How this document was signed, as it appears on the certificate.
     * Lives here so the FPDI and Dompdf renderers cannot describe the same
     * signature differently.
     */
    public function signatureMethodLabel(): string
    {
        if ($this->signature_type !== 'drawn') {
            return 'Typed-name electronic signature, in person';
        }

        $strokes = $this->strokeCount();

        return 'Handwritten electronic signature captured in person'
            .($strokes ? sprintf(' (%d pen stroke%s recorded)', $strokes, $strokes === 1 ? '' : 's') : '');
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
