<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleRequirement extends Model
{
    protected $fillable = ['day_of_week', 'role_id', 'required_count'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
