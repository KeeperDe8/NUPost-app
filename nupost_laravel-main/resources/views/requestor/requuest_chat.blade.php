@extends('layouts.requestor')

@section('title', 'Comments')

@section('head-styles')
<style>
.main { max-width: 700px; margin: 0 auto; padding: 28px 24px; }
.back-btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; background: white; border: 1px solid var(--color-border); border-radius: 8px; font-size: 13px; font-weight: 500; color: var(--color-text-muted); text-decoration: none; transition: background .15s; margin-bottom: 20px; }
.back-btn:hover { background: var(--color-bg); }
.req-strip { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.req-strip-title { font-size: 14.5px; font-weight: 700; }
.req-strip-id    { font-size: 11px; color: var(--color-text-muted); margin-top: 2px; }
.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; white-space: nowrap; }
.badge--approved      { background: #dcfce7; color: #16a34a; }
.badge--posted        { background: #dbeafe; color: #2563eb; }
.badge--under-review  { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
.badge--pending       { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
.badge--rejected      { background: #fee2e2; color: #dc2626; }
.chat-card { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; display: flex; flex-direction: column; height: 560px; }
.chat-header { padding: 14px 18px; background: var(--color-primary); color: white; display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.chat-header-dot { width: 8px; height: 8px; border-radius: 50%; background: #4ade80; }
.chat-header-title { font-size: 13.5px; font-weight: 600; }
.chat-header-sub { font-size: 11px; opacity: .7; }
.chat-messages { flex: 1; overflow-y: auto; padding: 20px 18px; display: flex; flex-direction: column; gap: 14px; background: #f8faff; }
.chat-messages::-webkit-scrollbar { width: 4px; }
.chat-messages::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
.chat-bubble-row { display: flex; align-items: flex-end; gap: 8px; }
.chat-bubble-row--me { flex-direction: row-reverse; }
.chat-avatar { width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: white; }
.chat-avatar--me    { background: #7c3aed; }
.chat-avatar--admin { background: var(--color-primary); }
.chat-bubble { max-width: 72%; padding: 10px 14px; border-radius: 14px; font-size: 13px; line-height: 1.55; word-break: break-word; }
.chat-bubble--me    { background: #7c3aed; color: white; border-bottom-right-radius: 4px; }
.chat-bubble--admin { background: white; color: var(--color-text); border: 1px solid var(--color-border); border-bottom-left-radius: 4px; }
.chat-meta { font-size: 10.5px; margin-top: 4px; opacity: .65; }
.chat-bubble--me .chat-meta    { text-align: right; color: rgba(255,255,255,.8); }
.chat-bubble--admin .chat-meta { color: #9ca3af; }
.chat-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; color: #9ca3af; }
.chat-empty p { font-size: 13px; }
.chat-input-area { border-top: 1px solid var(--color-border); padding: 14px 16px; background: white; display: flex; gap: 10px; align-items: flex-end; flex-shrink: 0; }
.chat-input { flex: 1; border: 1px solid var(--color-border); border-radius: 10px; padding: 10px 14px; font-size: 13px; font-family: var(--font); resize: none; outline: none; transition: border-color .15s; color: var(--color-text); min-height: 42px; max-height: 120px; }
.chat-input:focus { border-color: #7c3aed; }
.chat-send-btn { width: 42px; height: 42px; background: #7c3aed; color: white; border: none; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.chat-send-btn:hover { background: #6d28d9; }
</style>
@endsection

@section('content')
<main class="main">
    <a href="{{ route('requestor.requests') }}" class="back-btn">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        Back to Requests
    </a>

    @php
        $status_raw   = strtolower($req->status);
        $status_class = match(true) {
            str_contains($status_raw, 'approved')     => 'badge--approved',
            str_contains($status_raw, 'posted')       => 'badge--posted',
            str_contains($status_raw, 'under review') => 'badge--under-review',
            str_contains($status_raw, 'rejected')     => 'badge--rejected',
            default                                   => 'badge--pending',
        };
    @endphp

    <div class="req-strip">
        <div>
            <div class="req-strip-title">{{ $req->title }}</div>
            <div class="req-strip-id">{{ $req->request_id ?? 'REQ-' . $req->id }}</div>
        </div>
        <span class="badge {{ $status_class }}">{{ $req->status }}</span>
    </div>

    <div class="chat-card">
        <div class="chat-header">
            <div class="chat-header-dot"></div>
            <div>
                <div class="chat-header-title">Comments with Admin</div>
                <div class="chat-header-sub">Messages here are visible to admin</div>
            </div>
        </div>

        <div class="chat-messages" id="chat-messages">
            @forelse($chat_messages as $msg)
                @php $is_me = $msg->sender_role === 'requestor'; @endphp
                <div class="chat-bubble-row {{ $is_me ? 'chat-bubble-row--me' : '' }}">
                    <div class="chat-avatar {{ $is_me ? 'chat-avatar--me' : 'chat-avatar--admin' }}">
                        {{ strtoupper(substr($msg->sender_name, 0, 1)) }}
                    </div>
                    <div class="chat-bubble {{ $is_me ? 'chat-bubble--me' : 'chat-bubble--admin' }}">
                        {!! nl2br(e($msg->message)) !!}
                        <div class="chat-meta">
                            {{ $is_me ? 'You' : $msg->sender_name }} &middot; {{ $msg->created_at->format('M j, g:i A') }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="chat-empty">
                    <svg width="36" height="36" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    <p>No messages yet. Ask admin a question here!</p>
                </div>
            @endforelse
        </div>

        <form method="POST" action="{{ route('requestor.requests.chat.send', $req->id) }}" class="chat-input-area" id="chat-form">
            @csrf
            <textarea class="chat-input" name="chat_message" id="chat-input"
                      placeholder="Send a message or question to admin..."
                      rows="1"
                      onkeydown="handleChatKey(event)"></textarea>
            <button type="submit" class="chat-send-btn" title="Send">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </form>
    </div>
</main>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const chatBox = document.getElementById('chat-messages');
    if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
});
function handleChatKey(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        const val = document.getElementById('chat-input').value.trim();
        if (val) document.getElementById('chat-form').submit();
    }
}
</script>
@endsection