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
        // 1. Ingredients Table Updates
        Schema::table('ingredients', function (Blueprint $table) {
            if (!Schema::hasColumn('ingredients', 'usage_quantity')) {
                $table->decimal('usage_quantity', 15, 3)->nullable()->after('purchase_quantity');
            }
        });

        // 2. Recipes Table Updates
        Schema::table('recipes', function (Blueprint $table) {
            if (!Schema::hasColumn('recipes', 'yield_weight_grams')) {
                $table->decimal('yield_weight_grams', 15, 3)->nullable()->after('yield_weight');
            }
            if (!Schema::hasColumn('recipes', 'prep_time_minutes')) {
                $table->integer('prep_time_minutes')->nullable()->after('yield_batches');
            }
        });

        // 3. Create Purchase Units Table
        Schema::create('purchase_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('base_unit'); // e.g., 'g', 'ml'
            $table->decimal('conversion_factor', 15, 3);
            $table->timestamps();
        });

        // 4. Create FIFO Batch Table
        Schema::create('purchase_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity_remaining', 15, 3);
            $table->decimal('price_per_unit', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_batches');
        Schema::dropIfExists('purchase_units');
        
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropColumn('yield_weight_grams');
            $table->integer('prep_time_minutes')->nullable();
        });

        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn('usage_quantity');
        });
    }
};
