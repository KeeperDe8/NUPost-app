<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NUPost | National University Lipa Social Media Portal</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/clearlogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/clearlogo.png') }}">
    <meta name="description" content="The official social media request and content management platform for National University Lipa.">

    <!-- Prevent Theme Flash -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('nupost-theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* ── Base Color System (NU Navy & Gold Palette) ── */
        :root {
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --bg-subtle: #f1f5f9;
            --border: #e2e8f0;
            --border-subtle: #edf2f7;

            --text-main: #0f172a;
            --text-mid: #334155;
            --text-muted: #64748b;
            --text-faint: #94a3b8;

            --nu-navy: #002366;
            --nu-navy-hover: #001a4e;
            --nu-navy-light: #0a358c;
            --nu-navy-pale: #edf3fc;

            --nu-gold: #d97706;
            --nu-gold-bg: #fef3c7;
            --nu-gold-border: #fde68a;

            --status-green-bg: #ecfdf5;
            --status-green-text: #059669;
            --status-green-border: #a7f3d0;

            --status-blue-bg: #eff6ff;
            --status-blue-text: #2563eb;
            --status-blue-border: #bfdbfe;

            --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.05);

            --radius-sm: 6px;
            --radius-md: 10px;
            --radius-lg: 14px;
        }

        [data-theme="dark"] {
            --bg-page: #0b1329;
            --bg-card: #111c38;
            --bg-subtle: #172445;
            --border: #1e2c4f;
            --border-subtle: #192747;

            --text-main: #f8fafc;
            --text-mid: #cbd5e1;
            --text-muted: #94a3b8;
            --text-faint: #64748b;

            --nu-navy: #1d4ed8;
            --nu-navy-hover: #2563eb;
            --nu-navy-light: #3b82f6;
            --nu-navy-pale: rgba(29, 78, 216, 0.15);

            --nu-gold: #f59e0b;
            --nu-gold-bg: rgba(245, 158, 11, 0.12);
            --nu-gold-border: rgba(245, 158, 11, 0.25);

            --status-green-bg: rgba(16, 185, 129, 0.12);
            --status-green-text: #34d399;
            --status-green-border: rgba(16, 185, 129, 0.25);

            --status-blue-bg: rgba(59, 130, 246, 0.12);
            --status-blue-text: #60a5fa;
            --status-blue-border: rgba(59, 130, 246, 0.25);

            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 6px 18px rgba(0, 0, 0, 0.35);
        }

        html {
            scroll-behavior: smooth;
        }

        /* ── Reset ── */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--bg-page);
            color: var(--text-main);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            transition: background-color 0.2s ease, color 0.2s ease;
            min-height: 100vh;
            overflow-x: hidden;
        }

        [data-theme="dark"] body {
            background-color: #040c1e;
        }

        /* ── Ambient Yellow & Blue Background (Hardware-Accelerated, Zero-Lag) ── */
        .ambient-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            overflow: hidden;
            pointer-events: none;
            z-index: 0;
            transform: translate3d(0, 0, 0);
            -webkit-transform: translate3d(0, 0, 0);
            contain: strict;
            will-change: transform;
            background-image:
                radial-gradient(circle 520px at 6% 6%, rgba(0, 35, 102, 0.20) 0%, rgba(29, 78, 216, 0.08) 45%, transparent 75%),
                radial-gradient(circle 480px at 94% 8%, rgba(245, 158, 11, 0.25) 0%, rgba(217, 119, 6, 0.10) 45%, transparent 75%),
                radial-gradient(circle 460px at 94% 50%, rgba(29, 78, 216, 0.16) 0%, rgba(0, 35, 102, 0.06) 45%, transparent 75%),
                radial-gradient(circle 440px at 6% 56%, rgba(245, 158, 11, 0.20) 0%, rgba(251, 191, 36, 0.08) 45%, transparent 75%),
                radial-gradient(circle 500px at 15% 94%, rgba(0, 35, 102, 0.18) 0%, rgba(29, 78, 216, 0.08) 45%, transparent 75%),
                radial-gradient(circle 460px at 88% 92%, rgba(245, 158, 11, 0.22) 0%, rgba(217, 119, 6, 0.08) 45%, transparent 75%);
        }

        [data-theme="dark"] .ambient-bg {
            background-color: #040c1e;
            background-image:
                radial-gradient(circle 520px at 6% 6%, rgba(29, 78, 216, 0.35) 0%, rgba(13, 56, 140, 0.18) 45%, transparent 75%),
                radial-gradient(circle 480px at 94% 8%, rgba(245, 158, 11, 0.28) 0%, rgba(217, 119, 6, 0.12) 45%, transparent 75%),
                radial-gradient(circle 460px at 94% 50%, rgba(37, 99, 235, 0.28) 0%, rgba(29, 78, 216, 0.12) 45%, transparent 75%),
                radial-gradient(circle 440px at 6% 56%, rgba(245, 158, 11, 0.24) 0%, rgba(251, 191, 36, 0.10) 45%, transparent 75%),
                radial-gradient(circle 500px at 15% 94%, rgba(29, 78, 216, 0.30) 0%, transparent 75%),
                radial-gradient(circle 460px at 88% 92%, rgba(245, 158, 11, 0.26) 0%, rgba(217, 119, 6, 0.10) 45%, transparent 75%);
        }

        /* Page Stacking */
        .site-header,
        main,
        .site-footer {
            position: relative;
            z-index: 1;
        }

        /* Page Loading Indicator */
        #np-page-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            width: 0%;
            background: linear-gradient(90deg, #002366 0%, #1d4ed8 50%, #f59e0b 100%);
            box-shadow: 0 0 10px rgba(245, 158, 11, 0.6);
            z-index: 99999;
            pointer-events: none;
            transition: width 0.2s ease, opacity 0.2s ease;
            opacity: 0;
        }

        /* Container */
        .container {
            max-width: 1140px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ── Header & Navigation ── */
        .site-header {
            background-color: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        [data-theme="dark"] .site-header {
            background-color: rgba(4, 12, 30, 0.85);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
        }

        .brand-link {
            display: inline-flex;
            align-items: center;
            text-decoration: none;
        }

        .brand-logo {
            height: 38px;
            width: auto;
            display: block;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 28px;
            list-style: none;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
            transition: color 0.15s ease;
        }

        .nav-link:hover {
            color: var(--nu-navy);
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .theme-btn {
            background: none;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .theme-btn:hover {
            color: var(--text-main);
            background-color: var(--bg-subtle);
        }

        .theme-btn .sun-icon { display: none; }
        .theme-btn .moon-icon { display: block; }
        [data-theme="dark"] .theme-btn .sun-icon { display: block; }
        [data-theme="dark"] .theme-btn .moon-icon { display: none; }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 18px;
            border-radius: var(--radius-sm);
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.15s ease;
            cursor: pointer;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .btn-primary {
            background-color: #002366;
            color: #ffffff;
            border-color: #002366;
        }

        .btn-primary:hover {
            background-color: #001a4e;
            border-color: #001a4e;
            color: #ffffff;
        }

        [data-theme="dark"] .btn-primary {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }

        [data-theme="dark"] .btn-primary:hover {
            background-color: #2563eb;
            border-color: #2563eb;
        }

        .btn-outline {
            background-color: transparent;
            color: var(--text-main);
            border-color: var(--border);
        }

        .btn-outline:hover {
            background-color: var(--bg-subtle);
            border-color: var(--border);
        }

        .btn-gold {
            background-color: #d97706;
            color: #ffffff;
            border-color: #d97706;
        }

        .btn-gold:hover {
            background-color: #b45309;
            border-color: #b45309;
        }

        .btn-lg {
            padding: 12px 22px;
            font-size: 14.5px;
        }

        /* ── Hero Section ── */
        .hero-section {
            padding: 72px 0 60px;
            border-bottom: 1px solid var(--border);
            background-color: transparent;
        }

        [data-theme="dark"] .hero-section {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .hero-content {
            max-width: 820px;
        }

        .institution-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--nu-navy-pale);
            color: var(--nu-navy);
            border: 1px solid rgba(0, 35, 102, 0.12);
            padding: 5px 12px;
            border-radius: var(--radius-sm);
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        [data-theme="dark"] .institution-badge {
            color: #93c5fd;
            border-color: rgba(147, 197, 253, 0.2);
        }

        .hero-title {
            font-size: 40px;
            font-weight: 800;
            line-height: 1.2;
            color: var(--text-main);
            letter-spacing: -0.6px;
            margin-bottom: 18px;
        }

        .hero-desc {
            font-size: 16.5px;
            color: var(--text-muted);
            line-height: 1.65;
            margin-bottom: 30px;
            max-width: 700px;
        }

        .hero-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 34px;
            flex-wrap: wrap;
        }

        .hero-features-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            padding-top: 24px;
            border-top: 1px solid var(--border);
            max-width: 700px;
        }

        .feature-check-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13.5px;
            color: var(--text-mid);
        }

        .feature-check-icon {
            color: #059669;
            font-weight: 700;
            font-size: 14px;
        }

        /* ── Steps / Process Section ── */
        .section-block {
            padding: 64px 0;
            background-color: transparent;
        }

        .section-alt {
            background-color: rgba(255, 255, 255, 0.65);
            border-top: 1px solid var(--border);
            border-bottom: 1px solid var(--border);
        }

        [data-theme="dark"] .section-alt {
            background-color: rgba(17, 28, 56, 0.60);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .section-header {
            margin-bottom: 40px;
        }

        .section-tag {
            font-size: 12px;
            font-weight: 700;
            color: var(--nu-gold);
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 6px;
        }

        .section-title {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.4px;
            margin-bottom: 8px;
        }

        .section-lead {
            font-size: 14.5px;
            color: var(--text-muted);
            max-width: 600px;
        }

        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        .step-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 24px;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .step-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: rgba(217, 119, 6, 0.35);
        }

        [data-theme="dark"] .step-card {
            background-color: var(--bg-card);
            border-color: rgba(255, 255, 255, 0.08);
        }

        [data-theme="dark"] .step-card:hover {
            border-color: rgba(245, 158, 11, 0.4);
        }

        .step-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            background-color: var(--nu-navy-pale);
            color: var(--nu-navy);
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .step-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .step-desc {
            font-size: 13.5px;
            color: var(--text-muted);
            line-height: 1.55;
        }

        /* ── Categories Grid ── */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
        }

        .category-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 20px;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }

        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: rgba(0, 35, 102, 0.3);
        }

        [data-theme="dark"] .category-card {
            background-color: var(--bg-card);
            border-color: rgba(255, 255, 255, 0.08);
        }

        [data-theme="dark"] .category-card:hover {
            border-color: rgba(59, 130, 246, 0.4);
        }

        .category-card h4 {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .category-card p {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        /* ── Guidelines Section ── */
        .guidelines-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 28px;
            box-shadow: var(--shadow-sm);
        }

        [data-theme="dark"] .guidelines-card {
            background-color: var(--bg-card);
            border-color: rgba(255, 255, 255, 0.08);
        }

        .guidelines-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
        }

        .guideline-col h4 {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .guideline-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .guideline-list li {
            font-size: 13.5px;
            color: var(--text-mid);
            display: flex;
            align-items: flex-start;
            gap: 8px;
            line-height: 1.5;
        }

        .bullet-dot {
            color: var(--nu-navy);
            font-size: 18px;
            line-height: 1;
        }

        /* ── FAQ Section ── */
        .faq-accordion {
            background-color: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            overflow: hidden;
        }

        [data-theme="dark"] .faq-accordion {
            background-color: var(--bg-card);
            border-color: rgba(255, 255, 255, 0.08);
        }

        .faq-item {
            border-bottom: 1px solid var(--border);
        }

        .faq-item:last-child {
            border-bottom: none;
        }

        .faq-question {
            width: 100%;
            padding: 18px 22px;
            background: none;
            border: none;
            text-align: left;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 15px;
            font-weight: 600;
            color: var(--text-main);
            cursor: pointer;
            font-family: inherit;
        }

        .faq-question:hover {
            background-color: var(--bg-subtle);
        }

        .faq-chevron {
            width: 18px;
            height: 18px;
            color: var(--text-muted);
            transition: transform 0.2s ease;
        }

        .faq-item.open .faq-chevron {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0 22px 18px;
            font-size: 13.5px;
            color: var(--text-muted);
            line-height: 1.6;
            display: none;
        }

        .faq-item.open .faq-answer {
            display: block;
        }

        /* ── Call to Action Banner ── */
        .cta-banner {
            background: linear-gradient(135deg, #002366 0%, #0a358c 60%, #001a4e 100%);
            color: #ffffff;
            border-radius: var(--radius-lg);
            padding: 44px 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
            box-shadow: 0 10px 30px rgba(0, 35, 102, 0.25);
            margin: 40px 0 60px;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        [data-theme="dark"] .cta-banner {
            background: linear-gradient(135deg, #091a42 0%, #0f2b6b 60%, #040c1e 100%);
            border: 1px solid rgba(59, 130, 246, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .cta-text h3 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 8px;
            color: #ffffff;
        }

        .cta-text p {
            font-size: 14.5px;
            color: rgba(255, 255, 255, 0.85);
            max-width: 520px;
        }

        .cta-actions {
            display: flex;
            gap: 12px;
            flex-shrink: 0;
        }

        .btn-white {
            background-color: #ffffff;
            color: #002366;
            border-color: #ffffff;
        }

        .btn-white:hover {
            background-color: #f1f5f9;
            color: #001a4e;
        }

        /* ── Footer ── */
        .site-footer {
            background-color: rgba(255, 255, 255, 0.90);
            border-top: 1px solid var(--border);
            padding: 40px 0 30px;
        }

        [data-theme="dark"] .site-footer {
            background-color: rgba(4, 12, 30, 0.90);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .footer-grid {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 28px;
            margin-bottom: 30px;
        }

        .footer-brand {
            max-width: 380px;
        }

        .footer-logo {
            height: 34px;
            width: auto;
            margin-bottom: 12px;
        }

        .footer-desc {
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.55;
        }

        .footer-nav {
            display: flex;
            gap: 40px;
        }

        .footer-col h5 {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .footer-col ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .footer-col a {
            font-size: 13px;
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.15s ease;
        }

        .footer-col a:hover {
            color: var(--nu-navy);
        }

        .footer-bottom {
            padding-top: 24px;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: var(--text-faint);
            flex-wrap: wrap;
            gap: 12px;
        }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .hero-title {
                font-size: 32px;
            }
            .steps-grid {
                grid-template-columns: 1fr;
            }
            .categories-grid {
                grid-template-columns: 1fr 1fr;
            }
            .guidelines-grid {
                grid-template-columns: 1fr;
            }
            .cta-banner {
                flex-direction: column;
                align-items: flex-start;
            }
            .nav-links {
                display: none;
            }
        }

        @media (max-width: 600px) {
            .categories-grid {
                grid-template-columns: 1fr;
            }
            .hero-actions {
                flex-direction: column;
                align-items: stretch;
            }
            .cta-actions {
                flex-direction: column;
                width: 100%;
            }
            .cta-actions .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div id="np-page-progress"></div>

    <!-- ── Ambient Yellow & Blue Background (Light & Dark Mode) ── -->
    <div class="ambient-bg" aria-hidden="true"></div>

    <!-- ── Header ── -->
    <header class="site-header">
        <div class="container header-inner">
            <a href="{{ route('landing') }}" class="brand-link">
                <img src="{{ asset('assets/nupostlogo.png') }}" alt="NUPost Logo" class="brand-logo">
            </a>

            <nav class="nav-links">
                <a href="#how-it-works" class="nav-link">How It Works</a>
                <a href="#categories" class="nav-link">Request Types</a>
                <a href="#guidelines" class="nav-link">Guidelines</a>
                <a href="#faq" class="nav-link">FAQ</a>
            </nav>

            <div class="nav-right">
                <button id="themeToggle" class="theme-btn" aria-label="Toggle Theme" title="Toggle theme">
                    <svg class="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    <svg class="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </button>

                <a href="{{ route('login') }}" class="btn btn-outline">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-primary">Create Account</a>
            </div>
        </div>
    </header>

    <main>
        <!-- ── Hero Section ── -->
        <section class="hero-section">
            <div class="container">
                <div class="hero-content">
                    <div class="institution-badge">
                        <span>National University Lipa</span> &bull; <span>Marketing Office</span>
                    </div>

                    <h1 class="hero-title">
                        Social Media Request & Content Portal
                    </h1>

                    <p class="hero-desc">
                        The official system for academic departments, student organizations, and faculty to submit pubmats, announcements, and event publications for National University Lipa social media channels.
                    </p>

                    <div class="hero-actions">
                        <a href="{{ route('login') }}" class="btn btn-lg btn-primary">
                            <span>Submit a Post Request</span>
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-lg btn-outline">
                            <span>Register Organization</span>
                        </a>
                    </div>

                    <div class="hero-features-list">
                        <div class="feature-check-item">
                            <span class="feature-check-icon">✓</span>
                            <span>Standard 3 to 5 business day review and approval turnaround</span>
                        </div>
                        <div class="feature-check-item">
                            <span class="feature-check-icon">✓</span>
                            <span>Direct editorial coordination and revision messaging with Marketing</span>
                        </div>
                        <div class="feature-check-item">
                            <span class="feature-check-icon">✓</span>
                            <span>Conflict-free calendar scheduling across official social media pages</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── How It Works Section ── -->
        <section class="section-block" id="how-it-works">
            <div class="container">
                <div class="section-header">
                    <div class="section-tag">Process</div>
                    <h2 class="section-title">How the Request Workflow Works</h2>
                    <p class="section-lead">A straightforward, transparent 3-step submission and publication process.</p>
                </div>

                <div class="steps-grid">
                    <div class="step-card">
                        <div class="step-num">1</div>
                        <h3 class="step-title">Submit Details</h3>
                        <p class="step-desc">Provide event details, caption copy, target social media channels, target date, and upload your high-resolution publicity materials.</p>
                    </div>

                    <div class="step-card">
                        <div class="step-num">2</div>
                        <h3 class="step-title">Marketing Review</h3>
                        <p class="step-desc">The Marketing Office verifies brand standards, checks scheduling conflicts, and coordinates revisions with you through direct messaging.</p>
                    </div>

                    <div class="step-card">
                        <div class="step-num">3</div>
                        <h3 class="step-title">Official Publication</h3>
                        <p class="step-desc">Once approved, your post is queued and published on official NU Lipa social media channels with live confirmation and logs.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Request Categories ── -->
        <section class="section-block section-alt" id="categories">
            <div class="container">
                <div class="section-header">
                    <div class="section-tag">Categories</div>
                    <h2 class="section-title">Types of Supported Requests</h2>
                    <p class="section-lead">Select the appropriate category when submitting your publication request.</p>
                </div>

                <div class="categories-grid">
                    <div class="category-card">
                        <h4>Ready-Made PubMats</h4>
                        <p>Pre-designed posters and collateral following institutional branding standards.</p>
                    </div>
                    <div class="category-card">
                        <h4>Event Announcements</h4>
                        <p>General assemblies, academic seminars, webinars, and college-wide activities.</p>
                    </div>
                    <div class="category-card">
                        <h4>Materials Checking</h4>
                        <p>Preliminary review of design drafts and copy prior to official dissemination.</p>
                    </div>
                    <div class="category-card">
                        <h4>Event Documentation</h4>
                        <p>Photo coverage, post-activity highlights, and student achievement recognition.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Guidelines Section ── -->
        <section class="section-block" id="guidelines">
            <div class="container">
                <div class="section-header">
                    <div class="section-tag">Guidelines</div>
                    <h2 class="section-title">Submission Requirements</h2>
                    <p class="section-lead">Please review these basic requirements before submitting a post request.</p>
                </div>

                <div class="guidelines-card">
                    <div class="guidelines-grid">
                        <div class="guideline-col">
                            <h4>
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <span>Lead Time & Timelines</span>
                            </h4>
                            <ul class="guideline-list">
                                <li><span class="bullet-dot">&bull;</span> Submit requests at least 3 to 5 business days prior to your preferred posting date.</li>
                                <li><span class="bullet-dot">&bull;</span> Urgent advisories and emergency notices are prioritized on a case-by-case basis.</li>
                                <li><span class="bullet-dot">&bull;</span> Revisions requested by Marketing should be resolved promptly to avoid schedule delays.</li>
                            </ul>
                        </div>

                        <div class="guideline-col">
                            <h4>
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                <span>File Quality & Formats</span>
                            </h4>
                            <ul class="guideline-list">
                                <li><span class="bullet-dot">&bull;</span> High-resolution PNG or JPG formats (minimum 1080x1080px for square posts).</li>
                                <li><span class="bullet-dot">&bull;</span> Ensure official NU Lipa and organization logos are clear and un-distorted.</li>
                                <li><span class="bullet-dot">&bull;</span> Text in graphics must be legible on mobile devices.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── FAQ Section ── -->
        <section class="section-block section-alt" id="faq">
            <div class="container">
                <div class="section-header">
                    <div class="section-tag">FAQ</div>
                    <h2 class="section-title">Frequently Asked Questions</h2>
                    <p class="section-lead">Answers to common questions regarding post requests and marketing review.</p>
                </div>

                <div class="faq-accordion">
                    <div class="faq-item open">
                        <button type="button" class="faq-question">
                            <span>Who is eligible to submit requests on NUPost?</span>
                            <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div class="faq-answer">
                            Any recognized student organization, academic department, college council, faculty member, or administrative office of National University Lipa with an active account may submit requests.
                        </div>
                    </div>

                    <div class="faq-item">
                        <button type="button" class="faq-question">
                            <span>How long does the review and approval take?</span>
                            <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div class="faq-answer">
                            Standard review takes 24 to 48 hours. If adjustments are required (such as branding guidelines or caption polishing), the Marketing Office will coordinate directly with you through the request chat.
                        </div>
                    </div>

                    <div class="faq-item">
                        <button type="button" class="faq-question">
                            <span>Can I edit my request after submitting?</span>
                            <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div class="faq-answer">
                            Yes. While your request is in "Pending Review" status, you can update details, replace attachments, or message the admin directly from your dashboard instead of creating duplicate requests.
                        </div>
                    </div>

                    <div class="faq-item">
                        <button type="button" class="faq-question">
                            <span>What social media platforms are supported?</span>
                            <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div class="faq-answer">
                            Requests can be targeted to the official National University Lipa Facebook Page, Instagram, and campus digital bulletin channels depending on content scope and audience.
                        </div>
                    </div>
                </div>

                <!-- Call to Action Banner -->
                <div class="cta-banner">
                    <div class="cta-text">
                        <h3>Ready to submit your post request?</h3>
                        <p>Sign in with your university account or register your organization to submit and track publication requests.</p>
                    </div>
                    <div class="cta-actions">
                        <a href="{{ route('login') }}" class="btn btn-lg btn-white">Sign In to NUPost</a>
                        <a href="{{ route('register') }}" class="btn btn-lg btn-gold">Create Account</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- ── Footer ── -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <img src="{{ asset('assets/nupostlogo.png') }}" alt="NUPost Logo" class="footer-logo">
                    <p class="footer-desc">
                        The official social media request and content management system of National University Lipa. Supporting campus communication, student achievements, and institutional news.
                    </p>
                </div>

                <div class="footer-nav">
                    <div class="footer-col">
                        <h5>Navigation</h5>
                        <ul>
                            <li><a href="#how-it-works">How It Works</a></li>
                            <li><a href="#categories">Request Types</a></li>
                            <li><a href="#guidelines">Guidelines</a></li>
                            <li><a href="#faq">FAQ</a></li>
                        </ul>
                    </div>

                    <div class="footer-col">
                        <h5>Portal Access</h5>
                        <ul>
                            <li><a href="{{ route('login') }}">Sign In</a></li>
                            <li><a href="{{ route('register') }}">Register Account</a></li>
                            <li><a href="{{ route('password.forgot') }}">Reset Password</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <span>&copy; {{ date('Y') }} National University Lipa &bull; Marketing & Communications Office. All rights reserved.</span>
                <span>Education That Works &bull; Lipa City, Batangas</span>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Theme toggle
            const themeToggle = document.getElementById('themeToggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const current = document.documentElement.getAttribute('data-theme') || 'light';
                    const next = current === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-theme', next);
                    localStorage.setItem('nupost-theme', next);
                });
            }

            // Minimal FAQ accordion
            const faqItems = document.querySelectorAll('.faq-item');
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                if (question) {
                    question.addEventListener('click', () => {
                        const isOpen = item.classList.contains('open');
                        faqItems.forEach(other => other.classList.remove('open'));
                        if (!isOpen) {
                            item.classList.add('open');
                        }
                    });
                }
            });
        });

        // Instant top bar feedback on link clicks
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link || !link.href) return;
            if (link.target === '_blank' || link.hasAttribute('download')) return;
            if (link.href.startsWith('javascript:') || link.href.includes('#')) return;
            if (link.origin !== window.location.origin) return;
            if (link.pathname === window.location.pathname && link.search === window.location.search) return;

            const bar = document.getElementById('np-page-progress');
            if (bar) {
                bar.style.opacity = '1';
                bar.style.width = '75%';
            }
        });
    </script>
</body>
</html>
