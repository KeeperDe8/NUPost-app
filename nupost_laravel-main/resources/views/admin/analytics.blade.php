@extends('layouts.admin')
@section('title', 'Request Analytics')

@section('head-styles')
<style>
.page-hd { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; }
.page-hd__title { font-family:var(--font-disp); font-size:22px; color:var(--ink); }
.page-hd__sub   { font-size:13px; color:var(--ink-soft); margin-top:3px; }

/* REQUEST ANALYTICS SEARCH BAR */
.vis-search-wrap {
    margin: 0 0 24px 0;
    display: flex;
    align-items: center;
    gap: 12px;
    background: var(--card);
    border: 1.5px solid var(--card-border);
    border-radius: var(--radius);
    padding: 10px 18px;
    box-shadow: var(--shadow-sm);
    transition: all .2s cubic-bezier(0.16, 1, 0.3, 1);
}
.vis-search-wrap:focus-within {
    border-color: var(--navy);
    box-shadow: 0 0 0 3.5px rgba(0, 35, 102, 0.1);
    transform: translateY(-1px);
}
.vis-search-icon {
    color: var(--ink-faint);
    display: flex;
    align-items: center;
    flex-shrink: 0;
    transition: color .15s;
}
.vis-search-wrap:focus-within .vis-search-icon {
    color: var(--navy);
}
.vis-search-input {
    flex: 1;
    border: none;
    background: transparent;
    font-size: 13.5px;
    font-family: var(--font);
    color: var(--ink);
    outline: none;
    padding: 0 4px;
}
.vis-search-input::placeholder {
    color: var(--ink-faint);
}
.vis-search-clear {
    background: none;
    border: none;
    color: var(--ink-faint);
    cursor: pointer;
    font-size: 13px;
    padding: 4px 8px;
    border-radius: 6px;
    display: none;
    align-items: center;
    justify-content: center;
    transition: all .15s;
}
.vis-search-clear:hover {
    background: var(--cream-dark);
    color: var(--ink);
}
.vis-search-clear.visible {
    display: flex;
}
.vis-search-pill {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--navy);
    background: var(--navy-pale);
    border: 1px solid rgba(0, 35, 102, 0.12);
    padding: 4px 11px;
    border-radius: 20px;
    display: none;
    align-items: center;
    gap: 5px;
    flex-shrink: 0;
}

/* STAT CARDS */
.stats-row { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:22px; }
@media (max-width: 900px) {
    .stats-row { grid-template-columns:repeat(2,1fr); }
}
.scard {
    background:linear-gradient(135deg,#001a4d 0%,#002366 60%,#002e7a 100%);
    border-radius:var(--radius); padding:20px 18px;
    box-shadow:0 4px 18px rgba(0,26,77,0.22);
    transition:transform .2s,box-shadow .2s;
    position:relative; overflow:hidden;
}
.scard::before {
    content:''; position:absolute; top:-30px; right:-30px;
    width:110px; height:110px; border-radius:50%;
    background:radial-gradient(circle,rgba(255,255,255,0.07) 0%,transparent 70%);
    pointer-events:none;
}
.scard:hover { transform:translateY(-3px); box-shadow:0 8px 28px rgba(0,26,77,0.3); }
.scard__top { display:flex; align-items:center; justify-content:space-between; margin-bottom:14px; }
.scard__icon { width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; }
.scard__trend { font-size:11px; font-weight:700; padding:3px 9px; border-radius:20px; background:rgba(255,255,255,0.12); color:rgba(255,255,255,0.85); }
.scard__num { font-size:28px; font-weight:800; letter-spacing:-1px; line-height:1; margin-bottom:5px; color:white; }
.scard__lbl { font-size:12px; color:rgba(255,255,255,0.6); font-weight:500; }

/* PANELS */
.panel-a { background:var(--card); border:1.5px solid var(--card-border); border-radius:var(--radius); box-shadow:var(--shadow-sm); overflow:hidden; margin-bottom:18px; }
.pa-head { padding:18px 22px 14px; display:flex; align-items:flex-start; justify-content:space-between; border-bottom:1.5px solid var(--card-border); }
.pa-title { font-family:var(--font-disp); font-size:17px; color:var(--ink); }
.pa-sub   { font-size:11.5px; color:var(--ink-soft); margin-top:2px; }
.pa-body  { padding:22px; }

.two-col { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px; }
@media (max-width: 800px) {
    .two-col { grid-template-columns:1fr; }
}

/* PLATFORM BARS */
.platform-list { display:flex; flex-direction:column; gap:13px; }
.platform-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:5px; }
.platform-left { display:flex; align-items:center; gap:9px; font-size:13px; font-weight:600; color:var(--ink); }
.platform-icon { width:28px; height:28px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; }
.platform-val { font-size:13px; font-weight:700; color:var(--ink); }
.pbar-bg { height:8px; background:var(--cream-dark); border-radius:4px; overflow:hidden; }
.pbar-fill { height:100%; border-radius:4px; }

