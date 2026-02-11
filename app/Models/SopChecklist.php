<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SopChecklist extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'shift', 'deadline_time'];

    public function items()
    {
        return $this->hasMany(SopItem::class, 'checklist_id')->orderBy('sort_order');
    }

    public function todayLog()
    {
        return $this->hasOne(SopLog::class, 'checklist_id')->where('log_date', today());
    }
}
