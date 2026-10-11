@extends('layouts.requestor')

@section('title', 'Request Tracking')
@section('page-title', 'Request Tracking')

@section('head-styles')
<style>
/* ── BASE ── */
.trk { 
    padding: 24px 40px 48px; /* Increased side padding for better balance */
    width: 100%; /* Removed max-width to eliminate empty right side */
}

/* BACK */
.trk-back {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 12.5px; font-weight: 500; color: #64748b;
    text-decoration: none; margin-bottom: 16px;
    transition: color .15s;
}
.trk-back:hover { color: #334155; }

/* PAGE HEADER */
.trk-req-pill {
    display: inline-flex; align-items: center;
    font-size: 10.5px; font-weight: 700; letter-spacing: 0.8px;
    color: #3b82f6; background: #eff6ff;
    border: 1px solid #bfdbfe; border-radius: 20px;
    padding: 3px 10px; margin-bottom: 8px;
}
.trk-title {
    font-size: 28px; font-weight: 800; /* Slightly larger title */
    color: #0f172a; letter-spacing: -0.5px; margin-bottom: 4px;
}
.trk-sub { font-size: 13px; color: #94a3b8; }

.trk-header-row {
    display: flex; align-items: flex-end; /* Align items to the bottom */
    justify-content: space-between; gap: 16px;
    flex-wrap: wrap; margin-bottom: 24px;
}
.trk-header-actions { display: flex; gap: 8px; flex-wrap: wrap; flex-shrink: 0; padding-bottom: 4px; }
.btn-edit {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 9px;
    border: 1px solid #fde68a; background: #fefce8;
    font-size: 12.5px; font-weight: 600; color: #92400e;
    text-decoration: none; transition: all .15s;
}
.btn-edit:hover { background: #fef3c7; }
.btn-edit svg, .btn-delete svg { width: 13px; height: 13px; flex-shrink: 0; }
.btn-delete {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 9px;
    border: 1px solid #fecaca; background: #fff5f5;
    font-size: 12.5px; font-weight: 600; color: #991b1b;
    cursor: pointer; font-family: inherit; transition: all .15s;
}
.btn-delete:hover { background: #fee2e2; }

/* ── STATUS HERO ── */
.status-hero {
    border-radius: 20px;
    background: linear-gradient(135deg, #001a4d 0%, #002877 50%, #003399 100%);
    padding: 32px 40px; /* Slightly more spacious hero */
    margin-bottom: 24px;
    position: relative; overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,26,77,0.22);
}
.status-hero::after {
    content: '';
    position: absolute; top: -70px; right: -70px;
    width: 300px; height: 300px; border-radius: 50%;
    background: radial-gradient(circle, rgba(99,179,237,0.12) 0%, transparent 70%);
    pointer-events: none;
}
.hero-top { display: flex; align-items: center; gap: 20px; margin-bottom: 32px; }
.hero-icon {
    width: 56px; height: 56px; border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.hero-icon svg { width: 24px; height: 24px; }
.hero-icon--pending      { background: rgba(148,163,184,0.18); border: 1.5px solid rgba(148,163,184,0.3);  color: #94a3b8; }
.hero-icon--under-review { background: rgba(251,191,36,0.18);  border: 1.5px solid rgba(251,191,36,0.35);  color: #fbbf24; }
.hero-icon--approved     { background: rgba(52,211,153,0.18);  border: 1.5px solid rgba(52,211,153,0.35);  color: #34d399; }
.hero-icon--posted       { background: rgba(167,139,250,0.18); border: 1.5px solid rgba(167,139,250,0.35); color: #a78bfa; }
.hero-icon--rejected     { background: rgba(252,165,165,0.18); border: 1.5px solid rgba(252,165,165,0.35); color: #fca5a5; }

.hero-label {
    font-size: 11px; font-weight: 700; letter-spacing: 1.2px;
    text-transform: uppercase; color: rgba(255,255,255,0.4); margin-bottom: 6px;
}
.hero-status {
    font-size: 36px; font-weight: 800; color: white;
    letter-spacing: -0.8px; line-height: 1; margin-bottom: 8px;
}
.hero-desc { font-size: 14px; color: rgba(255,255,255,0.5); }

.hero-divider { height: 1px; background: rgba(255,255,255,0.08); margin-bottom: 24px; }
.prog-label {
    font-size: 10px; font-weight: 700; letter-spacing: 1px;
    text-transform: uppercase; color: rgba(255,255,255,0.3); margin-bottom: 20px;
}
.prog-steps { display: flex; align-items: flex-start; max-width: 900px; margin: 0 auto; }
.prog-step { flex: 1; display: flex; flex-direction: column; align-items: center; position: relative; z-index: 1; }
.prog-connector {
    flex: 1; height: 2px; background: rgba(255,255,255,0.1);
    margin-top: 19px; position: relative; z-index: 0;
}
.prog-connector--done { background: rgba(96,165,250,0.7); }
.prog-circle {
    width: 38px; height: 38px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid rgba(255,255,255,0.15);
    background: rgba(255,255,255,0.05);
    color: rgba(255,255,255,0.25);
    font-size: 13px; font-weight: 700;
    margin-bottom: 10px; transition: all .3s;
}
.prog-circle svg { width: 14px; height: 14px; }
.prog-circle--done {
    background: #3b82f6; border-color: #60a5fa; color: white;
    box-shadow: 0 0 0 4px rgba(59,130,246,0.2);
}
.prog-circle--active {
    background: rgba(59,130,246,0.15); border-color: #60a5fa; color: #93c5fd;
    box-shadow: 0 0 0 4px rgba(59,130,246,0.15);
    animation: prog-pulse 2s infinite;
}
@keyframes prog-pulse {
    0%,100% { box-shadow: 0 0 0 4px rgba(59,130,246,0.15); }
    50%      { box-shadow: 0 0 0 8px rgba(59,130,246,0.07); }
}
.prog-name { font-size: 11px; font-weight: 600; text-align: center; color: rgba(255,255,255,0.25); }
.prog-name--done   { color: rgba(255,255,255,0.65); }
.prog-name--active { color: #93c5fd; }

/* ── GRID ── */
.trk-grid { 
    display: grid; 
    grid-template-columns: 2fr 1fr; /* 2/3 and 1/3 layout to use space better */
    gap: 24px; 
}
.trk-grid--full { grid-column: 1 / -1; }
.trk-col-stack { display: flex; flex-direction: column; gap: 24px; }

@media(max-width:1024px) { .trk-grid { grid-template-columns: 1fr; } }

.trk-card {
    background: white; border: 1px solid #e2e8f0;
    border-radius: 16px; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
    height: fit-content;
}
.trk-card-head {
    padding: 14px 20px;
    border-bottom: 1px solid #f1f5f9;
    display: flex; align-items: center; gap: 10px;
}
.trk-card-icon {
    width: 30px; height: 30px; border-radius: 8px;
    background: #f8fafc; border: 1px solid #e2e8f0;
    display: flex; align-items: center; justify-content: center;
    color: #64748b; flex-shrink: 0;
}
.trk-card-icon svg { width: 14px; height: 14px; }
.trk-card-title {
    font-size: 11.5px; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.7px; color: #94a3b8;
}

/* DETAILS */
.detail-rows { padding: 16px 20px; display: flex; flex-direction: column; gap: 13px; }
.detail-row  { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.detail-key  { font-size: 12.5px; color: #94a3b8; flex-shrink: 0; }
.detail-val  { font-size: 12.5px; font-weight: 600; color: #1e293b; text-align: right; }
.detail-desc { padding: 12px 20px 16px; border-top: 1px solid #f1f5f9; font-size: 13px; color: #64748b; line-height: 1.65; }

/* BADGES */
.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 10.5px; font-weight: 700; white-space: nowrap; }
.badge--approved     { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.badge--posted       { background: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; }
.badge--under-review { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.badge--pending      { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.badge--rejected     { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.badge--urgent       { background: #fff7ed; color: #9a3412; border: 1px solid #fed7aa; }
.badge--high         { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.badge--medium       { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.badge--low          { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }

/* MEDIA */
.media-thumbs { display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px; padding: 14px 20px; }
.media-thumb {
    width: 100%; aspect-ratio: 1; border-radius: 10px;
    object-fit: cover; border: 1px solid #e2e8f0;
    cursor: pointer; transition: transform .15s, box-shadow .15s;
}
.media-thumb:hover { transform: scale(1.04); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.media-video-thumb {
    width: 100%; aspect-ratio: 1; border-radius: 10px;
    background: #f1f5f9; border: 1px solid #e2e8f0;
    display: flex; align-items: center; justify-content: center; color: #94a3b8;
}
.media-video-thumb svg { width: 22px; height: 22px; }
.media-none { padding: 16px 20px; font-size: 12.5px; color: #cbd5e1; }
.caption-box { padding: 16px 20px 20px; border-top: 1px solid #f1f5f9; background: #fafcff; }
.caption-label { font-size: 10px; font-weight: 700; letter-spacing: 0.7px; text-transform: uppercase; color: #94a3b8; margin-bottom: 8px; }
.caption-text  { font-size: 13px; color: #334155; line-height: 1.6; }

/* TIMELINE */
.timeline { padding: 16px 20px; display: flex; flex-direction: column; }
.tl-item { display: flex; gap: 12px; padding-bottom: 18px; }
.tl-item:last-child { padding-bottom: 0; }
.tl-left { display: flex; flex-direction: column; align-items: center; flex-shrink: 0; }
.tl-dot {
    width: 32px; height: 32px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; border: 1.5px solid transparent;
}
.tl-dot svg { width: 13px; height: 13px; }
.tl-dot--submit { background: #eff6ff; border-color: #bfdbfe; color: #3b82f6; }
.tl-dot--status { background: #f0fdf4; border-color: #bbf7d0; color: #16a34a; }
.tl-dot--update { background: #fffbeb; border-color: #fde68a; color: #d97706; }
.tl-dot--chat   { background: #faf5ff; border-color: #e9d5ff; color: #7c3aed; }
.tl-dot--note   { background: #f8fafc; border-color: #e2e8f0; color: #64748b; }
.tl-line { width: 1.5px; flex: 1; min-height: 12px; background: #e2e8f0; margin: 3px 0; }
.tl-item:last-child .tl-line { display: none; }
.tl-body { flex: 1; padding-top: 4px; }
.tl-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 2px; }
.tl-event  { font-size: 13px; font-weight: 700; color: #1e293b; }
.tl-latest {
    font-size: 9.5px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;
    background: #eff6ff; color: #3b82f6; border: 1px solid #bfdbfe;
    padding: 2px 8px; border-radius: 20px;
}
.tl-actor { font-size: 12px; color: #64748b; margin-bottom: 3px; }
.tl-time  { font-size: 11px; color: #94a3b8; display: flex; align-items: center; gap: 4px; }
.tl-time svg { width: 10px; height: 10px; }

/* CHAT CTA */
.chat-cta {
    padding: 24px 20px;
    display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
}
.chat-cta-left { display: flex; align-items: center; gap: 12px; }
.chat-cta-avatar {
    width: 48px; height: 48px; border-radius: 50%;
    background: linear-gradient(135deg, #001a4d, #0038a8);
    display: flex; align-items: center; justify-content: center;
    color: white; flex-shrink: 0;
}
.chat-cta-avatar svg { width: 20px; height: 20px; }
.chat-cta-name { font-size: 15px; font-weight: 700; color: #1e293b; }
.chat-cta-sub  { font-size: 12px; color: #94a3b8; margin-top: 1px; }

.btn-open-chat {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 12px 24px; border-radius: 12px;
    background: linear-gradient(135deg, #002877, #0038a8);
    color: white; font-size: 13px; font-weight: 600;
    text-decoration: none; white-space: nowrap; flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(0,40,119,0.25);
    transition: opacity .15s, transform .1s;
}
.btn-open-chat:hover { opacity: 0.88; transform: translateY(-1px); color: white; }

/* ── MODAL OVERLAY ── */
.modal-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(15, 23, 42, 0.6); /* Slate-900 with transparency */
    backdrop-filter: blur(4px); /* Modern frosted glass effect */
    display: flex; align-items: center; justify-content: center;
    z-index: 9999; opacity: 0; pointer-events: none;
    transition: all 0.2s ease-out;
}
.modal-overlay.open { opacity: 1; pointer-events: auto; }

/* ── MODAL BOX ── */
.modal {
    background: white; width: 100%; max-width: 400px;
    padding: 32px; border-radius: 20px;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    transform: scale(0.95); transition: transform 0.2s ease-out;
    text-align: center;
}
.modal-overlay.open .modal { transform: scale(1); }

.modal h3 {
    font-size: 20px; font-weight: 800; color: #0f172a;
    margin-bottom: 12px; letter-spacing: -0.5px;
}
.modal p {
    font-size: 14px; color: #64748b; line-height: 1.6;
    margin-bottom: 28px;
}

/* ── MODAL ACTIONS ── */
.modal-actions {
    display: flex; flex-direction: column; gap: 10px;
}

.modal-confirm {
    width: 100%; padding: 12px; border-radius: 12px;
    background: #ef4444; color: white; border: none;
    font-size: 14px; font-weight: 700; cursor: pointer;
    transition: background 0.15s;
}
.modal-confirm:hover { background: #dc2626; }

.modal-cancel {
    width: 100%; padding: 12px; border-radius: 12px;
    background: #f1f5f9; color: #475569; border: none;
    font-size: 14px; font-weight: 600; cursor: pointer;
    transition: background 0.15s;
}
.modal-cancel:hover { background: #e2e8f0; }

/* ── DARK THEME OVERRIDES ── */
[data-theme="dark"] .trk-title {
    color: #f8fafc !important;
}
[data-theme="dark"] .trk-card {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
}
[data-theme="dark"] .trk-card-head {
    background: #06112a !important;
    border-bottom-color: rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .trk-card-icon {
    background: #0b1a3d !important;
    border-color: rgba(30, 79, 216, 0.3) !important;
    color: #94a3b8 !important;
}
[data-theme="dark"] .trk-card-title {
    color: #94a3b8 !important;
}
[data-theme="dark"] .detail-val {
    color: #f8fafc !important;
}
[data-theme="dark"] .detail-desc {
    color: #cbd5e1 !important;
    border-top-color: rgba(30, 79, 216, 0.2) !important;
}
[data-theme="dark"] .caption-box {
    background: #06112a !important;
    border-top-color: rgba(30, 79, 216, 0.2) !important;
}
[data-theme="dark"] .caption-text {
    color: #cbd5e1 !important;
}
[data-theme="dark"] .tl-event {
    color: #f8fafc !important;
}
[data-theme="dark"] .tl-line {
    background: rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .tl-dot--note {
    background: #06112a !important;
    border-color: rgba(30, 79, 216, 0.3) !important;
    color: #94a3b8 !important;
}
[data-theme="dark"] .chat-cta-name {
    color: #f8fafc !important;
}
[data-theme="dark"] .media-thumb {
    border-color: rgba(30, 79, 216, 0.3) !important;
}
[data-theme="dark"] .media-video-thumb {
    background: #0b1a3d !important;
    border-color: rgba(30, 79, 216, 0.3) !important;
}
[data-theme="dark"] .modal {
    background: #081636 !important;
    border: 1px solid rgba(30, 79, 216, 0.3) !important;
}
[data-theme="dark"] .modal h3 {
    color: #f8fafc !important;
}
[data-theme="dark"] .modal p {
    color: #94a3b8 !important;
}
[data-theme="dark"] .modal-cancel {
    background: #0b1a3d !important;
    color: #cbd5e1 !important;
}
[data-theme="dark"] .modal-cancel:hover {
    background: #0f2250 !important;
    color: #f8fafc !important;
}
</style>
@endsection

@section('content')
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
    $status_desc = match($status_class) {
        'approved'     => 'Your request has been approved and is ready to post.',
        'posted'       => 'Your content has been published successfully.',
        'under-review' => 'Admin is currently reviewing your request.',
        'rejected'     => 'Your request was not approved. Check comments for details.',
        default        => 'Your request is currently queued for review.',
    };
    $step_index = match($status_class) {
        'under-review' => 2, 'approved' => 3, 'posted' => 4, 'rejected' => 4, default => 1,
    };
    $steps = [['label'=>'Submitted'],['label'=>'In Review'],['label'=>'Approved'],['label'=>'Posted']];
    $is_pending  = $req->status === 'Pending Review';
    $media_files = $req->media_file ? array_filter(array_map('trim', explode(',', $req->media_file))) : [];
    $msg_count   = $chat_messages->count();
    $last_msg    = $chat_messages->last();
@endphp

<div class="trk">

    <a href="{{ route('requestor.requests') }}" class="trk-back">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        Back to My Requests
    </a>

    {{-- HEADER --}}
    <div class="trk-header-row">
        <div>
            <div class="trk-req-pill">{{ $req->request_id ?? 'REQ-' . str_pad($req->id, 5, '0', STR_PAD_LEFT) }}</div>
            <div class="trk-title">{{ $req->title }}</div>
            <div class="trk-sub">{{ $req->category }} &middot; Submitted {{ $req->created_at->format('M j, Y') }}</div>
        </div>
        @if($is_pending)
        <div class="trk-header-actions">
            <a href="{{ route('requestor.requests.edit', $req->id) }}" class="btn-edit">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Request
            </a>
            <button class="btn-delete" onclick="document.getElementById('del-modal').classList.add('open')">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                Delete
            </button>
        </div>
        @endif
    </div>

    {{-- STATUS HERO --}}
    <div class="status-hero">
        <div class="hero-top">
            <div class="hero-icon hero-icon--{{ $status_class }}">
                @if($status_class === 'pending')
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                @elseif($status_class === 'under-review')
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                @elseif($status_class === 'approved')
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                @elseif($status_class === 'posted')
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2L15 22l-4-9-9-4 20-7z"/></svg>
                @else
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                @endif
            </div>
            <div>
                <div class="hero-label">Current Status</div>
                <div class="hero-status">{{ $req->status }}</div>
                <div class="hero-desc">{{ $status_desc }}</div>
            </div>
        </div>

        <div class="hero-divider"></div>
        <div class="prog-label">Progress Tracking</div>
        <div class="prog-steps">
            @foreach($steps as $i => $step)
                @php
                    $num = $i + 1;
                    $is_done   = $num < $step_index;
                    $is_active = $num === $step_index;
                @endphp
                <div class="prog-step">
                    <div class="prog-circle {{ $is_done ? 'prog-circle--done' : ($is_active ? 'prog-circle--active' : '') }}">
                        @if($is_done)
                            <svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        @elseif($is_active)
                            <div style="width:9px;height:9px;border-radius:50%;background:currentColor;"></div>
                        @else
                            {{ $num }}
                        @endif
                    </div>
                    <div class="prog-name {{ $is_done ? 'prog-name--done' : ($is_active ? 'prog-name--active' : '') }}">{{ $step['label'] }}</div>
                </div>
                @if($i < count($steps) - 1)
                    <div class="prog-connector {{ $num < $step_index ? 'prog-connector--done' : '' }}"></div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="trk-grid">
        
        {{-- LEFT COLUMN: Details & Media --}}
        <div class="trk-col-stack">
            {{-- REQUEST DETAILS --}}
            <div class="trk-card">
                <div class="trk-card-head">
                    <div class="trk-card-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </div>
                    <div class="trk-card-title">General Information</div>
                </div>
                <div class="detail-rows">
                    <div class="detail-row">
                        <span class="detail-key">Category</span>
                        <span class="detail-val">{{ $req->category }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-key">Priority</span>
                        <span class="detail-val"><span class="badge badge--{{ $priority_class }}">{{ strtoupper($req->priority) }}</span></span>
                    </div>
                    @if($req->platform)
                    <div class="detail-row">
                        <span class="detail-key">Target Platform</span>
                        <span class="detail-val">{{ $req->platform }}</span>
                    </div>
                    @endif
                    @if($req->preferred_date)
                    <div class="detail-row">
                        <span class="detail-key">Target Posting Date</span>
                        <span class="detail-val">{{ \Carbon\Carbon::parse($req->preferred_date)->format('M j, Y') }}</span>
                    </div>
                    @endif
                </div>
                @if($req->description)
                <div class="detail-desc">
                    <div style="font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px;">Description</div>
                    {{ $req->description }}
                </div>
                @endif
            </div>

            {{-- ATTACHED MEDIA --}}
            <div class="trk-card">
                <div class="trk-card-head">
                    <div class="trk-card-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    </div>
                    <div class="trk-card-title">Content & Media</div>
                </div>
                @if(count($media_files) > 0)
                    <div class="media-thumbs">
                        @foreach($media_files as $file)
                            @php $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION)); @endphp
                            @if(in_array($ext, ['jpg','jpeg','png','gif','webp','jfif','svg']))
                                <img class="media-thumb" src="/uploads/{{ $file }}" alt="media" onerror="this.style.opacity=0.2">
                            @else
                                <div class="media-video-thumb">
                                    <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="media-none">No media files attached to this request.</div>
                @endif
                @if($req->caption)
                <div class="caption-box">
                    <div class="caption-label">Draft Caption</div>
                    <div class="caption-text">{{ $req->caption }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- RIGHT COLUMN: Timeline & Chat --}}
        <div class="trk-col-stack">
            {{-- ACTIVITY TIMELINE --}}
            <div class="trk-card">
                <div class="trk-card-head">
                    <div class="trk-card-icon">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <div class="trk-card-title">Activity</div>
                </div>
                @if($activities->count() > 0)
                <div class="timeline">
                    @foreach($activities as $i => $act)
                        @php
                            $al = strtolower($act->action);
                            $dc = 'tl-dot--note';
                            $di = '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
                            
                            if (str_contains($al,'submitted') || str_contains($al,'created')) {
                                $dc = 'tl-dot--submit';
                                $di = '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>';
                            } elseif (str_contains($al,'approved')||str_contains($al,'posted')||str_contains($al,'rejected')||str_contains($al,'status')) {
                                $dc = 'tl-dot--status';
                                $di = '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
                            } elseif (str_contains($al,'update')||str_contains($al,'edit')) {
                                $dc = 'tl-dot--update';
                                $di = '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
                            } elseif (str_contains($al,'message')||str_contains($al,'chat')||str_contains($al,'comment')) {
                                $dc = 'tl-dot--chat';
                                $di = '<svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>';
                            }
                        @endphp
                        <div class="tl-item">
                            <div class="tl-left">
                                <div class="tl-dot {{ $dc }}">{!! $di !!}</div>
                                <div class="tl-line"></div>
                            </div>
                            <div class="tl-body">
                                <div class="tl-top">
                                    <div class="tl-event">{{ ucfirst($act->action) }}</div>
                                    @if($i === 0)<span class="tl-latest">Latest</span>@endif
                                </div>
                                <div class="tl-actor">by {{ $act->actor }}</div>
                                <div class="tl-time">
                                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    {{ $act->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @else
                    <div class="tl-empty">No activity recorded yet.</div>
                @endif
            </div>

{{-- COMMENTS / CHAT --}}
<div class="trk-card">
    <div class="trk-card-head">
        <div class="trk-card-icon">
            {{-- In-update ang icon para mas mukhang messaging --}}
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
            </svg>
        </div>
        <div class="trk-card-title">Admin Chat</div>
    </div>
    <div class="chat-cta">
        <div class="chat-cta-left">
            <div class="chat-cta-avatar">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            <div>
                <div class="chat-cta-name">Admin Support</div>
                <div class="chat-cta-sub">Direct message for this request</div>
            </div>
        </div>
        {{-- Siguraduhing ang $req ay ang variable na gamit mo sa loop/view --}}
        <a href="{{ route('requestor.requests.chat', $req->id) }}" class="btn-open-chat">
            Open Chat Box
        </a>
    </div>
</div>

{{-- DELETE MODAL --}}
<div class="modal-overlay" id="del-modal">
    <div class="modal">
        <h3>Delete Request</h3>
        <p>Are you sure you want to delete <strong>"{{ $req->title }}"</strong>? This action cannot be reversed.</p>
        <div class="modal-actions">
            <button class="modal-cancel" onclick="document.getElementById('del-modal').classList.remove('open')">Keep Request</button>
            <form method="POST" action="{{ route('requestor.requests.destroy', $req->id) }}" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="modal-confirm">Confirm Delete</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function initTrackingModal() {
    const modal = document.getElementById('del-modal');
    if (modal && !modal._bound) {
        modal._bound = true;
        modal.addEventListener('click', function(e) {
            if (e.target === this) this.classList.remove('open');
        });
    }
}
document.addEventListener('DOMContentLoaded', initTrackingModal);
</script>
@endsection