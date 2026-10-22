@extends('layouts.requestor')

@section('title', 'Create Request')
@section('page-title', 'Create Request')

@section('head-styles')
<style>

/* ── PAGE SHELL ─────────────────────── */
.create-page {
    min-height: calc(100vh - 64px);
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 32px 24px 60px;
    background: #e8ecf4;
}

/* ── HERO CARD ───────────────────────── */
.hero-card {
    width: 100%; max-width: 680px;
    background: linear-gradient(135deg, #001a4d 0%, #002366 60%, #003080 100%);
    border-radius: 22px; padding: 30px 32px 26px;
    margin-bottom: 18px; position: relative; overflow: hidden;
}
.hero-card::before {
    content: '';
    position: absolute; right: -40px; top: -40px;
    width: 200px; height: 200px; border-radius: 50%;
    background: rgba(59,110,245,0.1);
}
.hero-card::after {
    content: '';
    position: absolute; right: 60px; bottom: -60px;
    width: 140px; height: 140px; border-radius: 50%;
    background: rgba(59,110,245,0.06);
}
.hero-eyebrow {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 10px; font-weight: 700; letter-spacing: 1.1px; text-transform: uppercase;
    color: #93c5fd; background: rgba(147,197,253,0.12);
    border: 1px solid rgba(147,197,253,0.22);
    padding: 4px 12px; border-radius: 20px; margin-bottom: 14px;
    position: relative; z-index: 1;
}
.hero-card h1 {
    font-size: 26px; font-weight: 800; color: white;
    letter-spacing: -0.6px; margin-bottom: 8px; line-height: 1.15;
    position: relative; z-index: 1;
}
.hero-card h1 span { color: #93c5fd; }
.hero-card p {
    font-size: 12.5px; color: rgba(255,255,255,0.45);
    line-height: 1.65; max-width: 460px; position: relative; z-index: 1;
}
.hero-progress { margin-top: 22px; position: relative; z-index: 1; }
.hero-progress__label {
    display: flex; justify-content: space-between;
    font-size: 10.5px; color: rgba(255,255,255,0.38); margin-bottom: 7px;
}
.hero-progress__track {
    height: 4px; background: rgba(255,255,255,0.1); border-radius: 4px;
}
.hero-progress__fill {
    height: 4px; background: linear-gradient(90deg, #3b6ef5, #60a5fa);
    border-radius: 4px; width: 33%;
    transition: width .4s ease;
}

/* ── ALERTS ─────────────────────────── */
.alert {
    padding: 12px 16px; border-radius: 12px; font-size: 13px;
    margin-bottom: 16px; font-weight: 500; width: 100%; max-width: 680px;
}
.alert--success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.alert--error   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

/* ── FORM WRAPPER ───────────────────── */
.form-body { width: 100%; max-width: 680px; display: flex; flex-direction: column; gap: 14px; }

/* ── SECTION CARD ───────────────────── */
.form-section {
    background: white; border-radius: 18px;
    border: 1.5px solid #e4e8f0;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    transition: box-shadow .2s, border-color .2s;
}
.form-section:focus-within {
    box-shadow: 0 4px 20px rgba(59,110,245,0.08);
    border-color: rgba(59,110,245,0.25);
}
.section-head {
    display: flex; align-items: center; gap: 14px;
    padding: 15px 22px; border-bottom: 1.5px solid #f0f2f8;
    background: #fafbfd;
}
.section-num {
    width: 28px; height: 28px; border-radius: 8px;
    background: #002366; color: white;
    font-size: 12px; font-weight: 800;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.section-title-txt { font-size: 13.5px; font-weight: 700; color: #111827; }
.section-sub { font-size: 11px; color: #9ca3af; margin-top: 1px; }
.section-body { padding: 22px 24px; }

/* ── FIELDS ─────────────────────────── */
.field { margin-bottom: 16px; }
.field:last-child { margin-bottom: 0; }
.field label {
    display: block; font-size: 10.5px; font-weight: 700; color: #6b7280;
    margin-bottom: 7px; letter-spacing: 0.4px; text-transform: uppercase;
}
.field label .req { color: #ef4444; margin-left: 2px; }
.field label .opt { color: #d1d5db; font-weight: 400; text-transform: none; font-size: 11px; letter-spacing: 0; }
.field input[type="text"],
.field input[type="date"],
.field select,
.field textarea {
    width: 100%; border: 1.5px solid #e4e8f0; border-radius: 11px;
    padding: 11px 14px; font-size: 13.5px; font-family: var(--font);
    color: #111827; background: #fafbfd;
    outline: none; transition: border-color .15s, box-shadow .15s, background .15s;
}
.field input:focus, .field select:focus, .field textarea:focus {
    border-color: #3b6ef5; background: white;
    box-shadow: 0 0 0 3px rgba(59,110,245,0.08);
}
.field textarea { resize: vertical; min-height: 110px; line-height: 1.65; }
.field input::placeholder, .field textarea::placeholder { color: #d1d5db; }
.field select { appearance: none; cursor: pointer; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

/* ── DATE PICKER WITH CALENDAR LOAD ─── */
.date-wrapper { position: relative; }
.date-input-wrap { position: relative; }
.date-input-wrap input[type="date"] { padding-right: 44px; cursor: pointer; }
.date-cal-icon {
    position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
    color: #9ca3af; pointer-events: none;
}

/* Calendar load panel */
.cal-load-panel {
    display: none; margin-top: 10px;
    background: white; border: 1.5px solid #e4e8f0;
    border-radius: 14px; overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
    animation: panelIn .18s ease;
}
@keyframes panelIn { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:none; } }
.cal-load-panel.visible { display: block; }

.cal-panel-header {
    padding: 12px 16px; border-bottom: 1px solid #f0f2f8;
    background: #fafbfd; display: flex; align-items: center; justify-content: space-between;
}
.cal-panel-date-label {
    font-size: 12.5px; font-weight: 700; color: #111827;
}
.cal-panel-meta { font-size: 11px; color: #9ca3af; }

.cal-load-body { padding: 14px 16px; }

/* Capacity bar */
.cal-capacity-wrap { margin-bottom: 14px; }
.cal-capacity-label {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 6px;
}
.cal-capacity-label span { font-size: 11.5px; color: #6b7280; font-weight: 500; }
.cal-capacity-label strong { font-size: 13px; font-weight: 800; }
.cal-capacity-label strong.low    { color: #16a34a; }
.cal-capacity-label strong.medium { color: #d97706; }
.cal-capacity-label strong.high   { color: #dc2626; }

.cal-bar-track {
    height: 8px; background: #f3f4f6; border-radius: 8px; overflow: hidden;
}
.cal-bar-fill {
    height: 8px; border-radius: 8px;
    transition: width .5s cubic-bezier(.34,1.4,.64,1);
}
.cal-bar-fill.low    { background: linear-gradient(90deg, #4ade80, #16a34a); }
.cal-bar-fill.medium { background: linear-gradient(90deg, #fbbf24, #d97706); }
.cal-bar-fill.high   { background: linear-gradient(90deg, #f87171, #dc2626); }

/* Existing requests mini-list */
.cal-req-list { display: flex; flex-direction: column; gap: 7px; margin-top: 12px; }
.cal-req-item {
    display: flex; align-items: center; gap: 10px;
    padding: 9px 12px; border-radius: 9px;
    background: #f8f9fb; border: 1px solid #f0f2f8;
    font-size: 12px;
}
.cal-req-dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.cal-req-dot.pending      { background: #9ca3af; }
.cal-req-dot.approved     { background: #16a34a; }
.cal-req-dot.under-review { background: #d97706; }
.cal-req-name { font-weight: 600; color: #374151; flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.cal-req-status { font-size: 10.5px; color: #9ca3af; white-space: nowrap; }

.cal-empty-day {
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    padding: 18px 0; text-align: center;
}
.cal-empty-day p { font-size: 12.5px; color: #9ca3af; }
.cal-empty-day strong { display: block; font-size: 13px; color: #16a34a; font-weight: 700; }

.cal-tip {
    margin-top: 12px; padding: 9px 12px;
    background: #fffbeb; border: 1px solid #fde68a; border-radius: 9px;
    font-size: 11.5px; color: #92400e; line-height: 1.5;
    display: none;
}
.cal-tip.visible { display: block; }

.cal-loading {
    padding: 20px; text-align: center;
    font-size: 12.5px; color: #9ca3af;
    display: flex; align-items: center; justify-content: center; gap: 8px;
}
.cal-spinner {
    width: 16px; height: 16px; border: 2px solid #e4e8f0;
    border-top-color: #3b6ef5; border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ── PLATFORM BUTTONS ───────────────── */
.platform-group { display: flex; gap: 8px; flex-wrap: wrap; }
.platform-btn {
    padding: 9px 18px; border-radius: 10px; border: 1.5px solid #e4e8f0;
    font-size: 12.5px; font-weight: 600; cursor: pointer;
    background: #fafbfd; color: #6b7280;
    font-family: var(--font); transition: all .15s;
    display: flex; align-items: center; gap: 8px;
}
.platform-btn:hover:not(.selected) { border-color: #93c5fd; color: #1d4ed8; background: #eff6ff; }
.platform-btn.selected { background: #002366; color: white; border-color: #002366; box-shadow: 0 2px 10px rgba(0,26,110,0.25); }
.platform-dot { width: 9px; height: 9px; border-radius: 50%; }
.platform-dot--fb { background: #1877f2; }
.platform-dot--li { background: #0a66c2; }
.platform-btn.selected .platform-dot--fb,
.platform-btn.selected .platform-dot--li { background: rgba(255,255,255,0.6); }

/* ── MEDIA UPLOAD ───────────────────── */
.upload-zone {
    border: 2px dashed #d1d5db; border-radius: 14px;
    padding: 28px 20px; text-align: center; background: #fafbfd;
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    cursor: pointer; transition: all .15s;
}
.upload-zone:hover { border-color: #3b6ef5; background: #eff6ff; }
.upload-icon-wrap {
    width: 44px; height: 44px; border-radius: 12px;
    background: #dbeafe; display: flex; align-items: center; justify-content: center;
}
.upload-zone p { font-size: 13px; color: #6b7280; }
.upload-zone strong { color: #3b6ef5; font-weight: 700; }
.upload-tags { display: flex; gap: 6px; }
.upload-tag { font-size: 10.5px; color: #9ca3af; background: #f3f4f6; padding: 3px 9px; border-radius: 6px; font-weight: 500; }
.upload-previews { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px; }
.preview-wrap { position: relative; width: 82px; height: 82px; }
.preview-thumb { width: 82px; height: 82px; border-radius: 10px; object-fit: cover; border: 1.5px solid #e4e8f0; display: block; }
.preview-remove {
    position: absolute; top: -7px; right: -7px;
    width: 22px; height: 22px; background: #ef4444; color: white;
    border: 2px solid white; border-radius: 50%; font-size: 12px;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 1px 4px rgba(0,0,0,0.15);
}
#media-input { display: none; }

/* ── FILL FROM POSTER BUTTON ────────── */
.btn-fill-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 7px 18px;
    background: linear-gradient(135deg, #002366 0%, #1e40af 100%);
    color: #ffffff;
    border: 1.5px solid rgba(59, 130, 246, 0.4);
    border-radius: 9999px;
    font-size: 12.5px;
    font-weight: 700;
    font-family: var(--font, 'DM Sans', sans-serif);
    letter-spacing: 0.3px;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 35, 102, 0.25);
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    white-space: nowrap;
}
.btn-fill-action:hover {
    background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
    border-color: #60a5fa;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
}
.btn-fill-action:active {
    transform: translateY(0);
}
.btn-fill-action:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}
[data-theme="dark"] .btn-fill-action {
    background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
    border-color: rgba(96, 165, 250, 0.5);
    color: #f8fafc;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
}
[data-theme="dark"] .btn-fill-action:hover {
    background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
    border-color: #93c5fd;
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.45);
}
@keyframes pulseFieldGreen {
    0% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.45); border-color: #22c55e; }
    50% { box-shadow: 0 0 0 6px rgba(34, 197, 94, 0.2); border-color: #22c55e; }
    100% { box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
}
.field-highlight-pulse {
    animation: pulseFieldGreen 1.2s ease-out 2 !important;
}

/* ── AI SECTION ─────────────────────── */
.ai-section {
    background: white;
    border: 1.5px solid #e4e8f0;
    border-top: 3px solid #7c3aed;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.ai-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 15px 22px; border-bottom: 1.5px solid #f0f2f8;
    background: #fafbfd;
}
.ai-head-left { display: flex; align-items: center; gap: 10px; }
.ai-icon {
    width: 32px; height: 32px; border-radius: 9px;
    background: #ede9fe; display: flex; align-items: center; justify-content: center;
}
.ai-title-name { font-size: 13.5px; font-weight: 700; color: #111827; }
.ai-badge {
    font-size: 9px; font-weight: 800; letter-spacing: 0.5px;
    color: #7c3aed; background: #ede9fe; border: 1px solid #ddd6fe;
    padding: 2px 8px; border-radius: 20px;
}
.ai-generate-btn {
    display: flex; align-items: center; gap: 7px; padding: 9px 18px;
    background: #5b21b6; color: white; border: none; border-radius: 10px;
    font-size: 12.5px; font-weight: 700; cursor: pointer; font-family: var(--font);
    transition: all .15s; box-shadow: 0 2px 8px rgba(91,33,182,0.25);
}
.ai-generate-btn:hover { background: #4c1d95; transform: translateY(-1px); }
.ai-generate-btn:disabled { opacity: .5; cursor: not-allowed; transform: none; }
.ai-body { padding: 20px 22px; }
.ai-hint { font-size: 12px; color: #9ca3af; margin-bottom: 10px; }
.ai-textarea {
    width: 100%; border: 1.5px solid #e4e8f0; border-radius: 11px;
    padding: 12px 14px; font-size: 13.5px; font-family: var(--font);
    background: #fafbfd; color: #111827;
    outline: none; resize: vertical; min-height: 140px;
    transition: border-color .15s, box-shadow .15s; line-height: 1.65;
}
.ai-textarea:focus { border-color: #8b5cf6; background: white; box-shadow: 0 0 0 3px rgba(139,92,246,0.1); }
.ai-textarea::placeholder { color: #d1d5db; }
.ai-footer-preview {
    margin-top: 12px; padding: 11px 14px;
    background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 10px;
    font-size: 11.5px; color: #6d28d9; line-height: 1.7;
}
.ai-footer-preview__label {
    display: block; font-size: 9px; font-weight: 800; letter-spacing: 0.6px;
    text-transform: uppercase; color: #7c3aed; margin-bottom: 6px; opacity: 0.7;
}

/* ── FORM ACTIONS ───────────────────── */
.form-actions {
    display: flex; justify-content: space-between; align-items: center;
    padding: 18px 22px; background: white; border-radius: 18px;
    border: 1.5px solid #e4e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.form-actions__left { font-size: 12px; color: #9ca3af; }
.form-actions__left span { color: #ef4444; }
.actions-right { display: flex; gap: 10px; }
.btn-cancel {
    padding: 10px 22px; border-radius: 10px; border: 1.5px solid #e4e8f0;
    background: white; font-size: 13.5px; font-weight: 600; cursor: pointer;
    color: #6b7280; font-family: var(--font); transition: all .15s;
}
.btn-cancel:hover { background: #f3f4f6; border-color: #c8cdd8; color: #374151; }
.btn-submit {
    padding: 10px 28px; border-radius: 10px; border: none;
    background: #002366; color: white; font-size: 13.5px; font-weight: 700;
    cursor: pointer; font-family: var(--font);
    transition: all .15s; display: flex; align-items: center; gap: 8px;
    box-shadow: 0 3px 12px rgba(0,26,110,0.3);
}
.btn-submit:hover { background: #001a56; transform: translateY(-1px); box-shadow: 0 5px 18px rgba(0,26,110,0.35); }

@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
@media (max-width: 600px) { .field-row { grid-template-columns: 1fr; } }

/* ── SLA POLICY MODAL ──────────────── */
.sla-overlay {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,0.55);
    backdrop-filter: blur(6px);
    display: flex; align-items: center; justify-content: center;
    padding: 20px;
    animation: slaFadeIn .25s ease;
}
@keyframes slaFadeIn { from { opacity: 0; } to { opacity: 1; } }
.sla-modal {
    width: 100%; max-width: 520px;
    max-height: 90vh;
    background: linear-gradient(170deg, #0d1526 0%, #111b33 60%, #162040 100%);
    border-radius: 24px;
    border: 1px solid rgba(255,255,255,0.08);
    box-shadow: 0 32px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.04);
    display: flex; flex-direction: column;
    overflow: hidden;
    animation: slaSlideUp .3s cubic-bezier(.34,1.3,.64,1);
}
@keyframes slaSlideUp { from { opacity: 0; transform: translateY(30px) scale(0.96); } to { opacity: 1; transform: none; } }
.sla-header {
    display: flex; align-items: center; gap: 14px;
    padding: 22px 24px 18px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
}
.sla-header-icon {
    width: 44px; height: 44px; border-radius: 14px;
    background: rgba(59,110,245,0.15);
    border: 1px solid rgba(59,110,245,0.25);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sla-header-text h2 {
    font-size: 18px; font-weight: 800; color: white;
    letter-spacing: -0.3px; margin: 0;
    display: flex; align-items: center; gap: 8px;
}
.sla-badge-policy {
    font-size: 9px; font-weight: 800; letter-spacing: 0.6px;
    text-transform: uppercase;
    background: rgba(16,185,129,0.18); color: #6ee7b7;
    border: 1px solid rgba(16,185,129,0.3);
    padding: 3px 10px; border-radius: 20px;
}
.sla-header-text p {
    font-size: 12.5px; color: rgba(255,255,255,0.45);
    margin: 4px 0 0; line-height: 1.5;
}
.sla-body {
    flex: 1; overflow-y: auto; padding: 8px 16px 16px;
}
.sla-body::-webkit-scrollbar { width: 4px; }
.sla-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
.sla-item {
    display: flex; align-items: center; gap: 14px;
    padding: 12px 16px; margin-bottom: 6px;
    border-radius: 14px;
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.05);
    transition: background .15s, border-color .15s;
}
.sla-item:hover {
    background: rgba(255,255,255,0.06);
    border-color: rgba(255,255,255,0.1);
}
.sla-item-icon {
    width: 38px; height: 38px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 16px;
}
.sla-item-icon--check   { background: rgba(16,185,129,0.14); }
.sla-item-icon--post    { background: rgba(14,165,233,0.14); }
.sla-item-icon--tmpl    { background: rgba(168,85,247,0.14); }
.sla-item-icon--std     { background: rgba(245,158,11,0.14); }
.sla-item-icon--multi   { background: rgba(239,68,68,0.14); }
.sla-item-icon--campaign{ background: rgba(99,102,241,0.14); }
.sla-item-icon--event   { background: rgba(239,68,68,0.14); }
.sla-item-info { flex: 1; min-width: 0; }
.sla-item-title {
    font-size: 13.5px; font-weight: 700; color: white;
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
    margin-bottom: 2px;
}
.sla-turnaround {
    font-size: 10.5px; font-weight: 700; letter-spacing: 0.3px;
    padding: 3px 10px; border-radius: 20px;
    white-space: nowrap;
}
.sla-ta--green  { background: rgba(16,185,129,0.18); color: #6ee7b7; }
.sla-ta--yellow { background: rgba(245,158,11,0.18); color: #fcd34d; }
.sla-ta--orange { background: rgba(249,115,22,0.18); color: #fdba74; }
.sla-ta--red    { background: rgba(239,68,68,0.18); color: #fca5a5; }
.sla-ta--purple { background: rgba(168,85,247,0.18); color: #d8b4fe; }
.sla-item-note {
    font-size: 11.5px; color: rgba(255,255,255,0.4);
    line-height: 1.55; margin-top: 2px;
}
.sla-item-warn {
    font-size: 11px; color: #fcd34d; margin-top: 4px;
    font-weight: 600;
}
.sla-context-note {
    margin: 8px 16px 0;
    padding: 12px 16px;
    background: rgba(59,130,246,0.08);
    border: 1px solid rgba(59,130,246,0.15);
    border-radius: 12px;
    font-size: 11.5px; color: rgba(255,255,255,0.55);
    line-height: 1.6;
    display: flex; align-items: flex-start; gap: 8px;
}
.sla-footer {
    padding: 14px 20px;
    border-top: 1px solid rgba(255,255,255,0.06);
    display: flex; flex-direction: column; gap: 12px;
}
.sla-checkbox-row {
    display: flex; align-items: center; gap: 10px;
    padding: 0 4px;
}
.sla-checkbox-row input[type="checkbox"] {
    width: 18px; height: 18px; accent-color: #3b6ef5;
    cursor: pointer; flex-shrink: 0;
}
.sla-checkbox-row label {
    font-size: 12px; color: rgba(255,255,255,0.5);
    cursor: pointer; user-select: none;
}
.sla-actions {
    display: flex; gap: 10px;
}
.sla-btn-cancel {
    flex: 1; padding: 12px; border-radius: 12px;
    border: 1.5px solid rgba(255,255,255,0.12);
    background: transparent; color: rgba(255,255,255,0.6);
    font-size: 13.5px; font-weight: 600; cursor: pointer;
    font-family: var(--font); transition: all .15s;
}
.sla-btn-cancel:hover { background: rgba(255,255,255,0.06); color: white; }
.sla-btn-proceed {
    flex: 2; padding: 12px 20px; border-radius: 12px;
    border: none;
    background: linear-gradient(135deg, #1d4ed8 0%, #3b6ef5 100%);
    color: white; font-size: 13.5px; font-weight: 700;
    cursor: pointer; font-family: var(--font);
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: all .15s;
    box-shadow: 0 4px 16px rgba(59,110,245,0.35);
}
.sla-btn-proceed:hover { background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%); transform: translateY(-1px); }

/* ── SLA NOTE BELOW CATEGORY ───────── */
.sla-inline-note {
    margin-top: 12px; padding: 11px 14px;
    background: #f0fdf4; border: 1px solid #bbf7d0;
    border-radius: 11px;
    font-size: 12px; color: #15803d; line-height: 1.6;
    display: none;
    align-items: flex-start; gap: 8px;
    animation: noteIn .2s ease;
}
@keyframes noteIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: none; } }
.sla-inline-note.visible { display: flex; }
.sla-inline-note svg { flex-shrink: 0; margin-top: 1px; }

/* ── VALIDATION ERROR STYLES ───────── */
.field-error {
    font-size: 11px; color: #ef4444; margin-top: 4px;
    display: none;
}
.field-error.visible { display: block; }
.field.has-error input,
.field.has-error textarea,
.field.has-error select {
    border-color: #fca5a5 !important;
    background: #fef2f2 !important;
}

/* ── DARK THEME OVERRIDES ── */
[data-theme="dark"] .create-page {
    background: #040c1e !important;
}
[data-theme="dark"] .form-section {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
}
[data-theme="dark"] .section-head {
    background: #06112a !important;
    border-bottom: 1.5px solid rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .section-num {
    background: #1e4fd8 !important;
    color: white !important;
}
[data-theme="dark"] .section-title-txt {
    color: #f8fafc !important;
}
[data-theme="dark"] .section-sub {
    color: #94a3b8 !important;
}
[data-theme="dark"] .field label {
    color: #94a3b8 !important;
}
[data-theme="dark"] .field label .opt {
    color: #64748b !important;
}
[data-theme="dark"] .field input[type="text"],
[data-theme="dark"] .field input[type="date"],
[data-theme="dark"] .field select,
[data-theme="dark"] .field textarea {
    background-color: #0b1a3d !important;
    color: #f8fafc !important;
    border-color: rgba(30, 79, 216, 0.3) !important;
}
[data-theme="dark"] .field input:focus,
[data-theme="dark"] .field select:focus,
[data-theme="dark"] .field textarea:focus {
    border-color: #3b82f6 !important;
    background-color: #0d214d !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2) !important;
}
[data-theme="dark"] .field input::placeholder,
[data-theme="dark"] .field textarea::placeholder {
    color: #64748b !important;
}
[data-theme="dark"] .field select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") !important;
}
[data-theme="dark"] .platform-btn {
    background: #0b1a3d !important;
    color: #cbd5e1 !important;
    border-color: rgba(30, 79, 216, 0.3) !important;
}
[data-theme="dark"] .platform-btn:hover:not(.selected) {
    background: #102654 !important;
    color: #93c5fd !important;
    border-color: #3b82f6 !important;
}
[data-theme="dark"] .platform-btn.selected {
    background: #1d4ed8 !important;
    color: white !important;
    border-color: #3b82f6 !important;
    box-shadow: 0 2px 10px rgba(29, 78, 216, 0.4) !important;
}
[data-theme="dark"] .upload-zone {
    background: #0b1a3d !important;
    border-color: rgba(30, 79, 216, 0.35) !important;
}
[data-theme="dark"] .upload-zone:hover {
    background: #0f2250 !important;
    border-color: #3b82f6 !important;
}
[data-theme="dark"] .upload-icon-wrap {
    background: rgba(59, 110, 245, 0.2) !important;
}
[data-theme="dark"] .upload-zone p {
    color: #94a3b8 !important;
}
[data-theme="dark"] .upload-zone strong {
    color: #60a5fa !important;
}
[data-theme="dark"] .upload-tag {
    background: #06112a !important;
    color: #94a3b8 !important;
}
[data-theme="dark"] .preview-thumb {
    border-color: rgba(30, 79, 216, 0.35) !important;
}
[data-theme="dark"] .ai-section {
    background: #081636 !important;
    border-color: rgba(124, 58, 237, 0.35) !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
}
[data-theme="dark"] .ai-head {
    background: #06112a !important;
    border-bottom: 1.5px solid rgba(124, 58, 237, 0.25) !important;
}
[data-theme="dark"] .ai-icon {
    background: rgba(124, 58, 237, 0.2) !important;
}
[data-theme="dark"] .ai-title-name {
    color: #f8fafc !important;
}
[data-theme="dark"] .ai-badge {
    background: rgba(124, 58, 237, 0.25) !important;
    border-color: rgba(124, 58, 237, 0.4) !important;
    color: #c4b5fd !important;
}
[data-theme="dark"] .ai-hint {
    color: #94a3b8 !important;
}
[data-theme="dark"] .ai-textarea {
    background: #0b1a3d !important;
    color: #f8fafc !important;
    border-color: rgba(124, 58, 237, 0.3) !important;
}
[data-theme="dark"] .ai-textarea:focus {
    background: #0e2252 !important;
    border-color: #a78bfa !important;
    box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.2) !important;
}
[data-theme="dark"] .ai-textarea::placeholder {
    color: #64748b !important;
}
[data-theme="dark"] .ai-footer-preview {
    background: rgba(124, 58, 237, 0.12) !important;
    border-color: rgba(124, 58, 237, 0.3) !important;
    color: #c4b5fd !important;
}
[data-theme="dark"] .ai-footer-preview__label {
    color: #a78bfa !important;
}
[data-theme="dark"] .form-actions {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3) !important;
}
[data-theme="dark"] .form-actions__left {
    color: #94a3b8 !important;
}
[data-theme="dark"] .btn-cancel {
    background: #0b1a3d !important;
    border-color: rgba(30, 79, 216, 0.3) !important;
    color: #cbd5e1 !important;
}
[data-theme="dark"] .btn-cancel:hover {
    background: #0f2250 !important;
    color: #f8fafc !important;
    border-color: #3b82f6 !important;
}
[data-theme="dark"] .cal-load-panel {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.3) !important;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5) !important;
}
[data-theme="dark"] .cal-panel-header {
    background: #06112a !important;
    border-bottom: 1px solid rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .cal-panel-date-label {
    color: #f8fafc !important;
}
[data-theme="dark"] .cal-panel-meta {
    color: #94a3b8 !important;
}
[data-theme="dark"] .cal-capacity-label span {
    color: #94a3b8 !important;
}
[data-theme="dark"] .cal-bar-track {
    background: #0b1a3d !important;
}
[data-theme="dark"] .cal-req-item {
    background: #0b1a3d !important;
    border-color: rgba(30, 79, 216, 0.2) !important;
}
[data-theme="dark"] .cal-req-name {
    color: #cbd5e1 !important;
}
[data-theme="dark"] .cal-req-status {
    color: #94a3b8 !important;
}
[data-theme="dark"] .cal-tip {
    background: rgba(245, 158, 11, 0.12) !important;
    border-color: rgba(245, 158, 11, 0.25) !important;
    color: #fcd34d !important;
}
[data-theme="dark"] .cal-empty-day p {
    color: #94a3b8 !important;
}
[data-theme="dark"] .cal-loading {
    color: #94a3b8 !important;
}
[data-theme="dark"] .sla-inline-note {
    background: rgba(16, 185, 129, 0.12) !important;
    border-color: rgba(16, 185, 129, 0.3) !important;
    color: #6ee7b7 !important;
}
</style>
@endsection

@section('content')

{{-- SLA POLICY MODAL --}}
<div class="sla-overlay" id="sla-overlay" style="display:none;">
    <div class="sla-modal">
        <div class="sla-header">
            <div class="sla-header-icon">
                <svg width="22" height="22" fill="none" stroke="#60a5fa" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div class="sla-header-text">
                <h2>Creative SLA <span class="sla-badge-policy">Policy</span></h2>
                <p>Standard turnaround periods observed by Marketing Office.</p>
            </div>
        </div>
        <div class="sla-body">
            <div class="sla-item">
                <div class="sla-item-icon sla-item-icon--check">
                    <svg width="18" height="18" fill="none" stroke="#10b981" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <div class="sla-item-info">
                    <div class="sla-item-title">
                        Checking of Materials
                        <span class="sla-turnaround sla-ta--green">Up to 24 hours</span>
                    </div>
                </div>
            </div>
            <div class="sla-item">
                <div class="sla-item-icon sla-item-icon--post">
                    <svg width="18" height="18" fill="none" stroke="#0ea5e9" stroke-width="2" viewBox="0 0 24 24"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
                </div>
                <div class="sla-item-info">
                    <div class="sla-item-title">
                        Posting with Ready-Made PubMat
                        <span class="sla-turnaround sla-ta--green">Up to 24 hours</span>
                    </div>
                    <div class="sla-item-warn">Caption must also be provided upon request submission.</div>
                </div>
            </div>
            <div class="sla-item">
                <div class="sla-item-icon sla-item-icon--tmpl">
                    <svg width="18" height="18" fill="none" stroke="#a855f7" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                </div>
                <div class="sla-item-info">
                    <div class="sla-item-title">
                        Template-Based PubMat
                        <span class="sla-turnaround sla-ta--yellow">Up to 48 hours</span>
                    </div>
                    <div class="sla-item-warn">e.g. announcements, congratulatory, news articles, partnerships.</div>
                </div>
            </div>
            <div class="sla-item">
                <div class="sla-item-icon sla-item-icon--std">
                    <svg width="18" height="18" fill="none" stroke="#f59e0b" stroke-width="2" viewBox="0 0 24 24"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><circle cx="12" cy="12" r="1"/></svg>
                </div>
                <div class="sla-item-info">
                    <div class="sla-item-title">
                        Standard PubMat
                        <span class="sla-turnaround sla-ta--orange">2–4 working days</span>
                    </div>
                    <div class="sla-item-note">Basic, non-templated promotional material created for a specific institutional event.</div>
                </div>
            </div>
            <div class="sla-item">
                <div class="sla-item-icon sla-item-icon--multi">
                    <svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                </div>
                <div class="sla-item-info">
                    <div class="sla-item-title">
                        Multiple Collaterals / Tarpaulins
                        <span class="sla-turnaround sla-ta--red">5–10 working days</span>
                    </div>
                </div>
            </div>
            <div class="sla-item">
                <div class="sla-item-icon sla-item-icon--campaign">
                    <svg width="18" height="18" fill="none" stroke="#6366f1" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18h6"/><path d="M10 22h4"/><path d="M12 2a7 7 0 0 0-7 7c0 2.5 1.5 4.7 3.5 6h7c2-1.3 3.5-3.5 3.5-6a7 7 0 0 0-7-7z"/></svg>
                </div>
                <div class="sla-item-info">
                    <div class="sla-item-title">
                        New Campaign / Creative Concept
                        <span class="sla-turnaround sla-ta--purple">10–20 working days</span>
                    </div>
                </div>
            </div>
            <div class="sla-item">
                <div class="sla-item-icon sla-item-icon--event">
                    <svg width="18" height="18" fill="none" stroke="#ef4444" stroke-width="2" viewBox="0 0 24 24"><path d="M23 7l-7 5 7 5V7z"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>
                </div>
                <div class="sla-item-info">
                    <div class="sla-item-title">
                        Event Documentation
                        <span class="sla-turnaround sla-ta--red">1 month prior to event</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="sla-context-note" id="sla-context-note" style="display:none;">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span id="sla-context-text"></span>
        </div>
        <div class="sla-footer">
            <div class="sla-checkbox-row">
                <input type="checkbox" id="sla-dont-show">
                <label for="sla-dont-show">Don't show this notice automatically again</label>
            </div>
            <div class="sla-actions">
                <button type="button" class="sla-btn-cancel" onclick="history.back()">Cancel</button>
                <button type="button" class="sla-btn-proceed" id="sla-proceed-btn">
                    I Understand & Proceed
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- SUBMISSION PREVIEW MODAL --}}
<div class="sla-overlay" id="preview-overlay" style="display:none;">
    <div class="sla-modal" style="max-width: 580px;">
        <div class="sla-header">
            <div class="sla-header-icon" style="background: rgba(30,79,216,0.18); border-color: rgba(30,79,216,0.35);">
                <svg width="20" height="20" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <div class="sla-header-text">
                <h2>Review Your Request <span class="sla-badge-policy" style="background:rgba(59,130,246,0.18);color:#93c5fd;border-color:rgba(59,130,246,0.3);">PREVIEW</span></h2>
                <p>Please double-check all details below before final submission to Marketing.</p>
            </div>
        </div>
        <div class="sla-body" style="padding: 16px 20px;">
            <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 18px; margin-bottom: 14px;">
                <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.6px; color: #94a3b8; margin-bottom: 6px;">Event / Post Title</div>
                <div id="pv-title" style="font-size: 16px; font-weight: 800; color: #f8fafc; line-height: 1.4; word-break: break-word;"></div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 14px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,0.06);">
                    <div>
                        <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #94a3b8; margin-bottom: 4px;">Category</div>
                        <div id="pv-category" style="font-size: 13px; font-weight: 700; color: #fcd34d;"></div>
                    </div>
                    <div>
                        <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #94a3b8; margin-bottom: 4px;">Priority</div>
                        <div id="pv-priority" style="font-size: 13px; font-weight: 700;"></div>
                    </div>
                    <div>
                        <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #94a3b8; margin-bottom: 4px;">Preferred Date</div>
                        <div id="pv-date" style="font-size: 13px; font-weight: 700; color: #f8fafc;"></div>
                    </div>
                    <div>
                        <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #94a3b8; margin-bottom: 4px;">Target Channels</div>
                        <div id="pv-platforms" style="font-size: 13px; font-weight: 700; color: #60a5fa;"></div>
                    </div>
                </div>
            </div>

            <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 14px; padding: 14px; margin-bottom: 12px;">
                <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #94a3b8; margin-bottom: 6px;">Description</div>
                <div id="pv-desc" style="font-size: 12.5px; color: #cbd5e1; line-height: 1.5; max-height: 80px; overflow-y: auto; white-space: pre-wrap;"></div>
            </div>

            <div id="pv-caption-wrap" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.06); border-radius: 14px; padding: 14px; margin-bottom: 12px; display:none;">
                <div style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: #94a3b8; margin-bottom: 6px;">Post Caption</div>
                <div id="pv-caption" style="font-size: 12px; color: #94a3b8; line-height: 1.4; max-height: 80px; overflow-y: auto; white-space: pre-wrap; font-family: monospace;"></div>
            </div>

            <div id="pv-media-info" style="font-size: 12px; color: #94a3b8; display: flex; align-items: center; gap: 8px;">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <span id="pv-media-count">No attachments</span>
            </div>
        </div>
        <div class="sla-footer">
            <div style="font-size: 11.5px; color: #94a3b8;">
                Ready to submit?
            </div>
            <div class="sla-actions">
                <button type="button" class="sla-btn-cancel" id="btn-close-preview">Edit Details</button>
                <button type="button" class="sla-btn-proceed" id="btn-confirm-submit" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 14px rgba(16,185,129,0.35);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Confirm & Submit
                </button>
            </div>
        </div>
    </div>
</div>

<div class="create-page" id="create-page">

    {{-- HERO CARD --}}
    <div class="hero-card" style="width:100%;max-width:680px;">
        <div class="hero-eyebrow">
            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Post Request
        </div>
        <h1>What would you like to <span>post?</span></h1>
        <p>Fill in the details below and our admin team will review your request. The more detail you provide, the faster we can approve it.</p>
        <div class="hero-progress">
            <div class="hero-progress__label">
                <span>Step 1 of 3 — Basic Info</span>
                <span>33%</span>
            </div>
            <div class="hero-progress__track">
                <div class="hero-progress__fill"></div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert--success">✅ {{ session('success') }} <a href="{{ route('requestor.requests') }}" style="color:inherit;font-weight:700;margin-left:6px;">View requests →</a></div>
    @endif
    @if(session('error'))
        <div class="alert alert--error">❌ {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('requestor.requests.store') }}" enctype="multipart/form-data" id="create-form" class="form-body">
        @csrf
        <input type="hidden" name="submission_token" id="submission-token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">

        {{-- SECTION 1: BASIC INFO --}}
        <div class="form-section">
            <div class="section-head">
                <div class="section-num">1</div>
                <div>
                    <div class="section-title-txt">Basic Information</div>
                    <div class="section-sub">Title, category, priority, and preferred date</div>
                </div>
            </div>
            <div class="section-body">
                <div class="field">
                    <label>Post Title <span class="req">*</span></label>
                    <input type="text" name="title" id="title-field" placeholder="e.g., College Week 2025 Opening Ceremony" value="{{ old('title') }}" required minlength="5">
                    <div class="field-error" id="title-error">Title must be at least 5 characters.</div>
                </div>
                <div class="field">
                    <label>Description <span class="req">*</span></label>
                    <textarea name="description" id="desc-field" placeholder="Describe your event or announcement in detail. Include key info like date, time, and location..." required minlength="15">{{ old('description') }}</textarea>
                    <div class="field-error" id="desc-error">Description must be at least 15 characters.</div>
                </div>
                <div class="field-row">
                    <div class="field" style="margin-bottom:0;">
                        <label>Category <span class="req">*</span></label>
                        <select name="category" id="cat-field" required>
                            <option value="" disabled {{ !old('category') ? 'selected' : '' }}>Select category</option>
                            @foreach(['Checking of Materials','Posting with Ready-Made PubMat','Template-Based PubMat','Standard PubMat','Multiple Collaterals / Tarpaulins','New Campaign / Creative Concept','Event Documentation'] as $cat)
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                        <div id="cat-suggest-badge" style="display:none;margin-top:6px;font-size:11.5px;color:#2563eb;font-weight:600;align-items:center;gap:4px;">
                            <span>💡</span> <span id="cat-suggest-text">Suggested based on title. (You can change this anytime)</span>
                        </div>
                    </div>
                    <div class="field" style="margin-bottom:0;">
                        <label>Priority <span class="req">*</span></label>
                        <select name="priority" id="priority-field" required>
                            <option value="" disabled {{ !old('priority') ? 'selected' : '' }}>Select priority</option>
                            @foreach(['Low','Medium','High'] as $pri)
                                <option value="{{ $pri }}" {{ old('priority') === $pri ? 'selected' : '' }}>{{ $pri }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- SLA TURNAROUND NOTE --}}
                <div class="sla-inline-note" id="sla-inline-note">
                    <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span id="sla-inline-text"></span>
                </div>

                {{-- PREFERRED DATE WITH LOAD INDICATOR --}}
                <div class="field" style="margin-top:16px;margin-bottom:0;">
                    <label>Preferred Post Date <span class="req">*</span></label>
                    <div class="date-wrapper">
                        <div class="date-input-wrap">
                            <input type="date" name="post_date" id="post-date-input"
                                   value="{{ old('post_date') }}" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                            <span class="date-cal-icon">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2.5"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </span>
                        </div>

                        {{-- CALENDAR LOAD PANEL --}}
                        <div class="cal-load-panel" id="cal-load-panel">
                            <div class="cal-panel-header">
                                <div class="cal-panel-date-label" id="cal-date-label">—</div>
                                <div class="cal-panel-meta" id="cal-meta">Checking schedule…</div>
                            </div>
                            <div class="cal-load-body" id="cal-load-body">
                                <div class="cal-loading">
                                    <div class="cal-spinner"></div>
                                    Loading schedule…
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- SECTION 2: PLATFORMS --}}
        <div class="form-section">
            <div class="section-head">
                <div class="section-num">2</div>
                <div>
                    <div class="section-title-txt">Target Platforms</div>
                    <div class="section-sub">Where should this be posted?&nbsp;<span style="color:#ef4444;font-size:11px;">*</span></div>
                </div>
            </div>
            <div class="section-body">
                @php $selected_platforms = old('platforms', []); @endphp
                <div class="platform-group" id="platform-group">
                    @foreach(['Facebook','LinkedIn'] as $p)
                        <button type="button"
                            class="platform-btn {{ in_array($p, $selected_platforms) ? 'selected' : '' }}"
                            data-platform="{{ $p }}">
                            <span class="platform-dot {{ $p === 'Facebook' ? 'platform-dot--fb' : 'platform-dot--li' }}"></span>
                            {{ $p }}
                        </button>
                    @endforeach
                </div>
                <div id="platform-inputs">
                    @foreach($selected_platforms as $p)
                        <input type="hidden" name="platforms[]" value="{{ $p }}">
                    @endforeach
                </div>
            </div>
        </div>

        {{-- SECTION 3: MEDIA --}}
        <div class="form-section">
            <div class="section-head" style="justify-content: space-between;">
                <div style="display:flex; align-items:center; gap:14px;">
                    <div class="section-num">3</div>
                    <div>
                        <div class="section-title-txt">Media Attachments</div>
                        <div class="section-sub">Up to 10 images · Max 10MB each</div>
                    </div>
                </div>
                <div class="section-head-actions">
                    <button type="button" 
                            id="btn-fill-description" 
                            class="btn-fill-action" 
                            style="display:none;" 
                            onclick="triggerFillFromPoster()"
                            title="Auto-fill description or fix spelling and add missing details from poster">
                        Fill
                    </button>
                </div>
            </div>
            <div class="section-body">
                <label class="upload-zone" for="media-input" id="upload-zone">
                    <div class="upload-icon-wrap">
                        <svg width="22" height="22" fill="none" stroke="#3b6ef5" stroke-width="1.8" viewBox="0 0 24 24"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                    </div>
                    <p><strong>Click to upload</strong> or drag and drop</p>
                    <div class="upload-tags">
                        <span class="upload-tag">JPG</span>
                        <span class="upload-tag">PNG</span>
                        <span class="upload-tag">MP4</span>
                        <span class="upload-tag">MOV</span>
                        <span class="upload-tag">Max 10MB</span>
                    </div>
                </label>
                <div class="upload-previews" id="upload-previews"></div>
                {{-- POSTER PROOFREADER QUALITY ADVISORY --}}
                <div id="proofread-advisory" style="display:none;margin-top:12px;padding:12px 16px;background:#fffbeb;border:1.5px solid #fde68a;border-radius:12px;">
                    <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;font-weight:700;color:#b45309;margin-bottom:6px;">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <span>Poster Proofreader Quality Tips</span>
                    </div>
                    <div id="proofread-issues-list" style="display:flex;flex-direction:column;gap:5px;font-size:12px;color:#92400e;line-height:1.4;">
                    </div>
                </div>
                <input type="file" id="media-input" name="media[]" multiple accept="image/*,video/mp4,video/quicktime">
            </div>
        </div>

        {{-- AI CAPTION --}}
        <div class="ai-section">
            <div class="ai-head">
                <div class="ai-head-left">
                    <div class="ai-icon">
                        <svg width="16" height="16" fill="none" stroke="#7c3aed" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                    </div>
                    <div class="ai-title-name">AI Caption Generator</div>
                    <span class="ai-badge">✦ Gemini AI</span>
                </div>
                <button type="button" class="ai-generate-btn" id="generate-btn" onclick="generateCaption()">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    Generate Caption
                </button>
            </div>
            <div class="ai-body">
                <p class="ai-hint">Fill in title &amp; description first, then click Generate.</p>
                <textarea class="ai-textarea" name="caption" id="caption-field"
                          placeholder="Your AI-generated caption will appear here. You can also write your own...">{{ old('caption') }}</textarea>
                <div class="ai-footer-preview">
                    <span class="ai-footer-preview__label">✦ Auto-added when you submit — not shown in the box above</span>
                    Apply now and secure your place for the upcoming academic year: https://onlineapp.nu-lipa.edu.ph/quest/register.php<br>
                    Experience 𝘌𝘥𝘶𝘤𝘢𝘵𝘪𝘰𝘯 𝘛𝘩𝘢𝘵 𝘞𝘰𝘳𝘬𝘴.<br>
                    #NULipa #EducationThatWorks
                </div>
            </div>
        </div>

        {{-- SUBMIT BAR --}}
        <div class="form-actions">
            <div class="form-actions__left">Fields marked <span>*</span> are required</div>
            <div class="actions-right">
                <button type="button" class="btn-cancel" onclick="history.back()">Cancel</button>
                <button type="submit" class="btn-submit" id="btn-submit-request">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                    Submit Request
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

@section('scripts')
<script>
// ── SLA POLICY MODAL ──────────────────────────────────────────────
(function() {
    const overlay   = document.getElementById('sla-overlay');
    const proceedBtn= document.getElementById('sla-proceed-btn');
    const dontShow  = document.getElementById('sla-dont-show');
    const KEY       = 'nupost_sla_dismissed';

    if (overlay && proceedBtn) {
        if (localStorage.getItem(KEY) === 'true') {
            overlay.style.display = 'none';
        } else {
            overlay.style.display = 'flex';
        }

        proceedBtn.addEventListener('click', function() {
            if (dontShow && dontShow.checked) {
                localStorage.setItem(KEY, 'true');
            }
            overlay.style.display = 'none';
        });
    }
})();

// ── SLA CATEGORY NOTES ────────────────────────────────────────────
const SLA_NOTES = {
    'Checking of Materials': 'SLA Turnaround: Up to 24 hours • Review and validation of ready marketing and informational materials.',
    'Posting with Ready-Made PubMat': 'SLA Turnaround: Up to 24 hours • Immediate scheduling of completed pubmats. Caption must be provided upon submission.',
    'Template-Based PubMat': 'SLA Turnaround: Up to 48 hours • Design generation using official NU Lipa pre-approved templates (e.g. announcements, congratulatory, news articles, partnerships).',
    'Standard PubMat': 'SLA Turnaround: 2–4 working days • Standard PubMat (2–4 days). Basic, non-templated promotional material created for a specific institutional event.',
    'Multiple Collaterals / Tarpaulins': 'SLA Turnaround: 5–10 working days • Tarpaulins, print collateral packages, badges, brochures, and composite signage.',
    'New Campaign / Creative Concept': 'SLA Turnaround: 10–20 working days • Comprehensive visual branding, campaign art direction, and theme conceptualization.',
    'Event Documentation': 'SLA Turnaround: 1 month prior to event • On-site coverage, photo/video documentation booking, and media team deployment. Event coverage requires 30 days notice.',
};

const catField     = document.getElementById('cat-field');
const slaNote      = document.getElementById('sla-inline-note');
const slaText      = document.getElementById('sla-inline-text');

function updateSlaNote() {
    const cat = catField.value;
    if (cat && SLA_NOTES[cat]) {
        slaText.textContent = SLA_NOTES[cat];
        slaNote.classList.add('visible');
    } else {
        slaNote.classList.remove('visible');
    }
}
catField.addEventListener('change', updateSlaNote);
// Show on load if category is pre-selected (old input)
if (catField.value) updateSlaNote();

// ── FIXED FOOTER ──────────────────────────────────────────────────
const FIXED_FOOTER = "\n\nApply now and secure your place for the upcoming academic year: https://onlineapp.nu-lipa.edu.ph/quest/register.php\nExperience \u{1D60C}\u{1D625}\u{1D636}\u{1D624}\u{1D622}\u{1D635}\u{1D62A}\u{1D630}\u{1D62F} \u{1D61B}\u{1D629}\u{1D622}\u{1D635} \u{1D61E}\u{1D630}\u{1D633}\u{1D62C}\u{1D634}.\n#NULipa\n#EducationThatWorks";

const createForm = document.getElementById('create-form');
const previewOverlay = document.getElementById('preview-overlay');
const btnClosePreview = document.getElementById('btn-close-preview');
const btnConfirmSubmit = document.getElementById('btn-confirm-submit');

function closePreviewModal() {
    previewOverlay.style.display = 'none';
}
if (btnClosePreview) {
    btnClosePreview.addEventListener('click', closePreviewModal);
}
previewOverlay.addEventListener('click', function(e) {
    if (e.target === previewOverlay) closePreviewModal();
});

createForm.addEventListener('submit', function(e) {
    // If submission is confirmed from modal, let it proceed
    if (createForm.dataset.confirmed === 'true') {
        return;
    }

    e.preventDefault();

    const submitBtn = this.querySelector('.btn-submit');
    if (submitBtn.dataset.submitted === 'true') {
        return;
    }

    // ── CLIENT-SIDE VALIDATION ──
    let hasError = false;

    // Title min 5
    const titleVal = document.getElementById('title-field').value.trim();
    const titleErr = document.getElementById('title-error');
    if (titleVal.length < 5) {
        titleErr.classList.add('visible');
        titleErr.closest('.field').classList.add('has-error');
        hasError = true;
    } else {
        titleErr.classList.remove('visible');
        titleErr.closest('.field').classList.remove('has-error');
    }

    // Category
    const catVal = document.getElementById('cat-field').value;
    if (!catVal) {
        alert('Please select a request category.');
        hasError = true;
    }

    // Priority
    const prioVal = document.getElementById('priority-field').value;
    if (!prioVal) {
        alert('Please select a priority level.');
        hasError = true;
    }

    // Preferred date
    const dateVal = document.getElementById('post-date-input').value;
    if (!dateVal) {
        alert('Please select a preferred post date.');
        hasError = true;
    }

    // Description min 15
    const descVal = document.getElementById('desc-field').value.trim();
    const descErr = document.getElementById('desc-error');
    if (descVal.length < 15) {
        descErr.classList.add('visible');
        descErr.closest('.field').classList.add('has-error');
        hasError = true;
    } else {
        descErr.classList.remove('visible');
        descErr.closest('.field').classList.remove('has-error');
    }

    // At least one platform
    const selectedPlatforms = Array.from(document.querySelectorAll('.platform-btn.selected')).map(b => b.dataset.platform);
    if (selectedPlatforms.length === 0) {
        hasError = true;
        alert('Please select at least one target platform.');
    }

    if (hasError) {
        return;
    }

    // Populate preview modal fields
    document.getElementById('pv-title').textContent = titleVal;
    document.getElementById('pv-category').textContent = catVal;

    const pvPrio = document.getElementById('pv-priority');
    pvPrio.textContent = prioVal;
    if (prioVal === 'High') {
        pvPrio.style.color = '#f87171';
    } else if (prioVal === 'Medium') {
        pvPrio.style.color = '#60a5fa';
    } else {
        pvPrio.style.color = '#94a3b8';
    }

    document.getElementById('pv-date').textContent = formatDateLabel(dateVal);
    document.getElementById('pv-platforms').textContent = selectedPlatforms.join(', ');
    document.getElementById('pv-desc').textContent = descVal;

    const captionVal = document.getElementById('caption-field').value.trim();
    const pvCaptionWrap = document.getElementById('pv-caption-wrap');
    if (captionVal) {
        pvCaptionWrap.style.display = 'block';
        document.getElementById('pv-caption').textContent = captionVal;
    } else {
        pvCaptionWrap.style.display = 'none';
    }

    const filesCount = mediaInput.files ? mediaInput.files.length : 0;
    document.getElementById('pv-media-count').textContent = filesCount > 0 ? `${filesCount} media file(s) attached` : 'No media attachments';

    // Show preview modal
    previewOverlay.style.display = 'flex';
});

btnConfirmSubmit.addEventListener('click', function() {
    const submitBtn = createForm.querySelector('.btn-submit');

    // Append fixed footer to caption
    const box = document.getElementById('caption-field');
    const current = box.value.trim();
    if (current && !current.includes('onlineapp.nu-lipa.edu.ph')) {
        box.value = current + FIXED_FOOTER;
    }

    btnConfirmSubmit.disabled = true;
    btnConfirmSubmit.innerHTML = `
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" 
             viewBox="0 0 24 24" style="animation:spin 1s linear infinite;display:inline-block;margin-right:6px;">
            <polyline points="23 4 23 10 17 10"/>
            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
        </svg> Submitting...`;

    submitBtn.dataset.submitted = 'true';
    submitBtn.disabled = true;
    submitBtn.style.opacity = '0.7';
    submitBtn.style.cursor = 'not-allowed';

    createForm.dataset.confirmed = 'true';
    createForm.submit();
});

// Reset submit button state on back/forward browser navigation (bfcache)
window.addEventListener('pageshow', function() {
    const btn = document.getElementById('btn-submit-request');
    if (btn) {
        btn.dataset.submitted = 'false';
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.style.cursor = 'pointer';
        btn.innerHTML = `
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Submit Request`;
    }
    createForm.dataset.confirmed = 'false';
    if (previewOverlay) previewOverlay.style.display = 'none';
});

// ── PLATFORM BUTTONS ──────────────────────────────────────────────
const platformInputs = document.getElementById('platform-inputs');
function updatePlatformInputs() {
    platformInputs.innerHTML = '';
    document.querySelectorAll('.platform-btn.selected').forEach(btn => {
        const inp = document.createElement('input');
        inp.type = 'hidden'; inp.name = 'platforms[]'; inp.value = btn.dataset.platform;
        platformInputs.appendChild(inp);
    });
}
document.querySelectorAll('.platform-btn').forEach(btn => {
    btn.addEventListener('click', () => { btn.classList.toggle('selected'); updatePlatformInputs(); });
});

// ── MEDIA PREVIEW ─────────────────────────────────────────────────
const mediaInput    = document.getElementById('media-input');
const uploadPreviews = document.getElementById('upload-previews');
const uploadZone    = document.getElementById('upload-zone');

function syncMediaUIState() {
    const files = mediaInput && mediaInput.files ? Array.from(mediaInput.files) : [];
    const hasImage = files.some(file => file.type && file.type.startsWith('image/'));
    const fillBtn = document.getElementById('btn-fill-description');
    if (fillBtn) {
        fillBtn.style.display = hasImage ? 'inline-flex' : 'none';
    }
    if (uploadZone) {
        uploadZone.style.display = files.length >= 10 ? 'none' : 'flex';
    }
}

if (mediaInput) {
    mediaInput.addEventListener('change', () => {
        uploadPreviews.innerHTML = '';
        const files = Array.from(mediaInput.files).slice(0, 10);
        files.forEach((file, idx) => {
            if (!file.type.startsWith('image/')) return;
            const reader = new FileReader();
            reader.onload = e => {
                const wrap = document.createElement('div'); wrap.className = 'preview-wrap';
                const img  = document.createElement('img'); img.src = e.target.result; img.className = 'preview-thumb';
                const rm   = document.createElement('button'); rm.type = 'button'; rm.className = 'preview-remove'; rm.innerHTML = '×';
                rm.onclick = () => {
                    wrap.remove();
                    if (uploadPreviews.querySelectorAll('.preview-wrap').length === 0) {
                        mediaInput.value = '';
                        syncMediaUIState();
                        if (typeof runPosterProofreader === 'function') runPosterProofreader();
                    }
                };
                wrap.appendChild(img); wrap.appendChild(rm);
                uploadPreviews.appendChild(wrap);
            };  
            reader.readAsDataURL(file);
        });
        syncMediaUIState();
    });
}

// Drag & drop support on uploadZone
if (uploadZone && mediaInput) {
    ['dragenter', 'dragover'].forEach(eventName => {
        uploadZone.addEventListener(eventName, e => {
            e.preventDefault();
            e.stopPropagation();
            uploadZone.style.borderColor = '#3b6ef5';
            uploadZone.style.background = '#eff6ff';
        }, false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
        uploadZone.addEventListener(eventName, e => {
            e.preventDefault();
            e.stopPropagation();
            uploadZone.style.borderColor = '';
            uploadZone.style.background = '';
        }, false);
    });
    uploadZone.addEventListener('drop', e => {
        const dt = e.dataTransfer;
        if (dt && dt.files && dt.files.length > 0) {
            mediaInput.files = dt.files;
            mediaInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
}

// ── SMART FILL DESCRIPTION FROM POSTER ────────────────────────────
async function triggerFillFromPoster() {
    const btn = document.getElementById('btn-fill-description');
    const descField = document.getElementById('desc-field');
    const titleField = document.getElementById('title-field');
    const catField = document.getElementById('cat-field');

    if (!mediaInput || !mediaInput.files || mediaInput.files.length === 0) {
        alert('Please attach a poster image first.');
        return;
    }

    const files = Array.from(mediaInput.files);
    const imageFile = files.find(f => f.type && f.type.startsWith('image/'));
    if (!imageFile) {
        alert('Please upload an image file (PNG/JPG/WEBP) to use Fill.');
        return;
    }

    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin 0.8s linear infinite;display:inline-block;"><circle cx="12" cy="12" r="10" stroke-dasharray="32" stroke-dashoffset="12"/></svg> Filling...`;

    try {
        const formData = new FormData();
        formData.append('image', imageFile);
        formData.append('title', titleField ? titleField.value.trim() : '');
        formData.append('description', descField ? descField.value.trim() : '');
        formData.append('category', catField ? catField.value : '');
        formData.append('_token', '{{ csrf_token() }}');

        const res = await fetch('/ai/fill-description', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const data = await res.json();
        if (!res.ok || !data.success) {
            throw new Error(data.message || 'AI could not read the poster image.');
        }

        if (descField && data.description) {
            descField.value = data.description;
            descField.dispatchEvent(new Event('input', { bubbles: true }));
            descField.dispatchEvent(new Event('change', { bubbles: true }));

            // Smooth scroll & pulse green highlight
            descField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            descField.classList.remove('field-highlight-pulse');
            void descField.offsetWidth; // trigger reflow
            descField.classList.add('field-highlight-pulse');
            setTimeout(() => descField.classList.remove('field-highlight-pulse'), 2500);

            // Re-run proofreader & update form progress
            if (typeof runPosterProofreader === 'function') runPosterProofreader();
            if (typeof updateProgress === 'function') updateProgress();
        }

    } catch (err) {
        alert('Could not fill description: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}

// ── CALENDAR LOAD INDICATOR ───────────────────────────────────────
const dateInput  = document.getElementById('post-date-input');
const calPanel   = document.getElementById('cal-load-panel');
const calLabel   = document.getElementById('cal-date-label');
const calMeta    = document.getElementById('cal-meta');
const calBody    = document.getElementById('cal-load-body');

const MONTH_NAMES = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const DAY_NAMES   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

function formatDateLabel(dateStr) {
    const [y, m, d] = dateStr.split('-').map(Number);
    const dt = new Date(y, m - 1, d);
    return `${DAY_NAMES[dt.getDay()]}, ${MONTH_NAMES[m - 1]} ${d}, ${y}`;
}

function getCapacityClass(count) {
    if (count === 0) return 'low';
    if (count <= 2)  return 'medium';
    return 'high';
}

function getCapacityWidth(count) {
    const pct = Math.min(count * 20, 100);
    return pct + '%';
}

function getCapacityLabel(count) {
    if (count === 0) return 'Open — no requests yet';
    if (count === 1) return 'Light — 1 request';
    if (count <= 2)  return `Moderate — ${count} requests`;
    return `Busy — ${count} requests`;
}

function renderCalPanel(data, dateStr) {
    const count   = data.total || 0;
    const reqs    = data.requests || [];
    const cls     = getCapacityClass(count);
    const width   = getCapacityWidth(count);
    const lbl     = getCapacityLabel(count);

    calLabel.textContent = formatDateLabel(dateStr);
    calMeta.textContent  = count === 0 ? 'Free day' : `${count} request${count !== 1 ? 's' : ''} scheduled`;

    let html = `
        <div class="cal-capacity-wrap">
            <div class="cal-capacity-label">
                <span>Schedule load</span>
                <strong class="${cls}">${lbl}</strong>
            </div>
            <div class="cal-bar-track">
                <div class="cal-bar-fill ${cls}" style="width:${width};"></div>
            </div>
        </div>`;

    if (reqs.length > 0) {
        html += `<div class="cal-req-list">`;
        reqs.slice(0, 5).forEach(r => {
            const statusRaw = (r.status || '').toLowerCase();
            let dotClass = 'pending';
            if (statusRaw.includes('approved')) dotClass = 'approved';
            else if (statusRaw.includes('review')) dotClass = 'under-review';
            const statusLabel = r.status || 'Pending';
            const title = r.title ? (r.title.length > 36 ? r.title.slice(0, 36) + '…' : r.title) : 'Untitled';
            html += `
                <div class="cal-req-item">
                    <div class="cal-req-dot ${dotClass}"></div>
                    <span class="cal-req-name">${escHtml(title)}</span>
                    <span class="cal-req-status">${escHtml(statusLabel)}</span>
                </div>`;
        });
        if (reqs.length > 5) {
            html += `<div style="font-size:11.5px;color:#9ca3af;text-align:center;padding:4px 0;">+${reqs.length - 5} more</div>`;
        }
        html += `</div>`;
    } else {
        html += `
            <div class="cal-empty-day">
                <svg width="28" height="28" fill="none" stroke="#d1fae5" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <div>
                    <strong>Great choice!</strong>
                    <p>No requests on this day yet.</p>
                </div>
            </div>`;
    }

    // Tip if busy
    if (count >= 3) {
        html += `<div class="cal-tip visible">⚠️ This day is quite busy. Consider choosing a nearby date for faster turnaround.</div>`;
    }

    calBody.innerHTML = html;
}

function escHtml(str) {
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

dateInput.addEventListener('change', function() {
    const val = this.value;
    if (!val) { calPanel.classList.remove('visible'); return; }

    // Show panel with loading state
    calPanel.classList.add('visible');
    calLabel.textContent = formatDateLabel(val);
    calMeta.textContent  = 'Checking schedule…';
    calBody.innerHTML    = `<div class="cal-loading"><div class="cal-spinner"></div> Loading schedule…</div>`;

    // Fetch requests for this date from the public calendar endpoint
    fetch(`/requestor/calendar/requests-by-date?date=${val}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(data => renderCalPanel(data, val))
    .catch(() => {
        calBody.innerHTML = `<div class="cal-loading" style="color:#ef4444;">Could not load schedule. Try again.</div>`;
    });
});

// Hide panel when date is cleared
dateInput.addEventListener('input', function() {
    if (!this.value) calPanel.classList.remove('visible');
});

// ── AI CAPTION GENERATOR ──────────────────────────────────────────
async function generateCaption() {
    const title = document.getElementById('title-field').value.trim();
    const desc  = document.getElementById('desc-field').value.trim();
    const cat   = document.getElementById('cat-field').value || 'General';
    const plats = Array.from(document.querySelectorAll('.platform-btn.selected'))
                       .map(b => b.dataset.platform).join(', ');
    const btn   = document.getElementById('generate-btn');
    const box   = document.getElementById('caption-field');

    if (!title || !desc) {
        alert('Please fill in the Title and Description first.');
        return;
    }

    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="animation:spin 1s linear infinite;display:inline-block;"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg> Generating...`;

    try {
        const res = await fetch('/api/generate-caption', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ title, description: desc, category: cat, platforms: plats })
        });

        const data = await res.json();
        if (data.error) throw new Error(data.error);
        if (!data.caption) throw new Error('No caption returned.');

        let caption = data.caption
            .replace(/#\w+/g, '')
            .replace(/Apply now[\s\S]*?register\.php/gi, '')
            .replace(/https?:\/\/onlineapp\.nu-lipa\.edu\.ph[^\s]*/gi, '')
            .replace(/Experience\s+\S*[Ee]ducation\S*\s+\S*[Tt]hat\S*\s+\S*[Ww]orks\S*/gi, '')
            .replace(/\n{3,}/g, '\n\n')
            .trim();

        box.value = caption;

    } catch(e) {
        alert('Caption generation failed: ' + e.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = orig;
    }
}

// ── PROGRESS BAR — update as user fills in fields ─────────────────
function updateProgress() {
    const title    = document.getElementById('title-field').value.trim();
    const desc     = document.getElementById('desc-field').value.trim();
    const cat      = document.getElementById('cat-field').value;
    const platform = document.querySelectorAll('.platform-btn.selected').length > 0;

    let filled = 0;
    if (title)    filled++;
    if (desc)     filled++;
    if (cat)      filled++;
    if (platform) filled++;

    const pct  = Math.round((filled / 4) * 100);
    const step = filled <= 1 ? 1 : filled <= 2 ? 2 : 3;
    const stepLabels = ['', 'Basic Info', 'Platforms', 'Ready to submit'];

    document.querySelector('.hero-progress__fill').style.width = pct + '%';
    document.querySelector('.hero-progress__label span:first-child').textContent =
        `Step ${step} of 3 — ${stepLabels[step]}`;
    document.querySelector('.hero-progress__label span:last-child').textContent = pct + '%';
}

document.getElementById('title-field').addEventListener('input', updateProgress);
document.getElementById('desc-field').addEventListener('input', updateProgress);
document.getElementById('cat-field').addEventListener('change', updateProgress);
document.querySelectorAll('.platform-btn').forEach(b => b.addEventListener('click', updateProgress));

// ── SMART CATEGORY SUGGESTER ─────────────────────────────────────
let hasManualCategoryOverride = false;
const catSuggestBadge = document.getElementById('cat-suggest-badge');
const catSuggestText = document.getElementById('cat-suggest-text');
const titleField = document.getElementById('title-field');

if (catField) {
    catField.addEventListener('change', function(e) {
        // If triggered programmatically with e.isAutoSuggest, don't set manual override
        if (!e.isAutoSuggest) {
            hasManualCategoryOverride = true;
            if (catSuggestBadge) catSuggestBadge.style.display = 'none';
        }
    });
}

let aiSuggestAbortCtrl = null;
function applyCategorySuggestion(catName, sourceLabel) {
    if (!catField || catField.value === catName) return;
    catField.value = catName;
    const evt = new Event('change', { bubbles: true });
    evt.isAutoSuggest = true;
    catField.dispatchEvent(evt);
    if (catSuggestBadge) {
        catSuggestBadge.style.display = 'flex';
        catSuggestText.textContent = `${sourceLabel}: "${catName}". (You can change this anytime)`;
    }
}

async function autoSuggestCategory(title) {
    if (hasManualCategoryOverride || !title || title.trim().length < 3 || !catField) return;
    const lower = title.toLowerCase();
    let suggested = null;

    if (/\b(docu|documentation|coverage|photo doc|livestream|recording|video coverage|photographer|videographer)\b/i.test(lower)) {
        suggested = 'Event Documentation';
    } else if (/\b(tarpaulin|tarp|banner|backdrop|standee|collaterals|billboard|streamer|signage)\b/i.test(lower)) {
        suggested = 'Multiple Collaterals / Tarpaulins';
    } else if (/\b(campaign|creative concept|advocacy|rebrand|series|movement|theme launch|awareness)\b/i.test(lower)) {
        suggested = 'New Campaign / Creative Concept';
    } else if (/\b(ready[- ]made|ready pubmat|ready to post|final pubmat|ready layout|post this)\b/i.test(lower)) {
        suggested = 'Posting with Ready-Made PubMat';
    } else if (/\b(template|congratulatory|announcement|quote of the day|greeting|greetings|holiday|congrats|passer|passers|board exam|welcome|orientation)\b/i.test(lower)) {
        suggested = 'Template-Based PubMat';
    } else if (/\b(check|review|proofing|validation|materials check|evaluation)\b/i.test(lower)) {
        suggested = 'Checking of Materials';
    }

    if (suggested) {
        applyCategorySuggestion(suggested, 'Suggested based on title');
        return;
    }

    // Micro-Gemini AI check if title has at least 5 chars and didn't match local keywords
    if (title.trim().length >= 5) {
        if (aiSuggestAbortCtrl) aiSuggestAbortCtrl.abort();
        aiSuggestAbortCtrl = new AbortController();
        try {
            const formData = new FormData();
            formData.append('title', title.trim());
            formData.append('_token', '{{ csrf_token() }}');

            const res = await fetch('/ai/suggest-category', {
                method: 'POST',
                body: formData,
                signal: aiSuggestAbortCtrl.signal,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            if (!res.ok) return;
            const data = await res.json();
            if (data.success && data.category && !hasManualCategoryOverride) {
                applyCategorySuggestion(data.category, '✦ AI Suggested');
            }
        } catch (_) {}
    }
}

let titleDebounceTimer = null;
if (titleField) {
    titleField.addEventListener('input', function() {
        clearTimeout(titleDebounceTimer);
        titleDebounceTimer = setTimeout(() => autoSuggestCategory(this.value), 350);
    });
}

// ── POSTER PROOFREADER ───────────────────────────────────────────
function runPosterProofreader() {
    const advisory = document.getElementById('proofread-advisory');
    const issuesList = document.getElementById('proofread-issues-list');
    if (!advisory || !issuesList) return;

    const files = mediaInput?.files;
    const prefDate = dateInput?.value;
    const title = titleField?.value || '';
    const desc = document.getElementById('desc-field')?.value || '';
    const combined = (title + ' ' + desc).toLowerCase();
    let localIssues = [];

    // 1. Zero-Token Local Date-Day Consistency Check
    if (prefDate) {
        const parts = prefDate.split('-').map(Number);
        if (parts.length === 3) {
            const dt = new Date(parts[0], parts[1] - 1, parts[2]);
            const days = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
            const actualDay = days[dt.getDay()];
            const actualDayCap = actualDay.charAt(0).toUpperCase() + actualDay.slice(1);

            days.forEach(day => {
                if (day !== actualDay) {
                    const short = day.slice(0, 3);
                    const reg = new RegExp('\\b(' + day + '|' + short + ')\\b', 'i');
                    if (reg.test(combined)) {
                        localIssues.push(`Calendar Tip: Preferred date (${prefDate}) is a ${actualDayCap}, but your text mentions ${day.charAt(0).toUpperCase() + day.slice(1)}. Please verify!`);
                    }
                }
            });
        }
    }

    // 2. Resolution Check on attached image
    if (files && files.length > 0 && files[0].type.startsWith('image/')) {
        const img = new Image();
        img.onload = function() {
            if (img.width < 600 || img.height < 600) {
                localIssues.push(`Resolution Note: Attached image is ${img.width}×${img.height}px. High resolution (at least 1080×1080px) is recommended for crisp social media feeds.`);
            }
            displayProofreadIssues(localIssues);
        };
        img.src = URL.createObjectURL(files[0]);
    } else {
        displayProofreadIssues(localIssues);
    }
}

function displayProofreadIssues(issues) {
    const advisory = document.getElementById('proofread-advisory');
    const issuesList = document.getElementById('proofread-issues-list');
    if (!advisory || !issuesList) return;

    if (issues && issues.length > 0) {
        issuesList.innerHTML = issues.map(iss => `<div>⚠️ ${escHtml(iss)}</div>`).join('');
        advisory.style.display = 'block';
    } else {
        advisory.style.display = 'none';
        issuesList.innerHTML = '';
    }
}

if (mediaInput) mediaInput.addEventListener('change', runPosterProofreader);
if (dateInput) dateInput.addEventListener('change', runPosterProofreader);
const descEl = document.getElementById('desc-field');
if (descEl) descEl.addEventListener('blur', runPosterProofreader);
</script>
@endsection