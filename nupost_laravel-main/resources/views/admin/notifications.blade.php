@extends('layouts.admin')

@section('title', 'Notifications')

@section('head-styles')
<style>
/* ── PAGE WRAPPER ───────────────────── */
.page-outer {
    min-height: calc(100vh - 64px);
    display: flex; flex-direction: column;
    align-items: center;
    padding: 36px 24px 60px;
}
.page-inner { width: 100%; max-width: 860px; }

/* PAGE HEADER */
.page-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 24px;
}
.page-header__left h1 {
    font-family: var(--font-disp);
    font-size: 26px; font-weight: 400;
    letter-spacing: -0.3px; color: var(--ink);
}
.page-header__left p { font-size: 13.5px; color: var(--ink-soft); margin-top: 4px; }

.btn-mark-all {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 18px; background: var(--card);
    border: 1.5px solid rgba(0,0,0,0.09); border-radius: 11px;
    font-size: 13px; font-weight: 600; color: var(--ink-mid);
    cursor: pointer; font-family: var(--font); transition: all .15s;
}
.btn-mark-all:hover { background: var(--cream-dark); color: var(--ink); }

/* ── FILTER TABS ────────────────────── */
.filter-tabs {
    display: flex; gap: 6px; margin-bottom: 18px;
}
.filter-tab {
    padding: 7px 16px; border-radius: 20px;
    font-size: 12.5px; font-weight: 600;
    cursor: pointer; border: 1.5px solid transparent;
    transition: all .15s; background: var(--card);
    color: var(--ink-soft); border-color: rgba(0,0,0,0.08);
}
.filter-tab.active {
    background: var(--navy); color: white; border-color: var(--navy);
}
.filter-tab:hover:not(.active) { background: var(--cream-dark); }

/* ── CARD ───────────────────────────── */
.notif-card {
    background: var(--card); border-radius: 20px;
    border: 1.5px solid rgba(0,0,0,0.07);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

/* ── NOTIF ROWS ─────────────────────── */
.notif-row {
    display: flex; align-items: flex-start; gap: 16px;
    padding: 20px 26px; border-bottom: 1px solid rgba(0,0,0,0.05);
    transition: background .12s; cursor: pointer; position: relative;
    text-decoration: none; color: var(--ink);
}
.notif-row:last-child { border-bottom: none; }
.notif-row:hover { background: var(--cream); }
.notif-row--unread { background: rgba(0,35,102,0.025); }
.notif-row--unread:hover { background: rgba(0,35,102,0.045); }
.notif-row--unread::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0;
    width: 3px; background: var(--navy-light); border-radius: 0 3px 3px 0;
}

.notif-unread-dot {
    position: absolute; top: 22px; right: 22px;
    width: 9px; height: 9px; border-radius: 50%;
    background: var(--navy-light);
    box-shadow: 0 0 0 3px rgba(30,79,216,0.15);
}

