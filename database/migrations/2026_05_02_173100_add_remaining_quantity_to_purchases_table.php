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
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('remaining_quantity', 15, 3)->after('quantity')->nullable();
        });

        // Initialize remaining_quantity for existing approved purchases
        DB::statement("UPDATE purchases SET remaining_quantity = quantity WHERE status = 'approved'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('remaining_quantity');
        });
    }
};
