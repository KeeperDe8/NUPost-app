@extends('layouts.requestor')

@section('title', 'Account Settings')

@section('head-styles')
<style>
.main { max-width: 600px; margin: 0 auto; padding: 32px 24px; }
.breadcrumb { display: flex; align-items: center; gap: 6px; margin-bottom: 6px; font-size: 12.5px; }
.breadcrumb a { color: var(--color-primary); text-decoration: none; display: flex; align-items: center; gap: 4px; }
.breadcrumb a:hover { text-decoration: underline; }
.page-header { margin-bottom: 20px; }
.page-header h1 { font-size: 20px; font-weight: 700; }
.page-header p { font-size: 13px; color: var(--color-text-muted); margin-top: 3px; }
.alert { padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; font-weight: 500; }
.alert--success { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
.alert--error   { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
.section-card { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); padding: 22px 24px; margin-bottom: 16px; }
.section-header { display: flex; align-items: center; gap: 8px; font-size: 13.5px; font-weight: 600; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--color-border); }
.field { margin-bottom: 14px; }
.field label { display: block; font-size: 11.5px; font-weight: 500; color: var(--color-text-muted); margin-bottom: 5px; }
.field input[type="password"] { width: 100%; border: 1px solid var(--color-border); border-radius: 7px; padding: 9px 12px; font-size: 13px; font-family: var(--font); outline: none; transition: border-color .15s; }
.field input:focus { border-color: var(--color-primary); }
.field input::placeholder { color: #d1d5db; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.btn-primary { padding: 9px 20px; background: var(--color-primary); color: white; border: none; border-radius: 7px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: var(--font); margin-top: 16px; }
.btn-primary:hover { background: var(--color-primary-light); }
.toggle-row { display: flex; align-items: flex-start; justify-content: space-between; padding: 14px 0; border-bottom: 1px solid #f3f4f6; gap: 16px; cursor: pointer; }
.toggle-row:last-child { border-bottom: none; padding-bottom: 0; }
.toggle-row:hover { background: #fafafa; }
.toggle-label { font-size: 13px; font-weight: 500; color: var(--color-text); margin-bottom: 2px; }
.toggle-desc { font-size: 11.5px; color: var(--color-text-muted); line-height: 1.4; }
.toggle-switch { position: relative; display: inline-block; width: 44px; height: 24px; flex-shrink: 0; margin-top: 2px; }
.toggle-switch input { opacity: 0; width: 0; height: 0; position: absolute; }
.toggle-slider { position: absolute; cursor: pointer; inset: 0; background: #d1d5db; border-radius: 24px; transition: background .25s; }
.toggle-slider:before { content: ""; position: absolute; width: 18px; height: 18px; left: 3px; top: 3px; background: white; border-radius: 50%; transition: transform .25s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
.toggle-switch input:checked + .toggle-slider { background: var(--color-primary); }
.toggle-switch input:checked + .toggle-slider:before { transform: translateX(20px); }
.toggle-state { font-size: 11px; font-weight: 600; color: var(--color-text-muted); margin-top: 4px; text-align: center; display: block; }
.toggle-state.on { color: var(--color-primary); }
.toggle-wrap { display: flex; flex-direction: column; align-items: center; flex-shrink: 0; }
.email-badge { display: inline-flex; align-items: center; gap: 6px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 8px 12px; font-size: 12px; color: #1e40af; margin-top: 14px; margin-bottom: 4px; width: 100%; }
.session-info { background: #f9fafb; border: 1px solid var(--color-border); border-radius: 8px; padding: 12px 14px; font-size: 12px; color: var(--color-text-muted); margin-top: 14px; }
.save-all-wrap { display: flex; justify-content: flex-end; margin-top: 4px; }
.btn-save-all { padding: 10px 28px; background: var(--color-primary); color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; font-family: var(--font); }
.btn-save-all:hover { background: var(--color-primary-light); }
</style>
@endsection

@section('content')
<main class="main">
    <div class="breadcrumb">
        <a href="{{ route('requestor.profile') }}">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Profile
        </a>
    </div>
    <div class="page-header">
        <h1>Account Settings</h1>
        <p>Manage your account preferences and security settings.</p>
    </div>

    @if(session('success'))
        <div class="alert alert--success">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert--error">❌ {{ session('error') }}</div>
    @endif

    <!-- PASSWORD -->
    <div class="section-card">
        <div class="section-header">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Password &amp; Security
        </div>
        <form method="POST" action="{{ route('requestor.settings.password') }}">
            @csrf
            <div class="field">
                <label>Current Password</label>
                <input type="password" name="current_password" placeholder="Enter current password" autocomplete="current-password">
            </div>
            <div class="field-row">
                <div class="field">
                    <label>New Password</label>
                    <input type="password" name="new_password" placeholder="Min. 8 chars" autocomplete="new-password">
                </div>
                <div class="field">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" placeholder="Confirm new password" autocomplete="new-password">
                </div>
            </div>
            <button type="submit" class="btn-primary">Change Password</button>
        </form>
        <div class="session-info">
            <strong>Logged in as:</strong> {{ $user->email ?? '—' }}
        </div>
    </div>

    <!-- NOTIFICATIONS + PRIVACY -->
    <form method="POST" action="{{ route('requestor.settings.save') }}" id="settings-form">
        @csrf
        <div class="section-card">
            <div class="section-header">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                Notification Preferences
            </div>
            <div class="email-badge">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Notifications will be sent to: <strong>{{ $user->email ?? '—' }}</strong>
            </div>
            <div class="toggle-row" onclick="toggleSwitch('email_notif')">
                <div>
                    <div class="toggle-label">Email Notifications</div>
                    <div class="toggle-desc">Receive all notifications via Gmail when enabled</div>
                </div>
                <div class="toggle-wrap">
                    <label class="toggle-switch" onclick="event.stopPropagation()">
                        <input type="checkbox" name="email_notif" id="email_notif"
                               {{ $user->email_notif ? 'checked' : '' }}
                               onchange="updateState(this); syncStatusToggle()">
                        <span class="toggle-slider"></span>
                    </label>
                    <span class="toggle-state {{ $user->email_notif ? 'on' : '' }}" id="state_email_notif">
                        {{ $user->email_notif ? 'ON' : 'OFF' }}
                    </span>
                </div>
            </div>
            <div class="toggle-row" id="status-row"
                 onclick="toggleSwitch('status_updates')"
                 style="{{ !$user->email_notif ? 'opacity:.45;pointer-events:none;' : '' }}">
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
                    <span class="toggle-state {{ $user->status_updates ? 'on' : '' }}" id="state_status_updates">
                        {{ $user->status_updates ? 'ON' : 'OFF' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
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
                    <span class="toggle-state {{ $user->public_profile ? 'on' : '' }}" id="state_public_profile">
                        {{ $user->public_profile ? 'ON' : 'OFF' }}
                    </span>
                </div>
            </div>
        </div>

        <div class="save-all-wrap">
            <button type="submit" class="btn-save-all">Save All Settings</button>
        </div>
    </form>
</main>
@endsection

@section('scripts')
<script>
function toggleSwitch(id) {
    const cb = document.getElementById(id);
    if (!cb || cb.disabled) return;
    cb.checked = !cb.checked;
    updateState(cb);
    if (id === 'email_notif') syncStatusToggle();
}
function updateState(cb) {
    const stateEl = document.getElementById('state_' + cb.id);
    if (!stateEl) return;
    stateEl.textContent = cb.checked ? 'ON' : 'OFF';
    stateEl.classList.toggle('on', cb.checked);
}
function syncStatusToggle() {
    const master = document.getElementById('email_notif');
    const row    = document.getElementById('status-row');
    const sub    = document.getElementById('status_updates');
    if (!master || !row || !sub) return;
    if (master.checked) {
        row.style.opacity = '1'; row.style.pointerEvents = 'auto';
    } else {
        row.style.opacity = '0.45'; row.style.pointerEvents = 'none';
        sub.checked = false; updateState(sub);
    }
}
document.querySelectorAll('.toggle-switch input[type="checkbox"]').forEach(cb => updateState(cb));
syncStatusToggle();
</script>
@endsection