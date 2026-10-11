@extends('layouts.admin')
@section('title', 'Calendar')

@section('head-styles')
<style>
.page-hd { margin-bottom: 24px; }
.page-hd__title { font-family:var(--font-disp); font-size:22px; color:var(--ink); }
.page-hd__sub   { font-size:13px; color:var(--ink-soft); margin-top:3px; }

.cal-layout { display:grid; grid-template-columns:1fr 320px; gap:20px; align-items:start; }
@media(max-width:900px){ .cal-layout{grid-template-columns:1fr;} }

/* CALENDAR PANEL — navy blue */
.cal-panel {
    background:linear-gradient(160deg,#001a4d 0%,#002366 55%,#003080 100%);
    box-shadow:0 8px 32px rgba(0,26,77,0.35);
    border-radius:22px; overflow:hidden; border:none;
}

.cal-toolbar {
    display:flex; align-items:center; justify-content:space-between;
    padding:18px 24px; border-bottom:1px solid rgba(255,255,255,0.1);
    background:rgba(0,0,0,0.15);
}
.cal-nav { display:flex; align-items:center; gap:12px; }
.cal-nav-btn {
    width:32px; height:32px; border-radius:9px;
    background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.18);
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; color:rgba(255,255,255,0.75); text-decoration:none; transition:all .15s;
}
.cal-nav-btn:hover { background:rgba(255,255,255,0.2); color:white; }
.cal-month-label { font-size:17px; font-weight:700; color:white; min-width:160px; text-align:center; letter-spacing:-0.3px; }
.cal-today-btn {
    padding:7px 16px; border-radius:9px;
    background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2);
    font-size:12.5px; font-weight:600; cursor:pointer;
    color:rgba(255,255,255,0.8); font-family:var(--font); text-decoration:none; transition:all .15s;
}
.cal-today-btn:hover { background:rgba(255,255,255,0.2); color:white; }

.cal-grid-head {
    display:grid; grid-template-columns:repeat(7,1fr);
    padding:10px 12px 4px; background:rgba(0,0,0,0.1);
    border-bottom:1px solid rgba(255,255,255,0.07);
}
.cal-grid-head span {
    text-align:center; font-size:10px; font-weight:700;
    color:rgba(255,255,255,0.4); letter-spacing:0.7px; text-transform:uppercase;
}

.cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:2px; padding:6px 10px 14px; }
.cal-day { min-height:88px; padding:8px 6px; border-radius:10px; position:relative; transition:background .12s; cursor:default; border:1px solid transparent; }
.cal-day.has-events { cursor:pointer; }
.cal-day.has-events:hover { background:rgba(255,255,255,0.09); border-color:rgba(255,255,255,0.12); }
.cal-day.selected-day { background:rgba(245,158,11,0.15)!important; border-color:rgba(245,158,11,0.45)!important; }
.cal-day--other { opacity:0.3; cursor:default!important; pointer-events:none; }
.cal-day--today { background:rgba(245,158,11,0.1); border-color:rgba(245,158,11,0.3); }

