@extends('layouts.requestor')

@section('title', 'Calendar')
@section('page-title', 'Calendar')

@section('head-styles')
<style>
.main { padding: 24px 26px 36px; }

.page-header {
    display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 20px;
}
.page-header-left h1 { font-size: 22px; font-weight: 700; letter-spacing: -0.4px; color: var(--text); }
.page-header-left p  { font-size: 13px; color: var(--text-muted); margin-top: 3px; }

.toggle-wrap  { display: flex; align-items: center; gap: 10px; flex-shrink: 0; margin-top: 4px; }
.toggle-label { font-size: 12.5px; font-weight: 600; color: var(--text-muted); display: flex; align-items: center; gap: 5px; }
.toggle-btn   {
    position: relative; width: 42px; height: 23px; border-radius: 12px;
    background: #d1d5db; border: none; cursor: pointer; transition: background .25s; flex-shrink: 0; padding: 0;
}
.toggle-btn.on { background: #10b981; }
.toggle-btn::after {
    content: ''; position: absolute; top: 3px; left: 3px; width: 17px; height: 17px;
    border-radius: 50%; background: white; transition: transform .25s; box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
.toggle-btn.on::after { transform: translateX(19px); }

.public-banner {
    display: flex; align-items: flex-start; gap: 10px;
    background: linear-gradient(135deg,rgba(16,185,129,0.08),rgba(5,150,105,0.06));
    border: 1.5px solid rgba(16,185,129,0.3); border-radius: 14px;
    padding: 13px 16px; font-size: 12.5px; color: #065f46; margin-bottom: 16px;
    animation: fadeIn .35s ease;
}
.public-banner svg { flex-shrink: 0; margin-top: 1px; color: #059669; }
@keyframes fadeIn { from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none} }

/* LAYOUT */
.cal-layout { display: grid; grid-template-columns: 1fr 310px; gap: 18px; align-items: start; }
@media(max-width:900px){ .cal-layout{grid-template-columns:1fr;} }

/* CALENDAR CARD */
.cal-card {
    background: linear-gradient(160deg,#001a4d 0%,#002366 55%,#003080 100%);
    box-shadow: 0 8px 32px rgba(0,26,77,0.35);
    border-radius: 22px; overflow: hidden; border: none;
}

.cal-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 22px; border-bottom: 1px solid rgba(255,255,255,0.1);
    background: rgba(0,0,0,0.15);
}
.cal-toolbar-left { display: flex; align-items: center; gap: 12px; }
.cal-mode-badge {
    display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px;
    border-radius: 20px; font-size: 10.5px; font-weight: 700; letter-spacing: 0.3px;
}
.cal-mode-badge--private { background:rgba(255,255,255,0.12); color:rgba(255,255,255,0.8); border:1px solid rgba(255,255,255,0.18); }
.cal-mode-badge--public  { background:rgba(52,211,153,0.2);   color:#6ee7b7;              border:1px solid rgba(52,211,153,0.35); }
.cal-mode-badge__dot { width:6px; height:6px; border-radius:50%; }
.cal-mode-badge--private .cal-mode-badge__dot { background:rgba(255,255,255,0.6); }
.cal-mode-badge--public  .cal-mode-badge__dot { background:#34d399; box-shadow:0 0 6px #34d399; }

.cal-nav { display:flex; align-items:center; gap:10px; }
.cal-nav-btn {
    width:28px; height:28px; border-radius:8px;
    background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.18);
    cursor:pointer; display:flex; align-items:center; justify-content:center;
    color:rgba(255,255,255,0.7); transition:all .15s; text-decoration:none;
}
.cal-nav-btn:hover { background:rgba(255,255,255,0.2); color:white; }
.cal-month-label { font-size:14px; font-weight:700; min-width:110px; text-align:center; color:white; letter-spacing:-0.3px; }
.cal-today-btn {
    padding:6px 14px; border-radius:8px;
    background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2);
    font-size:12px; font-weight:600; cursor:pointer; color:rgba(255,255,255,0.8);
    font-family:var(--font); text-decoration:none; transition:all .15s;
}
.cal-today-btn:hover { background:rgba(255,255,255,0.2); color:white; }

.cal-grid { width:100%; border-collapse:collapse; table-layout:fixed; }
.cal-grid thead th {
    padding:10px 8px; text-align:center; font-size:10px; font-weight:700;
    color:rgba(255,255,255,0.4); text-transform:uppercase; letter-spacing:0.7px;
    border-bottom:1px solid rgba(255,255,255,0.08); background:rgba(0,0,0,0.1);
}
.cal-grid tbody td {
    vertical-align:top; height:90px; padding:7px 8px;
    border:1px solid rgba(255,255,255,0.05); transition:background .12s; cursor:pointer;
}
.cal-grid tbody td:hover { background:rgba(255,255,255,0.05); }
.cal-grid tbody td.has-events:hover { background:rgba(255,255,255,0.09); }
.cal-grid tbody td.selected-day { background:rgba(245,158,11,0.15)!important; outline:2px solid rgba(245,158,11,0.5); outline-offset:-2px; }
.cal-grid tbody td.cal-day--other { background:rgba(0,0,0,0.12); cursor:default; pointer-events:none; }

.cal-day-num {
    font-size:12px; font-weight:600; color:rgba(255,255,255,0.55);
    width:26px; height:26px; display:flex; align-items:center; justify-content:center;
    border-radius:50%; margin-bottom:3px;
}
.cal-day-num--today { background:#f59e0b; color:#1a1a1a; font-weight:800; box-shadow:0 3px 10px rgba(245,158,11,0.45); }
.cal-day--other .cal-day-num { color:rgba(255,255,255,0.2); }

.day-load { width:100%; height:3px; border-radius:2px; margin-bottom:3px; }
.day-load--low    { background:rgba(52,211,153,0.5); }
.day-load--medium { background:rgba(251,191,36,0.6); }
.day-load--high   { background:rgba(239,68,68,0.6); }
.day-count { font-size:9px; font-weight:600; color:rgba(255,255,255,0.4); margin-bottom:2px; }

/* EVENT PILLS */
.cal-event {
    display:block; padding:2px 6px; border-radius:5px; font-size:10px; font-weight:600;
    color:white; margin-bottom:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; line-height:1.5;
}
.cal-event--urgent  { background:rgba(239,68,68,0.85); }
.cal-event--high    { background:rgba(245,158,11,0.85); color:#1a1a1a; }
.cal-event--medium  { background:rgba(59,110,245,0.8); }
.cal-event--low     { background:rgba(148,163,184,0.45); }
/* Public: orange=mine, muted gray=others */
.cal-event--mine    { background:rgba(249,115,22,0.9); border-left:3px solid #fb923c; }
.cal-event--others  { background:rgba(100,116,139,0.3); color:rgba(255,255,255,0.5); font-style:italic; border-left:3px solid rgba(100,116,139,0.4); }
.cal-event--more    { background:none!important; color:rgba(255,255,255,0.5)!important; font-size:9px; font-style:italic; }

.cal-legend {
    display:flex; align-items:center; gap:16px; flex-wrap:wrap;
    padding:12px 22px; border-top:1px solid rgba(255,255,255,0.1); background:rgba(0,0,0,0.15);
}
.legend-item { display:flex; align-items:center; gap:6px; font-size:11px; color:rgba(255,255,255,0.5); }
.legend-dot  { width:9px; height:9px; border-radius:3px; flex-shrink:0; }

/* UPCOMING */
.upcoming-card { background:white; border-radius:18px; border:1.5px solid var(--border); box-shadow:0 1px 4px rgba(0,0,0,0.05); padding:20px 24px; margin-top:16px; }
.upcoming-card h3 { font-size:14px; font-weight:700; margin-bottom:14px; color:var(--text); }
.upcoming-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; padding:24px; color:var(--text-faint); gap:8px; }
.upcoming-empty p { font-size:13px; }
.upcoming-item { display:flex; align-items:center; gap:14px; padding:11px 0; border-bottom:1px solid #f3f4f6; }
.upcoming-item:last-child { border-bottom:none; }
.upcoming-dot         { width:9px; height:9px; border-radius:50%; background:#001a6e; flex-shrink:0; }
.upcoming-dot--urgent { background:#ef4444; }
.upcoming-dot--high   { background:#f59e0b; }
.upcoming-dot--others { background:#cbd5e1; }
.upcoming-dot--posted { background:#7c3aed; }
.upcoming-title { font-size:13px; font-weight:600; color:var(--text); }
.upcoming-meta  { font-size:11.5px; color:var(--text-muted); margin-top:2px; }
.upcoming-mine-tag   { display:inline-block; padding:1px 8px; background:#ffedd5; color:#c2410c; border-radius:10px; font-size:10px; font-weight:700; margin-left:6px; }
.upcoming-others-tag { display:inline-block; padding:1px 8px; background:#f1f5f9; color:#64748b; border-radius:10px; font-size:10px; font-weight:700; margin-left:6px; }

/* SIDE PANEL — always visible */
.side-col { display:flex; flex-direction:column; gap:14px; position:sticky; top:16px; }
.day-panel {
    background:white; border-radius:18px; border:1.5px solid #e5e7eb;
    box-shadow:0 4px 20px rgba(0,26,77,0.1); overflow:hidden;
}
.dp__head {
    background:linear-gradient(135deg,#001a4d,#002e7a);
    padding:16px 18px;
}
.dp__date  { font-size:15px; font-weight:700; color:white; }
.dp__count { font-size:11px; color:rgba(255,255,255,0.6); margin-top:3px; }
.dp__body { padding:12px 14px; max-height:500px; overflow-y:auto; }
.dp__body::-webkit-scrollbar { width:4px; }
.dp__body::-webkit-scrollbar-thumb { background:rgba(0,0,0,0.1); border-radius:4px; }

/* Empty state */
.dp__empty {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    padding:28px 16px; gap:10px; color:#94a3b8; text-align:center;
}
.dp__empty p { font-size:12.5px; font-weight:600; color:#64748b; margin:0; }
.dp__empty span { font-size:11px; color:#cbd5e1; line-height:1.5; }

/* Request cards in panel */
.req-card {
    border:1.5px solid #e5e7eb; border-radius:12px;
    padding:12px 14px; margin-bottom:8px; position:relative; transition:all .15s;
}
.req-card:last-child { margin-bottom:0; }
.req-card:hover { border-color:#a5b4fc; box-shadow:0 3px 12px rgba(0,26,77,0.1); background:#fafbff; }
.req-card--urgent { border-left:4px solid #ef4444; }
.req-card--high   { border-left:4px solid #f59e0b; }
.req-card--medium { border-left:4px solid #3b6ef5; }
.req-card--low    { border-left:4px solid #cbd5e1; }
.req-card--mine   { border-left:4px solid #f97316; }
.req-card--others { border-left:4px solid #94a3b8; background:#f8fafc; }

.req-card__num {
    position:absolute; top:9px; right:10px; width:20px; height:20px; border-radius:50%;
    background:#f1f5f9; color:#64748b; font-size:10px; font-weight:800;
    display:flex; align-items:center; justify-content:center;
}
.req-card__title { font-size:13px; font-weight:700; color:#0f172a; line-height:1.3; padding-right:26px; margin-bottom:4px; }
.req-card__meta  { font-size:11px; color:#64748b; }
.req-card__tags  { display:flex; gap:5px; flex-wrap:wrap; margin-top:7px; }
.req-tag { display:inline-flex; padding:2px 8px; border-radius:7px; font-size:10px; font-weight:700; }
.req-tag--urgent  { background:#fee2e2; color:#b91c1c; }
.req-tag--high    { background:#fef3c7; color:#b45309; }
.req-tag--medium  { background:#dbeafe; color:#1d4ed8; }
.req-tag--low     { background:#f1f5f9; color:#64748b; }
.req-tag--approved{ background:#dcfce7; color:#15803d; }
.req-tag--posted  { background:#ede9fe; color:#7c3aed; }
.req-tag--review  { background:#fef3c7; color:#b45309; }
.req-tag--rejected{ background:#fee2e2; color:#b91c1c; }
.req-tag--pending { background:#f8fafc; color:#64748b; border:1px solid #e2e8f0; }
.req-tag--mine    { background:#ffedd5; color:#c2410c; }
.req-tag--others  { background:#f1f5f9; color:#64748b; }

@media (max-width: 900px) {
    .main { padding: 16px 14px 36px; }

    /* Page header stacks vertically */
    .page-header {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    .toggle-wrap { margin-top: 0; }

    /* Calendar layout: single column */
    .cal-layout {
        grid-template-columns: 1fr;
    }

    /* Toolbar stacks on small screens */
    .cal-toolbar {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
        padding: 14px 16px;
    }
    .cal-toolbar-left {
        flex-direction: column;
        align-items: stretch;
        gap: 8px;
    }
    .cal-today-btn { text-align: center; }

    /* Shrink calendar cells so they fit */
    .cal-grid tbody td {
        height: 60px;
        padding: 4px 3px;
    }
    .cal-grid thead th {
        padding: 8px 2px;
        font-size: 9px;
    }
    .cal-day-num {
        width: 20px;
        height: 20px;
        font-size: 10px;
        margin-bottom: 1px;
    }
    .cal-event {
        font-size: 8.5px;
        padding: 1px 4px;
    }
    .day-load { height: 2px; margin-bottom: 2px; }
    .day-count { font-size: 8px; }

    /* Legend wraps */
    .cal-legend {
        gap: 10px;
        padding: 10px 14px;
        flex-wrap: wrap;
    }
    .legend-item { font-size: 10px; }

    /* Side panel: goes below calendar, loses sticky */
    .side-col {
        position: static;
    }
    .dp__body { max-height: 300px; }

    /* Upcoming card */
    .upcoming-card { padding: 16px 16px; }
}

@media (max-width: 480px) {
    /* Even smaller cells on phones */
    .cal-grid tbody td { height: 48px; padding: 3px 2px; }
    .cal-grid thead th { font-size: 8px; letter-spacing: 0; }
    .cal-day-num { width: 18px; height: 18px; font-size: 9px; }
    .cal-event { display: none; } /* hide pills, rely on load bar only */
    .day-count { font-size: 8px; }

    /* Public banner wraps nicely */
    .public-banner { font-size: 11.5px; padding: 10px 12px; }

    /* Upcoming items */
    .upcoming-title { font-size: 12px; }
    .upcoming-meta  { font-size: 10.5px; }
}

/* ── DARK THEME OVERRIDES ── */
[data-theme="dark"] .upcoming-card {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
}
[data-theme="dark"] .upcoming-card h3 {
    color: #f8fafc !important;
}
[data-theme="dark"] .upcoming-empty {
    color: #64748b !important;
}
[data-theme="dark"] .upcoming-empty p {
    color: #94a3b8 !important;
}
[data-theme="dark"] .upcoming-item {
    border-bottom-color: rgba(30, 79, 216, 0.15) !important;
}
[data-theme="dark"] .upcoming-title {
    color: #f8fafc !important;
}
[data-theme="dark"] .upcoming-meta {
    color: #94a3b8 !important;
}
[data-theme="dark"] .upcoming-others-tag {
    background: #06112a !important;
    color: #94a3b8 !important;
}
[data-theme="dark"] .day-panel {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
}
[data-theme="dark"] .dp__empty {
    color: #64748b !important;
}
[data-theme="dark"] .dp__empty p {
    color: #cbd5e1 !important;
}
[data-theme="dark"] .dp__empty span {
    color: #64748b !important;
}
[data-theme="dark"] .dp__body::-webkit-scrollbar-thumb {
    background: rgba(30, 79, 216, 0.3) !important;
}
[data-theme="dark"] .req-card {
    background: #0b1a3d !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .req-card:hover {
    background: #0f2250 !important;
    border-color: #3b82f6 !important;
}
[data-theme="dark"] .req-card__num {
    background: #06112a !important;
    color: #94a3b8 !important;
}
[data-theme="dark"] .req-card__title {
    color: #f8fafc !important;
}
[data-theme="dark"] .req-card__meta {
    color: #94a3b8 !important;
}
[data-theme="dark"] .req-card--others {
    background: #06112a !important;
    border-left-color: #64748b !important;
}
[data-theme="dark"] .req-tag--pending {
    background: #06112a !important;
    color: #94a3b8 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .req-tag--others {
    background: #06112a !important;
    color: #94a3b8 !important;
}
[data-theme="dark"] .public-banner {
    background: rgba(16, 185, 129, 0.12) !important;
    border-color: rgba(16, 185, 129, 0.25) !important;
    color: #6ee7b7 !important;
}
</style>
@endsection

@section('content')
<div class="main">

@php
    $user_name   = session('name');
    $today_label = date('F j, Y');

    $prio_ev_cls = fn($p) => match(strtolower($p??'low')){
        'urgent'=>'cal-event--urgent','high'=>'cal-event--high','medium'=>'cal-event--medium',default=>'cal-event--low'
    };
@endphp

    <div class="page-header">
        <div class="page-header-left">
            <h1>Post Tracking Calendar</h1>
            <p>
                {{ $is_public
                    ? "Public view — all users' preferred posting dates"
                    : "Your preferred posting dates" }}
                · Click any date to view requests
            </p>
        </div>
        <div class="toggle-wrap">
            <span class="toggle-label">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Public Calendar
            </span>
            <a href="{{ route('requestor.calendar', ['toggle_public'=>1,'month'=>$month,'year'=>$year]) }}" style="text-decoration:none;">
                <div class="toggle-btn {{ $is_public ? 'on' : '' }}"></div>
            </a>
        </div>
    </div>

    @if($is_public)
    <div class="public-banner">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span><strong>Public view is ON</strong> — <span style="color:#f97316;font-weight:700;">● Orange</span> = your requests &nbsp;·&nbsp; <span style="color:#94a3b8;font-weight:700;">● Gray italic</span> = others' requests. Pick a less busy date!</span>
    </div>
    @endif

    <div class="cal-layout">

        {{-- CALENDAR --}}
        <div>
            <div class="cal-card">
                <div class="cal-toolbar">
                    <div class="cal-toolbar-left">
                        <div class="cal-mode-badge {{ $is_public ? 'cal-mode-badge--public' : 'cal-mode-badge--private' }}">
                            <span class="cal-mode-badge__dot"></span>
                            {{ $is_public ? 'Public View' : 'My Schedule' }}
                        </div>
                        <div class="cal-nav">
                            <a href="{{ route('requestor.calendar',['month'=>$prev_month,'year'=>$prev_year,'public'=>$is_public?1:0]) }}" class="cal-nav-btn">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                            </a>
                            <span class="cal-month-label">{{ $month_name }}</span>
                            <a href="{{ route('requestor.calendar',['month'=>$next_month,'year'=>$next_year,'public'=>$is_public?1:0]) }}" class="cal-nav-btn">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                        </div>
                    </div>
                    <a href="{{ route('requestor.calendar',['month'=>$today_month,'year'=>$today_year]) }}" class="cal-today-btn">Today</a>
                </div>

                <table class="cal-grid">
                    <thead><tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr></thead>
                    <tbody>
                    @php
                        $cell        = 0;
                        $total_cells = ceil(($first_day + $days_in_month) / 7) * 7;
                        echo "<tr>";
                        for ($i = 0; $i < $total_cells; $i++) {
                            $day_num    = $i - $first_day + 1;
                            $is_current = ($day_num >= 1 && $day_num <= $days_in_month);
                            $is_today   = $is_current && $day_num===$today_day && $month===$today_month && $year===$today_year;

                            if ($is_current)         $display = $day_num;
                            elseif ($i < $first_day) $display = $days_in_prev - ($first_day - $i - 1);
                            else                     $display = $day_num - $days_in_month;

                            $day_evs = ($is_current && isset($events[$day_num])) ? $events[$day_num] : [];
                            $cnt     = count($day_evs);

                            // Build JSON for side panel
                            $ev_json = [];
                            foreach ($day_evs as $ev) {
                                $ev_json[] = [
                                    'id'       => $ev->id,
                                    'title'    => $ev->title,
                                    'requester'=> $ev->requester,
                                    'priority' => ucfirst(strtolower($ev->priority ?? 'Low')),
                                    'status'   => $ev->status,
                                    'category' => $ev->category ?? '',
                                    'is_mine'  => ($ev->requester === $user_name),
                                    'created'  => \Carbon\Carbon::parse($ev->created_at)->format('M j, Y'),
                                    'pref_date'=> \Carbon\Carbon::parse($ev->preferred_date)->format('M j, Y'),
                                ];
                            }

                            $td_cls      = !$is_current ? ' cal-day--other' : '';
                            $has_cls     = ($is_current && $cnt > 0) ? ' has-events' : '';
                            $data_date   = $is_current ? date('F j, Y', mktime(0,0,0,$month,$day_num,$year)) : '';
                            $data_events = $is_current ? htmlspecialchars(json_encode($ev_json), ENT_QUOTES) : '[]';
                            $onclick     = $is_current ? 'onclick="showPanel(this)"' : '';

                            echo "<td class='$td_cls$has_cls' data-date='$data_date' data-events='$data_events' $onclick>";
                            $nc = $is_today ? 'cal-day-num cal-day-num--today' : 'cal-day-num';
                            echo "<div class='$nc'>$display</div>";

                            if ($is_current && $cnt > 0) {
                                $bar = $cnt >= 4 ? 'day-load--high' : ($cnt >= 2 ? 'day-load--medium' : 'day-load--low');
                                echo "<div class='day-load $bar'></div>";
                                if ($is_public) echo "<div class='day-count'>$cnt req" . ($cnt>1?'s':'') . "</div>";
                                $shown = 0;
                                foreach ($day_evs as $ev) {
                                    if ($shown >= 2) break;
                                    $is_mine = ($ev->requester === $user_name);
                                    if ($is_public) {
                                        $cls = $is_mine ? 'cal-event cal-event--mine' : 'cal-event cal-event--others';
                                    } else {
                                        $p   = ucfirst(strtolower($ev->priority ?? 'Low'));
                                        $cls = 'cal-event ' . $prio_ev_cls($p);
                                    }
                                    $short = htmlspecialchars(mb_strimwidth($ev->title, 0, 18, '…'));
                                    echo "<span class='$cls'>$short</span>";
                                    $shown++;
                                }
                                if ($cnt - $shown > 0) echo "<span class='cal-event cal-event--more'>+" . ($cnt-$shown) . " more</span>";
                            }
                            echo "</td>";
                            $cell++;
                            if ($cell % 7 === 0 && $i < $total_cells - 1) echo "</tr><tr>";
                        }
                        echo "</tr>";
                    @endphp
                    </tbody>
                </table>

                <div class="cal-legend">
                    @if($is_public)
                        <div class="legend-item"><span class="legend-dot" style="background:rgba(249,115,22,0.9);"></span>Your request</div>
                        <div class="legend-item"><span class="legend-dot" style="background:rgba(100,116,139,0.35);"></span>Others' request</div>
                        <div class="legend-item"><span class="legend-dot" style="border-radius:50%;background:#f59e0b;"></span>Today</div>
                        <div class="legend-item"><span class="legend-dot" style="border-radius:50%;background:rgba(239,68,68,0.6);"></span>Busy day</div>
                    @else
                        <div class="legend-item"><span class="legend-dot" style="background:rgba(239,68,68,0.85);"></span>Urgent</div>
                        <div class="legend-item"><span class="legend-dot" style="background:rgba(245,158,11,0.85);"></span>High</div>
                        <div class="legend-item"><span class="legend-dot" style="background:rgba(59,110,245,0.8);"></span>Medium</div>
                        <div class="legend-item"><span class="legend-dot" style="background:rgba(148,163,184,0.45);"></span>Low</div>
                        <div class="legend-item"><span class="legend-dot" style="border-radius:50%;background:#f59e0b;"></span>Today</div>
                    @endif
                </div>
            </div>

            {{-- UPCOMING --}}
            <div class="upcoming-card">
                <h3>{{ $is_public ? 'Upcoming — Next 7 Days (All Users)' : 'Your Upcoming — Next 7 Days' }}</h3>
                @if($upcoming->isEmpty())
                    <div class="upcoming-empty">
                        <svg width="36" height="36" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <p>No upcoming scheduled posts in the next 7 days</p>
                    </div>
                @else
                    @foreach($upcoming as $up)
                    @php
                        $pl  = strtolower($up->priority ?? 'low');
                        $stl = strtolower($up->status);
                        $dc  = !($up->is_mine) ? 'upcoming-dot upcoming-dot--others'
                             : ($pl==='urgent' ? 'upcoming-dot upcoming-dot--urgent'
                             : ($pl==='high'   ? 'upcoming-dot upcoming-dot--high'
                             : (str_contains($stl,'posted') ? 'upcoming-dot upcoming-dot--posted' : 'upcoming-dot')));
                    @endphp
                    <div class="upcoming-item">
                        <span class="{{ $dc }}"></span>
                        <div>
                            <div class="upcoming-title">
                                {{ $up->title }}
                                @if($is_public)
                                    @if($up->is_mine)<span class="upcoming-mine-tag">Yours</span>
                                    @else<span class="upcoming-others-tag">Others</span>@endif
                                @endif
                            </div>
                            <div class="upcoming-meta">
                                {{ \Carbon\Carbon::parse($up->preferred_date)->format('M j, Y') }} · {{ $up->status }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>
        </div>

        {{-- SIDE PANEL — always visible, no close button --}}
        <div class="side-col">
            <div class="day-panel" id="day-panel">
                <div class="dp__head">
                    <div class="dp__date"  id="panel-date">{{ $today_label }}</div>
                    <div class="dp__count" id="panel-count">Click any date to view requests</div>
                </div>
                <div class="dp__body" id="panel-body">
                    {{-- Default: show today's events if any, else empty state --}}
                    <div class="dp__empty">
                        <svg width="42" height="42" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        <p>No requests today.</p>
                        <span>Select any highlighted date<br>on the calendar to view<br>its scheduled requests.</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
const PRIO_ORDER = {'Urgent':0,'High':1,'Medium':2,'Low':3};
const IS_PUBLIC  = {{ $is_public ? 'true' : 'false' }};

function cardCls(p, is_mine) {
    if (IS_PUBLIC) return is_mine ? 'req-card--mine' : 'req-card--others';
    return {'Urgent':'req-card--urgent','High':'req-card--high','Medium':'req-card--medium'}[p] || 'req-card--low';
}
function tagCls(p) {
    return {'Urgent':'req-tag--urgent','High':'req-tag--high','Medium':'req-tag--medium'}[p] || 'req-tag--low';
}
function statCls(s) {
    s = s.toLowerCase();
    if (s.includes('approved')) return 'req-tag--approved';
    if (s.includes('posted'))   return 'req-tag--posted';
    if (s.includes('review'))   return 'req-tag--review';
    if (s.includes('rejected')) return 'req-tag--rejected';
    return 'req-tag--pending';
}

function showPanel(el) {
    const raw  = el.dataset.events;
    const date = el.dataset.date;

    document.querySelectorAll('.cal-grid td').forEach(t => t.classList.remove('selected-day'));
    el.classList.add('selected-day');

    document.getElementById('panel-date').textContent = date || 'Selected Date';

    let evs = [];
    try { evs = JSON.parse(raw || '[]'); } catch(e) {}

    evs.sort((a, b) => (PRIO_ORDER[a.priority] ?? 3) - (PRIO_ORDER[b.priority] ?? 3));

    if (evs.length === 0) {
        document.getElementById('panel-count').textContent = 'No requests on this date';
        document.getElementById('panel-body').innerHTML = `
            <div class="dp__empty">
                <svg width="42" height="42" fill="none" stroke="#cbd5e1" stroke-width="1.5" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <p>No requests on this date.</p>
                <span>This day is open —<br>a great time to schedule<br>your next post!</span>
            </div>`;
        return;
    }

    document.getElementById('panel-count').textContent = evs.length + ' request' + (evs.length !== 1 ? 's' : '') + ' · sorted by priority';

    let html = '';
    evs.forEach((ev, i) => {
        const mineTag = IS_PUBLIC
            ? (ev.is_mine
                ? '<span class="req-tag req-tag--mine">Yours</span>'
                : '<span class="req-tag req-tag--others">Others</span>')
            : '';

        const metaLine = IS_PUBLIC && !ev.is_mine
            ? `👤 ${esc(ev.requester)} · 📅 ${esc(ev.pref_date)}`
            : `📅 ${esc(ev.pref_date)}${ev.category ? ' · ' + esc(ev.category) : ''}`;

        html += `
        <div class="req-card ${cardCls(ev.priority, ev.is_mine)}">
            <div class="req-card__num">${i + 1}</div>
            <div class="req-card__title">${esc(ev.title)}</div>
            <div class="req-card__meta">${metaLine}</div>
            <div class="req-card__tags">
                <span class="req-tag ${tagCls(ev.priority)}">${esc(ev.priority)}</span>
                <span class="req-tag ${statCls(ev.status)}">${esc(ev.status)}</span>
                ${mineTag}
            </div>
        </div>`;
    });

    document.getElementById('panel-body').innerHTML = html;
}

function esc(s) {
    return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// On load: auto-show today's panel if today has events
function initCalendarToday() {
    const todayNum = document.querySelector('.cal-day-num--today');
    if (todayNum) {
        const td = todayNum.closest('td');
        if (td && td.dataset.date) showPanel(td);
    }
}
document.addEventListener('DOMContentLoaded', initCalendarToday);
</script>
@endsection