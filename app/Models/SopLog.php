<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SopLog extends Model
{
    protected $fillable = ['checklist_id', 'user_id', 'log_date', 'status', 'completed_at'];

    protected $casts = [
        'log_date' => 'date',
        'completed_at' => 'datetime'
    ];

    public function checklist()
    {
        return $this->belongsTo(SopChecklist::class, 'checklist_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function itemLogs()
    {
        return $this->hasMany(SopItemLog::class, 'log_id');
    }
}
