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
            {!! $version->body_html !!}
        </div>
    @else
        <p class="muted">No content available for this document yet.</p>
    @endif
</div>

@if ($record?->status === 'signed')
<div class="card">
    <p style="color:#146c43; margin:0;">Accepted by <strong>{{ $record->signer_typed_name }}</strong> on {{ $record->signed_at->format('d M Y, H:i') }} (recorded by {{ $record->recordedBy?->name ?? 'system' }}).</p>
</div>
@else
<div class="card" style="max-width:480px;">
    <h2 style="font-size:1rem; margin-top:0;">Accept this document</h2>
    <p class="muted">Have the employee type their own name below, in front of you, to confirm they have read and accept it.</p>
    <form method="POST" action="{{ route('employees.documents.accept', [$employee, $documentTemplate]) }}">
        @csrf

        @foreach ($version->field_schema ?? [] as $field)
            <label for="field-{{ $field['name'] }}">{{ $field['label'] }}</label>
            @if ($field['type'] === 'textarea')
                <textarea id="field-{{ $field['name'] }}" name="fields[{{ $field['name'] }}]">{{ $field['default'] ?? '' }}</textarea>
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
                <input id="field-{{ $field['name'] }}" name="fields[{{ $field['name'] }}]" value="{{ $field['default'] ?? '' }}">
            @endif
        @endforeach

        <label for="signer_typed_name">Employee's typed name (this is their acceptance)</label>
        <input id="signer_typed_name" name="signer_typed_name" required autocomplete="off" value="{{ old('signer_typed_name', $employee->name) }}">
        <button type="submit" class="btn" style="margin-top:1rem;">Record acceptance</button>
    </form>
</div>
@endif

<p><a href="{{ route('employees.documents.index', $employee) }}">&larr; Back to document checklist</a></p>
@endsection