/* CATEGORY PILLS */
.cat-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.cat-pill { background:var(--cream-dark); border-radius:12px; padding:12px 14px; display:flex; align-items:center; gap:10px; transition:transform .15s; }
.cat-pill:hover { transform:scale(1.02); }
.cat-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }

/* TOP REQUESTORS */
.trop-list { display:flex; flex-direction:column; gap:12px; }
.trop-item { display:flex; align-items:center; gap:11px; }
.trop-av { width:36px; height:36px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; color:white; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1); }
.trop-av img { width:100%; height:100%; object-fit:cover; }
.trop-name { font-size:13px; font-weight:600; color:var(--ink); }
.trop-dept { font-size:10.5px; color:var(--ink-soft); margin-top:1px; }
.trop-bar-bg { height:6px; background:var(--cream-dark); border-radius:3px; overflow:hidden; }
.trop-bar-fill { height:100%; border-radius:3px; }
.trop-count { font-size:13px; font-weight:700; color:var(--navy); width:24px; text-align:right; flex-shrink:0; }

/* STATUS SUMMARY */
.status-list { display:flex; flex-direction:column; gap:9px; }

/* RECENT TABLE */
.rtable { width:100%; border-collapse:collapse; }
.rtable th { padding:9px 16px; text-align:left; font-size:10px; font-weight:700; color:var(--ink-soft); text-transform:uppercase; letter-spacing:0.7px; border-bottom:1.5px solid var(--card-border); background:var(--cream-dark); }
.rtable td { padding:12px 16px; font-size:12.5px; border-bottom:1px solid var(--cream-dark); vertical-align:middle; }
.rtable tbody tr:last-child td { border-bottom:none; }
.rtable tbody tr:hover { background:var(--cream); cursor:pointer; }
</style>
@endsection

@section('content')
@php
    $total    = \App\Models\PostRequest::count();
    $posted   = \App\Models\PostRequest::where('status','Posted')->count();
    $approved = \App\Models\PostRequest::where('status','Approved')->count();
    $pending  = \App\Models\PostRequest::where('status','Pending Review')->count();
    $rejected = \App\Models\PostRequest::where('status','Rejected')->count();
    $review   = \App\Models\PostRequest::where('status','Under Review')->count();
    $users    = \App\Models\User::count();
    $approval_rate = $total > 0 ? round(($approved + $posted) / $total * 100) : 0;
    $safe = max($total, 1);

    $platforms_raw   = \App\Models\PostRequest::all();
    $platform_counts = [];
    foreach ($platforms_raw as $req) {
        foreach ($req->platforms_array ?? [] as $p) {
            $platform_counts[$p] = ($platform_counts[$p] ?? 0) + 1;
        }
    }
    arsort($platform_counts);
    $plat_max    = max($platform_counts ?: [1]);
    $plat_colors = [
        'Facebook'  => ['#1877f2','📘'],
        'Instagram' => ['#e1306c','📸'],
        'Youtube'   => ['#ff0000','📺'],
        'TikTok'    => ['#010101','🎵'],
        'Twitter'   => ['#1da1f2','🐦'],
        'LinkedIn'  => ['#0077b5','💼'],
    ];

    $categories = \App\Models\PostRequest::selectRaw('category,count(*) as count')->groupBy('category')->orderByDesc('count')->get();
    $cat_max    = $categories->max('count') ?: 1;
    $cat_colors = ['#2563eb','#10b981','#f59e0b','#8b5cf6','#ef4444','#0ea5e9','#f97316'];

    $top_requestors = \App\Models\PostRequest::selectRaw('requester,count(*) as total')->groupBy('requester')->orderByDesc('total')->limit(5)->get();
    $top_users      = \App\Models\User::whereIn('name', $top_requestors->pluck('requester'))->get()->keyBy('name');
    $tr_max         = $top_requestors->max('total') ?: 1;
    $av_colors      = ['#2563eb','#10b981','#8b5cf6','#f59e0b','#ef4444'];

    $recent_posted  = \App\Models\PostRequest::whereIn('status',['Posted','Approved'])->orderByDesc('created_at')->limit(8)->get();
