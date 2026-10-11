@extends('layouts.requestor')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

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

/* CARDS */
.form-card {
    background: white; border-radius: 20px;
    border: 1.5px solid var(--border);
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    padding: 32px 40px; margin-bottom: 16px;
    transition: box-shadow .2s;
}
.form-card:focus-within {
    box-shadow: 0 4px 20px rgba(59,110,245,0.08);
    border-color: rgba(59,110,245,0.25);
}
.card-title {
    font-size: 10.5px; font-weight: 800; letter-spacing: 1.2px;
    text-transform: uppercase; color: var(--text-faint);
    margin-bottom: 24px; padding-bottom: 16px;
    border-bottom: 1.5px solid var(--border);
}

/* PHOTO SECTION */
.photo-section { display: flex; align-items: center; gap: 24px; }
.photo-avatar {
    width: 84px; height: 84px; border-radius: 50%;
    background: #dbeafe; border: 3px solid #bfdbfe;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; overflow: hidden;
}
.photo-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.photo-avatar svg { color: #3b82f6; }
.btn-upload {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 20px; background: #001a6e; color: white;
    border: none; border-radius: 11px; font-size: 13.5px; font-weight: 700;
    cursor: pointer; font-family: var(--font); margin-bottom: 7px; transition: all .15s;
}
.btn-upload:hover { background: #00237a; transform: translateY(-1px); }
.photo-hint { font-size: 12px; color: var(--text-faint); }
#photo-input { display: none; }
.photo-preview-name { font-size: 12px; color: #3b6ef5; margin-top: 5px; font-weight: 600; }

/* FIELDS */
.field { margin-bottom: 18px; }
.field:last-child { margin-bottom: 0; }
.field label {
    display: block; font-size: 11px; font-weight: 700;
    color: var(--text-faint); margin-bottom: 7px;
    text-transform: uppercase; letter-spacing: 0.4px;
}
.field label .req { color: #ef4444; margin-left: 2px; }
.field label .opt { color: var(--text-faint); font-weight: 400; text-transform: none; font-size: 11px; }
.field input[type="text"],
.field input[type="email"],
.field input[type="tel"],
.field textarea {
    width: 100%; border: 1.5px solid var(--border); border-radius: 12px;
    padding: 12px 16px; font-size: 14px; font-family: var(--font);
    color: var(--text); background: #fafbfd; outline: none;
    transition: border-color .15s, box-shadow .15s, background .15s;
}
.field input:focus, .field textarea:focus {
    border-color: #3b6ef5; background: white;
    box-shadow: 0 0 0 3px rgba(59,110,245,0.08);
}
.field input::placeholder, .field textarea::placeholder { color: #c8cdd8; }
.field textarea { resize: vertical; min-height: 100px; line-height: 1.6; }
.field-hint { font-size: 11.5px; color: var(--text-faint); margin-top: 6px; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }

/* ACTIONS */
.form-actions {
    display: flex; justify-content: space-between; align-items: center;
    padding: 22px 40px;
    background: white; border-radius: 20px;
    border: 1.5px solid var(--border);
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
.form-actions__hint { font-size: 12.5px; color: var(--text-faint); }
.form-actions__hint span { color: #ef4444; }
.actions-right { display: flex; gap: 10px; }
.btn-cancel {
    padding: 11px 26px; border-radius: 11px;
    border: 1.5px solid var(--border); background: white;
    font-size: 13.5px; font-weight: 600; cursor: pointer;
    color: var(--text-muted); font-family: var(--font); text-decoration: none;
    display: inline-flex; align-items: center; transition: all .15s;
}
.btn-cancel:hover { background: #f3f4f6; border-color: #c8cdd8; color: var(--text); }
.btn-save {
    padding: 11px 28px; border-radius: 11px; border: none;
    background: #001a6e; color: white; font-size: 13.5px; font-weight: 700;
    cursor: pointer; font-family: var(--font); transition: all .15s;
    box-shadow: 0 3px 12px rgba(0,26,110,0.3);
}
.btn-save:hover { background: #00237a; transform: translateY(-1px); box-shadow: 0 5px 18px rgba(0,26,110,0.35); }

@media (max-width: 600px) {
    .field-row { grid-template-columns: 1fr; }
    .form-card { padding: 24px; }
    .form-actions { flex-direction: column; gap: 14px; padding: 20px 24px; }
}

/* ── DARK THEME OVERRIDES ── */
[data-theme="dark"] .form-card {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
}
[data-theme="dark"] .card-title {
    border-bottom-color: rgba(30, 79, 216, 0.25) !important;
    color: #94a3b8 !important;
}
[data-theme="dark"] .form-actions {
    background: #081636 !important;
    border-color: rgba(30, 79, 216, 0.25) !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3) !important;
}
[data-theme="dark"] .btn-cancel {
    background: #0b1a3d !important;
    border-color: rgba(30, 79, 216, 0.3) !important;
    color: #cbd5e1 !important;
}
[data-theme="dark"] .btn-cancel:hover {
    background: #0f2250 !important;
    color: #f8fafc !important;
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
        <h1>Edit Profile</h1>
        <p>Update your personal information and profile details</p>
    </div>

    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }} <a href="{{ route('requestor.profile') }}" style="color:inherit;font-weight:700;">View Profile →</a></div>
    @endif
    @if(session('error'))
        <div class="alert alert--error">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('requestor.profile.update') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-card">
            <div class="card-title">Profile Photo</div>
            <div class="photo-section">
                <div class="photo-avatar" id="photo-preview">
                    @if($user->profile_photo && file_exists(public_path('uploads/' . $user->profile_photo)))
                        <img src="/uploads/{{ $user->profile_photo }}?v={{ time() }}" alt="Avatar">
                    @else
                        <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    @endif
                </div>
                <div>
                    <label class="btn-upload" for="photo-input">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
                        Upload Photo
                    </label>
                    <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/png,image/gif,image/webp">
                    <div class="photo-hint">JPG, PNG, GIF or WEBP · Max 5MB</div>
                    <div class="photo-preview-name" id="photo-name"></div>
                </div>
            </div>
        </div>

        <div class="form-card">
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
                    <label>Office</label>
                    <input type="text" name="organization" value="{{ old('organization', $user->organization) }}" placeholder="e.g., Marketing Office">
                </div>
            </div>
            <div class="field">
                <label>Department</label>
                <input type="text" name="department" value="{{ old('department', $user->department) }}" placeholder="e.g., College of Computing">
            </div>
            <div class="field">
                <label>Bio <span class="opt">(Optional)</span></label>
                <textarea name="bio" placeholder="Tell us about yourself..." maxlength="250">{{ old('bio', $user->bio) }}</textarea>
                <div class="field-hint">Maximum 250 characters.</div>
            </div>
        </div>

        <div class="form-actions">
            <div class="form-actions__hint">Fields marked <span>*</span> are required</div>
            <div class="actions-right">
                <a href="{{ route('requestor.profile') }}" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-save">Save Changes</button>
            </div>
        </div>
    </form>

</div>
</div>
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