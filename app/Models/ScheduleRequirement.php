<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleRequirement extends Model
{
    use BelongsToTenant;

    protected $fillable = ['kitchen_id', 'day_of_week', 'role_id', 'required_count'];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
