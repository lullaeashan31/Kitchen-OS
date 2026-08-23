<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A "document type" — e.g. POSH Policy, Cash & Float Handling
     * Undertaking. Business data managed from Admin → Document Types.
     * Actual signable content lives one level down, in
     * document_template_variants → document_template_versions, so the
     * same type can have a Manager version, a Housekeeping version, etc.,
     * each independently versioned and multilingual.
     */
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('kind', ['acknowledge_only', 'sign_with_fields', 'upload_required']);
            $table->string('category')->nullable()->comment('Free-text grouping for the admin UI, e.g. Compliance, Statutory');
            $table->boolean('conditional')->default(false)->comment('True for "(if applicable)" documents, e.g. Cash & Float');
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
