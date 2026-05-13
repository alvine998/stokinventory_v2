<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Access Denied</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        :root {
            --ink: #17202a;
            --deep: #172033;
            --teal: #0f766e;
            --rose: #e11d48;
            --soft-rose: #fff0f3;
            --border: #e5eaef;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: Inter, system-ui, sans-serif;
            background: #f5f7fb;
            color: var(--ink);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .error-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 56px 48px 48px;
            max-width: 520px;
            width: 100%;
            text-align: center;
            box-shadow: 0 8px 40px rgba(0,0,0,.07);
            animation: rise .4s cubic-bezier(.22,.68,0,1.2);
        }
        @keyframes rise {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .error-icon-wrap {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            background: var(--soft-rose);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 28px;
        }
        .error-icon-wrap i {
            font-size: 36px;
            color: var(--rose);
        }

        .error-code {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--rose);
            margin-bottom: 10px;
        }

        h1 {
            font-size: 28px;
            font-weight: 800;
            line-height: 1.2;
            color: var(--ink);
            margin-bottom: 12px;
        }

        .error-desc {
            font-size: 15px;
            line-height: 1.65;
            color: #60757f;
            margin-bottom: 36px;
        }

        .error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 22px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: opacity .15s, box-shadow .15s;
        }
        .btn:hover { opacity: .88; }
        .btn-primary {
            background: var(--teal);
            color: #fff;
            box-shadow: 0 2px 10px rgba(15,118,110,.22);
        }
        .btn-ghost {
            background: transparent;
            color: var(--ink);
            border: 1.5px solid var(--border);
        }

        .error-divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 36px 0 24px;
        }

        .error-hint {
            font-size: 13px;
            color: #8fa4ae;
        }
        .error-hint a {
            color: var(--teal);
            font-weight: 500;
            text-decoration: none;
        }
        .error-hint a:hover { text-decoration: underline; }

        @media (max-width: 480px) {
            .error-card { padding: 40px 24px 36px; }
            h1 { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon-wrap">
            <i class="fa-solid fa-lock"></i>
        </div>

        <p class="error-code">Error 403</p>
        <h1>Access Denied</h1>
        <p class="error-desc">
            You don't have permission to view this page.<br>
            Contact your administrator if you believe this is a mistake.
        </p>

        <div class="error-actions">
            @auth
                <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="btn btn-ghost">
                    <i class="fa-solid fa-arrow-left"></i> Go Back
                </a>
                <a href="{{ route('dashboard') }}" class="btn btn-primary">
                    <i class="fa-solid fa-house"></i> Dashboard
                </a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary">
                    <i class="fa-solid fa-right-to-bracket"></i> Sign In
                </a>
            @endauth
        </div>

        <hr class="error-divider">
        <p class="error-hint">
            Need access? <a href="mailto:{{ config('mail.from.address', 'admin@example.com') }}">Contact support</a>
            &nbsp;·&nbsp;
            <a href="{{ route('dashboard') }}">Home</a>
        </p>
    </div>
</body>
</html>
