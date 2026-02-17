<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'joining_date',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'bank_name',
        'account_number',
        'ifsc_code',
        'identity_proof_path',
        'secondary_phone',
        'employment_history',
        'references',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'employment_history' => 'array',
        'references' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
