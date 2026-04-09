@extends('layouts.requestor')

@section('title', 'Profile')

@section('head-styles')
<style>
.main { max-width: 680px; margin: 0 auto; padding: 32px 24px; }
.page-header { margin-bottom: 20px; }
.page-header h1 { font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
.page-header p { font-size: 13px; color: var(--color-text-muted); margin-top: 3px; }
.profile-card { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
.profile-hero { background: var(--color-primary); padding: 24px 28px; display: flex; align-items: center; gap: 16px; }
.profile-avatar { width: 56px; height: 56px; border-radius: 50%; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; border: 2px solid rgba(255,255,255,0.3); }
.profile-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.profile-avatar svg { color: white; }
.profile-hero-name { font-size: 17px; font-weight: 700; color: white; }
.profile-hero-role { font-size: 12.5px; color: rgba(255,255,255,0.7); margin-top: 2px; }
.profile-info { padding: 24px 28px; border-bottom: 1px solid var(--color-border); }
.profile-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px 32px; }
.info-field-label { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 500; color: var(--color-text-muted); margin-bottom: 4px; }
.info-field-value { font-size: 13px; color: var(--color-text); }
.profile-member { padding: 12px 28px; border-bottom: 1px solid var(--color-border); }
.profile-member p { font-size: 12px; color: var(--color-text-muted); }
.profile-member strong { color: var(--color-text); font-weight: 500; }
.profile-stats { padding: 24px 28px; border-bottom: 1px solid var(--color-border); }
.profile-stats h2 { font-size: 14px; font-weight: 600; margin-bottom: 16px; }
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.stat-box { border: 1px solid var(--color-border); border-radius: 8px; padding: 14px 10px; text-align: center; }
.stat-box__num { font-size: 22px; font-weight: 700; line-height: 1; margin-bottom: 4px; }
.stat-box__num--total    { color: var(--color-text); }
.stat-box__num--pending  { color: #f59e0b; }
.stat-box__num--approved { color: #10b981; }
.stat-box__num--posted   { color: #8b5cf6; }
.stat-box__label { font-size: 11.5px; color: var(--color-text-muted); }
.profile-actions { padding: 24px 28px; }
.profile-actions h2 { font-size: 14px; font-weight: 600; margin-bottom: 12px; }
.action-list { display: flex; flex-direction: column; gap: 6px; }
.action-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; border-radius: 8px; border: 1px solid var(--color-border); text-decoration: none; color: var(--color-text); font-size: 13px; font-weight: 500; transition: background .15s; background: white; }
.action-item:hover { background: var(--color-bg); }
.action-item--danger { color: #ef4444; border-color: #fecaca; }
.action-item--danger:hover { background: #fff5f5; }
.action-item__left { display: flex; align-items: center; gap: 10px; }
.action-item__arrow { color: var(--color-text-muted); }
@media (max-width: 600px) { .profile-info-grid { grid-template-columns: 1fr; } .stats-grid { grid-template-columns: repeat(2, 1fr); } }
</style>
@endsection

@section('content')
<main class="main">
    <div class="page-header">
        <h1>Profile</h1>
        <p>Manage your account information and settings</p>
    </div>

    <div class="profile-card">
        <div class="profile-hero">
            <div class="profile-avatar">
                @if($user->profile_photo && file_exists(public_path('uploads/' . $user->profile_photo)))
                    <img src="/uploads/{{ $user->profile_photo }}?v={{ time() }}" alt="Profile Photo">
                @else
                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                @endif
            </div>
            <div>
                <div class="profile-hero-name">{{ $user->name }}</div>
                <div class="profile-hero-role">Requester</div>
            </div>
        </div>

        <div class="profile-info">
            <div class="profile-info-grid">
                <div>
                    <div class="info-field-label">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        Email Address
                    </div>
                    <div class="info-field-value">{{ $user->email ?? '—' }}</div>
                </div>
                <div>
                    <div class="info-field-label">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.36 2 2 0 0 1 3.6 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.64a16 16 0 0 0 6.29 6.29l.96-.96a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        Phone Number
                    </div>
                    <div class="info-field-value">{{ $user->phone ?? '—' }}</div>
                </div>
                <div>
                    <div class="info-field-label">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Organization
                    </div>
                    <div class="info-field-value">{{ $user->organization ?? '—' }}</div>
                </div>
                <div>
                    <div class="info-field-label">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                        Department
                    </div>
                    <div class="info-field-value">{{ $user->department ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="profile-member">
            <p>Member since: <strong>{{ $member_since }}</strong></p>
        </div>

        <div class="profile-stats">
            <h2>Your Statistics</h2>
            <div class="stats-grid">
                <div class="stat-box"><div class="stat-box__num stat-box__num--total">{{ $total }}</div><div class="stat-box__label">Total Requests</div></div>
                <div class="stat-box"><div class="stat-box__num stat-box__num--pending">{{ $pending }}</div><div class="stat-box__label">Pending</div></div>
                <div class="stat-box"><div class="stat-box__num stat-box__num--approved">{{ $approved }}</div><div class="stat-box__label">Approved</div></div>
                <div class="stat-box"><div class="stat-box__num stat-box__num--posted">{{ $posted }}</div><div class="stat-box__label">Posted</div></div>
            </div>
        </div>

        <div class="profile-actions">
            <h2>Settings &amp; Actions</h2>
            <div class="action-list">
                <a href="{{ route('requestor.settings') }}" class="action-item">
                    <span class="action-item__left">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                        Account Settings
                    </span>
                    <span class="action-item__arrow">→</span>
                </a>
                <a href="{{ route('requestor.profile.edit') }}" class="action-item">
                    <span class="action-item__left">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Edit Profile
                    </span>
                    <span class="action-item__arrow">→</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="action-item action-item--danger" style="width:100%;cursor:pointer;">
                        <span class="action-item__left">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            Logout
                        </span>
                        <span class="action-item__arrow">→</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection