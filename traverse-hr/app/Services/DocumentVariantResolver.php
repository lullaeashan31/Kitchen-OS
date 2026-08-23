<?php

namespace App\Services;

use App\Models\DocumentTemplate;
use App\Models\DocumentTemplateVariant;
use App\Models\JobRole;
use App\Models\JobRoleDocumentVariant;

/**
 * Answers "which variant of this document type does this job role get?" —
 * the dropdown behaviour the owner asked for. An explicit
 * job_role_document_variant_map row wins; otherwise falls back to the
 * type's variant marked is_default.
 */
class DocumentVariantResolver
{
    public static function resolve(JobRole $jobRole, DocumentTemplate $documentTemplate): ?DocumentTemplateVariant
    {
        $mapped = JobRoleDocumentVariant::where('job_role_id', $jobRole->id)
            ->where('document_template_id', $documentTemplate->id)
            ->first();

        if ($mapped) {
            return $mapped->variant;
        }

        return $documentTemplate->defaultVariant()->first();
    }
}
