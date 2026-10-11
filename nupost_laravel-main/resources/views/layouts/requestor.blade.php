<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NUPost – @yield('title', 'Dashboard')</title>
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
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet">
<style>
:root {
    --sidebar-bg:   #111111;
    --accent:       #3b6ef5;
    --accent-dim:   rgba(59,110,245,0.15);
    --navy:         #002366;
    --navy-light:   #1e4fd8;
    --navy-pale:    #e8eef8;
    --page-bg:      #d8dce8;
    --card-bg:      #ffffff;
    --border:       #e4e8f0;
    --text:         #111827;
    --text-muted:   #6b7280;
    --text-faint:   #9ca3af;
    --font:         'DM Sans', sans-serif;
    --topbar-h:     64px;
    --radius-xl:    22px;
    --radius-lg:    16px;
    --radius:       12px;
    --radius-sm:    8px;
    --sb-w:         80px;
    --sb-inner:     56px;
    --amber:        #f59e0b;
}

:root[data-theme="dark"],
html[data-theme="dark"],
[data-theme="dark"] {
    --page-bg:      #040c1e;
    --card-bg:      #081636;
    --border:       rgba(30, 79, 216, 0.25);
    --text:         #f8fafc;
    --text-muted:   #94a3b8;
    --text-faint:   #64748b;
    --navy-pale:    rgba(30, 79, 216, 0.25);

    --card:         #081636;
    --stroke:       rgba(30, 79, 216, 0.25);
    --ink:          #f8fafc;
    --ink-mid:      #cbd5e1;
    --ink-soft:     #94a3b8;
    --sand:         #0a1b42;
}

/* ── DARK THEME COMPONENT OVERRIDES ── */
[data-theme="dark"] .main-wrapper,
[data-theme="dark"] .page-content,
[data-theme="dark"] .page-outer,
[data-theme="dark"] .create-page,
[data-theme="dark"] .trk {
    background: var(--page-bg) !important;
}

[data-theme="dark"] .topbar {
    background: var(--card-bg);
    border-bottom-color: var(--border);
}
[data-theme="dark"] .topbar__title {
    color: #f8fafc;
}

[data-theme="dark"] .topbar__user {
    background: #0b1a3d;
    border-color: var(--border);
    color: #f8fafc;
}
[data-theme="dark"] .topbar__user:hover {
    background: #0f2250;
    border-color: #f59e0b;
}

[data-theme="dark"] .notif-btn {
    background: #0b1a3d;
    border-color: var(--border);
    color: var(--text-muted);
}
[data-theme="dark"] .notif-btn:hover {
    background: #0f2250;
    color: #f8fafc;
}
[data-theme="dark"] .notif-dropdown {
    background: var(--card-bg);
    border-color: var(--border);
    box-shadow: 0 12px 40px rgba(0,0,0,0.5);
}
[data-theme="dark"] .notif-head,
[data-theme="dark"] .notif-footer {
    background: #06112a;
    border-color: var(--border);
}
[data-theme="dark"] .notif-head__title {
    color: #f8fafc;
}
[data-theme="dark"] .notif-item {
    color: #f8fafc;
    border-bottom-color: rgba(255,255,255,0.05);
}
[data-theme="dark"] .notif-item:hover {
    background: rgba(30, 79, 216, 0.15);
}
[data-theme="dark"] .notif-icon--default {
    background: #0d214d;
}

/* Forms & Inputs in Dark Mode */
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
    background-color: #0d214d !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
}
[data-theme="dark"] input::placeholder,
[data-theme="dark"] textarea::placeholder {
    color: #64748b !important;
}
[data-theme="dark"] .field label {
    color: #94a3b8 !important;
}
[data-theme="dark"] .field label .opt {
    color: #64748b !important;
}

