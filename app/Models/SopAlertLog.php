<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SopAlertLog extends Model
{
    protected $fillable = [
        'checklist_id',
        'date',
        'alert_type',
        'status',
    ];

    public function checklist()
    {
        return $this->belongsTo(SopChecklist::class);
    }
}
