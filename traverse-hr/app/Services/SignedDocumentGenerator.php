<?php

namespace App\Services;

use App\Models\EmployeeDocument;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

/**
 * Produces the *signed* artefact — the thing that actually proves Rajesh
 * accepted the document, as opposed to a database row that merely claims he did.
 *
 * Two source shapes, one output:
 *
 *  - Uploaded PDF (the Traverse onboarding kit): every original page is
 *    imported and stamped along the footer, then a full Signature
 *    Certificate page is appended. Stamping every page matters — it stops
 *    any single page being detached and presented as unsigned.
 *  - Authored HTML (e.g. the HR Policy Manual): rendered by Dompdf with the
 *    same certificate appended in one pass.
 *
 * Output is written once, hashed with SHA-256, and never rewritten. A
 * re-signature produces a new file; the old one stays exactly as it was.
 */
class SignedDocumentGenerator
{
    public static function generate(EmployeeDocument $doc): EmployeeDocument
    {
        $doc->loadMissing('employee.outlet', 'employee.jobRole', 'documentTemplate', 'version', 'recordedBy', 'signingOutlet');

        $pdfBytes = $doc->version->source_file_path
            ? self::stampExistingPdf($doc)
            : self::renderHtmlDocument($doc);

        $path = sprintf(
            'signed-documents/%d/%d-%s-v%d-%s.pdf',
            $doc->employee_id,
            $doc->id,
            \Illuminate\Support\Str::slug($doc->documentTemplate->name),
            $doc->version->version,
            $doc->signed_at->format('Ymd-His')
        );

        Storage::disk('local')->put($path, $pdfBytes);

        $doc->forceFill([
            'signed_pdf_path' => $path,
            'signed_pdf_sha256' => hash('sha256', $pdfBytes),
        ])->save();

        return $doc;
    }

    /** Import the real document, stamp every page, append the certificate. */
    private static function stampExistingPdf(EmployeeDocument $doc): string
    {
        $source = Storage::disk('local')->path($doc->version->source_file_path);

        $pdf = new Fpdi;
        $pdf->SetAutoPageBreak(false);
        $pageCount = $pdf->setSourceFile($source);

        $footer = sprintf(
            'Electronically signed by %s  |  %s IST  |  Ref %s  |  See Signature Certificate (final page)',
            $doc->signer_typed_name,
            $doc->signed_at->timezone('Asia/Kolkata')->format('d M Y, H:i'),
            self::reference($doc)
        );

        for ($p = 1; $p <= $pageCount; $p++) {
            $tpl = $pdf->importPage($p);
            $size = $pdf->getTemplateSize($tpl);
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($tpl);

            // Thin rule + footer strip, kept inside the bottom margin so it
            // does not cover the original letterhead or signature lines.
            $y = $size['height'] - 8;
            $pdf->SetDrawColor(150, 150, 150);
            $pdf->SetLineWidth(0.2);
            $pdf->Line(10, $y - 2.5, $size['width'] - 10, $y - 2.5);
            $pdf->SetFont('Helvetica', '', 6.5);
            $pdf->SetTextColor(70, 70, 70);
            $pdf->SetXY(10, $y);
            $pdf->Cell($size['width'] - 20, 4, self::latin1($footer), 0, 0, 'L');
        }

        // Certificate page, same size as the last imported page.
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        self::drawCertificate($pdf, $doc, $size['width']);

        return $pdf->Output('S');
    }

    /** Authored-in-app documents: body + certificate rendered together. */
    private static function renderHtmlDocument(EmployeeDocument $doc): string
    {
        return Pdf::loadView('pdf.signed-document', [
            'doc' => $doc,
            'reference' => self::reference($doc),
            'fields' => self::readableFields($doc),
        ])->setPaper('a4')->output();
    }