/* Icons */
.n-icon {
    width: 44px; height: 44px; border-radius: 13px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; margin-top: 2px;
}
.n-icon--request { background: #dbeafe; color: #2563eb; }
.n-icon--comment { background: #dcfce7; color: #16a34a; }
.n-icon--default { background: var(--cream-dark); color: var(--ink-soft); }

.notif-body { flex: 1; min-width: 0; padding-right: 28px; }
.notif-body__title {
    font-size: 14px; font-weight: 700; color: var(--ink);
    margin-bottom: 4px; line-height: 1.4;
}
.notif-body__msg {
    font-size: 13px; color: var(--ink-soft);
    line-height: 1.6; margin-bottom: 6px;
}
.notif-body__time { font-size: 11.5px; color: var(--ink-faint); }

.notif-type-tag {
    display: inline-flex; align-items: center; gap: 5px;
    margin-top: 6px; font-size: 11.5px; font-weight: 600;
    padding: 3px 10px; border-radius: 20px;
}
.notif-type-tag--request {
    background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;
}
.notif-type-tag--comment {
    background: #dcfce7; color: #166534; border: 1px solid #bbf7d0;
}

/* EMPTY */
.empty-state {
    padding: 72px 20px; text-align: center;
    display: flex; flex-direction: column; align-items: center; gap: 14px;
}
.empty-state__icon {
    width: 64px; height: 64px; border-radius: 18px;
    background: var(--cream-dark);
    display: flex; align-items: center;
    justify-content: center; color: var(--ink-faint);
}
.empty-state p { font-size: 14px; color: var(--ink-faint); }

/* Hidden rows (filter) */
.notif-row.hidden { display: none; }
</style>
@endsection

@section('content')
<div class="page-outer">
<div class="page-inner">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-header__left">
            <h1>Notifications</h1>
            <p>New requests and replies from requestors</p>
        </div>
        <button class="btn-mark-all" onclick="markAllReadPage()">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            Mark all as read
        </button>
    </div>

    {{-- Filter Tabs --}}
    <div class="filter-tabs">
        <div class="filter-tab active" data-filter="all" onclick="filterNotifs('all', this)">All</div>
        <div class="filter-tab" data-filter="new_request" onclick="filterNotifs('new_request', this)">
            📄 New Requests
        </div>
        <div class="filter-tab" data-filter="comment" onclick="filterNotifs('comment', this)">
            💬 Replies
        </div>
    </div>

    {{-- Notifications Card --}}
    <div class="notif-card" id="notif-card">

        @forelse($notifications as $notif)
            @php
                $type      = $notif['type'] ?? 'default';
                $isUnread  = !($notif['is_read'] ?? false);
                $requestId = $notif['request_id'] ?? null;
                $link      = $requestId ? route('admin.requests.show', $requestId) : route('admin.requests');
                $notifId   = $notif['id'];

                $iconClass = match($type) {
                    'new_request' => 'n-icon--request',
                    'comment'     => 'n-icon--comment',
                    default       => 'n-icon--default',
                };

                $iconSvg = match($type) {
                    'new_request' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
                    'comment'     => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
                    default       => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
                };

                $tagClass = match($type) {
                    'new_request' => 'notif-type-tag--request',
                    'comment'     => 'notif-type-tag--comment',
                    default       => '',
                };

                $tagLabel = match($type) {
                    'new_request' => 'New Request',
                    'comment'     => 'Requestor Reply',
                    default       => 'Notification',
                };

                $createdAt = isset($notif['created_at'])
                    ? \Carbon\Carbon::parse($notif['created_at'])->format('M j, Y · g:i A')
                    : '';
            @endphp

            <a href="{{ $link }}"
               class="notif-row {{ $isUnread ? 'notif-row--unread' : '' }}"
               data-type="{{ $type }}"
               data-id="{{ $notifId }}"
               onclick="markReadPage('{{ $notifId }}', this)">

                <div class="n-icon {{ $iconClass }}">{!! $iconSvg !!}</div>

                <div class="notif-body">
                    <div class="notif-body__title">{{ $notif['title'] }}</div>
                    <div class="notif-body__msg">{{ $notif['message'] }}</div>
                    @if($tagClass)
                        <div class="notif-type-tag {{ $tagClass }}">{{ $tagLabel }}</div>
                    @endif
                    <div class="notif-body__time">{{ $createdAt }}</div>
                </div>

                @if($isUnread)
                    <span class="notif-unread-dot"></span>
                @endif
            </a>

        @empty
            <div class="empty-state">
                <div class="empty-state__icon">
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                </div>
                <p>No notifications yet.</p>
            </div>
        @endforelse

    </div>

</div>
</div>
@endsection

@section('scripts')
<script>
const NOTIF_READ_URL = '{{ route("admin.notifications.read") }}';
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

// ── Filter tabs ──────────────────────────────────────────────────
function filterNotifs(type, tabEl) {
    document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
    tabEl.classList.add('active');

    document.querySelectorAll('.notif-row').forEach(row => {
        if (type === 'all' || row.dataset.type === type) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });
}

// ── Mark single as read ──────────────────────────────────────────
function markReadPage(id, el) {
    if (!el.classList.contains('notif-row--unread')) return;

    fetch(NOTIF_READ_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id })
    }).then(() => {
        el.classList.remove('notif-row--unread');
        const dot = el.querySelector('.notif-unread-dot');
        if (dot) dot.remove();
        // Refresh bell badge too
        if (typeof loadNotifications === 'function') loadNotifications();
    }).catch(() => {});
}

// ── Mark all as read ─────────────────────────────────────────────
function markAllReadPage() {
    fetch(NOTIF_READ_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ all: true })
    }).then(() => {
        document.querySelectorAll('.notif-row--unread').forEach(row => {
            row.classList.remove('notif-row--unread');
            const dot = row.querySelector('.notif-unread-dot');
            if (dot) dot.remove();
        });
        if (typeof loadNotifications === 'function') loadNotifications();
    }).catch(() => {});
}
</script>
@endsection