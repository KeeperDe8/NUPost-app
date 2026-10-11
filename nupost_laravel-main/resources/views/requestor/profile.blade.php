@extends('layouts.requestor')

@section('title', 'Profile')
@section('page-title', 'Profile')

@section('head-styles')
<style>
.page-outer {
    min-height: calc(100vh - 64px);
    display: flex; flex-direction: column;
    align-items: center; padding: 36px 24px 60px;
}
.page-inner { width: 100%; max-width: 820px; }

.page-header { margin-bottom: 24px; }
.page-header h1 { font-size: 24px; font-weight: 800; letter-spacing: -0.5px; color: var(--text); }
.page-header p  { font-size: 13.5px; color: var(--text-muted); margin-top: 4px; }

.profile-card {
    background: white; border-radius: 20px;
    border: 1.5px solid var(--border);
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    overflow: hidden;
}

/* HERO */
.profile-hero {
    background: linear-gradient(125deg, #001a6e 0%, #0a2fa8 50%, #1a4fd6 100%);
    padding: 36px 40px;
    display: flex; align-items: center; justify-content: space-between; gap: 20px;
    position: relative; overflow: hidden;
}
.profile-hero::before {
    content: ''; position: absolute; top: -60px; right: -60px;
    width: 280px; height: 280px;
    background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
    pointer-events: none;
}
.profile-hero::after {
    content: ''; position: absolute; bottom: -40px; left: 20%;
    width: 160px; height: 160px;
    background: radial-gradient(circle, rgba(59,110,245,0.15) 0%, transparent 70%);
    pointer-events: none;
}
.profile-hero__left { display: flex; align-items: center; gap: 20px; position: relative; z-index: 1; }
.profile-avatar {
    width: 72px; height: 72px; border-radius: 50%;
    background: rgba(255,255,255,0.15);
    border: 3px solid rgba(255,255,255,0.3);
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
}
.profile-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.profile-avatar svg { color: white; }
.profile-hero-name { font-size: 22px; font-weight: 800; color: white; letter-spacing: -0.4px; }
.profile-hero-role {
    display: inline-flex; align-items: center; margin-top: 6px;
    font-size: 11.5px; font-weight: 700; letter-spacing: 0.3px;
    padding: 4px 12px; border-radius: 20px;
    background: rgba(255,255,255,0.15); color: rgba(255,255,255,0.9);
    border: 1px solid rgba(255,255,255,0.2);
}
.profile-hero__edit {
    position: relative; z-index: 1; flex-shrink: 0;
    display: flex; align-items: center; gap: 7px;
    padding: 10px 20px;
    background: rgba(255,255,255,0.12);
    border: 1.5px solid rgba(255,255,255,0.3);
    border-radius: 12px; color: white;
    font-size: 13.5px; font-weight: 700; font-family: var(--font);
    text-decoration: none; transition: background .15s;
}
.profile-hero__edit:hover { background: rgba(255,255,255,0.22); }

/* MEMBER SINCE */
.profile-member {
    padding: 12px 40px; border-bottom: 1.5px solid var(--border);
    font-size: 12.5px; color: var(--text-faint); background: #fafbfc;
}
.profile-member strong { color: var(--text-muted); font-weight: 600; }

/* INFO */
.profile-info { padding: 32px 40px; border-bottom: 1.5px solid var(--border); }
.section-label {
    font-size: 10.5px; font-weight: 800; letter-spacing: 1.2px;
    text-transform: uppercase; color: var(--text-faint); margin-bottom: 20px;
}
.profile-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 26px 40px; }
.info-field-label {
    display: flex; align-items: center; gap: 6px;
    font-size: 10.5px; font-weight: 700; color: var(--text-faint);
    text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;
}
.info-field-value { font-size: 14px; color: var(--text); font-weight: 500; }

