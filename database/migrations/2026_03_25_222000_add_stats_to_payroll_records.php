<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payroll_records', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_records', 'salary_type')) {
                $table->string('salary_type')->nullable()->after('year');
            }
            if (!Schema::hasColumn('payroll_records', 'present_days')) {
                $table->integer('present_days')->default(0)->after('salary_type');
            }
            if (!Schema::hasColumn('payroll_records', 'absent_days')) {
                $table->integer('absent_days')->default(0)->after('present_days');
            }
            if (!Schema::hasColumn('payroll_records', 'working_hours')) {
                $table->decimal('working_hours', 10, 2)->default(0)->after('absent_days');
            }
            if (!Schema::hasColumn('payroll_records', 'attendance_data')) {
                $table->json('attendance_data')->nullable()->after('working_hours');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_records', function (Blueprint $table) {
            $table->dropColumn(['salary_type', 'present_days', 'absent_days', 'working_hours', 'attendance_data']);
        });
    }
};
