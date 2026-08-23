<?php

namespace App\Http\Controllers;

use App\Models\DocumentTemplateVersion;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * The only way a document's source file is ever served — never a direct
 * public URL, never a guessable filename (§6). Every download is logged.
 */
class DocumentTemplateVersionController extends Controller
{
    public function download(Request $request, DocumentTemplateVersion $version)
    {
        abort_unless($request->user()->can('document.view'), 403);
        abort_unless($version->source_file_path && Storage::disk('local')->exists($version->source_file_path), 404);

        AuditLogger::log('document_template_version_downloaded', $version);

        return Storage::disk('local')->download($version->source_file_path, $version->source_file_original_name ?? 'document.pdf');
    }
}
