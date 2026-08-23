<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A named variant of a document type, e.g. "Default", "Manager",
     * "Housekeeping". Exactly one variant per document_template should be
     * marked is_default — that's what a job role gets unless the admin
     * explicitly maps a different variant to it (see
     * job_role_document_variant_map).
     */
    public function up(): void
    {
        Schema::create('document_template_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_template_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['document_template_id', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_template_variants');
    }
};
