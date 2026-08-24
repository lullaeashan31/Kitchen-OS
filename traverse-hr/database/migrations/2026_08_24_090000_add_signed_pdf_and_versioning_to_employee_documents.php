<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Turns a signature from "a row in a table" into evidence:
     *
     *  - signed_pdf_path / signed_pdf_sha256: the actual rendered document
     *    with the signature block stamped into it, plus a hash so tampering
     *    with the stored file is detectable.
     *  - signing_outlet_id / signing_place: WHERE the signing happened. Real
     *    GPS isn't obtainable on a kiosk without prompting each employee and
     *    a third-party lookup service, so we record the outlet the session
     *    was run at — defensible and verifiable, rather than false precision.
     *  - superseded_at/by: re-signing no longer destroys the prior signature.
     *    The old row is kept and marked superseded, so an updated HR policy
     *    leaves a full chain of what each person acknowledged and when.
     */
    public function up(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->string('signed_pdf_path')->nullable()->after('field_values');
            $table->string('signed_pdf_sha256', 64)->nullable()->after('signed_pdf_path');
            $table->foreignId('signing_outlet_id')->nullable()->after('signed_user_agent')
                ->constrained('outlets')->nullOnDelete();
            $table->string('signing_place')->nullable()->after('signing_outlet_id');
            $table->timestamp('superseded_at')->nullable()->after('signing_place');
            $table->foreignId('superseded_by_id')->nullable()->after('superseded_at')
                ->constrained('employee_documents')->nullOnDelete();
        });

        // One-signature-per-document was the thing destroying history on
        // re-acknowledgement. Uniqueness now lives in application logic:
        // at most one NON-superseded signature per (employee, template).
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropUnique('employee_documents_employee_id_document_template_id_unique');
            $table->index(['employee_id', 'document_template_id']);
            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'document_template_id']);
            $table->dropIndex(['employee_id', 'status']);
            $table->dropConstrainedForeignId('signing_outlet_id');
            $table->dropConstrainedForeignId('superseded_by_id');
            $table->dropColumn(['signed_pdf_path', 'signed_pdf_sha256', 'signing_place', 'superseded_at']);
            $table->unique(['employee_id', 'document_template_id']);
        });
    }
};