/* STATS */
.profile-stats { padding: 32px 40px; border-bottom: 1.5px solid var(--border); }
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
.stat-box {
    border: 1.5px solid var(--border); border-radius: 16px;
    padding: 20px 12px; text-align: center;
    background: #fafbfc; transition: border-color .15s, transform .15s;
}
.stat-box:hover { border-color: #93c5fd; transform: translateY(-2px); }
.stat-box__num { font-size: 30px; font-weight: 800; line-height: 1; margin-bottom: 6px; letter-spacing: -1px; }
.stat-box__num--total    { color: var(--text); }
.stat-box__num--pending  { color: #d97706; }
.stat-box__num--approved { color: #16a34a; }
.stat-box__num--posted   { color: #7c3aed; }
.stat-box__label { font-size: 11.5px; color: var(--text-faint); font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }

/* ACTIONS */
.profile-actions { padding: 32px 40px; }
.action-list { display: flex; flex-direction: column; gap: 10px; }
.action-item {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 20px; border-radius: 14px;
    border: 1.5px solid var(--border); text-decoration: none;
    color: var(--text); font-size: 14px; font-weight: 600;
    transition: all .15s; background: white;
    font-family: var(--font); cursor: pointer; width: 100%;
}
.action-item:hover { background: #f5f8ff; border-color: #93c5fd; }
.action-item--danger { color: #dc2626; border-color: #fecaca; }
.action-item--danger:hover { background: #fff5f5; border-color: #fca5a5; }
.action-item__left { display: flex; align-items: center; gap: 14px; }
.action-item__icon {
    width: 38px; height: 38px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.action-item__icon--blue { background: #dbeafe; color: #2563eb; }
.action-item__icon--green { background: #dcfce7; color: #16a34a; }
.action-item__icon--red   { background: #fee2e2; color: #dc2626; }
.action-item__arrow { color: var(--text-faint); font-size: 16px; }

@media (max-width: 600px) {
    .profile-info-grid { grid-template-columns: 1fr; }
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .profile-hero { flex-direction: column; align-items: flex-start; padding: 28px 24px; }
    .profile-info, .profile-stats, .profile-actions { padding-left: 24px; padding-right: 24px; }
    .profile-member { padding-left: 24px; padding-right: 24px; }
}

/* ── DARK THEME OVERRIDES ── */
[data-theme="dark"] .profile-card {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
}
[data-theme="dark"] .profile-member {
    background: #06112a !important;
    border-bottom-color: rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .profile-info {
    border-bottom-color: rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .profile-stats {
    border-bottom-color: rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .stat-box {
    background: #06112a !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
}
[data-theme="dark"] .stat-box:hover {
    border-color: #3b82f6 !important;
}
[data-theme="dark"] .action-item {
    background: #0b1a3d !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    color: #f8fafc !important;
}
[data-theme="dark"] .action-item:hover {
    background: #0f2250 !important;
    border-color: #3b82f6 !important;
}
[data-theme="dark"] .action-item--danger {
    background: rgba(239, 68, 68, 0.1) !important;
    border-color: rgba(239, 68, 68, 0.3) !important;
    color: #fca5a5 !important;
}
[data-theme="dark"] .action-item--danger:hover {
    background: rgba(239, 68, 68, 0.2) !important;
}
</style>
@endsection

@section('content')
<div class="page-outer">
<div class="page-inner">

    <div class="page-header">
        <h1>Profile</h1>
        <p>Manage your account information and settings</p>
    </div>

    <div class="profile-card">
        <div class="profile-hero">
            <div class="profile-hero__left">
                <div class="profile-avatar">
                    @if($user->profile_photo && file_exists(public_path('uploads/' . $user->profile_photo)))
                        <img src="/uploads/{{ $user->profile_photo }}?v={{ time() }}" alt="Profile Photo">
                    @else
                        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    @endif
                </div>
                <div>
                    <div class="profile-hero-name">{{ $user->name }}</div>
                    <div class="profile-hero-role">Requestor</div>
                </div>
            </div>
            <a href="{{ route('requestor.profile.edit') }}" class="profile-hero__edit">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit Profile
            </a>
        </div>

        <div class="profile-member">Member since: <strong>{{ $member_since }}</strong></div>

        <div class="profile-info">
            <div class="section-label">Personal Information</div>
            <div class="profile-info-grid">
                <div>
                    <div class="info-field-label"><svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg> Email Address</div>
                    <div class="info-field-value">{{ $user->email ?? '—' }}</div>
                </div>
                <div>
                    <div class="info-field-label"><svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.36 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.64a16 16 0 0 0 6.29 6.29l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg> Phone Number</div>
                    <div class="info-field-value">{{ $user->phone ?? '—' }}</div>
                </div>
                <div>
                    <div class="info-field-label"><svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><line x1="8" y1="6" x2="10" y2="6"/><line x1="14" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="10" y2="10"/><line x1="14" y1="10" x2="16" y2="10"/></svg> Office</div>
                    <div class="info-field-value">{{ $user->organization ?? '—' }}</div>
                </div>
                <div>
                    <div class="info-field-label"><svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg> Department</div>
                    <div class="info-field-value">{{ $user->department ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="profile-stats">
            <div class="section-label">Statistics</div>
            <div class="stats-grid">
                <div class="stat-box"><div class="stat-box__num stat-box__num--total">{{ $total }}</div><div class="stat-box__label">Total</div></div>
                <div class="stat-box"><div class="stat-box__num stat-box__num--pending">{{ $pending }}</div><div class="stat-box__label">Pending</div></div>
                <div class="stat-box"><div class="stat-box__num stat-box__num--approved">{{ $approved }}</div><div class="stat-box__label">Approved</div></div>
                <div class="stat-box"><div class="stat-box__num stat-box__num--posted">{{ $posted }}</div><div class="stat-box__label">Posted</div></div>
            </div>
        </div>

        <div class="profile-actions">
            <div class="section-label">Settings &amp; Actions</div>
            <div class="action-list">
                <a href="{{ route('requestor.settings') }}" class="action-item">
                    <span class="action-item__left">
                        <span class="action-item__icon action-item__icon--blue"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg></span>
                        Account Settings
                    </span>
                    <span class="action-item__arrow">→</span>
                </a>
                <a href="{{ route('requestor.profile.edit') }}" class="action-item">
                    <span class="action-item__left">
                        <span class="action-item__icon action-item__icon--green"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></span>
                        Edit Profile
                    </span>
                    <span class="action-item__arrow">→</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" style="display:contents;">
                    @csrf
                    <button type="submit" class="action-item action-item--danger">
                        <span class="action-item__left">
                            <span class="action-item__icon action-item__icon--red"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg></span>
                            Logout
                        </span>
                        <span class="action-item__arrow">→</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
</div>
@endsection