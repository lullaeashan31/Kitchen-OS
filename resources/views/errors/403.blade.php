<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied - {{ config('app.name') }}</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --warning: #f59e0b;
            --warning-dark: #d97706;
            --bg: #fffbeb;
            --text: #92400e;
            --text-muted: #b45309;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
        }

        .container {
            max-width: 500px;
            padding: 2rem;
        }

        .icon-box {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            color: var(--warning);
        }

        h1 {
            font-size: 3rem;
            margin: 0;
            font-weight: 800;
        }

        h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        p {
            color: var(--text-muted);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--warning);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn:hover {
            background-color: var(--warning-dark);
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="icon-box">
            <i data-lucide="shield-off" style="width: 64px; height: 64px;"></i>
        </div>
        <h1>403</h1>
        <h2>Access Denied</h2>
        <p>You don't have permission to access this area. If you believe this is an error, please contact your
            administrator.</p>
        <a href="/dashboard" class="btn">
            <i data-lucide="shield"></i> Back to Safety
        </a>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>