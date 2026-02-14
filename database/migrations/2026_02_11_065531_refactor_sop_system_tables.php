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
        // 1. Rename existing tables to new names
        Schema::rename('sop_items', 'sop_checklist_items');
        Schema::rename('sop_logs', 'sop_daily_runs');
        Schema::rename('sop_item_logs', 'sop_item_completions');

        // 2. Update sop_checklists
        Schema::table('sop_checklists', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->string('role')->nullable()->after('shift'); // e.g. 'staff', 'manager'
            $table->string('status')->default('active')->after('deadline_time'); // active, archived, paused
        });

        // 3. Create sop_checklist_assignments
        Schema::create('sop_checklist_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('sop_checklists')->onDelete('cascade');
            $table->string('shift');
            $table->string('role');
            $table->time('deadline_time');
            $table->timestamps();
        });

        // 4. Update sop_checklist_items
        Schema::table('sop_checklist_items', function (Blueprint $table) {
            $table->renameColumn('task', 'name');
        });

        // 5. Update sop_daily_runs
        Schema::table('sop_daily_runs', function (Blueprint $table) {
            $table->renameColumn('log_date', 'date');
            $table->timestamp('approved_at')->nullable()->after('status');
        });

        // Ensure status reflects pending/approved
        DB::statement("ALTER TABLE sop_daily_runs MODIFY COLUMN status ENUM('pending', 'approved') DEFAULT 'pending'");

        // 6. Update sop_item_completions
        Schema::table('sop_item_completions', function (Blueprint $table) {
            $table->renameColumn('log_id', 'run_id');
            $table->foreignId('user_id')->nullable()->after('item_id')->constrained('users');
        });
    }

    public function down(): void
    {
        Schema::table('sop_item_completions', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->renameColumn('run_id', 'log_id');
        });

        Schema::table('sop_daily_runs', function (Blueprint $table) {
            $table->dropColumn('approved_at');
            $table->renameColumn('date', 'log_date');
        });

        DB::statement("ALTER TABLE sop_daily_runs MODIFY COLUMN status ENUM('pending', 'completed') DEFAULT 'pending'");

        Schema::table('sop_checklist_items', function (Blueprint $table) {
            $table->renameColumn('name', 'task');
        });

        Schema::dropIfExists('sop_checklist_assignments');

        Schema::table('sop_checklists', function (Blueprint $table) {
            $table->dropColumn(['description', 'role', 'status']);
        });

        Schema::rename('sop_item_completions', 'sop_item_logs');
        Schema::rename('sop_daily_runs', 'sop_logs');
        Schema::rename('sop_checklist_items', 'sop_items');
    }
};
