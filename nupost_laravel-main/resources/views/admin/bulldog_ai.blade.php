{{-- ============================================================
     BULLDOG AI — Admin Dashboard Assistant
     Paste this BEFORE the closing </body> tag in your admin layout
     OR at the bottom of dashboard.blade.php before @endsection('scripts')

     REQUIRED: Add GROQ_API_KEY=your_key_here to your .env file
     Get a free key at: https://console.groq.com
     ============================================================ --}}

{{-- ── BULLDOG STYLES ──────────────────────────────────────────── --}}
<style>
@import url('https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap');

/* ── LAUNCHER BUTTON ──────────────────────────────────────── */
#bulldog-launcher {
    position: fixed;
    bottom: 28px;
    right: 28px;
    z-index: 9000;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #001a4d 0%, #002e7a 100%);
    border: none;
    cursor: pointer;
    box-shadow: 0 6px 28px rgba(0,26,77,0.45), 0 0 0 0 rgba(0,46,122,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform .2s cubic-bezier(.34,1.56,.64,1), box-shadow .2s;
    animation: bulldog-pulse 3s ease-in-out infinite;
}
#bulldog-launcher:hover {
    transform: scale(1.1);
    box-shadow: 0 10px 36px rgba(0,26,77,0.55), 0 0 0 8px rgba(0,46,122,0.12);
    animation: none;
}
@keyframes bulldog-pulse {
    0%, 100% { box-shadow: 0 6px 28px rgba(0,26,77,0.45), 0 0 0 0 rgba(0,46,122,0.35); }
    50%       { box-shadow: 0 6px 28px rgba(0,26,77,0.45), 0 0 0 10px rgba(0,46,122,0); }
}

/* Bulldog face SVG area */
.bulldog-face {
    width: 38px;
    height: 38px;
    position: relative;
}

/* Notification dot */
#bulldog-notif-dot {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 14px;
    height: 14px;
    background: #ef4444;
    border-radius: 50%;
    border: 2px solid #fff;
    font-size: 8px;
    font-weight: 800;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'DM Sans', sans-serif;
    animation: notif-pop .3s cubic-bezier(.34,1.56,.64,1);
}
@keyframes notif-pop {
    from { transform: scale(0); }
    to   { transform: scale(1); }
}

/* ── MODAL ─────────────────────────────────────────────────── */
#bulldog-modal {
    position: fixed;
    bottom: 100px;
    right: 28px;
    z-index: 8999;
    width: 380px;
    max-height: 580px;
    background: #0d1526;
    border-radius: 24px;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 24px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.04);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: scale(0.85) translateY(20px);
    transform-origin: bottom right;
    opacity: 0;
    pointer-events: none;
    transition: transform .25s cubic-bezier(.34,1.3,.64,1), opacity .2s ease;
}
#bulldog-modal.open {
    transform: scale(1) translateY(0);
    opacity: 1;
    pointer-events: all;
}

/* Header */
.bd-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 18px 14px;
    background: linear-gradient(135deg, #001a4d 0%, #002e7a 100%);
    border-bottom: 1px solid rgba(255,255,255,0.08);
    flex-shrink: 0;
}
.bd-avatar {
    width: 42px;
    height: 42px;
    border-radius: 13px;
    background: rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    border: 1px solid rgba(255,255,255,0.15);
}
.bd-header-info { flex: 1; }
.bd-name {
    font-family: 'Syne', sans-serif;
    font-size: 15px;
    font-weight: 800;
    color: white;
    letter-spacing: 0.3px;
    display: flex;
    align-items: center;
    gap: 7px;
}
.bd-name-badge {
    font-size: 9px;
    font-weight: 700;
    font-family: 'DM Sans', sans-serif;
    background: rgba(245,158,11,0.2);
    color: #fcd34d;
    border: 1px solid rgba(245,158,11,0.3);
    padding: 2px 8px;
    border-radius: 20px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.bd-status {
    font-size: 11px;
    color: rgba(255,255,255,0.45);
    font-family: 'DM Sans', sans-serif;
    margin-top: 1px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.bd-status-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 6px #10b981;
    animation: status-blink 2s ease-in-out infinite;
}
@keyframes status-blink {
    0%, 100% { opacity: 1; }
    50%       { opacity: 0.4; }
}
.bd-close {
    width: 30px; height: 30px;
    border-radius: 8px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.5);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all .15s;
    flex-shrink: 0;
}
.bd-close:hover { background: rgba(255,255,255,0.15); color: white; }

