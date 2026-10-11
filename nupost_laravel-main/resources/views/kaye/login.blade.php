<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Expired - NUPost</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/clearlogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/clearlogo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #002366;
            --gold: #FFD700;
            --gold-dark: #b89600;
            --bg: #f8fafc;
            --text: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #001233 0%, #002366 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            color: var(--text);
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            max-width: 460px;
            width: 100%;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo {
            font-size: 26px;
            font-weight: 800;
            color: var(--navy);
            letter-spacing: -0.5px;
            margin-bottom: 24px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .logo span {
            color: var(--gold-dark);
        }

        .icon-container {
            width: 80px;
            height: 80px;
            background: #fef2f2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            color: #ef4444;
            border: 2px solid #fee2e2;
        }

        h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #0f172a;
        }

        p {
            font-size: 14.5px;
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 30px;
        }

        .tip-box {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 14px;
            padding: 16px;
            font-size: 13px;
            color: #b45309;
            text-align: left;
            margin-bottom: 32px;
            line-height: 1.5;
        }

        .footer {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 24px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">NUPost<span>.</span></div>
        
        <div class="icon-container">
            <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <h1>Access Link Expired</h1>
        
        <p>
            The secure access token you are using has expired or is invalid. Access tokens have a strict 60-day validity period.
        </p>

        <div class="tip-box">
            <strong>What to do next?</strong><br>
            Please contact the NUPost administrator at NU Lipa to request a new access link. A new token can be generated instantly from the Admin settings panel.
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} NUPost &mdash; National University Lipa
        </div>
    </div>
</body>
</html>
