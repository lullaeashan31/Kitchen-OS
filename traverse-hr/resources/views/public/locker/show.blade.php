@extends('layouts.public')
@section('title', 'My documents')
@section('content')
<div class="card">
    <p style="margin:0;">Hi <strong>{{ $employee->name }}</strong> — here are the documents you've signed. Tap any one to open the signed copy. This link is yours to keep.</p>
</div>

@forelse ($documents as $doc)
    <div class="doc-link" style="display:flex; justify-content:space-between; align-items:center; gap:.6rem;">
        <a href="{{ route('locker.document', [$token, $doc]) }}" style="text-decoration:none; color:inherit; flex:1;">
            <div class="name">{{ $doc->documentTemplate->name }}</div>
            <div class="meta">
                Signed {{ $doc->signed_at->timezone('Asia/Kolkata')->format('d M Y') }}
                @if ($doc->isSuperseded())
                    &middot; <span style="color:#8a6d3b;">replaced by a newer version</span>
                @endif
            </div>
        </a>
        <a href="{{ route('locker.verify', [$token, $doc]) }}" class="muted" style="font-size:.78rem; white-space:nowrap;">Verify</a>
    </div>
@empty
    <p class="muted">No signed documents yet.</p>
@endforelse
@endsection
