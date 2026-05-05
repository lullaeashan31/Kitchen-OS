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
        Schema::create('inventory_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kitchen_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_id')->comment('The batch (purchase) this deduction came from')->constrained('purchases')->onDelete('cascade');
            $table->decimal('quantity_used', 15, 3);
            $table->decimal('unit_price', 15, 2)->comment('Price of the batch at time of deduction');
            $table->decimal('total_cost', 15, 2);
            $table->string('source_type')->nullable()->comment('Production, Adjustment, etc');
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_deductions');
    }
};
