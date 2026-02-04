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
            $table->decimal('price', 10, 2)->default(0)->after('name');
            $table->string('measurement_unit')->nullable()->after('price');
            $table->string('purchase_unit')->nullable()->after('measurement_unit');
            $table->string('category')->nullable()->after('purchase_unit'); // Or foreignId if using category table, keeping flexible as string for now based on user request "Master Excel Upload" fields
            $table->string('vendor')->nullable()->after('category');
            $table->string('status')->default('approved')->after('vendor'); // pending, approved
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            //
        });
    }
};
