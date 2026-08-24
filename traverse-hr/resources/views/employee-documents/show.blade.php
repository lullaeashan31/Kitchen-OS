@extends('layouts.app')
@section('title', $documentTemplate->name)
@section('content')
<h1 style="font-size:1.3rem;">{{ $documentTemplate->name }}</h1>
<p class="muted">For {{ $employee->name }} ({{ $employee->employee_code }}) &middot; variant: {{ $variant->label }} &middot; v{{ $version->version }}</p>

<div class="card">
    @if ($version->source_file_path)
        <p>This document is a file. Open it with the employee so they can read it in full, then have them type their name below to accept.</p>
        <a href="{{ route('document-template-versions.download', $version) }}" class="btn" target="_blank" rel="noopener">Open document</a>
    @elseif ($version->body_html)
        <div style="border:1px solid var(--line); border-radius:8px; padding:1rem; max-height:400px; overflow-y:auto;">
            {!! \App\Services\HtmlSanitizer::clean($version->body_html) !!}
        </div>
    @else
        <p class="muted">No content available for this document yet.</p>
    @endif
</div>

@if ($record?->status === 'signed')
<div class="card">
    <p style="color:#146c43; margin:0 0 .6rem;">
        Accepted by <strong>{{ $record->signer_typed_name }}</strong>
        on {{ $record->signed_at->timezone('Asia/Kolkata')->format('d M Y, H:i') }} IST
        at {{ $record->signing_place ?? '—' }}
        (witnessed by {{ $record->recordedBy?->name ?? 'system' }}).
    </p>

    @if ($record->signed_pdf_path)
        <a href="{{ route('signed-documents.download', $record) }}" class="btn">Download signed PDF</a>
        <p class="muted" style="margin-top:.6rem;">
            Reference {{ \App\Services\SignedDocumentGenerator::reference($record) }} &middot;
            SHA-256 <code style="font-size:.72rem;">{{ \Illuminate\Support\Str::limit($record->signed_pdf_sha256, 24) }}</code>
        </p>
    @else
        <p class="muted">The signed PDF has not been generated for this record.</p>
    @endif

    <form method="POST" action="{{ route('signed-documents.regenerate', $record) }}" style="margin-top:.4rem;">
        @csrf
        <button type="submit" class="btn secondary">Regenerate signed PDF</button>
    </form>

    <p class="muted" style="margin-top:.8rem;">
        Need them to re-accept an updated version? Use the form below — the current acceptance is kept on record, not replaced.
    </p>
</div>

@if ($history->isNotEmpty())
<div class="card">
    <h2 style="font-size:1rem; margin-top:0;">Previous acceptances</h2>
    <p class="muted">Retained permanently — superseded, never deleted.</p>
    <table>
        <thead><tr><th>Signed by</th><th>When</th><th>Version</th><th></th></tr></thead>
        <tbody>
        @foreach ($history as $old)
            <tr>
                <td data-label="Signed by">{{ $old->signer_typed_name }}</td>
                <td data-label="When">{{ $old->signed_at->timezone('Asia/Kolkata')->format('d M Y, H:i') }}</td>
                <td data-label="Version">v{{ $old->version->version }}</td>
                <td data-label="Download">
                    @if ($old->signed_pdf_path)<a href="{{ route('signed-documents.download', $old) }}">Download</a>@endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif
@endif

<div class="card" style="max-width:480px;">
    <h2 style="font-size:1rem; margin-top:0;">{{ $record ? 'Re-accept this document' : 'Accept this document' }}</h2>
    <p class="muted">Have the employee type their own name below, in front of you, to confirm they have read and accept it.</p>
    <form method="POST" action="{{ route('employees.documents.accept', [$employee, $documentTemplate]) }}">
        @csrf

        @foreach ($version->field_schema ?? [] as $field)
            <label for="field-{{ $field['name'] }}">{{ $field['label'] }}</label>
            @if ($field['type'] === 'textarea')
                <textarea id="field-{{ $field['name'] }}" name="fields[{{ $field['name'] }}]">{{ old('fields.'.$field['name'], data_get($record?->field_values, $field['name'], $field['default'] ?? '')) }}</textarea>
            @elseif ($field['type'] === 'boolean')
                <select id="field-{{ $field['name'] }}" name="fields[{{ $field['name'] }}]">
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>
            @elseif ($field['type'] === 'select')
                <select id="field-{{ $field['name'] }}" name="fields[{{ $field['name'] }}]">
                    @foreach ($field['options'] as $option)
                        <option value="{{ $option }}">{{ ucfirst(str_replace('_', ' ', $option)) }}</option>
                    @endforeach
                </select>
            @else
                <input id="field-{{ $field['name'] }}" name="fields[{{ $field['name'] }}]" value="{{ old('fields.'.$field['name'], data_get($record?->field_values, $field['name'], $field['default'] ?? '')) }}">
            @endif
        @endforeach

        <label for="signer_typed_name">Name in block letters</label>
        <input id="signer_typed_name" name="signer_typed_name" required autocomplete="off" value="{{ old('signer_typed_name', $employee->name) }}">

        <div style="margin-top:.8rem;">
            @include('partials.signature-pad', ['label' => "Employee's signature", 'required' => true])
        </div>

        @if ($signatories->isNotEmpty())
            <label for="company_signatory_id">Signing on behalf of Traverse Inc.</label>
            <select id="company_signatory_id" name="company_signatory_id">
                @foreach ($signatories as $s)
                    <option value="{{ $s->id }}" @selected($s->is_default)>{{ $s->name }} — {{ $s->designation }}</option>
                @endforeach
            </select>
            <p class="muted">Their signature is placed on the document automatically.</p>
        @else
            <p class="muted" style="margin-top:.6rem;">
                No company signatory set up yet — the document will record the employee's signature only.
                @can('admin.settings.manage')<a href="{{ route('company-signatories.index') }}">Add one</a>.@endcan
            </p>
        @endif

        <button type="submit" class="btn" style="margin-top:1rem;">Record acceptance</button>
    </form>
</div>

<p><a href="{{ route('employees.documents.index', $employee) }}">&larr; Back to document checklist</a></p>
@endsection
