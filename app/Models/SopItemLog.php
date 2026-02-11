<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SopItemLog extends Model
{
    protected $table = 'sop_item_logs'; // Explicit table name just in case convention fails? No, simpler is better. But keeping safe.

    protected $fillable = ['log_id', 'item_id', 'is_completed', 'photo_path', 'completed_at'];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime'
    ];

    public function item()
    {
        return $this->belongsTo(SopItem::class, 'item_id');
    }
}
