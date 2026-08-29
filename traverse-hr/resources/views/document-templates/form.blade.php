@extends('layouts.app')
@section('title', $documentTemplate->exists ? 'Edit document type' : 'Add document type')
@section('content')
<h1>{{ $documentTemplate->exists ? 'Edit document type' : 'Add document type' }}</h1>
<div class="card" style="max-width:560px;">
    <form method="POST" action="{{ $documentTemplate->exists ? route('document-templates.update', $documentTemplate) : route('document-templates.store') }}">
        @csrf
        @if ($documentTemplate->exists) @method('PUT') @endif
        <label for="name">Name</label>
        <input id="name" name="name" value="{{ old('name', $documentTemplate->name) }}" required>
        <label for="kind">Kind</label>
        <select id="kind" name="kind" required>
            @foreach (['acknowledge_only' => 'Acknowledge only (read & sign)', 'sign_with_fields' => 'Sign with fields (fill in, then sign)', 'upload_required' => 'Upload required (staff/HR uploads a scan)'] as $value => $label)
                <option value="{{ $value }}" @selected(old('kind', $documentTemplate->kind) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <label for="category">Category (optional grouping)</label>
        <input id="category" name="category" value="{{ old('category', $documentTemplate->category) }}" placeholder="e.g. Compliance, Statutory, Onboarding">
        <label style="display:flex; align-items:center; gap:.4rem; flex-direction:row;">
            <input type="checkbox" name="conditional" value="1" style="width:auto;" @checked(old('conditional', $documentTemplate->conditional))> <span>Conditional / "if applicable" (e.g. Cash & Float Handling)</span>
        </label>
        <label style="display:flex; align-items:center; gap:.4rem; flex-direction:row;">
            <input type="checkbox" name="active" value="1" style="width:auto;" @checked(old('active', $documentTemplate->active ?? true))> <span>Active</span>
        </label>
        <button type="submit" class="btn" style="margin-top:1rem;">Save</button>
    </form>
</div>

@if ($documentTemplate->exists)
<h2>Variants & content</h2>
<p class="muted">Upload the actual document (PDF/DOCX) for each variant and language. A new upload creates a new version — it never overwrites what was already sent to an employee.</p>

@foreach ($documentTemplate->variants as $variant)
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="font-size:1rem; margin:0;">{{ $variant->label }} @if($variant->is_default)<span class="muted">(default)</span>@endif</h3>
        @unless ($variant->is_default)
        <form method="POST" action="{{ route('document-template-variants.make-default', $variant) }}">
            @csrf
            <button type="submit" class="btn secondary" style="padding:.2rem .6rem;">Make default</button>
        </form>
        @endunless
    </div>
    <table style="margin-top:.5rem;">
        <thead><tr><th>Language</th><th>Version</th><th>Content</th><th>Saved</th></tr></thead>
        <tbody>
        @forelse ($variant->versions as $version)
            <tr>
                <td data-label="Language">{{ strtoupper($version->language) }}</td>
                <td data-label="Version">v{{ $version->version }}</td>
                <td data-label="Content">
                    @if ($version->source_file_path)
                        <a href="{{ route('document-template-versions.download', $version) }}">{{ $version->source_file_original_name }}</a>
                    @elseif ($version->body_html)
                        <span class="muted">Written content ({{ \Illuminate\Support\Str::words(strip_tags($version->body_html), 8) }})</span>
                    @else
                        <span class="muted">—</span>
                    @endif
                </td>
                <td data-label="Saved">{{ $version->created_at->format('d M Y, H:i') }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">No content yet — placeholder. Upload a file or write the policy content below.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>

    <form method="POST" action="{{ route('document-template-variants.versions.store', $variant) }}" enctype="multipart/form-data" style="margin-top:.6rem; display:flex; gap:.5rem; align-items:flex-end; flex-wrap:wrap;">
        @csrf
        <div>
            <label for="language-{{ $variant->id }}">Language</label>
            <select id="language-{{ $variant->id }}" name="language">
                <option value="en">English</option>
                <option value="hi">हिन्दी (Hindi)</option>
                <option value="mr">मराठी (Marathi)</option>
            </select>
        </div>
        <div style="flex:1; min-width:200px;">
            <label for="file-{{ $variant->id }}">Upload a file (PDF/DOCX)</label>
            <input id="file-{{ $variant->id }}" type="file" name="file" accept=".pdf,.doc,.docx" required>
        </div>
        <button type="submit" class="btn" style="height:2.4rem;">Upload version</button>
    </form>

    @php $latestEn = $variant->versions->where('language', 'en')->sortByDesc('version')->first(); @endphp
    <details style="margin-top:.8rem;" @if(!$variant->versions->count()) open @endif>
        <summary style="cursor:pointer; font-size:.9rem; color:var(--muted);">Or write / edit the content directly (English) — for policies that change often, like the HR Policy Manual</summary>
        <form method="POST" action="{{ route('document-template-variants.text-versions.store', $variant) }}" style="margin-top:.6rem;">
            @csrf
            <input type="hidden" name="language" value="en">
            <label for="body_html-{{ $variant->id }}">Policy content</label>
            <textarea id="body_html-{{ $variant->id }}" name="body_html" rows="8" placeholder="Paste or write the current policy text here. Saving creates a new version — staff who already signed an earlier version keep seeing what they actually signed.">{{ old('body_html', $latestEn->body_html ?? '') }}</textarea>
            <button type="submit" class="btn" style="margin-top:.6rem;">Save content{{ $latestEn ? ' as new version' : '' }}</button>
        </form>
    </details>
</div>
@endforeach

<div class="card" style="max-width:420px;">
    <h3 style="font-size:1rem; margin-top:0;">Add another variant</h3>
    <form method="POST" action="{{ route('document-templates.variants.store', $documentTemplate) }}">
        @csrf
        <label for="label">Label (e.g. Manager, Housekeeping)</label>
        <input id="label" name="label" required>
        <button type="submit" class="btn" style="margin-top:.6rem;">Add variant</button>
    </form>
</div>
@endif
@endsection
