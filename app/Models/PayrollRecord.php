<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PayrollRecord extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'kitchen_id',
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
