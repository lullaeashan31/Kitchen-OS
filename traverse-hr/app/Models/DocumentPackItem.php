<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentPackItem extends Model
{
    protected $fillable = ['document_pack_id', 'document_template_id', 'sort_order', 'required'];

    protected $casts = ['required' => 'boolean'];

    public function documentPack()
    {
        return $this->belongsTo(DocumentPack::class);
    }

    public function documentTemplate()
    {
        return $this->belongsTo(DocumentTemplate::class);
    }
}
