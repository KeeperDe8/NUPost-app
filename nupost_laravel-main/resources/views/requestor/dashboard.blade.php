@extends('layouts.requestor')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('head-styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap');

:root {
    --font-disp: 'DM Serif Display', serif;
    --ink:       #1a1a1a;
    --ink-mid:   #3d3d3d;
    --ink-soft:  #8a8a8a;
    --sand:      #e8e3da;
    --card:      #faf9f7;
    --stroke:    rgba(0,0,0,0.06);
    --radius-card: 22px;
}

.db-wrap {
    padding: 24px 26px 40px;
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 16px;
}

/* ── HERO ────────────────────────────── */
.hero-card {
    background: linear-gradient(135deg, #001a4d 0%, #002e7a 60%, #003a8c 100%);
    border-radius: var(--radius-card);
    padding: 28px 30px;
    position: relative; overflow: hidden;
    min-height: 190px;
    display: flex; flex-direction: column; justify-content: space-between;
    grid-column: 1; grid-row: 1;
    box-shadow: 0 8px 32px rgba(0,26,77,0.35);
}
.hero-card__glow {
    position: absolute; top: -40px; right: -40px;
    width: 240px; height: 240px; border-radius: 50%;
    background: radial-gradient(circle, rgba(59,130,246,0.22) 0%, transparent 65%);
    pointer-events: none;
}
.hero-card__glow2 {
    position: absolute; bottom: -30px; left: 60px;
    width: 160px; height: 160px; border-radius: 50%;
    background: radial-gradient(circle, rgba(245,158,11,0.12) 0%, transparent 65%);
    pointer-events: none;
}
.hero-card__label { font-size: 12px; color: rgba(255,255,255,0.5); font-weight: 500; margin-bottom: 6px; letter-spacing: 0.3px; }
.hero-card__title {
    font-family: var(--font-disp);
    font-size: 30px; color: white; line-height: 1.2; margin-bottom: 4px;
    text-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.hero-card__bottom { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 20px; }
.hero-stat {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.18);
    border-radius: 12px; padding: 10px 16px;
    display: flex; align-items: center; gap: 8px;
    backdrop-filter: blur(8px);
}
.hero-stat__dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; box-shadow: 0 0 6px currentColor; }
.hero-stat__num { font-size: 20px; font-weight: 700; color: white; }
.hero-stat__lbl { font-size: 10.5px; color: rgba(255,255,255,0.6); font-weight: 500; }
.hero-card__btn {
    margin-left: auto;
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 18px;
    background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.22);
    border-radius: 12px; color: white; font-size: 12.5px; font-weight: 600;
    text-decoration: none; transition: background .15s; font-family: var(--font);
}
.hero-card__btn:hover { background: rgba(255,255,255,0.2); }