/* Cards, Panels, Sections */
[data-theme="dark"] .form-section,
[data-theme="dark"] .form-card,
[data-theme="dark"] .form-actions,
[data-theme="dark"] .profile-card,
[data-theme="dark"] .action-item,
[data-theme="dark"] .stat-card,
[data-theme="dark"] .white-box,
[data-theme="dark"] .settings-card,
[data-theme="dark"] .section-card,
[data-theme="dark"] .table-wrap,
[data-theme="dark"] .db-card,
[data-theme="dark"] .bubbles-card,
[data-theme="dark"] .mini-card,
[data-theme="dark"] .panel,
[data-theme="dark"] .qa-panel,
[data-theme="dark"] .upcoming-card,
[data-theme="dark"] .day-panel,
[data-theme="dark"] .trk-card {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    color: #f8fafc !important;
}

[data-theme="dark"] .section-head,
[data-theme="dark"] .panel__head,
[data-theme="dark"] .qa-head,
[data-theme="dark"] .trk-card-head,
[data-theme="dark"] .profile-member,
[data-theme="dark"] .stat-box {
    background: #06112a !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
}

/* Titles and Headings in Dark Mode */
[data-theme="dark"] .section-title-txt,
[data-theme="dark"] .panel__title,
[data-theme="dark"] .qa-head-title,
[data-theme="dark"] .bubbles-card__title,
[data-theme="dark"] .upcoming-card h3,
[data-theme="dark"] .trk-title,
[data-theme="dark"] .chat-cta-name,
[data-theme="dark"] .ai-title-name {
    color: #f8fafc !important;
}

[data-theme="dark"] .section-sub,
[data-theme="dark"] .panel__sub,
[data-theme="dark"] .qa-sub,
[data-theme="dark"] .bubbles-card__sub,
[data-theme="dark"] .mini-card__label,
[data-theme="dark"] .req-meta,
[data-theme="dark"] .upcoming-meta,
[data-theme="dark"] .trk-sub,
[data-theme="dark"] .trk-card-title,
[data-theme="dark"] .detail-key,
[data-theme="dark"] .ai-hint {
    color: #94a3b8 !important;
}

[data-theme="dark"] .mini-card__value,
[data-theme="dark"] .bl-num,
[data-theme="dark"] .req-title,
[data-theme="dark"] .qa-label,
[data-theme="dark"] .upcoming-title,
[data-theme="dark"] .detail-val,
[data-theme="dark"] .tl-event {
    color: #f8fafc !important;
}

[data-theme="dark"] .bl-name,
[data-theme="dark"] .detail-desc,
[data-theme="dark"] .caption-text {
    color: #cbd5e1 !important;
}

[data-theme="dark"] .req-item,
[data-theme="dark"] .qa-item,
[data-theme="dark"] .upcoming-item {
    border-bottom-color: rgba(30, 79, 216, 0.15) !important;
    color: #f8fafc !important;
}

[data-theme="dark"] .req-item:hover,
[data-theme="dark"] .qa-item:hover {
    background: rgba(30, 79, 216, 0.12) !important;
}

/* Upload zone, Platform buttons, AI Section */
[data-theme="dark"] .upload-zone {
    background: #0b1a3d !important;
    border-color: rgba(30, 79, 216, 0.35) !important;
}
[data-theme="dark"] .upload-zone:hover {
    background: #0f2250 !important;
    border-color: #3b82f6 !important;
}
[data-theme="dark"] .upload-icon-wrap {
    background: rgba(59, 110, 245, 0.2) !important;
}
[data-theme="dark"] .upload-zone p {
    color: #94a3b8 !important;
}
[data-theme="dark"] .upload-zone strong {
    color: #60a5fa !important;
}
[data-theme="dark"] .upload-tag {
    background: #06112a !important;
    color: #94a3b8 !important;
}

