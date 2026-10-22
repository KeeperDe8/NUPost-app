@extends('layouts.admin')

@section('title', 'Activity History')

@section('head-styles')
<style>
.page-hd { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.page-hd__title { font-family:var(--font-disp, 'DM Serif Display', serif); font-size:24px; font-weight:800; color:var(--ink, #0f172a); }
.page-hd__sub   { font-size:13px; color:var(--ink-soft, #64748b); margin-top:3px; }

/* STAT CARDS — Navy Blue Style matching Request Management */
.stats-row { display:grid; grid-template-columns:repeat(auto-fit, minmax(210px, 1fr)); gap:14px; margin-bottom:22px; }
.scard {
    background:linear-gradient(135deg,#001a4d 0%,#002366 60%,#002e7a 100%);
    border-radius:var(--radius, 16px); padding:18px 20px;
    display:flex; align-items:center; gap:14px;
    box-shadow:0 4px 18px rgba(0,26,77,0.22);
    transition:transform .2s,box-shadow .2s;
    position:relative; overflow:hidden;
}
.scard::before {
    content:''; position:absolute; top:-30px; right:-30px;
    width:100px; height:100px; border-radius:50%;
    background:radial-gradient(circle,rgba(255,255,255,0.07) 0%,transparent 70%);
    pointer-events:none;
}
.scard:hover { transform:translateY(-3px); box-shadow:0 8px 28px rgba(0,26,77,0.3); }
.scard__icon { width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.scard__num  { font-size:26px; font-weight:800; letter-spacing:-0.8px; line-height:1; color:white; }
.scard__lbl  { font-size:11.5px; color:rgba(255,255,255,0.65); font-weight:500; margin-top:4px; text-transform:uppercase; letter-spacing:0.4px; }

.scard--blue .scard__icon { background:rgba(59,130,246,0.25); color:#93c5fd; }
.scard--blue::after { content:''; position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#3b82f6,#93c5fd); }
.scard--green .scard__icon { background:rgba(16,185,129,0.25); color:#6ee7b7; }
.scard--green::after { content:''; position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#10b981,#6ee7b7); }
.scard--red .scard__icon { background:rgba(239,68,68,0.25); color:#fca5a5; }
.scard--red::after { content:''; position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#ef4444,#fca5a5); }
.scard--yellow .scard__icon { background:rgba(245,158,11,0.25); color:#fbbf24; }
.scard--yellow::after { content:''; position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#f59e0b,#fbbf24); }

/* CONTROLS TOOLBAR */
.audit-controls {
    display:flex; align-items:center; justify-content:space-between;
    gap:12px; margin-bottom:18px; flex-wrap:wrap;
}
.filter-tabs { display:flex; gap:8px; flex-wrap:wrap; }
.filter-tab {
    padding:8px 16px; border-radius:20px; font-size:12.5px; font-weight:600;
    cursor:pointer; border:1px solid rgba(0,0,0,0.08); transition:all .15s;
    background:var(--card, #ffffff); color:var(--ink-soft, #64748b); text-decoration:none;
}
[data-theme="dark"] .filter-tab { background:#0d1526; border-color:rgba(255,255,255,0.08); color:#94a3b8; }
.filter-tab:hover { background:rgba(0,35,102,0.06); color:var(--ink, #0f172a); }
[data-theme="dark"] .filter-tab:hover { background:rgba(255,255,255,0.05); color:#ffffff; }
.filter-tab.active {
    background:#002366 !important; color:#ffffff !important;
    border-color:#002366 !important; box-shadow:0 2px 8px rgba(0,35,102,0.25);
}

.search-form { display:flex; align-items:center; gap:8px; }
.search-input {
    height:38px; padding:0 14px; border-radius:10px;
    border:1px solid rgba(0,0,0,0.1); font-size:12.5px;
    background:var(--card, #ffffff); color:var(--ink, #0f172a);
    outline:none; min-width:230px;
}
[data-theme="dark"] .search-input { background:#0d1526; border-color:rgba(255,255,255,0.1); color:#f8fafc; }
.btn-search {
    height:38px; padding:0 16px; border-radius:10px;
    background:#002366; color:white; border:none;
    font-size:12.5px; font-weight:700; cursor:pointer;
    transition:background .15s;
}
.btn-search:hover { background:#001a4d; }

/* TABLE PANEL */
.table-panel {
    background:var(--card, #ffffff); border-radius:var(--radius, 16px);
    border:1px solid rgba(0,0,0,0.06); box-shadow:0 4px 20px rgba(0,0,0,0.04);
    overflow:hidden; width:100%;
}
[data-theme="dark"] .table-panel {
    background:#0d1526; border-color:rgba(255,255,255,0.08);
    box-shadow:0 4px 24px rgba(0,0,0,0.3);
}
.table-panel__head {
    padding:16px 22px; border-bottom:1px solid rgba(0,0,0,0.05);
    display:flex; align-items:center; justify-content:space-between;
}
[data-theme="dark"] .table-panel__head { border-bottom-color:rgba(255,255,255,0.06); }
.table-panel__title { font-size:16px; font-weight:700; color:var(--ink, #0f172a); }
[data-theme="dark"] .table-panel__title { color:#ffffff; }
.table-panel__count { font-size:12.5px; color:var(--ink-soft, #64748b); }

table { width:100%; border-collapse:collapse; }
thead tr { border-bottom:1px solid rgba(0,0,0,0.05); background:rgba(0,0,0,0.02); }
[data-theme="dark"] thead tr { border-bottom-color:rgba(255,255,255,0.06); background:rgba(255,255,255,0.02); }
th {
    padding:12px 18px; text-align:left; font-size:10.5px;
    font-weight:700; color:var(--ink-soft, #64748b);
    text-transform:uppercase; letter-spacing:0.7px; white-space:nowrap;
}
tbody tr { border-bottom:1px solid rgba(0,0,0,0.04); transition:background .1s; }
[data-theme="dark"] tbody tr { border-bottom-color:rgba(255,255,255,0.04); }
tbody tr:last-child { border-bottom:none; }
tbody tr:hover { background:rgba(0,0,0,0.015); }
[data-theme="dark"] tbody tr:hover { background:rgba(255,255,255,0.02); }
td { padding:14px 18px; font-size:13px; vertical-align:middle; color:var(--ink, #0f172a); }
[data-theme="dark"] td { color:#cbd5e1; }

.actor-avatar {
    width:34px; height:34px; border-radius:50%;
    background:#002366; color:white;
    font-size:11px; font-weight:800;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0;
}
.actor-role {
    font-size:10.5px; font-weight:700; padding:3px 9px;
    border-radius:6px; background:rgba(0,0,0,0.05);
    color:var(--ink-soft, #64748b); text-transform:uppercase;
}
[data-theme="dark"] .actor-role { background:rgba(255,255,255,0.06); color:#94a3b8; }

/* ACTION BADGES */
.badge-action {
    display:inline-flex; align-items:center; gap:6px;
    padding:4px 11px; border-radius:999px; font-size:11.5px; font-weight:700;
}
.badge-approved { background:#ecfdf5; color:#059669; border:1px solid #a7f3d0; }
.badge-rejected { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
.badge-login    { background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; }
.badge-created  { background:#fffbeb; color:#d97706; border:1px solid #fde68a; }
.badge-settings { background:#faf5ff; color:#7c3aed; border:1px solid #ddd6fe; }

[data-theme="dark"] .badge-approved { background:rgba(16,185,129,0.15); color:#34d399; border-color:rgba(16,185,129,0.3); }
[data-theme="dark"] .badge-rejected { background:rgba(239,68,68,0.15); color:#f87171; border-color:rgba(239,68,68,0.3); }
[data-theme="dark"] .badge-login    { background:rgba(59,130,246,0.15); color:#60a5fa; border-color:rgba(59,130,246,0.3); }
[data-theme="dark"] .badge-created  { background:rgba(245,158,11,0.15); color:#fbbf24; border-color:rgba(245,158,11,0.3); }
[data-theme="dark"] .badge-settings { background:rgba(139,92,246,0.15); color:#c4b5fd; border-color:rgba(139,92,246,0.3); }

/* NATIVE PAGINATION BAR — matching requests.blade.php */
.pagination-wrap {
    display:flex; align-items:center; justify-content:space-between;
    padding:14px 22px; border-top:1px solid rgba(0,0,0,0.05);
    background:rgba(0,0,0,0.015); flex-wrap:wrap; gap:12px;
}
[data-theme="dark"] .pagination-wrap {
    border-top-color:rgba(255,255,255,0.06); background:rgba(255,255,255,0.015);
}
.pagination-info { font-size:12.5px; color:var(--ink-soft, #64748b); }
.pagination-info strong { color:var(--ink, #0f172a); }
[data-theme="dark"] .pagination-info strong { color:#ffffff; }

.pagination-btns { display:flex; align-items:center; gap:5px; }
.pg-btn {
    min-width:34px; height:34px; padding:0 10px; border-radius:8px;
    border:1px solid rgba(0,0,0,0.08); background:var(--card, #ffffff);
    font-size:12.5px; font-weight:600; color:var(--ink-soft, #64748b);
    cursor:pointer; text-decoration:none;
    display:inline-flex; align-items:center; justify-content:center;
    transition:all .15s;
}
[data-theme="dark"] .pg-btn {
    background:#0d1526; border-color:rgba(255,255,255,0.08); color:#94a3b8;
}
.pg-btn svg { width:13px !important; height:13px !important; max-width:13px !important; max-height:13px !important; display:inline-block; }
.pg-btn:hover { background:rgba(0,35,102,0.08); border-color:#002366; color:#002366; }
[data-theme="dark"] .pg-btn:hover { background:rgba(255,255,255,0.08); border-color:#60a5fa; color:#ffffff; }
.pg-btn--active { background:#002366 !important; border-color:#002366 !important; color:white !important; font-weight:700; }
[data-theme="dark"] .pg-btn--active { background:#1e40af !important; border-color:#1e40af !important; color:white !important; }
.pg-btn--disabled { opacity:0.35; pointer-events:none; cursor:default; }
</style>
@endsection

@section('content')
<div>
    {{-- PAGE HEADER --}}
    <div class="page-hd">
        <div>
            <div class="page-hd__title">Activity History</div>
            <div class="page-hd__sub">A clear timeline of administrative reviews, authentications, and system actions across NUPost.</div>
        </div>
    </div>

    {{-- STATS ROW --}}
    <div class="stats-row">
        <div class="scard scard--blue">
            <div class="scard__icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3"/><circle cx="12" cy="12" r="9"/></svg>
            </div>
            <div>
                <div class="scard__num">{{ number_format($stats['total'] ?? 0) }}</div>
                <div class="scard__lbl">Total Activities</div>
            </div>
        </div>
        <div class="scard scard--green">
            <div class="scard__icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div>
                <div class="scard__num">{{ number_format($stats['approvals'] ?? 0) }}</div>
                <div class="scard__lbl">Approvals</div>
            </div>
        </div>
        <div class="scard scard--red">
            <div class="scard__icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
            <div>
                <div class="scard__num">{{ number_format($stats['rejections'] ?? 0) }}</div>
                <div class="scard__lbl">Rejections</div>
            </div>
        </div>
        <div class="scard scard--yellow">
            <div class="scard__icon">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
            </div>
            <div>
                <div class="scard__num">{{ number_format($stats['logins'] ?? 0) }}</div>
                <div class="scard__lbl">Sign-ins</div>
            </div>
        </div>
    </div>

    {{-- FILTERS & SEARCH --}}
    <div class="audit-controls">
        <div class="filter-tabs">
            <a href="{{ route('admin.audit-logs', ['category' => 'all', 'search' => $search]) }}" 
               class="filter-tab {{ $category === 'all' || !$category ? 'active' : '' }}">All Activities</a>
            <a href="{{ route('admin.audit-logs', ['category' => 'requests', 'search' => $search]) }}" 
               class="filter-tab {{ $category === 'requests' ? 'active' : '' }}">Post Requests</a>
            <a href="{{ route('admin.audit-logs', ['category' => 'auth', 'search' => $search]) }}" 
               class="filter-tab {{ $category === 'auth' ? 'active' : '' }}">Sign-ins</a>
            <a href="{{ route('admin.audit-logs', ['category' => 'system', 'search' => $search]) }}" 
               class="filter-tab {{ $category === 'system' ? 'active' : '' }}">System Updates</a>
        </div>

        <form action="{{ route('admin.audit-logs') }}" method="GET" class="search-form">
            <input type="hidden" name="category" value="{{ $category }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search user, action, or date..." class="search-input">
            <button type="submit" class="btn-search">Search</button>
        </form>
    </div>

    {{-- FULL WIDTH TABLE PANEL --}}
    <div class="table-panel">
        <div class="table-panel__head">
            <div class="table-panel__title">Activity Records</div>
            <div class="table-panel__count">Showing <strong>{{ $logs->count() }}</strong> of <strong>{{ number_format($logs->total()) }}</strong> events</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 230px;">Date & Time (PHT)</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Action Performed</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    @php
                        $action = $log->action_type;
                        $badgeClass = 'badge-created';
                        $label = ucwords(str_replace('_', ' ', $action));

                        if (str_contains($action, 'approved')) {
                            $badgeClass = 'badge-approved';
                            $label = 'Approved Post Request';
                        } elseif (str_contains($action, 'rejected')) {
                            $badgeClass = 'badge-rejected';
                            $label = 'Rejected Post Request';
                        } elseif (str_contains($action, 'login') || str_contains($action, 'logout')) {
                            $badgeClass = 'badge-login';
                            $label = str_contains($action, 'logout') ? 'User Signed Out' : 'User Signed In';
                        } elseif (str_contains($action, 'api_key') || str_contains($action, 'settings')) {
                            $badgeClass = 'badge-settings';
                            $label = 'Updated System Settings';
                        }

                        $user = $log->user;
                        $name = $user ? $user->name : 'System User';
                        $email = $user ? $user->email : 'N/A';
                        $role = $user ? ucfirst($user->role ?? 'user') : 'System';
                        $initials = strtoupper(substr($name, 0, 2));

                        // Ensure timestamp is accurately converted to Philippine Time (UTC+8)
                        $dt = $log->created_at ? \Carbon\Carbon::parse($log->created_at)->setTimezone('Asia/Manila') : null;
                    @endphp
                    <tr>
                        <td>
                            <div style="font-weight:700; color:var(--ink, #0f172a);">
                                {{ $dt ? $dt->format('M d, Y • h:i A') : 'N/A' }}
                            </div>
                            <div style="font-size:11px; color:var(--ink-soft, #64748b); margin-top:2px;">
                                {{ $dt ? $dt->diffForHumans() : '' }}
                            </div>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <div class="actor-avatar">{{ $initials }}</div>
                                <div>
                                    <div style="font-weight:700;">{{ $name }}</div>
                                    <div style="font-size:11px; color:var(--ink-soft, #64748b);">{{ $email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="actor-role">{{ $role }}</span>
                        </td>
                        <td>
                            <span class="badge-action {{ $badgeClass }}">
                                {{ $label }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align:center; padding:56px 20px; color:var(--ink-soft, #64748b);">
                            <svg width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:8px; opacity:0.4;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            <div>No activity records found matching your query.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- NATIVE PAGINATION BAR --}}
        @if($logs->hasPages())
        <div class="pagination-wrap">
            <div class="pagination-info">
                Page <strong>{{ $logs->currentPage() }}</strong> of <strong>{{ $logs->lastPage() }}</strong> ({{ number_format($logs->total()) }} total records)
            </div>
            <div class="pagination-btns">
                {{-- Prev --}}
                @if($logs->onFirstPage())
                    <span class="pg-btn pg-btn--disabled">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    </span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}" class="pg-btn">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    </a>
                @endif

                {{-- Page numbers --}}
                @php
                    $start = max(1, $logs->currentPage() - 2);
                    $end   = min($logs->lastPage(), $logs->currentPage() + 2);
                @endphp
                @if($start > 1)
                    <a href="{{ $logs->url(1) }}" class="pg-btn">1</a>
                    @if($start > 2)<span class="pg-btn pg-btn--disabled">…</span>@endif
                @endif
                @for($p = $start; $p <= $end; $p++)
                    <a href="{{ $logs->url($p) }}" class="pg-btn {{ $p === $logs->currentPage() ? 'pg-btn--active' : '' }}">{{ $p }}</a>
                @endfor
                @if($end < $logs->lastPage())
                    @if($end < $logs->lastPage() - 1)<span class="pg-btn pg-btn--disabled">…</span>@endif
                    <a href="{{ $logs->url($logs->lastPage()) }}" class="pg-btn">{{ $logs->lastPage() }}</a>
                @endif

                {{-- Next --}}
                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}" class="pg-btn">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                @else
                    <span class="pg-btn pg-btn--disabled">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    </span>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
