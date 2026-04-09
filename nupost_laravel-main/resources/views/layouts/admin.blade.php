<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NUPost Admin – @yield('title', 'Dashboard')</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
:root {
    --color-primary: #002366;
    --color-primary-light: #003a8c;
    --color-bg: #f5f6fa;
    --color-border: #e5e7eb;
    --color-text: #111827;
    --color-text-muted: #6b7280;
    --color-orange: #f97316;
    --font: 'Inter', sans-serif;
    --topbar-height: 56px;
    --shadow-sm: 0 1px 3px rgba(0,0,0,0.08);
    --radius: 10px;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html, body { font-family: var(--font); background: var(--color-bg); color: var(--color-text); font-size: 14px; }
.topnav {
    position: fixed; top: 0; left: 0; right: 0; height: var(--topbar-height);
    background: var(--color-primary); border-bottom: 1px solid var(--color-primary-light);
    display: flex; align-items: center; padding: 0 20px; gap: 8px; z-index: 100;
}
.topnav__logo { display: flex; align-items: center; gap: 8px; margin-right: 12px; }
.topnav__logo img { height: 30px; width: auto; }
.topnav__logo-text { font-size: 15px; font-weight: 700; color: white; }
.topnav__badge-admin {
    background: var(--color-orange); color: white; font-size: 10px; font-weight: 700;
    padding: 2px 8px; border-radius: 20px; margin-left: 4px;
}
.topnav__nav { display: flex; align-items: center; gap: 4px; flex: 1; }
.topnav__link {
    display: flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px;
    font-size: 13px; font-weight: 500; color: rgba(255,255,255,0.75);
    text-decoration: none; transition: background .15s, color .15s; white-space: nowrap;
}
.topnav__link:hover { background: rgba(255,255,255,0.1); color: white; }
.topnav__link--active { background: rgba(255,255,255,0.15); color: white; }
.topnav__actions { display: flex; align-items: center; gap: 8px; margin-left: auto; }
.topnav__icon-btn {
    position: relative; width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    background: none; border: none; cursor: pointer; color: rgba(255,255,255,0.75);
    text-decoration: none; transition: background .15s;
}
.topnav__icon-btn:hover { background: rgba(255,255,255,0.1); color: white; }
.layout { padding-top: var(--topbar-height); min-height: 100vh; }
@yield('extra-styles')
</style>
@yield('head-styles')
</head>
<body>
<nav class="topnav">
    <div class="topnav__logo">
        <img src="/assets/nupostlogo.png" alt="NUPost"
             onerror="this.style.display='none';">
        <span class="topnav__logo-text">NUPost</span>
        <span class="topnav__badge-admin">ADMIN</span>
    </div>
    <div class="topnav__nav">
        <a href="{{ route('admin.dashboard') }}"
           class="topnav__link {{ request()->routeIs('admin.dashboard') ? 'topnav__link--active' : '' }}">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg>
            Dashboard
        </a>
        <a href="{{ route('admin.requests') }}"
           class="topnav__link {{ request()->routeIs('admin.requests*') ? 'topnav__link--active' : '' }}">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Requests
        </a>
    </div>
    <div class="topnav__actions">
        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
            @csrf
            <button type="submit" class="topnav__icon-btn" title="Logout">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            </button>
        </form>
    </div>
</nav>
<div class="layout">
    @yield('content')
</div>
@yield('scripts')
</body>
</html>