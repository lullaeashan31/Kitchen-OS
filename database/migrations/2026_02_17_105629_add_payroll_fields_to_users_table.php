<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('monthly_salary', 10, 2)->nullable()->after('role');
            $table->boolean('variable_enabled')->default(false)->after('monthly_salary');
            $table->decimal('max_variable_amount', 10, 2)->nullable()->after('variable_enabled');
            $table->string('weekly_off_day')->default('Sunday')->after('max_variable_amount');
            $table->string('onboarding_status')->default('active')->after('weekly_off_day'); // active, pending, onboarding
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['monthly_salary', 'variable_enabled', 'max_variable_amount', 'weekly_off_day', 'onboarding_status']);
        });
    }
};
