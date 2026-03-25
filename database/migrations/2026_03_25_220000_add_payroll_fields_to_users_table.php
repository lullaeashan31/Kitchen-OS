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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'salary_type')) {
                $table->string('salary_type')->default('monthly')->after('monthly_salary'); // 'monthly', 'daily', 'hourly'
            }
            if (!Schema::hasColumn('users', 'daily_salary')) {
                $table->decimal('daily_salary', 10, 2)->nullable()->after('salary_type');
            }
            if (!Schema::hasColumn('users', 'hourly_salary')) {
                $table->decimal('hourly_salary', 10, 2)->nullable()->after('daily_salary');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['salary_type', 'daily_salary', 'hourly_salary']);
        });
    }
};