[data-theme="dark"] .platform-btn {
    background: #0b1a3d !important;
    color: #cbd5e1 !important;
    border-color: rgba(30, 79, 216, 0.3) !important;
}
[data-theme="dark"] .platform-btn:hover:not(.selected) {
    background: #102654 !important;
    color: #93c5fd !important;
    border-color: #3b82f6 !important;
}
[data-theme="dark"] .platform-btn.selected {
    background: #1d4ed8 !important;
    color: white !important;
    border-color: #3b82f6 !important;
    box-shadow: 0 2px 10px rgba(29, 78, 216, 0.4) !important;
}

[data-theme="dark"] .ai-section {
    background: #081636 !important;
    border-color: rgba(124, 58, 237, 0.35) !important;
}
[data-theme="dark"] .ai-head {
    background: #06112a !important;
    border-bottom-color: rgba(124, 58, 237, 0.25) !important;
}
[data-theme="dark"] .ai-icon {
    background: rgba(124, 58, 237, 0.2) !important;
}
[data-theme="dark"] .ai-badge {
    background: rgba(124, 58, 237, 0.25) !important;
    border-color: rgba(124, 58, 237, 0.4) !important;
    color: #c4b5fd !important;
}
[data-theme="dark"] .ai-footer-preview {
    background: rgba(124, 58, 237, 0.12) !important;
    border-color: rgba(124, 58, 237, 0.3) !important;
    color: #c4b5fd !important;
}
[data-theme="dark"] .ai-footer-preview__label {
    color: #a78bfa !important;
}

[data-theme="dark"] .btn-cancel {
    background: #0b1a3d !important;
    border-color: rgba(30, 79, 216, 0.3) !important;
    color: #94a3b8 !important;
}
[data-theme="dark"] .btn-cancel:hover {
    background: #0f2250 !important;
    color: #f8fafc !important;
}

[data-theme="dark"] .empty-state__icon {
    background: #0b1a3d !important;
    border: 1.5px solid rgba(30, 79, 216, 0.3) !important;
    color: #60a5fa !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25) !important;
}

/* Tables */
[data-theme="dark"] table th {
    background-color: #06112a !important;
    color: #94a3b8 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] table td {
    border-color: rgba(30, 79, 216, 0.15) !important;
    color: #f8fafc !important;
}
[data-theme="dark"] table tr:hover td {
    background-color: rgba(30, 79, 216, 0.08) !important;
}

