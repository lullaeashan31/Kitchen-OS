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

    protected $appends = ['photo_url'];

    public function getPhotoUrlAttribute()
    {
        if (!$this->photo_path) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->item->name ?? 'SOP') . '&color=7F9CF5&background=EBF4FF';
        }

        // Handle the case where "0" was saved due to a bug
        if ($this->photo_path === '0') {
            return null;
        }

        if (\Illuminate\Support\Facades\Storage::disk('public')->exists($this->photo_path)) {
            return '/storage/' . $this->photo_path;
        }

        return \Illuminate\Support\Facades\Storage::disk('s3')->url($this->photo_path);
    }

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
