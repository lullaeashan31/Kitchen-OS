<?php

namespace App\Http\Controllers;

use App\Models\CompanySignatory;
use App\Models\EmployeeDocument;
use App\Services\AuditLogger;
use App\Services\SignatureImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Admin → Company signatories. Manages the people who countersign
 * documents for the company, and their signature images.
 */
class CompanySignatoryController extends Controller
{
    public function index(Request $request)
    {
        $this->gate($request);

        return view('company-signatories.index', [
            'signatories' => CompanySignatory::orderByDesc('is_default')->orderBy('name')->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->gate($request);

        return view('company-signatories.form', ['signatory' => new CompanySignatory]);
    }

    public function store(Request $request)
    {
        $this->gate($request);
        $data = $this->validated($request);

        $signatory = CompanySignatory::create([
            'name' => $data['name'],
            'designation' => $data['designation'],
            'active' => true,
            'is_default' => CompanySignatory::count() === 0,
            'created_by' => $request->user()->id,
        ]);

        $note = $this->storeSignature($request, $signatory);
        AuditLogger::log('company_signatory_created', $signatory);

        return redirect()->route('company-signatories.index')
            ->with('status', trim("{$signatory->name} added. ".$note));
    }

    public function edit(Request $request, CompanySignatory $companySignatory)
    {
        $this->gate($request);

        return view('company-signatories.form', ['signatory' => $companySignatory]);
    }

    public function update(Request $request, CompanySignatory $companySignatory)
    {
        $this->gate($request);
        $data = $this->validated($request);

        $companySignatory->update([
            'name' => $data['name'],
            'designation' => $data['designation'],
            'active' => $request->boolean('active'),
        ]);

        $note = $this->storeSignature($request, $companySignatory);

        // A signatory who has been switched off must not stay the default,
        // or new documents silently go out with no countersignature.
        if (! $companySignatory->active && $companySignatory->is_default) {
            $companySignatory->update(['is_default' => false]);
            CompanySignatory::active()->orderBy('id')->first()?->update(['is_default' => true]);
        }

        AuditLogger::log('company_signatory_updated', $companySignatory);

        return redirect()->route('company-signatories.index')
            ->with('status', trim('Signatory updated. '.$note));
    }

    public function makeDefault(Request $request, CompanySignatory $companySignatory)
    {
        $this->gate($request);

        CompanySignatory::query()->update(['is_default' => false]);
        $companySignatory->update(['is_default' => true, 'active' => true]);
        AuditLogger::log('company_signatory_default_changed', $companySignatory);

        return back()->with('status', "{$companySignatory->name} now signs by default.");
    }

    /** Signature images are private; this is the only way they are served. */
    public function image(Request $request, CompanySignatory $companySignatory)
    {
        abort_unless($request->user()->can('document.view'), 403);
        abort_unless(
            $companySignatory->signature_image_path
                && Storage::disk('local')->exists($companySignatory->signature_image_path),
            404
        );

        return Storage::disk('local')->response($companySignatory->signature_image_path);
    }

    /** @return string a note for the user when a supplied signature could not be used */
    private function storeSignature(Request $request, CompanySignatory $signatory): string
    {
        $supplied = $request->filled('signature_capture') || $request->hasFile('signature_file');

        $binary = SignatureImage::fromRequest(
            $request->input('signature_capture'),
            $request->file('signature_file')
        );

        if ($binary === null) {
            // Don't fail silently: the Super Admin thinks they just uploaded
            // their signature and would otherwise never find out they hadn't.
            return $supplied
                ? 'The signature image could not be read (it may have been blank) — no signature was saved.'
                : '';
        }

        $path = 'company-signatures/'.$signatory->id.'-'.\Illuminate\Support\Str::random(10).'.png';
        Storage::disk('local')->put($path, $binary);

        $previous = $signatory->signature_image_path;
        $signatory->update(['signature_image_path' => $path]);

        // Never delete an image that an already-signed document points at —
        // that would strip the countersignature off signed paperwork.
        if ($previous && $previous !== $path
            && ! EmployeeDocument::where('company_signature_image_path', $previous)->exists()) {
            Storage::disk('local')->delete($previous);
        }

        return '';
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['required', 'string', 'max:255'],
            'signature_file' => ['nullable', 'image', 'max:2048'],
            'signature_capture' => ['nullable', 'string'],
        ]);
    }

    private function gate(Request $request): void
    {
        abort_unless($request->user()->can('admin.settings.manage'), 403);
    }
}
