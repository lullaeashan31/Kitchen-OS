<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobRoleDocumentVariant extends Model
{
    protected $table = 'job_role_document_variant_map';

    protected $fillable = ['job_role_id', 'document_template_id', 'document_template_variant_id'];

    public function jobRole()
    {
        return $this->belongsTo(JobRole::class);
    }

    public function documentTemplate()
    {
        return $this->belongsTo(DocumentTemplate::class);
    }

    public function variant()
    {
        return $this->belongsTo(DocumentTemplateVariant::class, 'document_template_variant_id');
    }
}
