@extends('layouts.requestor')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('head-styles')
<style>
/* ── PAGE WRAPPER ───────────────────── */
.page-outer {
    min-height: calc(100vh - 64px);
    display: flex; flex-direction: column;
    align-items: center;
    padding: 36px 24px 60px;
}
.page-inner { width: 100%; max-width: 820px; }

/* PAGE HEADER */
.page-header { margin-bottom: 24px; }
.page-header h1 { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; color: var(--text); }
.page-header p  { font-size: 13.5px; color: var(--text-muted); margin-top: 4px; }

/* ── CARD ───────────────────────────── */
.card {
    background: white; border-radius: 20px;
    border: 1.5px solid var(--border);
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    overflow: hidden;
}

/* ── NOTIF ITEMS ────────────────────── */
.notif-item {
    display: flex; align-items: flex-start; gap: 16px;
    padding: 20px 26px; border-bottom: 1px solid #f3f4f6;
    transition: background .12s; cursor: pointer; position: relative;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: #f8faff; }
.notif-item--unread { background: #f0f5ff; }
.notif-item--unread:hover { background: #e8f0fe; }

.notif-dot {
    position: absolute; top: 22px; right: 22px;
    width: 9px; height: 9px; border-radius: 50%;
    background: #3b6ef5; box-shadow: 0 0 0 3px rgba(59,110,245,0.15);
}
.notif-icon {
    width: 44px; height: 44px; border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; margin-top: 2px;
}
.notif-icon--approved { background: #dcfce7; color: #16a34a; }
.notif-icon--posted   { background: #ede9fe; color: #7c3aed; }
.notif-icon--reviewed { background: #dbeafe; color: #2563eb; }
.notif-icon--rejected { background: #fee2e2; color: #dc2626; }
.notif-icon--comment  { background: #fef3c7; color: #d97706; }
.notif-icon--default  { background: #f1f5f9; color: #64748b; }

.notif-body { flex: 1; min-width: 0; padding-right: 28px; }
.notif-title   { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
.notif-message { font-size: 13px; color: var(--text-muted); line-height: 1.6; margin-bottom: 6px; }
.notif-time    { font-size: 11.5px; color: var(--text-faint); }
.notif-admin-tag {
    display: inline-flex; align-items: center; gap: 5px;
    margin-top: 6px; font-size: 11.5px; color: #92400e;
    background: #fef3c7; padding: 3px 10px;
    border-radius: 20px; font-weight: 600; border: 1px solid #fde68a;
}

/* EMPTY */
.empty-state {
    padding: 72px 20px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 14px;
}
.empty-state__icon {
    width: 64px; height: 64px; border-radius: 18px;
    background: #f1f5f9; display: flex; align-items: center;
    justify-content: center; color: #cbd5e1;
}
.empty-state p { font-size: 14px; color: var(--text-faint); }

/* ── MODAL ──────────────────────────── */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,0.45); z-index: 400;
    align-items: center; justify-content: center;
    padding: 20px; backdrop-filter: blur(4px);
}
.modal-overlay--open { display: flex; }
.modal {
    background: white; border-radius: 20px;
    max-width: 680px; width: 100%;
    box-shadow: 0 24px 64px rgba(0,0,0,0.18);
    overflow: hidden; animation: modalIn .18s ease;
    border: 1.5px solid var(--border);
}
@keyframes modalIn { from { opacity:0; transform:scale(.96) translateY(10px); } to { opacity:1; transform:none; } }

.modal-header {
    display: flex; align-items: center; gap: 16px;
    padding: 22px 28px; border-bottom: 1.5px solid var(--border);
    background: #fafbfc;
}
.modal-icon { width: 46px; height: 46px; border-radius: 13px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
.modal-header-text { flex: 1; }
.modal-title { font-size: 16px; font-weight: 800; color: var(--text); margin-bottom: 3px; letter-spacing: -0.3px; }
.modal-time  { font-size: 12px; color: var(--text-faint); }
.modal-close {
    width: 34px; height: 34px; border-radius: 9px;
    border: 1.5px solid var(--border); background: white;
    cursor: pointer; display: flex; align-items: center;
    justify-content: center; color: var(--text-muted);
    font-size: 15px; flex-shrink: 0; transition: all .15s;
}
.modal-close:hover { background: #f1f5f9; }

/* TWO-COLUMN BODY */
.modal-body {
    display: grid; grid-template-columns: 1fr 1fr;
    min-height: 180px;
}
.modal-col-label {
    font-size: 10px; font-weight: 800; letter-spacing: 1.2px;
    text-transform: uppercase; color: var(--text-faint);
    margin-bottom: 14px; display: flex; align-items: center; gap: 6px;
}

/* LEFT */
.modal-left { padding: 26px 24px 26px 28px; border-right: 1.5px solid var(--border); }
.modal-status-badge {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 6px 14px; border-radius: 20px;
    font-size: 12.5px; font-weight: 700; margin-bottom: 14px;
}
.modal-status-dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
.modal-status-badge--approved     { background: #dcfce7; color: #15803d; }
.modal-status-badge--posted       { background: #ede9fe; color: #6d28d9; }
.modal-status-badge--under-review { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
.modal-status-badge--pending      { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
.modal-status-badge--rejected     { background: #fee2e2; color: #b91c1c; }
.modal-status-badge--default      { background: #f1f5f9; color: #475569; }
.modal-message { font-size: 14px; color: var(--text); line-height: 1.8; }

/* RIGHT */
.modal-right { padding: 26px 28px 26px 24px; background: #fffdf5; }
.modal-right--empty {
    background: #fafbfc;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center; gap: 10px;
}
.modal-no-comment { font-size: 13px; color: var(--text-faint); text-align: center; }
.modal-comment-text {
    font-size: 14px; color: #78350f; line-height: 1.75;
    background: #fef3c7; border: 1.5px solid #fde68a;
    border-radius: 12px; padding: 14px 16px;
}

/* FOOTER */
.modal-footer {
    padding: 16px 28px; border-top: 1.5px solid var(--border);
    display: flex; gap: 8px; justify-content: flex-end; background: #fafbfc;
}
.btn-modal-primary {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 22px; background: #001a6e; color: white;
    border: none; border-radius: 11px; font-size: 13.5px; font-weight: 700;
    cursor: pointer; font-family: var(--font); text-decoration: none; transition: all .15s;
}
.btn-modal-primary:hover { background: #00237a; transform: translateY(-1px); }
.btn-modal-secondary {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 20px; background: white; color: var(--text-muted);
    border: 1.5px solid var(--border); border-radius: 11px;
    font-size: 13.5px; cursor: pointer; font-family: var(--font); transition: all .15s;
}
.btn-modal-secondary:hover { background: #f1f5f9; }

/* ── DARK THEME OVERRIDES ── */
[data-theme="dark"] .card {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
}
[data-theme="dark"] .notif-item {
    border-bottom-color: rgba(30, 79, 216, 0.15) !important;
}
[data-theme="dark"] .notif-item:hover {
    background: rgba(30, 79, 216, 0.12) !important;
}
[data-theme="dark"] .notif-item--unread {
    background: rgba(30, 79, 216, 0.18) !important;
}
[data-theme="dark"] .notif-item--unread:hover {
    background: rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .notif-title {
    color: #f8fafc !important;
}
[data-theme="dark"] .notif-message {
    color: #94a3b8 !important;
}
[data-theme="dark"] .modal-overlay {
    background: rgba(0, 0, 0, 0.7) !important;
}
[data-theme="dark"] .notif-modal {
    background: #081636 !important;
    border: 1px solid rgba(30, 79, 216, 0.3) !important;
}
[data-theme="dark"] .modal-head {
    background: #06112a !important;
    border-bottom-color: rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .modal-title {
    color: #f8fafc !important;
}
[data-theme="dark"] .modal-left {
    border-right-color: rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .modal-req-title {
    color: #f8fafc !important;
}
[data-theme="dark"] .modal-message {
    color: #cbd5e1 !important;
}
[data-theme="dark"] .modal-right {
    background: #06112a !important;
}
[data-theme="dark"] .modal-right--empty {
    background: #06112a !important;
}
[data-theme="dark"] .modal-footer {
    background: #06112a !important;
    border-top-color: rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .btn-modal-secondary {
    background: #0b1a3d !important;
    border-color: rgba(30, 79, 216, 0.3) !important;
    color: #cbd5e1 !important;
}
[data-theme="dark"] .btn-modal-secondary:hover {
    background: #0f2250 !important;
    color: #f8fafc !important;
}
[data-theme="dark"] .empty-state__icon {
    background: #0b1a3d !important;
    border: 1.5px solid rgba(30, 79, 216, 0.3) !important;
    color: #60a5fa !important;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25) !important;
}
[data-theme="dark"] .empty-state p {
    color: #94a3b8 !important;
}
</style>
@endsection

@section('content')
<div class="page-outer">
<div class="page-inner">

    <div class="page-header">
        <h1>Notifications</h1>
        <p>Stay updated on your request statuses</p>
    </div>

    <div class="card">
        @forelse($notifications as $notif)
            @php
                $type = strtolower($notif->type ?? 'default');
                $icon_class = match(true) {
                    str_contains($type, 'approv')  => 'notif-icon--approved',
                    str_contains($type, 'post')    => 'notif-icon--posted',
                    str_contains($type, 'review')  => 'notif-icon--reviewed',
                    str_contains($type, 'reject')  => 'notif-icon--rejected',
                    str_contains($type, 'comment') => 'notif-icon--comment',
                    default                        => 'notif-icon--default',
                };
                $icon_svg = match(true) {
                    str_contains($type, 'approv')  => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
                    str_contains($type, 'post')    => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>',
                    str_contains($type, 'review')  => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
                    str_contains($type, 'reject')  => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
                    str_contains($type, 'comment') => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
                    default                        => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
                };
                $raw_message   = $notif->message;
                $admin_note    = '';
                $clean_message = $raw_message;
                if (str_contains($raw_message, ' Admin note: ')) {
                    [$clean_message, $admin_note] = explode(' Admin note: ', $raw_message, 2);
                }
                $status_badge_class = match(true) {
                    str_contains($type, 'approv')  => 'approved',
                    str_contains($type, 'post')    => 'posted',
                    str_contains($type, 'review')  => 'under-review',
                    str_contains($type, 'reject')  => 'rejected',
                    str_contains($type, 'pending') => 'pending',
                    default                        => 'default',
                };
                $status_label = match(true) {
                    str_contains($type, 'approv')  => 'Approved',
                    str_contains($type, 'post')    => 'Posted',
                    str_contains($type, 'review')  => 'Under Review',
                    str_contains($type, 'reject')  => 'Rejected',
                    str_contains($type, 'pending') => 'Pending',
                    default                        => 'Update',
                };
                $modal_data = json_encode([
                    'title'            => $notif->title,
                    'message'          => $clean_message,
                    'adminNote'        => $admin_note,
                    'time'             => $notif->created_at->format('F j, Y · g:i A'),
                    'iconClass'        => $icon_class,
                    'iconSvg'          => $icon_svg,
                    'statusBadgeClass' => $status_badge_class,
                    'statusLabel'      => $status_label,
                ]);
            @endphp
            <div class="notif-item {{ !$notif->is_read ? 'notif-item--unread' : '' }}"
                 onclick="openNotif({{ $modal_data }})">
                <div class="notif-icon {{ $icon_class }}">{!! $icon_svg !!}</div>
                <div class="notif-body">
                    <div class="notif-title">{{ $notif->title }}</div>
                    <div class="notif-message">{{ $clean_message }}</div>
                    @if($admin_note)
                        <div class="notif-admin-tag">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            Admin comment attached
                        </div>
                    @endif
                    <div class="notif-time">{{ $notif->created_at->format('M j, Y · g:i A') }}</div>
                </div>
                @if(!$notif->is_read)<span class="notif-dot"></span>@endif
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-state__icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </div>
                <p>No notifications yet.</p>
            </div>
        @endforelse
    </div>

</div>
</div>

<!-- MODAL -->
<div class="modal-overlay" id="notif-modal" onclick="closeNotif(event)">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-icon" id="modal-icon"></div>
            <div class="modal-header-text">
                <div class="modal-title" id="modal-title"></div>
                <div class="modal-time"  id="modal-time"></div>
            </div>
            <button class="modal-close" onclick="closeModalDirect()">&#10005;</button>
        </div>
        <div class="modal-body">
            <!-- LEFT: request status -->
            <div class="modal-left">
                <div class="modal-col-label">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Request Status
                </div>
                <div class="modal-status-badge modal-status-badge--default" id="modal-status-badge">
                    <span class="modal-status-dot"></span>
                    <span id="modal-status-label">Update</span>
                </div>
                <div class="modal-message" id="modal-message"></div>
            </div>
            <!-- RIGHT: admin comment -->
            <div class="modal-right modal-right--empty" id="modal-right">
                <div class="modal-col-label" id="modal-right-label" style="display:none;">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    Admin Comment
                </div>
                <div id="modal-comment-text" class="modal-comment-text" style="display:none;"></div>
                <svg id="modal-no-comment-icon" width="32" height="32" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <p class="modal-no-comment" id="modal-no-comment-text">No admin comment</p>
            </div>
        </div>
        <div class="modal-footer">
            <a href="{{ route('requestor.requests') }}" class="btn-modal-primary">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                View My Requests
            </a>
            <button class="btn-modal-secondary" onclick="closeModalDirect()">Close</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openNotif(data) {
    document.getElementById('modal-title').textContent  = data.title;
    document.getElementById('modal-time').textContent   = data.time;
    document.getElementById('modal-message').textContent = data.message;
    document.getElementById('modal-icon').className     = 'modal-icon ' + data.iconClass;
    document.getElementById('modal-icon').innerHTML     = data.iconSvg;

    const badge = document.getElementById('modal-status-badge');
    badge.className = 'modal-status-badge modal-status-badge--' + data.statusBadgeClass;
    document.getElementById('modal-status-label').textContent = data.statusLabel;

    const right      = document.getElementById('modal-right');
    const label      = document.getElementById('modal-right-label');
    const commentEl  = document.getElementById('modal-comment-text');
    const noIcon     = document.getElementById('modal-no-comment-icon');
    const noText     = document.getElementById('modal-no-comment-text');

    if (data.adminNote && data.adminNote.trim() !== '') {
        right.classList.remove('modal-right--empty');
        right.style.background   = '#fffdf5';
        label.style.display      = 'flex';
        commentEl.style.display  = 'block';
        commentEl.textContent    = data.adminNote;
        noIcon.style.display     = 'none';
        noText.style.display     = 'none';
    } else {
        right.classList.add('modal-right--empty');
        right.style.background   = '#fafbfc';
        label.style.display      = 'none';
        commentEl.style.display  = 'none';
        noIcon.style.display     = 'block';
        noText.style.display     = 'block';
    }

    document.getElementById('notif-modal').classList.add('modal-overlay--open');
    document.body.style.overflow = 'hidden';
}
function closeNotif(e) { if (e.target === document.getElementById('notif-modal')) closeModalDirect(); }
function closeModalDirect() {
    document.getElementById('notif-modal').classList.remove('modal-overlay--open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModalDirect(); });
</script>
@endsection