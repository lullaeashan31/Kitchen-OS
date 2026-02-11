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
            $table->string('phone')->unique()->nullable()->after('email');
            $table->string('email')->nullable()->change();
            $table->boolean('is_password_changed')->default(false)->after('password');
            $table->string('temp_password')->nullable()->after('password'); // Store temp if needed? Better not to store plain.
            // Just use boolean flag.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'is_password_changed', 'temp_password']);
            $table->string('email')->nullable(false)->change(); // Might fail if nulls exist
        });
    }
};
