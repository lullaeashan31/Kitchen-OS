<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            // Using 10,3 for precision (e.g. 0.001 kg = 1g)
            $table->decimal('current_stock', 10, 3)->default(0)->after('price');
            $table->decimal('alert_threshold', 10, 3)->default(0)->after('current_stock');
        });
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn(['current_stock', 'alert_threshold']);
        });
    }
};
