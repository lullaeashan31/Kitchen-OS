<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error - {{ config('app.name') }}</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --danger: #ef4444;
            --danger-dark: #dc2626;
            --bg: #fef2f2;
            --text: #7f1d1d;
            --text-muted: #991b1b;
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
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            color: var(--danger);
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
            background-color: var(--danger);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn:hover {
            background-color: var(--danger-dark);
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="icon-box">
            <i data-lucide="alert-octagon" style="width: 64px; height: 64px;"></i>
        </div>
        <h1>500</h1>
        <h2>System Under Maintenance</h2>
        <p>Something went wrong on our end. Our technicians have been notified and are looking into it. Please try again
            later.</p>
        <a href="/dashboard" class="btn">
            <i data-lucide="refresh-cw"></i> Reload Dashboard
        </a>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>

</html>