@extends('layouts.requestor')

@section('title', 'Dashboard')

@section('head-styles')
<style>
.main { max-width: 900px; margin: 0 auto; padding: 32px 24px; }
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
.stat-card__label { font-size: 11px; font-weight: 500; color: var(--color-text-muted); margin-bottom: 6px; }
.stat-card__value { font-size: 28px; font-weight: 700; }
.stat-card__icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
.stat-card__icon--yellow { background: #fef3c7; color: #f59e0b; }
.stat-card__icon--blue   { background: #eff6ff; color: #3b82f6; }
.stat-card__icon--green  { background: #ecfdf5; color: #10b981; }
.stat-card__icon--purple { background: #f5f3ff; color: #8b5cf6; }
.card { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
.card__header { padding: 18px 20px 14px; border-bottom: 1px solid var(--color-border); }
.card__title { font-size: 15px; font-weight: 600; }
.request-list { list-style: none; }
.request-item { display: flex; align-items: flex-start; gap: 14px; padding: 16px 20px; border-bottom: 1px solid #f3f4f6; }
.request-item:last-child { border-bottom: none; }
.request-item:hover { background: #fafafa; }
.request-item__info { flex: 1; min-width: 0; }
.request-item__title-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 4px; }
.request-item__title { font-size: 13.5px; font-weight: 600; }
.request-item__desc { font-size: 12px; color: var(--color-text-muted); margin-bottom: 8px; }
.request-item__meta { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
.request-item__thumb { width: 68px; height: 68px; border-radius: 8px; object-fit: cover; flex-shrink: 0; }
.request-item__thumb-placeholder { width: 68px; height: 68px; border-radius: 8px; background: linear-gradient(135deg, #e5e7eb, #d1d5db); display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 20px; flex-shrink: 0; }
.badge { display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 20px; font-size: 11px; font-weight: 500; white-space: nowrap; }
.badge--approved     { background: #dcfce7; color: #16a34a; }
.badge--posted       { background: #dbeafe; color: #2563eb; }
.badge--under-review { background: #fef3c7; color: #d97706; }
.badge--pending      { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
.badge--rejected     { background: #fee2e2; color: #dc2626; }
.badge--high         { background: #fee2e2; color: #dc2626; }
.badge--urgent       { background: #fef3c7; color: #b45309; }
.badge--medium       { background: #fef3c7; color: #d97706; }
.badge--low          { background: #f3f4f6; color: #6b7280; }
.tag { display: inline-flex; padding: 2px 8px; border-radius: 4px; font-size: 11px; background: #f3f4f6; color: #374151; }
@media (max-width: 768px) { .stats { grid-template-columns: repeat(2, 1fr); } }
</style>
@endsection

@section('content')
<main class="main">
    <div class="page-header">
        <h1>Dashboard</h1>
        <p>Overview of your post requests and performance</p>
    </div>

    <div class="stats">
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
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
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
    </div>

    <div class="card">
        <div class="card__header">
            <div class="card__title">Recent Requests</div>
        </div>
        <ul class="request-list">
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
                    $thumb = $req->first_media;
                @endphp
                <li class="request-item">
                    <div class="request-item__info">
                        <div class="request-item__title-row">
                            <span class="request-item__title">{{ $req->title }}</span>
                            <span class="badge badge--{{ $status_class }}">{{ $req->status }}</span>
                        </div>
                        <p class="request-item__desc">{{ Str::limit($req->description, 120) }}</p>
                        <div class="request-item__meta">
                            <span class="tag">{{ $req->category }}</span>
                            <span class="badge badge--{{ $priority_class }}">{{ strtoupper($req->priority) }}</span>
                            @if($req->platform)
                                <span class="tag">{{ $req->platform }}</span>
                            @endif
                        </div>
                    </div>
                    @if($thumb)
                        <img class="request-item__thumb" src="/uploads/{{ $thumb }}" alt=""
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="request-item__thumb-placeholder" style="display:none;">📄</div>
                    @else
                        <div class="request-item__thumb-placeholder">📄</div>
                    @endif
                </li>
            @empty
                <li style="padding:40px 20px;text-align:center;color:#9ca3af;font-size:13px;">
                    No requests yet. <a href="{{ route('requestor.requests.create') }}" style="color:var(--color-primary);font-weight:600;">Create one →</a>
                </li>
            @endforelse
        </ul>
    </div>
</main>
@endsection