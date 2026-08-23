@extends('layouts.app')
@section('title', 'Log in')
@section('content')
<div class="card" style="max-width:380px; margin:2rem auto;">
    <h1 style="font-size:1.2rem;">{{ config('app.name') }}</h1>
    <p class="muted">Internal staff sign-in. Staff members do not have an account — they receive a link.</p>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
        <label for="password">Password</label>
        <input id="password" type="password" name="password" required>
        <label style="display:flex; align-items:center; gap:.4rem; flex-direction:row;">
            <input type="checkbox" name="remember" style="width:auto;"> <span>Remember me</span>
        </label>
        <button type="submit" class="btn" style="margin-top:1rem; width:100%;">Log in</button>
    </form>
</div>
@endsection
