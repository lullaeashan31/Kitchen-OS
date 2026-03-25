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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('type')->default('ingredient')->after('name');
        });

        // Migrate existing categories used by recipes to 'recipe' type
        // This is a safety measure to ensure existing data doesn't break
        \DB::table('categories')
            ->whereIn('id', function($query) {
                $query->select('category_id')->from('recipes');
            })
            ->update(['type' => 'recipe']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