@endphp

<div class="page-hd">
    <div>
        <div class="page-hd__title">Request Analytics</div>
        <div class="page-hd__sub">Platform breakdown, category distribution & requestor insights</div>
    </div>
</div>

@if(session('success'))
    <div style="background:#dcfce7;border:1px solid #bbf7d0;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:500;color:#15803d;margin-bottom:16px;">{{ session('success') }}</div>
@endif

{{-- LIVE SEARCH BAR WITH PROPER SPACING --}}
<div class="vis-search-wrap" id="vis-search-wrap">
    <span class="vis-search-icon">
        <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
    </span>
    <input type="text"
           id="vis-search-input"
           class="vis-search-input"
           placeholder="Search request analytics data, published requests, categories, platforms..."
           autocomplete="off">
    <span class="vis-search-pill" id="vis-search-pill">
        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        <span id="vis-search-pill-text">0 matches</span>
    </span>
    <button type="button" class="vis-search-clear" id="vis-search-clear" onclick="clearVisSearch()" title="Clear search">✕</button>
</div>

{{-- STAT CARDS --}}
<div class="stats-row">
    <div class="scard" style="border-bottom:3px solid #60a5fa;">
        <div class="scard__top">
            <div class="scard__icon" style="background:rgba(255,255,255,0.12);">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <span class="scard__trend">All</span>
        </div>
        <div class="scard__num">{{ $total }}</div>
        <div class="scard__lbl">Total Requests</div>
    </div>
    <div class="scard" style="border-bottom:3px solid #c4b5fd;">
        <div class="scard__top">
            <div class="scard__icon" style="background:rgba(139,92,246,0.25);">
                <svg width="18" height="18" fill="none" stroke="#c4b5fd" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </div>
            <span class="scard__trend">Live</span>
        </div>
        <div class="scard__num">{{ $posted }}</div>
        <div class="scard__lbl">Published Posts</div>
    </div>
    <div class="scard" style="border-bottom:3px solid #6ee7b7;">
        <div class="scard__top">
            <div class="scard__icon" style="background:rgba(16,185,129,0.25);">
                <svg width="18" height="18" fill="none" stroke="#6ee7b7" stroke-width="2" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            </div>
            <span class="scard__trend">{{ $approval_rate }}%</span>
        </div>
        <div class="scard__num">{{ $approval_rate }}%</div>
        <div class="scard__lbl">Approval Rate</div>
    </div>
    <div class="scard" style="border-bottom:3px solid #fbbf24;">
        <div class="scard__top">
            <div class="scard__icon" style="background:rgba(245,158,11,0.25);">
                <svg width="18" height="18" fill="none" stroke="#fbbf24" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
            <span class="scard__trend">Active</span>
        </div>
        <div class="scard__num">{{ $users }}</div>
        <div class="scard__lbl">Requestors</div>
    </div>
</div>

