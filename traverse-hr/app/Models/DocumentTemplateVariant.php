<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplateVariant extends Model
{
    use HasFactory;

    protected $fillable = ['document_template_id', 'label', 'is_default'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function documentTemplate()
    {
        return $this->belongsTo(DocumentTemplate::class);
    }

    public function versions()
    {
        return $this->hasMany(DocumentTemplateVersion::class);
    }

    /** The active version to actually send, per language, highest version number first. */
    public function currentVersion(string $language = 'en'): ?DocumentTemplateVersion
    {
        return $this->versions()
            ->where('language', $language)
            ->where('active', true)
            ->orderByDesc('version')
            ->first();
    }
}
