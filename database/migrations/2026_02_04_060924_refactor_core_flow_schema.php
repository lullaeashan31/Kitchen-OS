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
            if (!Schema::hasColumn('ingredients', 'avg_cost')) {
                $table->decimal('avg_cost', 10, 2)->default(0)->after('price');
            }
        });

        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'price') && !Schema::hasColumn('purchases', 'total_price')) {
                $table->renameColumn('price', 'total_price');
            }

            if (!Schema::hasColumn('purchases', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)->after('quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn('avg_cost');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->renameColumn('total_price', 'price');
            $table->dropColumn('unit_price');
        });
    }
};