/* Tabs */
.bd-tabs {
    display: flex;
    padding: 10px 14px 0;
    gap: 4px;
    background: #0d1526;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    flex-shrink: 0;
}
.bd-tab {
    padding: 6px 14px;
    border-radius: 8px 8px 0 0;
    font-size: 11.5px;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    color: rgba(255,255,255,0.35);
    cursor: pointer;
    border: none;
    background: none;
    transition: all .15s;
    border-bottom: 2px solid transparent;
}
.bd-tab:hover { color: rgba(255,255,255,0.7); }
.bd-tab.active {
    color: #fcd34d;
    border-bottom-color: #fcd34d;
    background: rgba(252,211,77,0.06);
}

/* Body */
.bd-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px 18px;
    scrollbar-width: thin;
    scrollbar-color: rgba(255,255,255,0.1) transparent;
}
.bd-body::-webkit-scrollbar { width: 4px; }
.bd-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

/* Tab panels */
.bd-panel { display: none; }
.bd-panel.active { display: block; }

/* ── BRIEFING PANEL ──── */
.bd-briefing-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    padding: 32px 0;
    color: rgba(255,255,255,0.4);
    font-family: 'DM Sans', sans-serif;
    font-size: 13px;
}
.bd-loader {
    width: 36px; height: 36px;
    border: 3px solid rgba(255,255,255,0.08);
    border-top-color: #fcd34d;
    border-radius: 50%;
    animation: bd-spin 0.8s linear infinite;
}
@keyframes bd-spin { to { transform: rotate(360deg); } }

.bd-briefing-text {
    font-family: 'DM Sans', sans-serif;
    font-size: 13.5px;
    color: rgba(255,255,255,0.82);
    line-height: 1.75;
    white-space: pre-wrap;
}

/* Stat chips */
.bd-stat-chips {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    margin-bottom: 14px;
}
.bd-chip {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
    font-family: 'DM Sans', sans-serif;
    display: flex;
    align-items: center;
    gap: 5px;
}

/* Refresh button */
.bd-refresh-btn {
    display: flex;
    align-items: center;
    gap: 7px;
    margin-top: 14px;
    padding: 9px 16px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    color: rgba(255,255,255,0.6);
    font-size: 12px;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    transition: all .15s;
    width: 100%;
    justify-content: center;
}
.bd-refresh-btn:hover { background: rgba(255,255,255,0.12); color: white; }
.bd-refresh-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.bd-refresh-icon { transition: transform .4s; }
.bd-refresh-btn:not(:disabled):hover .bd-refresh-icon { transform: rotate(180deg); }

