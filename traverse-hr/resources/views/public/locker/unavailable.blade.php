@extends('layouts.public')
@section('title', 'Document not ready')
@section('content')
<p><a href="{{ route('locker.show', $token) }}">&larr; Back to my documents</a></p>
<div class="card">
    <h2 style="font-size:1rem; margin-top:0;">{{ $employeeDocument->documentTemplate->name }}</h2>
    <p>Your acceptance of this document on
       <strong>{{ $employeeDocument->signed_at->timezone('Asia/Kolkata')->format('d M Y') }}</strong>
       is recorded and safe.</p>
    <p class="muted">The signed PDF copy is still being prepared. Please check back shortly, or ask HR to regenerate it.</p>
</div>
@endsection
