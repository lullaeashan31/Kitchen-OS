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
        Schema::create('sop_alert_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('sop_checklists')->onDelete('cascade');
            $table->date('date');
            $table->string('alert_type')->default('deadline_missed');
            $table->string('status')->default('sent');
            $table->timestamps();

            $table->unique(['checklist_id', 'date', 'alert_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_alert_logs');
    }
};
