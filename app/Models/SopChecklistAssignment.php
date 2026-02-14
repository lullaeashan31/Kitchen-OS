<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SopChecklistAssignment extends Model
{
    protected $fillable = [
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
