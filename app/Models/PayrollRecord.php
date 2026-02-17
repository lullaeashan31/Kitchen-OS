<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRecord extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'year',
        'base_salary',
        'bonus',
        'deductions',
        'net_salary',
        'status',
        'payslip_path',
        'paid_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
