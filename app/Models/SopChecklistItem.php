<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SopChecklistItem extends Model
{
    protected $table = 'sop_checklist_items';

    protected $fillable = [
        'checklist_id',
        'name',
        'description',
        'is_photo_required',
        'sort_order',
    ];

    public function checklist()
    {
        return $this->belongsTo(SopChecklist::class);
    }
}
