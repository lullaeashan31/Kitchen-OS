@extends('layouts.app')
@section('title', 'Set up two-factor authentication')
@section('content')
<div class="card" style="max-width:420px; margin:2rem auto;">
    <h1>Set up two-factor authentication</h1>
    <p class="muted">Your role requires 2FA. Scan this with Google Authenticator or Authy, then enter the 6-digit code.</p>
    <p class="muted">Can't scan? Enter this key manually: <code>{{ $secret }}</code></p>
    <p class="muted">otpauth URL (paste into an authenticator app that supports import): <br><code style="word-break:break-all;">{{ $qrUrl }}</code></p>
    <form method="POST" action="{{ route('two-factor.setup') }}">
        @csrf
        <label for="code">6-digit code</label>
        <input id="code" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" required autofocus>
        <button type="submit" class="btn" style="margin-top:1rem; width:100%;">Confirm & enable</button>
    </form>
</div>
@endsection
