<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>NUPost Admin – @yield('title', 'Dashboard')</title>
<link rel="icon" type="image/png" href="{{ asset('assets/clearlogo.png') }}">
<link rel="apple-touch-icon" href="{{ asset('assets/clearlogo.png') }}">

<!-- Prevent Flash of Unstyled Theme -->
<script>
    (function() {
        const savedTheme = localStorage.getItem('nupost-theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    })();
</script>


<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=DM+Serif+Display&family=Syne:wght@700;800&display=swap" rel="stylesheet">
<style>
:root {
    --cream:       #f0ede8;
    --cream-dark:  #e8e3da;
    --card:        #faf9f7;
    --card-border: rgba(0,0,0,0.07);
    --navy:        #002366;
    --navy-mid:    #003a8c;
    --navy-light:  #1e4fd8;
    --navy-pale:   #e8eef8;
    --ink:         #1a1a1a;
    --ink-mid:     #3d3d3d;
    --ink-soft:    #7a7672;
    --ink-faint:   #b5b0a8;
    --amber:       #f59e0b;
    --green:       #10b981;
    --red:         #ef4444;
    --purple:      #8b5cf6;

    --sb-w:        80px;
    --sb-inner:    56px;
    --radius:      20px;
    --radius-sm:   14px;
    --font:        'DM Sans', sans-serif;
    --font-disp:   'DM Serif Display', serif;
    --shadow-sm:   0 2px 8px rgba(0,0,0,0.05);
    --shadow:      0 6px 24px rgba(0,0,0,0.08);
    --shadow-lg:   0 12px 40px rgba(0,0,0,0.12);
}

:root[data-theme="dark"],
html[data-theme="dark"],
[data-theme="dark"] {
    --cream:       #040c1e;
    --cream-dark:  #081636;
    --card:        #081636;
    --card2:       #0b1a3d;
    --card-border: rgba(30, 79, 216, 0.25);
    --sand:        #0a1b42;
    --stroke:      rgba(30, 79, 216, 0.25);
    --navy:        #002366;
    --navy-mid:    #0d388c;
    --navy-light:  #3b82f6;
    --navy-pale:   rgba(30, 79, 216, 0.25);
    --ink:         #f8fafc;
    --ink-mid:     #94a3b8;
    --ink-soft:    #64748b;
    --ink-faint:   #475569;
    --shadow-sm:   0 2px 8px rgba(0,0,0,0.3);
    --shadow:      0 6px 24px rgba(0,0,0,0.4);
    --shadow-lg:   0 12px 40px rgba(0,0,0,0.5);
}

/* ── DARK THEME COMPONENT OVERRIDES ── */
[data-theme="dark"] body {
    background: #040c1e !important;
    color: var(--ink) !important;
}

[data-theme="dark"] .main-wrapper,
[data-theme="dark"] .page-content {
    background: var(--cream) !important;
}

[data-theme="dark"] .card {
    background: var(--card) !important;
    border-color: var(--card-border) !important;
    color: var(--ink) !important;
}
[data-theme="dark"] .card__head {
    background: #06112a !important;
    border-bottom-color: var(--card-border) !important;
}
[data-theme="dark"] .card__title {
    color: var(--ink) !important;
}
[data-theme="dark"] .req-field-value {
    color: var(--ink-mid) !important;
}
[data-theme="dark"] .back-btn {
    background: var(--card) !important;
    border-color: var(--card-border) !important;
    color: var(--ink-mid) !important;
}
[data-theme="dark"] .back-btn:hover {
    background: #0b1a3d !important;
    color: var(--ink) !important;
}
[data-theme="dark"] .tabs {
    background: #06112a !important;
}
[data-theme="dark"] .tab-btn {
    color: var(--ink-soft) !important;
}
[data-theme="dark"] .tab-btn.active {
    background: #0b1a3d !important;
    color: var(--ink) !important;
}
[data-theme="dark"] .plat-pill {
    background: rgba(30, 79, 216, 0.25) !important;
    color: #93c5fd !important;
}
[data-theme="dark"] .comment-bubble--user {
    background: #0b1a3d !important;
    border-color: var(--card-border) !important;
    color: var(--ink) !important;
}
[data-theme="dark"] .comment-form-area {
    background: #0b1a3d !important;
    border-color: var(--card-border) !important;
}
[data-theme="dark"] .comment-textarea {
    color: var(--ink) !important;
}
[data-theme="dark"] .modal {
    background: var(--card) !important;
    border: 1px solid var(--card-border) !important;
}
[data-theme="dark"] .modal__head {
    background: var(--card) !important;
    border-bottom-color: var(--card-border) !important;
}
[data-theme="dark"] .modal__title {
    color: var(--ink) !important;
}
[data-theme="dark"] .modal__close {
    background: #0b1a3d !important;
    color: var(--ink-soft) !important;
}
[data-theme="dark"] .caption-textarea,
[data-theme="dark"] .prompt-input {
    background: #0b1a3d !important;
    color: var(--ink) !important;
    border-color: var(--card-border) !important;
}

[data-theme="dark"] .topbar {
    background: var(--card);
    border-bottom-color: var(--card-border);
}
[data-theme="dark"] .topbar__date {
    background: #0b1a3d;
    border-color: var(--card-border);
    color: var(--ink-mid);
}

[data-theme="dark"] .notif-btn {
    background: #0b1a3d;
    border-color: var(--card-border);
    color: var(--ink-soft);
}
[data-theme="dark"] .notif-btn:hover {
    background: #0f2250;
    color: var(--ink);
}
[data-theme="dark"] .notif-dropdown {
    background: var(--card);
    border-color: var(--card-border);
    box-shadow: var(--shadow-lg);
}
[data-theme="dark"] .notif-head {
    background: #06112a;
    border-bottom-color: var(--card-border);
}
[data-theme="dark"] .notif-head__title {
    color: var(--ink);
}
[data-theme="dark"] .notif-item {
    color: var(--ink);
    border-bottom-color: rgba(255,255,255,0.05);
}
[data-theme="dark"] .notif-item:hover {
    background: rgba(30, 79, 216, 0.12);
}
[data-theme="dark"] .notif-icon--default {
    background: #0d214d;
}
[data-theme="dark"] .notif-footer {
    background: #06112a;
    border-top-color: var(--card-border);
}

[data-theme="dark"] .admin-dropdown {
    background: var(--card);
    border-color: var(--card-border);
}
[data-theme="dark"] .admin-dropdown__header {
    background: #06112a;
    border-bottom-color: var(--card-border);
}
[data-theme="dark"] .admin-dropdown__name {
    color: var(--ink);
}
[data-theme="dark"] .admin-dropdown__item {
    color: var(--ink-mid);
}
[data-theme="dark"] .admin-dropdown__item:hover {
    background: rgba(30, 79, 216, 0.12);
    color: var(--ink);
}

[data-theme="dark"] .panel,
[data-theme="dark"] .stat-card,
[data-theme="dark"] .white-box,
[data-theme="dark"] .table-card,
[data-theme="dark"] .report-card {
    background: var(--card) !important;
    border-color: var(--card-border) !important;
    color: var(--ink) !important;
}
[data-theme="dark"] .panel__head {
    border-bottom-color: var(--card-border);
}
[data-theme="dark"] .panel__title {
    color: var(--ink);
}

[data-theme="dark"] input:not([type="checkbox"]):not([type="radio"]),
[data-theme="dark"] select,
[data-theme="dark"] textarea {
    background-color: #0b1a3d !important;
    color: #f8fafc !important;
    border-color: rgba(30, 79, 216, 0.3) !important;
}
[data-theme="dark"] input:focus,
[data-theme="dark"] select:focus,
[data-theme="dark"] textarea:focus {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
}
[data-theme="dark"] input::placeholder,
[data-theme="dark"] textarea::placeholder {
    color: #64748b !important;
}

[data-theme="dark"] table th {
    background-color: #06112a !important;
    color: #94a3b8 !important;
    border-color: var(--card-border) !important;
}
[data-theme="dark"] table td {
    border-color: rgba(30, 79, 216, 0.15) !important;
    color: var(--ink) !important;
}
[data-theme="dark"] table tr:hover td {
    background-color: rgba(30, 79, 216, 0.08) !important;
}

/* Theme Toggle Button */
.theme-toggle-btn {
    width: 38px; height: 38px; border-radius: 50%;
    background: #f4f6fb; border: 1.5px solid rgba(0,0,0,0.08);
    color: var(--ink); display: flex; align-items: center;
    justify-content: center; cursor: pointer;
    transition: background-color 0.2s ease, transform 0.2s ease, border-color 0.2s ease;
    flex-shrink: 0;
}
.theme-toggle-btn:hover {
    background: #e8ecf4; border-color: #f59e0b;
    transform: rotate(15deg);
}
.sun-icon { display: none; color: #fbbf24; }
.moon-icon { display: block; color: #002366; }
[data-theme="dark"] .theme-toggle-btn {
    background: #0b1a3d; border-color: rgba(30, 79, 216, 0.35);
    color: #f8fafc;
}
[data-theme="dark"] .theme-toggle-btn:hover {
    background: #0f2250; border-color: #f59e0b;
}
[data-theme="dark"] .sun-icon { display: block; }
[data-theme="dark"] .moon-icon { display: none; }
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body {
    height: 100%; width: 100%;
    font-family: var(--font);
    background: #111111;
    color: var(--ink);
    font-size: 14px;
    -webkit-font-smoothing: antialiased;
    overflow: hidden;
}

/* ── SHELL ─────────────────────────────────────────────── */
.shell { display: flex; height: 100vh; background: #111111; }

/* ══════════════════════════════════════════════════════
   SIDEBAR
══════════════════════════════════════════════════════ */
.sidebar {
    width: var(--sb-w);
    min-width: var(--sb-w);
    flex-shrink: 0;
    background: #111827;
    display: flex; flex-direction: column; align-items: center;
    padding: 18px 12px 18px; gap: 0; z-index: 200;
}

.sb-brand {
    width: var(--sb-inner); height: var(--sb-inner);
    border-radius: 16px; background: var(--navy);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; box-shadow: 0 4px 16px rgba(0,35,102,0.5);
    margin-bottom: 20px; cursor: default; transition: transform .2s;
    text-decoration: none;
}
.sb-brand:hover { transform: scale(1.06); }
.sb-brand svg { color: white; }

.sb-group {
    background: #1e2a3a; border-radius: 20px;
    padding: 6px; display: flex; flex-direction: column;
    gap: 2px; width: var(--sb-inner); flex-shrink: 0;
}
.sb-spacer { flex: 1; }

.sb-item {
    position: relative; width: 44px; height: 44px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.38); text-decoration: none;
    transition: all .2s cubic-bezier(.34,1.4,.64,1); cursor: pointer; flex-shrink: 0;
}
.sb-item svg { transition: transform .2s cubic-bezier(.34,1.4,.64,1); }
.sb-item:hover { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.85); }
.sb-item:hover svg { transform: scale(1.15); }

.sb-item--dashboard:hover { color: #7da8ff !important; }
.sb-item--requests:hover  { color: #6ee7b7 !important; }
.sb-item--calendar:hover  { color: #c084fc !important; }
.sb-item--analytics:hover { color: #fbbf24 !important; }
.sb-item--settings:hover  { color: #fb923c !important; }

.sb-item.active { color: white; box-shadow: 0 3px 12px rgba(0,0,0,0.3); }
.sb-item.active svg { transform: scale(1.05); }
.sb-item--dashboard.active { background: rgba(59,110,245,0.28); }
.sb-item--requests.active  { background: rgba(16,185,129,0.25); }
.sb-item--calendar.active  { background: rgba(168,85,247,0.25); }
.sb-item--analytics.active { background: rgba(245,158,11,0.25); }
.sb-item--settings.active  { background: rgba(249,115,22,0.22); }

.sb-item.active::before {
    content: ''; position: absolute;
    left: -6px; top: 28%; height: 44%;
    width: 3px; border-radius: 0 3px 3px 0;
    background: var(--amber);
}

/* Notification dot on sidebar icon */
.sb-item__dot {
    position: absolute; top: 8px; right: 8px;
    width: 7px; height: 7px; background: #ef4444;
    border-radius: 50%; border: 2px solid #111827;
    display: none;
}

/* Tooltip */
.sb-label {
    position: absolute; left: calc(100% + 16px); top: 50%;
    transform: translateY(-50%) scale(0.9) translateX(-6px);
    background: #0d1b3e; color: white;
    font-size: 11.5px; font-weight: 600; font-family: var(--font);
    padding: 6px 12px; border-radius: 10px; white-space: nowrap;
    opacity: 0; pointer-events: none;
    transition: all .18s cubic-bezier(.34,1.3,.64,1);
    box-shadow: 0 4px 16px rgba(0,0,0,0.3); z-index: 9999;
}
.sb-label::before {
    content: ''; position: absolute; left: -5px; top: 50%; transform: translateY(-50%);
    border: 5px solid transparent; border-right-color: #0d1b3e; border-left: 0;
}
.sb-item:hover .sb-label,
.sb-foot-item:hover .sb-label { opacity: 1; transform: translateY(-50%) scale(1) translateX(0); }

.sb-foot {
    display: flex; flex-direction: column; align-items: center;
    gap: 6px; width: var(--sb-inner); flex-shrink: 0;
}
.sb-foot-item {
    position: relative; width: 44px; height: 44px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.35); cursor: pointer;
    background: none; border: none;
    transition: all .2s cubic-bezier(.34,1.4,.64,1); font-family: var(--font);
}
.sb-foot-item:hover { background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.8); }
.sb-foot-item:hover svg { transform: scale(1.12); }
.sb-foot-item svg { transition: transform .2s; }
.sb-foot-item--logout:hover { background: rgba(239,68,68,0.15); color: #fca5a5; }
.sb-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: var(--navy); display: flex; align-items: center;
    justify-content: center; font-size: 14px; font-weight: 700; color: white;
    border: 2px solid rgba(255,255,255,0.12); flex-shrink: 0;
}

/* ══════════════════════════════════════════════════════
   FLOATING MAIN WRAPPER
══════════════════════════════════════════════════════ */
.main-wrapper {
    flex: 1;
    background: var(--cream);
    border-radius: 22px;
    margin: 10px 10px 10px 0;
    display: flex; flex-direction: column;
    overflow: hidden; min-width: 0;
}

/* ══════════════════════════════════════════════════════
   TOPBAR
══════════════════════════════════════════════════════ */
.topbar {
    height: 64px; background: var(--card);
    border-bottom: 1px solid rgba(0,0,0,0.06);
    display: flex; align-items: center;
    padding: 0 28px; gap: 14px; flex-shrink: 0;
    border-radius: 22px 22px 0 0;
}
.topbar__right { display: flex; align-items: center; gap: 10px; margin-left: auto; }
.topbar__date {
    padding: 7px 14px; border-radius: 20px;
    background: var(--card); border: 1px solid rgba(0,0,0,0.08);
    font-size: 12px; color: var(--ink-mid); font-weight: 600; white-space: nowrap;
}

/* ── NOTIFICATION BELL ─────────────────────────────── */
.notif-wrap { position: relative; }
.notif-btn {
    position: relative; width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    background: #f4f6fb; border: 1.5px solid rgba(0,0,0,0.08);
    cursor: pointer; color: var(--ink-soft); transition: all .15s;
}
.notif-btn:hover { background: var(--cream-dark); color: var(--ink); }
.notif-badge {
    position: absolute; top: -4px; right: -4px;
    min-width: 18px; height: 18px; border-radius: 9px;
    background: #ef4444; color: white;
    font-size: 10px; font-weight: 700;
    display: none; align-items: center; justify-content: center;
    padding: 0 4px; border: 2px solid var(--card);
}
.notif-badge.has-count { display: flex; }

/* Notification dropdown */
.notif-dropdown {
    position: absolute; top: calc(100% + 10px); right: 0;
    width: 360px; background: var(--card);
    border: 1px solid rgba(0,0,0,0.08);
    border-radius: 18px; box-shadow: var(--shadow-lg);
    z-index: 300; overflow: hidden; display: none;
    animation: dropIn .2s ease;
}
.notif-dropdown.open { display: block; }
@keyframes dropIn { from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:none} }

.notif-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px; border-bottom: 1px solid rgba(0,0,0,0.06);
    background: var(--cream-dark);
}
.notif-head__title { font-size: 14px; font-weight: 700; color: var(--ink); }
.notif-head__mark {
    font-size: 11.5px; font-weight: 600; color: var(--navy-light);
    cursor: pointer; background: none; border: none;
    font-family: var(--font); padding: 0;
}
.notif-head__mark:hover { text-decoration: underline; }

.notif-list { max-height: 380px; overflow-y: auto; }
.notif-list::-webkit-scrollbar { width: 4px; }
.notif-list::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 4px; }

.notif-item {
    display: flex; align-items: flex-start; gap: 11px;
    padding: 13px 18px; border-bottom: 1px solid rgba(0,0,0,0.04);
    cursor: pointer; transition: background .12s; text-decoration: none;
    position: relative; color: var(--ink);
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: var(--cream); }
.notif-item.unread { background: rgba(0,35,102,0.03); }
.notif-item.unread::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0;
    width: 3px; background: var(--navy-light); border-radius: 0 3px 3px 0;
}
.notif-icon {
    width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; font-size: 16px;
}
.notif-icon--request { background: #dbeafe; }
.notif-icon--comment { background: #dcfce7; }
.notif-icon--due     { background: #fef3c7; }
.notif-icon--overdue { background: #fee2e2; }
.notif-icon--default { background: var(--cream-dark); }
.notif-title { font-size: 12.5px; font-weight: 600; color: var(--ink); line-height: 1.4; }
.notif-msg   { font-size: 11.5px; color: var(--ink-soft); margin-top: 2px; line-height: 1.45; }
.notif-time  { font-size: 10.5px; color: var(--ink-faint); margin-top: 3px; }
.notif-empty {
    padding: 40px 20px; text-align: center; color: var(--ink-faint);
    display: flex; flex-direction: column; align-items: center; gap: 10px;
}
.notif-empty p { font-size: 13px; }
.notif-footer {
    padding: 11px 18px; border-top: 1px solid rgba(0,0,0,0.06);
    text-align: center; background: var(--cream-dark);
}
.notif-footer a {
    font-size: 12.5px; font-weight: 600; color: var(--navy-light);
    text-decoration: none;
}
.notif-footer a:hover { text-decoration: underline; }

/* ── NOTIFICATION TOAST POPUPS ── */
.notif-toast-container {
    position: fixed;
    top: 76px;
    right: 24px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 380px;
    width: calc(100vw - 48px);
    pointer-events: none;
}
.notif-toast {
    pointer-events: auto;
    background: #0b1a3d;
    color: #f8fafc;
    border: 1px solid rgba(59, 130, 246, 0.35);
    border-radius: 14px;
    padding: 14px 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.05);
    display: flex;
    align-items: flex-start;
    gap: 12px;
    transform: translateX(120%);
    opacity: 0;
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.35s ease;
    cursor: pointer;
    text-decoration: none;
    position: relative;
    overflow: hidden;
}
.notif-toast.show {
    transform: translateX(0);
    opacity: 1;
}
.notif-toast.hide {
    transform: translateX(120%);
    opacity: 0;
}
.notif-toast__icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.notif-toast__icon-wrap--new_request {
    background: rgba(59, 130, 246, 0.2);
    color: #60a5fa;
    border: 1px solid rgba(59, 130, 246, 0.3);
}
.notif-toast__icon-wrap--comment, .notif-toast__icon-wrap--message {
    background: rgba(16, 185, 129, 0.2);
    color: #34d399;
    border: 1px solid rgba(16, 185, 129, 0.3);
}
.notif-toast__icon-wrap--due_soon {
    background: rgba(245, 158, 11, 0.2);
    color: #fbbf24;
    border: 1px solid rgba(245, 158, 11, 0.3);
}
.notif-toast__icon-wrap--overdue {
    background: rgba(239, 68, 68, 0.2);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.3);
}
.notif-toast__icon-wrap--default, .notif-toast__icon-wrap--system, .notif-toast__icon-wrap--api_key {
    background: rgba(255, 255, 255, 0.1);
    color: #cbd5e1;
    border: 1px solid rgba(255, 255, 255, 0.1);
}
.notif-toast__content {
    flex: 1;
    min-width: 0;
}
.notif-toast__meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 3px;
}
.notif-toast__tag {
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.notif-toast__tag--new_request { color: #60a5fa; }
.notif-toast__tag--comment, .notif-toast__tag--message { color: #34d399; }
.notif-toast__tag--due_soon { color: #fbbf24; }
.notif-toast__tag--overdue { color: #f87171; }
.notif-toast__tag--default, .notif-toast__tag--system, .notif-toast__tag--api_key { color: #94a3b8; }
.notif-toast__close {
    background: none;
    border: none;
    color: #94a3b8;
    font-size: 16px;
    line-height: 1;
    cursor: pointer;
    padding: 0 4px;
    border-radius: 4px;
    transition: color 0.15s;
}
.notif-toast__close:hover { color: #f8fafc; }
.notif-toast__title {
    font-size: 13px;
    font-weight: 600;
    color: #f8fafc;
    line-height: 1.35;
    margin-bottom: 3px;
}
.notif-toast__msg {
    font-size: 12px;
    color: #cbd5e1;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.notif-toast__progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    background: linear-gradient(90deg, #3b82f6, #60a5fa);
    width: 100%;
    transform-origin: left;
    animation: toastProgress 6s linear forwards;
}
@keyframes toastProgress {
    from { transform: scaleX(1); }
    to { transform: scaleX(0); }
}
[data-theme="light"] .notif-toast {
    background: #ffffff;
    color: #1a1a1a;
    border-color: rgba(0, 35, 102, 0.15);
    box-shadow: 0 10px 30px rgba(0, 35, 102, 0.15), 0 2px 6px rgba(0, 0, 0, 0.08);
}
[data-theme="light"] .notif-toast__title { color: #002366; }
[data-theme="light"] .notif-toast__msg { color: #4b5563; }
[data-theme="light"] .notif-toast__close { color: #9ca3af; }
[data-theme="light"] .notif-toast__close:hover { color: #111827; }

/* ── ADMIN CHIP ─────────────────────────────────────── */
.admin-chip-wrap { position: relative; }
.topbar__admin-chip {
    display: flex; align-items: center; gap: 8px;
    padding: 5px 14px 5px 5px; border-radius: 24px;
    background: #111827; border: 1px solid rgba(255,255,255,0.08);
    cursor: pointer; transition: background .15s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.topbar__admin-chip:hover { background: #1e2a3a; }
.topbar__admin-av {
    width: 30px; height: 30px; border-radius: 50%;
    background: var(--navy); display: flex; align-items: center;
    justify-content: center; font-size: 13px; font-weight: 700; color: white;
}
.topbar__admin-name { font-size: 12.5px; font-weight: 600; color: white; }

.admin-dropdown {
    position: absolute; top: calc(100% + 10px); right: 0;
    background: var(--card); border: 1px solid rgba(0,0,0,0.08);
    border-radius: var(--radius); box-shadow: var(--shadow-lg);
    min-width: 210px; z-index: 200; overflow: hidden; display: none;
    animation: dropIn .2s ease;
}
.admin-dropdown.open { display: block; }
.admin-dropdown__header { padding: 14px 16px; border-bottom: 1px solid rgba(0,0,0,0.06); background: var(--cream-dark); }
.admin-dropdown__name  { font-size: 13.5px; font-weight: 700; color: var(--ink); }
.admin-dropdown__email { font-size: 11.5px; color: var(--ink-soft); margin-top: 2px; }
.admin-dropdown__item {
    display: flex; align-items: center; gap: 10px;
    padding: 11px 16px; font-size: 13px; color: var(--ink-mid);
    text-decoration: none; transition: background .12s; cursor: pointer;
    background: none; border: none; width: 100%; font-family: var(--font); text-align: left;
}
.admin-dropdown__item:hover { background: var(--cream-dark); }
.admin-dropdown__item--danger { color: #dc2626; }
.admin-dropdown__item--danger:hover { background: #fef2f2; }

/* ══════════════════════════════════════════════════════
   PAGE CONTENT AREA
══════════════════════════════════════════════════════ */
.page-content { flex: 1; overflow-y: auto; background: var(--cream); padding: 28px 30px; }

/* ══════════════════════════════════════════════════════
   GLOBAL COMPONENTS
══════════════════════════════════════════════════════ */
.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 10.5px; font-weight: 600; white-space: nowrap; }
.badge--pending      { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
.badge--under-review { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
.badge--approved     { background: #dcfce7; color: #16a34a; }
.badge--posted       { background: #ede9fe; color: #7c3aed; }
.badge--rejected     { background: #fee2e2; color: #dc2626; }
.badge--high         { background: #fee2e2; color: #dc2626; }
.badge--urgent       { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
.badge--medium       { background: #fef3c7; color: #d97706; }
.badge--low          { background: #f1f5f9; color: #64748b; }

.alert { padding: 12px 16px; border-radius: var(--radius-sm); font-size: 13px; margin-bottom: 16px; font-weight: 500; display: flex; align-items: center; gap: 8px; }
.alert--success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
.alert--error   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }

.panel {
    background: var(--card); border: 1px solid rgba(0,0,0,0.06);
    border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden;
}
.panel__head {
    padding: 18px 22px 14px; display: flex; align-items: flex-start; justify-content: space-between;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
.panel__title { font-family: var(--font-disp); font-size: 17px; color: var(--ink); }
.panel__sub   { font-size: 11.5px; color: var(--ink-soft); margin-top: 3px; }
.panel__link  { font-size: 12.5px; color: var(--navy-light); font-weight: 600; text-decoration: none; white-space: nowrap; }
.panel__link:hover { text-decoration: underline; }
.panel__body  { padding: 20px 22px; }

/* ══════════════════════════════════════════════════════
   BULLDOG AI STYLES
══════════════════════════════════════════════════════ */

/* ── LAUNCHER BUTTON ──────────────────────────────────────── */
#bulldog-launcher {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 9000;
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: visible;
    transition: transform .25s cubic-bezier(.34,1.56,.64,1);
    animation: bulldog-pulse 2.8s ease-in-out infinite;
}
#bulldog-launcher:hover {
    transform: scale(1.12) rotate(-5deg);
    animation: none;
    filter: drop-shadow(0 6px 20px rgba(0,26,77,0.8)) drop-shadow(0 0 18px rgba(252,211,77,0.6));
}
@keyframes bulldog-pulse {
    0%, 100% {
        filter: drop-shadow(0 4px 16px rgba(0,26,77,0.55)) drop-shadow(0 0 0px rgba(252,211,77,0));
    }
    50% {
        filter: drop-shadow(0 6px 24px rgba(0,26,77,0.75)) drop-shadow(0 0 16px rgba(252,211,77,0.5));
    }
}

/* Notification dot */
#bulldog-notif-dot {
    position: absolute;
    top: -2px;
    right: -2px;
    min-width: 20px;
    height: 20px;
    background: #ef4444;
    border-radius: 10px;
    border: 2.5px solid #111111;
    font-size: 9px;
    font-weight: 800;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'DM Sans', sans-serif;
    padding: 0 4px;
    animation: notif-pop .3s cubic-bezier(.34,1.56,.64,1);
    z-index: 9001;
}
@keyframes notif-pop {
    from { transform: scale(0); }
    to   { transform: scale(1); }
}

/* ── MODAL ─────────────────────────────────────────────────── */
#bulldog-modal {
    position: fixed;
    bottom: 104px;
    right: 28px;
    z-index: 8999;
    width: 390px;
    max-height: 600px;
    background: #0d1526;
    border-radius: 26px;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 28px 90px rgba(0,0,0,0.65), 0 0 0 1px rgba(255,255,255,0.04), inset 0 1px 0 rgba(255,255,255,0.06);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: scale(0.82) translateY(24px);
    transform-origin: bottom right;
    opacity: 0;
    pointer-events: none;
    transition: transform .28s cubic-bezier(.34,1.3,.64,1), opacity .22s ease;
}
#bulldog-modal.open {
    transform: scale(1) translateY(0);
    opacity: 1;
    pointer-events: all;
}

/* Header */
.bd-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 18px 14px;
    background: linear-gradient(135deg, #001233 0%, #002b6e 100%);
    border-bottom: 1px solid rgba(255,255,255,0.07);
    flex-shrink: 0;
    position: relative;
    overflow: hidden;
}
.bd-header::after {
    content: '';
    position: absolute;
    top: -30px; right: -30px;
    width: 100px; height: 100px;
    background: radial-gradient(circle, rgba(252,211,77,0.08) 0%, transparent 70%);
    pointer-events: none;
}
.bd-avatar {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    background: rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 1px solid rgba(252,211,77,0.2);
    box-shadow: 0 2px 12px rgba(0,0,0,0.3);
}
.bd-header-info { flex: 1; }
.bd-name {
    font-family: 'Syne', sans-serif;
    font-size: 15px;
    font-weight: 800;
    color: white;
    letter-spacing: 0.3px;
    display: flex;
    align-items: center;
    gap: 7px;
}
.bd-name-badge {
    font-size: 9px;
    font-weight: 700;
    font-family: 'DM Sans', sans-serif;
    background: rgba(245,158,11,0.18);
    color: #fcd34d;
    border: 1px solid rgba(245,158,11,0.28);
    padding: 2px 8px;
    border-radius: 20px;
    letter-spacing: 0.6px;
    text-transform: uppercase;
}
.bd-status {
    font-size: 11px;
    color: rgba(255,255,255,0.4);
    font-family: 'DM Sans', sans-serif;
    margin-top: 2px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.bd-status-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 8px rgba(16,185,129,0.8);
    animation: status-blink 2s ease-in-out infinite;
}
@keyframes status-blink {
    0%, 100% { opacity: 1; box-shadow: 0 0 8px rgba(16,185,129,0.8); }
    50%       { opacity: 0.5; box-shadow: 0 0 3px rgba(16,185,129,0.3); }
}
.bd-close {
    width: 30px; height: 30px;
    border-radius: 9px;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.45);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all .15s;
    flex-shrink: 0;
}
.bd-close:hover { background: rgba(255,255,255,0.15); color: white; transform: rotate(90deg); }

/* Tabs */
.bd-tabs {
    display: flex;
    padding: 10px 14px 0;
    gap: 2px;
    background: #0d1526;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    flex-shrink: 0;
}
.bd-tab {
    padding: 7px 14px;
    border-radius: 9px 9px 0 0;
    font-size: 11.5px;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    color: rgba(255,255,255,0.3);
    cursor: pointer;
    border: none;
    background: none;
    transition: all .15s;
    border-bottom: 2px solid transparent;
    letter-spacing: 0.2px;
}
.bd-tab:hover { color: rgba(255,255,255,0.65); background: rgba(255,255,255,0.04); }
.bd-tab.active {
    color: #fcd34d;
    border-bottom-color: #fcd34d;
    background: rgba(252,211,77,0.07);
}

/* Body */
.bd-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px 18px;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.08) transparent;
}
.bd-body::-webkit-scrollbar { width: 3px; }
.bd-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 4px; }

/* Tab panels */
.bd-panel { display: none; }
.bd-panel.active { display: block; }

/* ── BRIEFING PANEL ──── */
.bd-briefing-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    padding: 36px 0;
    color: rgba(255,255,255,0.35);
    font-family: 'DM Sans', sans-serif;
    font-size: 12.5px;
    text-align: center;
}
.bd-loader {
    width: 34px; height: 34px;
    border: 3px solid rgba(255,255,255,0.06);
    border-top-color: #fcd34d;
    border-radius: 50%;
    animation: bd-spin 0.75s linear infinite;
}
@keyframes bd-spin { to { transform: rotate(360deg); } }

.bd-briefing-text {
    font-family: 'DM Sans', sans-serif;
    font-size: 13.5px;
    color: rgba(255,255,255,0.8);
    line-height: 1.8;
    white-space: pre-wrap;
}

/* Stat chips */
.bd-stat-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 14px;
}
.bd-chip {
    padding: 4px 11px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    font-family: 'DM Sans', sans-serif;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Refresh button */
.bd-refresh-btn {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-top: 14px;
    padding: 9px 16px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.09);
    border-radius: 10px;
    color: rgba(255,255,255,0.5);
    font-size: 12px;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: all .15s;
    width: 100%;
    justify-content: center;
}
.bd-refresh-btn:hover { background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.85); border-color: rgba(255,255,255,0.18); }
.bd-refresh-btn:disabled { opacity: 0.35; cursor: not-allowed; }
.bd-refresh-icon { transition: transform .4s ease; }
.bd-refresh-btn:not(:disabled):hover .bd-refresh-icon { transform: rotate(180deg); }

/* ── CHAT PANEL ──── */
.bd-chat-messages {
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-height: 200px;
    margin-bottom: 12px;
    box-sizing: border-box;
}
.bd-msg {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    width: 100%;
    max-width: 100%;
    flex-shrink: 0;
    box-sizing: border-box;
    animation: msg-in .2s ease;
}
@keyframes msg-in {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: none; }
}
.bd-msg--user {
    flex-direction: row-reverse;
    justify-content: flex-start;
}
.bd-msg-av {
    width: 28px; height: 28px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 2px;
}
.bd-msg--bot .bd-msg-av {
    background: linear-gradient(135deg, #001a4d, #002e7a);
    color: #fcd34d;
    font-family: 'Syne', sans-serif;
    border: 1px solid rgba(252,211,77,0.2);
}
.bd-msg--user .bd-msg-av {
    background: rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.5);
    font-family: 'DM Sans', sans-serif;
}
.bd-msg-bubble {
    max-width: 80%;
    min-width: 40px;
    padding: 10px 14px;
    border-radius: 14px;
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    line-height: 1.6;
    word-break: break-word;
    overflow-wrap: anywhere;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
}
.bd-msg--bot .bd-msg-bubble {
    background: rgba(255,255,255,0.06);
    color: rgba(255,255,255,0.82);
    border-radius: 4px 14px 14px 14px;
    border: 1px solid rgba(255,255,255,0.06);
}
.bd-msg--user .bd-msg-bubble {
    background: linear-gradient(135deg, #002060, #003090);
    color: white;
    border-radius: 14px 4px 14px 14px;
}
.bd-typing {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 11px 14px;
    background: rgba(255,255,255,0.06);
    border-radius: 4px 14px 14px 14px;
    width: fit-content;
    border: 1px solid rgba(255,255,255,0.06);
}
.bd-typing span {
    width: 6px; height: 6px;
    background: rgba(255,255,255,0.35);
    border-radius: 50%;
    animation: typing-dot 1.2s ease-in-out infinite;
}
.bd-typing span:nth-child(2) { animation-delay: .2s; }
.bd-typing span:nth-child(3) { animation-delay: .4s; }
@keyframes typing-dot {
    0%, 60%, 100% { transform: translateY(0); opacity: .35; }
    30%            { transform: translateY(-5px); opacity: 1; }
}

/* Chat input */
.bd-chat-form {
    display: flex;
    gap: 8px;
    padding: 0 0 4px;
    flex-shrink: 0;
}
.bd-chat-input {
    flex: 1;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 11px;
    padding: 9px 13px;
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    color: white;
    outline: none;
    transition: border-color .15s, background .15s;
}
.bd-chat-input::placeholder { color: rgba(255,255,255,0.2); }
.bd-chat-input:focus { border-color: rgba(252,211,77,0.45); background: rgba(255,255,255,0.08); }
.bd-send-btn {
    width: 38px; height: 38px;
    border-radius: 11px;
    background: #fcd34d;
    border: none;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all .15s;
    flex-shrink: 0;
}
.bd-send-btn:hover { background: #f59e0b; transform: scale(1.07); }
.bd-send-btn:disabled { opacity: 0.35; cursor: not-allowed; transform: none; }

/* ── QUICK ACTIONS ──── */
.bd-quick-actions {
    display: flex;
    flex-direction: column;
    gap: 7px;
}
.bd-action-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 11px 14px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 13px;
    cursor: pointer;
    transition: all .18s;
    text-decoration: none;
    font-family: 'DM Sans', sans-serif;
}
.bd-action-btn:hover {
    background: rgba(255,255,255,0.09);
    border-color: rgba(255,255,255,0.14);
    transform: translateX(4px);
}
.bd-action-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.bd-action-label { font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.82); }
.bd-action-sub   { font-size: 11px; color: rgba(255,255,255,0.3); margin-top: 1px; }
.bd-action-arrow { margin-left: auto; color: rgba(255,255,255,0.2); }

/* Error state */
.bd-error {
    background: rgba(239,68,68,0.09);
    border: 1px solid rgba(239,68,68,0.18);
    border-radius: 11px;
    padding: 12px 14px;
    font-size: 12.5px;
    color: #fca5a5;
    font-family: 'DM Sans', sans-serif;
    line-height: 1.65;
}

/* Footer */
.bd-footer {
    padding: 9px 18px;
    border-top: 1px solid rgba(255,255,255,0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    flex-shrink: 0;
    background: rgba(0,0,0,0.15);
}
.bd-footer-txt {
    font-size: 10px;
    color: rgba(255,255,255,0.18);
    font-family: 'DM Sans', sans-serif;
    letter-spacing: 0.2px;
}

/* ══════════════════════════════════════════════════════
   MOBILE RESPONSIVENESS
══════════════════════════════════════════════════════ */
@media (max-width: 768px) {
    .shell { flex-direction: column; }
    
    .sidebar {
        flex-direction: row; justify-content: space-around;
        width: 100%; height: 65px; min-width: 0;
        padding: 0 10px; position: fixed; bottom: 0; left: 0;
        z-index: 1000; background: #111827; border-top: 1px solid rgba(255,255,255,0.1);
    }
    .sb-brand { display: none; }
    .sb-group { flex-direction: row; width: auto; padding: 0; gap: 6px; background: transparent; }
    .sb-spacer { display: none; }
    .sb-foot { flex-direction: row; width: auto; gap: 6px; }
    .sb-label { display: none !important; }
    .sb-item.active::before { display: none; }
    
    .main-wrapper {
        margin: 0; border-radius: 0;
        margin-bottom: 65px; 
    }
    .topbar { border-radius: 0; padding: 0 16px; height: 60px; }
    .topbar__date { display: none; }
    .topbar__admin-name { display: none; }
    
    .page-hd { margin-bottom: 16px; }
    
    /* Grids & Layouts */
    .dashboard-grid, .settings-layout, .field-row, .kpi-grid, .charts-grid {
        display: flex !important; flex-direction: column !important;
    }
    
    /* Scrollable tables */
    .table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    table { min-width: 700px; } 

    /* Dropdowns */
    .notif-dropdown { width: calc(100vw - 32px); right: -16px; max-width: 360px; }
    .admin-dropdown { right: -10px; width: 260px; }

    /* Bulldawg AI Panel */
    .bulldog { display: none; } /* Hide the AI float on mobile to save space, or it can be adapted later */
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

/* ── Premium UI Micro-Animations (Zero-Flicker & Tactile) ── */
button, .btn, .btn-primary, .back-btn, .tab-btn {
    transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1), 
                box-shadow 0.18s cubic-bezier(0.16, 1, 0.3, 1), 
                background-color 0.18s ease, 
                border-color 0.18s ease !important;
}
button:active, .btn:active, .btn-primary:active, .back-btn:active {
    transform: scale(0.97) !important;
}

.card, .scard, .table-card {
    transition: transform 0.22s cubic-bezier(0.16, 1, 0.3, 1), 
                box-shadow 0.22s cubic-bezier(0.16, 1, 0.3, 1), 
                border-color 0.22s ease;
}

table tbody tr {
    transition: background-color 0.15s ease;
}

input[type="text"], input[type="email"], input[type="password"], input[type="search"], select, textarea {
    transition: border-color 0.2s cubic-bezier(0.16, 1, 0.3, 1), 
                box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1), 
                background-color 0.2s ease !important;
}

.badge, .status-pill {
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease;
}
.badge:hover {
    transform: translateY(-2px);
}

.notif-dropdown, .admin-dropdown {
    animation: dropSmooth 0.2s cubic-bezier(0.16, 1, 0.3, 1) both;
    transform-origin: top right;
}
@keyframes dropSmooth {
    from {
        opacity: 0.7;
        transform: translateY(-6px) scale(0.98);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@yield('head-styles')
</style>
</head>
<body>
<div id="np-page-progress"></div>
<div class="shell">

<!-- ═══ SIDEBAR ═══════════════════════════════════════════ -->
<aside class="sidebar">
    <a href="{{ route('admin.dashboard') }}" class="sb-brand">
        <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
            <path d="M2 17l10 5 10-5"/>
            <path d="M2 12l10 5 10-5"/>
        </svg>
    </a>

    <div class="sb-group">
        <a href="{{ route('admin.dashboard') }}"
           class="sb-item sb-item--dashboard {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/></svg>
            <span class="sb-label">Dashboard</span>
        </a>
        <a href="{{ route('admin.requests') }}"
           class="sb-item sb-item--requests {{ request()->routeIs('admin.requests*') ? 'active' : '' }}">
            <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            <span class="sb-label">Requests</span>
        </a>
        <a href="{{ route('admin.calendar') }}"
           class="sb-item sb-item--calendar {{ request()->routeIs('admin.calendar') ? 'active' : '' }}">
            <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2.5"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span class="sb-label">Calendar</span>
        </a>
        <a href="{{ route('admin.analytics') }}"
           class="sb-item sb-item--analytics {{ request()->routeIs('admin.analytics*') ? 'active' : '' }}"
           title="Request Analytics">
            <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            <span class="sb-label">Request Analytics</span>
        </a>
        <a href="{{ route('admin.reports') }}"
           class="sb-item sb-item--reports {{ request()->routeIs('admin.reports*') ? 'active' : '' }}"
           title="Performance Analytics">
            <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
            <span class="sb-label">Performance Analytics</span>
        </a>
    </div>

    <div class="sb-spacer"></div>

    <div class="sb-group" style="margin-bottom:10px;">
        <a href="{{ route('admin.audit-logs') }}"
           class="sb-item sb-item--audit {{ request()->routeIs('admin.audit-logs*') ? 'active' : '' }}">
            <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
            <span class="sb-label">Activity History</span>
        </a>
        <a href="{{ route('admin.settings') }}"
           class="sb-item sb-item--settings {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
            <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            <span class="sb-label">Settings</span>
        </a>
    </div>

    <div class="sb-foot">
        <div class="sb-foot-item" style="position:relative;">
            <div class="sb-avatar">A</div>
            <span class="sb-label">Administrator</span>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="display:contents;">
            @csrf
            <button type="submit" class="sb-foot-item sb-foot-item--logout" style="position:relative;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span class="sb-label">Sign Out</span>
            </button>
        </form>
    </div>
</aside>

<!-- ═══ FLOATING MAIN WRAPPER ═══════════════════════════ -->
<div class="main-wrapper">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="topbar__right">
            <div class="topbar__date" id="topbar-date"></div>

            {{-- ── THEME TOGGLE ── --}}
            <button class="theme-toggle-btn" id="themeToggle" title="Toggle color mode" aria-label="Toggle color mode">
                <svg class="sun-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
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
                <svg class="moon-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                </svg>
            </button>

            {{-- ── NOTIFICATION BELL ── --}}
            <div class="notif-wrap" id="notif-wrap">
                <button class="notif-btn" id="notif-btn" type="button" aria-label="Notifications" onclick="toggleNotif(event)">
                    <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span class="notif-badge" id="notif-badge"></span>
                </button>

                <div class="notif-dropdown" id="notif-dropdown">
                    <div class="notif-head">
                        <div class="notif-head__title">
                            Notifications
                            <span id="notif-unread-label" style="font-size:11px;color:var(--ink-soft);font-weight:400;margin-left:4px;"></span>
                        </div>
                        <button type="button" class="notif-head__mark" onclick="markAllRead(event)">Mark all read</button>
                    </div>
                    <div class="notif-list" id="notif-list">
                        <div class="notif-empty">
                            <svg width="32" height="32" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                            <p>Loading…</p>
                        </div>
                    </div>
                    <div class="notif-footer">
                        <a href="{{ route('admin.notifications') }}">View all notifications →</a>
                    </div>
                </div>
            </div>

            {{-- ── ADMIN CHIP ── --}}
            <div class="admin-chip-wrap">
                <div class="topbar__admin-chip" id="admin-chip">
                    <div class="topbar__admin-av">{{ substr(session('admin_name', 'A'), 0, 1) }}</div>
                    <span class="topbar__admin-name">{{ session('admin_name', 'Admin') }}</span>
                </div>
                <div class="admin-dropdown" id="admin-dropdown">
                    <div class="admin-dropdown__header">
                        <div class="admin-dropdown__name">{{ session('admin_name', 'Administrator') }}</div>
                        <div class="admin-dropdown__email">{{ session('admin_email', 'admin@nupost.com') }}</div>
                    </div>
                    <a href="{{ route('admin.audit-logs') }}" class="admin-dropdown__item">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
                        Activity History
                    </a>
                    <a href="{{ route('admin.settings') }}" class="admin-dropdown__item">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        Settings
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="admin-dropdown__item admin-dropdown__item--danger">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- REAL-TIME NOTIFICATION TOAST CONTAINER -->
    <div class="notif-toast-container" id="notif-toast-container"></div>

    <!-- PAGE CONTENT -->
    <div class="page-content">
        @if(session('success'))
            <div class="alert alert--success">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert--error">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                {{ session('error') }}
            </div>
        @endif
        @yield('content')
    </div>

</div><!-- /.main-wrapper -->
</div><!-- /.shell -->

{{-- ════════════════════════════════════════════════════════
     BULLDOG AI — Floating Assistant
     Appears on every admin page via this layout
════════════════════════════════════════════════════════ --}}

{{-- Launcher Button --}}
<button id="bulldog-launcher" onclick="toggleBulldog()" title="Bulldog AI Assistant">
    <img
        src="{{ asset('images/bulldogs-ai.png') }}"
        alt="Bulldog AI"
        style="
            width: 70px;
            height: 70px;
            object-fit: contain;
            display: block;
            pointer-events: none;
        "
    >
    <div id="bulldog-notif-dot" style="display:none;"></div>
</button>

{{-- Modal --}}
<div id="bulldog-modal">
    {{-- Header --}}
    <div class="bd-header">
        <div class="bd-avatar">
            <svg viewBox="0 0 44 44" fill="none" style="width:28px;height:28px;">
                <circle cx="22" cy="22" r="18" fill="rgba(255,255,255,0.07)"/>
                <ellipse cx="22" cy="24" rx="4" ry="3" fill="#fcd34d" opacity="0.9"/>
                <circle cx="15.5" cy="18.5" r="3" fill="rgba(255,255,255,0.9)"/>
                <circle cx="28.5" cy="18.5" r="3" fill="rgba(255,255,255,0.9)"/>
                <circle cx="16" cy="18.5" r="1.8" fill="#0f172a"/>
                <circle cx="29" cy="18.5" r="1.8" fill="#0f172a"/>
                <circle cx="16.6" cy="17.7" r="0.7" fill="white" opacity="0.9"/>
                <circle cx="29.6" cy="17.7" r="0.7" fill="white" opacity="0.9"/>
            </svg>
        </div>
        <div class="bd-header-info">
            <div class="bd-name">
                Bulldog
                <span class="bd-name-badge">AI Assistant</span>
            </div>
            <div class="bd-status">
                <span class="bd-status-dot"></span>
                Online · Powered by Groq
            </div>
        </div>
        <button class="bd-close" onclick="toggleBulldog()">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    {{-- Tabs --}}
    <div class="bd-tabs">
        <button class="bd-tab active" onclick="switchTab('briefing')">📋 Briefing</button>
        <button class="bd-tab" onclick="switchTab('chat')">💬 Ask me</button>
        <button class="bd-tab" onclick="switchTab('actions')">⚡ Quick</button>
    </div>

    {{-- Body --}}
    <div class="bd-body">

        {{-- BRIEFING PANEL --}}
        <div class="bd-panel active" id="bd-panel-briefing">
            <div class="bd-stat-chips">
                <span class="bd-chip" style="background:rgba(245,158,11,0.14);color:#fcd34d;border:1px solid rgba(245,158,11,0.22);">
                    📥 {{ $pending ?? 0 }} Pending
                </span>
                <span class="bd-chip" style="background:rgba(14,165,233,0.14);color:#7dd3fc;border:1px solid rgba(14,165,233,0.22);">
                    👁 {{ $review ?? 0 }} Review
                </span>
                <span class="bd-chip" style="background:rgba(16,185,129,0.14);color:#6ee7b7;border:1px solid rgba(16,185,129,0.22);">
                    ✅ {{ $approved ?? 0 }} Approved
                </span>
                <span class="bd-chip" style="background:rgba(239,68,68,0.14);color:#fca5a5;border:1px solid rgba(239,68,68,0.22);">
                    ❌ {{ $rejected ?? 0 }} Rejected
                </span>
            </div>

            <div id="bd-briefing-content">
                <div class="bd-briefing-loading" id="bd-loading">
                    <div class="bd-loader"></div>
                    <span>Bulldog is reading your dashboard…</span>
                </div>
                <div id="bd-briefing-text" class="bd-briefing-text" style="display:none;"></div>
                <div id="bd-briefing-error" class="bd-error" style="display:none;"></div>
            </div>

            <button class="bd-refresh-btn" id="bd-refresh-btn" onclick="loadBriefing(true)" disabled>
                <svg class="bd-refresh-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                Refresh Briefing
            </button>
        </div>

        {{-- CHAT PANEL --}}
        <div class="bd-panel" id="bd-panel-chat">
            <div class="bd-chat-messages" id="bd-chat-messages">
                <div class="bd-msg bd-msg--bot">
                    <div class="bd-msg-av">B</div>
                    <div class="bd-msg-bubble">
                        Woof! Ask me anything about your requests, stats, or what needs attention. I got you. 🐾
                    </div>
                </div>
            </div>
            <div class="bd-chat-form">
                <input class="bd-chat-input" id="bd-chat-input" type="text"
                       placeholder="Ask Bulldog something…"
                       onkeydown="if(event.key==='Enter')sendBulldogChat()">
                <button class="bd-send-btn" id="bd-send-btn" onclick="sendBulldogChat()">
                    <svg width="16" height="16" fill="none" stroke="#1a1a1a" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </div>
        </div>

        {{-- QUICK ACTIONS PANEL --}}
        <div class="bd-panel" id="bd-panel-actions">
            <div class="bd-quick-actions">
                <a href="{{ route('admin.requests', ['filter'=>'pending']) }}" class="bd-action-btn">
                    <div class="bd-action-icon" style="background:rgba(245,158,11,0.13);">
                        <svg width="18" height="18" fill="none" stroke="#fcd34d" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <div class="bd-action-label">Pending Requests</div>
                        <div class="bd-action-sub">{{ $pending ?? 0 }} waiting for review</div>
                    </div>
                    <svg class="bd-action-arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="{{ route('admin.requests') }}" class="bd-action-btn">
                    <div class="bd-action-icon" style="background:rgba(37,99,235,0.13);">
                        <svg width="18" height="18" fill="none" stroke="#93c5fd" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <div>
                        <div class="bd-action-label">All Requests</div>
                        <div class="bd-action-sub">{{ $total ?? 0 }} total submissions</div>
                    </div>
                    <svg class="bd-action-arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="{{ route('admin.calendar') }}" class="bd-action-btn">
                    <div class="bd-action-icon" style="background:rgba(16,185,129,0.13);">
                        <svg width="18" height="18" fill="none" stroke="#6ee7b7" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div>
                        <div class="bd-action-label">Request Calendar</div>
                        <div class="bd-action-sub">View scheduled posts</div>
                    </div>
                    <svg class="bd-action-arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="{{ route('admin.reports.export') }}" class="bd-action-btn">
                    <div class="bd-action-icon" style="background:rgba(139,92,246,0.13);">
                        <svg width="18" height="18" fill="none" stroke="#c4b5fd" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    </div>
                    <div>
                        <div class="bd-action-label">Export CSV</div>
                        <div class="bd-action-sub">Download full report</div>
                    </div>
                    <svg class="bd-action-arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <button class="bd-action-btn" onclick="switchTab('briefing');loadBriefing(true);" style="border:none;width:100%;text-align:left;">
                    <div class="bd-action-icon" style="background:rgba(252,211,77,0.13);">
                        <svg width="18" height="18" fill="none" stroke="#fcd34d" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    </div>
                    <div>
                        <div class="bd-action-label">Re-generate Briefing</div>
                        <div class="bd-action-sub">Get a fresh AI summary</div>
                    </div>
                    <svg class="bd-action-arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>

    </div>{{-- /.bd-body --}}

    <div class="bd-footer">
        <svg width="10" height="10" fill="none" stroke="rgba(255,255,255,0.18)" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <span class="bd-footer-txt">Bulldog · NUPost Admin AI · Powered by Groq</span>
    </div>
</div>{{-- /#bulldog-modal --}}

<script>
// ── DATE ──────────────────────────────────────────────────────────
(function(){
    const el = document.getElementById('topbar-date');
    if(el) el.textContent = new Date().toLocaleDateString('en-US',{
        weekday:'short', month:'short', day:'numeric', year:'numeric'
    });
})();

// ── ADMIN CHIP DROPDOWN ───────────────────────────────────────────
const chip = document.getElementById('admin-chip');
const dd   = document.getElementById('admin-dropdown');
if(chip && dd){
    chip.addEventListener('click', e => { e.stopPropagation(); dd.classList.toggle('open'); });
    document.addEventListener('click', () => dd.classList.remove('open'));
}

// ── NOTIFICATION SYSTEM ───────────────────────────────────────────
const NOTIF_FETCH_URL = '{{ route("admin.notifications.fetch") }}';
const NOTIF_READ_URL  = '{{ route("admin.notifications.read") }}';
var CSRF = window.CSRF || document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
window.CSRF = CSRF;

function toggleNotif(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const dd = document.getElementById('notif-dropdown');
    if (!dd) return;
    const willOpen = !dd.classList.contains('open');
    dd.classList.toggle('open', willOpen);
    if (willOpen) {
        loadNotifications();
    }
}

document.addEventListener('click', function(e) {
    const wrap = document.getElementById('notif-wrap');
    const dd = document.getElementById('notif-dropdown');
    if (wrap && dd && !wrap.contains(e.target)) {
        dd.classList.remove('open');
    }
});

function timeAgo(dateStr) {
    if (!dateStr) return 'Just now';
    const parsed = new Date(String(dateStr).replace(' ', 'T'));
    if (isNaN(parsed.getTime())) return 'Recently';
    const diff = Math.floor((Date.now() - parsed.getTime()) / 1000);
    if (diff < 60)    return 'Just now';
    if (diff < 3600)  return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    return Math.floor(diff / 86400) + 'd ago';
}

function getIconSvg(type, size) {
    const s = size || 15;
    if (type === 'new_request') {
        return `<svg width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>`;
    }
    if (type === 'comment' || type === 'message') {
        return `<svg width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>`;
    }
    if (type === 'api_key') {
        return `<svg width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="7.5" cy="15.5" r="5.5"/><path d="m21 2-9.6 9.6"/><path d="m15.5 7.5 3 3"/></svg>`;
    }
    if (type === 'system') {
        return `<svg width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83-2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>`;
    }
    if (type === 'due_soon' || type === 'sla_due') {
        return `<svg width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>`;
    }
    if (type === 'overdue' || type === 'sla_overdue') {
        return `<svg width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`;
    }
    return `<svg width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>`;
}

function getIcon(type) {
    if (type === 'new_request') return `<div class="notif-icon notif-icon--request" style="color:#2563eb;">${getIconSvg('new_request', 16)}</div>`;
    if (type === 'comment' || type === 'message') return `<div class="notif-icon notif-icon--comment" style="color:#16a34a;">${getIconSvg('comment', 16)}</div>`;
    if (type === 'api_key')     return `<div class="notif-icon notif-icon--api" style="background:#ede9fe;color:#7c3aed;">${getIconSvg('api_key', 16)}</div>`;
    if (type === 'system')      return `<div class="notif-icon notif-icon--system" style="background:#e0f2fe;color:#0284c7;">${getIconSvg('system', 16)}</div>`;
    if (type === 'due_soon' || type === 'sla_due') return `<div class="notif-icon notif-icon--due" style="background:#fef3c7;color:#d97706;">${getIconSvg('due_soon', 16)}</div>`;
    if (type === 'overdue' || type === 'sla_overdue') return `<div class="notif-icon notif-icon--overdue" style="background:#fee2e2;color:#dc2626;">${getIconSvg('overdue', 16)}</div>`;
    return `<div class="notif-icon notif-icon--default" style="color:var(--ink-soft);">${getIconSvg('default', 16)}</div>`;
}

function getToastTagInfo(type) {
    if (type === 'new_request') return 'New Request';
    if (type === 'comment' || type === 'message') return 'New Reply';
    if (type === 'due_soon' || type === 'sla_due') return 'Due Soon SLA';
    if (type === 'overdue' || type === 'sla_overdue') return 'Overdue SLA';
    if (type === 'api_key') return 'API Key';
    if (type === 'system') return 'System Alert';
    return 'Notification';
}

function esc(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function playNotifChime() {
    try {
        const AudioCtx = window.AudioContext || window.webkitAudioContext;
        if (!AudioCtx) return;
        const ctx = new AudioCtx();
        if (ctx.state === 'suspended') ctx.resume();
        const now = ctx.currentTime;
        
        const osc1 = ctx.createOscillator();
        const gain1 = ctx.createGain();
        osc1.type = 'sine';
        osc1.frequency.setValueAtTime(587.33, now);
        gain1.gain.setValueAtTime(0.08, now);
        gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
        osc1.connect(gain1);
        gain1.connect(ctx.destination);
        osc1.start(now);
        osc1.stop(now + 0.3);

        const osc2 = ctx.createOscillator();
        const gain2 = ctx.createGain();
        osc2.type = 'sine';
        osc2.frequency.setValueAtTime(880, now + 0.1);
        gain2.gain.setValueAtTime(0.08, now + 0.1);
        gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.5);
        osc2.connect(gain2);
        gain2.connect(ctx.destination);
        osc2.start(now + 0.1);
        osc2.stop(now + 0.5);
    } catch(e) {}
}

function showNotifToast(n) {
    let container = document.getElementById('notif-toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notif-toast-container';
        container.className = 'notif-toast-container';
        document.body.appendChild(container);
    }

    let link = '{{ route("admin.requests") }}';
    if (n.type === 'api_key' || n.type === 'system') {
        link = '{{ route("admin.settings") }}#ai-keys';
    } else if (n.request_id) {
        link = `{{ url('admin/requests') }}/${n.request_id}`;
    }

    const type = n.type || 'new_request';
    const tagInfo = getToastTagInfo(type);

    const toast = document.createElement('div');
    toast.className = 'notif-toast';
    toast.innerHTML = `
        <div class="notif-toast__icon-wrap notif-toast__icon-wrap--${type}">
            ${getIconSvg(type, 18)}
        </div>
        <div class="notif-toast__content">
            <div class="notif-toast__meta">
                <span class="notif-toast__tag notif-toast__tag--${type}">${tagInfo}</span>
                <button type="button" class="notif-toast__close" aria-label="Close">&times;</button>
            </div>
            <div class="notif-toast__title">${esc(n.title)}</div>
            <div class="notif-toast__msg">${esc(n.message)}</div>
        </div>
        <div class="notif-toast__progress"></div>
    `;

    const closeBtn = toast.querySelector('.notif-toast__close');
    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dismissToast(toast);
        });
    }

    toast.addEventListener('click', (e) => {
        dismissToast(toast);
        handleAdminNotifClick(e, String(n.id), link);
    });

    container.appendChild(toast);
    requestAnimationFrame(() => {
        toast.classList.add('show');
    });

    toast._dismissTimer = setTimeout(() => {
        dismissToast(toast);
    }, 6000);
}

function dismissToast(toast) {
    if (!toast || toast._isDismissed) return;
    toast._isDismissed = true;
    if (toast._dismissTimer) clearTimeout(toast._dismissTimer);
    toast.classList.remove('show');
    toast.classList.add('hide');
    setTimeout(() => {
        if (toast.parentNode) toast.parentNode.removeChild(toast);
    }, 400);
}

let knownNotifIds = null;

function loadNotifications() {
    fetch(NOTIF_FETCH_URL, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(data => {
        const notifs = data.notifications || [];
        const unread = data.unread_count || 0;

        if (knownNotifIds === null) {
            try {
                const stored = JSON.parse(sessionStorage.getItem('admin_seen_notifs') || '[]');
                knownNotifIds = new Set(stored);
            } catch(e) {
                knownNotifIds = new Set();
            }

            if (knownNotifIds.size === 0) {
                notifs.forEach(n => {
                    if (n.id) knownNotifIds.add(String(n.id));
                });
                try {
                    sessionStorage.setItem('admin_seen_notifs', JSON.stringify(Array.from(knownNotifIds)));
                } catch(e) {}
            }
        }

        const brandNew = [];
        notifs.forEach(n => {
            const idStr = String(n.id);
            if (!n.is_read && !knownNotifIds.has(idStr)) {
                brandNew.push(n);
                knownNotifIds.add(idStr);
            }
        });

        if (brandNew.length > 0) {
            try {
                sessionStorage.setItem('admin_seen_notifs', JSON.stringify(Array.from(knownNotifIds)));
            } catch(e) {}
            brandNew.slice(0, 3).forEach(n => showNotifToast(n));
            playNotifChime();
        }

        renderNotifications(notifs, unread);
    })
    .catch(() => {});
}

function renderNotifications(notifs, unread) {
    const badge = document.getElementById('notif-badge');
    if (badge) {
        if (unread > 0) {
            badge.textContent = unread > 99 ? '99+' : unread;
            badge.classList.add('has-count');
        } else {
            badge.textContent = '';
            badge.classList.remove('has-count');
        }
    }

    const label = document.getElementById('notif-unread-label');
    if (label) label.textContent = unread > 0 ? `(${unread} new)` : '';

    const list = document.getElementById('notif-list');
    if (!list) return;

    if (!notifs.length) {
        list.innerHTML = `
            <div class="notif-empty">
                <svg width="32" height="32" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <p>No notifications yet</p>
            </div>`;
        return;
    }

    list.innerHTML = notifs.slice(0, 15).map(n => {
        let link = '{{ route("admin.requests") }}';
        if (n.type === 'api_key' || n.type === 'system') {
            link = '{{ route("admin.settings") }}#ai-keys';
        } else if (n.request_id) {
            link = `{{ url('admin/requests') }}/${n.request_id}`;
        }
        return `
            <a href="${link}"
               class="notif-item ${!n.is_read ? 'unread' : ''}"
               onclick="handleAdminNotifClick(event, '${esc(String(n.id))}', '${link}')">
                ${getIcon(n.type)}
                <div style="flex:1;min-width:0;">
                    <div class="notif-title">${esc(n.title)}</div>
                    <div class="notif-msg">${esc(n.message)}</div>
                    <div class="notif-time">${timeAgo(n.created_at)}</div>
                </div>
            </a>`;
    }).join('');
}

function handleAdminNotifClick(e, id, url) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const badge = document.getElementById('notif-badge');
    if (badge && badge.textContent) {
        const cur = parseInt(badge.textContent, 10);
        if (!isNaN(cur) && cur > 1) {
            badge.textContent = cur - 1;
        } else {
            badge.textContent = '';
            badge.classList.remove('has-count');
        }
    }
    fetch(NOTIF_READ_URL, {
        method: 'POST',
        keepalive: true,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id: id })
    }).catch(() => {}).finally(() => {
        if (url) window.location.href = url;
    });
}

function markRead(id, el) {
    fetch(NOTIF_READ_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id: id })
    }).then(() => {
        if (el) el.classList.remove('unread');
        loadNotifications();
    }).catch(() => {});
}

function markAllRead(e) {
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    const badge = document.getElementById('notif-badge');
    if (badge) {
        badge.textContent = '';
        badge.classList.remove('has-count');
    }
    const label = document.getElementById('notif-unread-label');
    if (label) label.textContent = '';
    document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));

    fetch(NOTIF_READ_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ all: true })
    }).then(() => loadNotifications()).catch(() => {});
}

// Initial load + poll every 30s
loadNotifications();
setInterval(loadNotifications, 30000);

// ══════════════════════════════════════════════════════
// BULLDOG AI LOGIC — v2 (Dashboard + Meta Analytics aware)
// ══════════════════════════════════════════════════════
(function() {

    // ── DASHBOARD DATA ───────────────────────────────────────────
    const BD_DASH = {
        total:        {{ $total          ?? 0 }},
        pending:      {{ $pending        ?? 0 }},
        review:       {{ $review         ?? 0 }},
        approved:     {{ $approved       ?? 0 }},
        posted:       {{ $posted         ?? 0 }},
        rejected:     {{ $rejected       ?? 0 }},
        users:        {{ $users          ?? 0 }},
        thisMonth:    {{ $this_month_count ?? 0 }},
        highPrio:     {{ $high_prio_count  ?? 0 }},
        approvalRate: {{ $approval_rate   ?? 0 }},
        topRequestors: @json(isset($top_requestors) ? $top_requestors->take(3)->pluck('requester') : []),
        recentTitles:  @json(isset($recent) ? $recent->take(5)->pluck('title') : []),
    };

    // ── META / FACEBOOK ANALYTICS DATA ───────────────────────────
    // Safely defaults to 0/[] on pages that don't pass these vars
    const BD_META = {
        pageLikes:       {{ $page_likes       ?? $pageLikes       ?? 0 }},
        pageFollowers:   {{ $page_followers   ?? $pageFollowers   ?? 0 }},
        pageReach:       {{ $page_reach       ?? $pageReach       ?? 0 }},
        pageImpressions: {{ $page_impressions ?? $pageImpressions ?? 0 }},
        pageEngagement:  {{ $page_engagement  ?? $pageEngagement  ?? 0 }},
        avgReach:        {{ $avg_reach        ?? $avgReach        ?? 0 }},
        avgImpressions:  {{ $avg_impressions  ?? $avgImpressions  ?? 0 }},
        avgEngagement:   {{ $avg_engagement   ?? $avgEngagement   ?? 0 }},
        totalPosts:      {{ $total_posts      ?? $totalPosts      ?? 0 }},
        totalLikes:      {{ $total_likes      ?? $totalLikes      ?? 0 }},
        totalComments:   {{ $total_comments   ?? $totalComments   ?? 0 }},
        totalShares:     {{ $total_shares     ?? $totalShares     ?? 0 }},
        totalReactions:  {{ $total_reactions  ?? $totalReactions  ?? 0 }},
        topPosts:   @json(isset($top_posts)   ? collect($top_posts)->take(3)->map(fn($p) => ['title' => $p['message'] ?? $p['title'] ?? 'Untitled', 'reach' => $p['reach'] ?? 0, 'engagement' => $p['engagement'] ?? 0]) : []),
        reachTrend: @json(isset($reach_trend) ? $reach_trend  : (isset($reachTrend)  ? $reachTrend  : [])),
        bestDays:   @json(isset($best_days)   ? $best_days    : (isset($bestDays)    ? $bestDays    : [])),
        peakHours:  @json(isset($peak_hours)  ? $peak_hours   : (isset($peakHours)   ? $peakHours   : [])),
        period:      '{{ $period ?? $analyticsPeriod ?? "last 30 days" }}',
        lastUpdated: '{{ isset($last_updated) ? \Carbon\Carbon::parse($last_updated)->format("M j, Y g:i A") : (isset($lastUpdated) ? \Carbon\Carbon::parse($lastUpdated)->format("M j, Y g:i A") : "N/A") }}',
    };

    // ── PAGE CONTEXT ─────────────────────────────────────────────
    const BD_PAGE = {
        current: '{{ request()->routeIs("admin.dashboard") ? "dashboard" : (request()->routeIs("admin.analytics*") ? "analytics" : (request()->routeIs("admin.reports*") ? "reports" : (request()->routeIs("admin.requests*") ? "requests" : (request()->routeIs("admin.calendar") ? "calendar" : "other")))) }}',
        date:    '{{ now()->format("l, F j, Y") }}',
        time:    '{{ now()->format("g:i A") }}',
    };

    // ── BUILD FULL CONTEXT STRING ─────────────────────────────────
    function buildContext() {
        const hasMeta = BD_META.pageLikes > 0 || BD_META.pageReach > 0 || BD_META.totalPosts > 0;

        let ctx = `=== NUPOST DASHBOARD ===
Total Requests: ${BD_DASH.total} | Pending: ${BD_DASH.pending} | Under Review: ${BD_DASH.review}
Approved: ${BD_DASH.approved} | Posted: ${BD_DASH.posted} | Rejected: ${BD_DASH.rejected}
Approval Rate: ${BD_DASH.approvalRate}% | High Priority: ${BD_DASH.highPrio} | New This Month: ${BD_DASH.thisMonth}
Registered Users: ${BD_DASH.users}
Top Requestors: ${BD_DASH.topRequestors.join(', ') || 'N/A'}
Recent Titles: ${BD_DASH.recentTitles.join(' | ') || 'None'}`;

        if (hasMeta) {
            ctx += `

=== FACEBOOK / META ANALYTICS (${BD_META.period}) ===
Page Likes: ${BD_META.pageLikes} | Followers: ${BD_META.pageFollowers}
Page Reach: ${BD_META.pageReach} | Impressions: ${BD_META.pageImpressions} | Engagement: ${BD_META.pageEngagement}
Avg Post Reach: ${BD_META.avgReach} | Avg Impressions: ${BD_META.avgImpressions} | Avg Engagement: ${BD_META.avgEngagement}
Total Likes: ${BD_META.totalLikes} | Comments: ${BD_META.totalComments} | Shares: ${BD_META.totalShares} | Reactions: ${BD_META.totalReactions}
Total Posts Analyzed: ${BD_META.totalPosts}
Best Days to Post: ${BD_META.bestDays.join(', ') || 'N/A'}
Peak Hours: ${BD_META.peakHours.join(', ') || 'N/A'}
Top Posts: ${BD_META.topPosts.map(p => `"${p.title}" (Reach: ${p.reach}, Engagement: ${p.engagement})`).join(' | ') || 'N/A'}
Last Updated: ${BD_META.lastUpdated}`;
        }

        ctx += `

=== SESSION ===
Current Page: ${BD_PAGE.current} | Date: ${BD_PAGE.date} | Time: ${BD_PAGE.time}`;

        return { ctx, hasMeta };
    }

    // ── STATE ────────────────────────────────────────────────────
    let isOpen       = false;
    let briefingDone = false;
    let chatHistory  = [];

    // ── TOGGLE ───────────────────────────────────────────────────
    window.toggleBulldog = function() {
        isOpen = !isOpen;
        document.getElementById('bulldog-modal').classList.toggle('open', isOpen);
        if (isOpen) {
            document.getElementById('bulldog-notif-dot').style.display = 'none';
            if (!briefingDone) loadBriefing(false);
        }
    };

    // ── TABS ─────────────────────────────────────────────────────
    window.switchTab = function(tab) {
        const tabNames = ['briefing', 'chat', 'actions'];
        document.querySelectorAll('.bd-tab').forEach((t, i) => {
            t.classList.toggle('active', tabNames[i] === tab);
        });
        document.querySelectorAll('.bd-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('bd-panel-' + tab).classList.add('active');
    };

    // ── GROQ API ─────────────────────────────────────────────────
    async function callGroq(messages) {
        const res = await fetch('/api/bulldog-chat', {
            method: 'POST',
            headers: {
                'Content-Type':  'application/json',
                'Accept':        'application/json',
                'X-CSRF-TOKEN':  window.CSRF || document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ messages })
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        return data.reply;
    }

    // ── BRIEFING ─────────────────────────────────────────────────
    window.loadBriefing = async function(manual = false) {
        const loadEl     = document.getElementById('bd-loading');
        const textEl     = document.getElementById('bd-briefing-text');
        const errEl      = document.getElementById('bd-briefing-error');
        const refreshBtn = document.getElementById('bd-refresh-btn');

        loadEl.style.display = 'flex';
        textEl.style.display = 'none';
        errEl.style.display  = 'none';
        refreshBtn.disabled  = true;

        const { ctx, hasMeta } = buildContext();

        const briefingPrompt = `You are Bulldog, the sharp and loyal AI assistant for NUPost admin panel.
${ctx}

Write a quick, friendly admin briefing. Use emojis sparingly, keep it under 130 words. Cover:
1. Request queue — what needs immediate action (pending + high priority)
2. ${hasMeta ? 'Facebook page health — highlight reach, engagement, or top post' : 'Approval wins or notable patterns'}
3. One specific recommended action for today

Tone: direct, confident, like a loyal assistant who read all the data. Plain text only, no markdown, no bullet symbols.`;

        try {
            const reply = await callGroq([{ role: 'user', content: briefingPrompt }]);
            textEl.textContent   = reply;
            textEl.style.display = 'block';
            briefingDone         = true;
        } catch(e) {
            errEl.innerHTML     = `⚠️ Briefing failed: ${e.message}<br><small style="opacity:.65;">Check GROQ_API_KEY in .env and the /api/bulldog-chat route.</small>`;
            errEl.style.display = 'block';
        } finally {
            loadEl.style.display = 'none';
            refreshBtn.disabled  = false;
        }
    };

    // ── CHAT ─────────────────────────────────────────────────────
    window.sendBulldogChat = async function() {
        const input   = document.getElementById('bd-chat-input');
        const sendBtn = document.getElementById('bd-send-btn');
        const msgBox  = document.getElementById('bd-chat-messages');
        const text    = input.value.trim();
        if (!text) return;

        appendBulldogMsg('user', text);
        input.value      = '';
        sendBtn.disabled = true;

        const typingEl = document.createElement('div');
        typingEl.className = 'bd-msg bd-msg--bot';
        typingEl.innerHTML = `<div class="bd-msg-av">B</div><div class="bd-typing"><span></span><span></span><span></span></div>`;
        msgBox.appendChild(typingEl);
        msgBox.scrollTop = msgBox.scrollHeight;

        const { ctx, hasMeta } = buildContext();

        const sysPrompt = `You are Bulldog, the NUPost admin AI assistant. Sharp, friendly, and fully data-aware.

${ctx}

YOUR CAPABILITIES:
- Answer anything about the request queue (pending, approved, rejected, high priority, requestors, titles)
- Analyze Facebook/Meta performance: ${hasMeta ? 'you have live analytics data above — use it' : 'no Meta data on this page, tell user to visit the Analytics page for that info'}
- Explain request analytics trends: reach vs impressions, engagement rate, best days/hours to post, top content patterns
- Give clear, actionable recommendations based on real data
- Help the admin prioritize and plan their day

RULES:
- Plain text only — no markdown, no asterisks, no bullet point symbols, no dashes as bullets
- Max 90 words per reply
- Be direct and confident
- If asked about something you have no data for, say so clearly and suggest where to find it`;

        chatHistory.push({ role: 'user', content: text });

        try {
            const messages = [
                { role: 'user',      content: sysPrompt },
                { role: 'assistant', content: 'Got it. I have full context of the NUPost dashboard and Meta analytics. Ready.' },
                ...chatHistory.slice(-8)
            ];
            const reply = await callGroq(messages);
            chatHistory.push({ role: 'assistant', content: reply });
            typingEl.remove();
            appendBulldogMsg('bot', reply);
        } catch(e) {
            typingEl.remove();
            appendBulldogMsg('bot', `Woof — hit a snag: ${e.message}`);
        } finally {
            sendBtn.disabled = false;
            input.focus();
        }
    };

    function appendBulldogMsg(role, text) {
        const msgBox = document.getElementById('bd-chat-messages');
        const div    = document.createElement('div');
        div.className = `bd-msg bd-msg--${role === 'user' ? 'user' : 'bot'}`;
        div.innerHTML = `
            <div class="bd-msg-av">${role === 'user' ? 'A' : 'B'}</div>
            <div class="bd-msg-bubble">${escapeBulldogHtml(text)}</div>`;
        msgBox.appendChild(div);
        msgBox.scrollTop = msgBox.scrollHeight;
    }

    function escapeBulldogHtml(str) {
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── NOTIF DOT ────────────────────────────────────────────────
    setTimeout(function() {
        const attention = BD_DASH.pending + BD_DASH.review;
        if (attention > 0 && !isOpen) {
            const dot       = document.getElementById('bulldog-notif-dot');
            dot.textContent = attention > 9 ? '9+' : attention;
            dot.style.display = 'flex';
        }
    }, 1500);

})();
</script>
<script>
// Theme toggle initialization
document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle && !themeToggle._bound) {
        themeToggle._bound = true;
        themeToggle.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme') || 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('nupost-theme', next);
        });
    }
});
</script>
<script>
// Prevent viewing authenticated pages via back button after logout (bfcache)
window.addEventListener('pageshow', function (event) {
    if (event.persisted || (window.performance && window.performance.navigation && window.performance.navigation.type === 2)) {
        window.location.reload();
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    if (link.href.includes('/export') || link.href.includes('/download')) return;

    const bar = document.getElementById('np-page-progress');
    if (bar) {
        bar.style.opacity = '1';
        bar.style.width = '75%';
    }
});
</script>
@yield('scripts')
</body>
</html>