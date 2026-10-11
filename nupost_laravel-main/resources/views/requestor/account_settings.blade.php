@extends('layouts.requestor')

@section('title', 'Account Settings')
@section('page-title', 'Account Settings')

@section('head-styles')
<style>
.page-outer {
    min-height: calc(100vh - 64px);
    display: flex; flex-direction: column;
    align-items: center; padding: 36px 24px 60px;
}
.page-inner { width: 100%; max-width: 820px; }

.breadcrumb { display: flex; align-items: center; gap: 6px; margin-bottom: 10px; font-size: 13px; }
.breadcrumb a { color: #3b6ef5; text-decoration: none; display: flex; align-items: center; gap: 5px; font-weight: 600; }
.breadcrumb a:hover { text-decoration: underline; }

.page-header { margin-bottom: 28px; }
.page-header h1 { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; color: var(--text); }
.page-header p  { font-size: 13.5px; color: var(--text-muted); margin-top: 4px; }

.alert { padding: 14px 18px; border-radius: 12px; font-size: 13.5px; margin-bottom: 20px; font-weight: 500; }
.alert--success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.alert--error   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }

/* SECTION CARDS */
.section-card {
    background: white; border-radius: 20px;
    border: 1.5px solid var(--border);
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    padding: 32px 40px; margin-bottom: 16px;
    transition: box-shadow .2s;
}
.section-card:focus-within {
    box-shadow: 0 4px 20px rgba(59,110,245,0.08);
    border-color: rgba(59,110,245,0.25);
}
.section-header {
    display: flex; align-items: center; gap: 12px;
    font-size: 10.5px; font-weight: 800; letter-spacing: 1.2px;
    text-transform: uppercase; color: var(--text-faint);
    margin-bottom: 24px; padding-bottom: 16px;
    border-bottom: 1.5px solid var(--border);
}
.section-header__icon {
    width: 34px; height: 34px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.section-header__icon--blue   { background: #dbeafe; color: #2563eb; }
.section-header__icon--bell   { background: #fef3c7; color: #d97706; }
.section-header__icon--shield { background: #dcfce7; color: #16a34a; }

/* FIELDS */
.field { margin-bottom: 18px; }
.field label {
    display: block; font-size: 11px; font-weight: 700;
    color: var(--text-faint); margin-bottom: 7px;
    text-transform: uppercase; letter-spacing: 0.4px;
}
.field input[type="password"],
.field input[type="text"] {
    width: 100%; border: 1.5px solid var(--border); border-radius: 12px;
    padding: 12px 16px; font-size: 14px; font-family: var(--font);
    color: var(--text); background: #fafbfd; outline: none;
    transition: border-color .15s, box-shadow .15s, background .15s;
    box-sizing: border-box;
}
.field input:focus {
    border-color: #3b6ef5; background: white;
    box-shadow: 0 0 0 3px rgba(59,110,245,0.08);
}
.field input::placeholder { color: #c8cdd8; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }

.btn-primary {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 11px 26px; background: #001a6e; color: white;
    border: none; border-radius: 11px; font-size: 13.5px; font-weight: 700;
    cursor: pointer; font-family: var(--font); margin-top: 20px; transition: all .15s;
    box-shadow: 0 3px 12px rgba(0,26,110,0.25);
}
.btn-primary:hover { background: #00237a; transform: translateY(-1px); }

/* SESSION INFO */
.session-info {
    background: #f8faff; border: 1.5px solid #dbeafe;
    border-radius: 12px; padding: 14px 18px;
    font-size: 13px; color: var(--text-muted); margin-top: 20px;
    display: flex; align-items: center; gap: 8px;
}
.session-info svg { color: #3b6ef5; flex-shrink: 0; }

/* EMAIL BADGE */
.email-badge {
    display: inline-flex; align-items: center; gap: 8px;
    background: #eff6ff; border: 1.5px solid #bfdbfe;
    border-radius: 12px; padding: 12px 16px;
    font-size: 13px; color: #1e40af; margin-bottom: 20px; width: 100%;
}

/* TOGGLE ROWS */
.toggle-row {
    display: flex; align-items: flex-start; justify-content: space-between;
    padding: 18px 0; border-bottom: 1px solid #f3f4f6;
    gap: 20px; cursor: pointer; border-radius: 8px;
    transition: background .1s;
}
.toggle-row:last-child { border-bottom: none; padding-bottom: 0; }
.toggle-label { font-size: 14px; font-weight: 600; color: var(--text); margin-bottom: 3px; }
.toggle-desc  { font-size: 12.5px; color: var(--text-muted); line-height: 1.5; }

.toggle-wrap { display: flex; flex-direction: column; align-items: center; flex-shrink: 0; gap: 4px; }
.toggle-switch { position: relative; display: inline-block; width: 46px; height: 25px; flex-shrink: 0; }
.toggle-switch input { opacity: 0; width: 0; height: 0; position: absolute; }
.toggle-slider {
    position: absolute; cursor: pointer; inset: 0;
    background: #d1d5db; border-radius: 25px; transition: background .25s;
}
.toggle-slider:before {
    content: ""; position: absolute; width: 19px; height: 19px;
    left: 3px; top: 3px; background: white; border-radius: 50%;
    transition: transform .25s; box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}
.toggle-switch input:checked + .toggle-slider { background: #001a6e; }
.toggle-switch input:checked + .toggle-slider:before { transform: translateX(21px); }
.toggle-state { font-size: 10.5px; font-weight: 700; color: var(--text-faint); }
.toggle-state.on { color: #001a6e; }

/* SAVE BAR */
.save-bar {
    display: flex; justify-content: flex-end;
    padding: 22px 40px; background: white;
    border-radius: 20px; border: 1.5px solid var(--border);
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
.btn-save-all {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 12px 32px; background: #001a6e; color: white;
    border: none; border-radius: 11px; font-size: 14px; font-weight: 700;
    cursor: pointer; font-family: var(--font); transition: all .15s;
    box-shadow: 0 3px 12px rgba(0,26,110,0.3);
}
.btn-save-all:hover { background: #00237a; transform: translateY(-1px); box-shadow: 0 5px 18px rgba(0,26,110,0.35); }

@media (max-width: 600px) {
    .field-row { grid-template-columns: 1fr; }
    .section-card { padding: 24px; }
    .save-bar { padding: 20px 24px; }
}

/* ── DARK THEME OVERRIDES ── */
[data-theme="dark"] .section-card {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
}
[data-theme="dark"] .section-header {
    border-bottom-color: rgba(30, 79, 216, 0.25) !important;
    color: #94a3b8 !important;
}
[data-theme="dark"] .toggle-row {
    border-bottom-color: rgba(30, 79, 216, 0.15) !important;
}
[data-theme="dark"] .toggle-label {
    color: #f8fafc !important;
}
[data-theme="dark"] .toggle-desc {
    color: #94a3b8 !important;
}
[data-theme="dark"] .save-bar {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
}
</style>
@endsection

@section('content')
<div class="page-outer">
<div class="page-inner">

    <div class="breadcrumb">
        <a href="{{ route('requestor.profile') }}">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Profile
        </a>
    </div>
    <div class="page-header">
        <h1>Account Settings</h1>
        <p>Manage your account preferences and security</p>
    </div>

    @if(session('success'))<div class="alert alert--success">✅ {{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert--error">❌ {{ session('error') }}</div>@endif

    <!-- PASSWORD -->
    <div class="section-card">
        <div class="section-header">
            <div class="section-header__icon section-header__icon--blue">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            Password &amp; Security
        </div>
        <form method="POST" action="{{ route('requestor.settings.password') }}">
            @csrf
            <div class="field">
                <label>Current Password</label>
                <div style="position:relative;">
                    <input type="password" name="current_password" id="current_password" placeholder="Enter your current password" autocomplete="current-password" style="padding-right:40px;">
                    <button type="button" onclick="togglePassword('current_password', this)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#6b7280; display:flex; align-items:center; padding:0;">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label>New Password</label>
                    <div style="position:relative;">
                        <input type="password" name="new_password" id="new_password" placeholder="Minimum 8 characters" autocomplete="new-password" style="padding-right:40px;">
                        <button type="button" onclick="togglePassword('new_password', this)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#6b7280; display:flex; align-items:center; padding:0;">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
                <div class="field">
                    <label>Confirm New Password</label>
                    <div style="position:relative;">
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Re-enter new password" autocomplete="new-password" style="padding-right:40px;">
                        <button type="button" onclick="togglePassword('confirm_password', this)" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#6b7280; display:flex; align-items:center; padding:0;">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="width:16px;height:16px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn-primary">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Update Password
            </button>
        </form>
        <div class="session-info">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Logged in as: <strong>{{ $user->email ?? '—' }}</strong>
        </div>
    </div>

    <!-- NOTIFICATIONS + PRIVACY -->
    <form method="POST" action="{{ route('requestor.settings.save') }}" id="settings-form">
        @csrf
        <div class="section-card">
            <div class="section-header">
                <div class="section-header__icon section-header__icon--bell">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                </div>
                Notification Preferences
            </div>
            <div class="email-badge">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Notifications will be sent to: <strong>{{ $user->email ?? '—' }}</strong>
            </div>
            <div class="toggle-row" onclick="toggleSwitch('email_notif')">
                <div>
                    <div class="toggle-label">Email Notifications</div>
                    <div class="toggle-desc">Receive all notifications via email when enabled</div>
                </div>
                <div class="toggle-wrap">
                    <label class="toggle-switch" onclick="event.stopPropagation()">
                        <input type="checkbox" name="email_notif" id="email_notif"
                               {{ $user->email_notif ? 'checked' : '' }}
                               onchange="updateState(this); syncStatusToggle()">
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-state {{ $user->email_notif ? 'on' : '' }}" id="state_email_notif">{{ $user->email_notif ? 'ON' : 'OFF' }}</span>
                </div>
            </div>
            <div class="toggle-row" id="status-row"
                 onclick="toggleSwitch('status_updates')"
                 style="{{ !$user->email_notif ? 'opacity:.4;pointer-events:none;' : '' }}">
                <div>
                    <div class="toggle-label">Request Status Updates</div>
                    <div class="toggle-desc">Get an email when your request is approved, posted, or rejected</div>
                </div>
                <div class="toggle-wrap">
                    <label class="toggle-switch" onclick="event.stopPropagation()">
                        <input type="checkbox" name="status_updates" id="status_updates"
                               {{ $user->status_updates ? 'checked' : '' }}
                               onchange="updateState(this)">
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-state {{ $user->status_updates ? 'on' : '' }}" id="state_status_updates">{{ $user->status_updates ? 'ON' : 'OFF' }}</span>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <div class="section-header__icon section-header__icon--shield">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                Privacy Settings
            </div>
            <div class="toggle-row" onclick="toggleSwitch('public_profile')" style="border:none;padding-bottom:0;">
                <div>
                    <div class="toggle-label">Public Profile</div>
                    <div class="toggle-desc">Make your profile visible to all users in NUPost</div>
                </div>
                <div class="toggle-wrap">
                    <label class="toggle-switch" onclick="event.stopPropagation()">
                        <input type="checkbox" name="public_profile" id="public_profile"
                               {{ $user->public_profile ? 'checked' : '' }}
                               onchange="updateState(this)">
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-state {{ $user->public_profile ? 'on' : '' }}" id="state_public_profile">{{ $user->public_profile ? 'ON' : 'OFF' }}</span>
                </div>
            </div>
        </div>

        <div class="save-bar">
            <button type="submit" class="btn-save-all">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Save All Settings
            </button>
        </div>
    </form>

</div>
</div>
@endsection

@section('scripts')
<script>
function toggleSwitch(id) {
    const cb = document.getElementById(id);
    if (!cb || cb.disabled) return;
    cb.checked = !cb.checked; updateState(cb);
    if (id === 'email_notif') syncStatusToggle();
}
function updateState(cb) {
    const el = document.getElementById('state_' + cb.id);
    if (!el) return;
    el.textContent = cb.checked ? 'ON' : 'OFF';
    el.classList.toggle('on', cb.checked);
}
function syncStatusToggle() {
    const master = document.getElementById('email_notif');
    const row    = document.getElementById('status-row');
    const sub    = document.getElementById('status_updates');
    if (!master || !row || !sub) return;
    if (master.checked) { row.style.opacity='1'; row.style.pointerEvents='auto'; }
    else { row.style.opacity='0.4'; row.style.pointerEvents='none'; sub.checked=false; updateState(sub); }
}
document.querySelectorAll('.toggle-switch input[type="checkbox"]').forEach(cb => updateState(cb));
syncStatusToggle();

function togglePassword(id, btn) {
    const input = document.getElementById(id);
    const svg = btn.querySelector('svg');
    if (input.type === 'password') {
        input.type = 'text';
        svg.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
        input.type = 'password';
        svg.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
}
</script>
@endsection