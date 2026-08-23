<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <style>
        :root { --ink:#1a2233; --muted:#5b6472; --line:#e3e6ea; --accent:#2f5fdb; --bg:#f7f8fa; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background:var(--bg); color:var(--ink); }
        header.top { display:flex; align-items:center; justify-content:space-between; padding:.75rem 1rem; background:#fff; border-bottom:1px solid var(--line); flex-wrap:wrap; gap:.5rem; }
        header.top a { color:var(--ink); text-decoration:none; font-weight:600; }
        nav.main { display:flex; gap:1rem; flex-wrap:wrap; font-size:.9rem; }
        nav.main a { color:var(--muted); text-decoration:none; }
        nav.main a:hover { color:var(--accent); }
        main { padding:1rem; max-width:1000px; margin:0 auto; }
        .card { background:#fff; border:1px solid var(--line); border-radius:10px; padding:1rem; margin-bottom:1rem; }
        table { width:100%; border-collapse:collapse; font-size:.9rem; }
        th, td { text-align:left; padding:.5rem; border-bottom:1px solid var(--line); }
        .btn { display:inline-block; padding:.5rem .9rem; background:var(--accent); color:#fff; border:none; border-radius:6px; text-decoration:none; font-size:.9rem; cursor:pointer; }
        .btn.secondary { background:#fff; color:var(--accent); border:1px solid var(--accent); }
        label { display:block; font-size:.85rem; color:var(--muted); margin:.6rem 0 .2rem; }
        input, select, textarea { width:100%; padding:.5rem; border:1px solid var(--line); border-radius:6px; font-size:1rem; }
        .status { background:#e7f6ec; color:#146c43; padding:.6rem 1rem; border-radius:8px; margin-bottom:1rem; }
        .errors { background:#fdecea; color:#a12622; padding:.6rem 1rem; border-radius:8px; margin-bottom:1rem; }
        .muted { color:var(--muted); font-size:.85rem; }
        .masked { font-family: monospace; letter-spacing:.05em; }
        @media (max-width:480px) { table, thead, tbody, th, td, tr { display:block; } thead { display:none; } tr { border-bottom:1px solid var(--line); padding:.5rem 0; } td { border:none; padding:.15rem 0; } td::before { content: attr(data-label) ": "; font-weight:600; color:var(--muted); } }
    </style>
</head>
<body>
@auth
<header class="top">
    <a href="{{ route('dashboard') }}">{{ config('app.name') }}</a>
    <nav class="main">
        @php
            // Don't show links to protected pages while a required 2FA
            // enrollment/challenge is still pending — they'd just bounce
            // straight back here anyway (RequireTwoFactorVerified), and
            // showing them is confusing mid-challenge.
            $twoFactorPending = auth()->user()->hasAnyRole(['super_admin', 'hr_manager', 'accounts'])
                && (! auth()->user()->twoFactorEnabled() || ! session('2fa_verified'));
        @endphp
        @unless ($twoFactorPending)
            @can('employee.view')<a href="{{ route('employees.index') }}">Employees</a>@endcan
            @can('admin.settings.manage')<a href="{{ route('outlets.index') }}">Outlets</a>
            <a href="{{ route('job-roles.index') }}">Job roles</a>
            <a href="{{ route('document-templates.index') }}">Document types</a>@endcan
            @can('audit-log.view')<a href="{{ route('audit-log.index') }}">Audit log</a>@endcan
        @endunless
        <form method="POST" action="{{ route('logout') }}" style="display:inline">
            @csrf
            <button type="submit" class="btn secondary" style="padding:.2rem .6rem;">Log out</button>
        </form>
    </nav>
</header>
@endauth
<main>
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="errors">
            <ul style="margin:0; padding-left:1.2rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @yield('content')
</main>
</body>
</html>
