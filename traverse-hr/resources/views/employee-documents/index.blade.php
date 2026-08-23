@extends('layouts.app')
@section('title', 'Onboarding documents — '.$employee->name)
@section('content')
<h1 style="font-size:1.3rem;">Onboarding documents — {{ $employee->name }}</h1>
<p class="muted">Classic in-person session: open each document with the employee, they read it, then type their name to accept. No signature pad needed.</p>
<p><a href="{{ route('employees.locker.show', $employee) }}">View their document locker link / QR &rarr;</a></p>
<div class="card">
    <table>
        <thead><tr><th>Document</th><th>Status</th><th>Accepted by / on</th><th></th></tr></thead>
        <tbody>
        @foreach ($rows as $row)
            <tr>
                <td data-label="Document">
                    {{ $row['template']->name }}
                    @if ($row['variant']->label !== 'Default')<span class="muted">({{ $row['variant']->label }})</span>@endif
                    @if ($row['template']->conditional)<span class="muted">(if applicable)</span>@endif
                </td>
                <td data-label="Status">
                    @if ($row['record']?->status === 'signed')
                        <span style="color:#146c43;">Accepted</span>
                    @else
                        <span class="muted">Pending</span>
                    @endif
                </td>
                <td data-label="Accepted by / on">
                    @if ($row['record']?->status === 'signed')
                        {{ $row['record']->signer_typed_name }} &middot; {{ $row['record']->signed_at->format('d M Y, H:i') }}
                    @else
                        —
                    @endif
                </td>
                <td data-label="Open">
                    <a href="{{ route('employees.documents.show', [$employee, $row['template']]) }}">
                        {{ $row['record']?->status === 'signed' ? 'View' : 'Open & accept' }}
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
<p><a href="{{ route('employees.edit', $employee) }}">&larr; Back to employee</a></p>
@endsection
