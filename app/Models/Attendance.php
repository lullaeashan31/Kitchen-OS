<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'staff_code',
        'user_id',
        'clock_in_time',
        'clock_out_time',
        'gps_latitude_in',
        'gps_longitude_in',
        'gps_latitude_out',
        'gps_longitude_out',
        'selfie_path_in',
        'selfie_path_out',
        'device_id',
        'location_id',
        'status',
    ];

    protected $casts = [
        'clock_in_time' => 'datetime',
        'clock_out_time' => 'datetime',
        'gps_latitude_in' => 'decimal:7',
        'gps_longitude_in' => 'decimal:7',
        'gps_latitude_out' => 'decimal:7',
        'gps_longitude_out' => 'decimal:7',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
