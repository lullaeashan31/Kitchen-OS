@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap;">
    <h1 style="font-size:1.3rem;">Users</h1>
    <a href="{{ route('users.create') }}" class="btn">Add user</a>
</div>
<p class="muted">People who log in to this system. Staff members are not users — they access their documents by link.</p>
<div class="card">
    <table>
        <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Outlet</th><th>2FA</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @foreach ($users as $u)
            <tr>
                <td data-label="Name">{{ $u->name }}</td>
                <td data-label="Email">{{ $u->email }}</td>
                <td data-label="Role">{{ str_replace('_',' ', $u->roles->first()?->name ?? '—') }}</td>
                <td data-label="Outlet">{{ $u->outlet?->name ?? 'All outlets' }}</td>
                <td data-label="2FA">{{ $u->twoFactorEnabled() ? 'Enrolled' : 'Not set up' }}</td>
                <td data-label="Status">
                    @if ($u->active)<span style="color:#146c43;">Active</span>
                    @else<span class="muted">Deactivated</span>@endif
                </td>
                <td data-label="Actions">
                    <a href="{{ route('users.edit', $u) }}">Edit</a>
                    @if ($u->twoFactorEnabled())
                        @php
                            // What this button does depends on whether 2FA is
                            // still required for them: it either clears the
                            // enrolment for good, or forces a fresh one.
                            $willReEnrol = $u->requiresTwoFactor();
                            $label = $willReEnrol ? 'Reset 2FA' : 'Turn off 2FA';
                            $confirm = $willReEnrol
                                ? "Reset two-factor for {$u->name}? They will set it up again at next login."
                                : "Turn off two-factor for {$u->name}? They will sign in with just their password after this.";
                        @endphp
                        &middot;
                        <form method="POST" action="{{ route('users.reset-2fa', $u) }}" style="display:inline"
                              onsubmit="return confirm('{{ $confirm }}');">
                            @csrf
                            <button type="submit" style="background:none;border:none;color:var(--accent);cursor:pointer;padding:0;font:inherit;">{{ $label }}</button>
                        </form>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{ $users->links() }}
@endsection
