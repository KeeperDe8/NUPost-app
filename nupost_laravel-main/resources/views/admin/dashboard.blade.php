@extends('layouts.admin')

@section('title', 'Dashboard')

@section('head-styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Serif+Display&display=swap');

:root {
    --cream:   #f0ede8;
    --card:    #faf9f7;
    --card2:   #1c1c1e;
    --sand:    #e8e3da;
    --ink:     #1a1a1a;
    --ink-mid: #3d3d3d;
    --ink-soft:#8a8a8a;
    --stroke:  #ddd8d0;
    --accent:  #002366;
    --amber:   #f59e0b;
    --green:   #10b981;
    --red:     #ef4444;
    --purple:  #8b5cf6;
    --radius-card: 22px;
    --font-body: 'DM Sans', sans-serif;
    --font-disp: 'DM Serif Display', serif;
}

/* Override layout bg */
body { background: var(--cream) !important; }

/* ── DASHBOARD WRAPPER ───────────────────────────────── */
.db-wrap {
    display: grid;
    grid-template-columns: 1fr 310px;
    grid-template-rows: auto auto auto;
    gap: 16px;
    font-family: var(--font-body);
}

/* ── HERO CARD (top left) ────────────────────────────── */
.hero-card {
    background: linear-gradient(135deg, #001a4d 0%, #002e7a 60%, #003a8c 100%);
    border-radius: var(--radius-card);
    padding: 28px 30px;
    position: relative; overflow: hidden;
    min-height: 180px;
    display: flex; flex-direction: column; justify-content: space-between;
    grid-column: 1; grid-row: 1;
    box-shadow: 0 8px 32px rgba(0,26,77,0.35);
}
.hero-card__glow {
    position: absolute; top: -40px; right: -40px;
    width: 220px; height: 220px; border-radius: 50%;
    background: radial-gradient(circle, rgba(59,130,246,0.25) 0%, transparent 65%);
    pointer-events: none;
}
.hero-card__glow2 {
    position: absolute; bottom: -30px; left: 60px;
    width: 160px; height: 160px; border-radius: 50%;
    background: radial-gradient(circle, rgba(245,158,11,0.15) 0%, transparent 65%);
    pointer-events: none;
}
.hero-card__greeting { font-size: 12px; color: rgba(255,255,255,0.55); font-weight: 500; margin-bottom: 6px; letter-spacing: 0.3px; }
.hero-card__title { font-family: var(--font-disp); font-size: 30px; color: white; line-height: 1.2; margin-bottom: 4px; text-shadow: 0 2px 8px rgba(0,0,0,0.2); }
.hero-card__sub { font-size: 12.5px; color: rgba(255,255,255,0.45); }
.hero-card__bottom { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; margin-top: 20px; }
.hero-stat {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.18);
    border-radius: 12px; padding: 10px 16px;
    display: flex; align-items: center; gap: 8px;
    backdrop-filter: blur(8px);
}
.hero-stat__num { font-size: 20px; font-weight: 700; color: white; }
.hero-stat__lbl { font-size: 10.5px; color: rgba(255,255,255,0.6); font-weight: 500; }
.hero-stat__dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; box-shadow: 0 0 6px currentColor; }

