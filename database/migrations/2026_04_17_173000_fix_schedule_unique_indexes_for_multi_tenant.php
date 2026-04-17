<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_requirements', function (Blueprint $table) {
            $table->dropUnique('schedule_requirements_day_of_week_role_id_unique');
            $table->unique(
                ['kitchen_id', 'day_of_week', 'role_id'],
                'schedule_requirements_kitchen_day_role_unique'
            );
        });

        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->dropUnique('schedule_assignments_date_role_id_slot_index_unique');
            $table->unique(
                ['kitchen_id', 'date', 'role_id', 'slot_index'],
                'schedule_assignments_kitchen_date_role_slot_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('schedule_requirements', function (Blueprint $table) {
            $table->dropUnique('schedule_requirements_kitchen_day_role_unique');
            $table->unique(['day_of_week', 'role_id'], 'schedule_requirements_day_of_week_role_id_unique');
        });

        Schema::table('schedule_assignments', function (Blueprint $table) {
            $table->dropUnique('schedule_assignments_kitchen_date_role_slot_unique');
            $table->unique(['date', 'role_id', 'slot_index'], 'schedule_assignments_date_role_id_slot_index_unique');
        });
    }
};
