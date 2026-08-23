<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'My documents') — {{ config('app.name') }}</title>
    <style>
        :root { --ink:#1a2233; --muted:#5b6472; --line:#e3e6ea; --accent:#2f5fdb; --bg:#f7f8fa; }
        * { box-sizing: border-box; }
        body { margin:0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background:var(--bg); color:var(--ink); }
        header.top { padding:1rem; background:#fff; border-bottom:1px solid var(--line); text-align:center; }
        header.top h1 { font-size:1.05rem; margin:0; }
        header.top p { margin:.2rem 0 0; color:var(--muted); font-size:.85rem; }
        main { padding:1rem; max-width:520px; margin:0 auto; }
        .card { background:#fff; border:1px solid var(--line); border-radius:10px; padding:1rem; margin-bottom:.75rem; }
        .doc-link { display:block; padding:.9rem 1rem; border:1px solid var(--line); border-radius:10px; background:#fff; text-decoration:none; color:var(--ink); margin-bottom:.6rem; }
        .doc-link .name { font-weight:600; }
        .doc-link .meta { color:var(--muted); font-size:.82rem; margin-top:.15rem; }
        .muted { color:var(--muted); font-size:.85rem; }
        .btn { display:inline-block; padding:.7rem 1rem; background:var(--accent); color:#fff; border:none; border-radius:8px; text-decoration:none; font-size:1rem; }
    </style>
</head>
<body>
<header class="top">
    <h1>{{ config('app.name') }}</h1>
    <p>Your signed onboarding documents</p>
</header>
<main>
    @yield('content')
</main>
</body>
</html>
