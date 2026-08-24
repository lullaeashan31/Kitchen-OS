@extends('layouts.app')
@section('title', $user->exists ? 'Edit user' : 'Add user')
@section('content')
<h1 style="font-size:1.3rem;">{{ $user->exists ? 'Edit user' : 'Add user' }}</h1>
<div class="card" style="max-width:560px;">
    <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}">
        @csrf
        @if ($user->exists) @method('PUT') @endif

        <label for="name">Full name</label>
        <input id="name" name="name" value="{{ old('name', $user->name) }}" required>

        <label for="email">Email (this is their login)</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>

        <label for="role">Role</label>
        <select id="role" name="role" required onchange="document.getElementById('outlet-row').style.display = this.value==='outlet_manager' ? 'block':'none';">
            @foreach ($roleLabels as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $currentRole) === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <div id="outlet-row" style="display:{{ old('role', $currentRole) === 'outlet_manager' ? 'block' : 'none' }};">
            <label for="outlet_id">Outlet (they will only see this outlet's data)</label>
            <select id="outlet_id" name="outlet_id">
                <option value="">— select —</option>
                @foreach ($outlets as $outlet)
                    <option value="{{ $outlet->id }}" @selected(old('outlet_id', $user->outlet_id) == $outlet->id)>{{ $outlet->name }}</option>
                @endforeach
            </select>
        </div>

        <label for="password">Password {{ $user->exists ? '(leave blank to keep current)' : '(min. 12 characters)' }}</label>
        <input id="password" name="password" type="password" autocomplete="new-password" @required(! $user->exists)>
        <label for="password_confirmation">Confirm password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" @required(! $user->exists)>

        @if ($user->exists)
            <label style="display:flex; align-items:center; gap:.4rem; flex-direction:row;">
                <input type="checkbox" name="active" value="1" style="width:auto;" @checked(old('active', $user->active))> <span>Active (can sign in)</span>
            </label>
        @endif

        <p class="muted" style="margin-top:.8rem;">Super Admin, HR and Accounts are required to set up two-factor authentication the first time they log in.</p>
        <button type="submit" class="btn" style="margin-top:.6rem;">Save</button>
    </form>
</div>

@if ($user->exists && $user->id !== auth()->id())
<div class="card" style="max-width:560px;">
    <h2 style="font-size:1rem; margin-top:0;">Deactivate</h2>
    <p class="muted">Removes their access. Their name stays on any document they witnessed — history is never deleted.</p>
    <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Deactivate {{ $user->name }}?');">
        @csrf @method('DELETE')
        <button type="submit" class="btn secondary">Deactivate user</button>
    </form>
</div>
@endif
<p><a href="{{ route('users.index') }}">&larr; Back to users</a></p>
@endsection