{{-- PLATFORM + CATEGORIES --}}
<div class="two-col">
    <div class="panel-a" style="margin-bottom:0;">
        <div class="pa-head"><div class="pa-title">Platform Breakdown</div></div>
        <div class="pa-body">
            @if(empty($platform_counts))
                <div style="text-align:center;color:var(--ink-faint);padding:20px;font-size:13px;">No platform data yet.</div>
            @else
            <div class="platform-list">
                @foreach($platform_counts as $plat => $cnt)
                @php $pct=round($cnt/$plat_max*100); $col=$plat_colors[$plat][0]??'#6b7280'; $ico=$plat_colors[$plat][1]??'🌐'; @endphp
                <div>
                    <div class="platform-top">
                        <div class="platform-left">
                            <span class="platform-icon" style="background:{{ $col }}22;">{{ $ico }}</span>
                            {{ $plat }}
                        </div>
                        <span class="platform-val">{{ $cnt }}</span>
                    </div>
                    <div class="pbar-bg"><div class="pbar-fill" style="width:{{ $pct }}%;background:{{ $col }};"></div></div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
    <div class="panel-a" style="margin-bottom:0;">
        <div class="pa-head"><div class="pa-title">Category Distribution</div></div>
        <div class="pa-body">
            @if($categories->isEmpty())
                <div style="text-align:center;color:var(--ink-faint);padding:20px;font-size:13px;">No data yet.</div>
            @else
            <div class="cat-grid">
                @foreach($categories as $i => $cat)
                @php $col = $cat_colors[$i % count($cat_colors)]; @endphp
                <div class="cat-pill">
                    <span class="cat-dot" style="background:{{ $col }};"></span>
                    <span style="font-size:12.5px;font-weight:600;color:var(--ink);flex:1;">{{ Str::limit($cat->category ?? 'Other', 12) }}</span>
                    <span style="font-size:13px;font-weight:700;color:var(--ink);">{{ $cat->count }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

{{-- TOP REQUESTORS + STATUS --}}
<div class="two-col">
    <div class="panel-a" style="margin-bottom:0;">
        <div class="pa-head">
            <div><div class="pa-title">Top Requestors</div><div class="pa-sub">By total submissions</div></div>
        </div>
        <div class="pa-body">
            <div class="trop-list">
                @forelse($top_requestors as $i => $tr)
                @php
                    $medals = ['🥇','🥈','🥉'];
                    $uobj   = $top_users[$tr->requester] ?? null;
                    $photo  = $uobj?->profile_photo;
                    $dept   = $uobj?->department ?? ($uobj?->organization ?? 'Requestor');
                    $pct    = round($tr->total / $tr_max * 100);
                    $col    = $av_colors[$i % 5];
                @endphp
                <div class="trop-item">
                    <div style="width:24px;text-align:center;font-size:15px;">{{ $medals[$i] ?? ($i+1).'.' }}</div>
                    <div class="trop-av" style="background:{{ $col }};">
                        @if($photo)<img src="/uploads/{{ $photo }}" alt="">@endif
                        {{ strtoupper(substr($tr->requester, 0, 1)) }}
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div class="trop-name">{{ Str::limit($tr->requester, 18) }}</div>
                        <div class="trop-dept">{{ Str::limit($dept, 20) }}</div>
                    </div>
                    <div style="flex:1;">
                        <div class="trop-bar-bg"><div class="trop-bar-fill" style="width:{{ $pct }}%;background:{{ $col }};"></div></div>
                    </div>
                    <div class="trop-count">{{ $tr->total }}</div>
                </div>
                @empty
                    <div style="text-align:center;color:var(--ink-faint);font-size:13px;padding:20px;">No data yet.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="panel-a" style="margin-bottom:0;">
        <div class="pa-head"><div><div class="pa-title">Status Summary</div></div></div>
        <div class="pa-body">
            @php
                $st_items = [
                    ['l'=>'Pending Review','v'=>$pending, 'pct'=>round($pending/$safe*100), 'c'=>'#f59e0b'],
                    ['l'=>'Under Review',  'v'=>$review,  'pct'=>round($review/$safe*100),  'c'=>'#1e4fd8'],
                    ['l'=>'Approved',      'v'=>$approved,'pct'=>round($approved/$safe*100),'c'=>'#10b981'],
                    ['l'=>'Posted',        'v'=>$posted,  'pct'=>round($posted/$safe*100),  'c'=>'#8b5cf6'],
                    ['l'=>'Rejected',      'v'=>$rejected,'pct'=>round($rejected/$safe*100),'c'=>'#ef4444'],
                ];
            @endphp
            <div class="status-list">
                @foreach($st_items as $si)
                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                        <div style="display:flex;align-items:center;gap:7px;font-size:12.5px;color:var(--ink-mid);">
                            <span style="width:8px;height:8px;border-radius:2px;background:{{ $si['c'] }};display:inline-block;"></span>
                            {{ $si['l'] }}
                        </div>
                        <span style="font-size:12.5px;font-weight:700;color:var(--ink);">{{ $si['v'] }} <span style="font-weight:400;color:var(--ink-faint);">({{ $si['pct'] }}%)</span></span>
                    </div>
                    <div style="height:6px;background:var(--cream-dark);border-radius:3px;overflow:hidden;">
                        <div style="width:{{ $si['pct'] }}%;height:100%;background:{{ $si['c'] }};border-radius:3px;"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- RECENT POSTED --}}
