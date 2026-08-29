@extends('layouts.app')
@section('title', 'Document locker — '.$employee->name)
@section('content')
<h1>Document locker — {{ $employee->name }}</h1>
<p class="muted">Permanent link — {{ $employee->name }} can open this any time to see everything they've signed. Scan the QR code, or copy the link/WhatsApp message below and send it to them.</p>

<div class="card" style="max-width:420px; text-align:center;">
    {!! $qrSvg !!}
</div>

<div class="card" style="max-width:560px;">
    <label for="locker-url">Link</label>
    <input id="locker-url" value="{{ $url }}" readonly onclick="this.select()">

    <label for="wa-link" style="margin-top:.8rem;">WhatsApp — tap to open with a prewritten message</label>
    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="btn" style="display:block; text-align:center; margin-top:.4rem;">Open in WhatsApp</a>

    <form method="POST" action="{{ route('employees.locker.regenerate', $employee) }}" style="margin-top:1rem;" onsubmit="return confirm('This invalidates the current link/QR — anyone using the old one loses access. Continue?');">
        @csrf
        <button type="submit" class="btn secondary">Regenerate link (revokes the old one)</button>
    </form>
</div>

<p><a href="{{ route('employees.documents.index', $employee) }}">&larr; Back to documents</a></p>
@endsection
