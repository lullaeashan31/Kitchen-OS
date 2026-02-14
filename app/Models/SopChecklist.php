<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SopChecklist extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'shift_id', 'shift', 'role', 'deadline_time', 'status'];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function items()
    {
        return $this->hasMany(SopChecklistItem::class, 'checklist_id')->orderBy('sort_order');
    }

    public function runs()
    {
        return $this->hasMany(SopDailyRun::class, 'checklist_id');
    }

    public function assignments()
    {
        return $this->hasMany(SopChecklistAssignment::class, 'checklist_id');
    }

    public function todayRun()
    {
        return $this->hasOne(SopDailyRun::class, 'checklist_id')->whereDate('date', today());
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
