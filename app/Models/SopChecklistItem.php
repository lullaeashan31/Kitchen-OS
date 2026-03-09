<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class SopChecklistItem extends Model
{
    use BelongsToTenant;

    protected $table = 'sop_checklist_items';

    protected $fillable = [
        'kitchen_id',
        'checklist_id',
        'name',
        'description',
        'is_photo_required',
        'sort_order',
    ];

    protected $casts = [
        'is_photo_required' => 'boolean',
    ];

    public function checklist()
    {
        return $this->belongsTo(SopChecklist::class);
    }
}
