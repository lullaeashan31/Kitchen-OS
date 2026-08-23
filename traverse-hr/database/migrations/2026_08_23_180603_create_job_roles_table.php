<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * "Job roles" (Commis, Server, Sous Chef, ...) are business data an
     * Admin manages. Deliberately a separate table from the permission
     * engine's `roles` table (spatie) — see PERMISSION_MATRIX.md.
     *
     * document_pack_id / offer_template_id / pipeline_id are plain nullable
     * unsigned bigints for now (no FK constraint) because document_packs,
     * offer_templates and pipelines are built in M2/M4. A follow-up
     * migration adds the FK constraint once those tables exist.
     */
    public function up(): void
    {
        Schema::create('job_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->nullable()->constrained()->nullOnDelete()
                ->comment('Null = group-wide role available to all outlets');
            $table->string('name');
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->string('default_designation')->nullable();
            $table->unsignedInteger('default_salary_band_min')->nullable();
            $table->unsignedInteger('default_salary_band_max')->nullable();
            $table->unsignedBigInteger('default_document_pack_id')->nullable();
            $table->unsignedBigInteger('default_offer_template_id')->nullable();
            $table->unsignedBigInteger('default_pipeline_id')->nullable();
            $table->unsignedSmallInteger('probation_months')->default(3);
            $table->unsignedSmallInteger('notice_period_days')->default(30);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_roles');
    }
};
