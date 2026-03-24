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
        // Fix Categories table indices
        Schema::table('categories', function (Blueprint $table) {
            // Drop the global unique index on name
            $table->dropUnique(['name']);
            // Add kitchen-scoped unique index
            $table->unique(['kitchen_id', 'name']);
        });

        // Fix Ingredients table indices
        Schema::table('ingredients', function (Blueprint $table) {
            // Drop the global unique index on name
            $table->dropUnique(['name']);
            // Add kitchen-scoped unique index
            $table->unique(['kitchen_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['kitchen_id', 'name']);
            $table->unique('name');
        });

        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropUnique(['kitchen_id', 'name']);
            $table->unique('name');
        });
    }
};
