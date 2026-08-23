<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retention_policies', function (Blueprint $table) {
            $table->id();
            $table->string('record_type')->unique()
                ->comment('e.g. applicant_rejected, employee_document_signed, payroll_record');
            $table->unsignedInteger('retention_months');
            $table->string('action')->default('flag_for_review')
                ->comment('Always flag_for_review — never auto_delete, per brief §9');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retention_policies');
    }
};