.cal-day-num {
    font-size:12.5px; font-weight:600; color:rgba(255,255,255,0.55);
    width:26px; height:26px; border-radius:50%;
    display:flex; align-items:center; justify-content:center; margin-bottom:4px;
}
.cal-day--today .cal-day-num { background:#f59e0b; color:#1a1a1a; font-weight:800; box-shadow:0 3px 10px rgba(245,158,11,0.45); }

.day-load { height:3px; border-radius:2px; margin-bottom:4px; width:100%; }
.day-load--low    { background:rgba(52,211,153,0.5); }
.day-load--medium { background:rgba(251,191,36,0.6); }
.day-load--high   { background:rgba(239,68,68,0.6); }

.cal-event {
    display:block; padding:2px 6px; border-radius:5px;
    font-size:9.5px; font-weight:600; color:white;
    white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    margin-bottom:2px; line-height:1.6;
}
.cal-event--urgent  { background:rgba(239,68,68,0.85); }
.cal-event--high    { background:rgba(245,158,11,0.85); color:#1a1a1a; }
.cal-event--medium  { background:rgba(59,110,245,0.8); }
.cal-event--low     { background:rgba(148,163,184,0.45); }
.cal-event--more    { background:none!important; color:rgba(255,255,255,0.5)!important; font-style:italic; font-size:9px; }

.cal-legend {
    display:flex; align-items:center; gap:16px; flex-wrap:wrap;
    padding:12px 22px; border-top:1px solid rgba(255,255,255,0.1); background:rgba(0,0,0,0.15);
}
.leg-item { display:flex; align-items:center; gap:6px; font-size:11px; color:rgba(255,255,255,0.5); }
.leg-dot  { width:9px; height:9px; border-radius:3px; flex-shrink:0; }

/* SIDEBAR */
.side-col { display:flex; flex-direction:column; gap:16px; position:sticky; top:16px; }

/* DAY PANEL */
.day-panel {
    background:white; border-radius:18px; border:1.5px solid #e5e7eb;
    box-shadow:0 4px 20px rgba(0,26,77,0.1); overflow:hidden;
    display:none; animation:slideIn .2s ease;
}
.day-panel.visible { display:block; }
@keyframes slideIn { from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none} }

.dp__head {
    background:linear-gradient(135deg,#001a4d,#002e7a);
    padding:16px 18px; display:flex; align-items:flex-start; justify-content:space-between;
}
.dp__date  { font-size:16px; font-weight:700; color:white; }
.dp__count { font-size:11.5px; color:rgba(255,255,255,0.6); margin-top:2px; }
.dp__close {
    width:28px; height:28px; border-radius:8px;
    background:rgba(255,255,255,0.15); border:none; cursor:pointer;
    display:flex; align-items:center; justify-content:center; color:white; transition:all .15s;
}
.dp__close:hover { background:rgba(255,255,255,0.25); }
.dp__body { padding:12px 14px; max-height:440px; overflow-y:auto; }
.dp__body::-webkit-scrollbar { width:4px; }
.dp__body::-webkit-scrollbar-thumb { background:rgba(0,0,0,0.1); border-radius:4px; }

.req-card {
    border:1.5px solid #e5e7eb; border-radius:12px;
    padding:12px 14px; margin-bottom:8px; position:relative;
    transition:all .15s; display:block; text-decoration:none; color:inherit;
}
.req-card:last-child { margin-bottom:0; }
.req-card:hover { border-color:#a5b4fc; box-shadow:0 3px 12px rgba(0,26,77,0.1); background:#fafbff; }
.req-card--urgent { border-left:4px solid #ef4444; }
.req-card--high   { border-left:4px solid #f59e0b; }
.req-card--medium { border-left:4px solid #3b6ef5; }
.req-card--low    { border-left:4px solid #cbd5e1; }

.req-card__num {
    position:absolute; top:9px; right:10px;
    width:20px; height:20px; border-radius:50%;
    background:#f1f5f9; color:#64748b; font-size:10px; font-weight:800;
    display:flex; align-items:center; justify-content:center;
}
.req-card__title { font-size:13px; font-weight:700; color:#0f172a; line-height:1.3; padding-right:26px; margin-bottom:4px; }
.req-card__meta  { font-size:11px; color:#64748b; }
.req-card__tags  { display:flex; gap:5px; flex-wrap:wrap; margin-top:7px; }
.req-tag { display:inline-flex; padding:2px 8px; border-radius:7px; font-size:10px; font-weight:700; }
.req-tag--urgent  { background:#fee2e2; color:#b91c1c; }
.req-tag--high    { background:#fef3c7; color:#b45309; }
.req-tag--medium  { background:#dbeafe; color:#1d4ed8; }
.req-tag--low     { background:#f1f5f9; color:#64748b; }
.req-tag--approved{ background:#dcfce7; color:#15803d; }
.req-tag--posted  { background:#ede9fe; color:#7c3aed; }
.req-tag--review  { background:#fef3c7; color:#b45309; }
.req-tag--rejected{ background:#fee2e2; color:#b91c1c; }
.req-tag--pending { background:#f8fafc; color:#64748b; border:1px solid #e2e8f0; }

/* STATS CARD */
.info-card { background:var(--card); border-radius:18px; border:1px solid rgba(0,0,0,0.06); box-shadow:0 2px 10px rgba(0,0,0,0.05); overflow:hidden; }
.info-card__head { padding:16px 18px; border-bottom:1.5px solid var(--card-border); background:var(--cream-dark); }
.info-card__title { font-family:var(--font-disp); font-size:16px; color:var(--ink); }
.info-card__sub   { font-size:11.5px; color:var(--ink-soft); margin-top:2px; }
.info-card__body  { padding:16px 18px; }

.cal-stats { display:flex; flex-direction:column; gap:11px; }
.cal-stat-row { display:flex; align-items:center; justify-content:space-between; }
.cal-stat-left { display:flex; align-items:center; gap:8px; font-size:12.5px; color:var(--ink-mid); }
.cal-stat-dot  { width:8px; height:8px; border-radius:2px; flex-shrink:0; }
.cal-stat-val  { font-size:13px; font-weight:700; color:var(--ink); }
.cal-stat-bar-bg   { height:4px; background:var(--cream-dark); border-radius:2px; margin-top:3px; overflow:hidden; }
.cal-stat-bar-fill { height:100%; border-radius:2px; }

.upcoming-list { display:flex; flex-direction:column; }
.upcoming-item { display:flex; align-items:flex-start; gap:10px; padding:11px 18px; border-bottom:1px solid var(--cream-dark); transition:background .1s; }
.upcoming-item:last-child { border-bottom:none; }
.upcoming-item:hover { background:var(--cream); }
.upcoming-dot  { width:9px; height:9px; border-radius:50%; flex-shrink:0; margin-top:4px; }
.upcoming-title { font-size:12.5px; font-weight:600; color:var(--ink); line-height:1.4; }
.upcoming-meta  { font-size:11px; color:var(--ink-soft); margin-top:2px; }
.upcoming-badge { display:inline-flex; padding:2px 7px; border-radius:10px; font-size:9.5px; font-weight:600; margin-top:4px; }
.upcoming-empty { padding:20px 18px; text-align:center; color:var(--ink-faint); font-size:13px; }
</style>
@endsection

@section('content')
@php
    $month = (int)request('month', date('n'));
    $year  = (int)request('year',  date('Y'));
    if($month<1){$month=12;$year--;} if($month>12){$month=1;$year++;}
    $prev_m=$month-1; $prev_y=$year; if($prev_m<1){$prev_m=12;$prev_y--;}
    $next_m=$month+1; $next_y=$year; if($next_m>12){$next_m=1;$next_y++;}
    $first_day     = (int)date('w',mktime(0,0,0,$month,1,$year));
    $days_in_month = (int)date('t',mktime(0,0,0,$month,1,$year));
    $days_in_prev  = (int)date('t',mktime(0,0,0,$prev_m,1,$prev_y));
    $month_name    = date('F Y',mktime(0,0,0,$month,1,$year));
    $today_d=(int)date('j'); $today_m=(int)date('n'); $today_y=(int)date('Y');

    $all_events = \App\Models\PostRequest::whereNotNull('preferred_date')
        ->whereMonth('preferred_date',$month)
        ->whereYear('preferred_date',$year)
        ->orderBy('preferred_date')
        ->orderByRaw("FIELD(priority,'Urgent','High','Medium','Low')")
        ->orderBy('created_at')
        ->get();

    $events = [];
    foreach ($all_events as $ev) {
        $d = (int)date('j', strtotime($ev->preferred_date));
        $events[$d][] = $ev;
    }

    $upcoming = \App\Models\PostRequest::whereNotNull('preferred_date')
        ->whereBetween('preferred_date',[now()->format('Y-m-d'),now()->addDays(7)->format('Y-m-d')])
        ->orderBy('preferred_date')
        ->orderByRaw("FIELD(priority,'Urgent','High','Medium','Low')")
        ->get();

    $month_total    = $all_events->count();
    $month_approved = $all_events->whereIn('status',['Approved','Posted'])->count();
    $month_pending  = $all_events->where('status','Pending Review')->count();
    $month_rejected = $all_events->where('status','Rejected')->count();

    $prio_ev_cls = fn($p) => match(strtolower($p??'low')){
        'urgent'=>'cal-event--urgent','high'=>'cal-event--high','medium'=>'cal-event--medium',default=>'cal-event--low'
    };
    $dot_col = fn($s) => match(true){
        str_contains(strtolower($s),'approved')    =>'#10b981',
        str_contains(strtolower($s),'posted')      =>'#8b5cf6',
        str_contains(strtolower($s),'under review')=>'#f59e0b',
        str_contains(strtolower($s),'rejected')    =>'#ef4444',
        default=>'#94a3b8',
    };
    $badge_style = fn($s) => match(true){
        str_contains(strtolower($s),'approved')    =>'background:#dcfce7;color:#16a34a;',
        str_contains(strtolower($s),'posted')      =>'background:#ede9fe;color:#7c3aed;',
        str_contains(strtolower($s),'under review')=>'background:#fef3c7;color:#d97706;',
        str_contains(strtolower($s),'rejected')    =>'background:#fee2e2;color:#dc2626;',
        default=>'background:#f1f5f9;color:#64748b;',
    };
@endphp

<div class="page-hd">
    <div class="page-hd__title">Scheduling Calendar</div>
    <div class="page-hd__sub">Track preferred posting dates · Click any date to view & sort by priority</div>
</div>

<div class="cal-layout">

    {{-- CALENDAR --}}
    <div class="cal-panel">
        <div class="cal-toolbar">
            <div class="cal-nav">
                <a href="{{ route('admin.calendar') }}?month={{ $prev_m }}&year={{ $prev_y }}" class="cal-nav-btn">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
                <span class="cal-month-label">{{ $month_name }}</span>
                <a href="{{ route('admin.calendar') }}?month={{ $next_m }}&year={{ $next_y }}" class="cal-nav-btn">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>
            <a href="{{ route('admin.calendar') }}?month={{ date('n') }}&year={{ date('Y') }}" class="cal-today-btn">Today</a>
        </div>

        <div class="cal-grid-head">
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                <span>{{ $d }}</span>
            @endforeach
        </div>

        <div class="cal-grid">
        @php
            $total_cells = ceil(($first_day + $days_in_month) / 7) * 7;
            for ($i = 0; $i < $total_cells; $i++) {
                $day_num    = $i - $first_day + 1;
                $is_current = ($day_num >= 1 && $day_num <= $days_in_month);
                $is_today   = $is_current && $day_num===$today_d && $month===$today_m && $year===$today_y;

                if ($is_current)         $display = $day_num;
                elseif ($i < $first_day) $display = $days_in_prev - ($first_day - $i - 1);
                else                     $display = $day_num - $days_in_month;

                $day_evs = ($is_current && isset($events[$day_num])) ? $events[$day_num] : [];
                $cnt     = count($day_evs);

                // Build JSON for side panel
                $ev_json = [];
                foreach ($day_evs as $ev) {
                    $ev_json[] = [
                        'id'       => $ev->id,
                        'title'    => $ev->title,
                        'requester'=> $ev->requester,
                        'priority' => ucfirst(strtolower($ev->priority ?? 'Low')),
                        'status'   => $ev->status,
                        'category' => $ev->category ?? '',
                        'created'  => \Carbon\Carbon::parse($ev->created_at)->format('M j, Y'),
                        'pref'     => \Carbon\Carbon::parse($ev->preferred_date)->format('M j, Y'),
                    ];
                }

                $other_cls   = !$is_current ? ' cal-day--other' : '';
                $today_cls   = $is_today    ? ' cal-day--today' : '';
                $has_cls     = ($is_current && $cnt > 0) ? ' has-events' : '';
                $data_date   = $is_current ? date('F j, Y', mktime(0,0,0,$month,$day_num,$year)) : '';
                $data_events = ($is_current && $cnt > 0) ? htmlspecialchars(json_encode($ev_json), ENT_QUOTES) : '';
                $onclick     = ($is_current && $cnt > 0) ? 'onclick="showPanel(this)"' : '';

                echo "<div class='cal-day$other_cls$today_cls$has_cls' data-date='$data_date' data-events='$data_events' $onclick>";
                echo "<div class='cal-day-num'>$display</div>";

                if ($is_current && $cnt > 0) {
                    $bar = $cnt >= 4 ? 'day-load--high' : ($cnt >= 2 ? 'day-load--medium' : 'day-load--low');
                    echo "<div class='day-load $bar'></div>";
                    $shown = 0;
                    foreach ($day_evs as $ev) {
                        if ($shown >= 2) break;
                        $p     = ucfirst(strtolower($ev->priority ?? 'Low'));
                        $short = htmlspecialchars(mb_strimwidth($ev->title, 0, 15, '…'));
                        echo "<span class='cal-event " . $prio_ev_cls($p) . "' title='" . htmlspecialchars($ev->title) . " [{$p}]'>$short</span>";
                        $shown++;
                    }
                    if ($cnt - $shown > 0) echo "<span class='cal-event cal-event--more'>+" . ($cnt-$shown) . " more</span>";
                }
                echo "</div>";
            }
        @endphp
        </div>

        <div class="cal-legend">
            <div class="leg-item"><span class="leg-dot" style="background:rgba(239,68,68,0.85);"></span>Urgent</div>
            <div class="leg-item"><span class="leg-dot" style="background:rgba(245,158,11,0.85);"></span>High</div>
            <div class="leg-item"><span class="leg-dot" style="background:rgba(59,110,245,0.8);"></span>Medium</div>
            <div class="leg-item"><span class="leg-dot" style="background:rgba(148,163,184,0.45);"></span>Low</div>
            <div class="leg-item"><span class="leg-dot" style="border-radius:50%;background:#f59e0b;"></span>Today</div>
            <div class="leg-item"><span class="leg-dot" style="border-radius:50%;background:rgba(239,68,68,0.6);"></span>Busy</div>
        </div>
    </div>

    {{-- RIGHT SIDEBAR --}}
    <div class="side-col">

        {{-- Day detail panel (appears when date clicked) --}}
        <div class="day-panel" id="day-panel">
            <div class="dp__head">
                <div>
                    <div class="dp__date"  id="panel-date">—</div>
                    <div class="dp__count" id="panel-count"></div>
                </div>
                <button class="dp__close" onclick="closePanel()">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="dp__body" id="panel-body"></div>
        </div>

        {{-- Month Stats --}}
        <div class="info-card">
            <div class="info-card__head">
                <div class="info-card__title">{{ date('F',mktime(0,0,0,$month,1,$year)) }} Overview</div>
                <div class="info-card__sub">{{ $month_total }} scheduled request{{ $month_total!==1?'s':'' }}</div>
            </div>
            <div class="info-card__body">
                @php
                    $safe = max($month_total,1);
                    $si   = [
                        ['label'=>'Approved / Posted','val'=>$month_approved,'pct'=>round($month_approved/$safe*100),'color'=>'#10b981'],
                        ['label'=>'Pending Review',   'val'=>$month_pending,  'pct'=>round($month_pending/$safe*100), 'color'=>'#f59e0b'],
                        ['label'=>'Rejected',         'val'=>$month_rejected, 'pct'=>round($month_rejected/$safe*100),'color'=>'#ef4444'],
                    ];
                @endphp
                <div class="cal-stats">
                    @foreach($si as $s)
                    <div>
                        <div class="cal-stat-row">
                            <div class="cal-stat-left"><span class="cal-stat-dot" style="background:{{ $s['color'] }};"></span>{{ $s['label'] }}</div>
                            <span class="cal-stat-val">{{ $s['val'] }}</span>
                        </div>
                        <div class="cal-stat-bar-bg"><div class="cal-stat-bar-fill" style="width:{{ $s['pct'] }}%;background:{{ $s['color'] }};"></div></div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Upcoming 7 days --}}
        <div class="info-card">
            <div class="info-card__head">
                <div class="info-card__title">Upcoming — Next 7 Days</div>
                <div class="info-card__sub">{{ $upcoming->count() }} request{{ $upcoming->count()!==1?'s':'' }} scheduled</div>
            </div>
            @if($upcoming->isEmpty())
                <div class="upcoming-empty">No upcoming requests in the next 7 days.</div>
            @else
                <div class="upcoming-list">
                @foreach($upcoming as $up)
                    <div class="upcoming-item">
                        <span class="upcoming-dot" style="background:{{ $dot_col($up->status) }};"></span>
                        <div>
                            <div class="upcoming-title">{{ Str::limit($up->title,30) }}</div>
                            <div class="upcoming-meta">{{ \Carbon\Carbon::parse($up->preferred_date)->format('M j, Y') }} · {{ $up->requester }}</div>
                            <span class="upcoming-badge" style="{{ $badge_style($up->status) }}">{{ $up->status }}</span>
                        </div>
                    </div>
                @endforeach
                </div>
            @endif
        </div>

        <a href="{{ route('admin.requests') }}"
           style="display:flex;align-items:center;gap:8px;padding:14px 18px;background:#001a4d;border-radius:14px;font-size:13px;font-weight:600;color:white;text-decoration:none;transition:background .15s;"
           onmouseover="this.style.background='#002366'" onmouseout="this.style.background='#001a4d'">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            View All Requests
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-left:auto;" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
        </a>

    </div>
</div>
@endsection

@section('scripts')
<script>
const PRIO_ORDER = {'Urgent':0,'High':1,'Medium':2,'Low':3};

function cardCls(p){ return {'Urgent':'req-card--urgent','High':'req-card--high','Medium':'req-card--medium'}[p]||'req-card--low'; }
function tagCls(p) { return {'Urgent':'req-tag--urgent','High':'req-tag--high','Medium':'req-tag--medium'}[p]||'req-tag--low'; }
function statCls(s){
    s=s.toLowerCase();
    if(s.includes('approved')) return 'req-tag--approved';
    if(s.includes('posted'))   return 'req-tag--posted';
    if(s.includes('review'))   return 'req-tag--review';
    if(s.includes('rejected')) return 'req-tag--rejected';
    return 'req-tag--pending';
}

function showPanel(el){
    const raw = el.dataset.events;
    if(!raw) return;

    let evs = JSON.parse(raw);
    evs.sort((a,b)=>{
        const pa = PRIO_ORDER[a.priority]??3;
        const pb = PRIO_ORDER[b.priority]??3;
        return pa - pb;
    });

    document.querySelectorAll('.cal-day').forEach(d=>d.classList.remove('selected-day'));
    el.classList.add('selected-day');

    document.getElementById('panel-date').textContent  = el.dataset.date;
    document.getElementById('panel-count').textContent = evs.length + ' request' + (evs.length!==1?'s':'') + ' — priority · earliest first';

    let html = '';
    evs.forEach((ev,i)=>{
        html += `
        <a href="/admin/requests/${ev.id}" class="req-card ${cardCls(ev.priority)}">
            <div class="req-card__num">${i+1}</div>
            <div class="req-card__title">${esc(ev.title)}</div>
            <div class="req-card__meta">
                👤 ${esc(ev.requester)}
                ${ev.category?' · '+esc(ev.category):''}
                · Submitted ${esc(ev.created)}
            </div>
            <div class="req-card__tags">
                <span class="req-tag ${tagCls(ev.priority)}">${esc(ev.priority)}</span>
                <span class="req-tag ${statCls(ev.status)}">${esc(ev.status)}</span>
            </div>
        </a>`;
    });

    document.getElementById('panel-body').innerHTML = html;
    document.getElementById('day-panel').classList.add('visible');
}

function closePanel(){
    document.getElementById('day-panel').classList.remove('visible');
    document.querySelectorAll('.cal-day').forEach(d=>d.classList.remove('selected-day'));
}

document.addEventListener('keydown', e=>{ if(e.key==='Escape') closePanel(); });
function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
</script>
@endsection