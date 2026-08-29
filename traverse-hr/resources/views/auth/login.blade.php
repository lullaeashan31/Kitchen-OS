@extends('layouts.app')
@section('title', 'Log in')
@section('content')
<div class="slim">
    <div class="card">
        <h1>{{ config('app.name') }}</h1>
        <p class="muted">Internal sign-in. Staff don't have accounts — they receive a personal link to their documents.</p>

        <form method="POST" action="{{ route('login') }}" class="mt">
            @csrf
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   autocomplete="username" required autofocus>

            <label for="password">Password</label>
            <input id="password" type="password" name="password" autocomplete="current-password" required>

            <label class="check">
                <input type="checkbox" name="remember">
                <span>Keep me signed in</span>
            </label>

            <button type="submit" class="btn block mt-lg">Log in</button>
        </form>
    </div>
</div>
@endsection
