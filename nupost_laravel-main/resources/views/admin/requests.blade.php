@extends('layouts.admin')

@section('title', 'Request Management')

@section('head-styles')
<style>
.main { padding: 28px; }
.topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
.topbar h1 { font-size: 20px; font-weight: 700; letter-spacing: -0.3px; }
.admin-badge { display: flex; align-items: center; gap: 7px; padding: 6px 12px; background: white; border-radius: 8px; border: 1px solid var(--color-border); font-size: 12.5px; font-weight: 500; }
.stats { display: grid; grid-template-columns: repeat(5,1fr); gap: 14px; margin-bottom: 24px; }
.stat-card { background: white; border-radius: var(--radius); padding: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: var(--shadow-sm); border: 1.5px solid transparent; }
.stat-card--yellow { border-color: #fde68a; }
.stat-card--blue   { border-color: #bfdbfe; }
.stat-card--green  { border-color: #a7f3d0; }
.stat-card--purple { border-color: #ddd6fe; }
.stat-card--red    { border-color: #fecaca; }
.stat-card__label { font-size: 11px; color: var(--color-text-muted); margin-bottom: 4px; }
.stat-card__value { font-size: 24px; font-weight: 700; }
.stat-card__icon { width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.stat-card__icon--yellow { background: #fef3c7; color: #f59e0b; }
.stat-card__icon--blue   { background: #eff6ff; color: #3b82f6; }
.stat-card__icon--green  { background: #ecfdf5; color: #10b981; }
.stat-card__icon--purple { background: #f5f3ff; color: #8b5cf6; }
.stat-card__icon--red    { background: #fee2e2; color: #ef4444; }
/* FILTER BAR */
.filter-bar { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; }
.filter-bar input[type="text"], .filter-bar select { height: 36px; border: 1px solid var(--color-border); border-radius: 7px; padding: 0 12px; font-size: 13px; font-family: var(--font); background: white; color: var(--color-text); outline: none; }
.filter-bar input[type="text"] { flex: 1; min-width: 200px; }
.filter-bar input:focus, .filter-bar select:focus { border-color: var(--color-primary); }
.filter-btn { height: 36px; padding: 0 16px; background: var(--color-primary); color: white; border: none; border-radius: 7px; font-size: 13px; font-weight: 500; cursor: pointer; font-family: var(--font); }
.filter-btn:hover { background: var(--color-primary-light); }
.reset-btn { height: 36px; padding: 0 14px; background: white; color: var(--color-text-muted); border: 1px solid var(--color-border); border-radius: 7px; font-size: 13px; cursor: pointer; font-family: var(--font); text-decoration: none; display: flex; align-items: center; }
.reset-btn:hover { background: var(--color-bg); }
/* TABLE */
.table-card { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
.table-card-header { padding: 14px 20px; border-bottom: 1px solid var(--color-border); display: flex; align-items: center; justify-content: space-between; }
.table-card-header span { font-size: 12.5px; color: var(--color-text-muted); }
.table-card-header strong { color: var(--color-text); }
table { width: 100%; border-collapse: collapse; }
thead tr { border-bottom: 1px solid var(--color-border); }
th { padding: 10px 14px; text-align: left; font-size: 10.5px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .1s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #fafafa; }
td { padding: 12px 14px; font-size: 12.5px; vertical-align: middle; }
.req-title { font-weight: 600; color: var(--color-text); margin-bottom: 2px; }
.req-desc { font-size: 11px; color: var(--color-text-muted); max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.req-thumb { width: 36px; height: 36px; border-radius: 6px; object-fit: cover; }
.req-thumb-placeholder { width: 36px; height: 36px; border-radius: 6px; background: linear-gradient(135deg, #e5e7eb, #d1d5db); display: flex; align-items: center; justify-content: center; font-size: 14px; }
.badge { display: inline-flex; align-items: center; padding: 3px 9px; border-radius: 20px; font-size: 10.5px; font-weight: 600; white-space: nowrap; }
.badge--high         { background: #fee2e2; color: #dc2626; }
.badge--urgent       { background: #fef3c7; color: #b45309; }
.badge--medium       { background: #fef3c7; color: #d97706; }
.badge--low          { background: #f3f4f6; color: #6b7280; }
.badge--approved     { background: #dcfce7; color: #16a34a; }
.badge--posted       { background: #dbeafe; color: #2563eb; }
.badge--under-review { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
.badge--pending      { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
.badge--rejected     { background: #fee2e2; color: #dc2626; }
.status-select { padding: 4px 8px; border-radius: 6px; border: 1px solid var(--color-border); font-size: 11.5px; font-family: var(--font); background: white; color: var(--color-text); cursor: pointer; outline: none; }
.status-select:focus { border-color: var(--color-primary); }
.update-btn { padding: 5px 10px; background: var(--color-primary); color: white; border: none; border-radius: 5px; font-size: 11px; font-weight: 600; cursor: pointer; font-family: var(--font); }
.update-btn:hover { background: var(--color-primary-light); }
.date-text { font-size: 11.5px; color: var(--color-text-muted); white-space: nowrap; }
.empty-state { padding: 48px; text-align: center; color: #9ca3af; font-size: 13px; }
/* COMMENT BTN */
.comment-btn { padding: 5px 10px; background: white; color: var(--color-text-muted); border: 1px solid var(--color-border); border-radius: 5px; font-size: 11px; font-weight: 500; cursor: pointer; font-family: var(--font); display: inline-flex; align-items: center; gap: 4px; transition: all .15s; }
.comment-btn:hover { background: #f0f4ff; border-color: var(--color-primary); color: var(--color-primary); }
.comment-badge { display: inline-flex; align-items: center; justify-content: center; width: 16px; height: 16px; background: var(--color-primary); color: white; border-radius: 50%; font-size: 9px; font-weight: 700; }
/* COMMENT PANEL */
.panel-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.3); z-index: 199; }
.panel-overlay.open { display: block; }
.comment-panel { display: none; position: fixed; top: 0; right: 0; bottom: 0; width: 400px; background: white; box-shadow: -4px 0 24px rgba(0,0,0,0.12); z-index: 200; flex-direction: column; }
.comment-panel.open { display: flex; }
.comment-panel__header { padding: 18px 20px; border-bottom: 1px solid var(--color-border); display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; background: var(--color-primary); }
.comment-panel__title { font-size: 14px; font-weight: 700; color: white; display: flex; align-items: center; gap: 8px; }
.comment-panel__close { background: rgba(255,255,255,0.15); border: none; cursor: pointer; color: white; padding: 6px; border-radius: 6px; display: flex; align-items: center; }
.comment-panel__close:hover { background: rgba(255,255,255,0.25); }
.comment-panel__req { padding: 12px 20px; background: #f8faff; border-bottom: 1px solid var(--color-border); flex-shrink: 0; }
.comment-panel__req-title { font-size: 13px; font-weight: 600; color: var(--color-text); margin-bottom: 4px; }
.comment-panel__req-meta { display: flex; align-items: center; gap: 8px; font-size: 11.5px; color: var(--color-text-muted); }
.comment-panel__list { flex: 1; overflow-y: auto; padding: 16px 20px; display: flex; flex-direction: column; gap: 12px; background: #f8faff; }
.comment-panel__list::-webkit-scrollbar { width: 4px; }
.comment-panel__list::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
/* Chat bubbles */
.chat-bubble-row { display: flex; align-items: flex-end; gap: 8px; }
.chat-bubble-row--admin { flex-direction: row-reverse; }
.chat-avatar { width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: white; }
.chat-avatar--admin      { background: var(--color-primary); }
.chat-avatar--requestor  { background: #7c3aed; }
.chat-bubble { max-width: 78%; padding: 9px 13px; border-radius: 14px; font-size: 13px; line-height: 1.55; word-break: break-word; }
.chat-bubble--admin     { background: var(--color-primary); color: white; border-bottom-right-radius: 4px; }
.chat-bubble--requestor { background: white; color: var(--color-text); border: 1px solid var(--color-border); border-bottom-left-radius: 4px; }
.chat-meta { font-size: 10px; margin-top: 4px; opacity: .7; }
.chat-bubble--admin .chat-meta     { text-align: right; color: rgba(255,255,255,.8); }
.chat-bubble--requestor .chat-meta { color: #9ca3af; }
.chat-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #9ca3af; padding: 32px 0; }
.chat-empty p { font-size: 13px; }
/* Comment form */
.comment-panel__form { padding: 14px 20px; border-top: 1px solid var(--color-border); flex-shrink: 0; background: white; }
.comment-panel__textarea { width: 100%; border: 1px solid var(--color-border); border-radius: 10px; padding: 10px 12px; font-size: 13px; font-family: var(--font); resize: none; outline: none; height: 80px; transition: border-color .15s; }
.comment-panel__textarea:focus { border-color: var(--color-primary); }
.comment-panel__send { width: 100%; margin-top: 8px; padding: 10px; background: var(--color-primary); color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: var(--font); display: flex; align-items: center; justify-content: center; gap: 6px; }
.comment-panel__send:hover { background: var(--color-primary-light); }
/* TOAST */
.toast { position: fixed; bottom: 24px; right: 24px; z-index: 999; background: #002366; color: white; padding: 12px 20px; border-radius: 10px; font-size: 13px; font-weight: 500; box-shadow: 0 4px 16px rgba(0,0,0,0.2); display: flex; align-items: center; gap: 10px; animation: slideIn .3s ease, fadeOut .4s ease 2.6s forwards; }
.toast--success { background: #059669; }
.toast--reject  { background: #dc2626; }
.toast--comment { background: #7c3aed; }
@keyframes slideIn { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes fadeOut { from { opacity: 1; } to { opacity: 0; pointer-events: none; } }
</style>
@endsection

@section('content')
<div class="main">
    <div class="topbar">
        <h1>Request Management</h1>
        <div class="admin-badge">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            {{ session('admin_email', 'admin@nupost.com') }}
        </div>
    </div>

    <!-- STAT CARDS -->
    <div class="stats">
        <div class="stat-card stat-card--yellow">
            <div><div class="stat-card__label">Pending Review</div><div class="stat-card__value">{{ $pending }}</div></div>
            <div class="stat-card__icon stat-card__icon--yellow">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <div class="stat-card stat-card--blue">
            <div><div class="stat-card__label">Under Review</div><div class="stat-card__value">{{ $review }}</div></div>
            <div class="stat-card__icon stat-card__icon--blue">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
        </div>
        <div class="stat-card stat-card--green">
            <div><div class="stat-card__label">Approved</div><div class="stat-card__value">{{ $approved }}</div></div>
            <div class="stat-card__icon stat-card__icon--green">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <div class="stat-card stat-card--purple">
            <div><div class="stat-card__label">Posted</div><div class="stat-card__value">{{ $posted }}</div></div>
            <div class="stat-card__icon stat-card__icon--purple">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </div>
        </div>
        <div class="stat-card stat-card--red">
            <div><div class="stat-card__label">Rejected</div><div class="stat-card__value">{{ $rejected }}</div></div>
            <div class="stat-card__icon stat-card__icon--red">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
        </div>
    </div>

    <!-- FILTER BAR -->
    <form method="GET" action="{{ route('admin.requests') }}" class="filter-bar">
        <input type="text" name="search" placeholder="Search title, requester, category..."
               value="{{ $search }}">
        <select name="filter">
            <option value="all"      {{ $filter === 'all'      ? 'selected' : '' }}>All Requests</option>
            <option value="pending"  {{ $filter === 'pending'  ? 'selected' : '' }}>Pending Review</option>
            <option value="review"   {{ $filter === 'review'   ? 'selected' : '' }}>Under Review</option>
            <option value="approved" {{ $filter === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="posted"   {{ $filter === 'posted'   ? 'selected' : '' }}>Posted</option>
            <option value="rejected" {{ $filter === 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
        <select name="sort">
            <option value="newest"   {{ $sort === 'newest'   ? 'selected' : '' }}>Newest First</option>
            <option value="oldest"   {{ $sort === 'oldest'   ? 'selected' : '' }}>Oldest First</option>
            <option value="priority" {{ $sort === 'priority' ? 'selected' : '' }}>By Priority</option>
        </select>
        <button type="submit" class="filter-btn">Apply</button>
        <a href="{{ route('admin.requests') }}" class="reset-btn">Reset</a>
    </form>

    <!-- TABLE -->
    <div class="table-card">
        <div class="table-card-header">
            <span>Showing <strong>{{ $total }}</strong> request{{ $total !== 1 ? 's' : '' }}</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th>REQUEST</th>
                    <th>REQUESTER</th>
                    <th>CATEGORY</th>
                    <th>PRIORITY</th>
                    <th>STATUS</th>
                    <th>DATE</th>
                    <th>ACTIONS</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
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
                        $thumb = $req->first_media;
                        $comment_count = $req->comments()->count();
                    @endphp
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                @if($thumb)
                                    <img class="req-thumb" src="/uploads/{{ $thumb }}" alt=""
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <div class="req-thumb-placeholder" style="display:none;">📄</div>
                                @else
                                    <div class="req-thumb-placeholder">📄</div>
                                @endif
                                <div>
                                    <div class="req-title">{{ Str::limit($req->title, 35) }}</div>
                                    <div class="req-desc">{{ Str::limit($req->description, 60) }}</div>
                                </div>
                            </div>
                        </td>
                        <td>{{ $req->requester }}</td>
                        <td>{{ $req->category }}</td>
                        <td><span class="badge badge--{{ $priority_class }}">{{ strtoupper($req->priority) }}</span></td>
                        <td><span class="badge badge--{{ $status_class }}">{{ $req->status }}</span></td>
                        <td class="date-text">{{ $req->created_at->format('M j, Y') }}</td>
                        <td>
                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                <!-- STATUS UPDATE -->
                                <form method="POST" action="{{ route('admin.requests.status') }}"
                                      style="display:flex;align-items:center;gap:4px;"
                                      onsubmit="return confirmStatus(this)">
                                    @csrf
                                    <input type="hidden" name="request_id" value="{{ $req->id }}">
                                    <select class="status-select" name="status">
                                        @foreach(['Pending Review','Under Review','Approved','Posted','Rejected'] as $s)
                                            <option value="{{ $s }}" {{ $req->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="update-btn">Save</button>
                                </form>
                                <!-- CHAT BUTTON -->
                                <button class="comment-btn"
                                        onclick="openChat({{ $req->id }}, {{ json_encode($req->title) }}, {{ json_encode($req->status) }}, {{ json_encode($req->requester) }})">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                    Chat
                                    @if($comment_count > 0)
                                        <span class="comment-badge">{{ $comment_count }}</span>
                                    @endif
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">No requests found.</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- OVERLAY -->
<div class="panel-overlay" id="panel-overlay" onclick="closeChat()"></div>

<!-- CHAT PANEL -->
<div class="comment-panel" id="comment-panel">
    <div class="comment-panel__header">
        <div class="comment-panel__title">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Chat with Requestor
        </div>
        <button class="comment-panel__close" onclick="closeChat()">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <div class="comment-panel__req">
        <div class="comment-panel__req-title" id="panel-req-title">—</div>
        <div class="comment-panel__req-meta">
            <span id="panel-req-requester"></span>
            <span id="panel-req-status"></span>
        </div>
    </div>
    <div class="comment-panel__list" id="chat-list">
        <div class="chat-empty">
            <svg width="32" height="32" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <p>No messages yet.</p>
        </div>
    </div>
    <div class="comment-panel__form">
        <form id="chat-form" onsubmit="sendComment(event)">
            @csrf
            <input type="hidden" id="chat-req-id" value="">
            <textarea class="comment-panel__textarea" id="chat-message"
                      placeholder="Type a message to the requestor..."
                      onkeydown="handleKey(event)"></textarea>
            <button type="submit" class="comment-panel__send">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Send Message
            </button>
        </form>
    </div>
</div>

@if(session('success'))
<div class="toast toast--success" id="toast">✅ {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="toast toast--reject" id="toast">❌ {{ session('error') }}</div>
@endif

@endsection

@section('scripts')
<script>
let currentReqId = null;

function openChat(reqId, title, status, requester) {
    currentReqId = reqId;
    document.getElementById('chat-req-id').value = reqId;
    document.getElementById('panel-req-title').textContent = title;
    document.getElementById('panel-req-requester').textContent = requester;
    document.getElementById('panel-req-status').textContent = status;
    document.getElementById('comment-panel').classList.add('open');
    document.getElementById('panel-overlay').classList.add('open');
    document.body.style.overflow = 'hidden';
    loadChat(reqId);
}

function closeChat() {
    document.getElementById('comment-panel').classList.remove('open');
    document.getElementById('panel-overlay').classList.remove('open');
    document.body.style.overflow = '';
    currentReqId = null;
}

function loadChat(reqId) {
    const list = document.getElementById('chat-list');
    list.innerHTML = '<div class="chat-empty"><p>Loading…</p></div>';

    fetch(`/admin/requests/${reqId}/comments`, {
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        const comments = data.comments || [];
        if (!comments.length) {
            list.innerHTML = '<div class="chat-empty"><svg width="32" height="32" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg><p>No messages yet. Start the conversation!</p></div>';
            return;
        }
        list.innerHTML = comments.map(c => {
            const isAdmin = c.sender_role === 'admin';
            return `
            <div class="chat-bubble-row ${isAdmin ? 'chat-bubble-row--admin' : ''}">
                <div class="chat-avatar ${isAdmin ? 'chat-avatar--admin' : 'chat-avatar--requestor'}">
                    ${esc(c.sender_name.charAt(0).toUpperCase())}
                </div>
                <div class="chat-bubble ${isAdmin ? 'chat-bubble--admin' : 'chat-bubble--requestor'}">
                    ${esc(c.message)}
                    <div class="chat-meta">${isAdmin ? 'Admin' : esc(c.sender_name)} · ${esc(c.created_at)}</div>
                </div>
            </div>`;
        }).join('');
        list.scrollTop = list.scrollHeight;
    })
    .catch(() => {
        list.innerHTML = '<div class="chat-empty"><p>Could not load messages.</p></div>';
    });
}

async function sendComment(e) {
    e.preventDefault();
    const message = document.getElementById('chat-message').value.trim();
    if (!message || !currentReqId) return;

    try {
        const res = await fetch('/admin/requests/comment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ request_id: currentReqId, message })
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('chat-message').value = '';
            loadChat(currentReqId);
        } else {
            alert('Failed to send: ' + (data.message || 'Unknown error'));
        }
    } catch(err) {
        alert('Connection error.');
    }
}

function handleKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        if (document.getElementById('chat-message').value.trim()) {
            sendComment(e);
        }
    }
}

function confirmStatus(form) {
    const select = form.querySelector('select[name="status"]');
    const status = select.value;
    return confirm(`Update status to "${status}"?`);
}

function esc(str) {
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// Auto-dismiss toast
setTimeout(() => document.getElementById('toast')?.remove(), 3000);
</script>
@endsection