<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recipe_ingredients', function (Blueprint $table) {
            $table->foreignId('recipe_stage_id')->nullable()->after('recipe_id')->constrained('recipe_stages')->cascadeOnDelete();
        });

        // Migrate existing ingredients to the default stage of their recipe
        $items = DB::table('recipe_ingredients')->get();
        foreach ($items as $item) {
            $stage = DB::table('recipe_stages')
                ->where('recipe_id', $item->recipe_id)
                ->first();

            if ($stage) {
                DB::table('recipe_ingredients')
                    ->where('id', $item->id)
                    ->update(['recipe_stage_id' => $stage->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_ingredients', function (Blueprint $table) {
            //
        });
    }
};
