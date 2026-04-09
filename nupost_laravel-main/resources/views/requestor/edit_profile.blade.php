@extends('layouts.requestor')

@section('title', 'Edit Profile')

@section('head-styles')
<style>
.main { max-width: 600px; margin: 0 auto; padding: 32px 24px; }
.breadcrumb { display: flex; align-items: center; gap: 6px; margin-bottom: 6px; font-size: 12.5px; }
.breadcrumb a { color: var(--color-primary); text-decoration: none; display: flex; align-items: center; gap: 4px; }
.breadcrumb a:hover { text-decoration: underline; }
.page-header { margin-bottom: 20px; }
.page-header h1 { font-size: 20px; font-weight: 700; letter-spacing: -0.3px; }
.page-header p { font-size: 13px; color: var(--color-text-muted); margin-top: 3px; }
.alert { padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; font-weight: 500; }
.alert--success { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
.alert--error   { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
.card { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); padding: 24px; margin-bottom: 16px; }
.card-title { font-size: 13.5px; font-weight: 600; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 1px solid var(--color-border); }
.photo-section { display: flex; align-items: center; gap: 18px; }
.photo-avatar { width: 72px; height: 72px; border-radius: 50%; background: #dbeafe; display: flex; align-items: center; justify-content: center; flex-shrink: 0; overflow: hidden; }
.photo-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.photo-avatar svg { color: #3b82f6; }
.btn-upload { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; background: var(--color-primary); color: white; border: none; border-radius: 7px; font-size: 12.5px; font-weight: 600; cursor: pointer; font-family: var(--font); margin-bottom: 6px; }
.btn-upload:hover { background: var(--color-primary-light); }
.photo-hint { font-size: 11.5px; color: var(--color-text-muted); }
#photo-input { display: none; }
.photo-preview-name { font-size: 11.5px; color: var(--color-primary); margin-top: 4px; font-weight: 500; }
.field { margin-bottom: 14px; }
.field label { display: flex; align-items: center; gap: 5px; font-size: 11.5px; font-weight: 500; color: var(--color-text-muted); margin-bottom: 5px; }
.field input[type="text"], .field input[type="email"], .field input[type="tel"], .field textarea {
    width: 100%; border: 1px solid var(--color-border); border-radius: 7px;
    padding: 9px 12px; font-size: 13px; font-family: var(--font);
    color: var(--color-text); background: white; outline: none; transition: border-color .15s;
}
.field input:focus, .field textarea:focus { border-color: var(--color-primary); }
.field input::placeholder, .field textarea::placeholder { color: #d1d5db; }
.field textarea { resize: vertical; min-height: 90px; }
.field-hint { font-size: 11px; color: #9ca3af; margin-top: 4px; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.req { color: #ef4444; margin-left: 2px; }
.form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }
.btn-cancel { padding: 9px 22px; border-radius: 7px; border: 1px solid var(--color-border); background: white; font-size: 13px; font-weight: 500; cursor: pointer; color: var(--color-text); font-family: var(--font); text-decoration: none; display: inline-flex; align-items: center; }
.btn-cancel:hover { background: var(--color-bg); }
.btn-save { padding: 9px 22px; border-radius: 7px; border: none; background: var(--color-primary); color: white; font-size: 13px; font-weight: 600; cursor: pointer; font-family: var(--font); }
.btn-save:hover { background: var(--color-primary-light); }
@media (max-width: 600px) { .field-row { grid-template-columns: 1fr; } }
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
        <h1>Edit Profile</h1>
        <p>Update your personal information and profile details.</p>
    </div>

    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }} <a href="{{ route('requestor.profile') }}" style="color:inherit;font-weight:700;">View Profile →</a></div>
    @endif
    @if(session('error'))
        <div class="alert alert--error">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('requestor.profile.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-title">Profile Photo</div>
            <div class="photo-section">
                <div class="photo-avatar" id="photo-preview">
                    @if($user->profile_photo && file_exists(public_path('uploads/' . $user->profile_photo)))
                        <img src="/uploads/{{ $user->profile_photo }}?v={{ time() }}" alt="Avatar">
                    @else
                        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    @endif
                </div>
                <div class="photo-info">
                    <label class="btn-upload" for="photo-input">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                        Upload Photo
                    </label>
                    <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/png,image/gif,image/webp">
                    <div class="photo-hint">JPG, PNG, GIF or WEBP (Max 5MB)</div>
                    <div class="photo-preview-name" id="photo-name"></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-title">Personal Information</div>
            <div class="field-row">
                <div class="field">
                    <label>Full Name <span class="req">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="Your full name" required>
                </div>
                <div class="field">
                    <label>Email Address <span class="req">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="your@email.com" required>
                </div>
            </div>
            <div class="field-row">
                <div class="field">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+63 912 345 6789">
                </div>
                <div class="field">
                    <label>Organization</label>
                    <input type="text" name="organization" value="{{ old('organization', $user->organization) }}" placeholder="e.g., Student Council">
                </div>
            </div>
            <div class="field">
                <label>Department</label>
                <input type="text" name="department" value="{{ old('department', $user->department) }}" placeholder="e.g., College of Computing">
            </div>
            <div class="field">
                <label>Bio (Optional)</label>
                <textarea name="bio" placeholder="Tell us about yourself..." maxlength="250">{{ old('bio', $user->bio) }}</textarea>
                <div class="field-hint">Maximum 250 characters.</div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('requestor.profile') }}" class="btn-cancel">Cancel</a>
            <button type="submit" class="btn-save">Save Changes</button>
        </div>
    </form>
</main>
@endsection

@section('scripts')
<script>
document.getElementById('photo-input').addEventListener('change', function() {
    const file = this.files[0];
    if (!file) return;
    document.getElementById('photo-name').textContent = '📎 ' + file.name;
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('photo-preview');
        preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview">';
    };
    reader.readAsDataURL(file);
});
</script>
@endsection