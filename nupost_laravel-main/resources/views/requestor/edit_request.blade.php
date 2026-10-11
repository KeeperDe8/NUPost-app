@extends('layouts.requestor')

@section('title', 'Edit Request')
@section('page-title', 'Edit Request')

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
    color: #fcd34d; background: rgba(252,211,77,0.12);
    border: 1px solid rgba(252,211,77,0.22);
    padding: 4px 12px; border-radius: 20px; margin-bottom: 14px;
    position: relative; z-index: 1;
}
.hero-card h1 {
    font-size: 26px; font-weight: 800; color: white;
    letter-spacing: -0.6px; margin-bottom: 8px; line-height: 1.15;
    position: relative; z-index: 1;
}
.hero-card h1 span { color: #fcd34d; }
.hero-card p {
    font-size: 12.5px; color: rgba(255,255,255,0.45);
    line-height: 1.65; max-width: 460px; position: relative; z-index: 1;
}
.hero-badge {
    display: inline-flex; align-items: center; gap: 8px;
    margin-top: 18px; padding: 9px 16px;
    background: rgba(252,211,77,0.1); border: 1px solid rgba(252,211,77,0.25);
    border-radius: 10px; font-size: 12px; color: #fcd34d;
    position: relative; z-index: 1;
    font-weight: 500;
}
.hero-badge svg { flex-shrink: 0; }

/* ── BREADCRUMB ─────────────────────── */
.breadcrumb-wrap {
    width: 100%; max-width: 680px; margin-bottom: 10px;
    display: flex; align-items: center; gap: 6px; font-size: 12.5px;
}
.breadcrumb-wrap a {
    color: #3b6ef5; text-decoration: none; display: flex; align-items: center; gap: 4px;
    font-weight: 600;
}
.breadcrumb-wrap a:hover { text-decoration: underline; }
.breadcrumb-sep { color: #9ca3af; }
.breadcrumb-cur { color: #6b7280; }

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

/* ── DATE PICKER ─────────────────────── */
.date-input-wrap { position: relative; }
.date-input-wrap input[type="date"] { padding-right: 44px; cursor: pointer; }
.date-cal-icon {
    position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
    color: #9ca3af; pointer-events: none;
}

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

/* ── MEDIA — CURRENT ────────────────── */
.current-media-label {
    font-size: 10.5px; font-weight: 700; color: #6b7280; text-transform: uppercase;
    letter-spacing: 0.4px; margin-bottom: 10px;
}
.current-media-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }
.current-media-item { position: relative; width: 82px; height: 82px; }
.current-media-item img {
    width: 82px; height: 82px; border-radius: 10px; object-fit: cover;
    border: 2px solid #a7f3d0; display: block;
}
.current-media-badge {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: rgba(16,185,129,0.85); color: white;
    font-size: 9px; font-weight: 700; text-align: center;
    padding: 3px; border-radius: 0 0 8px 8px; letter-spacing: 0.3px;
}
.current-media-divider {
    display: flex; align-items: center; gap: 10px; margin-bottom: 14px;
}
.current-media-divider span {
    font-size: 11px; color: #9ca3af; white-space: nowrap; font-weight: 500;
}
.current-media-divider::before,
.current-media-divider::after {
    content: ''; flex: 1; height: 1px; background: #e4e8f0;
}

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
    text-decoration: none; display: inline-flex; align-items: center;
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
[data-theme="dark"] .breadcrumb-wrap a {
    color: #60a5fa !important;
}
[data-theme="dark"] .breadcrumb-sep {
    color: #64748b !important;
}
[data-theme="dark"] .breadcrumb-cur {
    color: #94a3b8 !important;
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
[data-theme="dark"] .sla-inline-note {
    background: rgba(16, 185, 129, 0.12) !important;
    border-color: rgba(16, 185, 129, 0.3) !important;
    color: #6ee7b7 !important;
}
</style>
@endsection

@section('content')
<div class="create-page">

    {{-- BREADCRUMB --}}
    <div class="breadcrumb-wrap">
        <a href="{{ route('requestor.requests') }}">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            My Requests
        </a>
        <span class="breadcrumb-sep">/</span>
        <a href="{{ route('requestor.requests.show', $req->id) }}">{{ Str::limit($req->title, 30) }}</a>
        <span class="breadcrumb-sep">/</span>
        <span class="breadcrumb-cur">Edit</span>
    </div>

    {{-- HERO CARD --}}
    <div class="hero-card" style="width:100%;max-width:680px;">
        <div class="hero-eyebrow">
            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Editing Request
        </div>
        <h1>Update your <span>request</span></h1>
        <p>Make your changes below. Once an admin starts reviewing it, editing will be locked — so update now while you still can.</p>
        <div class="hero-badge">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Status: <strong>Pending Review</strong> — editing is still open
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert--error">❌ {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('requestor.requests.update', $req->id) }}" enctype="multipart/form-data" id="edit-form" class="form-body">
        @csrf

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
                    <input type="text" name="title" id="title-field"
                           placeholder="e.g., College Week 2025 Opening Ceremony"
                           value="{{ old('title', $req->title) }}" required minlength="5">
                    <div class="field-error" id="title-error">Title must be at least 5 characters.</div>
                </div>
                <div class="field">
                    <label>Description <span class="req">*</span></label>
                    <textarea name="description" id="desc-field" rows="4"
                              placeholder="Provide detailed information about your event or announcement..."
                              required minlength="15">{{ old('description', $req->description) }}</textarea>
                    <div class="field-error" id="desc-error">Description must be at least 15 characters.</div>
                </div>
                <div class="field-row">
                    <div class="field" style="margin-bottom:0;">
                        <label>Category <span class="req">*</span></label>
                        <select name="category" id="cat-field" required>
                            <option value="" disabled>Select category</option>
                            @foreach(['Checking of Materials','Posting with Ready-Made PubMat','Template-Based PubMat','Standard PubMat','Multiple Collaterals / Tarpaulins','New Campaign / Creative Concept','Event Documentation'] as $cat)
                                <option value="{{ $cat }}"
                                    {{ old('category', $req->category) === $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field" style="margin-bottom:0;">
                        <label>Priority <span class="req">*</span></label>
                        <select name="priority" required>
                            <option value="" disabled>Select priority</option>
                            @foreach(['Low','Medium','High','Urgent'] as $pri)
                                <option value="{{ $pri }}"
                                    {{ old('priority', $req->priority) === $pri ? 'selected' : '' }}>
                                    {{ $pri }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- SLA TURNAROUND NOTE --}}
                <div class="sla-inline-note" id="sla-inline-note">
                    <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span id="sla-inline-text"></span>
                </div>

                <div class="field" style="margin-top:16px;margin-bottom:0;">
                    <label>Preferred Post Date <span class="req">*</span></label>
                    <div class="date-input-wrap">
                        <input type="date" name="post_date" id="post-date-input"
                               value="{{ old('post_date', $req->preferred_date ? \Carbon\Carbon::parse($req->preferred_date)->format('Y-m-d') : '') }}" required>
                        <span class="date-cal-icon">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2.5"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        </span>
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
                @php
                    $selected_platforms = old('platforms', $req->platforms_array ?? []);
                @endphp
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
            <div class="section-head">
                <div class="section-num">3</div>
                <div>
                    <div class="section-title-txt">Media Attachments</div>
                    <div class="section-sub">Up to 10 images · Max 10MB each</div>
                </div>
            </div>
            <div class="section-body">

                {{-- Current media --}}
                @if($req->media_files && count($req->media_files) > 0)
                    <div class="current-media-label">Current media</div>
                    <div class="current-media-grid">
                        @foreach($req->media_files as $mf)
                            <div class="current-media-item">
                                <img src="/uploads/{{ $mf }}" alt="{{ $mf }}"
                                     onerror="this.src='';this.style.background='#e5e7eb';">
                                <div class="current-media-badge">SAVED</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="current-media-divider">
                        <span>Upload new files to replace existing ones</span>
                    </div>
                @endif

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
                    Re-generate Caption
                </button>
            </div>
            <div class="ai-body">
                <p class="ai-hint">Update title &amp; description first, then click Re-generate for a fresh caption.</p>
                <textarea class="ai-textarea" name="caption" id="caption-field"
                          placeholder="Your AI-generated caption will appear here. You can also write your own...">{{ old('caption', $req->caption) }}</textarea>
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
                <a href="{{ route('requestor.requests') }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Changes
                </button>
            </div>
        </div>

    </form>
</div>
@endsection

@section('scripts')
<script>
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

const catField = document.getElementById('cat-field');
const slaNote  = document.getElementById('sla-inline-note');
const slaText  = document.getElementById('sla-inline-text');

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
// Show on load if category is pre-selected
if (catField.value) updateSlaNote();

// ── FIXED FOOTER ──────────────────────────────────────────────────
const FIXED_FOOTER = "\n\nApply now and secure your place for the upcoming academic year: https://onlineapp.nu-lipa.edu.ph/quest/register.php\nExperience \u{1D60C}\u{1D625}\u{1D636}\u{1D624}\u{1D622}\u{1D635}\u{1D62A}\u{1D630}\u{1D62F} \u{1D61B}\u{1D629}\u{1D622}\u{1D635} \u{1D61E}\u{1D630}\u{1D633}\u{1D62C}\u{1D634}.\n#NULipa\n#EducationThatWorks";

document.getElementById('edit-form').addEventListener('submit', function(e) {
    // ── CLIENT-SIDE VALIDATION ──
    let hasError = false;

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

    const platforms = document.querySelectorAll('.platform-btn.selected');
    if (platforms.length === 0) {
        hasError = true;
        alert('Please select at least one target platform.');
    }

    if (hasError) {
        e.preventDefault();
        return;
    }

    const box     = document.getElementById('caption-field');
    const current = box.value.trim();
    if (current && !current.includes('onlineapp.nu-lipa.edu.ph')) {
        box.value = current + FIXED_FOOTER;
    }
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
const mediaInput     = document.getElementById('media-input');
const uploadPreviews = document.getElementById('upload-previews');
const uploadZone     = document.getElementById('upload-zone');

mediaInput.addEventListener('change', () => {
    uploadPreviews.innerHTML = '';
    const files = Array.from(mediaInput.files).slice(0, 10);
    files.forEach(file => {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = e => {
            const wrap = document.createElement('div'); wrap.className = 'preview-wrap';
            const img  = document.createElement('img'); img.src = e.target.result; img.className = 'preview-thumb';
            const rm   = document.createElement('button'); rm.type = 'button'; rm.className = 'preview-remove'; rm.innerHTML = '×';
            rm.onclick = () => wrap.remove();
            wrap.appendChild(img); wrap.appendChild(rm);
            uploadPreviews.appendChild(wrap);
        };
        reader.readAsDataURL(file);
    });
    uploadZone.style.display = files.length >= 10 ? 'none' : 'flex';
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
</script>
@endsection