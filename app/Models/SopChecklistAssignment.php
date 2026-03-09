<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class SopChecklistAssignment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'kitchen_id',
        'checklist_id',
        'shift',
        'role',
        'deadline_time',
    ];

    public function checklist()
    {
        return $this->belongsTo(SopChecklist::class);
    }
}
