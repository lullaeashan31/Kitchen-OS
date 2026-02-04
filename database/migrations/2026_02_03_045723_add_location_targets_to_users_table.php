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
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('target_latitude', 10, 7)->nullable()->after('staff_code');
            $table->decimal('target_longitude', 10, 7)->nullable()->after('target_latitude');
            $table->string('target_location_name')->nullable()->after('target_longitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['target_latitude', 'target_longitude', 'target_location_name']);
        });
    }
};
