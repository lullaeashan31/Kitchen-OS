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
        //
        // Order matters on MySQL/MariaDB: the unique index is what backs the
        // employee_id foreign key, and the server refuses to drop the last
        // index a foreign key depends on (errno 1553). Creating the plain
        // replacement index FIRST gives the constraint something else to
        // lean on. SQLite has no such rule, which is why this only shows up
        // against a real MySQL server.
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->index(['employee_id', 'document_template_id']);
            $table->index(['employee_id', 'status']);
        });

        $unique = 'employee_documents_employee_id_document_template_id_unique';
        if (self::hasIndex('employee_documents', $unique)) {
            Schema::table('employee_documents', function (Blueprint $table) use ($unique) {
                $table->dropUnique($unique);
            });
        }
    }

    /** Re-running against a database that has already been part-migrated must not fail. */
    private static function hasIndex(string $table, string $index): bool
    {
        foreach (Schema::getIndexes($table) as $existing) {
            if (strcasecmp($existing['name'], $index) === 0) {
                return true;
            }
        }

        return false;
    }

    public function down(): void
    {
        // Mirror image of up(): restore the unique index BEFORE removing the
        // plain ones, so the employee_id foreign key is never left without a
        // backing index (MySQL errno 1553 again, in the other direction).
        $unique = 'employee_documents_employee_id_document_template_id_unique';
        if (! self::hasIndex('employee_documents', $unique)) {
            Schema::table('employee_documents', function (Blueprint $table) {
                $table->unique(['employee_id', 'document_template_id']);
            });
        }

        foreach ([
            'employee_documents_employee_id_document_template_id_index',
            'employee_documents_employee_id_status_index',
        ] as $index) {
            if (self::hasIndex('employee_documents', $index)) {
                Schema::table('employee_documents', function (Blueprint $table) use ($index) {
                    $table->dropIndex($index);
                });
            }
        }

        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('signing_outlet_id');
            $table->dropConstrainedForeignId('superseded_by_id');
            $table->dropColumn(['signed_pdf_path', 'signed_pdf_sha256', 'signing_place', 'superseded_at']);
        });
    }
};
