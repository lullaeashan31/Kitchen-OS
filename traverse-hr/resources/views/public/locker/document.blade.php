@extends('layouts.public')
@section('title', $employeeDocument->documentTemplate->name)
@section('content')
<p><a href="{{ route('locker.show', $token) }}">&larr; Back to my documents</a></p>
<div class="card">
    <h2 style="font-size:1rem; margin-top:0;">{{ $employeeDocument->documentTemplate->name }}</h2>
    <p class="muted">Signed by you on {{ $employeeDocument->signed_at->format('d M Y, H:i') }}.</p>
    <div style="border-top:1px solid var(--line); padding-top:.8rem; margin-top:.8rem;">
        {!! \App\Services\HtmlSanitizer::clean($version->body_html) !!}
    </div>
</div>
@endsection
