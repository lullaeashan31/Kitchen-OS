<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Seeds the 16 documents from the owner's real "Manager Onboarding Kit"
 * PDF (split one-file-per-document under database/seed-documents/). Per
 * the owner's instruction, every type gets one "Default" variant used for
 * all roles for now — assign a role-specific variant later from
 * Admin → Job roles → Documents.
 *
 * Two items from the kit's own checklist are NOT seeded here — see
 * DECISIONS.md:
 *  - "Letter of Appointment" — this is the offer/appointment letter
 *    itself, which belongs to the separate Offer module (§3.2), not the
 *    onboarding document pack. Still waiting on that source document.
 *  - "Stores & Inventory Custody Accountability Undertaking (if
 *    applicable)" — referenced in the kit's checklist but no such
 *    document was actually included in the PDF provided.
 */
class DocumentTemplateSeeder extends Seeder
{
    private const SOURCE_DIR = __DIR__.'/../seed-documents/manager-onboarding-kit';

    public function run(): void
    {
        if (! is_dir(self::SOURCE_DIR)) {
            return;
        }

        foreach ($this->definitions() as $definition) {
            $this->seedOne($definition);
        }
    }

    private function definitions(): array
    {
        return [
            [
                'name' => 'Onboarding Document Acknowledgement',
                'kind' => 'acknowledge_only',
                'category' => 'Onboarding',
                'file' => 'onboarding-document-acknowledgement.pdf',
            ],
            [
                'name' => 'Acceptance of Appointment',
                'kind' => 'acknowledge_only',
                'category' => 'Employment',
                'file' => 'acceptance-of-appointment.pdf',
            ],
            [
                'name' => 'Trial Period & Probation Terms',
                'kind' => 'acknowledge_only',
                'category' => 'Employment',
                'file' => 'trial-period-probation-terms.pdf',
            ],
            [
                'name' => 'Confidentiality & Information-Protection Undertaking',
                'kind' => 'acknowledge_only',
                'category' => 'Compliance',
                'file' => 'confidentiality-information-protection-undertaking.pdf',
            ],
            [
                'name' => 'Non-Solicitation Undertaking',
                'kind' => 'acknowledge_only',
                'category' => 'Compliance',
                'file' => 'non-solicitation-undertaking.pdf',
            ],
            [
                'name' => 'Conflict of Interest, Anti-Bribery & Gifts Undertaking',
                'kind' => 'sign_with_fields',
                'category' => 'Compliance',
                'file' => 'conflict-of-interest-anti-bribery-gifts-undertaking.pdf',
                'field_schema' => [
                    ['name' => 'declared_interests', 'label' => 'Declared interests', 'type' => 'text', 'default' => 'None'],
                ],
            ],
            [
                'name' => 'Cash & Float Handling Undertaking',
                'kind' => 'sign_with_fields',
                'category' => 'Compliance',
                'conditional' => true,
                'file' => 'cash-float-handling-undertaking.pdf',
                'field_schema' => [
                    ['name' => 'float_amount', 'label' => 'Float / till assigned (₹)', 'type' => 'number'],
                ],
            ],
            [
                'name' => "Manager's Authority & Accountability Acknowledgement",
                'kind' => 'acknowledge_only',
                'category' => 'Management',
                'conditional' => true,
                'file' => 'managers-authority-accountability-acknowledgement.pdf',
            ],
            [
                'name' => 'IT & Systems Acceptable-Use Undertaking',
                'kind' => 'acknowledge_only',
                'category' => 'Compliance',
                'file' => 'it-systems-acceptable-use-undertaking.pdf',
            ],
            [
                'name' => 'Drug & Alcohol (Fit for Duty) Policy',
                'kind' => 'acknowledge_only',
                'category' => 'Compliance',
                'file' => 'drug-alcohol-fit-for-duty-policy.pdf',
            ],
            [
                'name' => 'POSH Policy',
                'kind' => 'acknowledge_only',
                'category' => 'Compliance',
                'file' => 'posh-policy.pdf',
            ],
            [
                'name' => 'HR Handbook & Code of Conduct — Receipt',
                'kind' => 'acknowledge_only',
                'category' => 'Onboarding',
                'file' => 'hr-handbook-code-of-conduct-receipt.pdf',
            ],
            [
                'name' => 'Background & Reference Check Consent',
                'kind' => 'acknowledge_only',
                'category' => 'Compliance',
                'file' => 'background-reference-check-consent.pdf',
            ],
            [
                'name' => 'Media & Image Consent',
                'kind' => 'sign_with_fields',
                'category' => 'Consent',
                'file' => 'media-image-consent.pdf',
                'field_schema' => [
                    ['name' => 'consent_choice', 'label' => 'Consent', 'type' => 'select', 'options' => ['consent', 'do_not_consent']],
                ],
            ],
            [
                'name' => 'Company Property Issue & Return Form',
                'kind' => 'sign_with_fields',
                'category' => 'Onboarding',
                'file' => 'company-property-issue-return-form.pdf',
                'field_schema' => [
                    ['name' => 'items_issued_detail', 'label' => 'Items issued (detail)', 'type' => 'textarea'],
                ],
            ],
            [
                'name' => 'Food Handler Health Declaration & Medical Fitness Undertaking',
                'kind' => 'sign_with_fields',
                'category' => 'Statutory',
                'conditional' => true,
                'file' => 'food-handler-health-declaration-medical-fitness.pdf',
                'field_schema' => [
                    ['name' => 'has_existing_certificate', 'label' => 'Already holds a valid medical fitness certificate', 'type' => 'boolean'],
                ],
            ],
        ];
    }

    private function seedOne(array $definition): void
    {
        $sourcePath = self::SOURCE_DIR.'/'.$definition['file'];

        if (! is_file($sourcePath)) {
            return;
        }

        $template = DocumentTemplate::firstOrCreate(
            ['name' => $definition['name']],
            [
                'kind' => $definition['kind'],
                'category' => $definition['category'],
                'conditional' => $definition['conditional'] ?? false,
                'active' => true,
            ]
        );

        $variant = $template->variants()->firstOrCreate(['label' => 'Default'], ['is_default' => true]);

        if ($variant->versions()->where('language', 'en')->exists()) {
            return; // already seeded
        }

        $storedPath = 'document-template-versions/seed-'.$template->id.'-'.$definition['file'];
        Storage::disk('local')->put($storedPath, file_get_contents($sourcePath));

        $variant->versions()->create([
            'language' => 'en',
            'version' => 1,
            'source_file_path' => $storedPath,
            'source_file_original_name' => $definition['file'],
            'field_schema' => $definition['field_schema'] ?? null,
            'active' => true,
        ]);
    }
}
