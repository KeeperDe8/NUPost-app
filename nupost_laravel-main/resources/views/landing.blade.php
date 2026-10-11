<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NUPost | Social Media Request System</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/clearlogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/clearlogo.png') }}">
    <meta name="description" content="The official social media request platform for National University Lipa. Submit, track, and manage content requests seamlessly.">
    
    <!-- Prevent Flash of Unstyled Theme -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('nupost-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* ── Theme Tokens ── */
        :root {
            --bg-page: #f8fafc;
            --bg-surface: #ffffff;
            --bg-surface-elevated: #ffffff;
            --bg-subtle: #f1f5f9;
            --bg-card-header: #f8fafc;
            --border-subtle: rgba(0, 35, 102, 0.08);
            --border-card: #e2e8f0;
            --border-hover: rgba(0, 35, 102, 0.25);

            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;

            --nav-bg: rgba(255, 255, 255, 0.95);
            --nav-border: rgba(226, 232, 240, 0.8);
            --nav-scrolled: #ffffff;

            --stat-border: #e2e8f0;
            --shadow-card: 0 4px 14px rgba(0, 26, 78, 0.06);
            --shadow-hover: 0 14px 30px -5px rgba(0, 26, 78, 0.12);

            --navy-950: #030d24;
            --navy-900: #001a4e;
            --navy-800: #002366;
            --navy-700: #0d388c;
            --navy-600: #1d4ed8;
            --navy-50: #f0f4fd;

            --gold-500: #f59e0b;
            --gold-400: #fbbf24;
            --gold-300: #fcd34d;

            --font-main: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            --font-heading: 'Outfit', 'Plus Jakarta Sans', sans-serif;

            --radius-sm: 8px;
            --radius-md: 14px;
            --radius-lg: 20px;
            --radius-xl: 28px;
            --radius-full: 9999px;
        }

        [data-theme="dark"] {
            --bg-page: #040c1e;
            --bg-surface: #081636;
            --bg-surface-elevated: #0c1f4c;
            --bg-subtle: #0a1b42;
            --bg-card-header: #06112a;
            --border-subtle: rgba(255, 255, 255, 0.08);
            --border-card: rgba(30, 79, 216, 0.25);
            --border-hover: rgba(245, 158, 11, 0.5);

            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;

            --nav-bg: rgba(4, 12, 30, 0.95);
            --nav-border: rgba(255, 255, 255, 0.08);
            --nav-scrolled: #06122d;

            --stat-border: rgba(255, 255, 255, 0.08);
            --shadow-card: 0 6px 24px rgba(0, 0, 0, 0.4);
            --shadow-hover: 0 16px 36px rgba(0, 0, 0, 0.55), 0 0 25px rgba(30, 79, 216, 0.2);

            --navy-50: rgba(13, 56, 140, 0.3);
            --navy-950: #f8fafc;
        }

        /* ── Reset & Core ── */
        *, *::before, *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            font-size: 16px;
            -webkit-text-size-adjust: 100%;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-page);
            color: var(--text-primary);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        /* ── Sleek Instant Top Progress Bar (YouTube-style feedback) ── */
        #np-page-progress {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            width: 0%;
            background: linear-gradient(90deg, #002366 0%, #3b82f6 50%, #f59e0b 100%);
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.7);
            z-index: 9999999;
            pointer-events: none;
            transition: width 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease;
            opacity: 0;
        }

        /* Ambient Gradients */
        .ambient-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            pointer-events: none;
            z-index: -1;
        }

        .ambient-glow {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            transform: translateZ(0);
        }

        .glow-top {
            top: -120px;
            right: -100px;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(29, 78, 216, 0.14) 0%, rgba(245, 158, 11, 0.06) 50%, transparent 70%);
        }

        .glow-bottom {
            top: 600px;
            left: -150px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(13, 56, 140, 0.12) 0%, transparent 70%);
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
            color: var(--text-primary);
            letter-spacing: -0.02em;
            font-weight: 700;
        }

        .gradient-text {
            background: linear-gradient(135deg, #3b82f6 0%, var(--gold-400) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* ── Navbar ── */
        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: var(--nav-bg);
            border-bottom: 1px solid var(--nav-border);
            z-index: 1000;
            transition: background-color 0.25s ease, box-shadow 0.25s ease;
            transform: translateZ(0);
        }

        .navbar.scrolled {
            background: var(--nav-scrolled);
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }

        .nav-container {
            max-width: 1240px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 24px;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .nav-brand img {
            height: 44px;
            width: auto;
            object-fit: contain;
            display: block;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 28px;
        }

        .nav-link {
            text-decoration: none;
            color: var(--text-secondary);
            font-size: 0.925rem;
            font-weight: 500;
            transition: color 0.15s ease;
        }

        .nav-link:hover {
            color: var(--gold-400);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .theme-toggle-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: var(--bg-subtle);
            border: 1px solid var(--border-subtle);
            color: var(--text-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
        }

        .theme-toggle-btn:hover {
            background: var(--bg-surface-elevated);
            border-color: var(--gold-500);
            transform: rotate(15deg);
        }

        .sun-icon { display: none; color: var(--gold-400); }
        .moon-icon { display: block; color: var(--navy-800); }

        [data-theme="dark"] .sun-icon { display: block; }
        [data-theme="dark"] .moon-icon { display: none; }

        /* ── Buttons ── */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: var(--font-main);
            font-weight: 600;
            text-decoration: none;
            border-radius: var(--radius-full);
            cursor: pointer;
            border: none;
            outline: none;
            transition: transform 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
            will-change: transform;
        }

        .btn-sm { padding: 8px 18px; font-size: 0.875rem; }
        .btn-lg { padding: 13px 28px; font-size: 1rem; }

        .btn-navy {
            background: var(--navy-800);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 35, 102, 0.2);
        }

        [data-theme="dark"] .btn-navy {
            background: #1d4ed8;
            color: #ffffff;
        }

        .btn-navy:hover {
            background: #1e40af;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(30, 64, 175, 0.35);
            color: #ffffff;
        }

        .btn-gold {
            background: var(--gold-500);
            color: #030d24;
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.3);
        }

        .btn-gold:hover {
            background: var(--gold-400);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.45);
            color: #030d24;
        }

        .btn-subtle {
            background: var(--bg-surface);
            color: var(--text-primary);
            border: 1px solid var(--border-card);
        }

        .btn-subtle:hover {
            background: var(--bg-subtle);
            border-color: var(--gold-400);
            transform: translateY(-2px);
        }

        /* ── Hero Section ── */
        .hero {
            padding: 130px 24px 70px;
            position: relative;
        }

        .hero-container {
            max-width: 1240px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
            gap: 56px;
            align-items: center;
        }

        .badge-live {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--bg-surface);
            border: 1px solid var(--border-subtle);
            padding: 5px 14px;
            border-radius: var(--radius-full);
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-primary);
            box-shadow: var(--shadow-card);
            margin-bottom: 20px;
        }

        .pulse-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #10b981;
        }

        .hero-title {
            font-size: clamp(2.4rem, 4vw, 3.8rem);
            line-height: 1.12;
            margin-bottom: 18px;
            letter-spacing: -0.03em;
        }

        .hero-lead {
            font-size: clamp(1rem, 1.2vw, 1.15rem);
            color: var(--text-secondary);
            line-height: 1.65;
            margin-bottom: 32px;
            max-width: 540px;
        }

        .hero-cta-group {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 36px;
        }

        .trust-pills {
            display: flex;
            align-items: center;
            gap: 20px;
            padding-top: 14px;
            border-top: 1px solid var(--border-card);
            flex-wrap: wrap;
        }

        .trust-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.825rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .trust-check {
            color: #10b981;
            font-weight: 800;
        }

        /* ── Hero Right & Floating Showcase Window ── */
        .hero-right {
            position: relative;
            padding: 30px 20px;
            perspective: 1000px;
        }

        .demo-window {
            background: var(--bg-surface);
            border: 1px solid var(--border-card);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            overflow: hidden;
            position: relative;
            z-index: 2;
            transition: box-shadow 0.3s ease, border-color 0.3s ease;
        }

        .demo-window:hover {
            box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.35);
            border-color: rgba(59, 130, 246, 0.35);
        }

        .demo-header {
            background: var(--bg-card-header);
            border-bottom: 1px solid var(--border-card);
            padding: 12px 18px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .demo-dots {
            display: flex;
            gap: 6px;
        }

        .demo-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        .demo-dot.red { background: #f87171; }
        .demo-dot.yellow { background: #fbbf24; }
        .demo-dot.green { background: #34d399; }

        .demo-badge-interactive {
            font-size: 0.725rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--gold-400);
            background: var(--navy-50);
            padding: 3px 8px;
            border-radius: 12px;
        }

        .demo-body {
            padding: 20px;
        }

        .demo-tabs-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .demo-tabs-label .hint {
            color: var(--gold-400);
            text-transform: none;
            font-weight: 600;
        }

        .demo-tabs {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
            margin-bottom: 16px;
            background: var(--bg-subtle);
            padding: 4px;
            border-radius: var(--radius-sm);
        }

        .demo-tab-btn {
            border: none;
            background: transparent;
            padding: 7px;
            font-family: var(--font-main);
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }

        .demo-tab-btn.active {
            background: var(--bg-surface-elevated);
            color: var(--gold-400);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
        }

        .demo-card-preview {
            background: var(--bg-surface-elevated);
            border: 1px solid var(--border-card);
            border-radius: var(--radius-md);
            padding: 16px;
            margin-bottom: 14px;
        }

        .preview-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .preview-org {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .preview-avatar {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: var(--navy-700);
            color: #ffffff;
            font-size: 0.775rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .preview-org-meta h5 {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .preview-org-meta p {
            font-size: 0.725rem;
            color: var(--text-muted);
        }

        .status-chip {
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.725rem;
            font-weight: 700;
        }

        .chip-approved { background: rgba(34, 197, 94, 0.2); color: #4ade80; }
        .chip-review { background: rgba(245, 158, 11, 0.2); color: #fbbf24; }
        .chip-scheduled { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }

        .preview-caption-box {
            background: var(--bg-subtle);
            border: 1px solid var(--border-card);
            border-radius: 8px;
            padding: 10px;
            position: relative;
            margin-bottom: 12px;
            min-height: 60px;
        }

        .preview-caption-box .ai-tag {
            position: absolute;
            top: -8px;
            right: 10px;
            background: #1e3a8a;
            color: var(--gold-300);
            font-size: 0.65rem;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 8px;
        }

        .demo-caption-text {
            font-size: 0.8rem;
            color: var(--text-primary);
            line-height: 1.45;
            transition: opacity 0.15s ease;
        }

        .preview-bottom-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.725rem;
        }

        .preview-platforms {
            display: flex;
            gap: 6px;
        }

        .platform-pill {
            background: var(--bg-card-header);
            border: 1px solid var(--border-subtle);
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .preview-media-badge {
            color: var(--text-muted);
            font-weight: 600;
        }

        .demo-interactive-action {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-card-header);
            padding: 8px 12px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-card);
        }

        .demo-interactive-action span {
            font-size: 0.775rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .btn-sparkle {
            background: var(--navy-800);
            color: #ffffff;
            border: none;
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 0.75rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.15s ease, background 0.15s ease;
        }

        [data-theme="dark"] .btn-sparkle {
            background: #1d4ed8;
        }

        .btn-sparkle:hover, .btn-sparkle-active {
            background: var(--gold-500) !important;
            color: #030d24 !important;
            transform: scale(1.03);
        }

        /* ── Modern Minimal Badges ── */
        .float-tag {
            position: absolute;
            background: var(--bg-surface);
            border-radius: var(--radius-md);
            padding: 8px 14px;
            box-shadow: var(--shadow-card);
            border: 1px solid var(--border-card);
            display: flex;
            align-items: center;
            gap: 10px;
            z-index: 4;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transition: border-color 0.2s ease, transform 0.2s ease;
        }

        .float-tag:hover {
            transform: translateY(-2px);
            border-color: rgba(59, 130, 246, 0.4);
        }

        .float-tag-1 {
            top: 6px;
            right: -10px;
        }

        .float-tag-2 {
            bottom: 6px;
            left: -10px;
        }

        .float-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .float-meta h6 {
            font-size: 0.775rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .float-meta p {
            font-size: 0.7rem;
            color: var(--text-muted);
        }

        /* ── Stats Section ── */
        .stats-section {
            padding: 20px 24px;
            margin-bottom: 40px;
        }

        .stats-card-wrapper {
            max-width: 1100px;
            margin: 0 auto;
            background: var(--bg-surface);
            border: 1px solid var(--border-card);
            border-radius: var(--radius-lg);
            padding: 28px 32px;
            box-shadow: var(--shadow-card);
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .stat-box {
            text-align: center;
            position: relative;
        }

        .stat-box:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 15%;
            right: 0;
            height: 70%;
            width: 1px;
            background: var(--stat-border);
        }

        .stat-value {
            font-family: var(--font-heading);
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-value span {
            color: var(--gold-400);
        }

        .stat-name {
            font-size: 0.825rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        /* ── Bento Grid Section ── */
        .features-section {
            padding: 60px 24px;
        }

        .section-head {
            text-align: center;
            max-width: 640px;
            margin: 0 auto 44px;
        }

        .section-tag {
            display: inline-block;
            background: var(--navy-50);
            color: var(--gold-400);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 4px 12px;
            border-radius: var(--radius-full);
            margin-bottom: 12px;
            border: 1px solid var(--border-subtle);
        }

        .section-headline {
            font-size: clamp(1.8rem, 3vw, 2.6rem);
            line-height: 1.2;
            margin-bottom: 12px;
        }

        .section-desc {
            font-size: 0.975rem;
            color: var(--text-secondary);
        }

        .bento-grid {
            display: grid;
            grid-template-columns: repeat(12, 1fr);
            gap: 20px;
            max-width: 1140px;
            margin: 0 auto;
        }

        .bento-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-card);
            border-radius: var(--radius-lg);
            padding: 30px 26px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease, box-shadow 0.25s ease;
            box-shadow: var(--shadow-card);
        }

        .bento-card:hover {
            transform: translateY(-6px);
            border-color: var(--gold-400);
            box-shadow: var(--shadow-hover);
        }

        .bento-col-8 { grid-column: span 8; }
        .bento-col-4 { grid-column: span 4; }

        .bento-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin-bottom: 16px;
        }

        .bento-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .bento-text {
            font-size: 0.9rem;
            color: var(--text-secondary);
            line-height: 1.55;
            margin-bottom: 20px;
        }

        .bento-visual-ai {
            background: var(--bg-subtle);
            border: 1px solid var(--border-card);
            border-radius: var(--radius-md);
            padding: 14px;
        }

        .ai-prompt-chip {
            display: inline-block;
            background: var(--bg-card-header);
            border: 1px solid var(--border-subtle);
            font-size: 0.725rem;
            font-weight: 600;
            padding: 2px 7px;
            border-radius: 4px;
            margin-bottom: 6px;
            color: var(--gold-400);
        }

        .ai-output-stream {
            font-size: 0.8rem;
            color: var(--text-primary);
            line-height: 1.45;
        }

        .ai-output-stream .cursor {
            display: inline-block;
            width: 4px;
            height: 12px;
            background: var(--gold-500);
            margin-left: 2px;
            vertical-align: middle;
        }

        .bento-visual-timeline {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            padding: 10px 0;
        }

        .bento-visual-timeline::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 8px;
            right: 8px;
            height: 2px;
            background: var(--border-card);
            z-index: 1;
        }

        .timeline-step-dot {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--bg-surface);
            border: 2px solid var(--border-card);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--text-muted);
            position: relative;
            z-index: 2;
        }

        .timeline-step-dot.done {
            background: #10b981;
            border-color: #10b981;
            color: #ffffff;
        }

        .timeline-step-dot.active {
            background: var(--gold-500);
            border-color: var(--gold-500);
            color: #030d24;
        }

        .bento-visual-chat {
            background: var(--bg-subtle);
            border: 1px solid var(--border-card);
            border-radius: var(--radius-md);
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .chat-bubble-mini {
            padding: 6px 10px;
            border-radius: 10px;
            font-size: 0.725rem;
            max-width: 90%;
            line-height: 1.35;
        }

        .chat-bubble-mini.admin {
            background: #1d4ed8;
            color: #ffffff;
            align-self: flex-start;
        }

        .chat-bubble-mini.user {
            background: var(--bg-surface-elevated);
            color: var(--text-primary);
            align-self: flex-end;
            border: 1px solid var(--border-card);
        }

        .bento-calendar-preview {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .cal-mini-day {
            background: var(--bg-subtle);
            border: 1px solid var(--border-card);
            border-radius: var(--radius-sm);
            padding: 10px;
            text-align: center;
        }

        .cal-mini-day.active {
            background: var(--bg-surface-elevated);
            border-color: var(--gold-500);
        }

        .day-num {
            display: block;
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--text-primary);
        }

        .day-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin: 2px 0 4px;
        }

        .bg-amber { background: var(--gold-500); }
        .bg-blue { background: #3b82f6; }
        .bg-green { background: #10b981; }

        .day-label {
            display: block;
            font-size: 0.7rem;
            color: var(--text-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* ── 3-Step Process ── */
        .process-section {
            padding: 70px 24px;
        }

        .steps-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            max-width: 1100px;
            margin: 0 auto;
        }

        .step-card-modern {
            background: var(--bg-surface);
            border: 1px solid var(--border-card);
            border-radius: var(--radius-lg);
            padding: 32px 26px;
            box-shadow: var(--shadow-card);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease;
        }

        .step-card-modern:hover {
            transform: translateY(-6px);
            border-color: var(--gold-400);
        }

        .step-number-badge {
            font-family: var(--font-heading);
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--gold-400);
            line-height: 1;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .step-number-badge svg {
            color: var(--text-secondary);
            opacity: 0.4;
        }

        .step-card-title {
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .step-card-text {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.55;
        }

        /* ── FAQ Section ── */
        .faq-section {
            padding: 70px 24px;
            max-width: 820px;
            margin: 0 auto;
        }

        .faq-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .faq-item {
            background: var(--bg-surface);
            border: 1px solid var(--border-card);
            border-radius: var(--radius-md);
            overflow: hidden;
            transition: border-color 0.15s ease;
        }

        .faq-item.open {
            border-color: var(--gold-400);
        }

        .faq-question {
            padding: 18px 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            font-size: 0.975rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .faq-chevron {
            width: 18px;
            height: 18px;
            color: var(--gold-400);
            transition: transform 0.2s ease;
            flex-shrink: 0;
        }

        .faq-item.open .faq-chevron {
            transform: rotate(180deg);
        }

        .faq-answer {
            display: none;
            padding: 0 22px 18px;
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .faq-item.open .faq-answer {
            display: block;
        }

        /* ── CTA Banner ── */
        .cta-wrapper {
            padding: 40px 24px 80px;
        }

        .cta-box {
            max-width: 1100px;
            margin: 0 auto;
            background: linear-gradient(135deg, #020917 0%, #071536 60%, #0d2a6b 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--radius-xl);
            padding: 60px 32px;
            text-align: center;
            box-shadow: var(--shadow-card);
        }

        .cta-box h2 {
            font-size: clamp(1.8rem, 3.2vw, 2.6rem);
            color: #ffffff;
            margin-bottom: 14px;
        }

        .cta-box p {
            font-size: 1.05rem;
            color: rgba(255, 255, 255, 0.75);
            max-width: 560px;
            margin: 0 auto 30px;
        }

        .cta-actions {
            display: flex;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        /* ── Footer ── */
        .footer-modern {
            background: #020817;
            color: rgba(255, 255, 255, 0.6);
            padding: 56px 24px 28px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        .footer-top {
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            gap: 40px;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }

        .footer-brand-block {
            max-width: 340px;
        }

        .footer-logo-wrap img {
            height: 44px;
            width: auto;
            filter: brightness(1.2);
            margin-bottom: 12px;
        }

        .footer-tagline {
            font-size: 0.875rem;
            line-height: 1.55;
        }

        .footer-nav-groups {
            display: flex;
            gap: 48px;
            flex-wrap: wrap;
        }

        .footer-group h4 {
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #ffffff;
            margin-bottom: 14px;
        }

        .footer-group ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .footer-group a {
            color: rgba(255, 255, 255, 0.65);
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.15s ease;
        }

        .footer-group a:hover {
            color: var(--gold-400);
        }

        .footer-bottom {
            max-width: 1100px;
            margin: 0 auto;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.775rem;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* ── Comprehensive Responsive System (Phones & Tablets) ── */
        @media (max-width: 1200px) {
            .hero-container {
                gap: 40px;
            }
            .float-tag-1 {
                right: -6px;
                top: -8px;
            }
            .float-tag-2 {
                left: -6px;
                bottom: -8px;
            }
        }

        /* ── Tablets (Landscape & Portrait: 641px - 1024px) ── */
        @media (max-width: 1024px) {
            .nav-menu {
                gap: 16px;
            }
            .nav-link {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 992px) {
            .nav-menu {
                display: none;
            }

            .nav-container {
                padding: 12px 20px;
            }

            .hero {
                padding: 110px 20px 48px;
            }

            .hero-container {
                grid-template-columns: 1fr;
                text-align: center;
                gap: 36px;
            }

            .hero-lead {
                margin-left: auto;
                margin-right: auto;
                max-width: 560px;
            }

            .hero-cta-group, .trust-pills {
                justify-content: center;
            }

            .hero-right {
                max-width: 520px;
                margin: 0 auto;
                width: 100%;
                padding: 18px 10px;
            }

            .float-tag {
                transform: scale(0.88);
            }
            .float-tag-1 {
                right: -2px;
                top: -6px;
            }
            .float-tag-2 {
                left: -2px;
                bottom: -6px;
            }

            .stats-section {
                padding: 10px 20px;
            }

            .stats-card-wrapper {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
                padding: 24px 20px;
            }

            .stat-box:nth-child(2)::after {
                display: none;
            }

            .features-section, .process-section, .faq-section {
                padding: 60px 20px;
            }

            .section-headline {
                font-size: 2.1rem;
            }

            .bento-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .bento-col-8, .bento-col-4 {
                grid-column: span 1;
            }

            .steps-container {
                grid-template-columns: 1fr;
                max-width: 480px;
                margin: 0 auto;
                gap: 18px;
            }

            .cta-wrapper {
                padding: 40px 20px;
            }

            .cta-box {
                padding: 44px 28px;
                text-align: center;
            }

            .cta-box h2 {
                font-size: 2.1rem;
            }

            .footer-modern {
                padding: 48px 20px 28px;
            }

            .footer-top {
                flex-direction: column;
                gap: 32px;
            }

            .footer-nav-groups {
                gap: 40px;
            }
        }

        /* ── Mobile Phones (320px - 640px) ── */
        @media (max-width: 640px) {
            .nav-container {
                padding: 10px 14px;
            }

            .nav-brand img {
                height: 36px;
            }

            .nav-actions {
                gap: 6px;
            }

            .theme-toggle-btn {
                width: 34px;
                height: 34px;
            }

            .btn-sm {
                padding: 6px 12px;
                font-size: 0.775rem;
            }

            .hero {
                padding: 85px 16px 36px;
            }

            .badge-live {
                font-size: 0.75rem;
                padding: 4px 10px;
                margin-bottom: 14px;
            }

            .hero-title {
                font-size: 2.1rem;
                line-height: 1.15;
                letter-spacing: -0.02em;
                margin-bottom: 14px;
            }

            .hero-lead {
                font-size: 0.925rem;
                line-height: 1.55;
                margin-bottom: 22px;
            }

            .hero-cta-group {
                flex-direction: column;
                width: 100%;
                gap: 10px;
                margin-bottom: 24px;
            }

            .hero-cta-group .btn {
                width: 100%;
                justify-content: center;
                padding: 12px 20px;
                font-size: 0.95rem;
            }

            .trust-pills {
                gap: 8px 12px;
                justify-content: center;
                font-size: 0.75rem;
            }

            .trust-item {
                font-size: 0.75rem;
            }

            .hero-right {
                padding: 10px 0;
                max-width: 100%;
            }

            .float-tag {
                display: none;
            }

            .demo-window {
                border-radius: var(--radius-md);
            }

            .demo-body {
                padding: 14px 12px;
            }

            .demo-tabs-label {
                font-size: 0.7rem;
                margin-bottom: 6px;
            }

            .demo-tabs {
                grid-template-columns: repeat(3, 1fr);
                gap: 4px;
                padding: 3px;
                margin-bottom: 12px;
            }

            .demo-tab-btn {
                padding: 6px 4px;
                font-size: 0.725rem;
            }

            .demo-card-preview {
                padding: 14px 12px;
                border-radius: 12px;
            }

            .preview-avatar {
                width: 32px;
                height: 32px;
                font-size: 0.75rem;
            }

            .preview-org-meta h5 {
                font-size: 0.8rem;
                line-height: 1.25;
            }

            .preview-org-meta p {
                font-size: 0.675rem;
            }

            .status-chip {
                font-size: 0.65rem;
                padding: 2px 7px;
            }

            .preview-caption-box {
                padding: 10px 12px;
                font-size: 0.78rem;
                line-height: 1.45;
                margin: 10px 0;
            }

            .preview-bottom-bar {
                gap: 6px;
                flex-wrap: wrap;
            }

            .platform-pill {
                font-size: 0.675rem;
                padding: 2px 6px;
            }

            .preview-media-badge {
                font-size: 0.675rem;
            }

            .demo-interactive-action {
                flex-direction: column;
                align-items: stretch;
                gap: 8px;
                text-align: center;
            }

            .demo-interactive-action span {
                font-size: 0.725rem;
            }

            .btn-sparkle {
                width: 100%;
                padding: 8px 12px;
                font-size: 0.75rem;
            }

            .stats-section {
                padding: 10px 14px;
                margin-bottom: 24px;
            }

            .stats-card-wrapper {
                grid-template-columns: repeat(2, 1fr);
                gap: 14px 10px;
                padding: 18px 12px;
                border-radius: var(--radius-md);
            }

            .stat-value {
                font-size: 1.7rem;
            }

            .stat-name {
                font-size: 0.725rem;
            }

            .stat-box::after {
                display: none !important;
            }

            .features-section, .process-section, .faq-section {
                padding: 45px 16px;
            }

            .section-tag {
                font-size: 0.725rem;
                padding: 3px 10px;
            }

            .section-headline {
                font-size: 1.75rem;
                line-height: 1.2;
            }

            .section-desc {
                font-size: 0.9rem;
            }

            .bento-card {
                padding: 20px 16px;
                border-radius: var(--radius-md);
            }

            .bento-title {
                font-size: 1.15rem;
            }

            .bento-text {
                font-size: 0.85rem;
                line-height: 1.5;
            }

            .bento-visual-ai {
                padding: 12px;
            }

            .ai-prompt-chip {
                font-size: 0.675rem;
            }

            .ai-output-stream {
                font-size: 0.78rem;
            }

            .bento-calendar-preview {
                flex-direction: column;
                gap: 8px;
            }

            .step-card-modern {
                padding: 22px 16px;
                border-radius: var(--radius-md);
            }

            .step-card-title {
                font-size: 1.15rem;
            }

            .step-card-text {
                font-size: 0.85rem;
            }

            .faq-list {
                gap: 10px;
            }

            .faq-question {
                padding: 14px 16px;
                font-size: 0.875rem;
            }

            .faq-answer {
                padding: 0 16px 14px;
                font-size: 0.825rem;
            }

            .cta-wrapper {
                padding: 36px 16px;
            }

            .cta-box {
                padding: 32px 18px;
                border-radius: var(--radius-md);
                text-align: center;
            }

            .cta-box h2 {
                font-size: 1.65rem;
                line-height: 1.25;
            }

            .cta-box p {
                font-size: 0.9rem;
                margin-bottom: 22px;
            }

            .cta-actions {
                flex-direction: column;
                width: 100%;
                gap: 10px;
            }

            .cta-actions .btn {
                width: 100%;
                padding: 12px 20px;
                font-size: 0.95rem;
                justify-content: center;
            }

            .footer-modern {
                padding: 36px 16px 20px;
            }

            .footer-top {
                flex-direction: column;
                gap: 24px;
            }

            .footer-nav-groups {
                flex-direction: column;
                gap: 18px;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
                gap: 8px;
                font-size: 0.725rem;
            }
        }

        /* ── Extra Small Phones (<= 380px) ── */
        @media (max-width: 380px) {
            .nav-actions .btn-subtle {
                display: none;
            }

            .hero-title {
                font-size: 1.85rem;
            }

            .stat-value {
                font-size: 1.45rem;
            }
        }
    </style>
</head>
<body>
    <div id="np-page-progress"></div>

    <div class="ambient-bg" aria-hidden="true">
        <div class="ambient-glow glow-top"></div>
        <div class="ambient-glow glow-bottom"></div>
    </div>

    <!-- ── NAVBAR ── -->
    <header class="navbar" id="navbar">
        <div class="nav-container">
            <a href="/" class="nav-brand">
                <img src="{{ asset('assets/nupostlogo.png') }}" alt="NUPost Logo" width="130" height="42">
            </a>
            
            <nav class="nav-menu">
                <a href="#features" class="nav-link">Features</a>
                <a href="#how-it-works" class="nav-link">Process</a>
                <a href="#demo" class="nav-link">Interactive Demo</a>
                <a href="#faq" class="nav-link">FAQ</a>
            </nav>

            <div class="nav-actions">
                <button id="themeToggle" class="theme-toggle-btn" aria-label="Toggle Theme" title="Toggle Dark/Light Mode">
                    <svg class="sun-icon" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="5"></circle>
                        <line x1="12" y1="1" x2="12" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="23"></line>
                        <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                        <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                        <line x1="1" y1="12" x2="3" y2="12"></line>
                        <line x1="21" y1="12" x2="23" y2="12"></line>
                        <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                        <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                    </svg>
                    <svg class="moon-icon" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                    </svg>
                </button>

                <a href="{{ route('login') }}" class="btn btn-sm btn-subtle">Sign In</a>
                <a href="{{ route('register') }}" class="btn btn-sm btn-gold">Get Started</a>
            </div>
        </div>
    </header>

    <main>
        <!-- ── HERO SECTION ── -->
        <section class="hero" id="hero">
            <div class="hero-container">
                
                <div class="hero-left">
                    <div class="badge-live">
                        <span class="pulse-dot"></span>
                        <span>Official NU Lipa Platform</span>
                    </div>
                    
                    <h1 class="hero-title">
                        Request. Track.<br>
                        <span class="gradient-text">Post. NU Post.</span>
                    </h1>
                    
                    <p class="hero-lead">
                        The all-in-one social media request platform built for NU Lipa departments and student organizations. From idea to approved publication in minutes.
                    </p>
                    
                    <div class="hero-cta-group">
                        <a href="{{ route('login') }}" class="btn btn-lg btn-navy">
                            <span>Submit a Request</span>
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                        <a href="#demo" class="btn btn-lg btn-subtle">
                            <span>Try Live Simulator</span>
                        </a>
                    </div>
                    
                    <div class="trust-pills">
                        <div class="trust-item">
                            <span class="trust-check">✓</span>
                            <span>AI Caption Generation</span>
                        </div>
                        <div class="trust-item">
                            <span class="trust-check">✓</span>
                            <span>Direct Admin Feedback</span>
                        </div>
                        <div class="trust-item">
                            <span class="trust-check">✓</span>
                            <span>Live Status Tracking</span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Interactive Simulator with Levitating Floating Badges -->
                <div class="hero-right" id="demo">
                    <!-- Floating Badge 1 -->
                    <div class="float-tag float-tag-1">
                        <div class="float-icon" style="background:rgba(34,197,94,0.15);color:#4ade80;">
                            <svg width="18" height="18" fill="none" stroke="#22c55e" stroke-width="2.2" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                        </div>
                        <div class="float-meta">
                            <h6>Rapid Review</h6>
                            <p>Average &lt; 24h turnaround</p>
                        </div>
                    </div>

                    <!-- Simulator Window -->
                    <div class="demo-window">
                        <div class="demo-header">
                            <div class="demo-dots">
                                <span class="demo-dot red"></span>
                                <span class="demo-dot yellow"></span>
                                <span class="demo-dot green"></span>
                            </div>
                            <div class="demo-badge-interactive">
                                <span>Live Preview</span>
                            </div>
                        </div>

                        <div class="demo-body">
                            <div class="demo-tabs-label">
                                <span>Select Post Category</span>
                                <span class="hint">Click to test live preview</span>
                            </div>

                            <div class="demo-tabs">
                                <button class="demo-tab-btn active" data-type="event">Event</button>
                                <button class="demo-tab-btn" data-type="sports">Sports</button>
                                <button class="demo-tab-btn" data-type="org">Notice</button>
                            </div>

                            <!-- Live Card Preview -->
                            <div class="demo-card-preview">
                                <div class="preview-top">
                                    <div class="preview-org">
                                        <div class="preview-avatar" id="demoAvatar">NU</div>
                                        <div class="preview-org-meta">
                                            <h5 id="demoTitle">College of Computing — Tech Summit 2026</h5>
                                            <p id="demoOrg">Target Date: May 24, 2026 • 2:00 PM</p>
                                        </div>
                                    </div>
                                    <span class="status-chip chip-approved" id="demoStatus">Approved</span>
                                </div>

                                <div class="preview-caption-box">
                                    <span class="ai-tag"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:-1px;margin-right:3px;"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg> Gemini AI</span>
                                    <p id="demoCaption" class="demo-caption-text">
                                        "Gear up for innovation! Join us at the NU Lipa Tech Summit 2026. Explore hands-on AI workshops and keynote talks from industry leaders. #NULipa #TechSummit2026 #NationalUniversity"
                                    </p>
                                </div>

                                <div class="preview-bottom-bar">
                                    <div class="preview-platforms">
                                        <span class="platform-pill">Facebook</span>
                                        <span class="platform-pill">Instagram</span>
                                    </div>
                                    <div class="preview-media-badge">
                                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:-1px;margin-right:4px;"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg> 3 Assets Attached
                                    </div>
                                </div>
                            </div>

                            <div class="demo-interactive-action">
                                <span id="demoActionHint">Need captions? Let AI assist:</span>
                                <button type="button" class="btn-sparkle" id="btnGenCaption">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" style="vertical-align:-1px;margin-right:5px;"><path d="M23 4v6h-6"/><path d="M1 20v-6h6"/><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"/></svg> Regenerate AI Caption
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Floating Badge 2 -->
                    <div class="float-tag float-tag-2">
                        <div class="float-icon" style="background:rgba(59,130,246,0.15);color:#60a5fa;">
                            <svg width="18" height="18" fill="none" stroke="#3b82f6" stroke-width="2.2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </div>
                        <div class="float-meta">
                            <h6>Verified NU Accounts</h6>
                            <p>Role-based access control</p>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- ── STATS SECTION ── -->
        <section class="stats-section">
            <div class="stats-card-wrapper">
                <div class="stat-box">
                    <div class="stat-value">100<span>%</span></div>
                    <div class="stat-name">Paperless Requests</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">< <span>24h</span></div>
                    <div class="stat-name">Fast Turnaround</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">3<span>+</span></div>
                    <div class="stat-name">Channels Published</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">24<span>/7</span></div>
                    <div class="stat-name">Real-Time Tracking</div>
                </div>
            </div>
        </section>

        <!-- ── BENTO FEATURES SECTION ── -->
        <section class="features-section" id="features">
            <div class="section-head">
                <div class="section-tag">Powerful Features</div>
                <h2 class="section-headline">Everything needed to streamline campus communication</h2>
                <p class="section-desc">Designed specifically for the NU Lipa Marketing Office, academic departments, and institutional offices.</p>
            </div>

            <div class="bento-grid">
                <div class="bento-card bento-col-8">
                    <div>
                        <div class="bento-icon" style="background:rgba(245,158,11,0.15);color:#fbbf24;">
                            <svg width="22" height="22" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        </div>
                        <h3 class="bento-title">AI-Powered Social Media Captioning</h3>
                        <p class="bento-text">Stuck on what to write? Our integrated Gemini AI crafts tailored, engaging captions with relevant hashtags and event highlights in seconds.</p>
                    </div>
                    <div class="bento-visual-ai">
                        <span class="ai-prompt-chip">Prompt: Campus Intramurals 2026 Announcement</span>
                        <div class="ai-output-stream">
                            "Unleash the Bulldog spirit! Are you ready to champion your college? Intramurals 2026 kickstarts this Monday at the Main Gymnasium. Be there, be proud!"<span class="cursor"></span>
                        </div>
                    </div>
                </div>

                <div class="bento-card bento-col-4">
                    <div>
                        <div class="bento-icon" style="background:rgba(59,130,246,0.15);color:#60a5fa;">
                            <svg width="22" height="22" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        </div>
                        <h3 class="bento-title">Live Tracking</h3>
                        <p class="bento-text">Know exactly when your post is queued, in review, or published.</p>
                    </div>
                    <div class="bento-visual-timeline">
                        <div class="timeline-step-dot done" title="Submitted">✓</div>
                        <div class="timeline-step-dot done" title="Review">✓</div>
                        <div class="timeline-step-dot active" title="Approved">●</div>
                        <div class="timeline-step-dot" title="Posted">4</div>
                    </div>
                </div>

                <div class="bento-card bento-col-4">
                    <div>
                        <div class="bento-icon" style="background:rgba(168,85,247,0.15);color:#c084fc;">
                            <svg width="22" height="22" fill="none" stroke="#a855f7" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                        </div>
                        <h3 class="bento-title">Admin Feedback</h3>
                        <p class="bento-text">Direct 1-on-1 comments per request for fast revisions and creative approval.</p>
                    </div>
                    <div class="bento-visual-chat">
                        <div class="chat-bubble-mini admin">"Please upload the high-res poster format!"</div>
                        <div class="chat-bubble-mini user">"Done! Attached the 4K PNG file."</div>
                    </div>
                </div>

                <div class="bento-card bento-col-8">
                    <div>
                        <div class="bento-icon" style="background:rgba(34,197,94,0.15);color:#4ade80;">
                            <svg width="22" height="22" fill="none" stroke="#22c55e" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </div>
                        <h3 class="bento-title">Conflict-Free Visual Calendar</h3>
                        <p class="bento-text">Prevent overlapping promotions and schedule posts strategically across official social channels with our interactive schedule grid.</p>
                    </div>
                    <div class="bento-calendar-preview">
                        <div class="cal-mini-day active">
                            <span class="day-num">18</span>
                            <span class="day-dot bg-amber"></span>
                            <span class="day-label">General Assembly</span>
                        </div>
                        <div class="cal-mini-day">
                            <span class="day-num">19</span>
                            <span class="day-dot bg-blue"></span>
                            <span class="day-label">Quiz Bee</span>
                        </div>
                        <div class="cal-mini-day">
                            <span class="day-num">20</span>
                            <span class="day-dot bg-green"></span>
                            <span class="day-label">Bulldog Sports</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── 3-STEP PROCESS SECTION ── -->
        <section class="process-section" id="how-it-works">
            <div class="section-head">
                <div class="section-tag">Streamlined Flow</div>
                <h2 class="section-headline">From Submission to Social Media in 3 Steps</h2>
                <p class="section-desc">No complicated paper forms. Everything is tracked digitally in real time.</p>
            </div>

            <div class="steps-container">
                <div class="step-card-modern">
                    <div class="step-number-badge">
                        <span>01</span>
                        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                    </div>
                    <h3 class="step-card-title">Create Request</h3>
                    <p class="step-card-text">Provide event details, select posting dates, target channels, and upload your graphic assets or pubmats.</p>
                </div>

                <div class="step-card-modern">
                    <div class="step-number-badge">
                        <span>02</span>
                        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    </div>
                    <h3 class="step-card-title">Review & Refine</h3>
                    <p class="step-card-text">The Marketing Office verifies brand guidelines, polishes captions with AI, and coordinates via direct chat.</p>
                </div>

                <div class="step-card-modern">
                    <div class="step-number-badge">
                        <span>03</span>
                        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 14 14"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                    </div>
                    <h3 class="step-card-title">Publish & Monitor</h3>
                    <p class="step-card-text">Content goes live on scheduled channels. Monitor post reach, engagement, and confirmed status.</p>
                </div>
            </div>
        </section>

        <!-- ── FAQ SECTION ── -->
        <section class="faq-section" id="faq">
            <div class="section-head">
                <div class="section-tag">Frequently Asked Questions</div>
                <h2 class="section-headline">Got Questions? We have answers.</h2>
            </div>

            <div class="faq-list">
                <div class="faq-item open">
                    <div class="faq-question">
                        <span>Who can submit posting requests?</span>
                        <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="faq-answer">
                        Any recognized student organization, academic department, faculty member, or office head with a verified NU Lipa email address can submit and manage requests.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>How far in advance should I submit my post request?</span>
                        <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="faq-answer">
                        We recommend submitting requests at least 3–5 business days before your target posting date to ensure proper marketing review, branding compliance, and optimal scheduling.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>How does the AI caption generator work?</span>
                        <svg class="faq-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                    </div>
                    <div class="faq-answer">
                        When creating or editing a request, simply click "Generate AI Caption". Our integrated AI analyzes your title, event context, and target platforms to produce catchy, professional captions and hashtags in seconds.
                    </div>
                </div>
            </div>
        </section>

        <!-- ── CTA BANNER ── -->
        <section class="cta-wrapper">
            <div class="cta-box">
                <h2>Ready to publish your next campus event?</h2>
                <p>Join student leaders and department officers who organize and schedule social media campaigns seamlessly with NUPost.</p>
                <div class="cta-actions">
                    <a href="{{ route('register') }}" class="btn btn-lg btn-gold">Create an Account</a>
                    <a href="{{ route('login') }}" class="btn btn-lg btn-subtle" style="color:#ffffff;background:rgba(255,255,255,0.12);border-color:rgba(255,255,255,0.2);">Sign In to Platform</a>
                </div>
            </div>
        </section>
    </main>

    <!-- ── FOOTER ── -->
    <footer class="footer-modern">
        <div class="footer-top">
            <div class="footer-brand-block">
                <div class="footer-logo-wrap">
                    <img src="{{ asset('assets/Title.png') }}" alt="NUPost Title Logo" height="50">
                </div>
                <p class="footer-tagline">The official social media request and management system for National University Lipa.</p>
            </div>
            
            <div class="footer-nav-groups">
                <div class="footer-group">
                    <h4>Navigation</h4>
                    <ul>
                        <li><a href="#hero">Overview</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#how-it-works">Process Flow</a></li>
                        <li><a href="#faq">FAQ</a></li>
                    </ul>
                </div>
                <div class="footer-group">
                    <h4>Account</h4>
                    <ul>
                        <li><a href="{{ route('login') }}">Sign In</a></li>
                        <li><a href="{{ route('register') }}">Create Account</a></li>
                        <li><a href="{{ route('password.forgot') }}">Forgot Password</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; {{ date('Y') }} NU Lipa Marketing Office. All rights reserved.</span>
            <span>National University Lipa • Education That Works</span>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const themeToggle = document.getElementById('themeToggle');
            themeToggle.addEventListener('click', () => {
                const current = document.documentElement.getAttribute('data-theme') || 'dark';
                const next = current === 'dark' ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', next);
                localStorage.setItem('nupost-theme', next);
            });

            const navbar = document.getElementById('navbar');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 30) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            }, { passive: true });

            const demoData = {
                event: {
                    avatar: "CS",
                    title: "College of Computing — Tech Summit 2026",
                    org: "Target Date: May 24, 2026 • 2:00 PM",
                    status: "Approved",
                    statusClass: "chip-approved",
                    captions: [
                        '"Gear up for innovation! Join us at the NU Lipa Tech Summit 2026. Explore hands-on AI workshops and keynote talks from industry leaders. #NULipa #TechSummit2026"',
                        '"Code, build, and lead! The future of technology unfolds at NU Lipa Tech Summit 2026. Free admission for all students! #BulldogPride #CCIT"',
                        '"Level up your digital skills! Join our interactive workshop tracks this May 24th at the Multipurpose Hall. Register now! #NUPost #NULipa"'
                    ]
                },
                sports: {
                    avatar: "ATH",
                    title: "Bulldogs Athletics — University Games 2026",
                    org: "Target Date: June 02, 2026 • 9:00 AM",
                    status: "Scheduled",
                    statusClass: "chip-scheduled",
                    captions: [
                        '"Hear the Bulldogs roar! Catch our varsity teams in action at the Southern Luzon University Games. Let\'s bring home the championship! #GoBulldogs #NUAthletics"',
                        '"Game day ready! Support our athletes as they defend the court this Tuesday. Gates open at 8:00 AM. Wear your Gold & Blue! #BulldogPride"',
                        '"Unstoppable spirit, unmatched energy! Watch the livestreams right here on our official Facebook page. #NULipaSports"'
                    ]
                },
                org: {
                    avatar: "OAA",
                    title: "Office of Academic Affairs — General Assembly",
                    org: "Target Date: May 28, 2026 • 10:00 AM",
                    status: "Under Review",
                    statusClass: "chip-review",
                    captions: [
                        '"Your voice, your community! Join the Annual Academic General Assembly. Submit your agenda items and hear key institutional updates. #NULipa #OneNU"',
                        '"Transparency in action! All academic chairs and office representatives are invited to our term review this Thursday. #NULipa"',
                        '"Lead the institutional change! Check the link in bio for the complete assembly agenda and open forum guidelines. #AcademicLeadership"'
                    ]
                }
            };

            let currentType = 'event';
            let captionIndex = 0;

            const tabButtons = document.querySelectorAll('.demo-tab-btn');
            const demoAvatar = document.getElementById('demoAvatar');
            const demoTitle = document.getElementById('demoTitle');
            const demoOrg = document.getElementById('demoOrg');
            const demoStatus = document.getElementById('demoStatus');
            const demoCaption = document.getElementById('demoCaption');
            const btnGenCaption = document.getElementById('btnGenCaption');

            function updateDemo(type) {
                currentType = type;
                captionIndex = 0;
                const data = demoData[type];
                
                demoAvatar.textContent = data.avatar;
                demoTitle.textContent = data.title;
                demoOrg.textContent = data.org;
                demoStatus.textContent = data.status;
                demoStatus.className = 'status-chip ' + data.statusClass;
                
                typeWriterText(demoCaption, data.captions[0]);
            }

            function typeWriterText(element, text) {
                element.style.opacity = '0.5';
                setTimeout(() => {
                    element.textContent = text;
                    element.style.opacity = '1';
                }, 150);
            }

            tabButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    tabButtons.forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    updateDemo(btn.dataset.type);
                });
            });

            if (btnGenCaption) {
                btnGenCaption.addEventListener('click', () => {
                    const data = demoData[currentType];
                    captionIndex = (captionIndex + 1) % data.captions.length;
                    btnGenCaption.classList.add('btn-sparkle-active');
                    typeWriterText(demoCaption, data.captions[captionIndex]);
                    setTimeout(() => {
                        btnGenCaption.classList.remove('btn-sparkle-active');
                    }, 400);
                });
            }

            const faqItems = document.querySelectorAll('.faq-item');
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                question.addEventListener('click', () => {
                    const isOpen = item.classList.contains('open');
                    faqItems.forEach(other => other.classList.remove('open'));
                    if (!isOpen) {
                        item.classList.add('open');
                    }
                });
            });
        });
    </script>
    <!-- instant.page: Pre-fetches pages on link hover for 0ms navigation -->
    <script src="//instant.page/5.2.0" type="module" integrity="sha384-jnZyxPjiipYXnSU0Rck6V38m2BQHTEXv80QwJuJChhxLMgkE4g++aD4IVFzpBc1z"></script>
    <script>
        // Instant visual feedback on link clicks (YouTube style top bar)
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
