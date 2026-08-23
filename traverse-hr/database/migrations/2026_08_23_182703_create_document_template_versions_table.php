<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The actual signable content for one variant, in one language.
     * `source_file_path` is the owner's uploaded original (PDF/DOCX) —
     * stored outside the public web root, served only through an
     * authorising controller (§6). `body_html` is reserved for a future
     * authored-in-app version with merge tokens (offer-letter-style);
     * for now the uploaded source file IS the document, and signing
     * appends a signature + audit page to it rather than re-rendering it.
     * `field_schema` holds the definition for sign_with_fields kinds.
     *
     * New content for a variant is a NEW row (version++), never an edit —
     * a document_template_version already referenced by a sent
     * employee_document_instance must never change under it.
     */
    public function up(): void
    {
        Schema::create('document_template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_template_variant_id')->constrained()->cascadeOnDelete();
            $table->enum('language', ['en', 'hi', 'mr'])->default('en');
            $table->unsignedInteger('version')->default(1);
            $table->string('source_file_path')->nullable();
            $table->string('source_file_original_name')->nullable();
            $table->longText('body_html')->nullable();
            $table->json('field_schema')->nullable();
            $table->boolean('active')->default(true);
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['document_template_variant_id', 'language', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_template_versions');
    }
};
