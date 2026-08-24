@extends('layouts.public')
@section('title', 'Verify document')
@section('content')
<p><a href="{{ route('locker.show', $token) }}">&larr; Back to my documents</a></p>
<div class="card">
    <h2 style="font-size:1rem; margin-top:0;">{{ $employeeDocument->documentTemplate->name }}</h2>
    <p class="muted">Use this to confirm a copy of this document is genuine and unaltered.</p>
    <table style="width:100%; border-collapse:collapse; font-size:.85rem;">
        <tr><td style="padding:.4rem 0; color:var(--muted);">Signed by</td><td>{{ $employeeDocument->signer_typed_name }}</td></tr>
        <tr><td style="padding:.4rem 0; color:var(--muted);">Date &amp; time</td><td>{{ $employeeDocument->signed_at->timezone('Asia/Kolkata')->format('d M Y, H:i:s') }} IST</td></tr>
        <tr><td style="padding:.4rem 0; color:var(--muted);">Place</td><td>{{ $employeeDocument->signing_place ?? '—' }}</td></tr>
        <tr><td style="padding:.4rem 0; color:var(--muted);">Reference</td><td>{{ \App\Services\SignedDocumentGenerator::reference($employeeDocument) }}</td></tr>
        <tr><td style="padding:.4rem 0; color:var(--muted);">Status</td>
            <td>@if ($employeeDocument->isSuperseded())
                    Superseded on {{ $employeeDocument->superseded_at->timezone('Asia/Kolkata')->format('d M Y') }}
                @else Current @endif</td></tr>
    </table>
    <p class="muted" style="margin-top:1rem;">SHA-256 of the signed document</p>
    <code style="display:block; word-break:break-all; font-size:.72rem; background:#f7f8fa; padding:.6rem; border-radius:6px;">{{ $employeeDocument->signed_pdf_sha256 ?? 'Not available' }}</code>
    <p class="muted" style="margin-top:.6rem;">A downloaded copy is genuine only if its SHA-256 matches this value exactly.</p>
</div>
@endsection
