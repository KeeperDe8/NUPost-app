@extends('layouts.requestor')

@section('title', 'Create Request')

@section('head-styles')
<style>
.main { max-width: 680px; margin: 0 auto; padding: 32px 24px; }
.page-header { margin-bottom: 24px; }
.page-header h1 { font-size: 20px; font-weight: 700; letter-spacing: -0.3px; }
.page-header p { font-size: 13px; color: var(--color-text-muted); margin-top: 3px; }
.alert { padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 16px; font-weight: 500; }
.alert--success { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
.alert--error   { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
.form-section { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); padding: 24px; margin-bottom: 16px; }
.section-title { font-size: 14px; font-weight: 600; margin-bottom: 18px; }
.field { margin-bottom: 16px; }
.field:last-child { margin-bottom: 0; }
.field label { display: block; font-size: 12px; font-weight: 500; color: var(--color-text-muted); margin-bottom: 6px; }
.field label span { color: #ef4444; }
.field input[type="text"], .field input[type="date"], .field select, .field textarea {
    width: 100%; border: 1px solid var(--color-border); border-radius: 7px;
    padding: 9px 12px; font-size: 13px; font-family: var(--font);
    color: var(--color-text); background: white; outline: none; transition: border-color .15s;
}
.field input:focus, .field select:focus, .field textarea:focus { border-color: var(--color-primary); }
.field textarea { resize: vertical; min-height: 100px; }
.field input::placeholder, .field textarea::placeholder { color: #d1d5db; }
.field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.platform-group { display: flex; gap: 8px; flex-wrap: wrap; }
.platform-btn { padding: 8px 20px; border-radius: 7px; border: 1px solid var(--color-border); font-size: 13px; font-weight: 500; cursor: pointer; background: white; color: var(--color-text); font-family: var(--font); transition: all .15s; }
.platform-btn.selected { background: var(--color-primary); color: white; border-color: var(--color-primary); }
.platform-btn:hover:not(.selected) { background: var(--color-bg); }
.upload-area { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 8px; align-items: flex-start; }
.upload-box { width: 80px; height: 80px; border: 1.5px dashed var(--color-border); border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; cursor: pointer; color: var(--color-text-muted); font-size: 11px; gap: 5px; transition: border-color .15s, background .15s; background: #fafafa; }
.upload-box:hover { border-color: var(--color-primary); background: #f0f4ff; }
.upload-box svg { color: #9ca3af; }
.upload-hint { font-size: 11.5px; color: var(--color-text-muted); }
#media-input { display: none; }
.preview-wrap { position: relative; width: 80px; height: 80px; }
.preview-thumb { width: 80px; height: 80px; border-radius: 8px; object-fit: cover; border: 1px solid var(--color-border); display: block; }
.preview-remove { position: absolute; top: -6px; right: -6px; width: 20px; height: 20px; background: #ef4444; color: white; border: none; border-radius: 50%; font-size: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.ai-section { background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: var(--radius); padding: 20px; margin-bottom: 16px; }
.ai-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.ai-title { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; color: #5b21b6; }
.ai-generate-btn { display: flex; align-items: center; gap: 6px; padding: 7px 14px; background: #6d28d9; color: white; border: none; border-radius: 7px; font-size: 12.5px; font-weight: 500; cursor: pointer; font-family: var(--font); }
.ai-generate-btn:hover { opacity: .85; }
.ai-generate-btn:disabled { opacity: .5; cursor: not-allowed; }
.ai-textarea { width: 100%; border: 1px solid #ddd6fe; border-radius: 7px; padding: 10px 12px; font-size: 13px; font-family: var(--font); background: white; color: var(--color-text); outline: none; resize: vertical; min-height: 90px; }
.ai-textarea:focus { border-color: #8b5cf6; }
.form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px; }
.btn-cancel { padding: 9px 22px; border-radius: 7px; border: 1px solid var(--color-border); background: white; font-size: 13px; font-weight: 500; cursor: pointer; color: var(--color-text); font-family: var(--font); }
.btn-cancel:hover { background: var(--color-bg); }
.btn-submit { padding: 9px 22px; border-radius: 7px; border: none; background: var(--color-primary); color: white; font-size: 13px; font-weight: 600; cursor: pointer; font-family: var(--font); }
.btn-submit:hover { background: var(--color-primary-light); }
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
@endsection

@section('content')
<main class="main">
    <div class="page-header">
        <h1>Create New Request</h1>
        <p>Submit a new social media post request</p>
    </div>

    @if(session('success'))
        <div class="alert alert--success">
            {{ session('success') }} <a href="{{ route('requestor.requests') }}" style="color:inherit;font-weight:700;">View your requests →</a>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert--error">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('requestor.requests.store') }}" enctype="multipart/form-data" id="create-form">
        @csrf

        <!-- BASIC INFO -->
        <div class="form-section">
            <div class="section-title">Basic Information</div>
            <div class="field">
                <label>Event/Post Title <span>*</span></label>
                <input type="text" name="title" placeholder="e.g., College Week 2025 Opening Ceremony"
                       value="{{ old('title') }}" required>
            </div>
            <div class="field">
                <label>Description <span>*</span></label>
                <textarea name="description" rows="4"
                          placeholder="Provide detailed information about your event or announcement..."
                          required>{{ old('description') }}</textarea>
            </div>
            <div class="field-row">
                <div class="field">
                    <label>Category <span>*</span></label>
                    <select name="category" required>
                        <option value="" disabled {{ !old('category') ? 'selected' : '' }}>Select category</option>
                        @foreach(['Events','Announcements','Academic','Sports','Community','Others'] as $cat)
                            <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field">
                    <label>Priority <span>*</span></label>
                    <select name="priority" required>
                        <option value="" disabled {{ !old('priority') ? 'selected' : '' }}>Select priority</option>
                        @foreach(['Low','Medium','High','Urgent'] as $pri)
                            <option value="{{ $pri }}" {{ old('priority') === $pri ? 'selected' : '' }}>{{ $pri }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="field">
                <label>Preferred Post Date (Optional)</label>
                <input type="date" name="post_date" value="{{ old('post_date') }}">
            </div>
        </div>

        <!-- PLATFORMS -->
        <div class="form-section">
            <div class="section-title">Target Platforms <span style="color:#ef4444;font-size:13px;font-weight:400;">*</span></div>
            <div class="platform-group" id="platform-group">
                @foreach(['Facebook','Youtube','Tiktok','LinkedIn'] as $p)
                    <button type="button" class="platform-btn {{ in_array($p, old('platforms', [])) ? 'selected' : '' }}"
                            data-platform="{{ $p }}">{{ $p }}</button>
                @endforeach
            </div>
            <div id="platform-inputs"></div>
        </div>

        <!-- MEDIA -->
        <div class="form-section">
            <div class="section-title">Media Attachments</div>
            <div class="upload-area" id="upload-area">
                <label class="upload-box" for="media-input" id="upload-label">
                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/>
                        <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                    </svg>
                    Upload
                </label>
            </div>
            <input type="file" id="media-input" name="media[]" multiple accept="image/*,video/mp4,video/quicktime">
            <p class="upload-hint" style="margin-top:8px;">Upload up to 4 images or videos (Max 10MB each)</p>
        </div>

        <!-- AI CAPTION -->
        <div class="ai-section">
            <div class="ai-header">
                <div class="ai-title">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/>
                    </svg>
                    AI Caption Generator
                </div>
                <button type="button" class="ai-generate-btn" id="generate-btn" onclick="generateCaption()">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                    </svg>
                    Generate
                </button>
            </div>
            <div style="font-size:12px;color:#6b7280;margin-bottom:6px;">Caption (Editable)</div>
            <textarea class="ai-textarea" name="caption" id="caption-field"
                      placeholder="Write or generate an AI-powered caption for your post...">{{ old('caption') }}</textarea>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-cancel" onclick="history.back()">Cancel</button>
            <button type="submit" class="btn-submit">Submit Request</button>
        </div>
    </form>
</main>
@endsection

@section('scripts')
<script>
// Platform buttons
const platformBtns    = document.querySelectorAll('.platform-btn');
const platformInputs  = document.getElementById('platform-inputs');
function updatePlatformInputs() {
    platformInputs.innerHTML = '';
    document.querySelectorAll('.platform-btn.selected').forEach(btn => {
        const inp   = document.createElement('input');
        inp.type    = 'hidden';
        inp.name    = 'platforms[]';
        inp.value   = btn.dataset.platform;
        platformInputs.appendChild(inp);
    });
}
platformBtns.forEach(btn => { btn.addEventListener('click', () => { btn.classList.toggle('selected'); updatePlatformInputs(); }); });
updatePlatformInputs();

// Media upload preview
const mediaInput  = document.getElementById('media-input');
const uploadArea  = document.getElementById('upload-area');
const uploadLabel = document.getElementById('upload-label');
mediaInput.addEventListener('change', () => {
    document.querySelectorAll('.preview-wrap').forEach(el => el.remove());
    const files = Array.from(mediaInput.files).slice(0, 4);
    files.forEach(file => {
        if (!file.type.startsWith('image/')) return;
        const reader = new FileReader();
        reader.onload = e => {
            const wrap      = document.createElement('div');
            wrap.className  = 'preview-wrap';
            const img       = document.createElement('img');
            img.src         = e.target.result;
            img.className   = 'preview-thumb';
            const removeBtn = document.createElement('button');
            removeBtn.type      = 'button';
            removeBtn.className = 'preview-remove';
            removeBtn.innerHTML = '×';
            removeBtn.onclick   = () => wrap.remove();
            wrap.appendChild(img);
            wrap.appendChild(removeBtn);
            uploadArea.insertBefore(wrap, uploadLabel);
        };
        reader.readAsDataURL(file);
    });
    uploadLabel.style.display = files.length >= 4 ? 'none' : 'flex';
});

async function generateCaption() {
    // 1. Kunin ang elements gamit ang querySelector
    const titleField = document.querySelector('input[name="title"]');
    const descField  = document.querySelector('textarea[name="description"]');
    const catField   = document.querySelector('select[name="category"]');
    const btn        = document.getElementById('generate-btn');
    const captionBox = document.getElementById('caption-field');

    // 2. I-extract ang values
    const title = titleField ? titleField.value.trim() : "";
    const desc  = descField ? descField.value.trim() : "";
    const cat   = catField ? catField.value : "General";
    
    // Kunin ang napiling platforms mula sa buttons
    const plats = Array.from(document.querySelectorAll('.platform-btn.selected'))
                       .map(b => b.dataset.platform).join(', ');

    // 3. Validation: Hindi pwedeng blanko ang title at description
    if (!title || !desc) {
        alert("Please fill in the Title and Description first before generating a caption.");
        return;
    }

    // 4. UI Feedback: I-disable ang button habang naghihintay sa AI
    const originalBtnText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="animation:spin 1s linear infinite; display:inline-block; margin-right:5px;"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg> Generating...`;

    try {
        // 5. Fetch request papunta sa Laravel route
        const response = await fetch('/api/generate-caption', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                // Importante: Ito ang kailangan ng Laravel para sa security
                'X-CSRF-TOKEN': '{{ csrf_token() }}' 
            },
            body: JSON.stringify({ 
                title: title, 
                description: desc, 
                category: cat, 
                platforms: plats 
            })
        });

        const data = await response.json();
        
        if (data.error) {
            throw new Error(data.error);
        }

        // 6. I-display ang caption sa textarea
        if (data.caption && captionBox) {
            captionBox.value = data.caption;
        } else {
            throw new Error("No caption returned from AI.");
        }

    } catch (e) {
        console.error("AI Generation Error:", e);
        alert("Caption generation failed: " + e.message);
    } finally {
        // 7. I-reset ang button state
        btn.disabled = false;
        btn.innerHTML = originalBtnText;
    }
}
</script>
@endsection