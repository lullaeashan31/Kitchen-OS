@extends('layouts.app')
@section('title', 'Recovery codes')
@section('content')
<div class="card" style="max-width:420px; margin:2rem auto;">
    <h1>Save your recovery codes</h1>
    <p class="muted">Each code can be used once if you lose access to your authenticator app. These are shown <strong>only once</strong> — save them somewhere safe now.</p>
    <ul style="font-family:monospace; font-size:1rem; line-height:1.8;">
        @foreach ($codes as $code)
            <li>{{ $code }}</li>
        @endforeach
    </ul>
    <a href="{{ route('dashboard') }}" class="btn" style="width:100%; text-align:center;">I've saved these — continue</a>
</div>
@endsection
