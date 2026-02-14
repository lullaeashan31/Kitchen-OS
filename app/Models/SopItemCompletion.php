<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SopItemCompletion extends Model
{
    protected $table = 'sop_item_completions';

    protected $fillable = [
        'run_id',
        'item_id',
        'user_id',
        'is_completed',
        'status',
        'rejection_reason',
        'photo_path',
        'photo_history',
        'completed_at',
    ];

    protected $casts = [
        'photo_history' => 'array',
        'completed_at' => 'datetime',
    ];

    public function run()
    {
        return $this->belongsTo(SopDailyRun::class, 'run_id');
    }

    public function item()
    {
        return $this->belongsTo(SopChecklistItem::class, 'item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
