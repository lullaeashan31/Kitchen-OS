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
        Schema::create('recipe_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->string('name')->default('Main');
            $table->text('method')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Migrate existing data: Create a default stage for every existing recipe
        $recipes = DB::table('recipes')->get();
        foreach ($recipes as $recipe) {
            DB::table('recipe_stages')->insert([
                'recipe_id' => $recipe->id,
                'name' => 'Main',
                'method' => $recipe->method, // Move method to stage
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_stages');
    }
};
