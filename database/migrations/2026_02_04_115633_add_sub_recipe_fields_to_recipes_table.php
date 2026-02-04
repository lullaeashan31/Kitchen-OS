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
            // Link to ingredient that this recipe produces
            $table->unsignedBigInteger('produces_ingredient_id')->nullable()->after('status');

            // Output specifications
            $table->decimal('output_quantity', 10, 3)->default(1)->after('produces_ingredient_id');
            $table->string('output_unit', 50)->nullable()->after('output_quantity');

            // Foreign key constraint
            $table->foreign('produces_ingredient_id')
                ->references('id')
                ->on('ingredients')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipes', function (Blueprint $table) {
            $table->dropForeign(['produces_ingredient_id']);
            $table->dropColumn(['produces_ingredient_id', 'output_quantity', 'output_unit']);
        });
    }
};
