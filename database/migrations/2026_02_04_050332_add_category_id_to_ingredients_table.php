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
        Schema::table('ingredients', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('name')->constrained('categories')->nullOnDelete();
        });

        // Migrate existing category strings to new category_id if name matches
        // Best effort: Match case-insensitive
        $ingredients = \App\Models\Ingredient::whereNotNull('category')->get();
        foreach ($ingredients as $ingredient) {
            $categoryName = trim($ingredient->category);
            if (!empty($categoryName)) {
                $category = \App\Models\Category::firstOrCreate(['name' => $categoryName]);
                $ingredient->category_id = $category->id;
                $ingredient->save();
            }
        }

        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
        });

        // Restore category strings from ID (approximate)
        $ingredients = \App\Models\Ingredient::with('category_relation')->get();
        foreach ($ingredients as $ingredient) {
            if ($ingredient->category_relation) {
                $ingredient->category = $ingredient->category_relation->name;
                $ingredient->save();
            }
        }

        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
