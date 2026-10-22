@extends('layouts.admin')

@section('title', 'Performance Analytics')

@section('head-styles')
<style>
.page-header { margin-bottom: 24px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
.page-header h1 { font-family: var(--font-disp, 'Outfit', sans-serif); font-size: 22px; font-weight: 800; color: var(--ink, #1a1a1a); letter-spacing: -0.5px; }
.page-header p  { font-size: 13px; color: var(--ink-soft, #7a7672); margin-top: 3px; }

.header-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.btn-export {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 18px; color: white;
    border: none; border-radius: 12px; font-size: 12.5px; font-weight: 600;
    cursor: pointer; font-family: var(--font, 'DM Sans', sans-serif); text-decoration: none;
    transition: all .15s; white-space: nowrap; flex-shrink: 0;
}
.btn-export:hover { opacity: 0.92; transform: translateY(-1px); }

.btn-refresh-fb {
    display: flex; align-items: center; gap: 7px;
    font-size: 12.5px; font-weight: 600;
    color: var(--navy, #002366); background: var(--navy-pale, #eff6ff);
    border: 1.5px solid rgba(0,35,102,0.18); padding: 9px 16px;
    border-radius: 12px; text-decoration: none; transition: all .15s;
    white-space: nowrap; flex-shrink: 0;
}
.btn-refresh-fb:hover { background: #dbeafe; transform: translateY(-1px); }

/* PANELS & CHARTS */
.panel-a { background: var(--card, #fff); border: 1.5px solid var(--card-border, #e5e7eb); border-radius: var(--radius, 16px); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 20px; }
.pa-head { padding: 18px 22px 14px; display: flex; align-items: flex-start; justify-content: space-between; border-bottom: 1.5px solid var(--card-border, #f0f2f8); }
.pa-title { font-family: var(--font-disp, 'Outfit', sans-serif); font-size: 16.5px; font-weight: 700; color: var(--ink, #1a1a1a); }
.pa-sub   { font-size: 12px; color: var(--ink-soft, #7a7672); margin-top: 2px; }
.pa-body  { padding: 22px; }
.chart-wrap { position: relative; height: 250px; }

/* ═══════════════════════════════════════════════════
   FACEBOOK SECTION
═══════════════════════════════════════════════════ */
.fb-divider { display: flex; align-items: center; gap: 14px; margin: 28px 0 20px; }
.fb-divider__line  { flex: 1; height: 1.5px; background: var(--card-border, #e5e7eb); }
.fb-divider__label { font-size: 11.5px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: var(--ink-faint, #9ca3af); white-space: nowrap; }

/* Page banner */
.fb-banner {
    display: flex; align-items: center; gap: 16px;
    background: var(--navy-pale, #eff6ff); border: 1.5px solid rgba(0,35,102,0.15);
    border-radius: var(--radius, 16px); padding: 18px 22px; margin-bottom: 18px;
}
.fb-banner__icon { width: 46px; height: 46px; background: var(--navy, #002366); border-radius: 13px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.fb-banner__title { font-size: 15px; font-weight: 700; color: var(--navy, #002366); margin-bottom: 3px; }
.fb-banner__sub   { font-size: 12.5px; color: var(--navy-light, #1e40af); }
.fb-badge { font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 20px; background: var(--navy, #002366); color: white; margin-top: 6px; display: inline-block; }

/* Month selector + CSV row */
.fb-controls-row {
    display: flex; align-items: center; justify-content: space-between;
    gap: 14px; flex-wrap: wrap; margin-bottom: 18px;
}
.fb-month-label {
    font-size: 11px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.8px; color: var(--ink-faint, #9ca3af); white-space: nowrap;
}
.fb-month-select {
    appearance: none; -webkit-appearance: none;
    background: var(--card, #fff) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 12px center;
    border: 1.5px solid var(--card-border, #e5e7eb);
    border-radius: 10px; padding: 9px 36px 9px 14px;
    font-size: 13px; font-weight: 600; color: var(--ink, #1a1a1a);
    cursor: pointer; transition: border-color .18s, box-shadow .18s;
    min-width: 160px;
}
.fb-month-select:focus {
    outline: none; border-color: var(--navy, #002366);
    box-shadow: 0 0 0 3px rgba(0,35,102,0.1);
}
.fb-month-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 9px 16px; border-radius: 10px; font-size: 12.5px;
    font-weight: 600; cursor: pointer; border: none; transition: all .18s;
}
.fb-month-btn--go {
    background: var(--navy, #002366); color: white;
    box-shadow: 0 2px 8px rgba(0,35,102,0.25);
}
.fb-month-btn--go:hover { background: #001a4d; }
.fb-month-btn--csv {
    background: var(--cream-dark, #f1f5f9); color: var(--ink, #1a1a1a);
    border: 1.5px solid var(--card-border, #e2e8f0);
    text-decoration: none;
}
.fb-month-btn--csv:hover { background: #e2e8f0; }

/* Metric cards grid — 3 cols, 2 rows */
.fb-metric-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px; margin-bottom: 20px;
}
@media (max-width: 900px) {
    .fb-metric-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
    .fb-metric-grid { grid-template-columns: 1fr; }
}

.fb-mcard {
    background: var(--card, #fff); border: 1.5px solid var(--card-border, #e5e7eb);
    border-radius: var(--radius, 16px); padding: 18px;
    display: flex; flex-direction: column; gap: 8px;
    box-shadow: var(--shadow-sm); transition: transform .18s, box-shadow .18s;
    position: relative; overflow: hidden;
}
.fb-mcard:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.08); }
.fb-mcard__icon-wrap {
    width: 40px; height: 40px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
}
.fb-mcard__label { font-size: 11.5px; font-weight: 600; color: var(--ink-soft, #7a7672); }
.fb-mcard__value {
    font-size: 28px; font-weight: 800; letter-spacing: -1px;
    line-height: 1; color: var(--ink, #1a1a1a);
}
.fb-mcard::after {
    content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 3px;
}

/* Enhanced chart panel */
.fb-chart-panel {
    background: var(--card, #fff); border: 1.5px solid var(--card-border, #e5e7eb);
    border-radius: var(--radius, 16px); box-shadow: var(--shadow-sm);
    overflow: hidden; margin-bottom: 20px;
}
.fb-chart-head {
    padding: 18px 22px 0;
    display: flex; align-items: flex-start; justify-content: space-between;
}
.fb-chart-legend {
    display: flex; align-items: center; gap: 16px;
    padding: 14px 22px; flex-wrap: wrap;
}
.fb-legend-dot {
    width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0;
}
.fb-chart-body { padding: 0 16px 20px; }
.fb-chart-wrap { position: relative; height: 270px; }

/* Toggle buttons for chart datasets */
.fb-chart-toggles {
    display: flex; gap: 0; background: var(--cream-dark, #f1f5f9); border-radius: 8px;
    padding: 3px; border: 1px solid rgba(0,0,0,0.06);
}
.fb-ctog {
    padding: 5px 14px; border-radius: 6px; font-size: 11.5px; font-weight: 600;
    color: var(--ink-soft, #7a7672); cursor: pointer; transition: all .15s; border: none; background: transparent;
}
.fb-ctog.active { background: white; color: var(--ink, #1a1a1a); box-shadow: 0 1px 4px rgba(0,0,0,0.1); }

/* Posts table */
.rtable { width: 100%; border-collapse: collapse; }
.rtable th { padding: 10px 16px; text-align: left; font-size: 10px; font-weight: 700; color: var(--ink-soft, #7a7672); text-transform: uppercase; letter-spacing: 0.7px; border-bottom: 1.5px solid var(--card-border, #f0f2f8); background: var(--cream-dark, #f8fafc); }
.rtable td { padding: 12px 16px; font-size: 12.5px; border-bottom: 1px solid var(--card-border, #f0f2f8); vertical-align: middle; }
.rtable tbody tr:last-child td { border-bottom: none; }
.rtable tbody tr:hover { background: #f9faff; }

.fb-post-thumb {
    width: 44px; height: 44px; border-radius: 10px; object-fit: cover; flex-shrink: 0;
}
.fb-post-no-thumb {
    width: 44px; height: 44px; border-radius: 10px; background: var(--cream-dark, #f1f5f9);
    display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;
}
.fb-metric-pill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700;
}

.fb-empty {
    text-align: center; padding: 48px 20px;
    display: flex; flex-direction: column; align-items: center; gap: 10px;
}
.fb-empty__icon {
    width: 56px; height: 56px; border-radius: 16px; background: var(--cream-dark, #f1f5f9);
    display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 4px;
}
.fb-empty__title { font-size: 14px; font-weight: 700; color: var(--ink, #1a1a1a); }
.fb-empty__sub   { font-size: 12.5px; color: var(--ink-faint, #9ca3af); max-width: 320px; line-height: 1.5; }

/* Placeholder cards when not configured */
.fb-ph-card {
    background: var(--card, #fff); border: 1.5px solid var(--card-border, #e5e7eb);
    border-radius: var(--radius, 16px); padding: 18px;
    display: flex; flex-direction: column; gap: 10px; box-shadow: var(--shadow-sm);
}
.fb-ph-block {
    flex: 1; background: var(--cream-dark, #f8fafc); border-radius: 10px;
    border: 1.5px dashed #93c5fd; min-height: 80px;
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 5px;
}
.fb-ph-block p { font-size: 10.5px; color: #93c5fd; font-weight: 500; }

/* ═══════════════════════════════════════════════════
   REPORTS & DOWNLOADS
═══════════════════════════════════════════════════ */
.reports-row {
    display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px;
}
.rep-row-item {
    background: var(--card, #fff);
    border: 1.5px solid var(--card-border, #e5e7eb);
    border-radius: 14px; padding: 14px 18px;
    display: flex; align-items: center; gap: 14px;
    transition: transform .15s, box-shadow .15s;
    text-decoration: none;
}
.rep-row-item:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.07); }
.rep-row-item__icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rep-row-item__name { font-size: 13.5px; font-weight: 700; color: var(--ink, #1a1a1a); width: 160px; flex-shrink: 0; }
.rep-row-item__desc { font-size: 12px; color: var(--ink-soft, #7a7672); flex: 1; line-height: 1.5; }
.rep-row-item__use  { font-size: 11px; color: var(--ink-faint, #9ca3af); width: 260px; flex-shrink: 0; text-align: right; }
.rep-row-item__rows { font-size: 11.5px; font-weight: 700; padding: 4px 12px; border-radius: 20px; white-space: nowrap; flex-shrink: 0; margin-left: 12px; }
.rep-row-item__dl {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: 10px; border: none;
    font-size: 12px; font-weight: 700; cursor: pointer;
    font-family: var(--font, sans-serif); transition: opacity .15s, transform .15s; text-decoration: none;
    white-space: nowrap; flex-shrink: 0; margin-left: 10px;
}
.rep-row-item__dl:hover { opacity: 0.88; transform: translateY(-1px); }

@media (max-width: 900px) {
    .rep-row-item { flex-wrap: wrap; }
    .rep-row-item__name { width: auto; flex: 1; }
    .rep-row-item__desc { width: 100%; order: 3; }
    .rep-row-item__use  { display: none; }
}

/* ═══════════════════════════════════════════════════
   REQUEST FILTER & RECORDS
═══════════════════════════════════════════════════ */
.filter-panel {
    background: var(--card, #fff); border-radius: var(--radius, 16px); border: 1.5px solid var(--card-border, #e5e7eb);
    box-shadow: var(--shadow-sm); padding: 20px 22px; margin-bottom: 20px;
}
.filter-panel__title { font-size: 14px; font-weight: 700; color: var(--ink, #1a1a1a); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.filter-row { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.filter-row select, .filter-row input {
    height: 40px; background: var(--bg, #f8fafc); border: 1.5px solid var(--card-border, #e5e7eb);
    border-radius: 10px; padding: 0 14px; font-size: 13px;
    font-family: var(--font, sans-serif); color: var(--ink, #1a1a1a); outline: none;
    transition: border-color .15s;
}
.filter-row select:focus, .filter-row input:focus { border-color: var(--primary-light, #2563eb); }
.btn-filter {
    height: 40px; padding: 0 18px; background: var(--navy, #002366); color: white;
    border: none; border-radius: 10px; font-size: 13px; font-weight: 600;
    cursor: pointer; font-family: var(--font, sans-serif); transition: background .15s;
}
.btn-filter:hover { background: #001a4d; }
.btn-reset-filter {
    height: 40px; padding: 0 16px; background: var(--card, #fff); color: var(--ink-soft, #7a7672);
    border: 1.5px solid var(--card-border, #e5e7eb); border-radius: 10px; font-size: 13px;
    cursor: pointer; font-family: var(--font, sans-serif); text-decoration: none;
    display: flex; align-items: center;
}

/* SUMMARY CARDS */
.summary-row { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; margin-bottom: 20px; }
@media (max-width: 800px) {
    .summary-row { grid-template-columns: repeat(2, 1fr); }
}
.sum-card {
    background: var(--card, #fff); border-radius: var(--radius, 16px); border: 1.5px solid var(--card-border, #e5e7eb);
    box-shadow: var(--shadow-sm); padding: 18px; text-align: center;
}
.sum-card__num { font-size: 28px; font-weight: 800; letter-spacing: -1px; margin-bottom: 4px; }
.sum-card__lbl { font-size: 12px; color: var(--ink-soft, #7a7672); font-weight: 500; }

/* TABLE PANEL */
.table-panel { background: var(--card, #fff); border-radius: var(--radius, 16px); border: 1.5px solid var(--card-border, #e5e7eb); box-shadow: var(--shadow-sm); overflow: hidden; margin-bottom: 20px; }
.table-panel__head { padding: 16px 22px; border-bottom: 1.5px solid var(--card-border, #f0f2f8); display: flex; align-items: center; justify-content: space-between; }
.table-panel__title { font-size: 14px; font-weight: 700; color: var(--ink, #1a1a1a); }
.table-panel__meta  { font-size: 12.5px; color: var(--ink-soft, #7a7672); }

table.main-table { width: 100%; border-collapse: collapse; }
table.main-table thead tr { border-bottom: 1.5px solid var(--card-border, #f0f2f8); background: var(--cream-dark, #f8fafc); }
table.main-table th { padding: 10px 16px; text-align: left; font-size: 10px; font-weight: 700; color: var(--ink-soft, #7a7672); text-transform: uppercase; letter-spacing: 0.7px; white-space: nowrap; }
table.main-table tbody tr { border-bottom: 1px solid var(--card-border, #f4f6fb); transition: background .1s; }
table.main-table tbody tr:last-child { border-bottom: none; }
table.main-table tbody tr:hover { background: #f9faff; }
table.main-table td { padding: 12px 16px; font-size: 12.5px; vertical-align: middle; color: var(--ink, #1a1a1a); }

.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 10.5px; font-weight: 600; white-space: nowrap; }
.badge--pending      { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
.badge--under-review { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
.badge--approved     { background: #dcfce7; color: #16a34a; }
.badge--posted       { background: #ede9fe; color: #7c3aed; }
.badge--rejected     { background: #fee2e2; color: #dc2626; }
.badge--high         { background: #fee2e2; color: #dc2626; }
.badge--urgent       { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
.badge--medium       { background: #fef3c7; color: #d97706; }
.badge--low          { background: #f1f5f9; color: #64748b; }

.empty-state { padding: 48px; text-align: center; color: var(--ink-soft, #9ca3af); font-size: 13px; }

/* ═══════════════════════════════════════════════════
   DARK MODE COMPATIBILITY
═══════════════════════════════════════════════════ */
[data-theme="dark"] .panel-a,
[data-theme="dark"] .filter-panel,
[data-theme="dark"] .sum-card,
[data-theme="dark"] .table-panel,
[data-theme="dark"] .fb-mcard,
[data-theme="dark"] .fb-chart-panel,
[data-theme="dark"] .fb-ph-card,
[data-theme="dark"] .rep-row-item {
    background: var(--card) !important;
    border-color: var(--card-border) !important;
    color: var(--ink) !important;
}
[data-theme="dark"] .pa-head,
[data-theme="dark"] .fb-chart-head,
[data-theme="dark"] .table-panel__head {
    border-bottom-color: var(--card-border) !important;
}
[data-theme="dark"] .pa-title,
[data-theme="dark"] .fb-banner__title,
[data-theme="dark"] .fb-mcard__value,
[data-theme="dark"] .filter-panel__title,
[data-theme="dark"] .table-panel__title,
[data-theme="dark"] .rep-row-item__name,
[data-theme="dark"] .page-header h1 {
    color: var(--ink) !important;
}
[data-theme="dark"] .pa-sub,
[data-theme="dark"] .fb-banner__sub,
[data-theme="dark"] .fb-mcard__label,
[data-theme="dark"] .table-panel__meta,
[data-theme="dark"] .rep-row-item__desc,
[data-theme="dark"] .page-header p {
    color: var(--ink-soft) !important;
}
[data-theme="dark"] .rep-row-item__use {
    color: var(--ink-faint) !important;
}
[data-theme="dark"] .fb-banner {
    background: rgba(0, 35, 102, 0.25) !important;
    border-color: rgba(30, 79, 216, 0.3) !important;
}
[data-theme="dark"] .btn-refresh-fb {
    background: rgba(0, 35, 102, 0.35) !important;
    border-color: rgba(30, 79, 216, 0.4) !important;
    color: #93c5fd !important;
}
[data-theme="dark"] .fb-month-select {
    background-color: #0b1a3d !important;
    color: #f8fafc !important;
    border-color: rgba(30, 79, 216, 0.3) !important;
}
[data-theme="dark"] .fb-month-btn--csv {
    background: #0b1a3d !important;
    color: var(--ink) !important;
    border-color: var(--card-border) !important;
}
[data-theme="dark"] .fb-chart-toggles {
    background: #06112a !important;
    border-color: var(--card-border) !important;
}
[data-theme="dark"] .fb-ctog.active {
    background: #0b1a3d !important;
    color: var(--ink) !important;
}
[data-theme="dark"] .rtable th,
[data-theme="dark"] table.main-table thead tr {
    background: #06112a !important;
    border-bottom-color: var(--card-border) !important;
    color: var(--ink-soft) !important;
}
[data-theme="dark"] .rtable td,
[data-theme="dark"] table.main-table td {
    border-bottom-color: var(--card-border) !important;
    color: var(--ink) !important;
}
[data-theme="dark"] .rtable tbody tr:hover,
[data-theme="dark"] table.main-table tbody tr:hover {
    background: rgba(30, 79, 216, 0.1) !important;
}
[data-theme="dark"] .filter-row select,
[data-theme="dark"] .filter-row input {
    background-color: #0b1a3d !important;
    color: #f8fafc !important;
    border-color: rgba(30, 79, 216, 0.3) !important;
}
[data-theme="dark"] .btn-reset-filter {
    background: #0b1a3d !important;
    color: var(--ink-soft) !important;
    border-color: var(--card-border) !important;
}
[data-theme="dark"] .fb-post-no-thumb,
[data-theme="dark"] .fb-empty__icon,
[data-theme="dark"] .fb-ph-block {
    background: #0b1a3d !important;
}
</style>
@endsection

@section('content')

@php
    $fb_configured = !empty(env('FB_PAGE_ID')) && !empty(env('FB_PAGE_ACCESS_TOKEN'));
    $selectedMonth = $fb['selected_month'] ?? now()->format('Y-m');
    $monthLabel    = $fb['month_label']    ?? now()->format('F Y');
    $availableMonths = $fb['available_months'] ?? [];

    $filter_status   = request('status', 'all');
    $filter_category = request('category', 'all');
    $filter_priority = request('priority', 'all');
    $date_from       = request('date_from', '');
    $date_to         = request('date_to', '');

    $query = \App\Models\PostRequest::query();

    if ($filter_status !== 'all') {
        $status_map = [
            'pending'  => 'Pending Review', 'review' => 'Under Review',
            'approved' => 'Approved',       'posted' => 'Posted', 'rejected' => 'Rejected',
        ];
        if (isset($status_map[$filter_status])) $query->where('status', $status_map[$filter_status]);
    }
    if ($filter_category !== 'all') $query->where('category', $filter_category);
    if ($filter_priority !== 'all') $query->where('priority', ucfirst($filter_priority));
    if ($date_from) $query->whereDate('created_at', '>=', $date_from);
    if ($date_to)   $query->whereDate('created_at', '<=', $date_to);

    $requests = $query->orderByDesc('created_at')->get();
    $total    = $requests->count();

    $r_pending  = $requests->where('status','Pending Review')->count();
    $r_approved = $requests->whereIn('status',['Approved','Posted'])->count();
    $r_rejected = $requests->where('status','Rejected')->count();

    $categories = \App\Models\PostRequest::selectRaw('category')->distinct()->orderBy('category')->pluck('category');

    /* ── Preset Reports & Downloads Data ── */
    $rep_total_all       = \App\Models\PostRequest::count();
    $rep_approved_posted = \App\Models\PostRequest::whereIn('status', ['Approved', 'Posted'])->count();
    $rep_pending_review  = \App\Models\PostRequest::whereIn('status', ['Pending', 'Pending Review', 'Under Review'])->count();
    $rep_rejected_count  = \App\Models\PostRequest::where('status', 'Rejected')->count();
    $rep_this_month      = \App\Models\PostRequest::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count();
    $rep_high_prio       = \App\Models\PostRequest::whereIn('priority', ['High', 'Urgent'])->count();

    $rep_items = [
        ['name'=>'All Requests',     'desc'=>'Complete list of every request — all statuses, categories, users.',          'use'=>'Full data export for records or analysis',         'icon_bg'=>'#eff6ff','icon_c'=>'#2563eb','btn_c'=>'#2563eb','rows'=>$rep_total_all,       'url'=>route('admin.reports.export')],
        ['name'=>'Approved & Posted','desc'=>'Only requests approved or published to social media platforms.',              'use'=>'Performance review & content calendar reference',   'icon_bg'=>'#f0fdf4','icon_c'=>'#10b981','btn_c'=>'#10b981','rows'=>$rep_approved_posted,  'url'=>route('admin.reports.export').'?status=approved'],
        ['name'=>'Pending Review',   'desc'=>'Requests waiting for admin action — needs to be reviewed or approved.',      'use'=>'Monitor backlog & follow up with requestors',       'icon_bg'=>'#fffbeb','icon_c'=>'#d97706','btn_c'=>'#f59e0b','rows'=>$rep_pending_review,   'url'=>route('admin.reports.export').'?status=pending'],
        ['name'=>'Rejected',         'desc'=>'Requests that were not approved — useful for quality analysis.',             'use'=>'Identify common rejection patterns',                'icon_bg'=>'#fef2f2','icon_c'=>'#ef4444','btn_c'=>'#ef4444','rows'=>$rep_rejected_count,   'url'=>route('admin.reports.export').'?status=rejected'],
        ['name'=>'This Month',       'desc'=>'All requests submitted this month — '.now()->format('F Y').'.',              'use'=>'Monthly reporting & management updates',            'icon_bg'=>'#faf5ff','icon_c'=>'#7c3aed','btn_c'=>'#8b5cf6','rows'=>$rep_this_month,       'url'=>route('admin.reports.export').'?date_from='.now()->startOfMonth()->format('Y-m-d').'&date_to='.now()->endOfMonth()->format('Y-m-d')],
        ['name'=>'High Priority',    'desc'=>'Urgent and High priority requests — time-sensitive items.',                  'use'=>'Escalation tracking & urgent handling',             'icon_bg'=>'#fff7ed','icon_c'=>'#f97316','btn_c'=>'#f97316','rows'=>$rep_high_prio,        'url'=>route('admin.reports.export').'?priority=high'],
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

{{-- PAGE HEADER --}}
<div class="page-header">
    <div>
        <h1>Performance Analytics</h1>
        <p>Request volume trends, Facebook & Meta analytics, and custom export reports</p>
    </div>
    <div class="header-actions">
        @if($fb_configured)
        <a href="{{ route('admin.reports.refresh', ['month' => $selectedMonth]) }}" class="btn-refresh-fb">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
            Refresh FB Data
        </a>
        @endif
        <a href="{{ route('admin.reports.export') }}?type=details&status={{ $filter_status }}&category={{ $filter_category }}&priority={{ $filter_priority }}&date_from={{ $date_from }}&date_to={{ $date_to }}"
           class="btn-export" style="background:#002366;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export Details & History
        </a>
        <a href="{{ route('admin.reports.export') }}?type=performance&status={{ $filter_status }}&category={{ $filter_category }}&priority={{ $filter_priority }}&date_from={{ $date_from }}&date_to={{ $date_to }}"
           class="btn-export" style="background:#b89600;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            Export Performance
        </a>
    </div>
</div>

@if(session('success'))
    <div style="background:#dcfce7;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:500;color:#15803d;margin-bottom:18px;">{{ session('success') }}</div>
@endif

{{-- ═══════════════════════════════════════════════
     REQUEST VOLUME TREND
════════════════════════════════════════════════ --}}
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
        <div style="background:#fee2e2;border:1.5px solid #fca5a5;border-radius:14px;padding:16px 20px;font-size:13px;color:#b91c1c;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div>
                <strong>Facebook API Error</strong> — {{ $fb['error'] }}<br>
                <small style="opacity:.7;">Check your FB_PAGE_ACCESS_TOKEN in .env</small>
            </div>
        </div>
    @else

        {{-- PAGE BANNER --}}
        <div class="fb-banner">
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
            <form method="GET" action="{{ route('admin.reports') }}" style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
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

            <a href="{{ route('admin.reports.fb-export', ['month' => $selectedMonth]) }}"
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
            ],
            [
                'key'   => 'total_engagement',
                'label' => 'Engagements',
                'icon'  => '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>',
                'color' => '#e1306c',
                'bg'    => '#fce7f3',
            ],
            [
                'key'   => 'total_likes',
                'label' => 'Reactions',
                'icon'  => '<circle cx="12" cy="12" r="10"/><path d="M8 13s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>',
                'color' => '#f59e0b',
                'bg'    => '#fef3c7',
            ],
            [
                'key'   => 'total_comments',
                'label' => 'Comments',
                'icon'  => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
                'color' => '#10b981',
                'bg'    => '#d1fae5',
            ],
            [
                'key'   => 'total_shares',
                'label' => 'Shares',
                'icon'  => '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>',
                'color' => '#8b5cf6',
                'bg'    => '#ede9fe',
            ],
            [
                'key'   => 'total_posts',
                'label' => 'Posts Published',
                'icon'  => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>',
                'color' => '#0ea5e9',
                'bg'    => '#e0f2fe',
            ],
        ];
        @endphp

        <div class="fb-metric-grid">
            @foreach($fbMetricDefs as $fm)
            @php $val = $fb['metrics'][$fm['key']]['total'] ?? 0; @endphp
            <div class="fb-mcard">
                <style>.fb-mcard:nth-child({{ $loop->iteration }})::after { background:{{ $fm['color'] }}; }</style>
                <div class="fb-mcard__icon-wrap" style="background:{{ $fm['bg'] }};">
                    <svg width="20" height="20" fill="none" stroke="{{ $fm['color'] }}" stroke-width="2" viewBox="0 0 24 24">{!! $fm['icon'] !!}</svg>
                </div>
                <div class="fb-mcard__label">{{ $fm['label'] }} · {{ $monthLabel }}</div>
                <div class="fb-mcard__value">{{ number_format($val) }}</div>
            </div>
            @endforeach
        </div>

        {{-- ENHANCED FB DAILY CHART --}}
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
                <div style="display:flex;align-items:center;gap:7px;font-size:12.5px;font-weight:600;color:var(--ink-soft,#7a7672);">
                    <span class="fb-legend-dot" style="background:#1877f2;"></span>
                    Impressions
                </div>
                <div style="display:flex;align-items:center;gap:7px;font-size:12.5px;font-weight:600;color:var(--ink-soft,#7a7672);">
                    <span class="fb-legend-dot" style="background:#e1306c;"></span>
                    Engagements
                </div>
                <div style="margin-left:auto;font-size:11.5px;color:var(--ink-faint,#9ca3af);">
                    Total Impressions: <strong style="color:var(--ink,#1a1a1a);">{{ number_format($fb['metrics']['total_reach']['total'] ?? 0) }}</strong>
                    &nbsp;·&nbsp;
                    Total Engagements: <strong style="color:var(--ink,#1a1a1a);">{{ number_format($fb['metrics']['total_engagement']['total'] ?? 0) }}</strong>
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
                    <div class="pa-title">Facebook Posts — {{ $monthLabel }}</div>
                    <div class="pa-sub">{{ count($fb['posts']) }} post(s) during this period</div>
                </div>
                <a href="{{ route('admin.reports.fb-export', ['month' => $selectedMonth]) }}"
                   style="display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:var(--navy,#002366);background:var(--navy-pale,#eff6ff);border:1.5px solid rgba(0,35,102,0.15);padding:7px 14px;border-radius:9px;text-decoration:none;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export CSV
                </a>
            </div>
            <div style="overflow-x:auto;">
                <table class="rtable">
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
                                    <div style="font-size:12.5px;font-weight:600;color:var(--ink,#1a1a1a);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:320px;">
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
                        <td style="font-size:11.5px;color:var(--ink-soft,#7a7672);white-space:nowrap;">
                            {{ \Carbon\Carbon::parse($post['created_time'])->format('M j, Y') }}
                        </td>
                        <td>
                            @if(!empty($post['permalink_url']))
                            <a href="{{ $post['permalink_url'] }}" target="_blank"
                               style="font-size:11.5px;color:var(--navy,#002366);font-weight:600;text-decoration:none;display:flex;align-items:center;gap:4px;">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
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
    <div class="fb-banner">
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
            <div style="font-size:12px;font-weight:600;color:var(--ink-soft,#7a7672);">{{ $lbl }}</div>
            <div class="fb-ph-block">
                <svg width="22" height="22" fill="none" stroke="#93c5fd" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                <p>Waiting for API</p>
            </div>
        </div>
        @endforeach
    </div>
@endif

{{-- ═══════════════════════════════════════════════
     REPORTS & DOWNLOADS
════════════════════════════════════════════════ --}}
<div class="fb-divider">
    <div class="fb-divider__line"></div>
    <div class="fb-divider__label">📥 Reports & Downloads</div>
    <div class="fb-divider__line"></div>
</div>

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

{{-- ═══════════════════════════════════════════════
     REQUEST RECORDS & FILTER
════════════════════════════════════════════════ --}}
<div class="fb-divider">
    <div class="fb-divider__line"></div>
    <div class="fb-divider__label">📋 Request Records & Filter</div>
    <div class="fb-divider__line"></div>
</div>

{{-- FILTER --}}
<div class="filter-panel">
    <div class="filter-panel__title">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filter Reports
    </div>
    <form method="GET" action="{{ route('admin.reports') }}" class="filter-row">
        <select name="status">
            <option value="all"      {{ $filter_status === 'all'      ? 'selected' : '' }}>All Statuses</option>
            <option value="pending"  {{ $filter_status === 'pending'  ? 'selected' : '' }}>Pending Review</option>
            <option value="review"   {{ $filter_status === 'review'   ? 'selected' : '' }}>Under Review</option>
            <option value="approved" {{ $filter_status === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="posted"   {{ $filter_status === 'posted'   ? 'selected' : '' }}>Posted</option>
            <option value="rejected" {{ $filter_status === 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
        <select name="category">
            <option value="all">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ $filter_category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <select name="priority">
            <option value="all"    {{ $filter_priority === 'all'    ? 'selected' : '' }}>All Priorities</option>
            <option value="urgent" {{ $filter_priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
            <option value="high"   {{ $filter_priority === 'high'   ? 'selected' : '' }}>High</option>
            <option value="medium" {{ $filter_priority === 'medium' ? 'selected' : '' }}>Medium</option>
            <option value="low"    {{ $filter_priority === 'low'    ? 'selected' : '' }}>Low</option>
        </select>
        <input type="date" name="date_from" value="{{ $date_from }}" placeholder="From date">
        <input type="date" name="date_to"   value="{{ $date_to }}"   placeholder="To date">
        <button type="submit" class="btn-filter">Apply Filter</button>
        @if($filter_status !== 'all' || $filter_category !== 'all' || $filter_priority !== 'all' || $date_from || $date_to)
            <a href="{{ route('admin.reports') }}" class="btn-reset-filter">Reset</a>
        @endif
    </form>
</div>

{{-- SUMMARY CARDS --}}
<div class="summary-row">
    <div class="sum-card">
        <div class="sum-card__num" style="color:var(--primary-light, #2563eb);">{{ $total }}</div>
        <div class="sum-card__lbl">Matching Requests</div>
    </div>
    <div class="sum-card">
        <div class="sum-card__num" style="color:#d97706;">{{ $r_pending }}</div>
        <div class="sum-card__lbl">Pending Review</div>
    </div>
    <div class="sum-card">
        <div class="sum-card__num" style="color:#10b981;">{{ $r_approved }}</div>
        <div class="sum-card__lbl">Approved / Posted</div>
    </div>
    <div class="sum-card">
        <div class="sum-card__num" style="color:#ef4444;">{{ $r_rejected }}</div>
        <div class="sum-card__lbl">Rejected</div>
    </div>
</div>

{{-- TABLE PANEL --}}
<div class="table-panel">
    <div class="table-panel__head">
        <div class="table-panel__title">Request Records</div>
        <div class="table-panel__meta">
            Showing <strong>{{ $total }}</strong> result{{ $total !== 1 ? 's' : '' }}
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table class="main-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Requestor</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Platforms</th>
                    <th>Preferred Date</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
            @forelse($requests as $i => $req)
                @php
                    $sl = strtolower($req->status);
                    $sc = match(true) {
                        str_contains($sl,'approved')     => 'approved',
                        str_contains($sl,'posted')       => 'posted',
                        str_contains($sl,'under review') => 'under-review',
                        str_contains($sl,'rejected')     => 'rejected',
                        default                          => 'pending',
                    };
                    $pc = match(strtolower($req->priority ?? '')) {
                        'urgent'=>'urgent','high'=>'high','medium'=>'medium',default=>'low'
                    };
                    $plats = $req->platforms_array ?? [];
                @endphp
                <tr onclick="window.location='{{ route('admin.requests.show', $req->id) }}'" style="cursor:pointer;">
                    <td style="color:var(--ink-soft,#7a7672);font-size:12px;">{{ $i + 1 }}</td>
                    <td>
                        <div style="font-weight:600;font-size:12.5px;color:var(--ink,#1a1a1a);">{{ Str::limit($req->title,30) }}</div>
                        <div style="font-size:11px;color:var(--ink-soft,#7a7672);">{{ Str::limit($req->description,45) }}</div>
                    </td>
                    <td style="font-size:12.5px;font-weight:500;">{{ $req->requester }}</td>
                    <td style="font-size:12px;color:var(--ink-soft,#7a7672);">{{ $req->category }}</td>
                    <td><span class="badge badge--{{ $pc }}">{{ strtoupper($req->priority) }}</span></td>
                    <td><span class="badge badge--{{ $sc }}">{{ $req->status }}</span></td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            @foreach(array_slice($plats,0,2) as $p)
                                <span style="font-size:10px;padding:2px 7px;background:var(--navy-pale,#eff6ff);color:var(--navy,#002366);border-radius:10px;font-weight:600;">{{ $p }}</span>
                            @endforeach
                            @if(count($plats) > 2)
                                <span style="font-size:10px;color:var(--ink-soft,#7a7672);">+{{ count($plats)-2 }}</span>
                            @endif
                        </div>
                    </td>
                    <td style="font-size:11.5px;color:var(--ink-soft,#7a7672);">
                        {{ $req->preferred_date ? \Carbon\Carbon::parse($req->preferred_date)->format('M j, Y') : '—' }}
                    </td>
                    <td style="font-size:11.5px;color:var(--ink-soft,#7a7672);white-space:nowrap;">
                        {{ $req->created_at->format('M j, Y') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <svg width="36" height="36" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:8px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            <div>No requests match your filters.</div>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
(function() {
    /* ── Internal Trend Chart ─────────────────────────────────────── */
    const trendEl = document.getElementById('trendChart');
    if (trendEl) {
        const months   = @json(array_column($months_data ?? [], 'label'));
        const totals   = @json(array_column($months_data ?? [], 'total'));
        const posted   = @json(array_column($months_data ?? [], 'posted'));
        const approved = @json(array_column($months_data ?? [], 'approved'));

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
                legend: { display: false },
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