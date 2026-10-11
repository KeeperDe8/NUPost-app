@extends('layouts.admin')
@section('title', 'Request Details')

@section('head-styles')
<style>
/* ── FORCE FONT ──────────────────────── */
@import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display&display=swap');

/* ── HERO ────────────────────────────── */
.req-hero {
    background: linear-gradient(135deg, #001a4d 0%, #002e7a 55%, #003a8c 100%) !important;
    border-radius: 22px; padding: 28px 32px;
    margin-bottom: 22px; position: relative; overflow: hidden;
    box-shadow: 0 8px 32px rgba(0,26,77,0.35);
    isolation: isolate;
}
.req-hero::before {
    content:''; position:absolute; top:-50px; right:-50px;
    width:260px; height:260px; border-radius:50%;
    background:radial-gradient(circle, rgba(59,130,246,0.18) 0%, transparent 65%);
    pointer-events:none;
}
.req-hero::after {
    content:''; position:absolute; bottom:-30px; left:30%;
    width:180px; height:180px; border-radius:50%;
    background:radial-gradient(circle, rgba(245,158,11,0.1) 0%, transparent 65%);
    pointer-events:none;
}
.req-hero__inner { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; position:relative; z-index:1; }
.req-hero__id    { font-size:11px; color:rgba(255,255,255,0.5) !important; font-weight:600; letter-spacing:0.5px; margin-bottom:8px; }
.req-hero__title { font-family:'DM Serif Display', var(--font-disp), Georgia, serif !important; font-size:26px; color:white !important; line-height:1.25; margin-bottom:14px; }
.req-hero__chips { display:flex; gap:8px; flex-wrap:wrap; }
.hchip {
    display:inline-flex; align-items:center; gap:6px;
    background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2);
    border-radius:20px; padding:5px 14px;
    font-size:12px; font-weight:700; color:white;
}
.hchip--green  { background:rgba(16,185,129,0.25);  border-color:rgba(16,185,129,0.4);  color:#6ee7b7; }
.hchip--amber  { background:rgba(245,158,11,0.25);  border-color:rgba(245,158,11,0.4);  color:#fcd34d; }
.hchip--purple { background:rgba(139,92,246,0.25);  border-color:rgba(139,92,246,0.4);  color:#c4b5fd; }
.hchip--red    { background:rgba(239,68,68,0.25);   border-color:rgba(239,68,68,0.4);   color:#fca5a5; }
.hchip--gray   { background:rgba(148,163,184,0.2);  border-color:rgba(148,163,184,0.3); color:#cbd5e1; }

.hero-status-form { display:flex; align-items:center; gap:8px; flex-shrink:0; }
.hero-status-sel {
    height:40px; padding:0 14px;
    background:rgba(255,255,255,0.15) !important; border:1px solid rgba(255,255,255,0.3);
    border-radius:12px; font-size:13px; font-family:var(--font); color:white !important;
    outline:none; cursor:pointer; transition:background .15s;
    -webkit-appearance:none; appearance:none;
}
.hero-status-sel option { background:#002366 !important; color:white !important; }
.hero-status-sel:hover  { background:rgba(255,255,255,0.22) !important; }
.hero-save-btn {
    height:40px; padding:0 20px;
    background:rgba(255,255,255,0.15) !important; border:1.5px solid rgba(255,255,255,0.35);
    color:white !important; border-radius:12px; font-size:13px; font-weight:700;
    cursor:pointer; font-family:var(--font); transition:all .15s;
}
.hero-save-btn:hover { background:rgba(255,255,255,0.28) !important; }

/* ── BACK BTN ────────────────────────── */
.back-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 16px; background: var(--card); border: 1px solid rgba(0,0,0,0.08);
    border-radius: 12px; font-size: 13px; font-weight: 600; color: var(--ink-mid);
    text-decoration: none; transition: all .15s; margin-bottom: 18px;
}
.back-btn:hover { background: var(--cream-dark); color: var(--ink); }

/* ── LAYOUT ──────────────────────────── */
.req-layout { display: grid; grid-template-columns: 1fr 360px; gap: 20px; align-items: start; }

/* ── CARD ────────────────────────────── */
.card {
    background: var(--card); border: 1px solid rgba(0,0,0,0.06);
    border-radius: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); overflow: hidden;
    margin-bottom: 18px;
}
.card:last-child { margin-bottom: 0; }
.card__head {
    padding: 16px 20px; border-bottom: 1px solid rgba(0,0,0,0.05);
    display: flex; align-items: center; justify-content: space-between;
    background: #fafbfc;
}
.card__title { font-family: var(--font-disp); font-size: 16px; color: var(--ink); }
.card__body { padding: 20px; }

/* ── REQUEST FIELDS ──────────────────── */
.req-field { margin-bottom: 18px; }
.req-field:last-child { margin-bottom: 0; }
.req-field-label {
    font-size: 10.5px; font-weight: 700; letter-spacing: 0.6px;
    text-transform: uppercase; color: var(--ink-faint); margin-bottom: 6px;
}
.req-field-value { font-size: 13.5px; color: var(--ink-mid); line-height: 1.6; }
.platforms-list { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 4px; }
.plat-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;
    background: var(--navy-pale); color: var(--navy);
}

/* ── TABS ────────────────────────────── */
.tabs { display: flex; gap: 4px; padding: 4px; background: var(--cream-dark); border-radius: 14px; margin-bottom: 18px; }
.tab-btn {
    flex: 1; padding: 8px 14px; border: none; border-radius: 11px;
    font-size: 13px; font-weight: 600; cursor: pointer; font-family: var(--font);
    color: var(--ink-soft); background: transparent; transition: all .15s;
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.tab-btn.active { background: var(--card); color: var(--ink); box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.tab-pane { display: none; }
.tab-pane.active { display: block; }

/* ── MEDIA GRID ──────────────────────── */
.media-toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px; }
.media-toolbar-left { display: flex; align-items: center; gap: 8px; }
.btn-sm {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px; border-radius: 10px; font-size: 12.5px; font-weight: 600;
    cursor: pointer; font-family: var(--font); transition: all .15s; border: none;
    text-decoration: none; white-space: nowrap;
}
.btn-sm--navy    { background: var(--navy); color: white; }
.btn-sm--navy:hover { background: var(--navy-mid); }
.btn-sm--outline { background: var(--card); color: var(--ink-mid); border: 1px solid rgba(0,0,0,0.1); }
.btn-sm--outline:hover { background: var(--cream-dark); }
.btn-sm--green   { background: #10b981; color: white; }
.btn-sm--green:hover { background: #059669; }
.btn-sm--amber   { background: #f59e0b; color: white; }
.btn-sm--amber:hover { background: #d97706; }
.select-all-wrap { display: flex; align-items: center; gap: 6px; font-size: 12.5px; color: var(--ink-soft); cursor: pointer; }

.media-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 14px; }
.media-item {
    background: var(--cream-dark); border-radius: 14px; overflow: hidden;
    position: relative; cursor: pointer; transition: transform .2s, box-shadow .2s;
    border: 2px solid transparent;
}
.media-item:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
.media-item.selected { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(0,35,102,0.15); }
.media-item__check {
    position: absolute; top: 8px; left: 8px; z-index: 3;
    width: 22px; height: 22px; border-radius: 6px;
    background: rgba(255,255,255,0.9); border: 2px solid rgba(0,0,0,0.15);
    display: flex; align-items: center; justify-content: center; transition: all .15s;
}
.media-item.selected .media-item__check { background: var(--navy); border-color: var(--navy); }
.media-item.selected .media-item__check svg { display: block; }
.media-item__check svg { display: none; color: white; }
.media-img-wrap { aspect-ratio: 4/3; overflow: hidden; position: relative; background: var(--cream-dark); }
.media-img-wrap img { width: 100%; height: 100%; object-fit: cover; display: block; }
.media-img-wrap .no-img { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-size: 36px; }
.media-item__actions {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 100%);
    padding: 24px 10px 10px; display: flex; gap: 6px;
    opacity: 0; transition: opacity .2s;
}
.media-item:hover .media-item__actions { opacity: 1; }
.media-action-btn {
    flex: 1; padding: 6px 8px; border-radius: 8px; border: none; cursor: pointer;
    font-size: 11px; font-weight: 600; font-family: var(--font);
    display: flex; align-items: center; justify-content: center; gap: 4px; transition: all .15s;
}
.media-action-btn--dl   { background: rgba(255,255,255,0.9); color: var(--ink); }
.media-action-btn--dl:hover   { background: white; }
.media-action-btn--edit { background: rgba(0,35,102,0.85); color: white; }
.media-action-btn--edit:hover { background: var(--navy); }
.media-caption { padding: 10px 12px; }
.media-caption__text { font-size: 12px; color: var(--ink-mid); line-height: 1.55; margin-bottom: 6px; min-height: 36px; }
.media-caption__ai   { font-size: 10px; color: var(--ink-faint); display: flex; align-items: center; gap: 4px; }
.ai-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 7px; border-radius: 10px; font-size: 10px; font-weight: 600;
    background: linear-gradient(135deg, #667eea, #764ba2); color: white;
}

/* ── CANVAS EDITOR ───────────────────── */
.canvas-wrap {
    background: #1a1a1a; border-radius: 16px; overflow: hidden;
    aspect-ratio: 16/9; position: relative; margin-bottom: 14px;
    display: flex; align-items: center; justify-content: center;
}
.canvas-wrap canvas { max-width: 100%; max-height: 100%; display: block; }
.canvas-tools { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 14px; }
.tool-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 14px; border-radius: 10px; font-size: 12.5px; font-weight: 600;
    cursor: pointer; font-family: var(--font); transition: all .15s;
    border: 1px solid rgba(0,0,0,0.1); background: var(--card); color: var(--ink-mid);
}
.tool-btn:hover { background: var(--cream-dark); color: var(--ink); }
.tool-btn.active { background: var(--navy); color: white; border-color: var(--navy); }
.canvas-no-img { padding: 40px; text-align: center; color: var(--ink-faint); }
.filter-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; margin-bottom: 14px; }
.filter-swatch { border-radius: 10px; overflow: hidden; cursor: pointer; border: 2px solid transparent; transition: all .15s; }
.filter-swatch:hover { border-color: var(--navy-light); }
.filter-swatch.active { border-color: var(--navy); }
.filter-swatch img { width: 100%; aspect-ratio: 4/3; object-fit: cover; display: block; }
.filter-swatch p { font-size: 10px; text-align: center; padding: 4px; font-weight: 600; color: var(--ink-soft); }
.slider-row { display: flex; align-items: center; gap: 12px; margin-bottom: 10px; }
.slider-row label { font-size: 12px; font-weight: 600; color: var(--ink-mid); width: 80px; flex-shrink: 0; }
.slider-row input[type=range] { flex: 1; accent-color: var(--navy); }
.slider-row span { font-size: 12px; color: var(--ink-soft); width: 30px; text-align: right; }

/* ── SIDEBAR ─────────────────────────── */
.info-row { display: flex; justify-content: space-between; align-items: flex-start; padding: 11px 0; border-bottom: 1px solid rgba(0,0,0,0.05); font-size: 13px; }
.info-row:last-child { border-bottom: none; }
.info-row__label { color: var(--ink-soft); font-weight: 500; }
.info-row__value { color: var(--ink); font-weight: 600; text-align: right; max-width: 60%; }

/* ── COMMENTS ────────────────────────── */
.comment-list { display: flex; flex-direction: column; gap: 12px; margin-bottom: 16px; max-height: 320px; overflow-y: auto; }
.comment-list::-webkit-scrollbar { width: 4px; }
.comment-list::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 4px; }
.comment-bubble-wrap { display: flex; align-items: flex-end; gap: 8px; }
.comment-bubble-wrap--admin { flex-direction: row-reverse; }
.comment-av { width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: white; }
.comment-av--admin { background: var(--navy); }
.comment-av--user  { background: var(--purple); }
.comment-bubble { max-width: 78%; padding: 9px 13px; border-radius: 14px; font-size: 12.5px; line-height: 1.5; word-break: break-word; }
.comment-bubble--admin { background: var(--navy); color: white; border-bottom-right-radius: 4px; }
.comment-bubble--user  { background: var(--card); color: var(--ink); border: 1px solid rgba(0,0,0,0.07); border-bottom-left-radius: 4px; }
.comment-meta { font-size: 10px; margin-top: 3px; opacity: .65; }
.comment-bubble--admin .comment-meta { text-align: right; color: rgba(255,255,255,.8); }
.comment-bubble--user  .comment-meta { color: var(--ink-faint); }
.comment-form-area { border: 1.5px solid rgba(0,0,0,0.1); border-radius: 14px; overflow: hidden; background: var(--cream); }
.comment-textarea { width: 100%; padding: 12px 14px; font-size: 13px; font-family: var(--font); resize: none; outline: none; height: 80px; background: transparent; color: var(--ink); border: none; line-height: 1.5; }
.comment-form-footer { padding: 8px 12px; border-top: 1px solid rgba(0,0,0,0.07); display: flex; align-items: center; justify-content: flex-end; }
.btn-send { display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; background: var(--navy); color: white; border: none; border-radius: 10px; font-size: 12.5px; font-weight: 600; cursor: pointer; font-family: var(--font); transition: background .15s; }
.btn-send:hover { background: var(--navy-mid); }

/* ── CAPTION MODAL ───────────────────── */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 300; backdrop-filter: blur(4px); align-items: center; justify-content: center; }
.modal-overlay.open { display: flex; }
.modal { background: var(--card); border-radius: 24px; width: 600px; max-width: 95vw; max-height: 90vh; overflow-y: auto; box-shadow: 0 24px 60px rgba(0,0,0,0.2); animation: popIn .2s cubic-bezier(.34,1.4,.64,1); }
@keyframes popIn { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
.modal__head { padding: 20px 24px 16px; border-bottom: 1px solid rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; background: var(--card); }
.modal__title { font-family: var(--font-disp); font-size: 18px; color: var(--ink); }
.modal__close { width: 34px; height: 34px; border-radius: 10px; background: var(--cream-dark); border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--ink-soft); transition: all .15s; }
.modal__close:hover { background: rgba(0,0,0,0.1); }
.modal__body { padding: 20px 24px; }
.modal__img-preview { width: 100%; aspect-ratio: 16/9; border-radius: 14px; overflow: hidden; background: var(--cream-dark); margin-bottom: 18px; }
.modal__img-preview img { width: 100%; height: 100%; object-fit: cover; }
.caption-textarea { width: 100%; border: 1.5px solid rgba(0,0,0,0.1); border-radius: 14px; padding: 14px; font-size: 13.5px; font-family: var(--font); color: var(--ink); resize: none; outline: none; min-height: 120px; line-height: 1.6; background: var(--cream); transition: border-color .15s; }
.caption-textarea:focus { border-color: var(--navy-light); box-shadow: 0 0 0 3px rgba(30,79,216,0.08); }
.caption-actions { display: flex; gap: 8px; margin-top: 12px; }
.prompt-wrap { margin-top: 14px; }
.prompt-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: var(--ink-faint); margin-bottom: 6px; }
.prompt-row { display: flex; gap: 8px; }
.prompt-input { flex: 1; height: 40px; border: 1.5px solid rgba(0,0,0,0.1); border-radius: 11px; padding: 0 14px; font-size: 13px; font-family: var(--font); color: var(--ink); background: var(--cream); outline: none; transition: border-color .15s; }
.prompt-input:focus { border-color: var(--navy-light); }
.prompt-input::placeholder { color: var(--ink-faint); }
.spinner { display: none; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin .6s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ── TOAST ───────────────────────────── */
.toast { position: fixed; bottom: 24px; right: 24px; z-index: 999; padding: 12px 20px; border-radius: 14px; font-size: 13px; font-weight: 500; box-shadow: 0 4px 20px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 10px; animation: slideUp .3s ease, fadeOut .4s ease 2.6s forwards; }
.toast--success { background: #059669; color: white; }
.toast--error   { background: #dc2626; color: white; }
@keyframes slideUp { from { transform: translateY(16px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes fadeOut { from { opacity: 1; } to { opacity: 0; pointer-events: none; } }
</style>
@endsection

@section('content')
@php
    $mediaFiles = $request->media_files
        ? (is_array($request->media_files)
            ? $request->media_files
            : (json_decode($request->media_files, true) ?? explode(',', $request->media_files)))
        : [];
    $mediaFiles = array_filter(array_map(
        fn($f) => trim(is_array($f) ? ($f['path'] ?? $f['file'] ?? $f['filename'] ?? '') : $f),
        $mediaFiles
    ));
    $captions      = json_decode($request->ai_captions ?? '{}', true) ?: [];
    $sl            = strtolower($request->status);
    $statusChip    = match(true) {
        str_contains($sl,'approved')     => 'hchip--green',
        str_contains($sl,'posted')       => 'hchip--purple',
        str_contains($sl,'under review') => 'hchip--amber',
        str_contains($sl,'rejected')     => 'hchip--red',
        default                          => 'hchip--gray',
    };
    $priorityChip  = match(strtolower($request->priority ?? '')) {
        'urgent' => 'hchip--red', 'high' => 'hchip--amber', default => 'hchip--gray',
    };
    $comments      = $request->comments()->orderBy('created_at')->get();
    $firstImage    = null;
    foreach ($mediaFiles as $f) {
        $ext2 = strtolower(pathinfo(trim($f), PATHINFO_EXTENSION));
        if (in_array($ext2, ['jpg','jpeg','png','gif','webp','jfif','svg'])) { $firstImage = trim($f); break; }
    }
@endphp

{{-- BACK --}}
<a href="{{ route('admin.requests') }}" class="back-btn">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Back to Requests
</a>

{{-- HERO BANNER --}}
<div class="req-hero" style="background:linear-gradient(135deg,#001a4d 0%,#002e7a 55%,#003a8c 100%)!important;color:white!important;border-radius:22px;padding:28px 32px;margin-bottom:22px;position:relative;overflow:hidden;box-shadow:0 8px 32px rgba(0,26,77,0.35);">
    <div class="req-hero__inner">
        <div style="flex:1;min-width:0;">
            <div class="req-hero__id" style="color:rgba(255,255,255,0.5);font-size:11px;font-weight:600;letter-spacing:0.5px;margin-bottom:8px;">{{ $request->request_id ?? 'REQ-' . str_pad($request->id, 4, '0', STR_PAD_LEFT) }}</div>
            <div class="req-hero__title" style="font-family:'DM Serif Display',Georgia,serif;font-size:26px;color:white;line-height:1.25;margin-bottom:14px;">{{ $request->title }}</div>
            <div class="req-hero__chips" style="display:flex;gap:8px;flex-wrap:wrap;">
                <span class="hchip {{ $statusChip }}">{{ $request->status }}</span>
                <span class="hchip {{ $priorityChip }}">{{ strtoupper($request->priority ?? 'NORMAL') }}</span>
                @if($request->category)<span class="hchip">{{ $request->category }}</span>@endif
                <span class="hchip">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    {{ $request->requester }}
                </span>
                <span class="hchip">📅 {{ $request->created_at->format('M j, Y') }}</span>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.requests.status') }}" class="hero-status-form"
              style="display:flex;align-items:center;gap:8px;flex-shrink:0;position:relative;z-index:2;"
              onsubmit="confirmHeroStatus(event, this)">
            @csrf
            <input type="hidden" name="request_id" value="{{ $request->id }}">
            <input type="hidden" name="redirect_to" value="{{ route('admin.requests.show', $request->id) }}">
            <select name="status" class="hero-status-sel"
                    style="height:40px;padding:0 14px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);border-radius:12px;font-size:13px;font-family:inherit;color:white;outline:none;cursor:pointer;">
                @foreach(['Pending Review','Under Review','Approved','Posted','Rejected'] as $s)
                    <option value="{{ $s }}" style="background:#002366;color:white;" {{ $request->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <button type="submit" class="hero-save-btn"
                    style="height:40px;padding:0 20px;background:rgba(255,255,255,0.18);border:1.5px solid rgba(255,255,255,0.35);color:white;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;">
                Save Status
            </button>
        </form>
    </div>
</div>

{{-- MAIN LAYOUT --}}
<div class="req-layout">

    {{-- LEFT --}}
    <div>
        {{-- Details card --}}
        <div class="card">
            <div class="card__body">
                @if($request->description)
                <div class="req-field">
                    <div class="req-field-label">Description</div>
                    <div class="req-field-value">{{ $request->description }}</div>
                </div>
                @endif
                <div class="req-field">
                    <div class="req-field-label" style="display:flex;justify-content:space-between;align-items:center;">
                        <span>Caption Draft</span>
                        @if($request->caption)
                        <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('caption-field').value);showToast('Caption copied!');" style="background:none;border:none;color:var(--navy);font-size:11px;font-weight:700;cursor:pointer;padding:0;">Copy</button>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.requests.caption', $request->id) }}" style="margin-top:6px;">
                        @csrf
                        <textarea id="caption-field" name="caption" class="caption-textarea" style="width:100%;min-height:95px;padding:12px;border:1.5px solid rgba(0,0,0,0.1);border-radius:12px;font-size:13px;font-family:inherit;outline:none;resize:vertical;background:var(--cream);" placeholder="Enter or edit social media caption draft...">{{ old('caption', $request->caption) }}</textarea>
                        <div style="display:flex;justify-content:flex-end;margin-top:8px;">
                            <button type="submit" class="btn-sm btn-sm--navy" style="padding:7px 16px;">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                Save Caption Draft
                            </button>
                        </div>
                    </form>
                </div>
                @if(!empty($request->platforms_array))
                <div class="req-field">
                    <div class="req-field-label">Target Platforms</div>
                    <div class="platforms-list">
                        @foreach($request->platforms_array as $plat)
                        <span class="plat-pill">{{ $plat }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
                @if($request->preferred_date)
                <div class="req-field">
                    <div class="req-field-label">Preferred Post Date</div>
                    <div class="req-field-value">{{ \Carbon\Carbon::parse($request->preferred_date)->format('F j, Y') }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Media + Edit Tabs --}}
        <div class="card">
            <div class="card__head">
                <div class="card__title">Media & Captions</div>
                @if(count($mediaFiles) > 0)
                <div style="display:flex;gap:8px;">
                    <button class="btn-sm btn-sm--outline" onclick="downloadSelected()">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Download Selected
                    </button>
                    <button class="btn-sm btn-sm--navy" onclick="downloadAll()">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Download All
                    </button>
                </div>
                @endif
            </div>
            <div class="card__body">
                <div class="tabs">
                    <button class="tab-btn active" onclick="switchTab('media', this)">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        Media & Captions
                    </button>
                    <button class="tab-btn" onclick="switchTab('edit', this)">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit Photo
                    </button>
                </div>

                {{-- MEDIA TAB --}}
                <div class="tab-pane active" id="tab-media">
                    @if(count($mediaFiles) > 0)
                    <div class="media-toolbar">
                        <div class="media-toolbar-left">
                            <label class="select-all-wrap">
                                <input type="checkbox" id="select-all" onchange="toggleSelectAll(this)">
                                Select All ({{ count($mediaFiles) }} files)
                            </label>
                        </div>
                        <div>
                            <button class="btn-sm btn-sm--amber" onclick="generateAllCaptions()">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zM8 12h8M12 8v8"/></svg>
                                Generate All Captions
                            </button>
                        </div>
                    </div>
                    <div class="media-grid" id="media-grid">
                        @foreach($mediaFiles as $i => $file)
                        @php
                            $filename  = trim($file);
                            $ext       = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                            $isImage   = in_array($ext, ['jpg','jpeg','png','gif','webp','jfif','svg']);
                            $caption   = $captions[$filename] ?? '';
                            $fileUrl   = '/uploads/' . $filename;
                            $apiUrl    = '/api/media.php?file=' . urlencode($filename);
                        @endphp
                        <div class="media-item" id="media-item-{{ $i }}" data-file="{{ $filename }}" data-url="{{ $fileUrl }}" onclick="toggleSelect({{ $i }})">
                            <div class="media-item__check">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                            <div class="media-img-wrap">
                                @if($isImage)
                                    <div class="no-img" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:36px;">🖼️</div>
                                    <img src="{{ $fileUrl }}" alt="{{ $filename }}" style="position:relative;z-index:1;" onerror="if(!this.dataset.triedApi){this.dataset.triedApi='1';this.src='{{ $apiUrl }}';}else{this.style.opacity='0';}">
                                @else
                                    <div class="no-img">📄</div>
                                @endif
                            </div>
                            <div class="media-item__actions">
                                <button class="media-action-btn media-action-btn--dl" onclick="event.stopPropagation();downloadFile('{{ $fileUrl }}','{{ $filename }}')">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    Save
                                </button>
                                <button class="media-action-btn media-action-btn--edit" onclick="event.stopPropagation();openCaptionEditor({{ $i }},'{{ $filename }}','{{ $fileUrl }}')">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Caption
                                </button>
                            </div>
                            <div class="media-caption">
                                <div class="media-caption__text" id="caption-display-{{ $i }}">
                                    {{ $caption ?: 'No caption yet. Click "Caption" to generate or write one.' }}
                                </div>
                                <div class="media-caption__ai">
                                    @if($caption)
                                        <span class="ai-badge">✦ AI</span> Generated caption
                                    @else
                                        <span style="color:var(--ink-faint);">No caption</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div style="padding:40px;text-align:center;color:var(--ink-faint);">
                        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 12px;display:block;opacity:.3;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        <p style="font-size:14px;font-weight:600;color:var(--ink-mid);">No media files attached</p>
                    </div>
                    @endif
                </div>

                {{-- EDIT TAB --}}
                <div class="tab-pane" id="tab-edit">
                    @if(count($mediaFiles) > 0)
                    <div style="margin-bottom:12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <label style="font-size:12px;font-weight:600;color:var(--ink-soft);">Select image to edit:</label>
                        <select id="edit-file-select" onchange="loadEditorImage(this.value)" style="padding:5px 12px;border:1px solid rgba(0,0,0,0.1);border-radius:8px;font-size:12.5px;font-family:var(--font);cursor:pointer;outline:none;">
                            @foreach($mediaFiles as $f)
                            @php $ext3 = strtolower(pathinfo(trim($f), PATHINFO_EXTENSION)); @endphp
                            @if(in_array($ext3, ['jpg','jpeg','png','gif','webp','jfif','svg']))
                            <option value="/uploads/{{ trim($f) }}">{{ trim($f) }}</option>
                            @endif
                            @endforeach
                        </select>
                        <button class="btn-sm btn-sm--green" onclick="downloadCanvas()" style="margin-left:auto;">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Download Edited
                        </button>
                    </div>
                    <div class="canvas-wrap"><canvas id="editor-canvas"></canvas></div>
                    <div class="canvas-tools">
                        <button class="tool-btn active" id="tool-filters" onclick="setTool('filters',this)">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
                            Filters
                        </button>
                        <button class="tool-btn" id="tool-adjust" onclick="setTool('adjust',this)">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
                            Adjust
                        </button>
                        <button class="tool-btn" id="tool-text" onclick="setTool('text',this)">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
                            Text
                        </button>
                    </div>
                    <div id="panel-filters" class="tool-panel">
                        <div class="filter-grid">
                            @php $filterNames = ['Normal','Warm','Cool','Vintage','B&W','Fade','Vivid','Matte']; @endphp
                            @foreach($filterNames as $fi => $fn)
                            <div class="filter-swatch {{ $fi===0?'active':'' }}" id="filter-{{ $fi }}" onclick="applyFilter('{{ strtolower($fn) }}', {{ $fi }})">
                                @if($firstImage)
                                <img src="/uploads/{{ $firstImage }}" alt="{{ $fn }}">
                                @else
                                <div style="background:var(--cream-dark);aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;font-size:18px;">🖼️</div>
                                @endif
                                <p>{{ $fn }}</p>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div id="panel-adjust" class="tool-panel" style="display:none;">
                        <div class="slider-row"><label>Brightness</label><input type="range" min="0" max="200" value="100" oninput="adjustCanvas('brightness',this.value);document.getElementById('v-brightness').textContent=this.value" id="sl-brightness"><span id="v-brightness">100</span></div>
                        <div class="slider-row"><label>Contrast</label><input type="range" min="0" max="200" value="100" oninput="adjustCanvas('contrast',this.value);document.getElementById('v-contrast').textContent=this.value" id="sl-contrast"><span id="v-contrast">100</span></div>
                        <div class="slider-row"><label>Saturation</label><input type="range" min="0" max="200" value="100" oninput="adjustCanvas('saturate',this.value);document.getElementById('v-saturation').textContent=this.value" id="sl-saturation"><span id="v-saturation">100</span></div>
                        <div class="slider-row"><label>Blur</label><input type="range" min="0" max="10" value="0" step="0.1" oninput="adjustCanvas('blur',this.value);document.getElementById('v-blur').textContent=this.value" id="sl-blur"><span id="v-blur">0</span></div>
                        <button class="btn-sm btn-sm--outline" style="margin-top:6px;" onclick="resetAdjustments()">Reset</button>
                    </div>
                    <div id="panel-text" class="tool-panel" style="display:none;">
                        <div style="display:flex;gap:8px;margin-bottom:10px;flex-wrap:wrap;align-items:center;">
                            <input type="text" id="text-input" placeholder="Enter text to overlay..." style="flex:1;min-width:160px;height:38px;border:1px solid rgba(0,0,0,0.1);border-radius:10px;padding:0 12px;font-size:13px;font-family:var(--font);outline:none;">
                            <input type="color" id="text-color" value="#ffffff" style="width:38px;height:38px;border:none;border-radius:8px;cursor:pointer;">
                            <select id="text-size" style="height:38px;border:1px solid rgba(0,0,0,0.1);border-radius:10px;padding:0 12px;font-size:13px;font-family:var(--font);cursor:pointer;outline:none;">
                                <option value="20">Small</option><option value="32" selected>Medium</option><option value="48">Large</option><option value="64">XLarge</option>
                            </select>
                            <button class="btn-sm btn-sm--navy" onclick="addTextOverlay()">Add Text</button>
                        </div>
                        <p style="font-size:12px;color:var(--ink-faint);">Text will be centered at the bottom of the image.</p>
                    </div>
                    @else
                    <div class="canvas-no-img"><p>No images to edit.</p></div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- RIGHT COLUMN --}}
    <div>
        {{-- Request Info card --}}
        <div class="card" style="margin-bottom:16px;">
            <div class="card__head"><div class="card__title">Request Info</div></div>
            <div class="card__body" style="padding-top:8px;">
                <div class="info-row"><span class="info-row__label">Submitted by</span><span class="info-row__value">{{ $request->requester }}</span></div>
                <div class="info-row"><span class="info-row__label">Submitted on</span><span class="info-row__value">{{ $request->created_at->format('M j, Y g:i A') }}</span></div>
                <div class="info-row"><span class="info-row__label">Category</span><span class="info-row__value">{{ $request->category ?? '—' }}</span></div>
                <div class="info-row"><span class="info-row__label">Priority</span><span class="info-row__value">{{ strtoupper($request->priority ?? 'Normal') }}</span></div>
                <div class="info-row"><span class="info-row__label">Preferred Date</span><span class="info-row__value">{{ $request->preferred_date ? \Carbon\Carbon::parse($request->preferred_date)->format('M j, Y') : '—' }}</span></div>
                <div class="info-row"><span class="info-row__label">Media Files</span><span class="info-row__value">{{ count($mediaFiles) }} file(s)</span></div>
                <div class="info-row"><span class="info-row__label">Status</span><span class="info-row__value">{{ $request->status }}</span></div>
            </div>
        </div>

        {{-- Comments card --}}
        <div class="card">
            <div class="card__head"><div class="card__title">💬 Comments</div></div>
            <div class="card__body">
                <div class="comment-list" id="comment-list">
                    @forelse($comments as $c)
                        <div class="comment-bubble-wrap {{ $c->sender_role === 'admin' ? 'comment-bubble-wrap--admin' : '' }}">
                            <div class="comment-av {{ $c->sender_role === 'admin' ? 'comment-av--admin' : 'comment-av--user' }}">{{ strtoupper(substr($c->sender_name, 0, 1)) }}</div>
                            <div class="comment-bubble {{ $c->sender_role === 'admin' ? 'comment-bubble--admin' : 'comment-bubble--user' }}">
                                {{ $c->message }}
                                <div class="comment-meta">{{ $c->sender_name }} · {{ $c->created_at->format('M j, g:i A') }}</div>
                            </div>
                        </div>
                    @empty
                        <p style="font-size:13px;color:var(--ink-faint);text-align:center;padding:16px 0;">No comments yet. Start the conversation below.</p>
                    @endforelse
                </div>
                <div class="comment-form-area">
                    <textarea class="comment-textarea" id="comment-input" placeholder="Write a comment… (Ctrl+Enter to send)"></textarea>
                    <div class="comment-form-footer">
                        <button class="btn-send" onclick="sendComment()">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                            Send
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>{{-- end right column --}}
</div>{{-- end req-layout --}}

{{-- CAPTION EDITOR MODAL --}}
<div class="modal-overlay" id="caption-modal">
    <div class="modal">
        <div class="modal__head">
            <div class="modal__title">Edit Caption</div>
            <button class="modal__close" onclick="closeCaptionEditor()">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal__body">
            <div class="modal__img-preview"><img id="modal-img" src="" alt=""></div>
            <input type="hidden" id="modal-filename">
            <input type="hidden" id="modal-index">
            <textarea class="caption-textarea" id="modal-caption" placeholder="Write or generate a caption for this image…"></textarea>
            <div class="caption-actions">
                <button class="btn-sm btn-sm--outline" onclick="closeCaptionEditor()">Cancel</button>
                <button class="btn-sm btn-sm--navy" onclick="saveCaption()" id="btn-save-caption">Save Caption</button>
            </div>
            <div class="prompt-wrap">
                <div class="prompt-label">✦ Generate with Gemini AI</div>
                <div class="prompt-row">
                    <input type="text" class="prompt-input" id="modal-prompt"
                           placeholder="Optional prompt (e.g. 'focus on the event theme')…"
                           onkeydown="if(event.key==='Enter')generateCaption()">
                    <button class="btn-sm btn-sm--amber" onclick="generateCaption()" id="btn-generate">
                        <span class="spinner" id="gen-spinner"></span>
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" id="gen-icon"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zM8 12h8M12 8v8"/></svg>
                        Generate
                    </button>
                </div>
                <p style="font-size:11px;color:var(--ink-faint);margin-top:6px;">Based on: <em>{{ Str::limit($request->title, 50) }}</em> — <em>{{ Str::limit($request->description ?? '', 60) }}</em></p>
            </div>
        </div>
    </div>
</div>

@if(session('success'))<div class="toast toast--success" id="toast"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>{{ session('success') }}</div>@endif
@if(session('error'))<div class="toast toast--error" id="toast"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>{{ session('error') }}</div>@endif
@endsection

@section('scripts')
<script>
var REQ_ID    = {{ $request->id }};
var REQ_TITLE = @json($request->title);
var REQ_DESC  = @json($request->description ?? '');
// CSRF is already declared globally in the admin layout — do not redeclare

function switchTab(tab, btn) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
    if (tab === 'edit') initEditor();
}

function toggleSelect(i) { document.getElementById('media-item-' + i)?.classList.toggle('selected'); }
function toggleSelectAll(cb) {
    document.querySelectorAll('.media-item').forEach(item => cb.checked ? item.classList.add('selected') : item.classList.remove('selected'));
}

function downloadFile(url, filename) {
    filename = filename || (url ? url.split('/').pop() : 'file');
    // Route through authenticated Laravel endpoint — direct /uploads/ access fails on live server
    const dlUrl = '/admin/requests/' + REQ_ID + '/download/' + encodeURIComponent(filename);
    const a = document.createElement('a');
    a.href     = dlUrl;
    a.download = filename;
    a.rel      = 'noopener';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
function downloadSelected() {
    const selected = [...document.querySelectorAll('.media-item.selected')];
    if (!selected.length) { showToast('Select at least one file first.', 'error'); return; }
    showToast('Downloading ' + selected.length + ' file(s)…', 'success');
    selected.forEach((item, i) => setTimeout(() => downloadFile(item.dataset.url, item.dataset.file), i * 300));
}
function downloadAll() {
    const items = [...document.querySelectorAll('.media-item')];
    if (!items.length) { showToast('No files to download.', 'error'); return; }
    showToast('Downloading all ' + items.length + ' file(s)…', 'success');
    items.forEach((item, i) => setTimeout(() => downloadFile(item.dataset.url, item.dataset.file), i * 300));
}

function openCaptionEditor(i, filename, url) {
    document.getElementById('modal-img').src        = url;
    document.getElementById('modal-filename').value  = filename;
    document.getElementById('modal-index').value     = i;
    const txt = document.getElementById('caption-display-' + i).textContent.trim();
    document.getElementById('modal-caption').value   = txt.includes('No caption yet') ? '' : txt;
    document.getElementById('modal-prompt').value    = '';
    document.getElementById('caption-modal').classList.add('open');
}
function closeCaptionEditor() { document.getElementById('caption-modal').classList.remove('open'); }

async function generateCaption() {
    const spinner = document.getElementById('gen-spinner');
    const icon    = document.getElementById('gen-icon');
    const btn     = document.getElementById('btn-generate');
    spinner.style.display = 'block'; icon.style.display = 'none'; btn.disabled = true;
    try {
        const res  = await fetch(`/admin/requests/${REQ_ID}/generate-caption`, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
            body: JSON.stringify({ filename: document.getElementById('modal-filename').value, prompt: document.getElementById('modal-prompt').value.trim(), title: REQ_TITLE, description: REQ_DESC })
        });
        const data = await res.json();
        if (data.caption) { document.getElementById('modal-caption').value = data.caption; showToast('Caption generated!', 'success'); }
        else showToast(data.error || 'Generation failed.', 'error');
    } catch { showToast('Connection error.', 'error'); }
    spinner.style.display = 'none'; icon.style.display = 'block'; btn.disabled = false;
}

async function saveCaption() {
    const filename = document.getElementById('modal-filename').value;
    const idx      = document.getElementById('modal-index').value;
    const caption  = document.getElementById('modal-caption').value.trim();
    const btn      = document.getElementById('btn-save-caption');
    btn.disabled   = true;
    try {
        const res  = await fetch(`/admin/requests/${REQ_ID}/save-caption`, {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' },
            body: JSON.stringify({ filename, caption })
        });
        const data = await res.json();
        if (data.success) {
            const disp = document.getElementById('caption-display-' + idx);
            disp.textContent = caption || 'No caption yet. Click "Caption" to generate or write one.';
            disp.nextElementSibling.innerHTML = caption ? '<span class="ai-badge">✦ AI</span> Generated caption' : '<span style="color:var(--ink-faint);">No caption</span>';
            closeCaptionEditor(); showToast('Caption saved!', 'success');
        } else showToast(data.error || 'Save failed.', 'error');
    } catch { showToast('Connection error.', 'error'); }
    btn.disabled = false;
}

async function generateAllCaptions() {
    showToast('Generating captions for all images…', 'success');
    const items = document.querySelectorAll('.media-item');
    for (const item of items) {
        const filename = item.dataset.file;
        const ext = filename.split('.').pop().toLowerCase();
        if (!['jpg','jpeg','png','gif','webp'].includes(ext)) continue;
        const idx = item.id.replace('media-item-','');
        try {
            const res  = await fetch(`/admin/requests/${REQ_ID}/generate-caption`, {
                method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
                body: JSON.stringify({ filename, title: REQ_TITLE, description: REQ_DESC })
            });
            const data = await res.json();
            if (data.caption) {
                const disp = document.getElementById('caption-display-' + idx);
                if (disp) { disp.textContent = data.caption; disp.nextElementSibling.innerHTML = '<span class="ai-badge">✦ AI</span> Generated caption'; }
                await fetch(`/admin/requests/${REQ_ID}/save-caption`, {
                    method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
                    body: JSON.stringify({ filename, caption: data.caption })
                });
            }
        } catch {}
        await new Promise(r => setTimeout(r, 500));
    }
    showToast('All captions generated!', 'success');
}

let isSendingComment = false;
async function sendComment() {
    if (isSendingComment) return;
    const input = document.getElementById('comment-input');
    const msg   = input ? input.value.trim() : '';
    if (!msg) return;
    const btn   = document.querySelector('.btn-send');
    isSendingComment = true;
    if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.style.cursor = 'not-allowed';
    }
    try {
        const res  = await fetch('/admin/requests/comment', {
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF,'Accept':'application/json'},
            body: JSON.stringify({ request_id: REQ_ID, message: msg })
        });
        const data = await res.json();
        if (data.success) {
            input.value = '';
            const list = document.getElementById('comment-list');
            const html = `<div class="comment-bubble-wrap comment-bubble-wrap--admin">
                <div class="comment-av comment-av--admin">A</div>
                <div class="comment-bubble comment-bubble--admin">${esc(msg)}<div class="comment-meta">Admin · Just now</div></div>
            </div>`;
            if (list.querySelector('p')) list.innerHTML = '';
            list.insertAdjacentHTML('beforeend', html);
            list.scrollTop = list.scrollHeight;
        }
    } catch {}
    finally {
        isSendingComment = false;
        if (btn) {
            btn.disabled = false;
            btn.style.opacity = '';
            btn.style.cursor = '';
        }
    }
}
document.getElementById('comment-input')?.addEventListener('keydown', e => { if(e.key==='Enter'&&e.ctrlKey){e.preventDefault();sendComment();} });

/* CANVAS EDITOR */
let editorImg = null, currentFilter = 'normal', adjustments = { brightness:100, contrast:100, saturate:100, blur:0 }, textOverlays = [];

function initEditor() { const sel = document.getElementById('edit-file-select'); if (sel) loadEditorImage(sel.value); }
function loadEditorImage(url) {
    const canvas = document.getElementById('editor-canvas'); if (!canvas) return;
    const img = new Image(); img.crossOrigin='anonymous';
    img.onload = () => { editorImg=img; canvas.width=img.width; canvas.height=img.height; renderCanvas(); };
    img.onerror = () => {};
    img.src = url + '?t=' + Date.now();
}
function renderCanvas() {
    const canvas = document.getElementById('editor-canvas'); if (!canvas || !editorImg) return;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0,0,canvas.width,canvas.height);
    const filters = [];
    if (currentFilter==='warm')    filters.push('sepia(40%) saturate(150%)');
    if (currentFilter==='cool')    filters.push('hue-rotate(180deg) saturate(80%)');
    if (currentFilter==='vintage') filters.push('sepia(60%) contrast(110%) brightness(95%)');
    if (currentFilter==='b&w')     filters.push('grayscale(100%)');
    if (currentFilter==='fade')    filters.push('opacity(80%) brightness(105%) saturate(70%)');
    if (currentFilter==='vivid')   filters.push('saturate(180%) contrast(110%)');
    if (currentFilter==='matte')   filters.push('brightness(95%) contrast(90%) saturate(80%)');
    filters.push(`brightness(${adjustments.brightness}%)`);
    filters.push(`contrast(${adjustments.contrast}%)`);
    filters.push(`saturate(${adjustments.saturate}%)`);
    if (adjustments.blur > 0) filters.push(`blur(${adjustments.blur}px)`);
    ctx.filter = filters.join(' ');
    ctx.drawImage(editorImg, 0, 0);
    ctx.filter = 'none';
    textOverlays.forEach(t => {
        ctx.font = `bold ${t.size}px DM Sans, sans-serif`;
        ctx.fillStyle = t.color; ctx.textAlign = 'center';
        ctx.strokeStyle = 'rgba(0,0,0,0.5)'; ctx.lineWidth = 3;
        ctx.strokeText(t.text, canvas.width/2, t.y);
        ctx.fillText(t.text, canvas.width/2, t.y);
    });
}
function applyFilter(name, idx) {
    currentFilter = name;
    document.querySelectorAll('.filter-swatch').forEach(s => s.classList.remove('active'));
    document.getElementById('filter-' + idx)?.classList.add('active');
    renderCanvas();
}
function adjustCanvas(prop, val) { adjustments[prop] = parseFloat(val); renderCanvas(); }
function resetAdjustments() {
    adjustments = { brightness:100, contrast:100, saturate:100, blur:0 };
    ['brightness','contrast','saturation','blur'].forEach(s => {
        const el = document.getElementById('sl-' + s); if (el) el.value = s==='blur' ? 0 : 100;
        const v  = document.getElementById('v-' + s);  if (v)  v.textContent = s==='blur' ? 0 : 100;
    });
    renderCanvas();
}
function addTextOverlay() {
    const text  = document.getElementById('text-input').value.trim(); if (!text) return;
    const color = document.getElementById('text-color').value;
    const size  = parseInt(document.getElementById('text-size').value);
    const canvas = document.getElementById('editor-canvas');
    textOverlays.push({ text, color, size, y: (canvas?.height || 400) - 40 });
    renderCanvas();
    document.getElementById('text-input').value = '';
}
function downloadCanvas() {
    const canvas = document.getElementById('editor-canvas');
    if (!canvas || !editorImg) { showToast('Load an image first.', 'error'); return; }
    const a = document.createElement('a');
    a.href     = canvas.toDataURL('image/png');
    a.download = 'edited-' + (document.getElementById('edit-file-select')?.value.split('/').pop() || 'image.png');
    a.click();
}
function setTool(tool, btn) {
    document.querySelectorAll('.tool-panel').forEach(p => p.style.display='none');
    document.querySelectorAll('.tool-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + tool).style.display = 'block';
    btn.classList.add('active');
}

function showToast(msg, type='success') {
    document.getElementById('toast-dynamic')?.remove();
    const t = document.createElement('div'); t.id = 'toast-dynamic';
    t.className = 'toast toast--' + type;
    t.innerHTML = `<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">${type==='success'?'<polyline points="20 6 9 17 4 12"/>':'<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>'}</svg>${esc(msg)}`;
    document.body.appendChild(t); setTimeout(() => t.remove(), 3000);
}
function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
setTimeout(() => document.getElementById('toast')?.remove(), 3000);

function confirmHeroStatus(e, f) {
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
</script>
@endsection