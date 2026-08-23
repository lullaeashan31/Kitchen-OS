<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentPack extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name'];

    public function items()
    {
        return $this->hasMany(DocumentPackItem::class)->orderBy('sort_order');
    }

    public function documentTemplates()
    {
        return $this->belongsToMany(DocumentTemplate::class, 'document_pack_items')
            ->withPivot('sort_order', 'required')
            ->orderByPivot('sort_order');
    }
}
