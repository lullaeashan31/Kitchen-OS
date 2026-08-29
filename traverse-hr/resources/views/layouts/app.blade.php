<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f5f5f7" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#000000" media="(prefers-color-scheme: dark)">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    {{-- Static file, cache-busted by the deployed file's own mtime, so a new
         build lands without anyone having to clear their browser cache. --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: 1 }}">
    @stack('head')
</head>
<body>
@auth
    @php
        // Links to protected pages are hidden while a 2FA challenge or
        // enrolment is still outstanding — they would only bounce back here.
        $u = auth()->user();
        $twoFactorPending = ($u->requiresTwoFactor() && ! $u->twoFactorEnabled())
            || ($u->twoFactorEnabled() && ! session('2fa_verified'));

        $nav = [];
        if ($u->can('employee.view'))          $nav['employees.*']           = ['Employees', route('employees.index')];
        if ($u->can('admin.settings.manage')) {
            $nav['outlets.*']                  = ['Outlets', route('outlets.index')];
            $nav['job-roles.*']                = ['Job roles', route('job-roles.index')];
            $nav['document-templates.*']       = ['Documents', route('document-templates.index')];
            $nav['company-signatories.*']      = ['Signatories', route('company-signatories.index')];
        }
        if ($u->can('user.manage'))            $nav['users.*']               = ['Users', route('users.index')];
        if ($u->can('audit-log.view'))         $nav['audit-log.*']           = ['Audit log', route('audit-log.index')];
    @endphp
    <header class="top">
        <div class="top-inner">
            <a class="brand" href="{{ route('dashboard') }}">{{ config('app.name') }}</a>
            @unless ($twoFactorPending)
                <nav class="main">
                    @foreach ($nav as $pattern => [$label, $url])
                        <a href="{{ $url }}" @class(['is-active' => request()->routeIs($pattern)])>{{ $label }}</a>
                    @endforeach
                </nav>
            @endunless
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn secondary small">Log out</button>
            </form>
        </div>
    </header>
@endauth

<main>
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif
    @if (session('notice'))
        <div class="notice">{{ session('notice') }}</div>
    @endif
    @if ($errors->any())
        <div class="errors">
            @if ($errors->count() === 1)
                {{ $errors->first() }}
            @else
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    @yield('content')
</main>

@stack('scripts')
</body>
</html>