<div class="panel-a">
    <div class="pa-head">
        <div><div class="pa-title">Recent Published Requests</div><div class="pa-sub">Latest approved & posted</div></div>
    </div>
    <div style="overflow-x:auto;">
        <table class="rtable" id="vis-recent-table">
            <thead>
                <tr><th>Request</th><th>Requestor</th><th>Category</th><th>Platforms</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody>
            @forelse($recent_posted as $req)
            @php
                $sl = strtolower($req->status);
                $sc = match(true) {
                    str_contains($sl,'approved')     => 'approved',
                    str_contains($sl,'posted')       => 'posted',
                    str_contains($sl,'under review') => 'under-review',
                    default                          => 'pending',
                };
            @endphp
            <tr onclick="window.location='{{ route('admin.requests.show', $req->id) }}'">
                <td>
                    <div style="font-weight:600;color:var(--ink);">{{ Str::limit($req->title, 32) }}</div>
                    <div style="font-size:11px;color:var(--ink-soft);">{{ Str::limit($req->description, 45) }}</div>
                </td>
                <td style="font-size:12.5px;font-weight:500;">{{ $req->requester }}</td>
                <td><span style="font-size:11.5px;color:var(--ink-mid);">{{ $req->category }}</span></td>
                <td>
                    <div style="display:flex;gap:4px;flex-wrap:wrap;">
                        @foreach(array_slice($req->platforms_array ?? [], 0, 2) as $p)
                            <span style="font-size:10px;padding:2px 7px;background:var(--navy-pale);color:var(--navy);border-radius:10px;font-weight:600;">{{ $p }}</span>
                        @endforeach
                    </div>
                </td>
                <td><span class="badge badge--{{ $sc }}">{{ $req->status }}</span></td>
                <td style="font-size:11.5px;color:var(--ink-soft);">{{ $req->created_at->format('M j, Y') }}</td>
            </tr>
            @empty
                <tr><td colspan="6" style="padding:32px;text-align:center;color:var(--ink-faint);">No published posts yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@section('scripts')
<script>
(function() {
    /* ── Request Analytics Live Search ─────────────────────────────────── */
    let visSearchTimer = null;
    const visInput = document.getElementById('vis-search-input');
    const visClear = document.getElementById('vis-search-clear');
    const visPill  = document.getElementById('vis-search-pill');
    const visPillText = document.getElementById('vis-search-pill-text');

    window.applyVisSearch = function(rawQuery) {
        const q = (rawQuery || '').toLowerCase().trim();
        if (!visInput) return;

        if (q) {
            visClear.classList.add('visible');
        } else {
            visClear.classList.remove('visible');
        }

        let matchCount = 0;

        // Filter Recent Published Requests table
        const recTable = document.getElementById('vis-recent-table');
        if (recTable) {
            const rows = recTable.querySelectorAll('tbody tr');
            rows.forEach(tr => {
                if (tr.querySelector('td[colspan]')) return;
                if (!q) {
                    tr.style.display = '';
                    matchCount++;
                } else {
                    const text = tr.textContent.toLowerCase();
                    if (text.includes(q)) {
                        tr.style.display = '';
                        matchCount++;
                    } else {
                        tr.style.display = 'none';
                    }
                }
            });
        }

        if (q) {
            visPill.style.display = 'inline-flex';
            visPillText.textContent = `${matchCount} match${matchCount === 1 ? '' : 'es'}`;
        } else {
            visPill.style.display = 'none';
        }
    };

    window.clearVisSearch = function() {
        if (!visInput) return;
        visInput.value = '';
        window.applyVisSearch('');
        visInput.focus();
    };

    if (visInput) {
        visInput.addEventListener('input', function() {
            clearTimeout(visSearchTimer);
            visSearchTimer = setTimeout(() => {
                window.applyVisSearch(this.value);
            }, 120);
        });
    }
})();
</script>
@endsection