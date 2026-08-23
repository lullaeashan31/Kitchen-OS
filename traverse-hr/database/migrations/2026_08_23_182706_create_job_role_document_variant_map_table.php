<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The per-role dropdown the owner asked for: "when I put in a role, I
     * can choose with a dropdown which document policy goes to that
     * person." One row per (job_role, document_template) pair that
     * deviates from the type's default variant. No row = falls back to
     * the variant marked is_default.
     */
    public function up(): void
    {
        Schema::create('job_role_document_variant_map', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_template_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_template_variant_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['job_role_id', 'document_template_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_role_document_variant_map');
    }
};
