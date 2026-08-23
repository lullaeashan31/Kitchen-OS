<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lean "classic session" signing record: HR sits with the employee at
     * onboarding, pulls up a document, the employee reads it and types
     * their name to accept — per the owner's explicit simplification
     * (no signature pad / canvas / drawn strokes required for now, no
     * tokenised remote link needed for the in-person flow).
     *
     * This intentionally does NOT yet split into the ERD's
     * employee_document_instances + signatures tables — that fuller
     * shape (with token/expiry for remote signing, and a signature_type
     * of drawn/typed/aadhaar_esign) is for when the pad or remote-phone
     * signing paths are actually built. This table's rows migrate
     * forward into that shape without data loss when that happens; see
     * DECISIONS.md.
     */
    public function up(): void
    {
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_template_id')->constrained();
            $table->foreignId('document_template_version_id')->constrained()
                ->comment('Pinned at send/sign time — never repointed if the template is edited later');
            $table->enum('language', ['en', 'hi', 'mr'])->default('en');
            $table->enum('status', ['pending', 'signed'])->default('pending');
            $table->json('field_values')->nullable()->comment('For sign_with_fields kinds');
            $table->string('signer_typed_name')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->ipAddress('signed_ip')->nullable();
            $table->string('signed_user_agent')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete()
                ->comment('The HR/manager user running the in-person session, if any');
            $table->timestamps();

            $table->unique(['employee_id', 'document_template_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
