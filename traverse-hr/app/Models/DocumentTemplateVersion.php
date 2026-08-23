<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplateVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_template_variant_id', 'language', 'version',
        'source_file_path', 'source_file_original_name',
        'body_html', 'field_schema', 'active', 'uploaded_by',
    ];

    protected $casts = [
        'field_schema' => 'array',
        'active' => 'boolean',
    ];

    public function variant()
    {
        return $this->belongsTo(DocumentTemplateVariant::class, 'document_template_variant_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
