<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kitchen extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'latitude',
        'longitude',
        'geofence_radius',
        'is_active',
    ];
}
