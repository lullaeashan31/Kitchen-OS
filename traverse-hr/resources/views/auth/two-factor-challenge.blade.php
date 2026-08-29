@extends('layouts.app')
@section('title', 'Verify identity')
@section('content')
<div class="card" style="max-width:380px; margin:2rem auto;">
    <h1>Enter your 6-digit code</h1>
    <p class="muted">Or use one of your recovery codes.</p>
    <form method="POST" action="{{ route('two-factor.challenge') }}">
        @csrf
        <label for="code">Code</label>
        <input id="code" name="code" maxlength="20" required autofocus>
        <button type="submit" class="btn" style="margin-top:1rem; width:100%;">Verify</button>
    </form>
</div>
@endsection
