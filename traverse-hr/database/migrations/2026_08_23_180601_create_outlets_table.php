<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outlets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 10)->unique()->comment('Used as employee code prefix, e.g. ALN');
            $table->text('address')->nullable();
            $table->string('timezone')->default('Asia/Kolkata');
            $table->enum('payroll_divisor_setting', ['calendar', 'fixed_26', 'fixed_30'])->default('calendar');
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outlets');
    }
};
