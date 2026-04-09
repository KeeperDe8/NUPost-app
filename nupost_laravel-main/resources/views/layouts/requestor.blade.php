<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NUPost – @yield('title', 'Dashboard')</title>
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

/* TOPNAV */
.topnav {
    position: fixed; top: 0; left: 0; right: 0; height: var(--topbar-height);
    background: white; border-bottom: 1px solid var(--color-border);
    display: flex; align-items: center; padding: 0 20px; gap: 8px; z-index: 100;
}
.topnav__logo img { height: 32px; width: auto; }
.topnav__logo-text { font-size: 15px; font-weight: 700; color: var(--color-primary); display: none; }
.topnav__nav { display: flex; align-items: center; gap: 4px; flex: 1; }
.topnav__link {
    display: flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px;
    font-size: 13px; font-weight: 500; color: var(--color-text-muted);
    text-decoration: none; transition: background .15s, color .15s; white-space: nowrap;
}
.topnav__link:hover { background: var(--color-bg); color: var(--color-text); }
.topnav__link--active { background: var(--color-primary); color: white; }
.topnav__link--active:hover { background: var(--color-primary-light); color: white; }
.topnav__create {
    display: flex; align-items: center; gap: 6px; padding: 7px 16px;
    background: var(--color-orange); color: white; border: none; border-radius: 8px;
    font-size: 13px; font-weight: 600; cursor: pointer; text-decoration: none; white-space: nowrap;
}
.topnav__create:hover { opacity: .9; }
.topnav__search { flex: 1; max-width: 320px; position: relative; margin: 0 8px; }
.topnav__search form { display: flex; }
.topnav__search input {
    width: 100%; height: 36px; border: 1px solid var(--color-border); border-radius: 8px;
    padding: 0 12px 0 36px; font-size: 13px; font-family: var(--font); background: var(--color-bg); outline: none;
}
.topnav__search input::placeholder { color: #9ca3af; }
.topnav__search input:focus { border-color: var(--color-primary); background: white; }
.topnav__search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
.topnav__actions { display: flex; align-items: center; gap: 8px; margin-left: auto; }
.topnav__icon-btn {
    position: relative; width: 36px; height: 36px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    background: none; border: none; cursor: pointer; color: var(--color-text-muted);
    text-decoration: none; transition: background .15s;
}
.topnav__icon-btn:hover { background: var(--color-bg); }
.topnav__icon-btn--active { background: var(--color-primary); color: white; }
.topnav__icon-btn--active:hover { background: var(--color-primary-light); }
.topnav__badge {
    position: absolute; top: 4px; right: 4px; width: 16px; height: 16px;
    background: #ef4444; color: white; font-size: 9px; font-weight: 700;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
}

/* LAYOUT */
.layout { padding-top: var(--topbar-height); min-height: 100vh; }

@yield('extra-styles')

@media (max-width: 768px) {
    .topnav__search { display: none; }
}
</style>
@yield('head-styles')
</head>
<body>

<nav class="topnav">
    <div class="topnav__logo">
        <img src="/assets/nupostlogo.png" alt="NUPost"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
        <span class="topnav__logo-text">NUPost</span>
    </div>
    <div class="topnav__nav">
        <a href="{{ route('requestor.dashboard') }}"
           class="topnav__link {{ request()->routeIs('requestor.dashboard') ? 'topnav__link--active' : '' }}">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9.5L12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9.5z"/><path d="M9 21V12h6v9"/></svg>
            Dashboard
        </a>
        <a href="{{ route('requestor.requests') }}"
           class="topnav__link {{ request()->routeIs('requestor.requests') ? 'topnav__link--active' : '' }}">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Requests
        </a>
        <a href="{{ route('requestor.calendar') }}"
           class="topnav__link {{ request()->routeIs('requestor.calendar') ? 'topnav__link--active' : '' }}">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Calendar
        </a>
        <a href="{{ route('requestor.requests.create') }}" class="topnav__create">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Create Request
        </a>
    </div>
    <div class="topnav__search">
        <form method="GET" action="{{ route('requestor.requests') }}">
            <span class="topnav__search-icon">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </span>
            <input type="text" name="search" placeholder="Search requests..."
                   value="{{ request('search') }}">
        </form>
    </div>
    <div class="topnav__actions">
        <a href="{{ route('requestor.notifications') }}"
           class="topnav__icon-btn {{ request()->routeIs('requestor.notifications') ? 'topnav__icon-btn--active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            @if(($unread_count ?? 0) > 0)
                <span class="topnav__badge">{{ $unread_count > 9 ? '9+' : $unread_count }}</span>
            @endif
        </a>
        <a href="{{ route('requestor.profile') }}"
           class="topnav__icon-btn {{ request()->routeIs('requestor.profile') ? 'topnav__icon-btn--active' : '' }}">
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </a>
    </div>
</nav>

<div class="layout">
    @yield('content')
</div>

@yield('scripts')
</body>
</html>