<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Moves from a typed name to a genuine drawn signature (signature pad,
     * stylus, finger or mouse), and adds the company's own countersignature.
     *
     * signature_strokes stores the raw pen path — points with timestamps and
     * pressure where the device reports it. That is far stronger evidence
     * than a flat image: it shows the signature was drawn by a hand in real
     * time, not pasted in.
     *
     * signature_type is deliberately an enum with room to grow: existing
     * records stay 'typed' (they were validly accepted under the previous
     * flow and must not be rewritten), new ones are 'drawn', and
     * 'aadhaar_esign' can be added later without a schema rewrite.
     */
    public function up(): void
    {
        Schema::create('company_signatories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('designation');
            $table->string('signature_image_path')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('employee_documents', function (Blueprint $table) {
            $table->string('signature_type', 32)->default('typed')->after('signer_typed_name');
            $table->string('signature_image_path')->nullable()->after('signature_type');
            $table->json('signature_strokes')->nullable()->after('signature_image_path');

            // Who signed for the company on this document. The name and
            // designation are snapshotted so renaming a signatory later can
            // never rewrite what a past document says.
            $table->foreignId('company_signatory_id')->nullable()->after('signature_strokes')
                ->constrained('company_signatories')->nullOnDelete();
            $table->string('company_signatory_name')->nullable()->after('company_signatory_id');
            $table->string('company_signatory_designation')->nullable()->after('company_signatory_name');
            $table->string('company_signature_image_path')->nullable()->after('company_signatory_designation');
        });
    }

    public function down(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('company_signatory_id');
            $table->dropColumn([
                'signature_type', 'signature_image_path', 'signature_strokes',
                'company_signatory_name', 'company_signatory_designation', 'company_signature_image_path',
            ]);
        });
        Schema::dropIfExists('company_signatories');
    }
};
