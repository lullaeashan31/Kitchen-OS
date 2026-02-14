<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SopDailyRun extends Model
{
    protected $table = 'sop_daily_runs';

    protected $fillable = [
        'checklist_id',
        'user_id',
        'date',
        'status',
        'approved_at',
        'completed_at',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function checklist()
    {
        return $this->belongsTo(SopChecklist::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function completions()
    {
        return $this->hasMany(SopItemCompletion::class, 'run_id');
    }
}
