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
        Schema::table('ingredients', function (Blueprint $table) {
            $table->decimal('purchase_quantity', 15, 3)->default(1)->after('purchase_unit');
            $table->decimal('purchase_price', 15, 2)->default(0)->after('purchase_quantity');
            $table->string('base_unit', 50)->nullable()->after('purchase_price');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->string('unit', 50)->nullable()->after('quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn(['purchase_quantity', 'purchase_price', 'base_unit']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['unit']);
        });
    }
};
