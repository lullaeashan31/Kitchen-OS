@extends('layouts.app')
@section('title', $documentTemplate->name)
@section('content')

<div class="page-head">
    <div>
        <h1>{{ $documentTemplate->name }}</h1>
        <p class="sub">
            {{ $employee->name }} ({{ $employee->employee_code }})
            &middot; {{ $variant->label }} &middot; v{{ $version->version }}
        </p>
    </div>
    <div class="actions">
        <a href="{{ route('employees.documents.index', $employee) }}" class="btn secondary">All documents</a>
    </div>
</div>

{{-- 1. The document itself --}}
<div class="card">
    @if ($version->source_file_path)
        <h2>Read it together</h2>
        <p class="muted">Open the document and let the employee read it in full before they sign.</p>
        <a href="{{ route('document-template-versions.download', $version) }}"
           class="btn secondary mt" target="_blank" rel="noopener">Open document</a>
    @elseif ($version->body_html)
        <div style="max-height:380px; overflow-y:auto; border:1px solid var(--line);
                    border-radius:var(--radius); padding:1.1rem; background:var(--surface-sunk);">
            {!! \App\Services\HtmlSanitizer::clean($version->body_html) !!}
        </div>
    @else
        <div class="empty">
            <h3>Nothing to show</h3>
            <p>This document type has no content uploaded yet.</p>
        </div>
    @endif
</div>

{{-- 2. Existing signature, if any --}}
@if ($record?->status === 'signed')
    <div class="card">
        <div class="row">
            <span class="pill ok">Signed</span>
            <span class="muted">
                {{ $record->signed_at->timezone('Asia/Kolkata')->format('d M Y, H:i') }} IST
                @if ($record->signing_place) &middot; {{ $record->signing_place }} @endif
            </span>
        </div>

        <p class="mt">
            <strong>{{ $record->signer_typed_name }}</strong>
            @if ($record->company_signatory_name)
                &middot; countersigned by {{ $record->company_signatory_name }} ({{ $record->company_signatory_designation }})
            @endif
            <br>
            <span class="muted">Witnessed by {{ $record->recordedBy?->name ?? 'system' }}</span>
        </p>

        @if ($record->signed_pdf_path)
            <div class="row mt">
                <a href="{{ route('signed-documents.download', $record) }}" class="btn">Download signed PDF</a>
                <form method="POST" action="{{ route('signed-documents.regenerate', $record) }}">
                    @csrf
                    <button type="submit" class="btn plain">Regenerate</button>
                </form>
            </div>
            <p class="hint">
                Ref {{ \App\Services\SignedDocumentGenerator::reference($record) }}
                &middot; SHA-256 <span class="masked">{{ \Illuminate\Support\Str::limit($record->signed_pdf_sha256, 24) }}</span>
            </p>
        @else
            <div class="notice mt">
                <div>The signed PDF hasn't been generated for this record.
                    <form method="POST" action="{{ route('signed-documents.regenerate', $record) }}" style="display:inline">
                        @csrf<button type="submit" class="linkbtn">Generate it now</button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    @if ($history->isNotEmpty())
        <div class="card card-flush">
            <div style="padding:1.25rem 1.25rem .25rem;">
                <h2>Earlier signatures</h2>
                <p class="muted">Kept permanently. Superseded, never deleted.</p>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Signed by</th><th>When</th><th>Version</th><th></th></tr></thead>
                    <tbody>
                    @foreach ($history as $old)
                        <tr>
                            <td class="td-primary" data-label="Signed by">{{ $old->signer_typed_name }}</td>
                            <td data-label="When">{{ $old->signed_at->timezone('Asia/Kolkata')->format('d M Y, H:i') }}</td>
                            <td data-label="Version">v{{ $old->version->version }}</td>
                            <td data-label="">
                                @if ($old->signed_pdf_path)
                                    <a href="{{ route('signed-documents.download', $old) }}">Download</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endif

{{-- 3. The signing session --}}
<div class="card" style="max-width:540px;">
    <h2>{{ $record ? 'Sign again' : 'Sign this document' }}</h2>
    <p class="muted">
        @if ($record)
            The signature on record is kept — this adds a new one rather than replacing it.
        @else
            Hand the employee the pad and have them sign in front of you.
        @endif
    </p>

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

        <label for="signer_typed_name">Full name</label>
        <input id="signer_typed_name" name="signer_typed_name" required autocomplete="off"
               value="{{ old('signer_typed_name', $employee->name) }}">
        <p class="hint">Printed under the signature on the certificate.</p>

        <div class="mt">
            @include('partials.signature-pad', ['label' => "Employee's signature", 'required' => true])
        </div>

        @if ($signatories->isNotEmpty())
            <label for="company_signatory_id">Signing for Traverse Inc.</label>
            <select id="company_signatory_id" name="company_signatory_id">
                @foreach ($signatories as $s)
                    <option value="{{ $s->id }}" @selected($s->is_default)>{{ $s->name }} — {{ $s->designation }}</option>
                @endforeach
            </select>
            <p class="hint">Their signature is placed on the document automatically.</p>
        @else
            <div class="notice mt">
                <div>
                    No company signatory set up — this will record the employee's signature only.
                    @can('admin.settings.manage')
                        <a href="{{ route('company-signatories.create') }}">Add one</a>.
                    @endcan
                </div>
            </div>
        @endif

        <button type="submit" class="btn block mt-lg">Record signature</button>
    </form>
</div>

@endsection
