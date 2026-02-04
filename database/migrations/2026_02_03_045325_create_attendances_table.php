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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->string('staff_code', 6)->index();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamp('clock_in_time');
            $table->timestamp('clock_out_time')->nullable();

            // GPS Location
            $table->decimal('gps_latitude_in', 10, 7);
            $table->decimal('gps_longitude_in', 10, 7);
            $table->decimal('gps_latitude_out', 10, 7)->nullable();
            $table->decimal('gps_longitude_out', 10, 7)->nullable();

            // Selfies (Drive URLs)
            $table->string('selfie_path_in');
            $table->string('selfie_path_out')->nullable();

            // Device & Status
            $table->string('device_id');
            $table->string('location_id')->nullable(); // Could be a foreign key if a locations table exists, treating as string for now based on requirements
            $table->string('status')->default('success'); // success, rejected

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
