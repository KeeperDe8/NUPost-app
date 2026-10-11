@extends('layouts.admin')
@section('title', 'Visualization')

@section('head-styles')
<style>
.page-hd { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; }
.page-hd__title { font-family:var(--font-disp); font-size:22px; color:var(--ink); }
.page-hd__sub   { font-size:13px; color:var(--ink-soft); margin-top:3px; }

/* VISUALIZATION SEARCH BAR */
.vis-search-wrap {
    margin: 0 0 24px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--card);
    border: 1.5px solid var(--card-border);
    border-radius: var(--radius);
    padding: 10px 18px;
    box-shadow: var(--shadow-sm);
    transition: all .2s cubic-bezier(0.16, 1, 0.3, 1);
}
.vis-search-wrap:focus-within {
    border-color: var(--navy);
    box-shadow: 0 0 0 3.5px rgba(0, 35, 102, 0.1);
    transform: translateY(-1px);
}
.vis-search-icon {
    color: var(--ink-faint);
    display: flex;
    align-items: center;
    flex-shrink: 0;
    transition: color .15s;
}
.vis-search-wrap:focus-within .vis-search-icon {
    color: var(--navy);
}
.vis-search-input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 13.5px;
    font-family: var(--font);
    color: var(--ink);
    outline: none;
    padding: 0 4px;
}
.vis-search-input::placeholder {
    color: var(--ink-faint);
}
.vis-search-clear {
    background: none;
    border: none;
    color: var(--ink-faint);
    cursor: pointer;
    font-size: 13px;
    padding: 4px 8px;
    border-radius: 6px;
    display: none;
    align-items: center;
    justify-content: center;
    transition: all .15s;
}
.vis-search-clear:hover {
    background: var(--cream-dark);
    color: var(--ink);
}
.vis-search-clear.visible {
    display: flex;
}
.vis-search-pill {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--navy);
    background: var(--navy-pale);
    border: 1px solid rgba(0, 35, 102, 0.12);
    padding: 4px 11px;
    border-radius: 20px;
    display: none;
    align-items: center;
    gap: 5px;
    flex-shrink: 0;
}
/* STAT CARDS */
.stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px; }
.scard {
    background:linear-gradient(135deg,#001a4d 0%,#002366 60%,#002e7a 100%);
    border-radius:var(--radius); padding:20px 18px;
    box-shadow:0 4px 18px rgba(0,26,77,0.22);
    transition:transform .2s,box-shadow .2s;
    position:relative; overflow:hidden;
}
.scard::before {
    content:''; position:absolute; top:-30px; right:-30px;
    width:110px; height:110px; border-radius:50%;
    background:radial-gradient(circle,rgba(255,255,255,0.07) 0%,transparent 70%);
    pointer-events:none;
}
.scard:hover { transform:translateY(-3px); box-shadow:0 8px 28px rgba(0,26,77,0.3); }
.scard__top { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
.scard__icon { width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; }
.scard__trend { font-size:11px; font-weight:700; padding:3px 9px; border-radius:20px; background:rgba(255,255,255,0.12); color:rgba(255,255,255,0.85); }
.scard__num { font-size:28px; font-weight:800; letter-spacing:-1px; line-height:1; margin-bottom:5px; color:white; }
.scard__lbl { font-size:12px; color:rgba(255,255,255,0.6); font-weight:500; }

/* PANELS */
.panel-a { background:var(--card); border:1.5px solid var(--card-border); border-radius:var(--radius); box-shadow:var(--shadow-sm); overflow:hidden; margin-bottom:18px; }
.pa-head { padding:18px 22px 14px; display:flex; align-items:flex-start; justify-content:space-between; border-bottom:1.5px solid var(--card-border); }
.pa-title { font-family:var(--font-disp); font-size:17px; color:var(--ink); }
.pa-sub   { font-size:11.5px; color:var(--ink-soft); margin-top:2px; }
.pa-body  { padding:22px; }

.two-col { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px; }

/* CHART */
.chart-wrap { position:relative; height:240px; }

/* PLATFORM BARS */
.platform-list { display:flex; flex-direction:column; gap:13px; }
.platform-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:5px; }
.platform-left { display:flex; align-items:center; gap:9px; font-size:13px; font-weight:600; color:var(--ink); }
.platform-icon { width:28px; height:28px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; }
.platform-val { font-size:13px; font-weight:700; color:var(--ink); }
.pbar-bg { height:8px; background:var(--cream-dark); border-radius:4px; overflow:hidden; }
.pbar-fill { height:100%; border-radius:4px; }

/* CATEGORY PILLS */
.cat-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.cat-pill { background:var(--cream-dark); border-radius:12px; padding:12px 14px; display:flex; align-items:center; gap:10px; transition:transform .15s; }
.cat-pill:hover { transform:scale(1.02); }
.cat-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }

