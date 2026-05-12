@php
    // Force form to always send fresh CSRF
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're all set — Traverse Inc.</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #fef2f2 0%, #fff0f9 50%, #f0f9ff 100%);
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .card {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 40px;
            padding: 4rem 3rem;
            max-width: 560px;
            width: 100%;
            text-align: center;
            box-shadow: 0 40px 80px rgba(0, 0, 0, 0.08);
        }

        .icon-box {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            border-radius: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            color: #16a34a;
            transform: rotate(6deg);
            box-shadow: 0 20px 40px rgba(34, 197, 94, 0.2);
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 900;
            color: #0f172a;
            margin: 0 0 1rem;
            line-height: 1.1;
        }

        p {
            color: #64748b;
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 2.5rem;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
            padding: 0.6rem 1.25rem;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 2.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #e11d48;
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.2s;
            box-shadow: 0 8px 24px rgba(225, 29, 72, 0.3);
        }

        .btn:hover {
            background: #be123c;
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(225, 29, 72, 0.4);
        }

        .steps {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            text-align: left;
            background: #f8fafc;
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .step-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: #475569;
        }

        .step-icon {
            color: #22c55e;
            flex-shrink: 0;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="icon-box">
            <i data-lucide="check-circle-2" style="width: 60px; height: 60px;"></i>
        </div>

        <div class="badge">
            <i data-lucide="sparkles" style="width: 14px; height: 14px;"></i>
            Form Submitted Successfully
        </div>

        <h1>You're all set! 🎉</h1>
        <p>Your onboarding form has been submitted. HR will review your details and activate your account shortly.</p>

        <div class="steps">
            <div class="step-item">
                <i data-lucide="check-circle" class="step-icon" style="width: 20px; height: 20px;"></i>
                Personal & Document details saved
            </div>
            <div class="step-item">
                <i data-lucide="check-circle" class="step-icon" style="width: 20px; height: 20px;"></i>
                Bank & Nominee details recorded
            </div>
            <div class="step-item">
                <i data-lucide="check-circle" class="step-icon" style="width: 20px; height: 20px;"></i>
                Policies acknowledged & signed
            </div>
            <div class="step-item">
                <i data-lucide="clock" style="width: 20px; height: 20px; color: #f59e0b; flex-shrink: 0;"></i>
                Waiting for Admin approval
            </div>
        </div>

        <a href="/login" class="btn">
            <i data-lucide="log-in" style="width: 18px; height: 18px;"></i>
            Go to Login
        </a>
    </div>

    <script>lucide.createIcons();</script>
</body>

</html>