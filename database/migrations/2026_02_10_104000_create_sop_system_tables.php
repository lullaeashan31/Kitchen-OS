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
        // 1. Define Checklists (e.g. "Morning Opening")
        Schema::create('sop_checklists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('shift', ['morning', 'mid', 'evening', 'closing']);
            $table->time('deadline_time')->nullable(); // e.g. 10:00:00
            $table->timestamps();
        });

        // 2. Define Items in Checklist (e.g. "Turn on oven")
        Schema::create('sop_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('sop_checklists')->onDelete('cascade');
            $table->string('task');
            $table->text('description')->nullable();
            $table->boolean('is_photo_required')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Log Daily Instance of Checklist (e.g. "Morning Opening for 2026-02-10")
        Schema::create('sop_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('sop_checklists')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // Who started/completed it
            $table->date('log_date');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // 4. Log Individual Item Completions
        Schema::create('sop_item_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('log_id')->constrained('sop_logs')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('sop_items')->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->string('photo_path')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sop_item_logs');
        Schema::dropIfExists('sop_logs');
        Schema::dropIfExists('sop_items');
        Schema::dropIfExists('sop_checklists');
    }
};
