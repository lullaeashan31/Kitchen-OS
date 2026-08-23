<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentTemplate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'kind', 'category', 'conditional', 'active', 'created_by'];

    protected $casts = [
        'conditional' => 'boolean',
        'active' => 'boolean',
    ];

    public function variants()
    {
        return $this->hasMany(DocumentTemplateVariant::class);
    }

    public function defaultVariant()
    {
        return $this->hasOne(DocumentTemplateVariant::class)->where('is_default', true);
    }
}
