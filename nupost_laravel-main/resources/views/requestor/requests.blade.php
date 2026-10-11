@extends('layouts.requestor')

@section('title', 'My Requests')
@section('page-title', 'My Requests')

@section('head-styles')
<style>
.main { padding: 24px 26px 36px; }

.page-header { margin-bottom: 20px; }
.page-header h1 { font-size: 22px; font-weight: 700; letter-spacing: -0.4px; color: var(--text); }
.page-header p  { font-size: 13px; color: var(--text-muted); margin-top: 3px; }

/* ALERTS */
.alert { padding: 12px 16px; border-radius: 10px; font-size: 13px; margin-bottom: 16px; font-weight: 500; }
.alert--success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.alert--error   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

/* ── MAIN CARD — dark navy ── */
.card {
    border-radius: 22px;
    overflow: hidden;
    background: linear-gradient(160deg, #001a4d 0%, #002366 55%, #003080 100%);
    box-shadow: 0 8px 32px rgba(0,26,77,0.35);
    border: none;
}

/* FILTER BAR */
.filter-bar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.1);
    gap: 10px; flex-wrap: wrap;
    background: rgba(0,0,0,0.15);
}
.filter-left { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.filter-tabs { display: flex; align-items: center; gap: 4px; }
.filter-tab {
    padding: 7px 16px; border-radius: 10px;
    font-size: 12.5px; font-weight: 500;
    color: rgba(255,255,255,0.45); text-decoration: none;
    transition: all .18s; border: 1px solid transparent;
}
.filter-tab:hover {
    background: rgba(255,255,255,0.08);
    color: rgba(255,255,255,0.85);
    border-color: rgba(255,255,255,0.12);
}
.filter-tab--active {
    background: rgba(255,255,255,0.15);
    color: white; font-weight: 700;
    border: 1px solid rgba(255,255,255,0.25);
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

/* ── INLINE SEARCH BOX ── */
.inline-search {
    display: flex; align-items: center; gap: 0;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.18);
    border-radius: 10px; overflow: hidden;
    transition: border-color .15s;
}
.inline-search:focus-within {
    border-color: rgba(147,197,253,0.5);
    background: rgba(255,255,255,0.12);
}
.inline-search__icon {
    padding: 0 10px; color: rgba(255,255,255,0.35);
    display: flex; align-items: center; flex-shrink: 0;
}
.inline-search__input {
    background: none; border: none; outline: none;
    font-size: 12.5px; font-family: var(--font);
    color: white; padding: 8px 0; width: 180px;
}
.inline-search__input::placeholder { color: rgba(255,255,255,0.3); }
.inline-search__clear {
    padding: 0 10px; color: rgba(255,255,255,0.3);
    cursor: pointer; display: none; align-items: center;
    background: none; border: none; font-size: 14px;
    transition: color .15s;
}
.inline-search__clear:hover { color: rgba(255,255,255,0.7); }
.inline-search__clear.visible { display: flex; }

.view-toggle { display: flex; align-items: center; gap: 4px; }
.view-btn {
    width: 32px; height: 32px; border-radius: 8px;
    border: 1px solid rgba(255,255,255,0.18);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.45);
    transition: all .15s;
}
.view-btn:hover { background: rgba(255,255,255,0.15); color: rgba(255,255,255,0.85); }
.view-btn--active { background: rgba(255,255,255,0.2); color: white; border-color: rgba(255,255,255,0.35); }

/* COUNT ROW */
.count-row {
    padding: 10px 20px 8px;
    font-size: 12px; color: rgba(255,255,255,0.4);
    border-bottom: 1px solid rgba(255,255,255,0.06);
    display: flex; align-items: center; justify-content: space-between;
}
.count-row span { font-weight: 700; color: rgba(255,255,255,0.7); }
.search-active-label {
    font-size: 11.5px; color: rgba(147,197,253,0.8);
    font-weight: 500;
}