/* TOP REQUESTORS */
.trop-list { display:flex; flex-direction:column; gap:12px; }
.trop-item { display:flex; align-items:center; gap:11px; }
.trop-av { width:36px; height:36px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; color:white; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.trop-av img { width:100%; height:100%; object-fit:cover; }
.trop-name { font-size:13px; font-weight:600; color:var(--ink); }
.trop-dept { font-size:10.5px; color:var(--ink-soft); margin-top:1px; }
.trop-bar-bg { height:6px; background:var(--cream-dark); border-radius:3px; overflow:hidden; }
.trop-bar-fill { height:100%; border-radius:3px; }
.trop-count { font-size:13px; font-weight:700; color:var(--navy); width:24px; text-align:right; flex-shrink:0; }

/* STATUS SUMMARY */
.status-list { display:flex; flex-direction:column; gap:9px; }

/* RECENT TABLE */
.rtable { width:100%; border-collapse:collapse; }
.rtable th { padding:9px 16px; text-align:left; font-size:10px; font-weight:700; color:var(--ink-soft); text-transform:uppercase; letter-spacing:0.7px; border-bottom:1.5px solid var(--card-border); background:var(--cream-dark); }
.rtable td { padding:12px 16px; font-size:12.5px; border-bottom:1px solid var(--cream-dark); vertical-align:middle; }
.rtable tbody tr:last-child td { border-bottom:none; }
.rtable tbody tr:hover { background:var(--cream); cursor:pointer; }

/* ═══════════════════════════════════════════════════
   FACEBOOK SECTION — redesigned
═══════════════════════════════════════════════════ */
.fb-divider { display:flex; align-items:center; gap:14px; margin:28px 0 18px; }
.fb-divider__line  { flex:1; height:1.5px; background:var(--card-border); }
.fb-divider__label { font-size:11px; font-weight:700; letter-spacing:1.2px; text-transform:uppercase; color:var(--ink-faint); white-space:nowrap; }

/* Page banner */
.fb-banner {
    display:flex; align-items:center; gap:16px;
    background:var(--navy-pale); border:1.5px solid rgba(0,35,102,0.15);
    border-radius:var(--radius); padding:20px 24px;
}
.fb-banner__icon { width:48px; height:48px; background:var(--navy); border-radius:13px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.fb-banner__title { font-size:15px; font-weight:700; color:var(--navy); margin-bottom:3px; }
.fb-banner__sub   { font-size:12.5px; color:var(--navy-light); }
.fb-badge { font-size:10px; font-weight:700; padding:3px 10px; border-radius:20px; background:var(--navy); color:white; margin-top:6px; display:inline-block; }

/* Month selector + CSV row */
.fb-controls-row {
    display:flex; align-items:center; justify-content:space-between;
    gap:14px; flex-wrap:wrap; margin-bottom:18px;
}
.fb-month-group {
    display:flex; align-items:center; gap:10px;
}
.fb-month-label {
    font-size:11px; font-weight:700; text-transform:uppercase;
    letter-spacing:0.8px; color:var(--ink-faint); white-space:nowrap;
}
/* Dropdown styled */
.fb-month-select {
    appearance:none; -webkit-appearance:none;
    background:var(--card) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 12px center;
    border:1.5px solid var(--card-border);
    border-radius:10px; padding:9px 36px 9px 14px;
    font-size:13px; font-weight:600; color:var(--ink);
    cursor:pointer; transition:border-color .18s, box-shadow .18s;
    min-width:160px;
}
.fb-month-select:focus {
    outline:none; border-color:var(--navy);
    box-shadow:0 0 0 3px rgba(0,35,102,0.1);
}
.fb-month-btn {
    display:flex; align-items:center; gap:6px;
    padding:9px 16px; border-radius:10px; font-size:12.5px;
    font-weight:600; cursor:pointer; border:none; transition:all .18s;
}
.fb-month-btn--go {
    background:var(--navy); color:white;
    box-shadow:0 2px 8px rgba(0,35,102,0.25);
}
.fb-month-btn--go:hover { background:#001a4d; }
.fb-month-btn--csv {
    background:var(--cream-dark); color:var(--ink);
    border:1.5px solid var(--card-border);
    text-decoration:none;
}
.fb-month-btn--csv:hover { background:#e8e3da; }

/* Metric cards grid — 3 cols, 2 rows */
.fb-metric-grid {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:14px; margin-bottom:18px;
}
.fb-mcard {
    background:var(--card); border:1.5px solid var(--card-border);
    border-radius:var(--radius); padding:20px 18px;
    display:flex; flex-direction:column; gap:8px;
    box-shadow:var(--shadow-sm); transition:transform .18s,box-shadow .18s;
    position:relative; overflow:hidden;
}
.fb-mcard:hover { transform:translateY(-2px); box-shadow:0 6px 22px rgba(0,0,0,0.08); }
.fb-mcard__icon-wrap {
    width:42px; height:42px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
}
.fb-mcard__label { font-size:11.5px; font-weight:600; color:var(--ink-soft); }
.fb-mcard__value {
    font-size:30px; font-weight:800; letter-spacing:-1.5px;
    line-height:1; color:var(--ink);
}
.fb-mcard__change {
    font-size:11px; font-weight:600; padding:2px 8px;
    border-radius:20px; display:inline-block; width:fit-content;
}
/* Accent bar at bottom of card */
.fb-mcard::after {
    content:''; position:absolute; bottom:0; left:0; right:0; height:3px;
}

/* Enhanced chart panel */
.fb-chart-panel {
    background:var(--card); border:1.5px solid var(--card-border);
    border-radius:var(--radius); box-shadow:var(--shadow-sm);
    overflow:hidden; margin-bottom:18px;
}
.fb-chart-head {
    padding:18px 22px 0;
    display:flex; align-items:flex-start; justify-content:space-between;
}
.fb-chart-legend {
    display:flex; align-items:center; gap:16px;
    padding: 14px 22px;
}
.fb-legend-dot {
    width:10px; height:10px; border-radius:3px; flex-shrink:0;
}
.fb-chart-body { padding:0 16px 20px; }
.fb-chart-wrap { position:relative; height:280px; }

/* Toggle buttons for chart datasets */
.fb-chart-toggles {
    display:flex; gap:0; background:var(--cream-dark); border-radius:8px;
    padding:3px; border:1px solid rgba(0,0,0,0.06);
}
.fb-ctog {
    padding:5px 14px; border-radius:6px; font-size:11.5px; font-weight:600;
    color:var(--ink-soft); cursor:pointer; transition:all .15s; border:none; background:transparent;
}
.fb-ctog.active { background:white; color:var(--ink); box-shadow:0 1px 4px rgba(0,0,0,0.1); }

/* Posts table */
.fb-post-thumb {
    width:44px; height:44px; border-radius:10px; object-fit:cover; flex-shrink:0;
}
.fb-post-no-thumb {
    width:44px; height:44px; border-radius:10px; background:var(--cream-dark);
    display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0;
}
.fb-metric-pill {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 10px; border-radius:20px; font-size:12px; font-weight:700;
}

/* Empty state */
.fb-empty {
    text-align:center; padding:48px 20px;
    display:flex; flex-direction:column; align-items:center; gap:10px;
}
.fb-empty__icon {
    width:56px; height:56px; border-radius:16px; background:var(--cream-dark);
    display:flex; align-items:center; justify-content:center; font-size:24px;
    margin-bottom:4px;
}
.fb-empty__title { font-size:14px; font-weight:700; color:var(--ink); }
.fb-empty__sub   { font-size:12.5px; color:var(--ink-faint); max-width:280px; line-height:1.5; }

/* Placeholder cards when not configured */
.fb-ph-card {
    background:var(--card); border:1.5px solid var(--card-border);
    border-radius:var(--radius); padding:18px;
    display:flex; flex-direction:column; gap:10px; box-shadow:var(--shadow-sm);
}
.fb-ph-block {
    flex:1; background:var(--cream-dark); border-radius:10px;
    border:1.5px dashed #93c5fd; min-height:80px;
    display:flex; flex-direction:column; align-items:center; justify-content:center; gap:5px;
}
.fb-ph-block p { font-size:10.5px; color:#93c5fd; font-weight:500; }
</style>
@endsection

@section('content')
@php
    $total    = \App\Models\PostRequest::count();
    $posted   = \App\Models\PostRequest::where('status','Posted')->count();
    $approved = \App\Models\PostRequest::where('status','Approved')->count();
    $pending  = \App\Models\PostRequest::where('status','Pending Review')->count();
    $rejected = \App\Models\PostRequest::where('status','Rejected')->count();
    $review   = \App\Models\PostRequest::where('status','Under Review')->count();
    $users    = \App\Models\User::count();
    $approval_rate = $total > 0 ? round(($approved + $posted) / $total * 100) : 0;
    $safe = max($total, 1);

    $months_data = [];
    for ($i = 5; $i >= 0; $i--) {
        $m = now()->subMonths($i);
        $months_data[] = [
            'label'    => $m->format('M'),
            'total'    => \App\Models\PostRequest::whereYear('created_at',$m->year)->whereMonth('created_at',$m->month)->count(),
            'posted'   => \App\Models\PostRequest::whereYear('created_at',$m->year)->whereMonth('created_at',$m->month)->where('status','Posted')->count(),
            'approved' => \App\Models\PostRequest::whereYear('created_at',$m->year)->whereMonth('created_at',$m->month)->where('status','Approved')->count(),
        ];
    }

    $platforms_raw   = \App\Models\PostRequest::all();
    $platform_counts = [];
    foreach ($platforms_raw as $req) {
        foreach ($req->platforms_array ?? [] as $p) {
            $platform_counts[$p] = ($platform_counts[$p] ?? 0) + 1;
        }
    }
    arsort($platform_counts);
    $plat_max    = max($platform_counts ?: [1]);
    $plat_colors = [
        'Facebook'  => ['#1877f2','📘'],
        'Instagram' => ['#e1306c','📸'],
        'Youtube'   => ['#ff0000','📺'],
        'TikTok'    => ['#010101','🎵'],
        'Twitter'   => ['#1da1f2','🐦'],
        'LinkedIn'  => ['#0077b5','💼'],
    ];

    $categories = \App\Models\PostRequest::selectRaw('category,count(*) as count')->groupBy('category')->orderByDesc('count')->get();
    $cat_max    = $categories->max('count') ?: 1;
    $cat_colors = ['#2563eb','#10b981','#f59e0b','#8b5cf6','#ef4444','#0ea5e9','#f97316'];

    $top_requestors = \App\Models\PostRequest::selectRaw('requester,count(*) as total')->groupBy('requester')->orderByDesc('total')->limit(5)->get();
    $top_users      = \App\Models\User::whereIn('name', $top_requestors->pluck('requester'))->get()->keyBy('name');
    $tr_max         = $top_requestors->max('total') ?: 1;
    $av_colors      = ['#2563eb','#10b981','#8b5cf6','#f59e0b','#ef4444'];

    $recent_posted  = \App\Models\PostRequest::whereIn('status',['Posted','Approved'])->orderByDesc('created_at')->limit(8)->get();

    $fb_configured = !empty(env('FB_PAGE_ID')) && !empty(env('FB_PAGE_ACCESS_TOKEN'));

    // FB month context
    $selectedMonth   = $fb['selected_month']   ?? now()->format('Y-m');
    $monthLabel      = $fb['month_label']       ?? now()->format('F Y');
    $availableMonths = $fb['available_months']  ?? [];
@endphp

<div class="page-hd">
    <div>
        <div class="page-hd__title">Visualization</div>
        <div class="page-hd__sub">Request trends, platform breakdown & performance overview</div>
    </div>
    @if($fb_configured)
    <a href="{{ route('admin.analytics.refresh', ['month' => $selectedMonth]) }}"
       style="display:flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;color:var(--navy);background:var(--navy-pale);border:1.5px solid rgba(0,35,102,0.15);padding:8px 16px;border-radius:10px;text-decoration:none;transition:all .15s;">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
        Refresh FB Data
    </a>
    @endif
</div>

@if(session('success'))
    <div style="background:#dcfce7;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:500;color:#15803d;margin-bottom:16px;">{{ session('success') }}</div>
@endif

{{-- LIVE SEARCH BAR WITH PROPER SPACING --}}
<div class="vis-search-wrap" id="vis-search-wrap">
    <span class="vis-search-icon">
        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
    </span>
    <input type="text"
           id="vis-search-input"
           class="vis-search-input"
           placeholder="Search visualization data, published requests, categories, platforms..."
           autocomplete="off">
    <span class="vis-search-pill" id="vis-search-pill">
        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        <span id="vis-search-pill-text">0 matches</span>
    </span>
    <button type="button" class="vis-search-clear" id="vis-search-clear" onclick="clearVisSearch()" title="Clear search">✕</button>
</div>
{{-- STAT CARDS --}}
<div class="stats-row">
    <div class="scard" style="border-bottom:3px solid #60a5fa;">
        <div class="scard__top">
            <div class="scard__icon" style="background:rgba(255,255,255,0.12);">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <span class="scard__trend">All</span>
        </div>
        <div class="scard__num">{{ $total }}</div>
        <div class="scard__lbl">Total Requests</div>
    </div>
    <div class="scard" style="border-bottom:3px solid #c4b5fd;">
        <div class="scard__top">
            <div class="scard__icon" style="background:rgba(139,92,246,0.25);">
                <svg width="18" height="18" fill="none" stroke="#c4b5fd" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </div>
            <span class="scard__trend">Live</span>
        </div>
        <div class="scard__num">{{ $posted }}</div>
        <div class="scard__lbl">Published Posts</div>
    </div>
    <div class="scard" style="border-bottom:3px solid #6ee7b7;">
        <div class="scard__top">
            <div class="scard__icon" style="background:rgba(16,185,129,0.25);">
                <svg width="18" height="18" fill="none" stroke="#6ee7b7" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            </div>
            <span class="scard__trend">{{ $approval_rate }}%</span>
        </div>
        <div class="scard__num">{{ $approval_rate }}%</div>
        <div class="scard__lbl">Approval Rate</div>
    </div>
    <div class="scard" style="border-bottom:3px solid #fbbf24;">
        <div class="scard__top">
            <div class="scard__icon" style="background:rgba(245,158,11,0.25);">
                <svg width="18" height="18" fill="none" stroke="#fbbf24" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <span class="scard__trend">Active</span>
        </div>
        <div class="scard__num">{{ $users }}</div>
        <div class="scard__lbl">Requestors</div>
    </div>
</div>

{{-- TREND CHART --}}
<div class="panel-a">
    <div class="pa-head">
        <div>
            <div class="pa-title">Request Volume Trend</div>
            <div class="pa-sub">Total submitted vs approved/posted — last 6 months</div>
        </div>
    </div>
    <div class="pa-body">
        <div class="chart-wrap"><canvas id="trendChart"></canvas></div>
    </div>
</div>

{{-- PLATFORM + CATEGORIES --}}
<div class="two-col">
    <div class="panel-a" style="margin-bottom:0;">
        <div class="pa-head"><div class="pa-title">Platform Breakdown</div></div>
        <div class="pa-body">
            @if(empty($platform_counts))
                <div style="text-align:center;color:var(--ink-faint);padding:20px;font-size:13px;">No platform data yet.</div>
            @else
            <div class="platform-list">
                @foreach($platform_counts as $plat => $cnt)
                @php $pct=round($cnt/$plat_max*100); $col=$plat_colors[$plat][0]??'#6b7280'; $ico=$plat_colors[$plat][1]??'🌐'; @endphp
                <div>
                    <div class="platform-top">
                        <div class="platform-left">
                            <span class="platform-icon" style="background:{{ $col }}22;">{{ $ico }}</span>
                            {{ $plat }}
                        </div>
                        <span class="platform-val">{{ $cnt }}</span>
                    </div>
                    <div class="pbar-bg"><div class="pbar-fill" style="width:{{ $pct }}%;background:{{ $col }};"></div></div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    <div class="panel-a" style="margin-bottom:0;">
        <div class="pa-head"><div class="pa-title">Category Distribution</div></div>
        <div class="pa-body">
            @if($categories->isEmpty())
                <div style="text-align:center;color:var(--ink-faint);padding:20px;font-size:13px;">No data yet.</div>
            @else
            <div class="cat-grid">
                @foreach($categories as $i => $cat)
                @php $col = $cat_colors[$i % count($cat_colors)]; @endphp
                <div class="cat-pill">
                    <span class="cat-dot" style="background:{{ $col }};"></span>
                    <span style="font-size:12.5px;font-weight:600;color:var(--ink);flex:1;">{{ Str::limit($cat->category ?? 'Other', 12) }}</span>
                    <span style="font-size:13px;font-weight:700;color:var(--ink);">{{ $cat->count }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

{{-- TOP REQUESTORS + STATUS --}}
<div class="two-col">
    <div class="panel-a" style="margin-bottom:0;">
        <div class="pa-head">
            <div><div class="pa-title">Top Requestors</div><div class="pa-sub">By total submissions</div></div>
        </div>
        <div class="pa-body">
            <div class="trop-list">
                @forelse($top_requestors as $i => $tr)
                @php
                    $medals = ['🥇','🥈','🥉'];
                    $uobj   = $top_users[$tr->requester] ?? null;
                    $photo  = $uobj?->profile_photo;
                    $dept   = $uobj?->department ?? ($uobj?->organization ?? 'Requestor');
                    $pct    = round($tr->total / $tr_max * 100);
                    $col    = $av_colors[$i % 5];
                @endphp
                <div class="trop-item">
                    <div style="width:24px;text-align:center;font-size:15px;">{{ $medals[$i] ?? ($i+1).'.' }}</div>
                    <div class="trop-av" style="background:{{ $col }};">
                        @if($photo)<img src="/uploads/{{ $photo }}" alt="">@endif
                        {{ strtoupper(substr($tr->requester, 0, 1)) }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div class="trop-name">{{ Str::limit($tr->requester, 18) }}</div>
                        <div class="trop-dept">{{ Str::limit($dept, 20) }}</div>
                    </div>
                    <div style="flex:1;">
                        <div class="trop-bar-bg"><div class="trop-bar-fill" style="width:{{ $pct }}%;background:{{ $col }};"></div></div>
                    </div>
                    <div class="trop-count">{{ $tr->total }}</div>
                </div>
                @empty
                    <div style="text-align:center;color:var(--ink-faint);font-size:13px;padding:20px;">No data yet.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="panel-a" style="margin-bottom:0;">
        <div class="pa-head"><div><div class="pa-title">Status Summary</div></div></div>
        <div class="pa-body">
            @php
                $st_items = [
                    ['l'=>'Pending Review','v'=>$pending, 'pct'=>round($pending/$safe*100), 'c'=>'#f59e0b'],
                    ['l'=>'Under Review',  'v'=>$review,  'pct'=>round($review/$safe*100),  'c'=>'#1e4fd8'],
                    ['l'=>'Approved',      'v'=>$approved,'pct'=>round($approved/$safe*100),'c'=>'#10b981'],
                    ['l'=>'Posted',        'v'=>$posted,  'pct'=>round($posted/$safe*100),  'c'=>'#8b5cf6'],
                    ['l'=>'Rejected',      'v'=>$rejected,'pct'=>round($rejected/$safe*100),'c'=>'#ef4444'],
                ];
            @endphp
            <div class="status-list">
                @foreach($st_items as $si)
                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                        <div style="display:flex;align-items:center;gap:7px;font-size:12.5px;color:var(--ink-mid);">
                            <span style="width:8px;height:8px;border-radius:2px;background:{{ $si['c'] }};display:inline-block;"></span>
                            {{ $si['l'] }}
                        </div>
                        <span style="font-size:12.5px;font-weight:700;color:var(--ink);">{{ $si['v'] }} <span style="font-weight:400;color:var(--ink-faint);">({{ $si['pct'] }}%)</span></span>
                    </div>
                    <div style="height:6px;background:var(--cream-dark);border-radius:3px;overflow:hidden;">
                        <div style="width:{{ $si['pct'] }}%;height:100%;background:{{ $si['c'] }};border-radius:3px;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- RECENT POSTED --}}
<div class="panel-a">
    <div class="pa-head">
        <div><div class="pa-title">Recent Published Requests</div><div class="pa-sub">Latest approved & posted</div></div>
    </div>
    <div style="overflow-x:auto;">
        <table class="rtable" id="vis-recent-table">
            <thead>
                <tr><th>Request</th><th>Requestor</th><th>Category</th><th>Platforms</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody>
            @forelse($recent_posted as $req)
            @php
                $sl    = strtolower($req->status);
                $sc    = str_contains($sl,'posted') ? 'posted' : 'approved';
                $plats = $req->platforms_array ?? [];
            @endphp
            <tr onclick="window.location='{{ route('admin.requests.show',$req->id) }}'">
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        @if($req->first_media)
                            <img src="/uploads/{{ $req->first_media }}" style="width:36px;height:36px;border-radius:8px;object-fit:cover;" onerror="this.style.display='none'">
                        @else
                            <div style="width:36px;height:36px;border-radius:8px;background:var(--cream-dark);display:flex;align-items:center;justify-content:center;font-size:14px;">📄</div>
                        @endif
                        <div>
                            <div style="font-weight:600;font-size:12.5px;color:var(--ink);">{{ Str::limit($req->title,28) }}</div>
                            <div style="font-size:11px;color:var(--ink-soft);">{{ Str::limit($req->description,38) }}</div>
                        </div>
                    </div>
                </td>
                <td style="font-size:12.5px;">{{ $req->requester }}</td>
                <td style="font-size:12px;color:var(--ink-soft);">{{ $req->category }}</td>
                <td>
                    <div style="display:flex;gap:4px;flex-wrap:wrap;">
                        @foreach(array_slice($plats,0,2) as $p)
                            <span style="font-size:10px;padding:2px 7px;background:var(--navy-pale);color:var(--navy);border-radius:10px;font-weight:600;">{{ $p }}</span>
                        @endforeach
                    </div>
                </td>
                <td><span class="badge badge--{{ $sc }}">{{ $req->status }}</span></td>
                <td style="font-size:11.5px;color:var(--ink-soft);">{{ $req->created_at->format('M j, Y') }}</td>
            </tr>
            @empty
                <tr><td colspan="6" style="padding:32px;text-align:center;color:var(--ink-faint);">No published posts yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════════════════════════════════════════
     FACEBOOK / META SECTION
════════════════════════════════════════════════ --}}
<div class="fb-divider">
    <div class="fb-divider__line"></div>
    <div class="fb-divider__label">📘 Facebook & Meta Visualization</div>
    <div class="fb-divider__line"></div>
</div>

@if($fb_configured && isset($fb))

    @if(!empty($fb['error']))
        <div style="background:#fee2e2;border:1.5px solid #fca5a5;border-radius:14px;padding:16px 20px;font-size:13px;color:#b91c1c;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div>
                <strong>Facebook API Error</strong> — {{ $fb['error'] }}<br>
                <small style="opacity:.7;">Check your FB_PAGE_ACCESS_TOKEN in .env</small>
            </div>
        </div>
    @else

        {{-- PAGE BANNER --}}
        <div class="fb-banner" style="margin-bottom:18px;">
            <div class="fb-banner__icon">
                <svg width="24" height="24" fill="white" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
            </div>
            <div style="flex:1;">
                <div class="fb-banner__title">
                    @if(!empty($fb['pageInfo']['link']))
                        <a href="{{ $fb['pageInfo']['link'] }}" target="_blank" style="color:inherit;text-decoration:none;display:inline-flex;align-items:center;gap:6px;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                            {{ $fb['pageInfo']['name'] ?? 'Facebook Page' }}
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                        </a>
                    @else
                        {{ $fb['pageInfo']['name'] ?? 'Facebook Page' }}
                    @endif
                </div>
                <div class="fb-banner__sub">
                    Likes: <strong>{{ number_format($fb['pageInfo']['fan_count'] ?? 0) }}</strong>
                    &nbsp;·&nbsp;
                    Followers: <strong>{{ number_format($fb['pageInfo']['followers_count'] ?? 0) }}</strong>
                </div>
                <span class="fb-badge">✅ Connected</span>
            </div>
        </div>

        {{-- MONTH SELECTOR + CSV --}}
        <div class="fb-controls-row">
            <form method="GET" action="{{ route('admin.analytics') }}" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span class="fb-month-label">View Month</span>
                <select name="month" class="fb-month-select" onchange="this.form.submit()">
                    @foreach($availableMonths as $am)
                        <option value="{{ $am['value'] }}" {{ $selectedMonth === $am['value'] ? 'selected' : '' }}>
                            {{ $am['label'] }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="fb-month-btn fb-month-btn--go">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    Go
                </button>
            </form>

            <a href="{{ route('admin.analytics.export', ['month' => $selectedMonth]) }}"
               class="fb-month-btn fb-month-btn--csv">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Download CSV — {{ $monthLabel }}
            </a>
        </div>

        @if(!empty($fb['metrics']))

        {{-- METRIC CARDS (2 rows × 3 cols) --}}
        @php
        $fbMetricDefs = [
            [
                'key'   => 'total_reach',
                'label' => 'Total Reach',
                'icon'  => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
                'color' => '#1877f2',
                'bg'    => '#dbeafe',
                'bar'   => '#1877f2',
            ],
            [
                'key'   => 'total_engagement',
                'label' => 'Engagements',
                'icon'  => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
                'color' => '#e1306c',
                'bg'    => '#fce7f3',
                'bar'   => '#e1306c',
            ],
            [
                'key'   => 'total_likes',
                'label' => 'Reactions',
                'icon'  => '<circle cx="12" cy="12" r="10"/><path d="M8 13s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>',
                'color' => '#f59e0b',
                'bg'    => '#fef3c7',
                'bar'   => '#f59e0b',
            ],
            [
                'key'   => 'total_comments',
                'label' => 'Comments',
                'icon'  => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
                'color' => '#10b981',
                'bg'    => '#d1fae5',
                'bar'   => '#10b981',
            ],
            [
                'key'   => 'total_shares',
                'label' => 'Shares',
                'icon'  => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>',
                'color' => '#8b5cf6',
                'bg'    => '#ede9fe',
                'bar'   => '#8b5cf6',
            ],
            [
                'key'   => 'total_posts',
                'label' => 'Posts Published',
                'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
                'color' => '#0ea5e9',
                'bg'    => '#e0f2fe',
                'bar'   => '#0ea5e9',
            ],
        ];
        @endphp

        <div class="fb-metric-grid">
            @foreach($fbMetricDefs as $fm)
            @php $val = $fb['metrics'][$fm['key']]['total'] ?? 0; @endphp
            <div class="fb-mcard" style="--accent:{{ $fm['color'] }};">
                <style>.fb-mcard:nth-child({{ $loop->iteration }})::after { background:{{ $fm['color'] }}; }</style>
                <div class="fb-mcard__icon-wrap" style="background:{{ $fm['bg'] }};">
                    <svg width="20" height="20" fill="none" stroke="{{ $fm['color'] }}" stroke-width="2" viewBox="0 0 24 24">{!! $fm['icon'] !!}</svg>
                </div>
                <div class="fb-mcard__label">{{ $fm['label'] }} · {{ $monthLabel }}</div>
                <div class="fb-mcard__value">{{ number_format($val) }}</div>
            </div>
            @endforeach
        </div>

        {{-- ENHANCED CHART --}}
        @if(!empty($fb['metrics']['total_reach']['daily']))
        <div class="fb-chart-panel">
            <div class="fb-chart-head">
                <div>
                    <div class="pa-title">Daily Reach & Engagement</div>
                    <div class="pa-sub" style="margin-top:3px;">Page impressions vs post engagements — {{ $monthLabel }}</div>
                </div>
                <div class="fb-chart-toggles" id="fbChartToggles">
                    <button class="fb-ctog active" data-idx="0" onclick="toggleFbDataset(0,this)">Impressions</button>
                    <button class="fb-ctog active" data-idx="1" onclick="toggleFbDataset(1,this)">Engagements</button>
                </div>
            </div>

            {{-- Legend --}}
            <div class="fb-chart-legend">
                <div style="display:flex;align-items:center;gap:7px;font-size:12.5px;font-weight:600;color:var(--ink-soft);">
                    <span class="fb-legend-dot" style="background:#1877f2;"></span>
                    Impressions
                </div>
                <div style="display:flex;align-items:center;gap:7px;font-size:12.5px;font-weight:600;color:var(--ink-soft);">
                    <span class="fb-legend-dot" style="background:#e1306c;"></span>
                    Engagements
                </div>
                <div style="margin-left:auto;font-size:11.5px;color:var(--ink-faint);">
                    Total Impressions: <strong style="color:var(--ink);">{{ number_format($fb['metrics']['total_reach']['total'] ?? 0) }}</strong>
                    &nbsp;·&nbsp;
                    Total Engagements: <strong style="color:var(--ink);">{{ number_format($fb['metrics']['total_engagement']['total'] ?? 0) }}</strong>
                </div>
            </div>

            <div class="fb-chart-body">
                <div class="fb-chart-wrap">
                    <canvas id="fbAnalyticsChart"></canvas>
                </div>
            </div>
        </div>
        @endif

        @endif {{-- end metrics --}}

        {{-- RECENT FB POSTS TABLE --}}
        @if(!empty($fb['posts']))
        <div class="panel-a">
            <div class="pa-head">
                <div>
                    <div class="pa-title">Posts — {{ $monthLabel }}</div>
                    <div class="pa-sub">{{ count($fb['posts']) }} post(s) during this period</div>
                </div>
                <a href="{{ route('admin.analytics.export', ['month' => $selectedMonth]) }}"
                   style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:var(--navy);background:var(--navy-pale);border:1.5px solid rgba(0,35,102,0.15);padding:7px 14px;border-radius:9px;text-decoration:none;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export CSV
                </a>
            </div>
            <div style="overflow-x:auto;">
                <table class="rtable" id="vis-fb-table">
                    <thead>
                        <tr>
                            <th>Post</th>
                            <th>
                                <span style="display:flex;align-items:center;gap:4px;">
                                    <span style="color:#1877f2;">♥</span> Reactions
                                </span>
                            </th>
                            <th>
                                <span style="display:flex;align-items:center;gap:4px;">
                                    <span style="color:#10b981;">💬</span> Comments
                                </span>
                            </th>
                            <th>
                                <span style="display:flex;align-items:center;gap:4px;">
                                    <span style="color:#8b5cf6;">↗</span> Shares
                                </span>
                            </th>
                            <th>Date</th>
                            <th>Link</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($fb['posts'] as $post)
                    @php
                        $likes    = $post['reactions']['summary']['total_count'] ?? $post['likes']['summary']['total_count'] ?? 0;
                        $comments = $post['comments']['summary']['total_count'] ?? 0;
                        $shares   = $post['shares']['count']                    ?? 0;
                    @endphp
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:11px;">
                                @if(!empty($post['full_picture']))
                                    <img src="{{ $post['full_picture'] }}" class="fb-post-thumb" onerror="this.style.display='none'">
                                @else
                                    <div class="fb-post-no-thumb">📘</div>
                                @endif
                                <div style="min-width:0;">
                                    <div style="font-size:12.5px;font-weight:600;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:320px;">
                                        {{ Str::limit($post['message'] ?? $post['story'] ?? 'No caption', 70) }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="fb-metric-pill" style="background:#dbeafe;color:#1d4ed8;">
                                {{ number_format($likes) }}
                            </span>
                        </td>
                        <td>
                            <span class="fb-metric-pill" style="background:#d1fae5;color:#065f46;">
                                {{ number_format($comments) }}
                            </span>
                        </td>
                        <td>
                            <span class="fb-metric-pill" style="background:#ede9fe;color:#6d28d9;">
                                {{ number_format($shares) }}
                            </span>
                        </td>
                        <td style="font-size:11.5px;color:var(--ink-soft);white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($post['created_time'])->format('M j, Y') }}
                        </td>
                        <td>
                            @if(!empty($post['permalink_url']))
                            <a href="{{ $post['permalink_url'] }}" target="_blank"
                               style="font-size:11.5px;color:var(--navy);font-weight:600;text-decoration:none;display:flex;align-items:center;gap:4px;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                View
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        {{-- No posts empty state --}}
        <div class="panel-a">
            <div class="fb-empty">
                <div class="fb-empty__icon">📭</div>
                <div class="fb-empty__title">No posts in {{ $monthLabel }}</div>
                <div class="fb-empty__sub">There were no Facebook posts during this period. Try selecting a different month from the dropdown above.</div>
            </div>
        </div>
        @endif

    @endif {{-- end no error --}}

@else
    {{-- NOT CONFIGURED PLACEHOLDER --}}
    <div class="fb-banner" style="margin-bottom:16px;">
        <div class="fb-banner__icon">
            <svg width="24" height="24" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <div>
            <div class="fb-banner__title">Facebook & Meta Graph API</div>
            <div class="fb-banner__sub">Connect your Facebook Page to view monthly reach, engagement, reactions, and page insights.</div>
            <span class="fb-badge">⏳ Configure FB_PAGE_ID & FB_PAGE_ACCESS_TOKEN in .env</span>
        </div>
    </div>

    <div class="fb-metric-grid">
        @foreach(['Total Reach','Engagements','Reactions','Comments','Shares','Posts'] as $lbl)
        <div class="fb-ph-card">
            <div style="font-size:12px;font-weight:600;color:var(--ink-soft);">{{ $lbl }}</div>
            <div class="fb-ph-block">
                <svg width="22" height="22" fill="none" stroke="#93c5fd" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                <p>Waiting for API</p>
            </div>
        </div>
        @endforeach
    </div>
@endif

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
(function() {
    /* ── Visualization Live Search ─────────────────────────────────────── */
    let visSearchTimer = null;
    const visInput = document.getElementById('vis-search-input');
    const visClear = document.getElementById('vis-search-clear');
    const visPill  = document.getElementById('vis-search-pill');
    const visPillText = document.getElementById('vis-search-pill-text');

    window.applyVisSearch = function(rawQuery) {
        const q = (rawQuery || '').toLowerCase().trim();
        if (!visInput) return;

        if (q) {
            visClear.classList.add('visible');
        } else {
            visClear.classList.remove('visible');
            visPill.style.display = 'none';
        }

        let matchCount = 0;

        // Filter Recent Published Requests table
        const recTable = document.getElementById('vis-recent-table');
        if (recTable) {
            const rows = recTable.querySelectorAll('tbody tr');
            rows.forEach(tr => {
                if (tr.querySelector('td[colspan]')) return;
                if (!q) {
                    tr.style.display = '';
                    matchCount++;
                } else {
                    const text = tr.textContent.toLowerCase();
                    if (text.includes(q)) {
                        tr.style.display = '';
                        matchCount++;
                    } else {
                        tr.style.display = 'none';
                    }
                }
            });
        }

        // Filter Facebook posts table if present
        const fbTable = document.getElementById('vis-fb-table');
        if (fbTable) {
            const fbRows = fbTable.querySelectorAll('tbody tr');
            fbRows.forEach(tr => {
                if (tr.querySelector('td[colspan]')) return;
                if (!q) {
                    tr.style.display = '';
                } else {
                    const text = tr.textContent.toLowerCase();
                    if (text.includes(q)) {
                        tr.style.display = '';
                        matchCount++;
                    } else {
                        tr.style.display = 'none';
                    }
                }
            });
        }

        if (q) {
            visPill.style.display = 'inline-flex';
            visPillText.textContent = `${matchCount} match${matchCount === 1 ? '' : 'es'}`;
        }
    };

    window.clearVisSearch = function() {
        if (!visInput) return;
        visInput.value = '';
        window.applyVisSearch('');
        visInput.focus();
    };

    if (visInput) {
        visInput.addEventListener('input', function() {
            clearTimeout(visSearchTimer);
            visSearchTimer = setTimeout(() => {
                window.applyVisSearch(this.value);
            }, 120);
        });
    }

    /* ── Internal Trend Chart ─────────────────────────────────────── */
    const trendEl = document.getElementById('trendChart');
    if (trendEl) {
        const months   = @json(array_column($months_data,'label'));
        const totals   = @json(array_column($months_data,'total'));
        const posted   = @json(array_column($months_data,'posted'));
        const approved = @json(array_column($months_data,'approved'));

        const ctx = trendEl.getContext('2d');
        const gT  = ctx.createLinearGradient(0,0,0,240); gT.addColorStop(0,'rgba(0,35,102,0.12)');  gT.addColorStop(1,'rgba(0,35,102,0)');
        const gP  = ctx.createLinearGradient(0,0,0,240); gP.addColorStop(0,'rgba(139,92,246,0.12)'); gP.addColorStop(1,'rgba(139,92,246,0)');
        const gA  = ctx.createLinearGradient(0,0,0,240); gA.addColorStop(0,'rgba(16,185,129,0.12)'); gA.addColorStop(1,'rgba(16,185,129,0)');

        new Chart(ctx,{
            type:'line',
            data:{
                labels:months,
                datasets:[
                    {label:'Total',    data:totals,   borderColor:'#002366',backgroundColor:gT,borderWidth:2.5,pointBackgroundColor:'#002366',pointRadius:4,tension:0.4,fill:true},
                    {label:'Approved', data:approved, borderColor:'#10b981',backgroundColor:gA,borderWidth:2.5,pointBackgroundColor:'#10b981',pointRadius:4,tension:0.4,fill:true},
                    {label:'Posted',   data:posted,   borderColor:'#8b5cf6',backgroundColor:gP,borderWidth:2.5,pointBackgroundColor:'#8b5cf6',pointRadius:4,tension:0.4,fill:true},
                ]
            },
            options:{
                responsive:true,maintainAspectRatio:false,
                interaction:{mode:'index',intersect:false},
                plugins:{
                    legend:{position:'top',align:'end',labels:{font:{family:'DM Sans',size:12,weight:'600'},color:'#7a7672',usePointStyle:true,pointStyleWidth:8,boxHeight:8,padding:20}},
                    tooltip:{backgroundColor:'#1a1a1a',titleFont:{family:'DM Sans',size:12,weight:'700'},bodyFont:{family:'DM Sans',size:12},padding:12,cornerRadius:10}
                },
                scales:{
                    x:{grid:{display:false},ticks:{font:{family:'DM Sans',size:12},color:'#b5b0a8'}},
                    y:{beginAtZero:true,grid:{color:'#e8e3da'},ticks:{font:{family:'DM Sans',size:12},color:'#b5b0a8',stepSize:1,callback:v=>Number.isInteger(v)?v:''}}
                }
            }
        });
    }
})();
</script>

@if(isset($fb) && empty($fb['error']) && !empty($fb['metrics']['total_reach']['daily']))
<script>
/* ── Facebook Analytics Chart ─────────────────────────────────── */
let fbChart = null;

(function() {
    const fbACtx = document.getElementById('fbAnalyticsChart');
    if (!fbACtx) return;

    const rawReach = @json($fb['metrics']['total_reach']['daily'] ?? []);
    const rawEng   = @json($fb['metrics']['total_engagement']['daily'] ?? []);

    const fbLabels = rawReach.map(d => {
        const dt = new Date(d.date);
        return dt.toLocaleDateString('en-US', { month:'short', day:'numeric' });
    });
    const fbReach = rawReach.map(d => d.value);
    const fbEng   = rawEng.map(d => d.value);

    const ctx2 = fbACtx.getContext('2d');

    /* Gradient fills */
    const gR = ctx2.createLinearGradient(0, 0, 0, 280);
    gR.addColorStop(0, 'rgba(24,119,242,0.20)');
    gR.addColorStop(0.6, 'rgba(24,119,242,0.06)');
    gR.addColorStop(1, 'rgba(24,119,242,0)');

    const gE = ctx2.createLinearGradient(0, 0, 0, 280);
    gE.addColorStop(0, 'rgba(225,48,108,0.18)');
    gE.addColorStop(0.6, 'rgba(225,48,108,0.05)');
    gE.addColorStop(1, 'rgba(225,48,108,0)');

    fbChart = new Chart(ctx2, {
        type: 'line',
        data: {
            labels: fbLabels,
            datasets: [
                {
                    label: 'Impressions',
                    data: fbReach,
                    borderColor: '#1877f2',
                    backgroundColor: gR,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#1877f2',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.42,
                    fill: true,
                },
                {
                    label: 'Engagements',
                    data: fbEng,
                    borderColor: '#e1306c',
                    backgroundColor: gE,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#e1306c',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.42,
                    fill: true,
                    borderDash: [6, 3],
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false }, // we use custom legend
                tooltip: {
                    backgroundColor: '#18181b',
                    titleFont: { family:'DM Sans', size:12, weight:'700' },
                    bodyFont:  { family:'DM Sans', size:12 },
                    padding: 14,
                    cornerRadius: 12,
                    borderColor: 'rgba(255,255,255,0.08)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(ctx) {
                            const icon = ctx.datasetIndex === 0 ? '👁' : '💬';
                            return `  ${icon}  ${ctx.dataset.label}: ${ctx.formattedValue}`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: {
                        font: { family:'DM Sans', size:11.5 },
                        color: '#b5b0a8',
                        maxRotation: 0,
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)', drawBorder: false },
                    border: { display: false, dash: [4, 4] },
                    ticks: {
                        font: { family:'DM Sans', size:11.5 },
                        color: '#b5b0a8',
                        padding: 8,
                        callback: v => v >= 1000 ? (v/1000).toFixed(1)+'k' : v
                    }
                }
            }
        }
    });
})();

/* Toggle individual datasets via buttons */
function toggleFbDataset(idx, btn) {
    if (!fbChart) return;
    const ds = fbChart.data.datasets[idx];
    ds.hidden = !ds.hidden;
    btn.classList.toggle('active', !ds.hidden);
    fbChart.update();
}
</script>
@endif
@endsection