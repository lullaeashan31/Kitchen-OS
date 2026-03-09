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
        $tables = [
            'recipes',
            'categories',
            'ingredients',
            'purchases',
            'production_days',
            'audit_logs',
            'tasks',
            'drive_files',
            'vendors',
            'attendances',
            'employee_profiles',
            'hr_policy_logs',
            'leave_requests',
            'payroll_records',
            'performance_reviews',
            'schedule_assignments',
            'schedule_requirements',
            'shifts',
            'shift_assignments',
            'sop_checklists',
            'sop_checklist_assignments',
            'sop_checklist_items',
            'sop_daily_runs',
            'sop_item_completions',
            'inventory_logs',
            'production_logs',
            'production_items',
            'recipe_ingredients',
            'recipe_stages',
            'recipe_versions',
            'onboarding_tokens'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'kitchen_id')) {
                    $table->foreignId('kitchen_id')->nullable()->constrained('kitchens')->onDelete('cascade');
                }
            });
        }

        // Assign existing data to the first available kitchen if any exists
        $firstKitchen = DB::table('kitchens')->first();
        if ($firstKitchen) {
            foreach ($tables as $tableName) {
                DB::table($tableName)->whereNull('kitchen_id')->update(['kitchen_id' => $firstKitchen->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'recipes',
            'categories',
            'ingredients',
            'purchases',
            'production_days',
            'audit_logs',
            'tasks',
            'drive_files',
            'vendors',
            'attendances',
            'employee_profiles',
            'hr_policy_logs',
            'leave_requests',
            'payroll_records',
            'performance_reviews',
            'schedule_assignments',
            'schedule_requirements',
            'shifts',
            'shift_assignments',
            'sop_checklists',
            'sop_checklist_assignments',
            'sop_checklist_items',
            'sop_daily_runs',
            'sop_item_completions',
            'inventory_logs',
            'production_logs',
            'production_items',
            'recipe_ingredients',
            'recipe_stages',
            'recipe_versions',
            'onboarding_tokens'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'kitchen_id')) {
                    // Laravel automatically names foreign keys as table_name_column_name_foreign
                    // We need to check if the foreign key constraint exists before dropping it
                    // This is a more robust way to handle it, though dropForeign([column_name]) often works
                    // For explicit naming, it would be $table->dropForeign(['kitchen_id']);
                    // For auto-named, it's usually table_name_column_name_foreign
                    // Let's use the column name directly, Laravel is smart enough to find the constraint
                    $table->dropForeign(['kitchen_id']);
                    $table->dropColumn('kitchen_id');
                }
            });
        }
    }
};
