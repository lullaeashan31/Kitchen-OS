<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            if (!Schema::hasColumn('recipes', 'prep_time_minutes')) {
                $table->integer('prep_time_minutes')->nullable()->after('yield_batches');
            }
            if (!Schema::hasColumn('recipes', 'yield_weight_grams')) {
                $table->decimal('yield_weight_grams', 15, 3)->nullable()->after('yield_weight');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            if (Schema::hasColumn('recipes', 'prep_time_minutes')) {
                $table->dropColumn('prep_time_minutes');
            }
            if (Schema::hasColumn('recipes', 'yield_weight_grams')) {
                $table->dropColumn('yield_weight_grams');
            }
        });
    }
};
