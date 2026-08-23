@extends('layouts.public')
@section('title', 'My documents')
@section('content')
<div class="card">
    <p style="margin:0;">Hi <strong>{{ $employee->name }}</strong> — here are the documents you've signed. Tap any one to open it. This link is yours to keep; bookmark it or keep the WhatsApp message.</p>
</div>

@forelse ($documents as $doc)
    <a class="doc-link" href="{{ route('locker.document', [$token, $doc]) }}">
        <div class="name">{{ $doc->documentTemplate->name }}</div>
        <div class="meta">Signed {{ $doc->signed_at->format('d M Y') }}</div>
    </a>
@empty
    <p class="muted">No signed documents yet.</p>
@endforelse
@endsection
