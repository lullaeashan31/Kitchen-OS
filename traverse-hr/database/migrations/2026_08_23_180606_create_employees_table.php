<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Statutory identifier columns (pan/uan/esic/bank/ifsc) are stored as
     * `text` and read/written through Laravel encrypted casts on the
     * Employee model — encrypted at rest, masked in list views, full value
     * shown only to Admin/HR with every unmask logged (see AuditLog).
     *
     * Deliberately NO full Aadhaar number column exists anywhere in this
     * schema — only aadhaar_last_four + verification metadata, per the
     * Aadhaar Act / DPDP Act constraint in the brief. Do not add one
     * without a deliberate, separate decision.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique();
            $table->unsignedBigInteger('applicant_id')->nullable()
                ->comment('Set when converted from an accepted offer; applicants table lands in M4');

            $table->string('name');
            $table->string('photo_path')->nullable();
            $table->string('phone', 20);
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->text('address')->nullable();
            $table->date('dob')->nullable();
            $table->date('date_of_joining');

            $table->foreignId('job_role_id')->constrained();
            $table->string('designation')->nullable();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('outlet_id')->constrained();
            $table->foreignId('reporting_manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->enum('employment_type', ['full_time', 'part_time', 'trainee', 'contract'])->default('full_time');
            $table->date('probation_end_date')->nullable();
            $table->date('confirmation_date')->nullable();
            $table->enum('status', ['active', 'on_notice', 'exited'])->default('active');
            $table->date('exit_date')->nullable();
            $table->text('exit_reason')->nullable();

            $table->text('pan_encrypted')->nullable();
            $table->text('uan_encrypted')->nullable();
            $table->text('esic_number_encrypted')->nullable();
            $table->text('bank_account_encrypted')->nullable();
            $table->text('ifsc_encrypted')->nullable();

            $table->char('aadhaar_last_four', 4)->nullable();
            $table->timestamp('aadhaar_verified_at')->nullable();
            $table->foreignId('aadhaar_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('aadhaar_scan_document_id')->nullable()
                ->comment('FK to restricted-storage documents table, built in M2');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
