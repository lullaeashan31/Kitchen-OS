<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('reviewer_id')->constrained('users');
            $table->integer('month');
            $table->integer('year');
            $table->integer('sop_compliance')->default(0);
            $table->integer('hygiene')->default(0);
            $table->integer('punctuality')->default(0);
            $table->integer('teamwork')->default(0);
            $table->integer('technical_skill')->default(0);
            $table->integer('total_score')->default(0);
            $table->decimal('bonus_amount', 10, 2)->default(0);
            $table->text('comments')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'month', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};
