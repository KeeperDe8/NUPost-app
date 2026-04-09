@extends('layouts.requestor')

@section('title', 'Notifications')

@section('head-styles')
<style>
.main { max-width: 680px; margin: 0 auto; padding: 32px 24px; }
.page-header { margin-bottom: 20px; }
.page-header h1 { font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
.page-header p { font-size: 13px; color: var(--color-text-muted); margin-top: 3px; }
.card { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
.notif-item {
    display: flex; align-items: flex-start; gap: 14px;
    padding: 16px 20px; border-bottom: 1px solid #f3f4f6;
    transition: background .1s; cursor: pointer; position: relative;
}
.notif-item:last-child { border-bottom: none; }
.notif-item:hover { background: #fafafa; }
.notif-item--unread { background: #f0f5ff; }
.notif-item--unread:hover { background: #e8f0fe; }
.notif-dot { position: absolute; top: 18px; right: 16px; width: 8px; height: 8px; border-radius: 50%; background: #3b82f6; }
.notif-icon { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
.notif-icon--approved  { background: #dcfce7; color: #16a34a; }
.notif-icon--posted    { background: #ede9fe; color: #7c3aed; }
.notif-icon--reviewed  { background: #dbeafe; color: #2563eb; }
.notif-icon--rejected  { background: #fee2e2; color: #dc2626; }
.notif-icon--comment   { background: #fef3c7; color: #d97706; }
.notif-icon--default   { background: #f3f4f6; color: #6b7280; }
.notif-body { flex: 1; min-width: 0; padding-right: 16px; }
.notif-title { font-size: 13.5px; font-weight: 600; color: var(--color-text); margin-bottom: 3px; }
.notif-message { font-size: 12.5px; color: var(--color-text-muted); line-height: 1.5; margin-bottom: 4px; }
.notif-time { font-size: 11px; color: #9ca3af; }
.empty-state { padding: 60px 20px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 12px; }
.empty-state p { font-size: 13px; color: #9ca3af; }
/* MODAL */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 200; align-items: center; justify-content: center; padding: 20px; }
.modal-overlay--open { display: flex; }
.modal { background: white; border-radius: 14px; max-width: 520px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.2); overflow: hidden; animation: modalIn .18s ease; }
@keyframes modalIn { from { opacity:0; transform:scale(.96) translateY(8px); } to { opacity:1; transform:scale(1) translateY(0); } }
.modal-header { display: flex; align-items: center; gap: 14px; padding: 20px 22px; border-bottom: 1px solid var(--color-border); }
.modal-icon { width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
.modal-header-text { flex: 1; }
.modal-title { font-size: 15px; font-weight: 700; color: var(--color-text); margin-bottom: 2px; }
.modal-time { font-size: 11.5px; color: #9ca3af; }
.modal-close { width: 32px; height: 32px; border-radius: 8px; border: none; background: var(--color-bg); cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--color-text-muted); font-size: 16px; flex-shrink: 0; }
.modal-close:hover { background: #e5e7eb; }
.modal-body { padding: 22px; }
.modal-message { font-size: 14px; color: var(--color-text); line-height: 1.75; margin-bottom: 16px; }
.modal-comment-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 16px; margin-bottom: 16px; }
.modal-comment-label { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 700; color: #92400e; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 8px; }
.modal-comment-text { font-size: 13.5px; color: #78350f; line-height: 1.65; }
.modal-footer { padding: 16px 22px; border-top: 1px solid var(--color-border); display: flex; gap: 8px; justify-content: flex-end; }
.btn-modal-primary { display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px; background: var(--color-primary); color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: var(--font); text-decoration: none; }
.btn-modal-primary:hover { background: var(--color-primary-light); }
.btn-modal-secondary { display: inline-flex; align-items: center; gap: 6px; padding: 9px 16px; background: white; color: var(--color-text-muted); border: 1px solid var(--color-border); border-radius: 8px; font-size: 13px; cursor: pointer; font-family: var(--font); }
.btn-modal-secondary:hover { background: var(--color-bg); }
</style>
@endsection

@section('content')
<main class="main">
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
                    str_contains($type, 'approv')  => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
                    str_contains($type, 'post')    => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>',
                    str_contains($type, 'review')  => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>',
                    str_contains($type, 'reject')  => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
                    str_contains($type, 'comment') => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
                    default                        => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
                };
                $raw_message   = $notif->message;
                $admin_note    = '';
                $clean_message = $raw_message;
                if (str_contains($raw_message, ' Admin note: ')) {
                    [$clean_message, $admin_note] = explode(' Admin note: ', $raw_message, 2);
                }
                $modal_data = json_encode([
                    'title'     => $notif->title,
                    'message'   => $clean_message,
                    'adminNote' => $admin_note,
                    'time'      => $notif->created_at->format('F j, Y g:i A'),
                    'iconClass' => $icon_class,
                    'iconSvg'   => $icon_svg,
                ]);
            @endphp
            <div class="notif-item {{ !$notif->is_read ? 'notif-item--unread' : '' }}"
                 onclick="openNotif({{ $modal_data }})">
                <div class="notif-icon {{ $icon_class }}">{!! $icon_svg !!}</div>
                <div class="notif-body">
                    <div class="notif-title">{{ $notif->title }}</div>
                    <div class="notif-message">{{ $clean_message }}</div>
                    @if($admin_note)
                        <div style="display:inline-flex;align-items:center;gap:4px;margin-top:4px;font-size:11px;color:#92400e;background:#fef3c7;padding:2px 8px;border-radius:20px;font-weight:600;">
                            Admin comment attached — click to view
                        </div>
                    @endif
                    <div class="notif-time">{{ $notif->created_at->format('n/j/Y, g:i A') }}</div>
                </div>
                @if(!$notif->is_read)
                    <span class="notif-dot"></span>
                @endif
            </div>
        @empty
            <div class="empty-state">
                <svg width="40" height="40" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <p>No notifications yet.</p>
            </div>
        @endforelse
    </div>
</main>

<!-- MODAL -->
<div class="modal-overlay" id="notif-modal" onclick="closeNotif(event)">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-icon" id="modal-icon"></div>
            <div class="modal-header-text">
                <div class="modal-title" id="modal-title"></div>
                <div class="modal-time" id="modal-time"></div>
            </div>
            <button class="modal-close" onclick="closeModalDirect()">&#10005;</button>
        </div>
        <div class="modal-body">
            <div class="modal-message" id="modal-message"></div>
            <div class="modal-comment-box" id="modal-comment-box" style="display:none;">
                <div class="modal-comment-label">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    Comment from Admin
                </div>
                <div class="modal-comment-text" id="modal-comment-text"></div>
            </div>
        </div>
        <div class="modal-footer">
            <a href="{{ route('requestor.requests') }}" class="btn-modal-primary">View My Requests</a>
            <button class="btn-modal-secondary" onclick="closeModalDirect()">Close</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function openNotif(data) {
    document.getElementById('modal-title').textContent   = data.title;
    document.getElementById('modal-time').textContent    = data.time;
    document.getElementById('modal-message').textContent = data.message;
    document.getElementById('modal-icon').className      = 'modal-icon ' + data.iconClass;
    document.getElementById('modal-icon').innerHTML      = data.iconSvg;
    const commentBox  = document.getElementById('modal-comment-box');
    const commentText = document.getElementById('modal-comment-text');
    if (data.adminNote && data.adminNote.trim() !== '') {
        commentText.textContent  = data.adminNote;
        commentBox.style.display = 'block';
    } else {
        commentBox.style.display = 'none';
    }
    document.getElementById('notif-modal').classList.add('modal-overlay--open');
    document.body.style.overflow = 'hidden';
}
function closeNotif(e) { if (e.target === document.getElementById('notif-modal')) closeModalDirect(); }
function closeModalDirect() { document.getElementById('notif-modal').classList.remove('modal-overlay--open'); document.body.style.overflow = ''; }
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModalDirect(); });
</script>
@endsection