/* Theme Toggle Button */
.theme-toggle-btn {
    width: 38px; height: 38px; border-radius: 50%;
    background: #f4f6fb; border: 1.5px solid var(--border);
    color: var(--text); display: flex; align-items: center;
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
.sb-create {
    width: 44px; height: 44px; border-radius: 14px;
    background: var(--accent); color: white;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; position: relative;
    box-shadow: 0 4px 14px rgba(59,110,245,0.5);
}
.sb-create svg {
    position: relative; z-index: 2;
    transition: transform .25s cubic-bezier(.34,1.4,.64,1);
}
.sb-create:hover svg { transform: rotate(90deg) scale(1.15); }

/* Pulse rings */
.sb-create::before,
.sb-create::after {
    content: '';
    position: absolute; inset: 0;
    border-radius: 14px;
    background: var(--accent);
    z-index: 0;
    animation: pulseRing 2.2s cubic-bezier(0.25,0.46,0.45,0.94) infinite;
}
.sb-create::after { animation-delay: 1.1s; }

@keyframes pulseRing {
    0%   { transform: scale(1);    opacity: 0.55; }
    80%  { transform: scale(1.75); opacity: 0; }
    100% { transform: scale(1.75); opacity: 0; }
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body {
    font-family: var(--font);
    background: var(--sidebar-bg);
    color: var(--text);
    font-size: 14px;
    -webkit-font-smoothing: antialiased;
    height: 100%;
    overflow: hidden;
}
.shell { display: flex; height: 100vh; background: #111111; }

/* ── SIDEBAR ─────────────────────────────────── */
.sidebar {
    width: var(--sb-w); min-width: var(--sb-w); flex-shrink: 0;
    background: #111827;
    display: flex; flex-direction: column; align-items: center;
    padding: 18px 12px 18px; gap: 0; z-index: 200;
}

/* Navy logo — same as admin */
.sidebar__logo {
    width: var(--sb-inner); height: var(--sb-inner);
    border-radius: 16px; background: var(--navy);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; box-shadow: 0 4px 16px rgba(0,35,102,0.5);
    margin-bottom: 20px; text-decoration: none; transition: transform .2s;
}
.sidebar__logo:hover { transform: scale(1.06); }
.sidebar__logo svg { color: white; }

.sb-group {
    background: #1e2a3a; border-radius: 20px;
    padding: 6px; display: flex; flex-direction: column;
    gap: 2px; width: var(--sb-inner); flex-shrink: 0;
}
.sb-spacer { flex: 1; }

.sb-item {
    position: relative; width: 44px; height: 44px; border-radius: 14px;
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
.sb-item--notifs:hover    { color: #fbbf24 !important; }
.sb-item.active { color: white; box-shadow: 0 3px 12px rgba(0,0,0,0.3); }
.sb-item.active svg { transform: scale(1.05); }
.sb-item--dashboard.active { background: rgba(59,110,245,0.28); }
.sb-item--requests.active  { background: rgba(16,185,129,0.25); }
.sb-item--calendar.active  { background: rgba(168,85,247,0.25); }
.sb-item--notifs.active    { background: rgba(245,158,11,0.25); }
.sb-item.active::before {
    content: ''; position: absolute; left: -6px; top: 28%; height: 44%;
    width: 3px; border-radius: 0 3px 3px 0; background: var(--amber);
}
.sb-item__dot {
    position: absolute; top: 8px; right: 8px;
    width: 7px; height: 7px; background: #ef4444;
    border-radius: 50%; border: 2px solid #111827;
}
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

/* Mini calendar */
.sidebar__cal {
    margin: 10px 0 14px; padding: 12px 8px;
    background: #1e2a3a; border-radius: 14px; width: var(--sb-inner);
    display: flex; flex-direction: column; align-items: center; gap: 5px;
}
.cal__month   { font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: rgba(255,255,255,0.4); }
.cal__day-num { font-size: 20px; font-weight: 700; color: white; line-height: 1; }
.cal__weekday { font-size: 9px; color: rgba(255,255,255,0.4); font-weight: 600; }
.cal__dots    { display: flex; gap: 3px; }
.cal__dot     { width: 5px; height: 5px; border-radius: 50%; background: var(--accent); opacity: 0.6; }
.cal__dot:first-child { opacity: 1; }

.sb-foot { display: flex; flex-direction: column; align-items: center; gap: 6px; width: var(--sb-inner); flex-shrink: 0; }

/* Create button */
.sb-create {
    width: 44px; height: 44px; border-radius: 14px;
    background: var(--accent); color: white;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; transition: all .2s cubic-bezier(.34,1.4,.64,1);
    box-shadow: 0 4px 14px rgba(59,110,245,0.5); position: relative;
}
.sb-create:hover { transform: scale(1.08); box-shadow: 0 6px 20px rgba(59,110,245,0.6); }
.sb-create:hover svg { transform: scale(1.15) rotate(90deg); }
.sb-create svg { transition: transform .2s cubic-bezier(.34,1.4,.64,1); }

.sb-foot-item {
    position: relative; width: 44px; height: 44px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.35); cursor: pointer;
    background: none; border: none;
    transition: all .2s cubic-bezier(.34,1.4,.64,1); font-family: var(--font);
}
.sb-foot-item:hover { background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.8); }
.sb-foot-item:hover svg { transform: scale(1.12); }
.sb-foot-item svg { transition: transform .2s; }

/* ── MAIN WRAPPER ──────────────────────────── */
.main-wrapper {
    flex: 1; background: var(--page-bg); border-radius: var(--radius-xl);
    margin: 10px 10px 10px 0;
    display: flex; flex-direction: column; overflow: hidden; min-width: 0;
}

/* ── TOPBAR ──────────────────────────────── */
.topbar {
    height: var(--topbar-h); background: var(--card-bg);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center;
    padding: 0 28px; gap: 16px; flex-shrink: 0;
    border-radius: var(--radius-xl) var(--radius-xl) 0 0;
}
.topbar__title { font-size: 20px; font-weight: 700; letter-spacing: -0.4px; }
.topbar__actions { display: flex; align-items: center; gap: 10px; margin-left: auto; }

/* ── NOTIFICATION BELL ─────────────────── */
.notif-wrap { position: relative; }
.notif-btn {
    position: relative; width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    background: #f4f6fb; border: 1.5px solid var(--border);
    cursor: pointer; color: var(--text-muted); transition: all .15s;
}
.notif-btn:hover { background: var(--border); color: var(--text); }
.notif-badge {
    position: absolute; top: -4px; right: -4px;
    min-width: 18px; height: 18px; border-radius: 9px;
    background: #ef4444; color: white;
    font-size: 10px; font-weight: 700;
    display: none; align-items: center; justify-content: center;
    padding: 0 4px; border: 2px solid var(--card-bg);
}
.notif-badge.has-count { display: flex; }

.notif-dropdown {
    position: absolute; top: calc(100% + 10px); right: 0;
    width: 340px; background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 18px; box-shadow: 0 12px 40px rgba(0,0,0,0.12);
    z-index: 300; overflow: hidden; display: none;
    animation: dropIn .2s ease;
}
.notif-dropdown.open { display: block; }
@keyframes dropIn { from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:none} }

.notif-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 16px; border-bottom: 1px solid var(--border);
    background: #f4f6fb;
}
.notif-head__title { font-size: 14px; font-weight: 700; color: var(--text); }
.notif-head__mark {
    font-size: 11.5px; font-weight: 600; color: var(--navy-light);
    cursor: pointer; background: none; border: none; font-family: var(--font); padding: 0;
}
.notif-head__mark:hover { text-decoration: underline; }

.notif-list { max-height: 340px; overflow-y: auto; }
.notif-list::-webkit-scrollbar { width: 4px; }
.notif-list::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 4px; }

