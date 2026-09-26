@extends('layouts.admin')

@section('title', 'Request Details – ' . ($req->request_id ?? 'REQ-' . $req->id))

@section('head-styles')
<style>
.req-info-container { max-width: 1200px; margin: 0 auto; padding: 28px 20px; }
.back-btn { display: inline-flex; align-items: center; gap: 6px; color: var(--color-primary); text-decoration: none; font-size: 13px; font-weight: 600; margin-bottom: 20px; transition: opacity .15s; }
.back-btn:hover { opacity: 0.8; }
.page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 14px; }
.page-header h1 { font-size: 22px; font-weight: 700; color: var(--color-text); display: flex; align-items: center; gap: 10px; }
.grid-layout { display: grid; grid-template-columns: 1fr 380px; gap: 24px; }
@media (max-width: 992px) {
    .grid-layout { grid-template-columns: 1fr; }
}
.card { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); padding: 22px; margin-bottom: 22px; border: 1px solid var(--color-border); }
.card__title { font-size: 15px; font-weight: 700; color: var(--color-primary); margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; }
.meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 18px; }
.meta-item { display: flex; flex-direction: column; gap: 4px; }
.meta-label { font-size: 11.5px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px; }
.meta-value { font-size: 13.5px; font-weight: 500; color: var(--color-text); }
.badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; width: fit-content; }
.badge--urgent       { background: #fef3c7; color: #b45309; }
.badge--high         { background: #fee2e2; color: #dc2626; }
.badge--medium       { background: #fef3c7; color: #d97706; }
.badge--low          { background: #f3f4f6; color: #6b7280; }
.badge--approved     { background: #dcfce7; color: #16a34a; }
.badge--posted       { background: #dbeafe; color: #2563eb; }
.badge--under-review { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
.badge--pending      { background: #f3f4f6; color: #6b7280; border: 1px solid #e5e7eb; }
.badge--rejected     { background: #fee2e2; color: #dc2626; }
.platform-chip { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; background: #f0f4ff; color: #1e40af; border-radius: 6px; font-size: 12px; font-weight: 600; margin-right: 6px; margin-bottom: 6px; }
.media-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 12px; margin-top: 12px; }
.media-item { position: relative; border-radius: 8px; overflow: hidden; border: 1px solid var(--color-border); background: #fafafa; aspect-ratio: 1; display: flex; align-items: center; justify-content: center; }
.media-item img, .media-item video { width: 100%; height: 100%; object-fit: cover; }
.media-item a { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.3); opacity: 0; color: white; text-decoration: none; font-size: 12px; font-weight: 600; transition: opacity .15s; }
.media-item:hover a { opacity: 1; }
.caption-textarea { width: 100%; min-height: 120px; border: 1px solid var(--color-border); border-radius: 8px; padding: 12px; font-family: var(--font); font-size: 13.5px; line-height: 1.5; outline: none; transition: border-color .15s; resize: vertical; }
.caption-textarea:focus { border-color: var(--color-primary); box-shadow: 0 0 0 3px rgba(0,35,102,0.08); }
.btn { height: 38px; padding: 0 16px; border-radius: 7px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 6px; transition: all .15s; }
.btn-primary { background: var(--color-primary); color: white; }
.btn-primary:hover { background: var(--color-primary-light); }
.status-actions-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 14px; }
.status-btn { padding: 9px 12px; border-radius: 8px; border: 1.5px solid transparent; font-size: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all .15s; }
.status-btn--review   { background: #fef3c7; color: #b45309; border-color: #fde68a; }
.status-btn--approved { background: #dcfce7; color: #15803d; border-color: #bbf7d0; }
.status-btn--posted   { background: #ede9fe; color: #6d28d9; border-color: #ddd6fe; }
.status-btn--rejected { background: #fee2e2; color: #b91c1c; border-color: #fecaca; }
.status-btn:hover { filter: brightness(0.95); transform: translateY(-1px); }
.status-btn--active { ring: 2px; box-shadow: 0 2px 6px rgba(0,0,0,0.12); }
.timeline { display: flex; flex-direction: column; gap: 14px; margin-top: 10px; }
.timeline-item { display: flex; gap: 12px; font-size: 12.5px; position: relative; }
.timeline-dot { width: 10px; height: 10px; border-radius: 50%; background: var(--color-primary); margin-top: 4px; flex-shrink: 0; }
.timeline-content { flex: 1; }
.timeline-title { font-weight: 600; color: var(--color-text); }
.timeline-time { font-size: 11px; color: var(--color-text-muted); margin-top: 2px; }
.comment-box { display: flex; flex-direction: column; gap: 10px; max-height: 280px; overflow-y: auto; padding: 8px; background: #f9fafb; border-radius: 8px; margin-bottom: 12px; }
.comment-bubble { padding: 8px 12px; border-radius: 10px; font-size: 12.5px; max-width: 85%; }
.comment-bubble--admin { background: var(--color-primary); color: white; align-self: flex-end; }
.comment-bubble--requester { background: white; border: 1px solid var(--color-border); color: var(--color-text); align-self: flex-start; }
.comment-author { font-size: 10.5px; font-weight: 700; margin-bottom: 2px; opacity: 0.85; }
.alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; font-weight: 500; }
.alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.alert-error   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
</style>
@endsection

@section('content')
<div class="req-info-container">
    <a href="{{ route('admin.requests') }}" class="back-btn">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
        Back to Requests
    </a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @php
        $status_raw = strtolower($req->status ?? 'pending review');
        $status_class = match(true) {
            str_contains($status_raw, 'approved')     => 'approved',
            str_contains($status_raw, 'posted')       => 'posted',
            str_contains($status_raw, 'under review') => 'under-review',
            str_contains($status_raw, 'rejected')     => 'rejected',
            default                                   => 'pending',
        };
        $priority_class = match(strtolower($req->priority ?? 'low')) {
            'urgent' => 'urgent', 'high' => 'high', 'medium' => 'medium', default => 'low'
        };
        $reqCode = $req->request_id ?: ('REQ-' . str_pad($req->id, 5, '0', STR_PAD_LEFT));
        $mediaList = array_values(array_filter(array_map('trim', explode(',', $req->media_file ?? ''))));
        $platformsList = array_values(array_filter(array_map('trim', explode(',', $req->platform ?? ''))));
    @endphp

    <div class="page-header">
        <div>
            <h1>
                <span>{{ $req->title }}</span>
                <span class="badge badge--{{ $status_class }}">{{ $req->status }}</span>
            </h1>
            <div style="font-size: 13px; color: var(--color-text-muted); margin-top: 4px;">
                Tracking ID: <strong>{{ $reqCode }}</strong> &middot; Submitted on {{ $req->created_at->format('M j, Y \a\t g:i A') }}
            </div>
        </div>
    </div>

    <div class="grid-layout">
        <!-- LEFT COLUMN: Details, Media, Caption -->
        <div>
            <!-- Request Overview -->
            <div class="card">
                <div class="card__title">
                    <span>Request Details</span>
                    <span class="badge badge--{{ $priority_class }}">{{ strtoupper($req->priority ?? 'LOW') }} PRIORITY</span>
                </div>
                <div class="meta-grid">
                    <div class="meta-item">
                        <span class="meta-label">Requester</span>
                        <span class="meta-value">{{ $req->requester ?: 'N/A' }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Category</span>
                        <span class="meta-value">{{ $req->category ?: 'General' }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Preferred Date</span>
                        <span class="meta-value">{{ $req->preferred_date ? $req->preferred_date->format('M j, Y') : 'Flexible' }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Target Platforms</span>
                        <div style="margin-top: 4px;">
                            @forelse($platformsList as $p)
                                <span class="platform-chip">{{ $p }}</span>
                            @empty
                                <span class="meta-value">Facebook</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="meta-item" style="margin-top: 14px;">
                    <span class="meta-label">Description</span>
                    <div style="font-size: 13.5px; line-height: 1.6; color: var(--color-text); margin-top: 6px; white-space: pre-wrap; background: #f9fafb; padding: 14px; border-radius: 8px; border: 1px solid var(--color-border);">{{ $req->description ?: 'No description provided.' }}</div>
                </div>
            </div>

            <!-- Uploaded Media -->
            <div class="card">
                <div class="card__title">
                    <span>Attached Media ({{ count($mediaList) }})</span>
                </div>
                @if(count($mediaList) > 0)
                    <div class="media-gallery">
                        @foreach($mediaList as $media)
                            @php
                                $ext = strtolower(pathinfo($media, PATHINFO_EXTENSION));
                                $isVideo = in_array($ext, ['mp4', 'mov', 'webm']);
                            @endphp
                            <div class="media-item">
                                @if($isVideo)
                                    <video src="/uploads/{{ $media }}" controls style="width:100%;height:100%;"></video>
                                @else
                                    <img src="/uploads/{{ $media }}" alt="Media" onerror="this.src='/assets/nubg1.png'">
                                    <a href="/uploads/{{ $media }}" target="_blank">View Full</a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="color: var(--color-text-muted); font-size: 13px; font-style: italic;">
                        No media attachments uploaded with this request.
                    </div>
                @endif
            </div>

            <!-- Caption Draft Editor -->
            <div class="card">
                <div class="card__title">
                    <span>Caption Draft</span>
                    <button type="button" class="btn" style="background: #f0f4ff; color: var(--color-primary); height: 32px; font-size: 12px;" onclick="copyCaption()">
                        Copy Caption
                    </button>
                </div>
                <form method="POST" action="{{ route('admin.requests.caption', $req->id) }}">
                    @csrf
                    <textarea class="caption-textarea" id="caption-field" name="caption" placeholder="Enter social media caption draft here...">{{ $req->caption }}</textarea>
                    <div style="display:flex; justify-content: flex-end; margin-top: 12px;">
                        <button type="submit" class="btn btn-primary">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            Save Caption Draft
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- RIGHT COLUMN: Workflow Status Actions, Comments, Activity -->
        <div>
            <!-- Status Update Action Card -->
            <div class="card">
                <div class="card__title">
                    <span>Status Workflow</span>
                </div>
                <div style="font-size: 12.5px; color: var(--color-text-muted); margin-bottom: 12px;">
                    Update the request stage. Status updates notify the requester automatically.
                </div>

                <form method="POST" action="{{ route('admin.requests.status') }}" id="status-form">
                    @csrf
                    <input type="hidden" name="request_id" value="{{ $req->id }}">
                    <input type="hidden" name="status" id="target-status-input" value="{{ $req->status }}">

                    <div style="margin-bottom: 12px;">
                        <label class="meta-label" style="display:block; margin-bottom: 6px;">Internal Admin Note (Optional):</label>
                        <input type="text" name="note" class="caption-textarea" style="min-height: 40px; height: 40px; padding: 8px 12px;" placeholder="e.g. Graphics approved, queue for Saturday">
                    </div>

                    <div class="status-actions-grid">
                        <button type="button" class="status-btn status-btn--review {{ $req->status === 'Under Review' ? 'status-btn--active' : '' }}" onclick="submitStatus('Under Review')">
                            Reviewing
                        </button>
                        <button type="button" class="status-btn status-btn--approved {{ $req->status === 'Approved' ? 'status-btn--active' : '' }}" onclick="submitStatus('Approved')">
                            Approve
                        </button>
                        <button type="button" class="status-btn status-btn--posted {{ $req->status === 'Posted' ? 'status-btn--active' : '' }}" onclick="submitStatus('Posted')">
                            Mark Posted
                        </button>
                        <button type="button" class="status-btn status-btn--rejected {{ $req->status === 'Rejected' ? 'status-btn--active' : '' }}" onclick="submitStatus('Rejected')">
                            Reject
                        </button>
                    </div>
                </form>
            </div>

            <!-- Requester Comments & Chat -->
            <div class="card">
                <div class="card__title">
                    <span>Discussion & Notes</span>
                </div>
                <div class="comment-box" id="comments-container">
                    @forelse($comments as $c)
                        <div class="comment-bubble {{ $c->sender_role === 'admin' ? 'comment-bubble--admin' : 'comment-bubble--requester' }}">
                            <div class="comment-author">{{ $c->sender_name }} ({{ ucfirst($c->sender_role) }})</div>
                            <div>{{ $c->message }}</div>
                            <div style="font-size: 9.5px; opacity: 0.7; margin-top: 3px; text-align: right;">{{ $c->created_at->format('M j, g:i A') }}</div>
                        </div>
                    @empty
                        <div style="text-align: center; color: var(--color-text-muted); font-size: 12px; padding: 20px;">
                            No messages on this request yet.
                        </div>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('admin.requests.comment') }}" onsubmit="sendCommentInline(event)">
                    @csrf
                    <input type="hidden" name="request_id" value="{{ $req->id }}">
                    <div style="display:flex; gap: 8px;">
                        <input type="text" id="inline-comment-text" name="message" class="caption-textarea" style="min-height: 38px; height: 38px; padding: 8px 12px;" placeholder="Send reply to requester..." required>
                        <button type="submit" class="btn btn-primary" style="height: 38px; padding: 0 12px;">Send</button>
                    </div>
                </form>
            </div>

            <!-- Activity Log Timeline -->
            <div class="card">
                <div class="card__title">
                    <span>Activity History</span>
                </div>
                <div class="timeline">
                    @forelse($activities as $act)
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <div class="timeline-title">{{ $act->action }}</div>
                                <div class="timeline-time">{{ $act->actor }} &middot; {{ $act->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @empty
                        <div style="color: var(--color-text-muted); font-size: 12px;">No activity logged yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function submitStatus(newStatus) {
    if (confirm(`Change request status to "${newStatus}"?`)) {
        document.getElementById('target-status-input').value = newStatus;
        document.getElementById('status-form').submit();
    }
}

function copyCaption() {
    const text = document.getElementById('caption-field').value;
    if (!text) {
        alert('Caption draft is currently empty.');
        return;
    }
    navigator.clipboard.writeText(text).then(() => {
        alert('Caption copied to clipboard!');
    }).catch(() => {
        alert('Failed to copy caption.');
    });
}

async function sendCommentInline(e) {
    e.preventDefault();
    const input = document.getElementById('inline-comment-text');
    const msg = input.value.trim();
    if (!msg) return;

    try {
        const res = await fetch("{{ route('admin.requests.comment') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ request_id: {{ $req->id }}, message: msg })
        });
        const data = await res.json();
        if (data.success) {
            input.value = '';
            location.reload();
        } else {
            alert(data.message || 'Failed to post message.');
        }
    } catch(err) {
        alert('Connection error.');
    }
}
</script>
@endsection
