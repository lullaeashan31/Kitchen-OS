<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The standing "your signed documents" link/QR the owner asked for:
     * generated once per employee, permanent by default (per §3.4 — the
     * locker stays live after onboarding), revocable and regenerable from
     * the admin side. Token is 32+ random bytes, never a guessable ID.
     */
    public function up(): void
    {
        Schema::create('employee_document_locker_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at')->nullable()->comment('Null = no expiry (default, per §3.4)');
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_document_locker_tokens');
    }
};
