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
        Schema::table('purchase_batches', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_batches', 'quantity_initial')) {
                $table->decimal('quantity_initial', 15, 3)->after('ingredient_id')->nullable();
            }
        });

        // Initialize quantity_initial with current quantity_remaining for existing batches
        \DB::table('purchase_batches')->update([
            'quantity_initial' => \DB::raw('quantity_remaining')
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_batches', function (Blueprint $table) {
            $table->dropColumn('quantity_initial');
        });
    }
};