/* ── CHAT PANEL ──── */
.bd-chat-messages {
    display: flex;
    flex-direction: column;
    gap: 12px;
    min-height: 200px;
    margin-bottom: 12px;
    box-sizing: border-box;
}
.bd-msg {
    display: flex;
    align-items: flex-end;
    gap: 8px;
    width: 100%;
    max-width: 100%;
    flex-shrink: 0;
    box-sizing: border-box;
    animation: msg-in .2s ease;
}
@keyframes msg-in {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: none; }
}
.bd-msg--user {
    flex-direction: row-reverse;
    justify-content: flex-start;
}
.bd-msg-av {
    width: 28px; height: 28px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 11px;
    font-weight: 700;
    margin-bottom: 2px;
}
.bd-msg--bot .bd-msg-av {
    background: linear-gradient(135deg, #001a4d, #002e7a);
    color: #fcd34d;
    font-family: 'Syne', sans-serif;
}
.bd-msg--user .bd-msg-av {
    background: rgba(255,255,255,0.1);
    color: rgba(255,255,255,0.6);
    font-family: 'DM Sans', sans-serif;
}
.bd-msg-bubble {
    max-width: 80%;
    min-width: 40px;
    padding: 10px 14px;
    border-radius: 14px;
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    line-height: 1.6;
    word-break: break-word;
    overflow-wrap: anywhere;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
}
.bd-msg--bot .bd-msg-bubble {
    background: rgba(255,255,255,0.07);
    color: rgba(255,255,255,0.85);
    border-radius: 4px 14px 14px 14px;
}
.bd-msg--user .bd-msg-bubble {
    background: linear-gradient(135deg, #002366, #003080);
    color: white;
    border-radius: 14px 4px 14px 14px;
}
.bd-typing {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 10px 13px;
    background: rgba(255,255,255,0.07);
    border-radius: 4px 14px 14px 14px;
    width: fit-content;
}
.bd-typing span {
    width: 6px; height: 6px;
    background: rgba(255,255,255,0.4);
    border-radius: 50%;
    animation: typing-dot 1.2s ease-in-out infinite;
}
.bd-typing span:nth-child(2) { animation-delay: .2s; }
.bd-typing span:nth-child(3) { animation-delay: .4s; }
@keyframes typing-dot {
    0%, 60%, 100% { transform: translateY(0); opacity: .4; }
    30%            { transform: translateY(-4px); opacity: 1; }
}

/* Chat input */
.bd-chat-form {
    display: flex;
    gap: 8px;
    margin-top: 4px;
    flex-shrink: 0;
    padding: 0 0 4px;
}
.bd-chat-input {
    flex: 1;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 10px;
    padding: 9px 13px;
    font-size: 13px;
    font-family: 'DM Sans', sans-serif;
    color: white;
    outline: none;
    transition: border-color .15s;
}
.bd-chat-input::placeholder { color: rgba(255,255,255,0.25); }
.bd-chat-input:focus { border-color: rgba(252,211,77,0.4); }
.bd-send-btn {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: #fcd34d;
    border: none;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all .15s;
    flex-shrink: 0;
}
.bd-send-btn:hover { background: #f59e0b; transform: scale(1.05); }
.bd-send-btn:disabled { opacity: 0.4; cursor: not-allowed; transform: none; }

/* ── QUICK ACTIONS ──── */
.bd-quick-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.bd-action-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    cursor: pointer;
    transition: all .15s;
    text-decoration: none;
    font-family: 'DM Sans', sans-serif;
}
.bd-action-btn:hover {
    background: rgba(255,255,255,0.1);
    border-color: rgba(255,255,255,0.16);
    transform: translateX(3px);
}
.bd-action-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.bd-action-label { font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.85); }
.bd-action-sub   { font-size: 11px; color: rgba(255,255,255,0.35); margin-top: 1px; }
.bd-action-arrow { margin-left: auto; color: rgba(255,255,255,0.25); }

/* Error state */
.bd-error {
    background: rgba(239,68,68,0.1);
    border: 1px solid rgba(239,68,68,0.2);
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 12.5px;
    color: #fca5a5;
    font-family: 'DM Sans', sans-serif;
    line-height: 1.6;
}

/* Footer */
.bd-footer {
    padding: 10px 18px;
    border-top: 1px solid rgba(255,255,255,0.06);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    flex-shrink: 0;
}
.bd-footer-txt {
    font-size: 10px;
    color: rgba(255,255,255,0.2);
    font-family: 'DM Sans', sans-serif;
}
</style>

{{-- ── BULLDOG HTML ─────────────────────────────────────────────── --}}

{{-- Launcher Button --}}
<button id="bulldog-launcher" onclick="toggleBulldog()" title="Bulldog AI Assistant">
    {{-- Bulldog face --}}
    <svg class="bulldog-face" viewBox="0 0 38 38" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Head -->
        <circle cx="19" cy="19" r="16" fill="#1e3a8a" stroke="rgba(255,255,255,0.15)" stroke-width="1.5"/>
        <!-- Ears -->
        <ellipse cx="7" cy="16" rx="4" ry="5" fill="#1e3a8a" stroke="rgba(255,255,255,0.12)" stroke-width="1"/>
        <ellipse cx="31" cy="16" rx="4" ry="5" fill="#1e3a8a" stroke="rgba(255,255,255,0.12)" stroke-width="1"/>
        <!-- Snout -->
        <ellipse cx="19" cy="23" rx="7" ry="5" fill="#2563eb" opacity="0.7"/>
        <!-- Nose -->
        <ellipse cx="19" cy="20.5" rx="3.5" ry="2.5" fill="#0f172a"/>
        <!-- Nostrils -->
        <circle cx="17.5" cy="20.5" r="0.9" fill="rgba(255,255,255,0.25)"/>
        <circle cx="20.5" cy="20.5" r="0.9" fill="rgba(255,255,255,0.25)"/>
        <!-- Eyes -->
        <circle cx="14" cy="16" r="3" fill="white"/>
        <circle cx="24" cy="16" r="3" fill="white"/>
        <circle cx="14.5" cy="16" r="1.8" fill="#0f172a"/>
        <circle cx="24.5" cy="16" r="1.8" fill="#0f172a"/>
        <!-- Eye shine -->
        <circle cx="15" cy="15.3" r="0.7" fill="white" opacity="0.8"/>
        <circle cx="25" cy="15.3" r="0.7" fill="white" opacity="0.8"/>
        <!-- Mouth / jowls -->
        <path d="M13 26 Q16 28.5 19 26.5 Q22 28.5 25 26" stroke="rgba(255,255,255,0.4)" stroke-width="1.2" stroke-linecap="round" fill="none"/>
        <!-- Collar tag -->
        <rect x="15" y="30" width="8" height="4" rx="2" fill="#fcd34d"/>
        <text x="19" y="33.2" text-anchor="middle" font-size="3.5" font-weight="bold" fill="#1a1a1a" font-family="sans-serif">B</text>
    </svg>
    <div id="bulldog-notif-dot" style="display:none;"></div>
</button>

{{-- Modal --}}
<div id="bulldog-modal">
    {{-- Header --}}
    <div class="bd-header">
        <div class="bd-avatar">
            <svg width="24" height="24" viewBox="0 0 38 38" fill="none">
                <circle cx="19" cy="19" r="16" fill="rgba(255,255,255,0.1)"/>
                <ellipse cx="19" cy="20.5" rx="3.5" ry="2.5" fill="#fcd34d"/>
                <circle cx="14" cy="16" r="2.5" fill="white"/>
                <circle cx="24" cy="16" r="2.5" fill="white"/>
                <circle cx="14.5" cy="16" r="1.4" fill="#0f172a"/>
                <circle cx="24.5" cy="16" r="1.4" fill="#0f172a"/>
            </svg>
        </div>
        <div class="bd-header-info">
            <div class="bd-name">
                Bulldog
                <span class="bd-name-badge">AI Assistant</span>
            </div>
            <div class="bd-status">
                <span class="bd-status-dot"></span>
                Online · Powered by Groq
            </div>
        </div>
        <button class="bd-close" onclick="toggleBulldog()">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>

    {{-- Tabs --}}
    <div class="bd-tabs">
        <button class="bd-tab active" onclick="switchTab('briefing')">📋 Briefing</button>
        <button class="bd-tab" onclick="switchTab('chat')">💬 Ask me</button>
        <button class="bd-tab" onclick="switchTab('actions')">⚡ Quick</button>
    </div>

    {{-- Body --}}
    <div class="bd-body">

        {{-- BRIEFING PANEL --}}
        <div class="bd-panel active" id="bd-panel-briefing">
            {{-- Stat chips --}}
            <div class="bd-stat-chips">
                <span class="bd-chip" style="background:rgba(245,158,11,0.15);color:#fcd34d;border:1px solid rgba(245,158,11,0.25);">
                    📥 {{ $pending ?? 0 }} Pending
                </span>
                <span class="bd-chip" style="background:rgba(14,165,233,0.15);color:#7dd3fc;border:1px solid rgba(14,165,233,0.25);">
                    👁 {{ $review ?? 0 }} Under Review
                </span>
                <span class="bd-chip" style="background:rgba(16,185,129,0.15);color:#6ee7b7;border:1px solid rgba(16,185,129,0.25);">
                    ✅ {{ $approved ?? 0 }} Approved
                </span>
                <span class="bd-chip" style="background:rgba(239,68,68,0.15);color:#fca5a5;border:1px solid rgba(239,68,68,0.25);">
                    ❌ {{ $rejected ?? 0 }} Rejected
                </span>
            </div>

            {{-- AI Generated Briefing --}}
            <div id="bd-briefing-content">
                <div class="bd-briefing-loading" id="bd-loading">
                    <div class="bd-loader"></div>
                    <span>Bulldog is reading your dashboard…</span>
                </div>
                <div id="bd-briefing-text" class="bd-briefing-text" style="display:none;"></div>
                <div id="bd-briefing-error" class="bd-error" style="display:none;"></div>
            </div>

            <button class="bd-refresh-btn" id="bd-refresh-btn" onclick="loadBriefing(true)" disabled>
                <svg class="bd-refresh-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                Refresh Briefing
            </button>
        </div>

        {{-- CHAT PANEL --}}
        <div class="bd-panel" id="bd-panel-chat">
            <div class="bd-chat-messages" id="bd-chat-messages">
                <div class="bd-msg bd-msg--bot">
                    <div class="bd-msg-av">B</div>
                    <div class="bd-msg-bubble">
                        Woof! Ask me anything about your requests, stats, or what needs attention. I got you. 🐾
                    </div>
                </div>
            </div>
            <div class="bd-chat-form">
                <input class="bd-chat-input" id="bd-chat-input" type="text"
                       placeholder="Ask Bulldog something…"
                       onkeydown="if(event.key==='Enter')sendChat()">
                <button class="bd-send-btn" id="bd-send-btn" onclick="sendChat()">
                    <svg width="16" height="16" fill="none" stroke="#1a1a1a" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </div>
        </div>

        {{-- QUICK ACTIONS PANEL --}}
        <div class="bd-panel" id="bd-panel-actions">
            <div class="bd-quick-actions">
                <a href="{{ route('admin.requests', ['filter'=>'pending']) }}" class="bd-action-btn">
                    <div class="bd-action-icon" style="background:rgba(245,158,11,0.15);">
                        <svg width="18" height="18" fill="none" stroke="#fcd34d" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <div class="bd-action-label">Pending Requests</div>
                        <div class="bd-action-sub">{{ $pending ?? 0 }} waiting for review</div>
                    </div>
                    <svg class="bd-action-arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="{{ route('admin.requests') }}" class="bd-action-btn">
                    <div class="bd-action-icon" style="background:rgba(37,99,235,0.15);">
                        <svg width="18" height="18" fill="none" stroke="#93c5fd" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <div>
                        <div class="bd-action-label">All Requests</div>
                        <div class="bd-action-sub">{{ $total ?? 0 }} total submissions</div>
                    </div>
                    <svg class="bd-action-arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="{{ route('admin.calendar') }}" class="bd-action-btn">
                    <div class="bd-action-icon" style="background:rgba(16,185,129,0.15);">
                        <svg width="18" height="18" fill="none" stroke="#6ee7b7" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                    <div>
                        <div class="bd-action-label">Request Calendar</div>
                        <div class="bd-action-sub">View scheduled posts</div>
                    </div>
                    <svg class="bd-action-arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <a href="{{ route('admin.reports.export') }}" class="bd-action-btn">
                    <div class="bd-action-icon" style="background:rgba(139,92,246,0.15);">
                        <svg width="18" height="18" fill="none" stroke="#c4b5fd" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    </div>
                    <div>
                        <div class="bd-action-label">Export CSV</div>
                        <div class="bd-action-sub">Download full report</div>
                    </div>
                    <svg class="bd-action-arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
                <button class="bd-action-btn" onclick="switchTab('briefing');loadBriefing(true);" style="border:none;width:100%;text-align:left;">
                    <div class="bd-action-icon" style="background:rgba(252,211,77,0.15);">
                        <svg width="18" height="18" fill="none" stroke="#fcd34d" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
                    </div>
                    <div>
                        <div class="bd-action-label">Re-generate Briefing</div>
                        <div class="bd-action-sub">Get a fresh AI summary</div>
                    </div>
                    <svg class="bd-action-arrow" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </div>
    </div>

    <div class="bd-footer">
        <svg width="10" height="10" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        <span class="bd-footer-txt">Bulldog · NUPost Admin AI · Powered by Groq</span>
    </div>
</div>

{{-- ── BULLDOG SCRIPTS ──────────────────────────────────────────── --}}
<script>
(function() {
    // ── DASHBOARD DATA passed to Groq ──────────────────────────
    const BD_DATA = {
        total:    {{ $total    ?? 0 }},
        pending:  {{ $pending  ?? 0 }},
        review:   {{ $review   ?? 0 }},
        approved: {{ $approved ?? 0 }},
        posted:   {{ $posted   ?? 0 }},
        rejected: {{ $rejected ?? 0 }},
        users:    {{ $users    ?? 0 }},
        thisMonth:{{ $this_month_count ?? 0 }},
        highPrio: {{ $high_prio_count  ?? 0 }},
        approvalRate: {{ $approval_rate ?? 0 }},
        topRequestors: @json($top_requestors->take(3)->pluck('requester')),
        recentTitles: @json($recent->take(5)->pluck('title')),
        date: '{{ now()->format("l, F j, Y") }}',
        time: '{{ now()->format("g:i A") }}',
    };

    // ── STATE ──────────────────────────────────────────────────
    let isOpen        = false;
    let activeTab     = 'briefing';
    let briefingDone  = false;
    let chatHistory   = [];

    // ── TOGGLE ─────────────────────────────────────────────────
    window.toggleBulldog = function() {
        isOpen = !isOpen;
        const modal = document.getElementById('bulldog-modal');
        modal.classList.toggle('open', isOpen);

        // Hide notif dot when opened
        if (isOpen) {
            document.getElementById('bulldog-notif-dot').style.display = 'none';
            if (!briefingDone) loadBriefing(false);
        }
    };

    // ── TABS ───────────────────────────────────────────────────
    window.switchTab = function(tab) {
        activeTab = tab;
        document.querySelectorAll('.bd-tab').forEach((t, i) => {
            const tabs = ['briefing','chat','actions'];
            t.classList.toggle('active', tabs[i] === tab);
        });
        document.querySelectorAll('.bd-panel').forEach(p => p.classList.remove('active'));
        document.getElementById('bd-panel-' + tab).classList.add('active');
    };

    // ── GROQ API CALL ──────────────────────────────────────────
    async function callGroq(messages) {
        const res = await fetch('/api/bulldog-chat', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF || document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ messages })
        });
        const data = await res.json();
        if (data.error) throw new Error(data.error);
        return data.reply;
    }

    // ── LOAD BRIEFING ──────────────────────────────────────────
    window.loadBriefing = async function(manual = false) {
        const loadEl  = document.getElementById('bd-loading');
        const textEl  = document.getElementById('bd-briefing-text');
        const errEl   = document.getElementById('bd-briefing-error');
        const refreshBtn = document.getElementById('bd-refresh-btn');

        loadEl.style.display  = 'flex';
        textEl.style.display  = 'none';
        errEl.style.display   = 'none';
        refreshBtn.disabled   = true;

        const briefingPrompt = `You are Bulldog, a sharp and friendly AI assistant for NUPost admin dashboard. 
Today is ${BD_DATA.date} at ${BD_DATA.time}.

Here is the current dashboard data:
- Total Requests: ${BD_DATA.total}
- Pending Review: ${BD_DATA.pending}
- Under Review: ${BD_DATA.review}
- Approved: ${BD_DATA.approved}
- Posted: ${BD_DATA.posted}
- Rejected: ${BD_DATA.rejected}
- Registered Users: ${BD_DATA.users}
- New This Month: ${BD_DATA.thisMonth}
- High/Urgent Priority: ${BD_DATA.highPrio}
- Approval Rate: ${BD_DATA.approvalRate}%
- Top Requestors: ${BD_DATA.topRequestors.join(', ') || 'N/A'}
- Recent Request Titles: ${BD_DATA.recentTitles.join(' | ') || 'None'}

Give the admin a quick, friendly morning briefing. Be conversational, use a few emojis, keep it under 120 words. Mention:
1. What needs immediate attention (pending + high priority)
2. Any wins (approval rate, posted count)
3. One specific action to take today

Be direct. No fluff. Sound like a loyal assistant who actually read the data.`;

        try {
            const reply = await callGroq([{ role: 'user', content: briefingPrompt }]);
            textEl.textContent = reply;
            textEl.style.display = 'block';
            briefingDone = true;
        } catch(e) {
            errEl.innerHTML = `⚠️ Couldn't load briefing: ${e.message}<br><small style="opacity:.7;">Check that GROQ_API_KEY is set in your .env and the /api/bulldog-chat route exists.</small>`;
            errEl.style.display = 'block';
        } finally {
            loadEl.style.display  = 'none';
            refreshBtn.disabled   = false;
        }
    };

    // ── CHAT ───────────────────────────────────────────────────
    window.sendChat = async function() {
        const input   = document.getElementById('bd-chat-input');
        const sendBtn = document.getElementById('bd-send-btn');
        const msgBox  = document.getElementById('bd-chat-messages');
        const text    = input.value.trim();
        if (!text) return;

        // Append user message
        appendMsg('user', text);
        input.value   = '';
        sendBtn.disabled = true;

        // Typing indicator
        const typingEl = document.createElement('div');
        typingEl.className = 'bd-msg bd-msg--bot';
        typingEl.innerHTML = `<div class="bd-msg-av">B</div><div class="bd-typing"><span></span><span></span><span></span></div>`;
        msgBox.appendChild(typingEl);
        msgBox.scrollTop = msgBox.scrollHeight;

        // Build context
        const systemPrompt = `You are Bulldog, a sharp, helpful AI assistant for NUPost admin. 
Dashboard snapshot: ${BD_DATA.total} total requests, ${BD_DATA.pending} pending, ${BD_DATA.review} under review, ${BD_DATA.approved} approved, ${BD_DATA.posted} posted, ${BD_DATA.rejected} rejected, ${BD_DATA.approvalRate}% approval rate, ${BD_DATA.highPrio} high-priority. Today: ${BD_DATA.date}.
Be concise, friendly, use plain text (no markdown). Max 80 words per reply.`;

        chatHistory.push({ role: 'user', content: text });

        try {
            const messages = [
                { role: 'system', content: systemPrompt },
                ...chatHistory.slice(-6) // keep last 3 turns
            ];
            const reply = await callGroq(messages);
            chatHistory.push({ role: 'assistant', content: reply });
            typingEl.remove();
            appendMsg('bot', reply);
        } catch(e) {
            typingEl.remove();
            appendMsg('bot', `Woof — something went wrong: ${e.message}`);
        } finally {
            sendBtn.disabled = false;
            input.focus();
        }
    };

    function appendMsg(role, text) {
        const msgBox = document.getElementById('bd-chat-messages');
        const div    = document.createElement('div');
        div.className = `bd-msg bd-msg--${role === 'user' ? 'user' : 'bot'}`;
        div.innerHTML = `
            <div class="bd-msg-av">${role === 'user' ? 'A' : 'B'}</div>
            <div class="bd-msg-bubble">${escHtml(text)}</div>`;
        msgBox.appendChild(div);
        msgBox.scrollTop = msgBox.scrollHeight;
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── AUTO SHOW NOTIF DOT after 1.5s if there are pending items ──
    setTimeout(() => {
        const attention = BD_DATA.pending + BD_DATA.review;
        if (attention > 0 && !isOpen) {
            const dot = document.getElementById('bulldog-notif-dot');
            dot.textContent = attention > 9 ? '9+' : attention;
            dot.style.display = 'flex';
        }
    }, 1500);

})();
</script>