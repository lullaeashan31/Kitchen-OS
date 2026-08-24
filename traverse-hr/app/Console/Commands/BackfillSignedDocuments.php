<?php

namespace App\Console\Commands;

use App\Models\EmployeeDocument;
use App\Services\SignedDocumentGenerator;
use Illuminate\Console\Command;

/**
 * Documents accepted before signed-PDF generation existed have a valid
 * signature record but no rendered artefact. This regenerates them from the
 * pinned version + stored signature data, so nothing already collected is
 * left without a downloadable signed copy.
 */
class BackfillSignedDocuments extends Command
{
    protected $signature = 'traverse:backfill-signed-documents';

    protected $description = 'Generate signed PDFs for acceptances recorded before PDF generation existed';

    public function handle(): int
    {
        $pending = EmployeeDocument::where('status', 'signed')
            ->whereNull('signed_pdf_path')
            ->get();

        if ($pending->isEmpty()) {
            $this->info('Nothing to backfill — every signed document already has a PDF.');

            return self::SUCCESS;
        }

        $ok = 0;
        $failed = 0;
        foreach ($pending as $doc) {
            try {
                SignedDocumentGenerator::generate($doc);
                $ok++;
            } catch (\Throwable $e) {
                $failed++;
                $this->error("  #{$doc->id} {$doc->documentTemplate->name}: {$e->getMessage()}");
            }
        }

        $this->info("Backfilled {$ok} signed document(s)".($failed ? ", {$failed} failed." : '.'));

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
