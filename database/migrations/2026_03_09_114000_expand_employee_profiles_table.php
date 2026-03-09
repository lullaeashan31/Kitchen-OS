<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            // Section A & B: Personal & Identification
            $table->string('full_name_aadhaar')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('father_spouse_name')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('blood_group')->nullable();
            $table->text('permanent_address')->nullable();
            $table->string('aadhaar_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('dl_number')->nullable();
            $table->string('voter_id')->nullable();
            $table->json('submitted_documents')->nullable(); // Tickable docs

            // Section D, E, G, H, I, J, L, N, O: Advanced Info (Mostly JSON for flexibility)
            $table->json('educational_qualifications')->nullable(); // Qualification, Institution, Year, Grade
            $table->json('emergency_contacts_json')->nullable();   // Array of {name, relationship, mobile, address}
            $table->json('nominee_details')->nullable();          // {name, relationship, dob, address, share}
            $table->json('medical_info')->nullable();            // {conditions, fitness_checked, fssai_info}
            $table->json('uniform_details')->nullable();         // {sizes, safety_shoes, etc.}
            $table->json('asset_acknowledgments')->nullable();   // {assets_issued, acknowledged_at}
            $table->json('salary_details_ext')->nullable();      // {ctc, breakup, payment_mode}
            $table->json('probation_info')->nullable();          // {duration, end_date, review_dates}

            // Section C: Management Info
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('reporting_manager')->nullable();
            $table->string('work_location')->nullable();
            $table->string('employment_type')->nullable();

            // Section K: Verification
            $table->string('police_verification_status')->nullable();
            $table->date('verification_date')->nullable();
            $table->string('verification_agency')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('employee_profiles', function (Blueprint $table) {
            $table->dropColumn([
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
                'verification_agency'
            ]);
        });
    }
};
