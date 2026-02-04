<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('production_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The cook
            $table->decimal('portions', 8, 2); // e.g. 5.0, 10.5

            // Snapshot of costs at the time of production
            $table->decimal('cost_per_portion', 10, 2);
            $table->decimal('total_cost', 10, 2);

            $table->timestamp('produced_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_logs');
    }
};