    private static function drawCertificate(Fpdi $pdf, EmployeeDocument $doc, float $w): void
    {
        $ist = fn ($d) => $d ? $d->timezone('Asia/Kolkata')->format('d M Y, H:i:s').' IST' : '—';
        $emp = $doc->employee;

        $pdf->SetTextColor(20, 25, 40);
        $pdf->SetFont('Helvetica', 'B', 15);
        $pdf->SetXY(15, 20);
        $pdf->Cell($w - 30, 8, self::latin1('Electronic Signature Certificate'), 0, 1, 'L');

        $pdf->SetFont('Helvetica', '', 8.5);
        $pdf->SetTextColor(90, 95, 105);
        $pdf->SetX(15);
        $pdf->MultiCell($w - 30, 4.2, self::latin1(
            'This certificate forms part of, and is issued with, the document it is attached to. '.
            'It records an electronic signature made under the Information Technology Act, 2000.'), 0, 'L');

        $rows = [
            ['Document', $doc->documentTemplate->name],
            ['Document version', 'v'.$doc->version->version.'  ('.strtoupper($doc->language).')'],
            ['Signed by', $doc->signer_typed_name],
            ['Employee', $emp->name.'  ('.$emp->employee_code.')'],
            ['Designation', $emp->designation ?: ($emp->jobRole->name ?? '—')],
            ['Date & time', $ist($doc->signed_at)],
            ['Place of signing', $doc->signing_place ?: ($doc->signingOutlet->name ?? $emp->outlet->name ?? '—')],
            ['Method', 'Typed-name electronic signature, in person'],
            ['Witnessed by', $doc->recordedBy->name ?? '—'],
            ['IP address', $doc->signed_ip ?: '—'],
            ['Device', \Illuminate\Support\Str::limit($doc->signed_user_agent ?: '—', 78)],
            ['Reference', self::reference($doc)],
        ];

        foreach (self::readableFields($doc) as $label => $value) {
            $rows[] = [$label, $value];
        }

        $y = 42;
        foreach ($rows as [$label, $value]) {
            $pdf->SetFont('Helvetica', 'B', 8.5);
            $pdf->SetTextColor(90, 95, 105);
            $pdf->SetXY(15, $y);
            $pdf->Cell(45, 5.6, self::latin1($label), 0, 0, 'L');

            $pdf->SetFont('Helvetica', '', 9);
            $pdf->SetTextColor(20, 25, 40);
            $pdf->SetXY(60, $y);
            $pdf->MultiCell($w - 75, 5.6, self::latin1((string) $value), 0, 'L');

            $y = max($pdf->GetY(), $y + 5.6) + 0.6;
            $pdf->SetDrawColor(228, 232, 238);
            $pdf->Line(15, $y - 0.6, $w - 15, $y - 0.6);
        }

        $y += 5;
        $pdf->SetFont('Helvetica', 'B', 8.5);
        $pdf->SetTextColor(90, 95, 105);
        $pdf->SetXY(15, $y);
        $pdf->Cell($w - 30, 5, self::latin1('Integrity check (SHA-256)'), 0, 1);
        $pdf->SetFont('Courier', '', 7.5);
        $pdf->SetTextColor(20, 25, 40);
        $pdf->SetX(15);
        $pdf->MultiCell($w - 30, 4, self::latin1(self::pendingHashNote()), 0, 'L');

        $pdf->SetFont('Helvetica', '', 7);
        $pdf->SetTextColor(120, 125, 135);
        $pdf->SetXY(15, $pdf->GetY() + 6);
        $pdf->MultiCell($w - 30, 3.6, self::latin1(
            'Traverse Inc.  |  Confidential  |  Retained in the Traverse HR system. '.
            'The signatory retains permanent access to this document through their personal document link.'),
            0, 'L');
    }

    /**
     * The final PDF cannot contain its own hash (adding it would change it),
     * so the certificate points at the stored hash, which is what the
     * verification screen compares against.
     */
    private static function pendingHashNote(): string
    {
        return 'Recorded in the Traverse HR audit log at the time of signing and shown on the '
            .'document verification screen. Any change to this file will not match that value.';
    }

    /** Turns stored field answers into something a human reads on the page. */
    public static function readableFields(EmployeeDocument $doc): array
    {
        $out = [];
        $schema = collect($doc->version->field_schema ?? [])->keyBy('name');

        foreach ($doc->field_values ?? [] as $name => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $label = $schema[$name]['label'] ?? ucfirst(str_replace('_', ' ', $name));
            $out[$label] = match (true) {
                $value === 'yes', $value === true, $value === '1' => 'Yes',
                $value === 'no', $value === false, $value === '0' => 'No',
                $value === 'consent' => 'Consents',
                $value === 'do_not_consent' => 'Does NOT consent',
                default => (string) $value,
            };
        }

        return $out;
    }

    /**
     * FPDF core fonts are Latin-1 only — an unsanitised rupee sign renders
     * as mojibake ("a<>"). Transliterate the characters we actually emit
     * rather than silently corrupting money amounts on a legal document.
     */
    private static function latin1(string $text): string
    {
        $text = strtr($text, [
            "\u{20B9}" => 'Rs.', "\u{2014}" => '-', "\u{2013}" => '-',
            "\u{2019}" => "'", "\u{2018}" => "'", "\u{201C}" => '"',
            "\u{201D}" => '"', "\u{2026}" => '...', "\u{2022}" => '-',
            "\u{00A0}" => ' ',
        ]);

        return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
    }

    public static function reference(EmployeeDocument $doc): string
    {
        return sprintf('THR-%s-%06d', $doc->employee->employee_code ?? 'EMP', $doc->id);
    }
}
