<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Flag-for-review only — nothing in this system auto-deletes on retention expiry (§9). */
class RetentionFlag extends Model
{
    protected $fillable = [
        'record_type', 'record_id', 'flagged_at', 'reviewed_at', 'reviewed_by', 'decision',
    ];

    protected $casts = [
        'flagged_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
