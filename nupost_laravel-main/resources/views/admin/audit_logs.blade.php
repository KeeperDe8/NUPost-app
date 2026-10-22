@extends('layouts.admin')

@section('title', 'Activity History')

@section('head-styles')
<style>
.audit-page-outer {
    min-height: calc(100vh - 64px);
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 36px 24px 60px;
}
.audit-page-inner {
    width: 100%;
    max-width: 1060px;
}
.audit-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}
.audit-header__left h1 {
    font-size: 26px;
    font-weight: 800;
    color: var(--ink, #0f172a);
    margin: 0;
    letter-spacing: -0.4px;
}
.audit-header__left p {
    font-size: 13.5px;
    color: var(--ink-soft, #64748b);
    margin-top: 4px;
}

/* Stats Row */
.audit-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
    margin-bottom: 24px;
}
.stat-box {
    background: var(--card, #ffffff);
    border-radius: 16px;
    border: 1px solid rgba(0,0,0,0.08);
    padding: 16px 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
[data-theme="dark"] .stat-box {
    background: #0d1526;
    border-color: rgba(255,255,255,0.08);
}
.stat-box-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--ink-soft, #64748b);
    margin-bottom: 6px;
}
.stat-box-val {
    font-size: 22px;
    font-weight: 800;
    color: var(--ink, #0f172a);
}
[data-theme="dark"] .stat-box-val {
    color: #f8fafc;
}

/* Filters & Search */
.audit-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
    flex-wrap: wrap;
}
.filter-tabs {
    display: flex;
    gap: 6px;
}
.filter-tab {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    border: 1.5px solid rgba(0,0,0,0.08);
    transition: all .15s;
    background: var(--card, #ffffff);
    color: var(--ink-soft, #64748b);
    text-decoration: none;
}
[data-theme="dark"] .filter-tab {
    background: #0d1526;
    border-color: rgba(255,255,255,0.08);
    color: #94a3b8;
}
.filter-tab.active {
    background: #002366;
    color: #ffffff;
    border-color: #002366;
}
[data-theme="dark"] .filter-tab.active {
    background: #1e4fd8;
    border-color: #1e4fd8;
    color: #ffffff;
}
.search-form {
    display: flex;
    align-items: center;
    gap: 8px;
}
.search-input {
    padding: 8px 14px;
    border-radius: 12px;
    border: 1px solid rgba(0,0,0,0.12);
    font-size: 13px;
    background: var(--card, #ffffff);
    color: var(--ink, #0f172a);
    outline: none;
    min-width: 220px;
}
[data-theme="dark"] .search-input {
    background: #0d1526;
    border-color: rgba(255,255,255,0.1);
    color: #f8fafc;
}
.btn-search {
    padding: 8px 14px;
    border-radius: 12px;
    background: #002366;
    color: white;
    border: none;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
}

/* Card & Table */
.audit-card {
    background: var(--card, #ffffff);
    border-radius: 20px;
    border: 1px solid rgba(0,0,0,0.08);
    box-shadow: 0 4px 16px rgba(0,0,0,0.04);
    overflow: hidden;
}
[data-theme="dark"] .audit-card {
    background: #0d1526;
    border-color: rgba(255,255,255,0.08);
}
.audit-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.audit-table th {
    text-align: left;
    padding: 14px 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--ink-soft, #64748b);
    border-bottom: 1px solid rgba(0,0,0,0.06);
    background: rgba(0,0,0,0.02);
}
[data-theme="dark"] .audit-table th {
    background: rgba(255,255,255,0.02);
    border-bottom-color: rgba(255,255,255,0.06);
    color: #94a3b8;
}
.audit-table td {
    padding: 16px 20px;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    color: var(--ink, #0f172a);
    vertical-align: middle;
}
[data-theme="dark"] .audit-table td {
    border-bottom-color: rgba(255,255,255,0.04);
    color: #cbd5e1;
}
.audit-table tr:hover td {
    background: rgba(0,0,0,0.015);
}
[data-theme="dark"] .audit-table tr:hover td {
    background: rgba(255,255,255,0.02);
}

/* Badges */
.badge-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
}
.badge-login { background: rgba(59, 130, 246, 0.12); color: #2563eb; }
.badge-approved { background: rgba(16, 185, 129, 0.12); color: #059669; }
.badge-rejected { background: rgba(239, 68, 68, 0.12); color: #dc2626; }
.badge-created { background: rgba(245, 158, 11, 0.12); color: #d97706; }
.badge-settings { background: rgba(139, 92, 246, 0.12); color: #7c3aed; }

[data-theme="dark"] .badge-login { background: rgba(59, 130, 246, 0.2); color: #93c5fd; }
[data-theme="dark"] .badge-approved { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; }
[data-theme="dark"] .badge-rejected { background: rgba(239, 68, 68, 0.2); color: #fca5a5; }
[data-theme="dark"] .badge-created { background: rgba(245, 158, 11, 0.2); color: #fcd34d; }
[data-theme="dark"] .badge-settings { background: rgba(139, 92, 246, 0.2); color: #c4b5fd; }

.actor-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #002366;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 11px;
}
.actor-role {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    padding: 2px 6px;
    border-radius: 6px;
    background: rgba(0,0,0,0.06);
    color: var(--ink-soft, #64748b);
}
[data-theme="dark"] .actor-role {
    background: rgba(255,255,255,0.08);
    color: #94a3b8;
}
</style>
@endsection

@section('content')
<div class="audit-page-outer">
    <div class="audit-page-inner">

        {{-- HEADER --}}
        <div class="audit-header">
            <div class="audit-header__left">
                <h1>Activity History</h1>
                <p>A clear timeline of user actions, logins, and request updates across NUPost.</p>
            </div>
        </div>

        {{-- STATS ROW --}}
        <div class="audit-stats-grid">
            <div class="stat-box">
                <div class="stat-box-label">Total Activities</div>
                <div class="stat-box-val">{{ number_format($stats['total'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-label">Approvals</div>
                <div class="stat-box-val" style="color:#059669;">{{ number_format($stats['approvals'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-label">Rejections</div>
                <div class="stat-box-val" style="color:#dc2626;">{{ number_format($stats['rejections'] ?? 0) }}</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-label">Sign-ins</div>
                <div class="stat-box-val" style="color:#2563eb;">{{ number_format($stats['logins'] ?? 0) }}</div>
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

        {{-- TABLE CARD --}}
        <div class="audit-card">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th style="width: 220px;">Date & Time</th>
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
                            } elseif (str_contains($action, 'api_key') || str_contains($action, 'settings')) {
                                $badgeClass = 'badge-settings';
                            }

                            $user = $log->user;
                            $name = $user ? $user->name : 'System User';
                            $email = $user ? $user->email : 'N/A';
                            $role = $user ? ucfirst($user->role ?? 'user') : 'System';
                            $initials = strtoupper(substr($name, 0, 2));
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight:700; color:var(--ink, #0f172a);">
                                    {{ $log->created_at ? $log->created_at->format('M d, Y • h:i A') : 'N/A' }}
                                </div>
                                <div style="font-size:11px; color:var(--ink-soft, #64748b); margin-top:2px;">
                                    {{ $log->created_at ? $log->created_at->diffForHumans() : '' }}
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
                            <td colspan="4" style="text-align:center; padding:48px 20px; color:var(--ink-soft, #64748b);">
                                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:8px; opacity:0.5;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                <div>No activity records found matching your query.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($logs->hasPages())
                <div style="padding: 16px 20px; border-top: 1px solid rgba(0,0,0,0.06);">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
