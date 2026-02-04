<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name', 'Kitchen Management') }}</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Styles -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>

<body style="display: flex; align-items: center; justify-content: center; height: 100vh; background-color: #f3f4f6;">

    <div class="card"
        style="width: 100%; max-width: 400px; padding: 2.5rem; border: none; box-shadow: var(--shadow-lg);">
        <div style="text-align: center; margin-bottom: 2rem;">
            <!-- Logo or Icon -->
            <div style="font-size: 2rem; margin-bottom: 1rem;">👨‍🍳</div>
            <h1 style="font-size: 1.5rem;">Kitchen OS</h1>
            <p class="text-muted">Sign in to your account</p>
        </div>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <span style="color: var(--danger-color); font-size: 0.8rem;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
                @error('password')
                    <span style="color: var(--danger-color); font-size: 0.8rem;">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary w-full"
                style="justify-content: center; padding: 0.75rem; font-size: 1rem;">
                Sign In
            </button>
        </form>
    </div>

</body>

</html>