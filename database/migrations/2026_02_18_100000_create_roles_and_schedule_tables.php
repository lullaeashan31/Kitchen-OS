<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Custom job roles (Manager, Cook, etc.) - admin can create multiple
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Manager, Cook
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['permission_id', 'role_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('job_role_id')->nullable()->after('role')->constrained('roles')->nullOnDelete();
        });

        // Weekly schedule: how many of each role needed per day (1=Monday .. 7=Sunday)
        Schema::create('schedule_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week'); // 1=Mon, 7=Sun
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('required_count')->default(1);
            $table->timestamps();
            $table->unique(['day_of_week', 'role_id']);
        });

        // Assignments: who is scheduled for which day/slot (for a given week)
        Schema::create('schedule_assignments', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('slot_index'); // 1st, 2nd, etc.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['date', 'role_id', 'slot_index']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['job_role_id']);
        });
        Schema::dropIfExists('schedule_assignments');
        Schema::dropIfExists('schedule_requirements');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('roles');
    }
};