/* TABLE */
.requests-table { width: 100%; border-collapse: collapse; }
.requests-table thead tr { border-bottom: 1px solid rgba(255,255,255,0.1); background: rgba(0,0,0,0.1); }
.requests-table th {
    padding: 10px 16px; text-align: left;
    font-size: 10px; font-weight: 700;
    color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.8px;
    white-space: nowrap;
}
.requests-table tbody tr {
    border-bottom: 1px solid rgba(255,255,255,0.05);
    transition: background .1s; cursor: pointer;
}
.requests-table tbody tr:last-child { border-bottom: none; }
.requests-table tbody tr:hover { background: rgba(255,255,255,0.06); }
.requests-table td { padding: 13px 16px; vertical-align: middle; font-size: 12.5px; }

/* Highlight match */
.highlight { background: rgba(147,197,253,0.35); border-radius: 3px; padding: 0 2px; color: white; }

.req-col { display: flex; align-items: center; gap: 10px; }
.req-thumb {
    width: 50px; height: 38px; border-radius: 8px;
    object-fit: cover; flex-shrink: 0; border: 1px solid rgba(255,255,255,0.15);
}
.req-thumb-placeholder {
    width: 50px; height: 38px; border-radius: 8px;
    background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12);
    display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.3); font-size: 16px; flex-shrink: 0;
}
.req-title { font-size: 13px; font-weight: 600; color: white; }
.req-desc  { font-size: 11px; color: rgba(255,255,255,0.45); margin-top: 2px; max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* BADGES */
.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 10.5px; font-weight: 700; white-space: nowrap; }
.badge--approved     { background: rgba(16,185,129,0.2);  color: #6ee7b7; border: 1px solid rgba(16,185,129,0.3); }
.badge--posted       { background: rgba(139,92,246,0.2);  color: #c4b5fd; border: 1px solid rgba(139,92,246,0.3); }
.badge--under-review { background: rgba(245,158,11,0.2);  color: #fcd34d; border: 1px solid rgba(245,158,11,0.3); }
.badge--pending      { background: rgba(148,163,184,0.15); color: #cbd5e1; border: 1px solid rgba(148,163,184,0.25); }
.badge--rejected     { background: rgba(239,68,68,0.2);   color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
.badge--high         { background: rgba(239,68,68,0.2);   color: #fca5a5; border: 1px solid rgba(239,68,68,0.3); }
.badge--urgent       { background: rgba(249,115,22,0.2);  color: #fdba74; border: 1px solid rgba(249,115,22,0.3); }
.badge--medium       { background: rgba(245,158,11,0.2);  color: #fcd34d; border: 1px solid rgba(245,158,11,0.3); }
.badge--low          { background: rgba(148,163,184,0.15); color: #cbd5e1; border: 1px solid rgba(148,163,184,0.25); }

/* ACTION BUTTONS */
.action-btns { display: flex; align-items: center; gap: 5px; }
.btn-chat {
    font-size: 11.5px; color: #93c5fd; font-weight: 600;
    text-decoration: none; padding: 5px 12px; border-radius: 7px;
    border: 1px solid rgba(147,197,253,0.35); background: rgba(59,110,245,0.15);
    transition: all .15s; white-space: nowrap;
}
.btn-chat:hover { background: rgba(59,110,245,0.35); color: white; border-color: rgba(147,197,253,0.5); }
.btn-edit {
    font-size: 11.5px; color: #fcd34d; font-weight: 600;
    text-decoration: none; padding: 5px 12px; border-radius: 7px;
    border: 1px solid rgba(245,158,11,0.35); background: rgba(245,158,11,0.12);
    transition: all .15s; white-space: nowrap;
}
.btn-edit:hover { background: rgba(245,158,11,0.3); color: white; }
.btn-delete {
    font-size: 11.5px; color: #fca5a5; font-weight: 600;
    padding: 5px 12px; border-radius: 7px;
    border: 1px solid rgba(239,68,68,0.35); background: rgba(239,68,68,0.12);
    cursor: pointer; font-family: var(--font); transition: all .15s; white-space: nowrap;
}
.btn-delete:hover { background: rgba(239,68,68,0.3); color: white; }
.date-text { font-size: 11.5px; color: rgba(255,255,255,0.35); white-space: nowrap; }

/* GRID VIEW */
.requests-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 12px; padding: 14px; }
.grid-card {
    background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);
    border-radius: 14px; overflow: hidden;
    transition: all .18s; cursor: pointer;
}
.grid-card:hover { background: rgba(255,255,255,0.1); border-color: rgba(255,255,255,0.22); transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.3); }
.grid-card__thumb { width: 100%; height: 130px; object-fit: cover; display: block; }
.grid-card__thumb-placeholder {
    width: 100%; height: 130px;
    background: rgba(255,255,255,0.04);
    display: flex; align-items: center; justify-content: center;
    font-size: 32px; color: rgba(255,255,255,0.2);
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.grid-card__body { padding: 12px 14px; }
.grid-card__title { font-size: 13px; font-weight: 700; margin-bottom: 5px; line-height: 1.4; color: white; }
.grid-card__desc {
    font-size: 11.5px; color: rgba(255,255,255,0.45); line-height: 1.5;
    margin-bottom: 10px;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.grid-card__footer { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 6px; }
.grid-card__actions { display: flex; align-items: center; gap: 5px; margin-top: 10px; flex-wrap: wrap; }
.grid-card__date { font-size: 10.5px; color: rgba(255,255,255,0.3); margin-top: 6px; }

/* EMPTY STATE */
.empty-state {
    padding: 56px 20px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 12px;
}
.empty-state__icon {
    width: 56px; height: 56px; border-radius: 16px;
    background: rgba(255,255,255,0.08); display: flex; align-items: center; justify-content: center;
    color: rgba(255,255,255,0.3);
}
.empty-state p { font-size: 13.5px; color: rgba(255,255,255,0.45); }
.empty-state a { color: #93c5fd; font-weight: 700; text-decoration: none; }

/* PAGINATION */
.pagination-wrap {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px; border-top: 1px solid rgba(255,255,255,0.08);
    background: rgba(0,0,0,0.15); flex-wrap: wrap; gap: 10px;
}
.pagination-info { font-size: 12.5px; color: rgba(255,255,255,0.4); }
.pagination-info strong { color: rgba(255,255,255,0.75); font-weight: 700; }
.pagination-btns { display: flex; align-items: center; gap: 4px; }
.page-btn {
    min-width: 32px; height: 32px; padding: 0 8px;
    border-radius: 8px; border: 1px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.07); font-size: 12.5px; font-weight: 600;
    color: rgba(255,255,255,0.5); cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; transition: all .15s; font-family: var(--font);
}
.page-btn:hover:not(.page-btn--active):not([disabled]) {
    border-color: rgba(147,197,253,0.4); color: #93c5fd; background: rgba(59,110,245,0.15);
}
.page-btn--active { background: rgba(255,255,255,0.2); color: white; border-color: rgba(255,255,255,0.35); }
.page-btn[disabled] { opacity: 0.25; cursor: not-allowed; }
.page-btn--dots { cursor: default; border-color: transparent; background: transparent; color: rgba(255,255,255,0.3); }

/* DELETE MODAL */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.6); z-index: 400;
    align-items: center; justify-content: center;
    backdrop-filter: blur(6px);
}
.modal-overlay.open { display: flex; }
.modal {
    background: #0f2148; border: 1px solid rgba(255,255,255,0.15);
    border-radius: 18px; padding: 28px;
    max-width: 400px; width: 90%;
    box-shadow: 0 24px 64px rgba(0,0,0,0.5);
    animation: modalIn .18s ease;
}
@keyframes modalIn { from { opacity:0; transform:translateY(12px) scale(.97); } to { opacity:1; transform:none; } }
.modal h3 { font-size: 16px; font-weight: 700; margin-bottom: 8px; color: white; }
.modal p { font-size: 13px; color: rgba(255,255,255,0.55); margin-bottom: 20px; line-height: 1.6; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; }
.modal-cancel {
    padding: 9px 20px; border-radius: 9px;
    border: 1px solid rgba(255,255,255,0.2); background: rgba(255,255,255,0.08);
    font-size: 13px; font-weight: 500; cursor: pointer; font-family: var(--font);
    color: rgba(255,255,255,0.65); transition: all .15s;
}
.modal-cancel:hover { background: rgba(255,255,255,0.14); color: white; }
.modal-confirm {
    padding: 9px 20px; border-radius: 9px; border: none;
    background: #dc2626; color: white;
    font-size: 13px; font-weight: 600; cursor: pointer; font-family: var(--font);
    transition: background .15s;
}
.modal-confirm:hover { background: #b91c1c; }

@media (max-width: 768px) {
    .main { padding: 16px 14px 36px; }

    .filter-bar {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
        padding: 12px 14px;
    }
    .filter-left {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }

    .filter-tabs {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 2px;
    }
    .filter-tabs::-webkit-scrollbar { display: none; }
    .filter-tab { white-space: nowrap; flex-shrink: 0; }

    .inline-search { width: 100%; }
    .inline-search__input { width: 100%; }

    .filter-bar > .view-toggle {
        align-self: flex-end;
        margin-top: -44px;
    }

    #view-list { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .requests-table { min-width: 580px; }

    .requests-table th:nth-child(2),
    .requests-table td:nth-child(2),
    .requests-table th:nth-child(3),
    .requests-table td:nth-child(3) { display: none; }

    .requests-grid { grid-template-columns: repeat(2, 1fr); padding: 10px; gap: 10px; }

    .pagination-wrap { flex-direction: column; align-items: center; gap: 10px; padding: 12px 14px; }
    .pagination-info { text-align: center; }

    .modal { padding: 22px 18px; }
    .modal-actions { flex-direction: column-reverse; }
    .modal-cancel, .modal-confirm { width: 100%; text-align: center; padding: 11px; }
}

@media (max-width: 480px) {
    .requests-grid { grid-template-columns: 1fr; }
    .filter-bar > .view-toggle { margin-top: 0; align-self: flex-start; }
    .action-btns { flex-wrap: wrap; }
}
</style>
@endsection

@section('content')
<div class="main">

    <div class="page-header">
        <h1>My Requests</h1>
        <p>View and manage all your post requests</p>
    </div>

    @if(session('success'))
        <div class="alert alert--success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert--error">❌ {{ session('error') }}</div>
    @endif

    <div class="card">

        {{-- FILTER BAR --}}
        <div class="filter-bar">
            <div class="filter-left">
                <div class="filter-tabs">
                    <a href="{{ route('requestor.requests', ['filter' => 'all']) }}"      class="filter-tab {{ $filter === 'all'      ? 'filter-tab--active' : '' }}">All</a>
                    <a href="{{ route('requestor.requests', ['filter' => 'pending']) }}"  class="filter-tab {{ $filter === 'pending'  ? 'filter-tab--active' : '' }}">Pending</a>
                    <a href="{{ route('requestor.requests', ['filter' => 'seen']) }}"     class="filter-tab {{ $filter === 'seen'     ? 'filter-tab--active' : '' }}">Seen</a>
                    <a href="{{ route('requestor.requests', ['filter' => 'approved']) }}" class="filter-tab {{ $filter === 'approved' ? 'filter-tab--active' : '' }}">Approved</a>
                    <a href="{{ route('requestor.requests', ['filter' => 'posted']) }}"   class="filter-tab {{ $filter === 'posted'   ? 'filter-tab--active' : '' }}">Posted</a>
                </div>

                {{-- INLINE LIVE SEARCH --}}
                <div class="inline-search">
                    <span class="inline-search__icon">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input class="inline-search__input" id="live-search-input"
                           type="text" placeholder="Search requests…"
                           autocomplete="off" value="{{ $search ?? '' }}">
                    <button class="inline-search__clear {{ ($search ?? '') !== '' ? 'visible' : '' }}"
                            id="live-search-clear" onclick="clearSearch()">✕</button>
                </div>
            </div>

            <div class="view-toggle">
                <button class="view-btn" id="btn-list" onclick="setView('list')" title="List view">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </button>
                <button class="view-btn" id="btn-grid" onclick="setView('grid')" title="Grid view">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                </button>
            </div>
        </div>

        <div class="count-row">
            <div>Showing <span id="visible-count">{{ $total }}</span> request<span id="count-plural">{{ $total !== 1 ? 's' : '' }}</span></div>
            <div class="search-active-label" id="search-label" style="{{ ($search ?? '') !== '' ? '' : 'display:none;' }}">
                Searching: "{{ $search ?? '' }}"
            </div>
        </div>

        {{-- ALL ROWS (JS does the filtering + pagination) --}}
        @if($total > 0)

        {{-- LIST VIEW --}}
        <div id="view-list">
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="list-tbody">
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
                        $thumb      = $req->first_media;
                        $is_pending = $req->status === 'Pending Review';
                    @endphp
                    <tr class="req-row"
                        data-title="{{ strtolower($req->title) }}"
                        data-desc="{{ strtolower($req->description) }}"
                        onclick="window.location='{{ route('requestor.requests.show', $req->id) }}'">
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
                                    <div class="req-title" data-original="{{ $req->title }}">{{ $req->title }}</div>
                                    <div class="req-desc" data-original="{{ Str::limit($req->description, 80) }}">{{ Str::limit($req->description, 80) }}</div>
                                </div>
                            </div>
                        </td>
                        <td style="color:rgba(255,255,255,0.5);font-size:12px;">{{ $req->category }}</td>
                        <td><span class="badge badge--{{ $priority_class }}">{{ strtoupper($req->priority) }}</span></td>
                        <td><span class="badge badge--{{ $status_class }}">{{ $req->status }}</span></td>
                        <td class="date-text">{{ $req->created_at->format('M j, Y') }}</td>
                        <td onclick="event.stopPropagation()">
                            <div class="action-btns">
                                <a href="{{ route('requestor.requests.chat', $req->id) }}" class="btn-chat">Chat</a>
                                @if($is_pending)
                                    <a href="{{ route('requestor.requests.edit', $req->id) }}" class="btn-edit">Edit</a>
                                    <button class="btn-delete" onclick="confirmDelete({{ $req->id }}, '{{ addslashes($req->title) }}')">Delete</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- GRID VIEW --}}
        <div id="view-grid" style="display:none;">
            <div class="requests-grid" id="grid-container">
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
                    $thumb      = $req->first_media;
                    $is_pending = $req->status === 'Pending Review';
                @endphp
                <div class="grid-card req-row"
                     data-title="{{ strtolower($req->title) }}"
                     data-desc="{{ strtolower($req->description) }}"
                     onclick="window.location='{{ route('requestor.requests.show', $req->id) }}'">
                    @if($thumb)
                        <img class="grid-card__thumb" src="/uploads/{{ $thumb }}" alt=""
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="grid-card__thumb-placeholder" style="display:none;">📄</div>
                    @else
                        <div class="grid-card__thumb-placeholder">📄</div>
                    @endif
                    <div class="grid-card__body">
                        <div class="grid-card__title" data-original="{{ $req->title }}">{{ $req->title }}</div>
                        <div class="grid-card__desc" data-original="{{ $req->description }}">{{ $req->description }}</div>
                        <div class="grid-card__footer">
                            <span class="badge badge--{{ $status_class }}">{{ $req->status }}</span>
                            <span class="badge badge--{{ $priority_class }}">{{ strtoupper($req->priority) }}</span>
                        </div>
                        <div class="grid-card__actions" onclick="event.stopPropagation()">
                            <a href="{{ route('requestor.requests.chat', $req->id) }}" class="btn-chat">Chat</a>
                            @if($is_pending)
                                <a href="{{ route('requestor.requests.edit', $req->id) }}" class="btn-edit">Edit</a>
                                <button class="btn-delete" onclick="confirmDelete({{ $req->id }}, '{{ addslashes($req->title) }}')">Delete</button>
                            @endif
                        </div>
                        <div class="grid-card__date">{{ $req->created_at->format('M j, Y') }}</div>
                    </div>
                </div>
            @endforeach
            </div>
        </div>

        {{-- EMPTY SEARCH STATE (hidden by default) --}}
        <div id="empty-search-state" style="display:none;">
            <div class="empty-state">
                <div class="empty-state__icon">
                    <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <p>No results found. Try a different keyword.</p>
            </div>
        </div>

        {{-- PAGINATION --}}
        <div class="pagination-wrap" id="pagination-wrap" style="display:none;">
            <div class="pagination-info">
                Page <strong id="page-cur">1</strong> of <strong id="page-last">1</strong>
                &nbsp;·&nbsp; <strong id="page-range"></strong>
            </div>
            <div class="pagination-btns" id="pagination-btns"></div>
        </div>

        @else
            <div class="empty-state">
                <div class="empty-state__icon">
                    <svg width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </div>
                <p>No requests yet. <a href="{{ route('requestor.requests.create') }}">Create one →</a></p>
            </div>
        @endif

    </div>
</div>

{{-- DELETE MODAL --}}
<div class="modal-overlay" id="delete-modal">
    <div class="modal">
        <h3>🗑️ Delete Request</h3>
        <p>Are you sure you want to delete "<strong id="delete-title"></strong>"? This cannot be undone.</p>
        <div class="modal-actions">
            <button class="modal-cancel" onclick="closeDeleteModal()">Cancel</button>
            <form id="delete-form" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="modal-confirm">Yes, Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// ── VIEW TOGGLE ────────────────────────────────────────────────────
function setView(v) {
    document.getElementById('view-list').style.display = v === 'list' ? 'block' : 'none';
    document.getElementById('view-grid').style.display = v === 'grid' ? 'block' : 'none';
    document.getElementById('btn-list').classList.toggle('view-btn--active', v === 'list');
    document.getElementById('btn-grid').classList.toggle('view-btn--active', v === 'grid');
    localStorage.setItem('nupost_view', v);
    renderPage(currentPage);
}
const savedView = localStorage.getItem('nupost_view') || 'list';

// ── LIVE SEARCH + PAGINATION ───────────────────────────────────────
const PER_PAGE = 5;
let currentPage = 1;
let filteredRows = []; // holds currently visible/matching rows

// All list rows and grid cards paired together
let allListRows  = [];
let allGridCards = [];

function escapeRegex(s) {
    return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

function highlightText(original, query) {
    if (!query) return original;
    const re = new RegExp('(' + escapeRegex(query) + ')', 'gi');
    return original.replace(re, '<mark class="highlight">$1</mark>');
}

function applySearch(query) {
    const q = query.trim().toLowerCase();
    filteredRows = [];

    allListRows.forEach((row, i) => {
        const title = row.dataset.title || '';
        const desc  = row.dataset.desc  || '';
        const match = !q || title.includes(q) || desc.includes(q);

        if (match) filteredRows.push(i);

        // highlight in list view
        const titleEl = row.querySelector('.req-title');
        const descEl  = row.querySelector('.req-desc');
        if (titleEl) titleEl.innerHTML = q ? highlightText(titleEl.dataset.original || titleEl.textContent, q) : (titleEl.dataset.original || titleEl.textContent);
        if (descEl)  descEl.innerHTML  = q ? highlightText(descEl.dataset.original  || descEl.textContent,  q) : (descEl.dataset.original  || descEl.textContent);
    });

    allGridCards.forEach((card, i) => {
        const title = card.dataset.title || '';
        const desc  = card.dataset.desc  || '';

        // highlight in grid view
        const titleEl = card.querySelector('.grid-card__title');
        const descEl  = card.querySelector('.grid-card__desc');
        const q_orig  = query.trim();
        if (titleEl) titleEl.innerHTML = q_orig ? highlightText(titleEl.dataset.original || titleEl.textContent, q_orig) : (titleEl.dataset.original || titleEl.textContent);
        if (descEl)  descEl.innerHTML  = q_orig ? highlightText(descEl.dataset.original  || descEl.textContent,  q_orig) : (descEl.dataset.original  || descEl.textContent);
    });

    // Update search label
    const searchLabel = document.getElementById('search-label');
    const clearBtn    = document.getElementById('live-search-clear');
    if (q) {
        searchLabel.textContent = `Searching: "${query.trim()}"`;
        searchLabel.style.display = '';
        clearBtn.classList.add('visible');
    } else {
        searchLabel.style.display = 'none';
        clearBtn.classList.remove('visible');
    }

    currentPage = 1;
    renderPage(1);
}

function renderPage(page) {
    const total = filteredRows.length;
    const last  = Math.max(1, Math.ceil(total / PER_PAGE));
    currentPage = Math.max(1, Math.min(page, last));

    const start = (currentPage - 1) * PER_PAGE;
    const end   = start + PER_PAGE;

    // Show/hide list rows
    allListRows.forEach((row, i) => {
        const inFilter = filteredRows.includes(i);
        const inPage   = inFilter && filteredRows.indexOf(i) >= start && filteredRows.indexOf(i) < end;
        row.style.display = inPage ? '' : 'none';
    });

    // Show/hide grid cards (mirror same indices)
    allGridCards.forEach((card, i) => {
        const inFilter = filteredRows.includes(i);
        const inPage   = inFilter && filteredRows.indexOf(i) >= start && filteredRows.indexOf(i) < end;
        card.style.display = inPage ? '' : 'none';
    });

    // Count display
    const visibleCount = document.getElementById('visible-count');
    const countPlural  = document.getElementById('count-plural');
    if (visibleCount) { visibleCount.textContent = total; }
    if (countPlural)  { countPlural.textContent  = total !== 1 ? 's' : ''; }

    // Empty search state
    const emptySearch = document.getElementById('empty-search-state');
    const listView    = document.getElementById('view-list');
    const gridView    = document.getElementById('view-grid');
    const currentView = localStorage.getItem('nupost_view') || 'list';

    if (total === 0) {
        if (emptySearch) emptySearch.style.display = 'block';
        if (currentView === 'list' && listView) listView.style.display = 'none';
        if (currentView === 'grid' && gridView) gridView.style.display = 'none';
    } else {
        if (emptySearch) emptySearch.style.display = 'none';
        if (currentView === 'list' && listView) listView.style.display = 'block';
        if (currentView === 'grid' && gridView) gridView.style.display = 'block';
    }

    // Pagination
    const paginWrap = document.getElementById('pagination-wrap');
    const curEl     = document.getElementById('page-cur');
    const lastEl    = document.getElementById('page-last');
    const rangeEl   = document.getElementById('page-range');

    if (paginWrap) {
        paginWrap.style.display = total > PER_PAGE ? 'flex' : 'none';
    }
    if (curEl)   curEl.textContent  = currentPage;
    if (lastEl)  lastEl.textContent = last;
    if (rangeEl) {
        const from = total === 0 ? 0 : start + 1;
        const to   = Math.min(end, total);
        rangeEl.textContent = `${from}–${to} of ${total}`;
    }

    const wrap = document.getElementById('pagination-btns');
    if (!wrap) return;

    let pages = [];
    if (last <= 7) {
        pages = Array.from({length: last}, (_, i) => i + 1);
    } else {
        pages = [1];
        if (currentPage > 3) pages.push('...');
        for (let p = Math.max(2, currentPage - 1); p <= Math.min(last - 1, currentPage + 1); p++) pages.push(p);
        if (currentPage < last - 2) pages.push('...');
        pages.push(last);
    }

    const prevDis = currentPage === 1    ? 'disabled' : '';
    const nextDis = currentPage === last ? 'disabled' : '';
    let html = `<button class="page-btn" ${prevDis} onclick="renderPage(${currentPage - 1})"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg></button>`;
    pages.forEach(p => {
        if (p === '...') {
            html += `<span class="page-btn page-btn--dots">···</span>`;
        } else {
            const act = p === currentPage ? 'page-btn--active' : '';
            html += `<button class="page-btn ${act}" onclick="renderPage(${p})">${p}</button>`;
        }
    });
    html += `<button class="page-btn" ${nextDis} onclick="renderPage(${currentPage + 1})"><svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg></button>`;
    wrap.innerHTML = html;
}

function clearSearch() {
    const input = document.getElementById('live-search-input');
    input.value = '';
    applySearch('');
    input.focus();
}

// ── INIT ──────────────────────────────────────────────────────────
function initRequestsPage() {
    allListRows  = Array.from(document.querySelectorAll('#list-tbody .req-row'));
    allGridCards = Array.from(document.querySelectorAll('#grid-container .req-row'));
    filteredRows = [];
    allListRows.forEach((_, i) => filteredRows.push(i));

    const v = localStorage.getItem('nupost_view') || 'list';
    setView(v);

    renderPage(1);

    const searchInput = document.getElementById('live-search-input');
    if (searchInput && !searchInput._bound) {
        searchInput._bound = true;
        let debounceTimer;
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => applySearch(this.value), 300);
        });
        if (searchInput.value.trim()) applySearch(searchInput.value);
    }
}
document.addEventListener('DOMContentLoaded', initRequestsPage);

// ── DELETE MODAL ─────────────────────────────────────────────────
function confirmDelete(id, title) {
    document.getElementById('delete-title').textContent = title;
    document.getElementById('delete-form').action = '/requestor/requests/' + id;
    document.getElementById('delete-modal').classList.add('open');
}
function closeDeleteModal() {
    document.getElementById('delete-modal').classList.remove('open');
}
document.getElementById('delete-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>
@endsection