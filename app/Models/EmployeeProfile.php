<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeProfile extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'kitchen_id',
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

        // New Onboarding Fields
        'full_name_aadhaar',
        'dob',
        'gender',
        'father_spouse_name',
        'marital_status',
        'blood_group',
        'permanent_address',
        'aadhaar_number',
        'pan_number',
        'passport_number',
        'dl_number',
        'voter_id',
        'submitted_documents',
        'educational_qualifications',
        'emergency_contacts_json',
        'nominee_details',
        'medical_info',
        'uniform_details',
        'asset_acknowledgments',
        'salary_details_ext',
        'probation_info',
        'designation',
        'department',
        'reporting_manager',
        'work_location',
        'employment_type',
        'police_verification_status',
        'verification_date',
        'verification_agency',
        'digital_signature'
    ];

    protected $casts = [
        'joining_date' => 'date',
        'dob' => 'date',
        'verification_date' => 'date',
        'employment_history' => 'array',
        'references' => 'array',
        'submitted_documents' => 'array',
        'educational_qualifications' => 'array',
        'emergency_contacts_json' => 'array',
        'nominee_details' => 'array',
        'medical_info' => 'array',
        'uniform_details' => 'array',
        'asset_acknowledgments' => 'array',
        'salary_details_ext' => 'array',
        'probation_info' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
