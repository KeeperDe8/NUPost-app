@extends('layouts.requestor')

@section('title', 'Comments')

@section('head-styles')
<style>
    .main { max-width: 700px; margin: 0 auto; padding: 28px 24px; }
    
    .back-btn { 
        display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; 
        background: white; border: 1px solid var(--color-border); border-radius: 8px; 
        font-size: 13px; font-weight: 500; color: var(--color-text-muted); 
        text-decoration: none; transition: background .15s; margin-bottom: 20px; 
    }
    .back-btn:hover { background: var(--color-bg); }

    .chat-card { 
        background: white; border-radius: 16px; box-shadow: var(--shadow-md); 
        overflow: hidden; display: flex; flex-direction: column; height: 600px; 
    }

    /* BLUE GRADIENT HEADER */
    .chat-header { 
        padding: 20px 24px; 
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); 
        color: white; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-shrink: 0; 
    }
    .header-info-group { display: flex; flex-direction: column; gap: 2px; }
    .chat-header-title { font-size: 15px; font-weight: 700; letter-spacing: -0.01em; }
    .chat-header-sub { font-size: 11px; opacity: 0.85; font-weight: 400; }
    
    /* STATUS BADGE INSIDE HEADER */
    .header-badge {
        padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: 700; 
        text-transform: uppercase; background: rgba(255, 255, 255, 0.2); 
        backdrop-filter: blur(4px); color: white; border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .chat-messages {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 20px 18px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        background: #f8faff;
        box-sizing: border-box;
        scroll-behavior: smooth;
    }
    .chat-messages::-webkit-scrollbar { width: 4px; }
    .chat-messages::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }

    .chat-bubble-row {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        width: 100%;
        max-width: 100%;
        flex-shrink: 0;
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        position: relative;
    }
    .chat-bubble-row--me {
        flex-direction: row-reverse;
        justify-content: flex-start;
    }
    
    .chat-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        color: white;
        margin-bottom: 2px;
    }
    .chat-avatar--me { background: #7c3aed; }
    .chat-avatar--admin { background: #1e3a8a; }
    .chat-avatar--assistant { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); font-size: 14px; }

    .chat-bubble {
        max-width: 75%;
        min-width: 48px;
        padding: 10px 14px;
        border-radius: 18px;
        font-size: 13.5px;
        line-height: 1.5;
        word-break: break-word;
        overflow-wrap: anywhere;
        box-sizing: border-box;
        position: relative;
        display: flex;
        flex-direction: column;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
    }
    .chat-bubble--me {
        background: #7c3aed;
        color: white;
        border-radius: 18px 18px 4px 18px;
    }
    .chat-bubble--admin {
        background: white;
        color: var(--color-text);
        border: 1px solid var(--color-border);
        border-radius: 18px 18px 18px 4px;
    }
    .chat-bubble--assistant {
        background: linear-gradient(135deg, #f8faff 0%, #f3f0ff 100%);
        color: #1e1b4b;
        border: 1px solid #c4b5fd;
        border-radius: 18px 18px 18px 4px;
        box-shadow: 0 4px 16px rgba(124, 58, 237, 0.08);
        padding: 12px 16px;
    }

    .assistant-tag-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 8px;
        padding-bottom: 6px;
        border-bottom: 1px solid rgba(139, 92, 246, 0.15);
    }
    .assistant-tag-title {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.4px;
        color: #7c3aed;
        text-transform: uppercase;
    }
    .assistant-tag-badge {
        font-size: 8.5px;
        font-weight: 800;
        letter-spacing: 0.5px;
        background: rgba(124, 58, 237, 0.12);
        color: #7c3aed;
        border: 1px solid rgba(124, 58, 237, 0.25);
        padding: 2px 7px;
        border-radius: 9999px;
    }

    .chat-bold {
        font-weight: 700;
        color: inherit;
    }
    .chat-code {
        background: rgba(124, 58, 237, 0.1);
        color: #6d28d9;
        padding: 2px 6px;
        border-radius: 6px;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 11.5px;
        border: 1px solid rgba(124, 58, 237, 0.2);
    }
    .chat-bullet-item {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin: 5px 0;
        line-height: 1.45;
    }
    .chat-bullet-dot {
        color: #8b5cf6;
        font-size: 10px;
        margin-top: 3px;
        flex-shrink: 0;
    }
    .chat-bullet-text {
        flex: 1;
    }

    .chat-quick-faqs {
        padding: 10px 16px;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(8px);
        border-top: 1px solid rgba(0, 0, 0, 0.06);
        display: flex;
        gap: 8px;
        overflow-x: auto;
        flex-wrap: nowrap;
        scrollbar-width: none;
    }
    .chat-quick-faqs::-webkit-scrollbar { display: none; }
    .faq-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        background: #ffffff;
        border: 1px solid rgba(139, 92, 246, 0.25);
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 700;
        color: #6d28d9;
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        flex-shrink: 0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }
    .faq-chip:hover {
        background: #ede9fe;
        border-color: #8b5cf6;
        color: #5b21b6;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(124, 58, 237, 0.15);
    }

    .chat-text {
        word-break: break-word;
        overflow-wrap: anywhere;
        line-height: 1.55;
    }

    .chat-meta {
        font-size: 10.5px;
        margin-top: 4px;
        line-height: 1.2;
        opacity: 0.75;
        user-select: none;
        white-space: nowrap;
    }
    .chat-bubble--me .chat-meta {
        text-align: right;
        color: rgba(255, 255, 255, 0.85);
        align-self: flex-end;
    }
    .chat-bubble--admin .chat-meta {
        color: #64748b;
        align-self: flex-start;
    }
    .chat-bubble--assistant .chat-meta {
        color: #7c3aed;
        font-weight: 600;
        align-self: flex-start;
    }

    .chat-empty { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; color: #9ca3af; }
    
    .chat-input-area { border-top: 1px solid var(--color-border); padding: 16px; background: white; display: flex; gap: 12px; align-items: flex-end; }
    .chat-input { flex: 1; border: 1.5px solid #e5e7eb; border-radius: 12px; padding: 12px 16px; font-size: 13.5px; outline: none; transition: all .2s; min-height: 46px; max-height: 120px; }
    .chat-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
    
    .chat-send-btn { width: 46px; height: 46px; background: #1e3a8a; color: white; border: none; border-radius: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: transform 0.1s; }
    .chat-send-btn:active { transform: scale(0.95); }
    .chat-send-btn:hover { background: #1e40af; }

    /* ── DARK THEME OVERRIDES ── */
    [data-theme="dark"] .back-btn {
        background: #081636 !important;
        border-color: rgba(30, 79, 216, 0.25) !important;
        color: #94a3b8 !important;
    }
    [data-theme="dark"] .back-btn:hover {
        background: #0b1a3d !important;
        color: #f8fafc !important;
    }
    [data-theme="dark"] .chat-card {
        background: #081636 !important;
        border: 1px solid rgba(30, 79, 216, 0.25) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
    }
    [data-theme="dark"] .chat-messages {
        background: #040c1e !important;
    }
    [data-theme="dark"] .chat-messages::-webkit-scrollbar-thumb {
        background: rgba(30, 79, 216, 0.3) !important;
    }
    [data-theme="dark"] .chat-bubble--admin {
        background: #0b1a3d !important;
        color: #f8fafc !important;
        border-color: rgba(30, 79, 216, 0.25) !important;
    }
    [data-theme="dark"] .chat-bubble--admin .chat-meta {
        color: #94a3b8 !important;
    }
    [data-theme="dark"] .chat-bubble--assistant {
        background: linear-gradient(145deg, #15112e 0%, #0d1733 100%) !important;
        color: #ede9fe !important;
        border: 1px solid rgba(139, 92, 246, 0.4) !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4), 0 0 16px rgba(124, 58, 237, 0.15) !important;
    }
    [data-theme="dark"] .chat-bubble--assistant .assistant-tag-bar {
        border-bottom-color: rgba(139, 92, 246, 0.25) !important;
    }
    [data-theme="dark"] .chat-bubble--assistant .assistant-tag-title {
        color: #c4b5fd !important;
    }
    [data-theme="dark"] .chat-bubble--assistant .assistant-tag-badge {
        background: rgba(139, 92, 246, 0.2) !important;
        color: #ddd6fe !important;
        border-color: rgba(139, 92, 246, 0.45) !important;
    }
    [data-theme="dark"] .chat-bubble--assistant strong {
        color: #ffffff !important;
    }
    [data-theme="dark"] .chat-bubble--assistant .chat-code {
        background: rgba(139, 92, 246, 0.2) !important;
        color: #fbcfe8 !important;
        border-color: rgba(139, 92, 246, 0.4) !important;
    }
    [data-theme="dark"] .chat-bubble--assistant .chat-meta {
        color: #a78bfa !important;
    }
    [data-theme="dark"] .chat-avatar--assistant {
        background: linear-gradient(135deg, #7c3aed 0%, #2563eb 100%) !important;
        border: 1.5px solid rgba(255, 255, 255, 0.25) !important;
        box-shadow: 0 0 14px rgba(124, 58, 237, 0.45) !important;
    }
    [data-theme="dark"] .chat-quick-faqs {
        background: #061129 !important;
        border-top: 1px solid rgba(30, 79, 216, 0.25) !important;
    }
    [data-theme="dark"] .faq-chip {
        background: rgba(139, 92, 246, 0.12) !important;
        border-color: rgba(139, 92, 246, 0.3) !important;
        color: #ddd6fe !important;
        box-shadow: none !important;
    }
    [data-theme="dark"] .faq-chip:hover {
        background: rgba(139, 92, 246, 0.28) !important;
        border-color: rgba(139, 92, 246, 0.65) !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px rgba(139, 92, 246, 0.35) !important;
    }
    [data-theme="dark"] .chat-input-area {
        background: #081636 !important;
        border-top-color: rgba(30, 79, 216, 0.25) !important;
    }
    [data-theme="dark"] .chat-input {
        background: #0b1a3d !important;
        color: #f8fafc !important;
        border-color: rgba(30, 79, 216, 0.3) !important;
    }
</style>
@endsection

@section('content')
<main class="main">
    <a href="{{ route('requestor.requests') }}" class="back-btn">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        Back to Requests
    </a>

    <div class="chat-card">
        {{-- HEADER WITH GRADIENT AND REQUEST TITLE --}}
        <div class="chat-header">
            <div class="header-info-group">
                <div class="chat-header-title">{{ $req->title }}</div>
                <div class="chat-header-sub">ID: {{ $req->request_id ?? 'REQ-' . $req->id }} &bull; Internal Comments</div>
            </div>
            <div class="header-badge">
                {{ $req->status }}
            </div>
        </div>

        <div class="chat-messages" id="chat-messages">
            @forelse($chat_messages as $msg)
                @php
                    $is_me = $msg->sender_role === 'requestor';
                    $is_assistant = str_contains($msg->sender_name, 'Assistant');

                    // Markdown rendering: bold, italic, code, bullets
                    $msgText = e($msg->message);
                    $msgText = preg_replace('/\*\*(.*?)\*\*/s', '<strong class="chat-bold">$1</strong>', $msgText);
                    $msgText = preg_replace('/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $msgText);
                    $msgText = preg_replace('/`([^`]+)`/', '<code class="chat-code">$1</code>', $msgText);
                    $msgText = preg_replace('/^[•\-\*]\s+(.+)$/m', '<div class="chat-bullet-item"><span class="chat-bullet-dot">✦</span><span class="chat-bullet-text">$1</span></div>', $msgText);

                    $formattedTime = is_string($msg->created_at) ? \Carbon\Carbon::parse($msg->created_at)->format('M j, g:i A') : $msg->created_at->format('M j, g:i A');
                @endphp
                <div class="chat-bubble-row {{ $is_me ? 'chat-bubble-row--me' : '' }}">
                    <div class="chat-avatar {{ $is_me ? 'chat-avatar--me' : ($is_assistant ? 'chat-avatar--assistant' : 'chat-avatar--admin') }}">
                        @if($is_assistant)
                            🤖
                        @else
                            {{ strtoupper(substr($msg->sender_name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="chat-bubble {{ $is_me ? 'chat-bubble--me' : ($is_assistant ? 'chat-bubble--assistant' : 'chat-bubble--admin') }}">
                        @if($is_assistant)
                            <div class="assistant-tag-bar">
                                <div class="assistant-tag-title">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                    NUPost Assistant
                                </div>
                                <span class="assistant-tag-badge">AI FAQ BOT</span>
                            </div>
                        @endif
                        <div class="chat-text">{!! nl2br($msgText) !!}</div>
                        <div class="chat-meta">
                            {{ $is_me ? 'You' : ($is_assistant ? 'NUPost Assistant (AI)' : 'Admin') }} &middot; {{ $formattedTime }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="chat-empty">
                    <svg width="40" height="40" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    <p style="font-size: 14px;">No messages yet. Send a query to admin or ask the Assistant below.</p>
                </div>
            @endforelse
        </div>

        {{-- QUICK FAQ SUGGESTIONS --}}
        <div class="chat-quick-faqs">
            <button type="button" class="faq-chip" onclick="askQuickFaq('What are the dimensions for IG carousel?')">📐 IG Dimensions</button>
            <button type="button" class="faq-chip" onclick="askQuickFaq('What are the SLA turnaround lead times before an event?')">⏱️ Lead Times</button>
            <button type="button" class="faq-chip" onclick="askQuickFaq('What file formats and sizes can I upload?')">📁 File Formats</button>
            <button type="button" class="faq-chip" onclick="askQuickFaq('What are the official NU branding guidelines?')">🎨 NU Branding</button>
        </div>

        <form method="POST" action="{{ route('requestor.requests.chat.send', $req->id) }}" class="chat-input-area" id="chat-form" onsubmit="return handleChatSubmit(event)">
            @csrf
            <textarea class="chat-input" name="chat_message" id="chat-input"
                      placeholder="Type your message here or ask for guidelines..."
                      rows="1"
                      onkeydown="handleChatKey(event)"></textarea>
            <button type="submit" class="chat-send-btn" id="chat-send-btn">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </form>
    </div>
</main>
@endsection

@section('scripts')
<script>
    let isSubmittingChat = false;

    function scrollChatBottom() {
        const chatBox = document.getElementById('chat-messages');
        if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
    }
    document.addEventListener('DOMContentLoaded', scrollChatBottom);

    function askQuickFaq(questionText) {
        const input = document.getElementById('chat-input');
        if (!input) return;
        input.value = questionText;
        const form = document.getElementById('chat-form');
        if (handleChatSubmit()) {
            form.submit();
        }
    }

    function handleChatSubmit(e) {
        if (isSubmittingChat) {
            if (e) e.preventDefault();
            return false;
        }
        const input = document.getElementById('chat-input');
        const val = input ? input.value.trim() : '';
        if (!val) {
            if (e) e.preventDefault();
            return false;
        }

        isSubmittingChat = true;
        const btn = document.getElementById('chat-send-btn');
        if (btn) {
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.style.cursor = 'not-allowed';
            btn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="animation:spin .7s linear infinite;"><path d="M21 12a9 9 0 1 1-6.22-8.56"/></svg>';
        }
        return true;
    }

    function handleChatKey(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            if (isSubmittingChat) return;
            const form = document.getElementById('chat-form');
            if (handleChatSubmit(e)) {
                form.submit();
            }
        }
    }
</script>
<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>
@endsection