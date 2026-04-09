@extends('layouts.requestor')

@section('title', 'My Requests')

@section('head-styles')
<style>
.main { max-width: 900px; margin: 0 auto; padding: 32px 24px; }
.page-header { margin-bottom: 20px; }
.page-header h1 { font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
.page-header p { font-size: 13px; color: var(--color-text-muted); margin-top: 3px; }
.search-banner { display: flex; align-items: center; justify-content: space-between; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px 14px; margin-bottom: 16px; font-size: 13px; color: #1e40af; }
.search-banner a { color: #2563eb; text-decoration: none; font-size: 12px; font-weight: 500; }
.card { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
.filter-bar { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-bottom: 1px solid var(--color-border); gap: 10px; flex-wrap: wrap; }
.filter-tabs { display: flex; align-items: center; gap: 4px; }
.filter-tab { padding: 5px 14px; border-radius: 6px; font-size: 12.5px; font-weight: 500; color: var(--color-text-muted); text-decoration: none; transition: background .15s; }
.filter-tab:hover { background: var(--color-bg); color: var(--color-text); }
.filter-tab--active { background: var(--color-primary); color: white; }
.view-toggle { display: flex; align-items: center; gap: 4px; }
.view-btn { width: 34px; height: 34px; border-radius: 8px; border: 1px solid var(--color-border); display: flex; align-items: center; justify-content: center; cursor: pointer; background: none; color: var(--color-text-muted); transition: all .15s; }
.view-btn:hover { background: var(--color-bg); }
.view-btn--active { background: var(--color-primary); color: white; border-color: var(--color-primary); }
.count-text { padding: 10px 16px 6px; font-size: 11.5px; color: var(--color-text-muted); }
.count-text span { font-weight: 600; color: var(--color-text); }
/* LIST VIEW */
.requests-table { width: 100%; border-collapse: collapse; }
.requests-table thead tr { border-bottom: 1px solid var(--color-border); }
.requests-table th { padding: 8px 12px; text-align: left; font-size: 10.5px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
.requests-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .1s; cursor: pointer; }
.requests-table tbody tr:last-child { border-bottom: none; }
.requests-table tbody tr:hover { background: #fafafa; }
.requests-table td { padding: 12px; vertical-align: middle; font-size: 12.5px; }
.req-col { display: flex; align-items: center; gap: 10px; }
.req-thumb { width: 48px; height: 36px; border-radius: 6px; object-fit: cover; flex-shrink: 0; }
.req-thumb-placeholder { width: 48px; height: 36px; border-radius: 6px; background: linear-gradient(135deg, #e5e7eb, #d1d5db); display: flex; align-items: center; justify-content: center; color: #9ca3af; font-size: 14px; flex-shrink: 0; }
.req-title { font-size: 12.5px; font-weight: 600; color: var(--color-text); }
.req-desc { font-size: 11px; color: var(--color-text-muted); margin-top: 2px; max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
/* GRID VIEW */
.requests-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; padding: 16px; }
.grid-card { background: white; border: 1px solid var(--color-border); border-radius: 10px; overflow: hidden; transition: box-shadow .15s, transform .15s; }
.grid-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); transform: translateY(-2px); }
.grid-card__thumb { width: 100%; height: 140px; object-fit: cover; display: block; }
.grid-card__thumb-placeholder { width: 100%; height: 140px; background: linear-gradient(135deg, #e5e7eb, #d1d5db); display: flex; align-items: center; justify-content: center; font-size: 32px; color: #9ca3af; }
.grid-card__body { padding: 12px 14px; }
.grid-card__title { font-size: 13px; font-weight: 600; margin-bottom: 6px; line-height: 1.4; }
.grid-card__desc { font-size: 11.5px; color: var(--color-text-muted); line-height: 1.5; margin-bottom: 10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.grid-card__footer { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px; }
.grid-card__date { font-size: 10.5px; color: #9ca3af; margin-top: 8px; }
/* BADGES */
.badge { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 10.5px; font-weight: 600; white-space: nowrap; }
.badge--high         { background: #fee2e2; color: #dc2626; }
.badge--urgent       { background: #fef3c7; color: #b45309; }
.badge--medium       { background: #fef3c7; color: #d97706; }
.badge--low          { background: #f3f4f6; color: #6b7280; }
.status-badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 20px; font-size: 10.5px; font-weight: 500; white-space: nowrap; }
.status-badge--approved     { background: #dcfce7; color: #16a34a; }
.status-badge--posted       { background: #dbeafe; color: #2563eb; }
.status-badge--under-review { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
.status-badge--pending      { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
.status-badge--rejected     { background: #fee2e2; color: #dc2626; }
.date-text { font-size: 11.5px; color: var(--color-text-muted); white-space: nowrap; }
.empty-state { padding: 50px 20px; text-align: center; color: #9ca3af; font-size: 13px; display: flex; flex-direction: column; align-items: center; gap: 10px; }
@media (max-width: 768px) { .requests-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 480px) { .requests-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<main class="main">
    <div class="page-header">
        <h1>My Requests</h1>
        <p>View and manage all your post requests</p>
    </div>

    @if($search !== '')
    <div class="search-banner">
        <span>Showing results for <strong>"{{ $search }}"</strong> — {{ $total }} result{{ $total !== 1 ? 's' : '' }} found</span>
        <a href="{{ route('requestor.requests', ['filter' => $filter]) }}">✕ Clear search</a>
    </div>
    @endif

    <div class="card">
        <div class="filter-bar">
            <div class="filter-tabs">
                <a href="{{ route('requestor.requests', ['filter' => 'all', 'search' => $search]) }}"      class="filter-tab {{ $filter === 'all'      ? 'filter-tab--active' : '' }}">All</a>
                <a href="{{ route('requestor.requests', ['filter' => 'pending', 'search' => $search]) }}"  class="filter-tab {{ $filter === 'pending'  ? 'filter-tab--active' : '' }}">Pending</a>
                <a href="{{ route('requestor.requests', ['filter' => 'seen', 'search' => $search]) }}"     class="filter-tab {{ $filter === 'seen'     ? 'filter-tab--active' : '' }}">Seen</a>
                <a href="{{ route('requestor.requests', ['filter' => 'approved', 'search' => $search]) }}" class="filter-tab {{ $filter === 'approved' ? 'filter-tab--active' : '' }}">Approved</a>
                <a href="{{ route('requestor.requests', ['filter' => 'posted', 'search' => $search]) }}"   class="filter-tab {{ $filter === 'posted'   ? 'filter-tab--active' : '' }}">Posted</a>
            </div>
            <div class="view-toggle">
                <button class="view-btn" id="btn-list" onclick="setView('list')" title="List view">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </button>
                <button class="view-btn" id="btn-grid" onclick="setView('grid')" title="Grid view">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                </button>
            </div>
        </div>

        <div class="count-text">Showing <span>{{ $total }}</span> request{{ $total !== 1 ? 's' : '' }}</div>

        @if($total > 0)
        <!-- LIST VIEW -->
        <div id="view-list">
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>REQUEST</th>
                        <th>CATEGORY</th>
                        <th>PRIORITY</th>
                        <th>STATUS</th>
                        <th>SUBMITTED</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($requests as $req)
                    @php
                        $status_raw   = strtolower($req->status);
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
                    <tr onclick="window.location='{{ route('requestor.requests.chat', $req->id) }}'">
                        <td>
                            <div class="req-col">
                                @if($thumb)
                                    <img class="req-thumb" src="/uploads/{{ $thumb }}" alt=""
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <div class="req-thumb-placeholder" style="display:none;">📄</div>
                                @else
                                    <div class="req-thumb-placeholder">📄</div>
                                @endif
                                <div>
                                    <div class="req-title">{{ $req->title }}</div>
                                    <div class="req-desc">{{ Str::limit($req->description, 80) }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $req->category }}</td>
                        <td><span class="badge badge--{{ $priority_class }}">{{ strtoupper($req->priority) }}</span></td>
                        <td><span class="status-badge status-badge--{{ $status_class }}">{{ $req->status }}</span></td>
                        <td class="date-text">{{ $req->created_at->format('n/j/Y') }}</td>
                        <td>
                            <a href="{{ route('requestor.requests.chat', $req->id) }}"
                               onclick="event.stopPropagation();"
                               style="font-size:11.5px;color:var(--color-primary);font-weight:600;text-decoration:none;">
                                Chat →
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <!-- GRID VIEW -->
        <div id="view-grid" style="display:none;">
            <div class="requests-grid">
            @foreach($requests as $req)
                @php
                    $status_raw   = strtolower($req->status);
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
                <div class="grid-card" onclick="window.location='{{ route('requestor.requests.chat', $req->id) }}'">
                    @if($thumb)
                        <img class="grid-card__thumb" src="/uploads/{{ $thumb }}" alt=""
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="grid-card__thumb-placeholder" style="display:none;">📄</div>
                    @else
                        <div class="grid-card__thumb-placeholder">📄</div>
                    @endif
                    <div class="grid-card__body">
                        <div class="grid-card__title">{{ $req->title }}</div>
                        <div class="grid-card__desc">{{ $req->description }}</div>
                        <div class="grid-card__footer">
                            <span class="status-badge status-badge--{{ $status_class }}">{{ $req->status }}</span>
                            <span class="badge badge--{{ $priority_class }}">{{ strtoupper($req->priority) }}</span>
                        </div>
                        <div class="grid-card__date">{{ $req->created_at->format('M j, Y') }}</div>
                    </div>
                </div>
            @endforeach
            </div>
        </div>

        @else
            <div class="empty-state">
                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                @if($search !== '')
                    <p>No results for "<strong>{{ $search }}</strong>"</p>
                    <a href="{{ route('requestor.requests') }}" style="color:var(--color-primary);font-size:12px;">Clear search</a>
                @else
                    <p>No requests yet. <a href="{{ route('requestor.requests.create') }}" style="color:var(--color-primary);font-weight:600;">Create one →</a></p>
                @endif
            </div>
        @endif
    </div>
</main>
@endsection

@section('scripts')
<script>
function setView(v) {
    const listEl  = document.getElementById('view-list');
    const gridEl  = document.getElementById('view-grid');
    const btnList = document.getElementById('btn-list');
    const btnGrid = document.getElementById('btn-grid');
    if (v === 'grid') {
        listEl.style.display = 'none'; gridEl.style.display = 'block';
        btnList.classList.remove('view-btn--active'); btnGrid.classList.add('view-btn--active');
    } else {
        listEl.style.display = 'block'; gridEl.style.display = 'none';
        btnGrid.classList.remove('view-btn--active'); btnList.classList.add('view-btn--active');
    }
    localStorage.setItem('nupost_view', v);
}
const savedView = localStorage.getItem('nupost_view') || 'list';
setView(savedView);
</script>
@endsection