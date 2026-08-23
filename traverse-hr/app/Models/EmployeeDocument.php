<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id', 'document_template_id', 'document_template_version_id',
        'language', 'status', 'field_values', 'signer_typed_name', 'signed_at',
        'signed_ip', 'signed_user_agent', 'recorded_by',
    ];

    protected $casts = [
        'field_values' => 'array',
        'signed_at' => 'datetime',
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
}