.notif-item {
    display: flex; align-items: flex-start; gap: 11px;
    padding: 12px 16px; border-bottom: 1px solid rgba(0,0,0,0.04);
    cursor: pointer; transition: background .12s; text-decoration: none;
    position: relative;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: #f4f6fb; }
.notif-item.unread { background: rgba(0,35,102,0.03); }
.notif-item.unread::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0;
    width: 3px; background: var(--navy-light); border-radius: 0 3px 3px 0;
}
.notif-icon { width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 15px; }
.notif-icon--request { background: #dbeafe; }
.notif-icon--comment { background: #dcfce7; }
.notif-icon--default { background: #f3f4f6; }
.notif-title { font-size: 12.5px; font-weight: 600; color: var(--text); line-height: 1.4; }
.notif-msg   { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; line-height: 1.45; }
.notif-time  { font-size: 10.5px; color: var(--text-faint); margin-top: 3px; }
.notif-empty {
    padding: 36px 20px; text-align: center; color: var(--text-faint);
    display: flex; flex-direction: column; align-items: center; gap: 8px;
}
.notif-empty p { font-size: 13px; }
.notif-footer {
    padding: 10px 16px; border-top: 1px solid var(--border);
    text-align: center; background: #f4f6fb;
}
.notif-footer a { font-size: 12.5px; font-weight: 600; color: var(--navy-light); text-decoration: none; }
.notif-footer a:hover { text-decoration: underline; }

/* User chip */
.topbar__user {
    display: flex; align-items: center; gap: 8px;
    padding: 4px 12px 4px 4px; border: 1.5px solid var(--border); border-radius: 24px;
    cursor: pointer; transition: all .15s; text-decoration: none; color: var(--text); background: white;
}
.topbar__user:hover { border-color: #c8d0de; background: #f4f6fb; }
.topbar__avatar {
    width: 30px; height: 30px; border-radius: 50%;
    background: var(--accent-dim);
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; font-weight: 700; color: var(--accent);
    overflow: hidden; flex-shrink: 0;
}
.topbar__avatar img { width: 100%; height: 100%; object-fit: cover; }
.topbar__username { font-size: 13px; font-weight: 600; }

/* ── PAGE CONTENT ──────────────────────── */
.page-content { flex: 1; overflow-y: auto; background: var(--page-bg); }

/* ── GLOBAL BADGES ──────────────────────── */
.badge { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap; }
.badge--approved     { background: #dcfce7; color: #15803d; }
.badge--posted       { background: #ede9fe; color: #6d28d9; }
.badge--under-review { background: #fef3c7; color: #b45309; }
.badge--pending      { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
.badge--rejected     { background: #fee2e2; color: #b91c1c; }
.badge--high         { background: #fee2e2; color: #b91c1c; }
.badge--urgent       { background: #fff7ed; color: #c2410c; }
.badge--medium       { background: #fef3c7; color: #b45309; }
.badge--low          { background: #f3f4f6; color: #4b5563; border: 1px solid #e5e7eb; }
.tag { display: inline-flex; padding: 2px 8px; border-radius: 5px; font-size: 11px; background: #f3f4f6; color: #374151; }
.alert { padding: 12px 16px; border-radius: var(--radius-sm); font-size: 13px; margin-bottom: 16px; font-weight: 500; }
.alert--success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.alert--error   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

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
    .sidebar__logo, .sidebar__cal, .sb-spacer { display: none; }
    .sb-group { flex-direction: row; width: auto; padding: 0; gap: 6px; background: transparent; }
    .sb-foot { flex-direction: row; width: auto; gap: 6px; }
    .sb-label { display: none !important; }
    .sb-item.active::before { display: none; }
    .sb-create { margin-bottom: 0 !important; }

    .main-wrapper {
        margin: 0; border-radius: 0;
        margin-bottom: 65px; 
    }
    .topbar { border-radius: 0; padding: 0 16px; height: 60px; gap: 8px; }
    .topbar__username { display: none; }
    .topbar__title { font-size: 17px; }
    
    .page-hd { margin-bottom: 16px; }
    
    /* Grids & Layouts */
    .dashboard-grid, .settings-layout, .field-row, .kpi-grid, .grid, .profile-layout {
        display: flex !important; flex-direction: column !important; gap: 16px;
    }
    
    /* Scrollable tables */
    .table-wrapper { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    table { min-width: 600px; } 

    /* Dropdowns */
    .notif-dropdown { width: calc(100vw - 32px); right: -16px; max-width: 360px; }

    /* Calendar */
    .calendar-container { overflow-x: auto; }
    .calendar-grid { min-width: 700px; }
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
button, .btn, .btn-primary, .back-btn, .hero-card__btn, .tab-btn {
    transition: transform 0.18s cubic-bezier(0.16, 1, 0.3, 1), 
                box-shadow 0.18s cubic-bezier(0.16, 1, 0.3, 1), 
                background-color 0.18s ease, 
                border-color 0.18s ease !important;
}
button:active, .btn:active, .btn-primary:active, .back-btn:active, .hero-card__btn:active {
    transform: scale(0.97) !important;
}

.card, .scard, .hero-card, .table-card {
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

.badge, .status-pill, .hero-stat {
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s ease;
}
.badge:hover, .hero-stat:hover {
    transform: translateY(-2px);
}

.notif-dropdown {
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

</style>
@yield('head-styles')
</head>
<body>
<div id="np-page-progress"></div>
<div class="shell">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <!-- Navy logo — same as admin -->
        <a href="{{ route('requestor.dashboard') }}" class="sidebar__logo">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                <path d="M2 17l10 5 10-5"/>
                <path d="M2 12l10 5 10-5"/>
            </svg>
        </a>

        <!-- Main nav group -->
        <div class="sb-group">
            <a href="{{ route('requestor.dashboard') }}"
               class="sb-item sb-item--dashboard {{ request()->routeIs('requestor.dashboard') ? 'active' : '' }}">
                <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg>
                <span class="sb-label">Dashboard</span>
            </a>
            <a href="{{ route('requestor.requests') }}"
               class="sb-item sb-item--requests {{ request()->routeIs('requestor.requests*') && !request()->routeIs('requestor.requests.create') ? 'active' : '' }}">
                <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span class="sb-label">My Requests</span>
            </a>
            <a href="{{ route('requestor.calendar') }}"
               class="sb-item sb-item--calendar {{ request()->routeIs('requestor.calendar') ? 'active' : '' }}">
                <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2.5"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span class="sb-label">Calendar</span>
            </a>
            <a href="{{ route('requestor.notifications') }}"
               class="sb-item sb-item--notifs {{ request()->routeIs('requestor.notifications') ? 'active' : '' }}">
                <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <span class="sb-item__dot" id="sb-notif-dot" style="display:none;"></span>
                <span class="sb-label">Notifications</span>
            </a>
        </div>

        <div class="sb-spacer"></div>

        <!-- Mini calendar -->
        <div class="sidebar__cal">
            <div class="cal__month">{{ date('M') }}</div>
            <div class="cal__day-num">{{ date('j') }}</div>
            <div class="cal__weekday">{{ date('D') }}</div>
            <div class="cal__dots"><div class="cal__dot"></div><div class="cal__dot"></div><div class="cal__dot"></div></div>
        </div>

        <!-- Footer -->
        <div class="sb-foot">
            <a href="{{ route('requestor.requests.create') }}" class="sb-create" style="margin-bottom:6px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span class="sb-label">New Request</span>
            </a>
            <button class="sb-foot-item" onclick="window.location='{{ route('requestor.profile') }}'">
                <svg width="19" height="19" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span class="sb-label">Profile</span>
            </button>
        </div>
    </aside>

    <!-- MAIN WRAPPER -->
    <div class="main-wrapper">

        <header class="topbar">
            <div class="topbar__title">@yield('page-title', 'Dashboard')</div>
            <div class="topbar__actions">

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

                <!-- NOTIFICATION BELL -->
                <div class="notif-wrap" id="notif-wrap">
                    <button class="notif-btn" id="notif-btn" onclick="toggleNotif(event)">
                        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        <span class="notif-badge" id="notif-badge"></span>
                    </button>
                    <div class="notif-dropdown" id="notif-dropdown">
                        <div class="notif-head">
                            <div class="notif-head__title">Notifications <span id="notif-unread-label" style="font-size:11px;color:var(--text-muted);font-weight:400;"></span></div>
                            <button class="notif-head__mark" onclick="markAllRead()">Mark all read</button>
                        </div>
                        <div class="notif-list" id="notif-list">
                            <div class="notif-empty">
                                <svg width="32" height="32" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                <p>Loading…</p>
                            </div>
                        </div>
                        <div class="notif-footer">
                            <a href="{{ route('requestor.notifications') }}">View all notifications →</a>
                        </div>
                    </div>
                </div>

                <!-- USER CHIP -->
                <a href="{{ route('requestor.profile') }}" class="topbar__user">
                    <div class="topbar__avatar">
                        @if(isset($auth_user) && $auth_user->profile_photo && file_exists(public_path('uploads/' . $auth_user->profile_photo)))
                            <img src="/uploads/{{ $auth_user->profile_photo }}" alt="">
                        @else
                            {{ strtoupper(substr(session('name', 'U'), 0, 1)) }}
                        @endif
                    </div>
                    <span class="topbar__username">{{ session('name', 'User') }}</span>
                </a>
            </div>
        </header>

        <div class="page-content">
            @yield('content')
        </div>

    </div>
</div>

<script>
// ── NOTIFICATION BELL ────────────────────────────────────────────
const NOTIF_FETCH_URL = '{{ route("requestor.notifications.fetch") }}';
const NOTIF_READ_URL  = '{{ route("requestor.notifications.markread") }}';
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

let notifOpen = false;

function toggleNotif(e) {
    e.stopPropagation();
    notifOpen = !notifOpen;
    document.getElementById('notif-dropdown').classList.toggle('open', notifOpen);
    if (notifOpen) loadNotifications();
}

document.addEventListener('click', function(e) {
    if (!document.getElementById('notif-wrap').contains(e.target)) {
        notifOpen = false;
        document.getElementById('notif-dropdown')?.classList.remove('open');
    }
});

function timeAgo(dateStr) {
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (diff < 60)    return 'Just now';
    if (diff < 3600)  return Math.floor(diff/60) + 'm ago';
    if (diff < 86400) return Math.floor(diff/3600) + 'h ago';
    return Math.floor(diff/86400) + 'd ago';
}

function getIcon(type) {
    if (type === 'new_request' || type === 'request') return '<div class="notif-icon notif-icon--request">📄</div>';
    if (type === 'comment'     || type === 'reply')   return '<div class="notif-icon notif-icon--comment">💬</div>';
    return '<div class="notif-icon notif-icon--default">🔔</div>';
}

function loadNotifications() {
    fetch(NOTIF_FETCH_URL, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(data => renderNotifications(data.notifications || [], data.unread_count || 0))
    .catch(() => {});
}

function renderNotifications(notifs, unread) {
    // Badge
    const badge = document.getElementById('notif-badge');
    const dot   = document.getElementById('sb-notif-dot');
    if (unread > 0) {
        badge.textContent = unread > 99 ? '99+' : unread;
        badge.classList.add('has-count');
        if (dot) dot.style.display = 'block';
    } else {
        badge.classList.remove('has-count');
        if (dot) dot.style.display = 'none';
    }

    const label = document.getElementById('notif-unread-label');
    if (label) label.textContent = unread > 0 ? `(${unread} new)` : '';

    const list = document.getElementById('notif-list');
    if (!notifs.length) {
        list.innerHTML = '<div class="notif-empty"><svg width="32" height="32" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg><p>No notifications yet</p></div>';
        return;
    }

    list.innerHTML = notifs.slice(0, 12).map(n => {
        const link = n.request_id ? `/requestor/requests/${n.request_id}/chat` : '/requestor/notifications';
        return `<a href="${link}" class="notif-item ${!n.is_read ? 'unread' : ''}" onclick="markRead(${n.id}, this)">
            ${getIcon(n.type)}
            <div style="flex:1;min-width:0;">
                <div class="notif-title">${esc(n.title)}</div>
                <div class="notif-msg">${esc(n.message)}</div>
                <div class="notif-time">${timeAgo(n.created_at)}</div>
            </div>
        </a>`;
    }).join('');
}

function markRead(id, el) {
    fetch(NOTIF_READ_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ id })
    }).then(() => {
        el.classList.remove('unread');
        loadNotifications();
    });
}

function markAllRead() {
    fetch(NOTIF_READ_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ all: true })
    }).then(() => loadNotifications());
}

function esc(s) { return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

// Poll every 30s
if (!window._notifPollActive) {
    window._notifPollActive = true;
    setInterval(loadNotifications, 30000);
}

// ── THEME TOGGLE ──
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
    loadNotifications();
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