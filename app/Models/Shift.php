<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'kitchen_id',
        'name',
        'start_time',
        'end_time',
        'is_active',
    ];

    public function assignments()
    {
        return $this->hasMany(ShiftAssignment::class);
    }

    public function checklists()
    {
        return $this->hasMany(SopChecklist::class);
    }
}
