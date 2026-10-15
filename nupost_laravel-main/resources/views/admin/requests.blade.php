@extends('layouts.admin')
@section('title', 'Request Management')

@section('head-styles')
<style>
.page-hd { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; }
.page-hd__title { font-family:var(--font-disp); font-size:22px; color:var(--ink); }
.page-hd__sub   { font-size:13px; color:var(--ink-soft); margin-top:3px; }

/* STAT CARDS — Navy Blue Style */
.stats-row { display:grid; grid-template-columns:repeat(5,1fr); gap:14px; margin-bottom:22px; }
.scard {
    background:linear-gradient(135deg,#001a4d 0%,#002366 60%,#002e7a 100%);
    border-radius:var(--radius); padding:18px 16px;
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
.scard__icon { width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.scard__num  { font-size:26px; font-weight:800; letter-spacing:-0.8px; line-height:1; color:white; }
.scard__lbl  { font-size:11.5px; color:rgba(255,255,255,0.6); font-weight:500; margin-top:3px; }

.scard--yellow .scard__icon { background:rgba(245,158,11,0.25); color:#fbbf24; }
.scard--yellow::after { content:''; position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#f59e0b,#fbbf24); }
.scard--blue .scard__icon { background:rgba(59,130,246,0.25); color:#93c5fd; }
.scard--blue::after { content:''; position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#3b82f6,#93c5fd); }
.scard--green .scard__icon { background:rgba(16,185,129,0.25); color:#6ee7b7; }
.scard--green::after { content:''; position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#10b981,#6ee7b7); }
.scard--purple .scard__icon { background:rgba(139,92,246,0.25); color:#c4b5fd; }
.scard--purple::after { content:''; position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#8b5cf6,#c4b5fd); }
.scard--red .scard__icon { background:rgba(239,68,68,0.25); color:#fca5a5; }
.scard--red::after { content:''; position:absolute; bottom:0; left:0; right:0; height:3px; background:linear-gradient(90deg,#ef4444,#fca5a5); }

/* TOOLBAR */
.toolbar { display:flex; align-items:center; gap:10px; margin-bottom:16px; flex-wrap:wrap; }
.search-wrap { flex:1; min-width:220px; position:relative; }
.search-wrap input { width:100%; height:42px; background:var(--card); border:1px solid rgba(0,0,0,0.08); border-radius:var(--radius-sm); padding:0 14px 0 42px; font-size:13px; font-family:var(--font); color:var(--ink); outline:none; transition:border-color .15s,box-shadow .15s; }
.search-wrap input:focus { border-color:var(--navy-light); box-shadow:0 0 0 3px rgba(30,79,216,0.08); }
.search-wrap input::placeholder { color:var(--ink-faint); }
.search-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--ink-faint); pointer-events:none; }
.search-spinner { position:absolute; right:14px; top:50%; transform:translateY(-50%); display:none; }
.search-spinner.visible { display:block; }

.toolbar select { height:42px; background:var(--card); border:1px solid rgba(0,0,0,0.08); border-radius:var(--radius-sm); padding:0 14px; font-size:13px; font-family:var(--font); color:var(--ink); outline:none; cursor:pointer; transition:border-color .15s; }
.toolbar select:focus { border-color:var(--navy-light); }
.btn-search { height:42px; padding:0 20px; background:var(--navy); color:white; border:none; border-radius:var(--radius-sm); font-size:13px; font-weight:600; cursor:pointer; font-family:var(--font); transition:background .15s; display:flex; align-items:center; gap:7px; }
.btn-search:hover { background:var(--navy-mid); }
.btn-reset { height:42px; padding:0 16px; background:var(--card); color:var(--ink-soft); border:1px solid rgba(0,0,0,0.08); border-radius:var(--radius-sm); font-size:13px; cursor:pointer; font-family:var(--font); text-decoration:none; display:flex; align-items:center; gap:6px; transition:all .15s; }
.btn-reset:hover { background:var(--cream-dark); }

.search-banner { display:flex; align-items:center; justify-content:space-between; background:var(--navy-pale); border:1px solid rgba(0,35,102,0.12); border-radius:var(--radius-sm); padding:10px 16px; margin-bottom:14px; font-size:13px; color:var(--navy); }
.search-banner a { color:var(--navy); font-weight:600; text-decoration:none; font-size:12px; }

/* TABLE */
.table-panel { background:var(--card); border-radius:var(--radius); border:1px solid rgba(0,0,0,0.06); box-shadow:var(--shadow-sm); overflow:hidden; }
.table-panel__head { padding:16px 22px; border-bottom:1px solid rgba(0,0,0,0.05); display:flex; align-items:center; justify-content:space-between; }
.table-panel__title { font-family:var(--font-disp); font-size:17px; color:var(--ink); }
.table-panel__count { font-size:12.5px; color:var(--ink-soft); }
.table-panel__count strong { color:var(--ink); }
table { width:100%; border-collapse:collapse; }
thead tr { border-bottom:1px solid rgba(0,0,0,0.05); background:rgba(0,0,0,0.02); }
th { padding:10px 16px; text-align:left; font-size:10px; font-weight:700; color:var(--ink-soft); text-transform:uppercase; letter-spacing:0.7px; white-space:nowrap; }
tbody tr { border-bottom:1px solid rgba(0,0,0,0.04); transition:background .1s; }
tbody tr:last-child { border-bottom:none; }
tbody tr:hover { background:rgba(0,0,0,0.02); }
td { padding:13px 16px; font-size:12.5px; vertical-align:middle; }
.req-info { display:flex; align-items:center; gap:11px; }
.req-thumb { width:40px; height:40px; border-radius:12px; object-fit:cover; flex-shrink:0; border:1px solid rgba(0,0,0,0.06); }
.req-thumb-ph { width:40px; height:40px; border-radius:12px; background:var(--cream-dark); display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; }
.req-title-text { font-weight:600; font-size:13px; color:var(--ink); }
.req-desc-text { font-size:11px; color:var(--ink-soft); margin-top:2px; max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.action-wrap { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
.status-select { padding:6px 10px; border-radius:10px; border:1px solid rgba(0,0,0,0.08); font-size:11.5px; font-family:var(--font); background:var(--card); color:var(--ink); cursor:pointer; outline:none; }
.btn-save { padding:6px 12px; background:var(--navy); color:white; border:none; border-radius:10px; font-size:11.5px; font-weight:600; cursor:pointer; font-family:var(--font); }
.btn-save:hover { background:var(--navy-mid); }
.btn-chat { padding:6px 12px; background:var(--card); color:var(--ink-soft); border:1px solid rgba(0,0,0,0.08); border-radius:10px; font-size:11.5px; font-weight:500; cursor:pointer; font-family:var(--font); display:inline-flex; align-items:center; gap:5px; transition:all .15s; }
.btn-chat:hover { background:var(--navy-pale); border-color:var(--navy-light); color:var(--navy); }
.chat-count { display:inline-flex; align-items:center; justify-content:center; width:17px; height:17px; background:var(--navy); color:white; border-radius:50%; font-size:9px; font-weight:700; }
.date-text { font-size:11.5px; color:var(--ink-soft); white-space:nowrap; }
.empty-state { padding:56px; text-align:center; color:var(--ink-faint); font-size:13px; }

/* SLA BADGES & ALERTS */
.sla-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 9px; border-radius: 999px; font-size: 11px; font-weight: 700;
    line-height: 1.3; white-space: nowrap;
}
.sla-chip--risk { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.sla-chip--soon { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
.sla-chip--ok   { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }

.sla-alert-banner {
    display: flex; align-items: center; justify-content: space-between;
    background: #fff1f2; border: 1px solid #fecdd3; border-radius: var(--radius-sm);
    padding: 12px 18px; margin-bottom: 14px; font-size: 13px; color: #9f1239;
}
.btn-view-risk {
    background: #e11d48; color: white !important; font-size: 12px; font-weight: 700;
    padding: 6px 14px; border-radius: 8px; text-decoration: none; transition: background .15s;
    white-space: nowrap;
}
.btn-view-risk:hover { background: #be123c; }

/* PAGINATION */
.pagination-wrap { display:flex; align-items:center; justify-content:space-between; padding:14px 22px; border-top:1px solid rgba(0,0,0,0.05); background:var(--cream); }
.pagination-info { font-size:12.5px; color:var(--ink-soft); }
.pagination-info strong { color:var(--ink); }
.pagination-btns { display:flex; align-items:center; gap:4px; }
.pg-btn {
    min-width:36px; height:36px; padding:0 10px; border-radius:9px;
    border:1px solid rgba(0,0,0,0.08); background:var(--card);
    font-size:13px; font-weight:500; color:var(--ink-soft);
    cursor:pointer; font-family:var(--font); text-decoration:none;
    display:inline-flex; align-items:center; justify-content:center;
    transition:all .15s;
}
.pg-btn:hover { background:var(--navy-pale); border-color:var(--navy-light); color:var(--navy); }
.pg-btn--active { background:var(--navy); border-color:var(--navy); color:white; font-weight:700; }
.pg-btn--active:hover { background:var(--navy-mid); }
.pg-btn--disabled { opacity:0.4; pointer-events:none; cursor:default; }

/* CHAT PANEL */
.panel-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.2); z-index:199; backdrop-filter:blur(2px); }
.panel-overlay.open { display:block; }
.chat-panel { display:none; position:fixed; top:0; right:0; bottom:0; width:420px; background:var(--card); z-index:200; flex-direction:column; border-left:1px solid rgba(0,0,0,0.08); border-radius:24px 0 0 24px; box-shadow:-8px 0 40px rgba(0,0,0,0.1); }
.chat-panel.open { display:flex; }
.chat-panel__head { padding:20px 22px 16px; background:#0d1b3e; border-radius:24px 0 0 0; display:flex; align-items:flex-start; justify-content:space-between; flex-shrink:0; }
.chat-panel__head-left { display:flex; align-items:center; gap:10px; }
.chat-panel__icon { width:36px; height:36px; border-radius:10px; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; }
.chat-panel__title { font-size:14px; font-weight:700; color:white; }
.chat-panel__sub { font-size:11.5px; color:rgba(255,255,255,0.45); margin-top:2px; }
.chat-panel__close { background:rgba(255,255,255,0.1); border:none; cursor:pointer; color:white; padding:7px; border-radius:9px; display:flex; align-items:center; transition:background .15s; }
.chat-panel__close:hover { background:rgba(255,255,255,0.18); }
.chat-panel__info { padding:12px 22px; background:rgba(0,0,0,0.03); border-bottom:1px solid rgba(0,0,0,0.05); flex-shrink:0; }
.chat-panel__req-title { font-size:13px; font-weight:600; color:var(--ink); }
.chat-panel__req-meta { font-size:11.5px; color:var(--ink-soft); margin-top:3px; }
.chat-panel__messages {
    flex: 1 1 auto;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 18px 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    background: var(--cream);
    box-sizing: border-box;
    scroll-behavior: smooth;
}
.chat-panel__messages::-webkit-scrollbar { width: 4px; }
.chat-panel__messages::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 4px; }

.bubble-row {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    width: 100%;
    max-width: 100%;
    flex-shrink: 0;
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    position: relative;
}
.bubble-row--admin {
    flex-direction: row-reverse;
    justify-content: flex-start;
}

.bubble-av {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    color: white;
    margin-bottom: 2px;
}
.bubble-av--admin { background: #0d1b3e; }
.bubble-av--requestor { background: var(--purple); }

.bubble {
    max-width: 75%;
    min-width: 48px;
    padding: 10px 14px;
    border-radius: 18px;
    font-size: 13px;
    line-height: 1.55;
    word-break: break-word;
    overflow-wrap: anywhere;
    box-sizing: border-box;
    position: relative;
    display: flex;
    flex-direction: column;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
.bubble--admin {
    background: #0d1b3e;
    color: white;
    border-radius: 18px 18px 4px 18px;
}
.bubble--requestor {
    background: var(--card);
    color: var(--ink);
    border: 1px solid rgba(0,0,0,0.06);
    border-radius: 18px 18px 18px 4px;
}

.bubble-text {
    word-break: break-word;
    overflow-wrap: anywhere;
    line-height: 1.5;
}

.bubble-meta {
    font-size: 10px;
    margin-top: 4px;
    line-height: 1.2;
    opacity: .75;
    user-select: none;
    white-space: nowrap;
}
.bubble--admin .bubble-meta {
    text-align: right;
    color: rgba(255,255,255,.8);
    align-self: flex-end;
}
.bubble--requestor .bubble-meta {
    color: var(--ink-faint);
    align-self: flex-start;
}
.chat-empty-state { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:8px; color:var(--ink-faint); padding:32px; }
.chat-panel__form { padding:14px 20px; border-top:1px solid rgba(0,0,0,0.05); flex-shrink:0; background:var(--card); border-radius:0 0 0 24px; }
.chat-panel__textarea { width:100%; border:1px solid rgba(0,0,0,0.08); border-radius:12px; padding:10px 14px; font-size:13px; font-family:var(--font); resize:none; outline:none; height:78px; background:var(--cream); color:var(--ink); transition:border-color .15s; }
.chat-panel__textarea:focus { border-color:var(--navy-light); }
.chat-panel__send { width:100%; margin-top:8px; padding:11px; background:#0d1b3e; color:white; border:none; border-radius:12px; font-size:13px; font-weight:600; cursor:pointer; font-family:var(--font); display:flex; align-items:center; justify-content:center; gap:7px; transition:background .15s; }
.chat-panel__send:hover { background:var(--navy); }

.toast { position:fixed; bottom:24px; right:24px; z-index:999; background:#0d1b3e; color:white; padding:12px 20px; border-radius:14px; font-size:13px; font-weight:500; box-shadow:0 4px 20px rgba(0,0,0,0.2); display:flex; align-items:center; gap:10px; animation:slideUp .3s ease,fadeOut .4s ease 2.6s forwards; }
.toast--success { background:#059669; }
.toast--reject  { background:#dc2626; }
@keyframes slideUp { from{transform:translateY(16px);opacity:0}to{transform:translateY(0);opacity:1} }
@keyframes fadeOut { from{opacity:1}to{opacity:0;pointer-events:none} }
</style>
@endsection

@section('content')
<div class="page-hd">
    <div>
        <div class="page-hd__title">Request Management</div>
        <div class="page-hd__sub">Review, update and communicate with requestors</div>
    </div>
</div>

<div class="stats-row">
    <div class="scard scard--yellow">
        <div class="scard__icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
        <div><div class="scard__num">{{ $pending }}</div><div class="scard__lbl">Pending Review</div></div>
    </div>
    <div class="scard scard--blue">
        <div class="scard__icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></div>
        <div><div class="scard__num">{{ $review }}</div><div class="scard__lbl">Under Review</div></div>
    </div>
    <div class="scard scard--green">
        <div class="scard__icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
        <div><div class="scard__num">{{ $approved }}</div><div class="scard__lbl">Approved</div></div>
    </div>
    <div class="scard scard--purple">
        <div class="scard__icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg></div>
        <div><div class="scard__num">{{ $posted }}</div><div class="scard__lbl">Posted</div></div>
    </div>
    <div class="scard scard--red">
        <div class="scard__icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg></div>
        <div><div class="scard__num">{{ $rejected }}</div><div class="scard__lbl">Rejected</div></div>
    </div>
</div>

<form method="GET" action="{{ route('admin.requests') }}" class="toolbar" id="filter-form">
    <div class="search-wrap">
        <span class="search-icon"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="text" name="search" id="search-input" placeholder="Search title, requestor, category..." value="{{ $search }}" autocomplete="off">
        <span class="search-spinner" id="search-spinner">
            <svg width="15" height="15" fill="none" stroke="var(--navy)" stroke-width="2" viewBox="0 0 24 24" style="animation:spin .7s linear infinite;">
                <path d="M21 12a9 9 0 1 1-6.22-8.56"/>
                <style>@keyframes spin{to{transform:rotate(360deg)}}</style>
            </svg>
        </span>
    </div>
    <select name="filter" onchange="this.form.submit()">
        <option value="all" {{ $filter==='all'?'selected':'' }}>All Requests</option>
        @if(($at_risk_count ?? 0) > 0)
            <option value="at_risk" {{ $filter==='at_risk'?'selected':'' }}>⚠️ At Risk / Overdue ({{ $at_risk_count }})</option>
        @endif
        @if(($due_soon_count ?? 0) > 0)
            <option value="due_soon" {{ $filter==='due_soon'?'selected':'' }}>⏰ Due Soon ({{ $due_soon_count }})</option>
        @endif
        <option value="pending" {{ $filter==='pending'?'selected':'' }}>Pending Review</option>
        <option value="review" {{ $filter==='review'?'selected':'' }}>Under Review</option>
        <option value="approved" {{ $filter==='approved'?'selected':'' }}>Approved</option>
        <option value="posted" {{ $filter==='posted'?'selected':'' }}>Posted</option>
        <option value="rejected" {{ $filter==='rejected'?'selected':'' }}>Rejected</option>
    </select>
    <select name="sort" onchange="this.form.submit()">
        <option value="urgency" {{ $sort==='urgency'?'selected':'' }}>⚡ Urgency / SLA Slack</option>
        <option value="newest" {{ $sort==='newest'?'selected':'' }}>Newest First</option>
        <option value="oldest" {{ $sort==='oldest'?'selected':'' }}>Oldest First</option>
        <option value="priority" {{ $sort==='priority'?'selected':'' }}>By Priority</option>
    </select>
    <button type="submit" class="btn-search">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        Search
    </button>
    @if($search!==''||$filter!=='all'||$sort!=='urgency')
        <a href="{{ route('admin.requests') }}" class="btn-reset">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Reset
        </a>
    @endif
</form>

@if(($at_risk_count ?? 0) > 0 && $filter !== 'at_risk')
<div class="sla-alert-banner">
    <div style="display:flex;align-items:center;gap:10px;">
        <span style="font-size:20px;">⚠️</span>
        <span><strong>{{ $at_risk_count }} request{{ $at_risk_count > 1 ? 's are' : ' is' }} currently At Risk / Overdue SLA!</strong> Production start date has passed based on lead times.</span>
    </div>
    <a href="{{ route('admin.requests',['filter'=>'at_risk','sort'=>'urgency']) }}" class="btn-view-risk">View Overdue ({{ $at_risk_count }})</a>
</div>
@endif

@if($search!=='')
<div class="search-banner">
    <span>Results for <strong>"{{ $search }}"</strong> — <strong>{{ $requests->total() }}</strong> found</span>
    <a href="{{ route('admin.requests',['filter'=>$filter,'sort'=>$sort]) }}">✕ Clear</a>
</div>
@endif

<div class="table-panel">
    <div class="table-panel__head">
        <div class="table-panel__title">All Requests</div>
        <div class="table-panel__count">
            Showing <strong>{{ $requests->firstItem() ?? 0 }}–{{ $requests->lastItem() ?? 0 }}</strong>
            of <strong>{{ $requests->total() }}</strong> request{{ $requests->total()!==1?'s':'' }}
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>Request</th>
                    <th>Requestor</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>SLA Slack & Urgency</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($requests as $req)
                @php
                    $sl=strtolower($req->status);
                    $sc=match(true){str_contains($sl,'approved')=>'approved',str_contains($sl,'posted')=>'posted',str_contains($sl,'under review')=>'under-review',str_contains($sl,'rejected')=>'rejected',default=>'pending'};
                    $pc=match(strtolower($req->priority??'')){'urgent'=>'urgent','high'=>'high','medium'=>'medium',default=>'low'};
                    $thumb=$req->first_media;
                    $cc=$req->comments()->count();
                @endphp
                <tr>
                    <td>
                        <div class="req-info" style="cursor:pointer;" onclick="window.location='{{ route('admin.requests.show', $req->id) }}'">
                            @if($thumb)
                                <img class="req-thumb" src="/uploads/{{ $thumb }}" alt="" onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                <div class="req-thumb-ph" style="display:none;">📄</div>
                            @else
                                <div class="req-thumb-ph">📄</div>
                            @endif
                            <div>
                                <div class="req-title-text">{{ Str::limit($req->title,32) }}</div>
                                <div class="req-desc-text">{{ Str::limit($req->description,55) }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-weight:500;">{{ $req->requester }}</td>
                    <td style="color:var(--ink-soft);">{{ $req->category }}</td>
                    <td><span class="badge badge--{{ $pc }}">{{ strtoupper($req->priority) }}</span></td>
                    <td>
                        @if($req->is_at_risk)
                            <div class="sla-chip sla-chip--risk" title="SLA Lead Time: {{ $req->sla_days }}d | Preferred: {{ $req->preferred_date ? \Carbon\Carbon::parse($req->preferred_date)->format('M j, Y') : 'N/A' }}">
                                <span>⚠️</span>
                                <span>Overdue ({{ abs($req->slack_days) }}d behind)</span>
                            </div>
                        @elseif($req->is_due_soon)
                            <div class="sla-chip sla-chip--soon" title="SLA Lead Time: {{ $req->sla_days }}d | Preferred: {{ $req->preferred_date ? \Carbon\Carbon::parse($req->preferred_date)->format('M j, Y') : 'N/A' }}">
                                <span>⏰</span>
                                <span>Due Soon ({{ $req->slack_days === 0 ? 'Today' : '1d slack' }})</span>
                            </div>
                        @else
                            <div class="sla-chip sla-chip--ok" title="SLA Lead Time: {{ $req->sla_days }}d | Preferred: {{ $req->preferred_date ? \Carbon\Carbon::parse($req->preferred_date)->format('M j, Y') : 'N/A' }}">
                                <span>✓</span>
                                <span>{{ $req->slack_days }}d slack</span>
                            </div>
                        @endif
                        <div style="font-size:10px;color:var(--ink-faint);margin-top:2px;">Lead time: {{ $req->sla_days }}d</div>
                    </td>
                    <td><span class="badge badge--{{ $sc }}">{{ $req->status }}</span></td>
                    <td class="date-text">{{ $req->created_at->format('M j, Y') }}</td>
                    <td>
                        <div class="action-wrap">
                            <a href="{{ route('admin.requests.show', $req->id) }}" class="btn-chat" style="text-decoration:none;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;font-weight:600;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                View
                            </a>
                            <form method="POST" action="{{ route('admin.requests.status') }}" style="display:flex;align-items:center;gap:5px;" onsubmit="confirmStatus(event, this)">
                                @csrf
                                <input type="hidden" name="request_id" value="{{ $req->id }}">
                                <select class="status-select" name="status">
                                    @foreach(['Pending Review','Under Review','Approved','Posted','Rejected'] as $s)
                                        <option value="{{ $s }}" {{ $req->status===$s?'selected':'' }}>{{ $s }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn-save">Save</button>
                            </form>
                            <button class="btn-chat" onclick="openChat({{ $req->id }},{{ json_encode($req->title) }},{{ json_encode($req->status) }},{{ json_encode($req->requester) }})">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                Chat @if($cc>0)<span class="chat-count">{{ $cc }}</span>@endif
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7"><div class="empty-state">No requests found.</div></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    @if($requests->hasPages())
    <div class="pagination-wrap">
        <div class="pagination-info">
            Page <strong>{{ $requests->currentPage() }}</strong> of <strong>{{ $requests->lastPage() }}</strong>
        </div>
        <div class="pagination-btns">
            {{-- Prev --}}
            @if($requests->onFirstPage())
                <span class="pg-btn pg-btn--disabled">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                </span>
            @else
                <a href="{{ $requests->previousPageUrl() }}" class="pg-btn">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
            @endif

            {{-- Page numbers --}}
            @php
                $start = max(1, $requests->currentPage() - 2);
                $end   = min($requests->lastPage(), $requests->currentPage() + 2);
            @endphp
            @if($start > 1)
                <a href="{{ $requests->url(1) }}" class="pg-btn">1</a>
                @if($start > 2)<span class="pg-btn pg-btn--disabled">…</span>@endif
            @endif
            @for($p = $start; $p <= $end; $p++)
                <a href="{{ $requests->url($p) }}" class="pg-btn {{ $p === $requests->currentPage() ? 'pg-btn--active' : '' }}">{{ $p }}</a>
            @endfor
            @if($end < $requests->lastPage())
                @if($end < $requests->lastPage() - 1)<span class="pg-btn pg-btn--disabled">…</span>@endif
                <a href="{{ $requests->url($requests->lastPage()) }}" class="pg-btn">{{ $requests->lastPage() }}</a>
            @endif

            {{-- Next --}}
            @if($requests->hasMorePages())
                <a href="{{ $requests->nextPageUrl() }}" class="pg-btn">
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

<div class="panel-overlay" id="panel-overlay" onclick="closeChat()"></div>
<div class="chat-panel" id="chat-panel">
    <div class="chat-panel__head">
        <div class="chat-panel__head-left">
            <div class="chat-panel__icon"><svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
            <div><div class="chat-panel__title">Chat with Requestor</div><div class="chat-panel__sub" id="panel-requester-name">—</div></div>
        </div>
        <button class="chat-panel__close" onclick="closeChat()"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="chat-panel__info">
        <div class="chat-panel__req-title" id="panel-req-title">—</div>
        <div class="chat-panel__req-meta" id="panel-req-status"></div>
    </div>
    <div class="chat-panel__messages" id="chat-messages">
        <div class="chat-empty-state">
            <svg width="36" height="36" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <p>No messages yet.</p>
        </div>
    </div>
    <div class="chat-panel__form">
        <textarea class="chat-panel__textarea" id="chat-input" placeholder="Type a message… (Enter to send)" onkeydown="handleKey(event)"></textarea>
        <button class="chat-panel__send" onclick="sendMessage()">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Send Message
        </button>
    </div>
</div>

@if(session('success'))<div class="toast toast--success" id="toast"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>{{ session('success') }}</div>@endif
@if(session('error'))<div class="toast toast--reject" id="toast"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>{{ session('error') }}</div>@endif
@endsection

@section('scripts')
<script>
// ── LIVE SEARCH ─────────────────────────────────────────────────
let searchTimer = null;
const searchInput   = document.getElementById('search-input');
const searchSpinner = document.getElementById('search-spinner');
const filterForm    = document.getElementById('filter-form');

searchInput.addEventListener('input', function () {
    clearTimeout(searchTimer);
    const val = this.value.trim();
    searchSpinner.classList.add('visible');
    searchTimer = setTimeout(() => {
        filterForm.submit();
    }, 400); // 400ms debounce — submits after user stops typing
});

// Stop spinner on form submit
filterForm.addEventListener('submit', function () {
    searchSpinner.classList.add('visible');
});

// ── CHAT PANEL ──────────────────────────────────────────────────
let cid = null;
let chatPollTimer = null;
function openChat(reqId, title, status, requester) {
    cid = reqId;
    document.getElementById('panel-req-title').textContent    = title;
    document.getElementById('panel-req-status').textContent   = status;
    document.getElementById('panel-requester-name').textContent = requester;
    document.getElementById('chat-panel').classList.add('open');
    document.getElementById('panel-overlay').classList.add('open');
    document.body.style.overflow = 'hidden';
    loadMessages(reqId);
    clearInterval(chatPollTimer);
    chatPollTimer = setInterval(() => {
        if (cid) loadMessages(cid, true);
    }, 2000);
}
function closeChat() {
    clearInterval(chatPollTimer);
    document.getElementById('chat-panel').classList.remove('open');
    document.getElementById('panel-overlay').classList.remove('open');
    document.body.style.overflow = '';
    cid = null;
}
function loadMessages(reqId, silent = false) {
    const box = document.getElementById('chat-messages');
    if (!silent) {
        box.innerHTML = '<div class="chat-empty-state"><p>Loading…</p></div>';
    }
    fetch(`/admin/requests/${reqId}/comments`, {
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const msgs = data.comments || [];
        if (!msgs.length) {
            box.innerHTML = '<div class="chat-empty-state"><svg width="36" height="36" fill="none" stroke="rgba(0,0,0,0.15)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><p>No messages yet!</p></div>';
            return;
        }
        const rendered = msgs.map(c => {
            const a = c.sender_role === 'admin';
            return `<div class="bubble-row ${a ? 'bubble-row--admin' : ''}">
                <div class="bubble-av ${a ? 'bubble-av--admin' : 'bubble-av--requestor'}">${esc(c.sender_name.charAt(0).toUpperCase())}</div>
                <div class="bubble ${a ? 'bubble--admin' : 'bubble--requestor'}">
                    <div class="bubble-text">${esc(c.message)}</div>
                    <div class="bubble-meta">${a ? 'Admin' : esc(c.sender_name)} · ${esc(c.created_at)}</div>
                </div>
            </div>`;
        }).join('');
        if (box.innerHTML !== rendered) {
            const shouldScroll = box.scrollTop + box.clientHeight >= box.scrollHeight - 60 || !silent;
            box.innerHTML = rendered;
            if (shouldScroll) box.scrollTop = box.scrollHeight;
        }
    })
    .catch(() => { if (!silent) box.innerHTML = '<div class="chat-empty-state"><p>Could not load messages.</p></div>'; });
}
let isSendingAdminChat = false;
async function sendMessage() {
    if (isSendingAdminChat) return;
    const input = document.getElementById('chat-input');
    const msg   = input.value.trim();
    if (!msg || !cid) return;
    const btn = document.querySelector('.chat-panel__send');
    isSendingAdminChat = true;
    if (btn) btn.disabled = true;
    try {
        const res = await fetch('/admin/requests/comment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ request_id: cid, message: msg })
        });
        const d = await res.json();
        if (d.success) { input.value = ''; loadMessages(cid); }
        else alert('Failed: ' + (d.message || 'Unknown error'));
    } catch(e) { alert('Connection error.'); }
    finally {
        isSendingAdminChat = false;
        if (btn) btn.disabled = false;
    }
}
function handleKey(e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } }

function confirmStatus(e, f) {
    e.preventDefault();
    const status = f.querySelector('select[name="status"]').value;
    
    Swal.fire({
        title: 'Update Status?',
        html: `Are you sure you want to change the status to <strong>${status}</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#002366',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, update it',
        cancelButtonText: 'Cancel',
        customClass: {
            popup: 'rounded-xl',
            confirmButton: 'rounded-lg px-4 py-2 font-semibold',
            cancelButton: 'rounded-lg px-4 py-2 font-semibold'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            f.submit();
        }
    });
}

function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;'); }
setTimeout(() => document.getElementById('toast')?.remove(), 3000);
</script>
@endsection