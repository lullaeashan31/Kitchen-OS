<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
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
