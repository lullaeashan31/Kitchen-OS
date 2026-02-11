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
        Schema::table('recipes', function (Blueprint $table) {
            if (!Schema::hasColumn('recipes', 'yield_portions')) {
                $table->integer('yield_portions')->nullable();
            }
            if (!Schema::hasColumn('recipes', 'yield_weight')) {
                $table->decimal('yield_weight', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('recipes', 'yield_weight_unit')) {
                $table->string('yield_weight_unit')->nullable();
            }
            if (!Schema::hasColumn('recipes', 'yield_volume')) {
                $table->decimal('yield_volume', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('recipes', 'yield_volume_unit')) {
                $table->string('yield_volume_unit')->nullable();
            }
            if (!Schema::hasColumn('recipes', 'yield_batches')) {
                $table->integer('yield_batches')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            //
        });
    }
};
