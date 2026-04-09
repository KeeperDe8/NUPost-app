@extends('layouts.admin')

@section('title', 'Dashboard')

@section('head-styles')
<style>
.main { max-width: 1000px; margin: 0 auto; padding: 32px 24px; }
.page-header { margin-bottom: 24px; }
.page-header h1 { font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
.page-header p { font-size: 13px; color: var(--color-text-muted); margin-top: 3px; }
.stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px; }
.stat-card {
    background: white; border-radius: var(--radius); padding: 20px;
    display: flex; align-items: center; justify-content: space-between;
    box-shadow: var(--shadow-sm); border: 1.5px solid transparent;
}
.stat-card--yellow { border-color: #fde68a; }
.stat-card--blue   { border-color: #bfdbfe; }
.stat-card--green  { border-color: #a7f3d0; }
.stat-card--purple { border-color: #ddd6fe; }
.stat-card--red    { border-color: #fecaca; }
.stat-card--gray   { border-color: #e5e7eb; }
.stat-card__label { font-size: 11px; font-weight: 500; color: var(--color-text-muted); margin-bottom: 6px; }
.stat-card__value { font-size: 28px; font-weight: 700; }
.stat-card__icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.stat-card__icon--yellow { background: #fef3c7; color: #f59e0b; }
.stat-card__icon--blue   { background: #eff6ff; color: #3b82f6; }
.stat-card__icon--green  { background: #ecfdf5; color: #10b981; }
.stat-card__icon--purple { background: #f5f3ff; color: #8b5cf6; }
.stat-card__icon--red    { background: #fee2e2; color: #ef4444; }
.stat-card__icon--gray   { background: #f3f4f6; color: #6b7280; }
.card { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
.card__header { padding: 18px 20px 14px; border-bottom: 1px solid var(--color-border); display: flex; align-items: center; justify-content: space-between; }
.card__title { font-size: 15px; font-weight: 600; }
.card__link { font-size: 12.5px; color: var(--color-primary); text-decoration: none; font-weight: 500; }
.card__link:hover { text-decoration: underline; }
.requests-table { width: 100%; border-collapse: collapse; }
.requests-table thead tr { border-bottom: 1px solid var(--color-border); }
.requests-table th { padding: 10px 16px; text-align: left; font-size: 11px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
.requests-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .1s; }
.requests-table tbody tr:last-child { border-bottom: none; }
.requests-table tbody tr:hover { background: #fafafa; }
.requests-table td { padding: 12px 16px; font-size: 13px; vertical-align: middle; }
.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 500; white-space: nowrap; }
.badge--approved     { background: #dcfce7; color: #16a34a; }
.badge--posted       { background: #dbeafe; color: #2563eb; }
.badge--under-review { background: #fef3c7; color: #d97706; }
.badge--pending      { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
.badge--rejected     { background: #fee2e2; color: #dc2626; }
.badge--high         { background: #fee2e2; color: #dc2626; }
.badge--urgent       { background: #fef3c7; color: #b45309; }
.badge--medium       { background: #fef3c7; color: #d97706; }
.badge--low          { background: #f3f4f6; color: #6b7280; }
.req-title { font-weight: 600; color: var(--color-text); }
.req-requester { font-size: 12px; color: var(--color-text-muted); margin-top: 2px; }
@media (max-width: 768px) { .stats { grid-template-columns: repeat(2, 1fr); } }
</style>
@endsection

@section('content')
<main class="main">
    <div class="page-header">
        <h1>Admin Dashboard</h1>
        <p>Overview of all post requests and system activity</p>
    </div>

    <div class="stats">
        <div class="stat-card stat-card--gray">
            <div>
                <div class="stat-card__label">Total Requests</div>
                <div class="stat-card__value">{{ $total }}</div>
            </div>
            <div class="stat-card__icon stat-card__icon--gray">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
        </div>
        <div class="stat-card stat-card--yellow">
            <div>
                <div class="stat-card__label">Pending Review</div>
                <div class="stat-card__value">{{ $pending }}</div>
            </div>
            <div class="stat-card__icon stat-card__icon--yellow">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <div class="stat-card stat-card--blue">
            <div>
                <div class="stat-card__label">Under Review</div>
                <div class="stat-card__value">{{ $review }}</div>
            </div>
            <div class="stat-card__icon stat-card__icon--blue">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
        </div>
        <div class="stat-card stat-card--green">
            <div>
                <div class="stat-card__label">Approved</div>
                <div class="stat-card__value">{{ $approved }}</div>
            </div>
            <div class="stat-card__icon stat-card__icon--green">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <div class="stat-card stat-card--purple">
            <div>
                <div class="stat-card__label">Posted</div>
                <div class="stat-card__value">{{ $posted }}</div>
            </div>
            <div class="stat-card__icon stat-card__icon--purple">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </div>
        </div>
        <div class="stat-card stat-card--red">
            <div>
                <div class="stat-card__label">Rejected</div>
                <div class="stat-card__value">{{ $rejected }}</div>
            </div>
            <div class="stat-card__icon stat-card__icon--red">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
        </div>
        <div class="stat-card stat-card--blue" style="grid-column: span 2;">
            <div>
                <div class="stat-card__label">Total Users</div>
                <div class="stat-card__value">{{ $users }}</div>
            </div>
            <div class="stat-card__icon stat-card__icon--blue">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card__header">
            <div class="card__title">Recent Requests</div>
            <a href="{{ route('admin.requests') }}" class="card__link">View all →</a>
        </div>
        <table class="requests-table">
            <thead>
                <tr>
                    <th>REQUEST</th>
                    <th>REQUESTER</th>
                    <th>CATEGORY</th>
                    <th>PRIORITY</th>
                    <th>STATUS</th>
                    <th>DATE</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent as $req)
                    @php
                        $status_raw = strtolower($req->status);
                        $status_class = match(true) {
                            str_contains($status_raw, 'approved')     => 'approved',
                            str_contains($status_raw, 'posted')       => 'posted',
                            str_contains($status_raw, 'under review') => 'under-review',
                            str_contains($status_raw, 'rejected')     => 'rejected',
                            default                                   => 'pending',
                        };
                        $priority_class = match(strtolower($req->priority ?? '')) {
                            'urgent' => 'urgent', 'high' => 'high', 'medium' => 'medium', default => 'low'
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="req-title">{{ Str::limit($req->title, 40) }}</div>
                        </td>
                        <td>{{ $req->requester }}</td>
                        <td>{{ $req->category }}</td>
                        <td><span class="badge badge--{{ $priority_class }}">{{ strtoupper($req->priority) }}</span></td>
                        <td><span class="badge badge--{{ $status_class }}">{{ $req->status }}</span></td>
                        <td>{{ $req->created_at->format('M j, Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:40px;text-align:center;color:#9ca3af;">No requests yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</main>
@endsection