/* ── CALENDAR CARD ───────────────────── */
.calendar-card {
    background: linear-gradient(160deg, #001a4d 0%, #002366 50%, #003080 100%);
    border-radius: var(--radius-card);
    padding: 22px 20px;
    grid-column: 2; grid-row: 1 / 3;
    box-shadow: 0 8px 32px rgba(0,26,77,0.3);
    transition: transform .2s, box-shadow .2s;
    cursor: pointer;
}
.calendar-card:hover { transform: translateY(-3px); box-shadow: 0 12px 40px rgba(0,26,77,0.4); }
.cal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.cal-title { font-family: var(--font-disp); font-size: 16px; color: white; }
.cal-month-nav { display: flex; align-items: center; gap: 6px; }
.cal-month-nav span { font-size: 11.5px; color: rgba(255,255,255,0.65); font-weight: 500; }
.cal-nav-btn {
    width: 26px; height: 26px; border-radius: 7px;
    background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);
    cursor: pointer; color: rgba(255,255,255,0.65);
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; transition: all .15s;
}
.cal-nav-btn:hover { background: rgba(255,255,255,0.2); color: white; }
.cal-grid-head { display: grid; grid-template-columns: repeat(7,1fr); margin-bottom: 4px; }
.cal-grid-head span { font-size: 9.5px; font-weight: 700; color: rgba(255,255,255,0.4); text-align: center; padding: 4px 0; letter-spacing: 0.5px; }
.cal-grid { display: grid; grid-template-columns: repeat(7,1fr); gap: 2px; }
.cal-day {
    aspect-ratio: 1; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 500; color: rgba(255,255,255,0.5);
    position: relative; cursor: default; transition: background .15s;
}
.cal-day:hover { background: rgba(255,255,255,0.08); }
.cal-day--other { color: rgba(255,255,255,0.18); }
.cal-day--today {
    background: #f59e0b !important; color: #1a1a1a !important;
    font-weight: 800; border-radius: 50%;
    box-shadow: 0 3px 12px rgba(245,158,11,0.45);
}
.cal-day--has-req { color: rgba(255,255,255,0.9); }
.cal-day--has-req::after {
    content: ''; position: absolute; bottom: 2px; left: 50%; transform: translateX(-50%);
    width: 4px; height: 4px; border-radius: 50%; background: #f59e0b;
}
.cal-day--high-req::after { background: #ef4444; width: 5px; height: 5px; }
.cal-legend { display: flex; gap: 12px; margin-top: 12px; flex-wrap: wrap; }
.cal-legend-item { display: flex; align-items: center; gap: 5px; font-size: 9.5px; color: rgba(255,255,255,0.4); }
.cal-legend-dot { width: 6px; height: 6px; border-radius: 50%; }

.cal-public-btn {
    display: flex; align-items: center; justify-content: center; gap: 7px;
    margin-top: 14px; padding: 9px 14px;
    background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.18);
    border-radius: 10px; color: rgba(255,255,255,0.75);
    font-size: 11.5px; font-weight: 600; text-decoration: none;
    transition: all .15s; font-family: var(--font);
}
.cal-public-btn:hover { background: rgba(255,255,255,0.18); color: white; }

/* ── BUBBLES CARD ────────────────────── */
.bubbles-card {
    background: white;
    border: 1.5px solid rgba(0,0,0,0.06);
    border-radius: var(--radius-card);
    padding: 22px 26px 24px;
    grid-column: 1; grid-row: 2;
    position: relative; overflow: hidden;
}
.bubbles-card__title { font-family: var(--font-disp); font-size: 17px; color: var(--ink); margin-bottom: 2px; }
.bubbles-card__sub   { font-size: 12px; color: var(--ink-soft); margin-bottom: 16px; }

