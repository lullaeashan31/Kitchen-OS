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
        // 1. Ingredients: Add Storage Location
        Schema::table('ingredients', function (Blueprint $table) {
            $table->string('storage_location')->nullable()->after('category_id'); // e.g. 'Fridge', 'Dry Store'
        });

        // 2. Recipes: Add Yield breakdown
        Schema::table('recipes', function (Blueprint $table) {
            $table->decimal('yield_portions', 10, 2)->nullable()->after('yields');
            $table->decimal('yield_weight', 10, 2)->nullable()->after('yield_portions');
            $table->string('yield_weight_unit')->nullable()->after('yield_weight'); // e.g. 'g', 'kg'
            $table->integer('prep_time_minutes')->nullable()->after('method');
        });

        // 3. Recipe Ingredients: Add Grouping
        Schema::table('recipe_ingredients', function (Blueprint $table) {
            $table->string('ingredient_group')->nullable()->after('unit'); // e.g. 'Sauce', 'Dough'
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn('storage_location');
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn(['yield_portions', 'yield_weight', 'yield_weight_unit', 'prep_time_minutes']);
        });

        Schema::table('recipe_ingredients', function (Blueprint $table) {
            $table->dropColumn('ingredient_group');
        });
    }
};
