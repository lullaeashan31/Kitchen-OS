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
            $table->boolean('is_sub_recipe')->default(false)->after('produces_ingredient_id');
        });

        // Backfill
        DB::table('recipes')->whereNotNull('produces_ingredient_id')->update(['is_sub_recipe' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn('is_sub_recipe');
        });
    }
};