.bubble-inner { display: flex; align-items: center; gap: 0; }
.bubble-chart-wrap {
    flex: 1; position: relative; height: 190px;
    display: flex; align-items: center; justify-content: center;
}
.bubble {
    position: absolute; border-radius: 50%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    box-shadow: 0 8px 28px rgba(0,0,0,0.14);
    transition: transform .2s; cursor: default;
}
.bubble:hover { transform: scale(1.07) !important; }
.bubble__num { font-size: 22px; font-weight: 700; color: white; line-height: 1; }
.bubble__lbl { font-size: 9.5px; font-weight: 500; color: rgba(255,255,255,0.8); margin-top: 2px; text-align: center; }
.bubble--total   { width: 110px; height: 110px; background: linear-gradient(135deg,#1e4fd8,#002366); top:50%; left:50%; transform:translate(-50%,-50%); z-index:3; }
.bubble--total .bubble__num { font-size: 30px; }
.bubble--pending { width: 78px; height: 78px; background: linear-gradient(135deg,#f59e0b,#d97706); top:8%; left:10%; z-index:2; }
.bubble--review  { width: 64px; height: 64px; background: linear-gradient(135deg,#38bdf8,#0284c7); bottom:6%; left:8%; z-index:2; }
.bubble--approved{ width: 74px; height: 74px; background: linear-gradient(135deg,#10b981,#059669); top:6%; right:10%; z-index:2; }
.bubble--posted  { width: 58px; height: 58px; background: linear-gradient(135deg,#8b5cf6,#6d28d9); bottom:10%; right:14%; z-index:2; }
.bubble--rejected{ width: 52px; height: 52px; background: linear-gradient(135deg,#ef4444,#dc2626); top:50%; right:2%; transform:translateY(-50%); z-index:2; }

.bubble-legend-wrap {
    width: 148px; flex-shrink: 0;
    display: flex; flex-direction: column; gap: 8px;
    padding-left: 18px; border-left: 1.5px solid rgba(0,0,0,0.06);
}
.bubble-legend-title { font-size: 10.5px; font-weight: 700; color: var(--ink-soft); text-transform: uppercase; letter-spacing: 0.7px; margin-bottom: 4px; }
.bl-item { display: flex; align-items: center; gap: 8px; }
.bl-dot  { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.bl-name { font-size: 12.5px; color: var(--ink-mid); flex: 1; }
.bl-num  { font-size: 12.5px; font-weight: 700; color: var(--ink); }

/* Mobile bubble grid — hidden on desktop */
.bubble-mobile-grid { display: none; }

/* ── MINI STATS ROW ──────────────────── */
.mini-row {
    display: grid; grid-template-columns: repeat(3,1fr); gap: 14px;
    grid-column: 1 / 3; grid-row: 3;
}
.mini-card {
    background: white; border: 1.5px solid rgba(0,0,0,0.06);
    border-radius: 18px; padding: 20px;
    display: flex; align-items: center; gap: 14px;
    transition: transform .2s, box-shadow .2s;
}
.mini-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.07); }
.mini-card__icon { width: 46px; height: 46px; border-radius: 13px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mini-card__label { font-size: 11.5px; color: var(--ink-soft); font-weight: 500; margin-bottom: 3px; }
.mini-card__value { font-size: 26px; font-weight: 700; color: var(--ink); letter-spacing: -1px; line-height: 1; }
.mini-card__sub   { font-size: 10.5px; color: var(--ink-soft); margin-top: 3px; }

/* ── BOTTOM ROW ──────────────────────── */
.bottom-row {
    display: grid; grid-template-columns: 1fr 300px; gap: 16px;
    grid-column: 1 / 3; grid-row: 4;
}

/* ── RECENT TABLE ────────────────────── */
.panel {
    background: white; border: 1.5px solid rgba(0,0,0,0.06);
    border-radius: var(--radius-card); overflow: hidden;
}
.panel__head {
    padding: 18px 22px 14px; display: flex; align-items: flex-start; justify-content: space-between;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}
.panel__title { font-family: var(--font-disp); font-size: 17px; color: var(--ink); }
.panel__sub   { font-size: 11.5px; color: var(--ink-soft); margin-top: 3px; }
.panel__link  { font-size: 12px; color: #1e4fd8; font-weight: 600; text-decoration: none; white-space: nowrap; }
.panel__link:hover { text-decoration: underline; }

.req-list { display: flex; flex-direction: column; }
.req-item {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 22px; border-bottom: 1px solid rgba(0,0,0,0.05);
    transition: background .12s; cursor: pointer; text-decoration: none;
}
.req-item:last-child { border-bottom: none; }
.req-item:hover { background: #f5f7ff; }
.req-av {
    width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: white; overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}
.req-av img { width: 100%; height: 100%; object-fit: cover; }
.req-info { flex: 1; min-width: 0; }
.req-title { font-size: 13px; font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.req-meta  { font-size: 11px; color: var(--ink-soft); margin-top: 1px; }
.req-status-lbl { font-size: 11px; font-weight: 700; white-space: nowrap; }

/* ── QUICK ACTIONS ───────────────────── */
.qa-panel { background: white; border: 1.5px solid rgba(0,0,0,0.06); border-radius: var(--radius-card); overflow: hidden; }
.qa-head  { padding: 18px 22px 14px; border-bottom: 1px solid rgba(0,0,0,0.05); }
.qa-head-title { font-family: var(--font-disp); font-size: 17px; color: var(--ink); }
.qa-item {
    display: flex; align-items: center; gap: 13px;
    padding: 14px 20px; border-bottom: 1px solid rgba(0,0,0,0.05);
    text-decoration: none; color: var(--ink); transition: background .12s;
}
.qa-item:last-child { border-bottom: none; }
.qa-item:hover { background: #f5f7ff; }
.qa-icon { width: 38px; height: 38px; border-radius: 11px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.qa-icon--blue   { background: #dbeafe; color: #2563eb; }
.qa-icon--amber  { background: #fef3c7; color: #d97706; }
.qa-icon--green  { background: #dcfce7; color: #16a34a; }
.qa-icon--purple { background: #ede9fe; color: #7c3aed; }
.qa-label { font-size: 13px; font-weight: 600; color: var(--ink); }
.qa-sub   { font-size: 11px; color: var(--ink-soft); margin-top: 1px; }
.qa-arrow { margin-left: auto; color: #9ca3af; font-size: 15px; }

.empty-state { padding: 40px 20px; text-align: center; color: #9ca3af; }
.empty-state p { font-size: 13px; margin-top: 10px; }
.empty-state a { color: #1e4fd8; font-weight: 600; text-decoration: none; }

/* ── RESPONSIVE ──────────────────────── */
@media (max-width: 768px) {

    .db-wrap {
        padding: 16px 14px 40px;
        grid-template-columns: 1fr;
        grid-template-rows: auto;
    }

    /* Stack all grid items into a single column */
    .hero-card     { grid-column: 1; grid-row: auto; }
    .calendar-card { grid-column: 1; grid-row: auto; }
    .bubbles-card  { grid-column: 1; grid-row: auto; }
    .mini-row      { grid-column: 1; grid-row: auto; grid-template-columns: 1fr; }
    .bottom-row    { grid-column: 1; grid-row: auto; grid-template-columns: 1fr; }

    /* Hero adjustments */
    .hero-card { padding: 22px 20px; min-height: unset; }
    .hero-card__title { font-size: 24px; }
    .hero-card__bottom { gap: 8px; }
    .hero-stat { padding: 8px 12px; }
    .hero-stat__num { font-size: 18px; }
    .hero-card__btn { margin-left: 0; width: 100%; justify-content: center; margin-top: 4px; }

    /* Calendar: show as a compact strip, no row-span */
    .calendar-card { cursor: default; }
    .calendar-card:hover { transform: none; box-shadow: 0 8px 32px rgba(0,26,77,0.3); }

    /* ── BUBBLES MOBILE FIX ──
       Hide the absolute-positioned chart, show a clean flex-wrap grid instead */
    .bubble-inner { flex-direction: column; gap: 16px; }
    .bubble-chart-wrap { display: none; }

    .bubble-mobile-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
        padding: 4px 0 8px;
    }
    .bubble-mobile-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 68px; height: 68px;
        border-radius: 50%;
        box-shadow: 0 4px 14px rgba(0,0,0,0.15);
    }
    .bubble-mobile-item.b-total {
        width: 90px; height: 90px;
        background: linear-gradient(135deg,#1e4fd8,#002366);
    }
    .bubble-mobile-item.b-pending  { background: linear-gradient(135deg,#f59e0b,#d97706); }
    .bubble-mobile-item.b-review   { background: linear-gradient(135deg,#38bdf8,#0284c7); }
    .bubble-mobile-item.b-approved { background: linear-gradient(135deg,#10b981,#059669); }
    .bubble-mobile-item.b-posted   { background: linear-gradient(135deg,#8b5cf6,#6d28d9); }
    .bubble-mobile-item.b-rejected { background: linear-gradient(135deg,#ef4444,#dc2626); }
    .bubble-mobile-item .bm-num {
        font-size: 20px; font-weight: 700;
        color: white; line-height: 1;
    }
    .bubble-mobile-item.b-total .bm-num { font-size: 26px; }
    .bubble-mobile-item .bm-lbl {
        font-size: 9px; font-weight: 500;
        color: rgba(255,255,255,0.85); margin-top: 3px; text-align: center;
    }

    .bubble-legend-wrap {
        width: 100%; border-left: none;
        border-top: 1.5px solid rgba(0,0,0,0.06);
        padding-left: 0; padding-top: 16px;
        display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
    }
    .bubble-legend-title { grid-column: 1 / 3; }

    /* Mini stats: 1 column */
    .mini-card { padding: 16px; }
    .mini-card__value { font-size: 22px; }

    /* Bottom row panels full width */
    .panel, .qa-panel { border-radius: 16px; }
}

/* ── DARK THEME OVERRIDES ── */
[data-theme="dark"] .bubbles-card {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
}
[data-theme="dark"] .bubbles-card__title {
    color: #f8fafc !important;
}
[data-theme="dark"] .bubbles-card__sub {
    color: #94a3b8 !important;
}
[data-theme="dark"] .bubble-legend-wrap {
    border-left-color: rgba(30, 79, 216, 0.25) !important;
    border-top-color: rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .bubble-legend-title {
    color: #94a3b8 !important;
}
[data-theme="dark"] .bl-name {
    color: #cbd5e1 !important;
}
[data-theme="dark"] .bl-num {
    color: #f8fafc !important;
}
[data-theme="dark"] .mini-card {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2) !important;
}
[data-theme="dark"] .mini-card:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35) !important;
    border-color: rgba(59, 110, 245, 0.4) !important;
}
[data-theme="dark"] .mini-card__label {
    color: #94a3b8 !important;
}
[data-theme="dark"] .mini-card__value {
    color: #f8fafc !important;
}
[data-theme="dark"] .mini-card__sub {
    color: #64748b !important;
}
[data-theme="dark"] .panel {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
}
[data-theme="dark"] .panel__head {
    background: #06112a !important;
    border-bottom-color: rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .panel__title {
    color: #f8fafc !important;
}
[data-theme="dark"] .panel__sub {
    color: #94a3b8 !important;
}
[data-theme="dark"] .panel__link {
    color: #60a5fa !important;
}
[data-theme="dark"] .req-item {
    border-bottom-color: rgba(30, 79, 216, 0.15) !important;
}
[data-theme="dark"] .req-item:hover {
    background: rgba(30, 79, 216, 0.12) !important;
}
[data-theme="dark"] .req-title {
    color: #f8fafc !important;
}
[data-theme="dark"] .req-meta {
    color: #94a3b8 !important;
}
[data-theme="dark"] .qa-panel {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
}
[data-theme="dark"] .qa-head {
    background: #06112a !important;
    border-bottom-color: rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .qa-head-title {
    color: #f8fafc !important;
}
[data-theme="dark"] .qa-item {
    color: #f8fafc !important;
    border-bottom-color: rgba(30, 79, 216, 0.15) !important;
}
[data-theme="dark"] .qa-item:hover {
    background: rgba(30, 79, 216, 0.12) !important;
}
[data-theme="dark"] .qa-label {
    color: #f8fafc !important;
}
[data-theme="dark"] .qa-sub {
    color: #94a3b8 !important;
}
[data-theme="dark"] .qa-arrow {
    color: #64748b !important;
}
[data-theme="dark"] .empty-state {
    color: #64748b !important;
}
[data-theme="dark"] .empty-state p {
    color: #94a3b8 !important;
}
</style>
@endsection

@section('content')
@php
    $total_all = $pending + $review + $approved + $posted;
    $rate      = $total_all > 0 ? round(($approved + $posted) / $total_all * 100) : 0;
    $rejected  = $rejected ?? 0;
    $attention = $pending + $review;

    // Calendar
    $cal_month = (int)request('month', date('n'));
    $cal_year  = (int)request('year',  date('Y'));
    $prev_m = $cal_month - 1; $prev_y = $cal_year;
    if ($prev_m < 1) { $prev_m = 12; $prev_y--; }
    $next_m = $cal_month + 1; $next_y = $cal_year;
    if ($next_m > 12) { $next_m = 1; $next_y++; }
    $first_day     = (int)date('w', mktime(0,0,0,$cal_month,1,$cal_year));
    $days_in_month = (int)date('t', mktime(0,0,0,$cal_month,1,$cal_year));
    $days_in_prev  = (int)date('t', mktime(0,0,0,$prev_m,1,$prev_y));
    $today_d = (int)date('j'); $today_m = (int)date('n'); $today_y = (int)date('Y');

    // User's own requests on calendar
    $cal_events = [];
    try {
        $user_requests = \App\Models\PostRequest::where('user_id', session('user_id'))
            ->whereNotNull('preferred_date')
            ->whereMonth('preferred_date', $cal_month)
            ->whereYear('preferred_date', $cal_year)
            ->get();
        foreach ($user_requests as $ev) {
            $d = (int)date('j', strtotime($ev->preferred_date));
            $cal_events[$d] = ($cal_events[$d] ?? 0) + 1;
        }
    } catch (\Exception $e) {}

    // Avatar colors pool
    $av_colors = ['#2563eb','#10b981','#8b5cf6','#f59e0b','#ef4444','#0ea5e9'];
@endphp

<div class="db-wrap">

    {{-- ── HERO ─────────────────────────────────────── --}}
    <div class="hero-card">
        <div class="hero-card__glow"></div>
        <div class="hero-card__glow2"></div>
        <div>
            <div class="hero-card__label">NUPost Requestor Portal</div>
            <div class="hero-card__title">Welcome Back,<br>{{ session('name', 'there') }}.</div>
        </div>
        <div class="hero-card__bottom">
            <div class="hero-stat">
                <div class="hero-stat__dot" style="color:#fbbf24;background:#fbbf24;"></div>
                <div>
                    <div class="hero-stat__num">{{ $total_all }}</div>
                    <div class="hero-stat__lbl">Total Requests</div>
                </div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat__dot" style="color:#34d399;background:#34d399;"></div>
                <div>
                    <div class="hero-stat__num">{{ $rate }}%</div>
                    <div class="hero-stat__lbl">Approval Rate</div>
                </div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat__dot" style="color:#f87171;background:#f87171;"></div>
                <div>
                    <div class="hero-stat__num">{{ $attention }}</div>
                    <div class="hero-stat__lbl">Need Attention</div>
                </div>
            </div>
            <a href="{{ route('requestor.requests') }}" class="hero-card__btn">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                View All Requests
            </a>
        </div>
    </div>

    {{-- ── CALENDAR CARD (spans rows 1–2 on right col) ─ --}}
    <div class="calendar-card" onclick="window.location='{{ route('requestor.calendar') }}'">
        <div class="cal-header">
            <div class="cal-title">My Schedule</div>
            <div class="cal-month-nav">
                <a href="?month={{ $prev_m }}&year={{ $prev_y }}" class="cal-nav-btn" onclick="event.stopPropagation()">
                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
                <span>{{ date('M Y', mktime(0,0,0,$cal_month,1,$cal_year)) }}</span>
                <a href="?month={{ $next_m }}&year={{ $next_y }}" class="cal-nav-btn" onclick="event.stopPropagation()">
                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>
        </div>
        <div class="cal-grid-head">
            @foreach(['M','T','W','T','F','S','S'] as $d)
                <span>{{ $d }}</span>
            @endforeach
        </div>
        <div class="cal-grid">
        @php
            $start_dow = ($first_day === 0) ? 6 : $first_day - 1;
            $total_cells = ceil(($start_dow + $days_in_month) / 7) * 7;
            for ($i = 0; $i < $total_cells; $i++) {
                $day_num    = $i - $start_dow + 1;
                $is_current = ($day_num >= 1 && $day_num <= $days_in_month);
                $is_today   = $is_current && $day_num === $today_d && $cal_month === $today_m && $cal_year === $today_y;
                if ($is_current) $display = $day_num;
                elseif ($i < $start_dow) $display = $days_in_prev - ($start_dow - $i - 1);
                else $display = $day_num - $days_in_month;
                $ev_count = $is_current ? ($cal_events[$day_num] ?? 0) : 0;
                $cls = 'cal-day';
                if (!$is_current) $cls .= ' cal-day--other';
                if ($is_today)    $cls .= ' cal-day--today';
                elseif ($ev_count >= 3) $cls .= ' cal-day--has-req cal-day--high-req';
                elseif ($ev_count > 0)  $cls .= ' cal-day--has-req';
                echo "<div class='$cls'>$display</div>";
            }
        @endphp
        </div>
        <div class="cal-legend">
            <div class="cal-legend-item"><span class="cal-legend-dot" style="background:#f59e0b;"></span>Today</div>
            <div class="cal-legend-item"><span class="cal-legend-dot" style="background:#f59e0b;opacity:.6;"></span>Has request</div>
            <div class="cal-legend-item"><span class="cal-legend-dot" style="background:#ef4444;"></span>3+ requests</div>
        </div>
        <a href="{{ route('requestor.calendar', ['toggle_public' => 1]) }}"
           class="cal-public-btn" onclick="event.stopPropagation()">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            View Public Calendar
        </a>
    </div>

    {{-- ── BUBBLES STATUS ───────────────────────────── --}}
    <div class="bubbles-card">
        <div class="bubbles-card__title">Request Status</div>
        <div class="bubbles-card__sub">Overview of all your submissions</div>
        <div class="bubble-inner">

            {{-- Desktop: absolute-positioned bubble chart --}}
            <div class="bubble-chart-wrap">
                <div class="bubble bubble--total">
                    <div class="bubble__num">{{ $total_all }}</div>
                    <div class="bubble__lbl">Total</div>
                </div>
                <div class="bubble bubble--pending">
                    <div class="bubble__num">{{ $pending }}</div>
                    <div class="bubble__lbl">Pending</div>
                </div>
                <div class="bubble bubble--review">
                    <div class="bubble__num">{{ $review }}</div>
                    <div class="bubble__lbl">Review</div>
                </div>
                <div class="bubble bubble--approved">
                    <div class="bubble__num">{{ $approved }}</div>
                    <div class="bubble__lbl">Approved</div>
                </div>
                <div class="bubble bubble--posted">
                    <div class="bubble__num">{{ $posted }}</div>
                    <div class="bubble__lbl">Posted</div>
                </div>
                <div class="bubble bubble--rejected">
                    <div class="bubble__num">{{ $rejected }}</div>
                    <div class="bubble__lbl">Rejected</div>
                </div>
            </div>

            {{-- Mobile: clean flex-wrap grid, no overlapping --}}
            <div class="bubble-mobile-grid">
                <div class="bubble-mobile-item b-total">
                    <div class="bm-num">{{ $total_all }}</div>
                    <div class="bm-lbl">Total</div>
                </div>
                <div class="bubble-mobile-item b-pending">
                    <div class="bm-num">{{ $pending }}</div>
                    <div class="bm-lbl">Pending</div>
                </div>
                <div class="bubble-mobile-item b-review">
                    <div class="bm-num">{{ $review }}</div>
                    <div class="bm-lbl">Review</div>
                </div>
                <div class="bubble-mobile-item b-approved">
                    <div class="bm-num">{{ $approved }}</div>
                    <div class="bm-lbl">Approved</div>
                </div>
                <div class="bubble-mobile-item b-posted">
                    <div class="bm-num">{{ $posted }}</div>
                    <div class="bm-lbl">Posted</div>
                </div>
                <div class="bubble-mobile-item b-rejected">
                    <div class="bm-num">{{ $rejected }}</div>
                    <div class="bm-lbl">Rejected</div>
                </div>
            </div>

            <div class="bubble-legend-wrap">
                <div class="bubble-legend-title">Breakdown</div>
                @foreach([
                    ['Pending',      $pending,  '#f59e0b'],
                    ['Under Review', $review,   '#38bdf8'],
                    ['Approved',     $approved, '#10b981'],
                    ['Posted',       $posted,   '#8b5cf6'],
                    ['Rejected',     $rejected, '#ef4444'],
                ] as [$lbl,$val,$col])
                <div class="bl-item">
                    <span class="bl-dot" style="background:{{ $col }};"></span>
                    <span class="bl-name">{{ $lbl }}</span>
                    <span class="bl-num">{{ $val }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── MINI STATS ───────────────────────────────── --}}
    <div class="mini-row">
        <div class="mini-card">
            <div class="mini-card__icon" style="background:#eff6ff;">
                <svg width="22" height="22" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <div class="mini-card__label">Pending Review</div>
                <div class="mini-card__value" style="color:#d97706;">{{ $pending }}</div>
                <div class="mini-card__sub">awaiting admin</div>
            </div>
        </div>
        <div class="mini-card">
            <div class="mini-card__icon" style="background:#f0fdf4;">
                <svg width="22" height="22" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div>
                <div class="mini-card__label">Approval Rate</div>
                <div class="mini-card__value" style="color:#047857;">{{ $rate }}%</div>
                <div class="mini-card__sub">approved + posted</div>
            </div>
        </div>
        <div class="mini-card">
            <div class="mini-card__icon" style="background:#faf5ff;">
                <svg width="22" height="22" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </div>
            <div>
                <div class="mini-card__label">Posted Live</div>
                <div class="mini-card__value" style="color:#6d28d9;">{{ $posted }}</div>
                <div class="mini-card__sub">live on platforms</div>
            </div>
        </div>
    </div>

    {{-- ── BOTTOM: RECENT + QUICK ACTIONS ──────────── --}}
    <div class="bottom-row">
        {{-- Recent Requests --}}
        <div class="panel">
            <div class="panel__head">
                <div>
                    <div class="panel__title">Recent Requests</div>
                    <div class="panel__sub">Latest submissions</div>
                </div>
                <a href="{{ route('requestor.requests') }}" class="panel__link">View all →</a>
            </div>
            <div class="req-list">
            @forelse($recent as $req)
                @php
                    $sl = strtolower($req->status);
                    $sc = match(true) {
                        str_contains($sl,'approved')     => ['#10b981','Approved'],
                        str_contains($sl,'posted')       => ['#8b5cf6','Posted'],
                        str_contains($sl,'under review') => ['#0ea5e9','Reviewing'],
                        str_contains($sl,'rejected')     => ['#ef4444','Rejected'],
                        default                          => ['#f59e0b','Pending'],
                    };
                    $thumb = $req->first_media;
                    $av_c  = $av_colors[crc32($req->title) % count($av_colors)];
                @endphp
                <a href="{{ route('requestor.requests.chat', $req->id) }}" class="req-item">
                    <div class="req-av" style="background:{{ $av_c }};">
                        @if($thumb)
                            <img src="/uploads/{{ $thumb }}" alt="" onerror="this.style.display='none'">
                        @else
                            {{ strtoupper(substr($req->title,0,1)) }}
                        @endif
                    </div>
                    <div class="req-info">
                        <div class="req-title">{{ Str::limit($req->title, 34) }}</div>
                        <div class="req-meta">{{ $req->category }} · {{ $req->created_at->format('M j, Y') }}</div>
                    </div>
                    <div class="req-status-lbl" style="color:{{ $sc[0] }};">{{ $sc[1] }}</div>
                </a>
            @empty
                <div class="empty-state">
                    <svg width="36" height="36" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <p>No requests yet. <a href="{{ route('requestor.requests.create') }}">Create one →</a></p>
                </div>
            @endforelse
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="qa-panel">
            <div class="qa-head"><div class="qa-head-title">Quick Actions</div></div>
            <a href="{{ route('requestor.requests.create') }}" class="qa-item">
                <div class="qa-icon qa-icon--blue"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></div>
                <div><div class="qa-label">New Request</div><div class="qa-sub">Submit a post request</div></div>
                <span class="qa-arrow">→</span>
            </a>
            <a href="{{ route('requestor.requests', ['filter'=>'pending']) }}" class="qa-item">
                <div class="qa-icon qa-icon--amber"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
                <div><div class="qa-label">Pending ({{ $pending }})</div><div class="qa-sub">Awaiting admin review</div></div>
                <span class="qa-arrow">→</span>
            </a>
            <a href="{{ route('requestor.requests', ['filter'=>'approved']) }}" class="qa-item">
                <div class="qa-icon qa-icon--green"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg></div>
                <div><div class="qa-label">Approved ({{ $approved }})</div><div class="qa-sub">Ready to go live</div></div>
                <span class="qa-arrow">→</span>
            </a>
            <a href="{{ route('requestor.calendar') }}" class="qa-item">
                <div class="qa-icon qa-icon--purple"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></div>
                <div><div class="qa-label">Calendar</div><div class="qa-sub">View post schedule</div></div>
                <span class="qa-arrow">→</span>
            </a>
        </div>
    </div>

</div>
@endsection