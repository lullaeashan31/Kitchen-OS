@extends('layouts.app')
@section('title', 'Document types')
@section('content')
<div style="display:flex; justify-content:space-between; align-items:center;">
    <h1>Document types</h1>
    <a href="{{ route('document-templates.create') }}" class="btn">Add document type</a>
</div>
<p class="muted">Each type can have multiple variants (e.g. Default, Manager, Housekeeping) — assign which one a job role gets from that role's "Documents" tab.</p>
<div class="card">
    <div class="table-wrap">
    <table>
        <thead><tr><th>Name</th><th>Kind</th><th>Variants</th><th>Active</th><th></th></tr></thead>
        <tbody>
        @foreach ($documentTemplates as $template)
            <tr>
                <td data-label="Name">{{ $template->name }}</td>
                <td data-label="Kind">{{ str_replace('_', ' ', $template->kind) }}</td>
                <td data-label="Variants">
                    @foreach ($template->variants as $variant)
                        {{ $variant->label }}@if($variant->is_default) <span class="muted">(default)</span>@endif{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </td>
                <td data-label="Active">{{ $template->active ? 'Yes' : 'No' }}</td>
                <td data-label="Edit"><a href="{{ route('document-templates.edit', $template) }}">Manage</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    </div>
</div>
@endsection