/* ── CALENDAR CARD (top right) ───────────────────────── */
.calendar-card {
    background: linear-gradient(160deg, #001a4d 0%, #002366 50%, #003080 100%);
    border-radius: var(--radius-card);
    padding: 22px 20px;
    grid-column: 2; grid-row: 1 / 3;
    box-shadow: 0 8px 32px rgba(0,26,77,0.3);
    transition: transform .2s, box-shadow .2s;
}
.calendar-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 40px rgba(0,26,77,0.4);
}
.cal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
.cal-title { font-family: var(--font-disp); font-size: 16px; color: white; }
.cal-month-nav { display: flex; align-items: center; gap: 8px; }
.cal-month-nav span { font-size: 12px; color: rgba(255,255,255,0.65); font-weight: 500; }
.cal-nav-btn {
    width: 26px; height: 26px; border-radius: 7px;
    background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);
    cursor: pointer; color: rgba(255,255,255,0.65);
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; font-size: 12px; transition: all .15s;
}
.cal-nav-btn:hover { background: rgba(255,255,255,0.2); color: white; }
.cal-grid-head { display: grid; grid-template-columns: repeat(7,1fr); margin-bottom: 6px; }
.cal-grid-head span { font-size: 10px; font-weight: 700; color: rgba(255,255,255,0.4); text-align: center; padding: 4px 0; letter-spacing: 0.5px; }
.cal-grid { display: grid; grid-template-columns: repeat(7,1fr); gap: 2px; }
.cal-day {
    aspect-ratio: 1; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 11.5px; font-weight: 500; color: rgba(255,255,255,0.55);
    position: relative; cursor: default;
}
.cal-day--other { color: rgba(255,255,255,0.18); }
/* TODAY — yellow like reference */
.cal-day--today {
    background: #f59e0b !important;
    color: #1a1a1a !important;
    font-weight: 800;
    border-radius: 50%;
    box-shadow: 0 3px 12px rgba(245,158,11,0.45);
}
.cal-day--has-req { color: rgba(255,255,255,0.9); }
.cal-day--has-req::after {
    content: ''; position: absolute; bottom: 3px; left: 50%; transform: translateX(-50%);
    width: 4px; height: 4px; border-radius: 50%; background: #f59e0b;
}
.cal-day--high-req::after { background: #ef4444; width: 5px; height: 5px; }
.cal-legend { display: flex; gap: 14px; margin-top: 14px; flex-wrap: wrap; }
.cal-legend-item { display: flex; align-items: center; gap: 6px; font-size: 10px; color: rgba(255,255,255,0.4); }
.cal-legend-dot { width: 7px; height: 7px; border-radius: 50%; }

/* ── STATUS BUBBLES (row 2 col 1) ────────────────────── */
.bubbles-card {
    background: var(--card);
    border: 1.5px solid rgba(0,0,0,0.06);
    border-radius: var(--radius-card);
    padding: 24px 26px;
    display: flex; align-items: center; gap: 0;
    grid-column: 1; grid-row: 2;
    position: relative; overflow: hidden;
}
.bubble-chart-wrap {
    flex: 1; display: flex; align-items: center; justify-content: center;
    position: relative; height: 180px;
}
.bubble {
    position: absolute; border-radius: 50%;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    font-family: var(--font-body);
    box-shadow: 0 8px 32px rgba(0,0,0,0.12);
    transition: transform .2s;
}
.bubble:hover { transform: scale(1.06); }
.bubble__num { font-size: 20px; font-weight: 700; color: white; line-height: 1; }
.bubble__lbl { font-size: 9px; font-weight: 500; color: rgba(255,255,255,0.75); margin-top: 2px; text-align: center; }
.bubble--total   { width: 110px; height: 110px; background: linear-gradient(135deg,#1e4fd8,#002366); top: 50%; left: 50%; transform: translate(-50%,-50%); z-index: 3; }
.bubble--pending { width: 78px; height: 78px; background: linear-gradient(135deg,#f59e0b,#d97706); top: 10%; left: 12%; z-index: 2; }
.bubble--review  { width: 64px; height: 64px; background: linear-gradient(135deg,#0ea5e9,#0284c7); bottom: 8%; left: 8%; z-index: 2; }
.bubble--approved{ width: 72px; height: 72px; background: linear-gradient(135deg,#10b981,#059669); top: 8%; right: 10%; z-index: 2; }
.bubble--posted  { width: 58px; height: 58px; background: linear-gradient(135deg,#8b5cf6,#6d28d9); bottom: 12%; right: 14%; z-index: 2; }
.bubble--rejected{ width: 52px; height: 52px; background: linear-gradient(135deg,#ef4444,#dc2626); top: 50%; right: 2%; transform: translateY(-50%); z-index: 2; }
.bubble-legend-wrap { width: 140px; flex-shrink: 0; display: flex; flex-direction: column; gap: 8px; padding-left: 16px; border-left: 1.5px solid rgba(0,0,0,0.06); }
.bubble-legend-title { font-size: 11px; font-weight: 600; color: var(--ink-soft); text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 4px; }
.bl-item { display: flex; align-items: center; gap: 8px; }
.bl-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.bl-name { font-size: 12px; color: var(--ink-mid); flex: 1; }
.bl-num  { font-size: 12px; font-weight: 700; color: var(--ink); }
.bubbles-card__title { position: absolute; top: 22px; left: 26px; font-family: var(--font-disp); font-size: 17px; color: var(--ink); }
.bubbles-card__sub   { position: absolute; top: 46px; left: 26px; font-size: 12px; color: var(--ink-soft); }

/* ── MINI STATS ROW ──────────────────────────────────── */
.mini-row {
    display: grid; grid-template-columns: repeat(3,1fr); gap: 14px;
    grid-column: 1 / 3; grid-row: 3;
}
.mini-card {
    background: var(--card);
    border: 1.5px solid rgba(0,0,0,0.06);
    border-radius: 18px; padding: 20px;
    display: flex; align-items: center; gap: 14px;
    transition: transform .2s, box-shadow .2s;
}
.mini-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.07); }
.mini-card__icon { width: 46px; height: 46px; border-radius: 13px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mini-card__label { font-size: 11.5px; color: var(--ink-soft); font-weight: 500; margin-bottom: 3px; }
.mini-card__value { font-size: 24px; font-weight: 700; color: var(--ink); letter-spacing: -0.8px; }
.mini-card__sub   { font-size: 10.5px; color: var(--ink-soft); margin-top: 2px; }

/* ── CHART ROW ───────────────────────────────────────── */
.chart-row {
    display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
    grid-column: 1 / 3; grid-row: 4;
}

/* ── BASE PANEL ──────────────────────────────────────── */
.np {
    background: var(--card);
    border: 1.5px solid rgba(0,0,0,0.06);
    border-radius: var(--radius-card); overflow: hidden;
}
.np__head { padding: 20px 22px 14px; display: flex; align-items: flex-start; justify-content: space-between; }
.np__title { font-family: var(--font-disp); font-size: 17px; color: var(--ink); }
.np__sub   { font-size: 11.5px; color: var(--ink-soft); margin-top: 3px; }
.np__link  { font-size: 12px; color: var(--accent); font-weight: 600; text-decoration: none; white-space: nowrap; }
.np__link:hover { text-decoration: underline; }
.np__body  { padding: 0 22px 22px; }
.np__body--p { padding: 22px; }

/* chart */
.chart-wrap { position: relative; height: 200px; }

/* ── REQUEST LIST (habits style) ─────────────────────── */
.req-habit-list { display: flex; flex-direction: column; }
.req-habit-item {
    display: flex; align-items: center; gap: 13px;
    padding: 12px 22px; border-bottom: 1px solid rgba(0,0,0,0.06);
    transition: background .12s; cursor: pointer;
}
.req-habit-item:last-child { border-bottom: none; }
.req-habit-item:hover { background: var(--sand); }
.req-hab-av {
    width: 38px; height: 38px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: white; overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}
.req-hab-av img { width: 100%; height: 100%; object-fit: cover; }
.req-hab-info { flex: 1; min-width: 0; }
.req-hab-title { font-size: 13px; font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.req-hab-by    { font-size: 11px; color: var(--ink-soft); margin-top: 1px; }
.req-hab-right { flex-shrink: 0; display: flex; align-items: center; gap: 8px; }
.req-hab-bar-wrap { width: 80px; }
.req-hab-bar-label { font-size: 10px; color: var(--ink-soft); margin-bottom: 3px; text-align: right; }
.req-hab-bar-bg { height: 5px; background: rgba(0,0,0,0.06); border-radius: 3px; overflow: hidden; }
.req-hab-bar-fill { height: 100%; border-radius: 3px; }

/* ── TOP REQUESTORS (progress style) ────────────────── */
.trop-list { display: flex; flex-direction: column; gap: 14px; padding: 0 22px 22px; }
.trop-item { display: flex; align-items: center; gap: 12px; }
.trop-rank { font-size: 12px; font-weight: 700; color: var(--ink-soft); width: 18px; flex-shrink: 0; }
.trop-av {
    width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: white; overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}
.trop-av img { width: 100%; height: 100%; object-fit: cover; }
.trop-info { flex: 1; min-width: 0; }
.trop-name { font-size: 13px; font-weight: 600; color: var(--ink); }
.trop-dept { font-size: 10.5px; color: var(--ink-soft); margin-top: 1px; }
.trop-bar-wrap { flex: 1; }
.trop-bar-bg { height: 6px; background: rgba(0,0,0,0.06); border-radius: 3px; overflow: hidden; }
.trop-bar-fill { height: 100%; border-radius: 3px; }
.trop-count { font-size: 13px; font-weight: 700; color: var(--ink); width: 24px; text-align: right; flex-shrink: 0; }

/* ── CATEGORY SECTION ────────────────────────────────── */
.cat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; padding: 0 22px 22px; }
.cat-pill {
    background: var(--sand); border-radius: 12px; padding: 10px 14px;
    display: flex; align-items: center; gap: 10px; min-height: 44px;
    transition: transform .15s;
}
.cat-pill:hover { transform: scale(1.02); }
.cat-pill__dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.cat-pill__name { font-size: 11.5px; font-weight: 600; color: var(--ink); flex: 1; line-height: 1.2; white-space: normal; }
.cat-pill__count { font-size: 13px; font-weight: 700; color: var(--ink); margin-left: 4px; }

/* ── REPORTS SECTION ─────────────────────────────────── */
.reports-row {
    display: flex; flex-direction: column; gap: 10px;
    grid-column: 1 / 3; grid-row: 5;
}
/* Compact row style */
.rep-row-item {
    background: var(--card);
    border: 1.5px solid rgba(0,0,0,0.06);
    border-radius: 14px; padding: 14px 18px;
    display: flex; align-items: center; gap: 14px;
    transition: transform .15s, box-shadow .15s;
    text-decoration: none;
}
.rep-row-item:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.07); }
.rep-row-item__icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rep-row-item__name { font-size: 13.5px; font-weight: 700; color: var(--ink); width: 160px; flex-shrink: 0; }
.rep-row-item__desc { font-size: 12px; color: var(--ink-soft); flex: 1; line-height: 1.5; }
.rep-row-item__use  { font-size: 11px; color: var(--ink-faint); width: 260px; flex-shrink: 0; text-align: right; }
.rep-row-item__rows { font-size: 11.5px; font-weight: 700; padding: 4px 12px; border-radius: 20px; white-space: nowrap; flex-shrink: 0; margin-left: 12px; }
.rep-row-item__dl {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: 10px; border: none;
    font-size: 12px; font-weight: 700; cursor: pointer;
    font-family: var(--font-body); transition: opacity .15s; text-decoration: none;
    white-space: nowrap; flex-shrink: 0; margin-left: 10px;
}
.rep-row-item__dl:hover { opacity: 0.85; }

/* ── META PLACEHOLDER ────────────────────────────────── */
.meta-row {
    display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 14px;
    grid-column: 1 / 3; grid-row: 6;
}
.meta-mini {
    background: var(--card); border: 1.5px solid rgba(0,0,0,0.06);
    border-radius: 18px; padding: 20px;
    display: flex; flex-direction: column; gap: 10px;
}
.meta-mini__icon-wrap { width: 44px; height: 44px; border-radius: 12px; background: #dbeafe; display: flex; align-items: center; justify-content: center; }
.meta-mini__label { font-size: 12px; font-weight: 600; color: var(--ink-soft); }
.meta-mini__ph {
    flex: 1; background: var(--sand); border-radius: 10px;
    border: 1.5px dashed #93c5fd; min-height: 80px;
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 5px;
}
.meta-mini__ph p { font-size: 10.5px; color: #93c5fd; font-weight: 500; }
.meta-full {
    background: var(--card); border: 1.5px solid rgba(0,0,0,0.06);
    border-radius: 18px; padding: 22px;
    grid-column: 1 / 3; grid-row: 7; margin-bottom: 8px;
}
.meta-full__head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.meta-full__title { font-family: var(--font-disp); font-size: 17px; color: var(--ink); }
.meta-api-badge { font-size: 10.5px; font-weight: 700; padding: 4px 12px; border-radius: 20px; background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
.meta-chart-ph {
    background: var(--sand); border-radius: 14px; border: 2px dashed #93c5fd;
    height: 160px; display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 8px;
}
.meta-chart-ph__title { font-size: 14px; font-weight: 600; color: #3b82f6; }
.meta-chart-ph__sub { font-size: 11.5px; color: #93c5fd; text-align: center; max-width: 360px; }

@media (max-width: 1024px) {
    .db-wrap {
        grid-template-columns: 1fr;
    }

    /* All grid items go single column */
    .hero-card    { grid-column: 1; grid-row: auto; }
    .calendar-card { grid-column: 1; grid-row: auto; }
    .bubbles-card  { grid-column: 1; grid-row: auto; }
    .mini-row      { grid-column: 1; grid-row: auto; }
    .chart-row     { grid-column: 1; grid-row: auto; grid-template-columns: 1fr; }

    /* Recent + Categories row */
    .db-wrap > div[style*="grid-column:1/3;grid-row:5"] { grid-column: 1 !important; }
    .db-wrap > div[style*="grid-column:1/3;grid-row:6"] { grid-column: 1 !important; }
    .db-wrap > div[style*="grid-column:1/3;grid-row:7"] { grid-column: 1 !important; }

    /* Calendar loses its row-span */
    .calendar-card { cursor: default; }

    /* Bubbles stacks chart + legend */
    .bubbles-card { flex-direction: column; padding-top: 58px; }
    .bubble-chart-wrap { width: 100%; }
    .bubble-legend-wrap {
        width: 100%; border-left: none;
        border-top: 1.5px solid rgba(0,0,0,0.06);
        padding-left: 0; padding-top: 16px;
        display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
    }
    .bubble-legend-title { grid-column: 1 / 3; }
}

@media (max-width: 768px) {
    .db-wrap { padding: 0; gap: 12px; }

    /* Hero */
    .hero-card { padding: 22px 18px; min-height: unset; }
    .hero-card__title { font-size: 24px; }
    .hero-card__bottom { gap: 8px; }
    .hero-stat { padding: 8px 12px; }
    .hero-stat__num { font-size: 18px; }
    /* Export button full width */
    .hero-card__bottom > a { margin-left: 0 !important; width: 100%; justify-content: center; margin-top: 4px; }

    /* Mini stats: 1 column */
    .mini-row { grid-template-columns: 1fr !important; }
    .mini-card { padding: 16px; }

    /* Chart row already 1 col from 1024px breakpoint */
    .chart-wrap { height: 160px; }

    /* Recent + Categories stacks */
    .db-wrap > div[style*="grid-template-columns:1fr 340px"] {
        grid-template-columns: 1fr !important;
    }

    /* Reports row */
    .rep-row-item {
        flex-wrap: wrap;
        gap: 10px;
    }
    .rep-row-item__name { width: auto; }
    .rep-row-item__desc { width: 100%; order: 3; }
    .rep-row-item__use  { display: none; }
    .rep-row-item__dl   { margin-left: auto; }

    /* Meta section: 2 columns instead of 4 */
    .db-wrap > div[style*="grid-row:7"] > div > div[style*="grid-template-columns:repeat(4"] {
        grid-template-columns: repeat(2, 1fr) !important;
    }

    /* Meta full chart */
    .meta-chart-ph { height: 120px; }
    .meta-full { padding: 16px; }

    /* Calendar card */
    .calendar-card { padding: 16px 14px; }
    .cal-grid-head span { font-size: 9px; }
    .cal-day { font-size: 10.5px; }
}

@media (max-width: 480px) {
    .hero-card__title { font-size: 20px; }
    .mini-card__value { font-size: 20px; }

    /* Bubble chart smaller on phones */
    .bubble-chart-wrap { height: 160px; }
    .bubble--total   { width: 90px; height: 90px; }
    .bubble--pending { width: 64px; height: 64px; }
    .bubble--review  { width: 54px; height: 54px; }
    .bubble--approved{ width: 60px; height: 60px; }
    .bubble--posted  { width: 50px; height: 50px; }
    .bubble--rejected{ width: 44px; height: 44px; }

    /* Reports: hide row count badge */
    .rep-row-item__rows { display: none; }

    /* Meta: 1 column */
    .db-wrap > div[style*="grid-row:7"] > div > div[style*="grid-template-columns:repeat(4"] {
        grid-template-columns: 1fr 1fr !important;
    }
}
</style>
@endsection

@section('content')

@php
    $safe         = max($total, 1);
    $pct_pending  = round($pending  / $safe * 100);
    $pct_approved = round($approved / $safe * 100);
    $pct_posted   = round($posted   / $safe * 100);
    $pct_rejected = round($rejected / $safe * 100);
    $approval_rate= $total > 0 ? round(($approved+$posted)/$total*100) : 0;
    $attention    = $pending + $review;

    // Calendar data
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
    $events = [];
    $all_events = \App\Models\PostRequest::whereNotNull('preferred_date')
        ->whereMonth('preferred_date',$cal_month)->whereYear('preferred_date',$cal_year)->get();
    foreach ($all_events as $ev) {
        $d = (int)date('j', strtotime($ev->preferred_date));
        $events[$d] = ($events[$d] ?? 0) + 1;
    }

    // Categories
    $categories = \App\Models\PostRequest::selectRaw('category, count(*) as count')
        ->groupBy('category')->orderByDesc('count')->get();
    $cat_max    = $categories->max('count') ?: 1;
    $cat_colors = ['#2563eb','#10b981','#f59e0b','#8b5cf6','#ef4444','#0ea5e9','#f97316'];

    // Top requestors
    $top_requestors = \App\Models\PostRequest::selectRaw('requester, count(*) as total_requests')
        ->groupBy('requester')->orderByDesc('total_requests')->limit(5)->get();
    $top_users = \App\Models\User::whereIn('name',$top_requestors->pluck('requester'))->get()->keyBy('name');
    $tr_max    = $top_requestors->max('total_requests') ?: 1;
    $av_colors = ['linear-gradient(135deg,#2563eb,#1e40af)','linear-gradient(135deg,#10b981,#047857)','linear-gradient(135deg,#8b5cf6,#6d28d9)','linear-gradient(135deg,#f59e0b,#b45309)','linear-gradient(135deg,#ef4444,#b91c1c)'];
    $av_colors_solid = ['#2563eb','#10b981','#8b5cf6','#f59e0b','#ef4444'];

    // Chart
    $months_data = [];
    for ($i=5; $i>=0; $i--) {
        $m = now()->subMonths($i);
        $months_data[] = [
            'label'    => $m->format('M'),
            'total'    => \App\Models\PostRequest::whereYear('created_at',$m->year)->whereMonth('created_at',$m->month)->count(),
            'approved' => \App\Models\PostRequest::whereYear('created_at',$m->year)->whereMonth('created_at',$m->month)->whereIn('status',['Approved','Posted'])->count(),
        ];
    }

    // This month / high prio
    $this_month_count = \App\Models\PostRequest::whereMonth('created_at',now()->month)->whereYear('created_at',now()->year)->count();
    $high_prio_count  = \App\Models\PostRequest::whereIn('priority',['High','Urgent'])->count();
@endphp

<div class="db-wrap">

    {{-- ── HERO CARD ─────────────────────────────────────── --}}
    <div class="hero-card">
        <div class="hero-card__glow"></div>
        <div class="hero-card__glow2"></div>
        <div>
            <div class="hero-card__greeting">NUPost Admin Panel</div>
            <div class="hero-card__title">Welcome Back,<br>{{ session('admin_name', 'Administrator') }}</div>
        </div>
        <div class="hero-card__bottom">
            <div class="hero-stat">
                <div class="hero-stat__dot" style="background:#f59e0b;"></div>
                <div>
                    <div class="hero-stat__num">{{ $total }}</div>
                    <div class="hero-stat__lbl">Total Requests</div>
                </div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat__dot" style="background:#10b981;"></div>
                <div>
                    <div class="hero-stat__num">{{ $approval_rate }}%</div>
                    <div class="hero-stat__lbl">Approval Rate</div>
                </div>
            </div>
            <div class="hero-stat">
                <div class="hero-stat__dot" style="background:#ef4444;"></div>
                <div>
                    <div class="hero-stat__num">{{ $attention }}</div>
                    <div class="hero-stat__lbl">Need Attention</div>
                </div>
            </div>
            <a href="{{ route('admin.reports.export') }}"
               style="margin-left:auto;display:inline-flex;align-items:center;gap:7px;padding:10px 18px;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);border-radius:12px;color:white;font-size:12.5px;font-weight:600;text-decoration:none;transition:background .15s;font-family:var(--font-body);"
               onmouseover="this.style.background='rgba(255,255,255,0.2)'"
               onmouseout="this.style.background='rgba(255,255,255,0.12)'">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export CSV
            </a>
        </div>
    </div>

    {{-- ── CALENDAR CARD ─────────────────────────────────── --}}
    <div class="calendar-card" style="cursor:pointer;" onclick="window.location='{{ route('admin.calendar') }}'">
        <div class="cal-header">
            <div class="cal-title">Request Schedule</div>
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
                $ev_count = $is_current ? ($events[$day_num] ?? 0) : 0;
                $cls  = 'cal-day';
                if (!$is_current) $cls .= ' cal-day--other';
                if ($is_today)    $cls .= ' cal-day--today';
                elseif ($ev_count >= 3) $cls .= ' cal-day--has-req cal-day--high-req';
                elseif ($ev_count > 0)  $cls .= ' cal-day--has-req';
                echo "<div class='$cls'>$display</div>";
            }
        @endphp
        </div>
        <div class="cal-legend">
            <div class="cal-legend-item"><span class="cal-legend-dot" style="background:var(--accent);"></span>Today</div>
            <div class="cal-legend-item"><span class="cal-legend-dot" style="background:var(--amber);"></span>Has request</div>
            <div class="cal-legend-item"><span class="cal-legend-dot" style="background:#ef4444;"></span>3+ requests</div>
        </div>
    </div>

    {{-- ── BUBBLE STATUS CARD ────────────────────────────── --}}
    <div class="bubbles-card" style="padding-top:58px;">
        <div class="bubbles-card__title">Request Status</div>
        <div class="bubbles-card__sub">Overview of all submissions</div>
        <div class="bubble-chart-wrap">
            <div class="bubble bubble--total">
                <div class="bubble__num">{{ $total }}</div>
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
                <div class="bubble__lbl">No</div>
            </div>
        </div>
        <div class="bubble-legend-wrap">
            <div class="bubble-legend-title">Breakdown</div>
            @php
                $bl_items = [
                    ['l'=>'Pending','v'=>$pending,'c'=>'#f59e0b'],
                    ['l'=>'Under Review','v'=>$review,'c'=>'#0ea5e9'],
                    ['l'=>'Approved','v'=>$approved,'c'=>'#10b981'],
                    ['l'=>'Posted','v'=>$posted,'c'=>'#8b5cf6'],
                    ['l'=>'Rejected','v'=>$rejected,'c'=>'#ef4444'],
                ];
            @endphp
            @foreach($bl_items as $bl)
            <div class="bl-item">
                <span class="bl-dot" style="background:{{ $bl['c'] }};"></span>
                <span class="bl-name">{{ $bl['l'] }}</span>
                <span class="bl-num">{{ $bl['v'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── MINI STATS ────────────────────────────────────── --}}
    <div class="mini-row">
        <div class="mini-card">
            <div class="mini-card__icon" style="background:#eff6ff;">
                <svg width="22" height="22" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <div class="mini-card__label">Total Requestors</div>
                <div class="mini-card__value">{{ $users }}</div>
                <div class="mini-card__sub">Registered users</div>
            </div>
        </div>
        <div class="mini-card">
            <div class="mini-card__icon" style="background:#f0fdf4;">
                <svg width="22" height="22" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            </div>
            <div>
                <div class="mini-card__label">Approval Rate</div>
                <div class="mini-card__value" style="color:#047857;">{{ $approval_rate }}%</div>
                <div class="mini-card__sub">Approved + Posted</div>
            </div>
        </div>
        <div class="mini-card">
            <div class="mini-card__icon" style="background:#fffbeb;">
                <svg width="22" height="22" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <div>
                <div class="mini-card__label">Need Attention</div>
                <div class="mini-card__value" style="color:#b45309;">{{ $attention }}</div>
                <div class="mini-card__sub">Pending + Under Review</div>
            </div>
        </div>
    </div>

    {{-- ── CHART ROW ─────────────────────────────────────── --}}
    <div class="chart-row">

        {{-- Line Chart --}}
        <div class="np">
            <div class="np__head">
                <div>
                    <div class="np__title">Requests Over Time</div>
                    <div class="np__sub">Total vs Approved — last 6 months</div>
                </div>
                <a href="{{ route('admin.requests') }}" class="np__link">View all →</a>
            </div>
            <div class="np__body">
                <div class="chart-wrap"><canvas id="trendChart"></canvas></div>
            </div>
        </div>

        {{-- Top Requestors --}}
        <div class="np">
            <div class="np__head">
                <div>
                    <div class="np__title">Top Requestors</div>
                    <div class="np__sub">Most active this period</div>
                </div>
            </div>
            <div class="trop-list">
                @forelse($top_requestors as $i => $tr)
                @php
                    $medals = ['🥇','🥈','🥉'];
                    $uobj   = $top_users[$tr->requester] ?? null;
                    $photo  = $uobj?->profile_photo;
                    $dept   = $uobj?->department ?? ($uobj?->organization ?? 'Requestor');
                    $pct    = round($tr->total_requests / $tr_max * 100);
                    $col    = $av_colors_solid[$i % 5];
                @endphp
                <div class="trop-item">
                    <div class="trop-rank">{{ $medals[$i] ?? ($i+1) }}</div>
                    <div class="trop-av" style="background:{{ $col }};">
                        @if($photo)<img src="/uploads/{{ $photo }}" alt="">@endif
                        {{ strtoupper(substr($tr->requester,0,1)) }}
                    </div>
                    <div class="trop-info">
                        <div class="trop-name">{{ Str::limit($tr->requester,18) }}</div>
                        <div class="trop-dept">{{ Str::limit($dept,20) }}</div>
                    </div>
                    <div class="trop-bar-wrap">
                        <div class="trop-bar-bg">
                            <div class="trop-bar-fill" style="width:{{ $pct }}%;background:{{ $col }};"></div>
                        </div>
                    </div>
                    <div class="trop-count">{{ $tr->total_requests }}</div>
                </div>
                @empty
                    <div style="padding:24px;text-align:center;color:var(--ink-soft);font-size:13px;">No data yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── RECENT REQUESTS + CATEGORIES ─────────────────── --}}
    <div style="grid-column:1/3;grid-row:5;display:grid;grid-template-columns:1fr 340px;gap:16px;">

        {{-- Recent Requests (habits style) --}}
        <div class="np">
            <div class="np__head">
                <div>
                    <div class="np__title">Recent Requests</div>
                    <div class="np__sub">Latest submissions</div>
                </div>
                <a href="{{ route('admin.requests') }}" class="np__link">View all →</a>
            </div>
            <div class="req-habit-list">
            @forelse($recent->take(7) as $req)
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
                    $colors_pool = ['#2563eb','#10b981','#8b5cf6','#f59e0b','#ef4444'];
                    $av_c = $colors_pool[crc32($req->requester) % 5];
                    $safe2 = max($total, 1);
                @endphp
                <div class="req-habit-item" onclick="window.location='{{ route('admin.requests.show',$req->id) }}'">
                    <div class="req-hab-av" style="background:{{ $av_c }};">
                        @if($thumb)
                            <img src="/uploads/{{ $thumb }}" alt="" onerror="this.style.display='none'">
                        @else
                            {{ strtoupper(substr($req->requester,0,1)) }}
                        @endif
                    </div>
                    <div class="req-hab-info">
                        <div class="req-hab-title">{{ Str::limit($req->title,32) }}</div>
                        <div class="req-hab-by">{{ $req->requester }} · {{ $req->created_at->format('M j, Y') }}</div>
                    </div>
                    <div class="req-hab-right">
                        <div class="req-hab-bar-wrap">
                            <div class="req-hab-bar-label" style="color:{{ $sc[0] }};font-weight:700;font-size:10.5px;">{{ $sc[1] }}</div>
                            <div class="req-hab-bar-bg">
                                <div class="req-hab-bar-fill" style="width:100%;background:{{ $sc[0] }};"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div style="padding:32px;text-align:center;color:var(--ink-soft);font-size:13px;">No requests yet.</div>
            @endforelse
            </div>
        </div>

        {{-- Categories --}}
        <div class="np">
            <div class="np__head">
                <div>
                    <div class="np__title">Category Breakdown</div>
                    <div class="np__sub">Request distribution</div>
                </div>
            </div>
            @if($categories->isEmpty())
                <div style="padding:24px;text-align:center;color:var(--ink-soft);font-size:13px;">No data yet.</div>
            @else
            <div class="cat-grid">
                @foreach($categories as $i => $cat)
                @php $col = $cat_colors[$i % count($cat_colors)]; @endphp
                <div class="cat-pill">
                    <span class="cat-pill__dot" style="background:{{ $col }};"></span>
                    <span class="cat-pill__name">{{ trim($cat->category ?? 'Other') }}</span>
                    <span class="cat-pill__count">{{ $cat->count }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ── REPORTS ───────────────────────────────────────── --}}
    <div style="grid-column:1/3;grid-row:6;">
        <div style="display:flex;align-items:center;gap:14px;margin:8px 0 16px;">
            <div style="flex:1;height:1px;background:rgba(0,0,0,0.06);"></div>
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--ink-soft);">Reports & Downloads</div>
            <div style="flex:1;height:1px;background:rgba(0,0,0,0.06);"></div>
        </div>

        @php
            $rep_items = [
                ['name'=>'All Requests',     'desc'=>'Complete list of every request — all statuses, categories, users.',          'use'=>'Full data export for records or analysis',         'icon_bg'=>'#eff6ff','icon_c'=>'#2563eb','btn_c'=>'#2563eb','rows'=>$total,             'url'=>route('admin.reports.export')],
                ['name'=>'Approved & Posted','desc'=>'Only requests approved or published to social media platforms.',              'use'=>'Performance review & content calendar reference',   'icon_bg'=>'#f0fdf4','icon_c'=>'#10b981','btn_c'=>'#10b981','rows'=>$approved+$posted,  'url'=>route('admin.reports.export').'?status=approved'],
                ['name'=>'Pending Review',   'desc'=>'Requests waiting for admin action — needs to be reviewed or approved.',      'use'=>'Monitor backlog & follow up with requestors',       'icon_bg'=>'#fffbeb','icon_c'=>'#d97706','btn_c'=>'#f59e0b','rows'=>$pending+$review,   'url'=>route('admin.reports.export').'?status=pending'],
                ['name'=>'Rejected',         'desc'=>'Requests that were not approved — useful for quality analysis.',             'use'=>'Identify common rejection patterns',                'icon_bg'=>'#fef2f2','icon_c'=>'#ef4444','btn_c'=>'#ef4444','rows'=>$rejected,           'url'=>route('admin.reports.export').'?status=rejected'],
                ['name'=>'This Month',       'desc'=>'All requests submitted this month — '.now()->format('F Y').'.',              'use'=>'Monthly reporting & management updates',            'icon_bg'=>'#faf5ff','icon_c'=>'#7c3aed','btn_c'=>'#8b5cf6','rows'=>$this_month_count,  'url'=>route('admin.reports.export').'?date_from='.now()->startOfMonth()->format('Y-m-d').'&date_to='.now()->endOfMonth()->format('Y-m-d')],
                ['name'=>'High Priority',    'desc'=>'Urgent and High priority requests — time-sensitive items.',                  'use'=>'Escalation tracking & urgent handling',             'icon_bg'=>'#fff7ed','icon_c'=>'#f97316','btn_c'=>'#f97316','rows'=>$high_prio_count,    'url'=>route('admin.reports.export').'?priority=high'],
            ];
            $rep_icons = [
                '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
                '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
                '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
                '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
                '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
                '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
            ];
        @endphp

        <div class="reports-row">
            @foreach($rep_items as $ri => $rep)
            <div class="rep-row-item">
                <div class="rep-row-item__icon" style="background:{{ $rep['icon_bg'] }};">
                    <svg width="18" height="18" fill="none" stroke="{{ $rep['icon_c'] }}" stroke-width="2" viewBox="0 0 24 24">{!! $rep_icons[$ri] !!}</svg>
                </div>
                <div class="rep-row-item__name">{{ $rep['name'] }}</div>
                <div class="rep-row-item__desc">{{ $rep['desc'] }}</div>
                <div class="rep-row-item__use">Use for: {{ $rep['use'] }}</div>
                <span class="rep-row-item__rows" style="background:{{ $rep['icon_bg'] }};color:{{ $rep['icon_c'] }};">{{ $rep['rows'] }} rows</span>
                <a href="{{ $rep['url'] }}" class="rep-row-item__dl" style="background:{{ $rep['btn_c'] }};color:white;">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download
                </a>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── META FB SECTION ────────────────────────────────── --}}
    <div style="grid-column:1/3;grid-row:7;">
        <div style="display:flex;align-items:center;gap:14px;margin:8px 0 18px;">
            <div style="flex:1;height:1px;background:rgba(0,0,0,0.06);"></div>
            <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:var(--ink-soft);">Meta / Facebook Visualization</div>
            <div style="flex:1;height:1px;background:rgba(0,0,0,0.06);"></div>
        </div>

        @if(isset($fb) && empty($fb['error']) && !empty($fb['pageInfo']))
            {{-- Connected: Page Info Banner --}}
            <div style="display:flex;align-items:center;gap:16px;background:linear-gradient(135deg,#001a4d 0%,#002e7a 100%);border-radius:18px;padding:18px 22px;margin-bottom:14px;box-shadow:0 4px 18px rgba(0,26,77,0.22);">
                <div style="width:48px;height:48px;background:rgba(255,255,255,0.12);border-radius:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <svg width="24" height="24" fill="white" viewBox="0 0 24 24"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </div>
                <div style="flex:1;">
                    <div style="font-size:15px;font-weight:700;color:white;margin-bottom:2px;">
                        @if(!empty($fb['pageInfo']['link']))
                            <a href="{{ $fb['pageInfo']['link'] }}" target="_blank" style="color:white;text-decoration:none;display:inline-flex;align-items:center;gap:6px;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                {{ $fb['pageInfo']['name'] ?? 'Facebook Page' }}
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                            </a>
                        @else
                            {{ $fb['pageInfo']['name'] ?? 'Facebook Page' }}
                        @endif
                    </div>
                    <div style="font-size:12.5px;color:rgba(255,255,255,0.6);">
                        Likes: <strong style="color:rgba(255,255,255,0.9);">{{ number_format($fb['pageInfo']['fan_count'] ?? 0) }}</strong> &nbsp;·&nbsp;
                        Followers: <strong style="color:rgba(255,255,255,0.9);">{{ number_format($fb['pageInfo']['followers_count'] ?? 0) }}</strong>
                    </div>
                </div>
                <span style="font-size:10px;font-weight:700;padding:4px 14px;border-radius:20px;background:rgba(16,185,129,0.2);color:#6ee7b7;border:1px solid rgba(16,185,129,0.3);">✅ Connected</span>
            </div>

            {{-- Metric Cards (Matching Meta Professional Dashboard 28-day window) --}}
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:14px;">
                @php
                    $fb_dash_items = [
                        ['key'=>'total_reach',      'label'=>'Views (28d)',        'c'=>'#1877f2','bg'=>'#dbeafe','path'=>'<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'],
                        ['key'=>'total_engagement',  'label'=>'Engagement (28d)',   'c'=>'#e1306c','bg'=>'#fce7f3','path'=>'<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>'],
                        ['key'=>'total_likes',       'label'=>'Reactions (28d)',    'c'=>'#f59e0b','bg'=>'#fef3c7','path'=>'<circle cx="12" cy="12" r="10"/><path d="M8 13s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>'],
                        ['key'=>'total_shares',      'label'=>'Shares (28d)',       'c'=>'#10b981','bg'=>'#d1fae5','path'=>'<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>'],
                    ];
                @endphp
                @foreach($fb_dash_items as $fi)
                <div class="meta-mini">
                    <div class="meta-mini__icon-wrap" style="background:{{ $fi['bg'] }};">
                        <svg width="20" height="20" fill="none" stroke="{{ $fi['c'] }}" stroke-width="2" viewBox="0 0 24 24">{!! $fi['path'] !!}</svg>
                    </div>
                    <div class="meta-mini__label">{{ $fi['label'] }}</div>
                    <div style="font-size:26px;font-weight:800;color:var(--ink);letter-spacing:-1px;line-height:1;padding:12px 0;">
                        {{ number_format($fb['metrics'][$fi['key']]['total'] ?? 0) }}
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Performance Chart --}}
            <div class="meta-full">
                <div class="meta-full__head">
                    <div class="meta-full__title">Post Performance — Views & Engagement (Last 28 Days)</div>
                    <span class="meta-api-badge" style="background:#d1fae5;color:#047857;border-color:#6ee7b7;">✅ Live Data</span>
                </div>
                @if(!empty($fb['metrics']['total_reach']['daily']))
                <div style="position:relative;height:200px;">
                    <canvas id="fbInsightsChart"></canvas>
                </div>
                @else
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;">
                    @php
                        $extra_items = [
                            ['k'=>'total_comments','l'=>'Comments','c'=>'#10b981'],
                            ['k'=>'total_posts','l'=>'Posts Fetched','c'=>'#8b5cf6'],
                            ['k'=>'page_fans','l'=>'Page Fans','c'=>'#1877f2'],
                            ['k'=>'followers','l'=>'Followers','c'=>'#f59e0b'],
                        ];
                    @endphp
                    @foreach($extra_items as $ei)
                    <div style="background:var(--sand);border-radius:14px;padding:18px;text-align:center;">
                        <div style="font-size:11px;color:var(--ink-soft);font-weight:600;margin-bottom:8px;">{{ $ei['l'] }}</div>
                        <div style="font-size:24px;font-weight:800;color:{{ $ei['c'] }};">{{ number_format($fb['metrics'][$ei['k']]['total'] ?? 0) }}</div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        @elseif(isset($fb) && !empty($fb['error']))
            {{-- Error State --}}
            <div style="background:#fee2e2;border:1.5px solid #fca5a5;border-radius:14px;padding:16px 20px;font-size:13px;color:#b91c1c;margin-bottom:14px;display:flex;align-items:center;gap:10px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div>
                    <strong>Facebook API Error</strong> — {{ $fb['error'] }}<br>
                    <small style="opacity:.7;">Check your FB_PAGE_ACCESS_TOKEN in .env — tokens expire and may need to be refreshed from <a href="https://developers.facebook.com/tools/explorer/" target="_blank" style="color:#1877f2;">Graph API Explorer</a></small>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:14px;">
            @php
                $meta_items = [
                    ['label'=>'Total Reach',  'icon_c'=>'#1877f2','icon_bg'=>'#dbeafe','path'=>'<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'],
                    ['label'=>'Engagements', 'icon_c'=>'#e1306c','icon_bg'=>'#fce7f3','path'=>'<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>'],
                    ['label'=>'Reactions',   'icon_c'=>'#f59e0b','icon_bg'=>'#fef3c7','path'=>'<circle cx="12" cy="12" r="10"/><path d="M8 13s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>'],
                    ['label'=>'Shares',      'icon_c'=>'#10b981','icon_bg'=>'#d1fae5','path'=>'<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>'],
                ];
            @endphp
            @foreach($meta_items as $mi)
            <div class="meta-mini">
                <div class="meta-mini__icon-wrap" style="background:{{ $mi['icon_bg'] }};">
                    <svg width="20" height="20" fill="none" stroke="{{ $mi['icon_c'] }}" stroke-width="2" viewBox="0 0 24 24">{!! $mi['path'] !!}</svg>
                </div>
                <div class="meta-mini__label">{{ $mi['label'] }}</div>
                <div class="meta-mini__ph">
                    <svg width="22" height="22" fill="none" stroke="#fca5a5" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                    <p style="color:#fca5a5;">API Error</p>
                </div>
            </div>
            @endforeach
            </div>

        @else
            {{-- Not Configured Placeholder --}}
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:14px;">
            @php
                $meta_items = [
                    ['label'=>'Total Reach',  'icon_c'=>'#1877f2','icon_bg'=>'#dbeafe','path'=>'<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'],
                    ['label'=>'Engagements', 'icon_c'=>'#e1306c','icon_bg'=>'#fce7f3','path'=>'<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>'],
                    ['label'=>'Reactions',   'icon_c'=>'#f59e0b','icon_bg'=>'#fef3c7','path'=>'<circle cx="12" cy="12" r="10"/><path d="M8 13s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>'],
                    ['label'=>'Shares',      'icon_c'=>'#10b981','icon_bg'=>'#d1fae5','path'=>'<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>'],
                ];
            @endphp
            @foreach($meta_items as $mi)
            <div class="meta-mini">
                <div class="meta-mini__icon-wrap" style="background:{{ $mi['icon_bg'] }};">
                    <svg width="20" height="20" fill="none" stroke="{{ $mi['icon_c'] }}" stroke-width="2" viewBox="0 0 24 24">{!! $mi['path'] !!}</svg>
                </div>
                <div class="meta-mini__label">{{ $mi['label'] }}</div>
                <div class="meta-mini__ph">
                    <svg width="22" height="22" fill="none" stroke="#93c5fd" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                    <p>API Pending</p>
                </div>
            </div>
            @endforeach
            </div>

            <div class="meta-full">
                <div class="meta-full__head">
                    <div class="meta-full__title">Post Performance — Reach & Engagement</div>
                    <span class="meta-api-badge">API Not Connected</span>
                </div>
                <div class="meta-chart-ph">
                    <svg width="36" height="36" fill="none" stroke="#93c5fd" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    <div class="meta-chart-ph__title">Facebook Graph API Not Connected</div>
                    <div class="meta-chart-ph__sub">Add <code style="background:#e0eaff;padding:2px 6px;border-radius:4px;font-size:10px;">FB_PAGE_ACCESS_TOKEN</code> and <code style="background:#e0eaff;padding:2px 6px;border-radius:4px;font-size:10px;">FB_PAGE_ID</code> to your <strong>.env</strong> to enable real-time analytics</div>
                </div>
            </div>
        @endif
    </div>

</div><!-- .db-wrap -->

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const months  = @json(array_column($months_data,'label'));
const totals  = @json(array_column($months_data,'total'));
const approvd = @json(array_column($months_data,'approved'));
const ctx = document.getElementById('trendChart').getContext('2d');
const gT = ctx.createLinearGradient(0,0,0,200); gT.addColorStop(0,'rgba(37,99,235,0.12)'); gT.addColorStop(1,'rgba(37,99,235,0)');
const gA = ctx.createLinearGradient(0,0,0,200); gA.addColorStop(0,'rgba(16,185,129,0.12)'); gA.addColorStop(1,'rgba(16,185,129,0)');
new Chart(ctx,{
    type:'line',
    data:{
        labels:months,
        datasets:[
            {label:'Total',data:totals,borderColor:'#2563eb',backgroundColor:gT,borderWidth:2.5,pointBackgroundColor:'#2563eb',pointRadius:3,tension:0.4,fill:true},
            {label:'Approved',data:approvd,borderColor:'#10b981',backgroundColor:gA,borderWidth:2.5,pointBackgroundColor:'#10b981',pointRadius:3,tension:0.4,fill:true}
        ]
    },
    options:{
        responsive:true,maintainAspectRatio:false,
        interaction:{mode:'index',intersect:false},
        plugins:{
            legend:{position:'top',align:'end',labels:{font:{family:'DM Sans',size:12,weight:'600'},color:'#8a8a8a',usePointStyle:true,pointStyleWidth:8,boxHeight:8,padding:18}},
            tooltip:{backgroundColor:'#1a1a1a',titleFont:{family:'DM Sans',size:12,weight:'700'},bodyFont:{family:'DM Sans',size:12},padding:12,cornerRadius:10}
        },
        scales:{
            x:{grid:{display:false},ticks:{font:{family:'DM Sans',size:12},color:'#8a8a8a'}},
            y:{beginAtZero:true,grid:{color:'#ede8e0'},ticks:{font:{family:'DM Sans',size:12},color:'#8a8a8a',stepSize:1,callback:v=>Number.isInteger(v)?v:''}}
        }
    }
});
</script>
@if(isset($fb) && empty($fb['error']) && !empty($fb['metrics']['total_reach']['daily']))
<script>
const fbCtx = document.getElementById('fbInsightsChart');
if (fbCtx) {
    const fbLabels = @json(array_map(function($d) { return \Carbon\Carbon::parse($d['date'])->format('M j'); }, $fb['metrics']['total_reach']['daily']));
    const fbReach  = @json(array_column($fb['metrics']['total_reach']['daily'], 'value'));
    const fbEng    = @json(array_column($fb['metrics']['total_engagement']['daily'], 'value'));
    const ctx2 = fbCtx.getContext('2d');
    const gR = ctx2.createLinearGradient(0,0,0,200); gR.addColorStop(0,'rgba(24,119,242,0.15)'); gR.addColorStop(1,'rgba(24,119,242,0)');
    const gE = ctx2.createLinearGradient(0,0,0,200); gE.addColorStop(0,'rgba(225,48,108,0.15)'); gE.addColorStop(1,'rgba(225,48,108,0)');
    new Chart(ctx2, {
        type:'line',
        data:{
            labels: fbLabels,
            datasets:[
                {label:'Views',data:fbReach,borderColor:'#1877f2',backgroundColor:gR,borderWidth:2.5,pointBackgroundColor:'#1877f2',pointRadius:3,tension:0.4,fill:true},
                {label:'Engagement',data:fbEng,borderColor:'#e1306c',backgroundColor:gE,borderWidth:2.5,pointBackgroundColor:'#e1306c',pointRadius:3,tension:0.4,fill:true}
            ]
        },
        options:{
            responsive:true,maintainAspectRatio:false,
            interaction:{mode:'index',intersect:false},
            plugins:{
                legend:{position:'top',align:'end',labels:{font:{family:'DM Sans',size:12,weight:'600'},color:'#8a8a8a',usePointStyle:true,pointStyleWidth:8,boxHeight:8,padding:18}},
                tooltip:{backgroundColor:'#1a1a1a',titleFont:{family:'DM Sans',size:12,weight:'700'},bodyFont:{family:'DM Sans',size:12},padding:12,cornerRadius:10}
            },
            scales:{
                x:{grid:{display:false},ticks:{font:{family:'DM Sans',size:12},color:'#8a8a8a'}},
                y:{beginAtZero:true,grid:{color:'#ede8e0'},ticks:{font:{family:'DM Sans',size:12},color:'#8a8a8a'}}
            }
        }
    });
}
</script>
@endif
@endsection