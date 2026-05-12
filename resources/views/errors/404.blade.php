<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found - {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #ffffff;
            color: #1C2D4F;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }

        .container {
            max-width: 480px;
            padding: 2rem;
        }

        .error-code {
            font-size: 5rem;
            font-weight: 800;
            color: #1C2D4F;
            margin: 0 0 0.5rem;
            letter-spacing: -2px;
            opacity: 0.15;
        }

        h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 0 1rem;
            color: #1C2D4F;
        }

        p {
            font-size: 1rem;
            color: #4a5568;
            line-height: 1.7;
            margin: 0 0 2rem;
        }

        .btn {
            display: inline-block;
            background-color: #1C2D4F;
            color: #ffffff;
            padding: 0.75rem 1.75rem;
            border-radius: 0.625rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: opacity 0.2s;
        }

        .btn:hover {
            opacity: 0.85;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="error-code">404</div>
        <h1>Page Not Found</h1>
        <p>This page doesn't exist. Try going back or return to the dashboard.</p>
        <a href="/dashboard" class="btn">Back to Dashboard</a>
    </div>
</body>

</html>
