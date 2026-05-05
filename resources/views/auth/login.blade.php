<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name', 'Kitchen Management') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        :root {
            --bg-primary: #06101E;
            --bg-card: #0A1628;
            --border-subtle: rgba(242, 237, 230, 0.08);
            --text-primary: #F2EDE6;
            --text-muted: rgba(242, 237, 230, 0.6);
            --accent: #B5975A;
            --accent-hover: #D4B06A;
        }

        body {
            background-color: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'DM Sans', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .login-card {
            background-color: var(--bg-card);
            width: 100%;
            max-width: 420px;
            padding: 24px;
            border-radius: 4px;
            border: 1px solid var(--border-subtle);
        }

        .login-header h1 {
            font-family: 'EB Garamond', serif;
            color: var(--text-primary);
            font-size: 2.25rem;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            letter-spacing: -0.02em;
            text-transform: uppercase;
        }

        .form-group { margin-bottom: 1.5rem; }

        .form-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--accent);
            margin-bottom: 0.5rem;
        }

        .form-control {
            width: 100%;
            background-color: transparent !important;
            border: 1px solid var(--border-subtle) !important;
            color: var(--text-primary) !important;
            padding: 12px 16px !important;
            border-radius: 4px !important;
            font-family: 'DM Sans', sans-serif;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: var(--accent) !important;
            outline: none;
        }

        .btn-primary {
            width: 100%;
            background-color: var(--accent);
            color: var(--bg-primary);
            border: none;
            padding: 1rem;
            border-radius: 2px;
            font-weight: 700;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 1rem;
        }
        .btn-primary:hover {
            background-color: var(--accent-hover);
        }

        .text-muted {
            color: var(--text-muted);
            font-size: 0.875rem;
        }
    </style>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

    <div class="login-card">
        <div class="login-header" style="text-align: center; margin-bottom: 2rem;">
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border: 1px solid var(--accent); margin-bottom: 1rem;">
                <i data-lucide="chef-hat" style="width: 24px; height: 24px; color: var(--accent);"></i>
            </div>
            <h1>Kitchen OS</h1>
            <p class="text-muted">Sign in to your account</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}" required autofocus
                    inputmode="numeric" pattern="[0-9]*"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '').substring(0, 10)"
                    title="Enter 10 digit phone number" maxlength="10" minlength="10"
                    placeholder="Enter your 10-digit phone number">
                @error('phone')
                    <p style="color: #f87171; font-size: 0.75rem; margin-top: 0.5rem;">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
                @error('password')
                    <p style="color: #f87171; font-size: 0.75rem; margin-top: 0.5rem;">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary">
                Sign In
            </button>
        </form>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>