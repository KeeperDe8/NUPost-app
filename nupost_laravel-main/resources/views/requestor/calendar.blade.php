@extends('layouts.requestor')

@section('title', 'Calendar')

@section('head-styles')
<style>
.main { max-width: 900px; margin: 0 auto; padding: 32px 24px; display: flex; flex-direction: column; gap: 20px; }
.page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.page-header-left h1 { font-size: 22px; font-weight: 700; letter-spacing: -0.3px; }
.page-header-left p  { font-size: 13px; color: var(--color-text-muted); margin-top: 3px; }
.toggle-wrap { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
.toggle-label { font-size: 12.5px; font-weight: 500; color: var(--color-text-muted); }
.toggle-btn { position: relative; width: 44px; height: 24px; border-radius: 12px; background: #d1d5db; border: none; cursor: pointer; transition: background .2s; flex-shrink: 0; padding: 0; }
.toggle-btn.on { background: var(--color-primary); }
.toggle-btn::after { content: ''; position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; border-radius: 50%; background: white; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
.toggle-btn.on::after { transform: translateX(20px); }
.public-banner { display: flex; align-items: center; gap: 10px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px 14px; font-size: 12.5px; color: #1e40af; }
.public-banner svg { flex-shrink: 0; }
.cal-card { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); overflow: hidden; }
.cal-toolbar { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--color-border); }
.cal-nav { display: flex; align-items: center; gap: 12px; }
.cal-nav-btn { width: 28px; height: 28px; border-radius: 6px; border: 1px solid var(--color-border); background: none; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--color-text-muted); transition: background .15s; text-decoration: none; }
.cal-nav-btn:hover { background: var(--color-bg); }
.cal-month-label { font-size: 14px; font-weight: 600; min-width: 120px; text-align: center; }
.cal-today-btn { padding: 5px 14px; border-radius: 6px; border: 1px solid var(--color-border); background: none; font-size: 12.5px; font-weight: 500; cursor: pointer; color: var(--color-text-muted); font-family: var(--font); text-decoration: none; }
.cal-today-btn:hover { background: var(--color-bg); }
.cal-grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
.cal-grid thead th { padding: 10px 8px; text-align: center; font-size: 11px; font-weight: 600; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid var(--color-border); }
.cal-grid tbody td { vertical-align: top; height: 100px; padding: 6px 8px; border: 1px solid #f3f4f6; font-size: 12px; }
.cal-day-num { font-size: 12px; font-weight: 500; color: var(--color-text); width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin-bottom: 4px; }
.cal-day-num--today { background: var(--color-primary); color: white; font-weight: 700; }
.cal-day--other .cal-day-num { color: #d1d5db; }
.cal-day--other { background: #fafafa; }
.day-load { width: 100%; height: 4px; border-radius: 2px; margin-bottom: 4px; }
.day-load--low { background: #bbf7d0; }
.day-load--medium { background: #fde68a; }
.day-load--high { background: #fca5a5; }
.day-count { font-size: 9px; font-weight: 600; color: var(--color-text-muted); margin-bottom: 3px; display: flex; align-items: center; gap: 3px; }
.day-count-dot { width: 6px; height: 6px; border-radius: 50%; background: #6b7280; }
.cal-event { display: block; padding: 3px 6px; border-radius: 4px; font-size: 10px; font-weight: 500; color: white; background: #002366; margin-bottom: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.4; }
.cal-event--posted   { background: #7c3aed; }
.cal-event--approved { background: #059669; }
.cal-event--pending  { background: #6b7280; }
.cal-event--review   { background: #d97706; }
.cal-event--others   { background: #9ca3af; font-style: italic; }
.cal-event--mine     { border-left: 3px solid #f97316; }
.cal-legend { display: flex; align-items: center; gap: 16px; flex-wrap: wrap; padding: 12px 20px; border-top: 1px solid var(--color-border); }
.legend-item { display: flex; align-items: center; gap: 6px; font-size: 11.5px; color: var(--color-text-muted); }
.legend-dot { width: 12px; height: 12px; border-radius: 3px; flex-shrink: 0; }
.legend-dot--mine    { background: #002366; }
.legend-dot--others  { background: #9ca3af; }
.legend-dot--posted  { background: #7c3aed; }
.legend-dot--today   { background: var(--color-primary); border-radius: 50%; }
.legend-dot--busy-low  { background: #bbf7d0; border-radius: 2px; }
.legend-dot--busy-high { background: #fca5a5; border-radius: 2px; }
.upcoming-card { background: white; border-radius: var(--radius); box-shadow: var(--shadow-sm); padding: 20px; }
.upcoming-card h3 { font-size: 14px; font-weight: 600; margin-bottom: 14px; }
.upcoming-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; color: #9ca3af; gap: 8px; }
.upcoming-empty p { font-size: 13px; }
.upcoming-item { display: flex; align-items: center; gap: 14px; padding: 10px 0; border-bottom: 1px solid #f3f4f6; }
.upcoming-item:last-child { border-bottom: none; }
.upcoming-dot { width: 10px; height: 10px; border-radius: 50%; background: #002366; flex-shrink: 0; }
.upcoming-dot--others { background: #9ca3af; }
.upcoming-dot--posted { background: #7c3aed; }
.upcoming-title { font-size: 13px; font-weight: 500; }
.upcoming-meta  { font-size: 11.5px; color: var(--color-text-muted); margin-top: 1px; }
.upcoming-mine-tag { display: inline-block; padding: 1px 7px; background: #eff6ff; color: var(--color-primary); border-radius: 10px; font-size: 10px; font-weight: 600; margin-left: 6px; }
.upcoming-others-tag { display: inline-block; padding: 1px 7px; background: #f3f4f6; color: #6b7280; border-radius: 10px; font-size: 10px; font-weight: 600; margin-left: 6px; }
@media (max-width: 768px) { .cal-grid tbody td { height: 70px; padding: 4px; } }
</style>
@endsection

@section('content')
<main class="main">

    <!-- PAGE HEADER + TOGGLE -->
    <div class="page-header">
        <div class="page-header-left">
            <h1>Post Tracking Calendar</h1>
            <p>{{ $is_public ? "Showing all users' preferred posting dates (titles only)" : 'Showing your personal post schedule' }}</p>
        </div>
        <div class="toggle-wrap">
            <span class="toggle-label">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:3px;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Public Calendar
            </span>
            <a href="{{ route('requestor.calendar', ['toggle_public' => 1, 'month' => $month, 'year' => $year]) }}"
               style="text-decoration:none;">
                <div class="toggle-btn {{ $is_public ? 'on' : '' }}"></div>
            </a>
        </div>
    </div>

    <!-- PUBLIC MODE BANNER -->
    @if($is_public)
    <div class="public-banner">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>
            <strong>Public view is ON</strong> — You can see all users' preferred posting dates (titles only, no personal info).
            Dates with many requests are highlighted in red so you can pick a less busy date.
            <strong>Your requests</strong> are shown with an orange left border.
        </span>
    </div>
    @endif

    <!-- CALENDAR CARD -->
    <div class="cal-card">
        <div class="cal-toolbar">
            <div class="cal-nav">
                <a href="{{ route('requestor.calendar', ['month' => $prev_month, 'year' => $prev_year]) }}" class="cal-nav-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                </a>
                <span class="cal-month-label">{{ $month_name }}</span>
                <a href="{{ route('requestor.calendar', ['month' => $next_month, 'year' => $next_year]) }}" class="cal-nav-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            </div>
            <a href="{{ route('requestor.calendar', ['month' => $today_month, 'year' => $today_year]) }}" class="cal-today-btn">Today</a>
        </div>

        <table class="cal-grid">
            <thead>
                <tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>
            </thead>
            <tbody>
            @php
                $cell = 0;
                $total_cells = ceil(($first_day + $days_in_month) / 7) * 7;
                $user_name = session('name');
                echo "<tr>";
                for ($i = 0; $i < $total_cells; $i++) {
                    $day_num    = $i - $first_day + 1;
                    $is_current = ($day_num >= 1 && $day_num <= $days_in_month);
                    $is_today   = $is_current && $day_num === $today_day && $month === $today_month && $year === $today_year;

                    if ($is_current)         $display_num = $day_num;
                    elseif ($i < $first_day) $display_num = $days_in_prev - ($first_day - $i - 1);
                    else                     $display_num = $day_num - $days_in_month;

                    $cell_class = $is_current ? "" : "cal-day--other";
                    echo "<td class='$cell_class'>";
                    $num_class = $is_today ? "cal-day-num cal-day-num--today" : "cal-day-num";
                    echo "<div class='$num_class'>$display_num</div>";

                    if ($is_current && isset($events[$day_num])) {
                        $day_events = $events[$day_num];
                        $count      = count($day_events);

                        if ($is_public) {
                            $busy_class = $count >= 4 ? "high" : ($count >= 2 ? "medium" : "low");
                            echo "<div class='day-load day-load--$busy_class'></div>";
                            echo "<div class='day-count'><span class='day-count-dot'></span>$count request" . ($count > 1 ? "s" : "") . "</div>";
                        }

                        $shown = 0;
                        foreach ($day_events as $ev) {
                            if ($shown >= ($is_public ? 2 : 3)) break;

                            $st      = strtolower($ev->status);
                            $is_mine = $is_public ? ($ev->requester === $user_name) : true;

                            if ($is_public && !$is_mine) {
                                $short = htmlspecialchars(mb_strimwidth($ev->title, 0, 18, "…"));
                                echo "<span class='cal-event cal-event--others' title='Someone else has a request on this date'>$short</span>";
                            } else {
                                $ev_class = match(true) {
                                    str_contains($st, "posted")       => "cal-event cal-event--posted",
                                    str_contains($st, "approved")     => "cal-event cal-event--approved",
                                    str_contains($st, "under review") => "cal-event cal-event--review",
                                    default                           => "cal-event cal-event--pending",
                                };
                                if ($is_public) $ev_class .= " cal-event--mine";
                                $short = htmlspecialchars(mb_strimwidth($ev->title, 0, 18, "…"));
                                if (!$is_public) {
                                    $time = date("g:i A", strtotime($ev->created_at));
                                    echo "<span class='$ev_class' title='" . htmlspecialchars($ev->title) . "'>$short</span>";
                                } else {
                                    echo "<span class='$ev_class' title='" . htmlspecialchars($ev->title) . " (Your request)'>$short</span>";
                                }
                            }
                            $shown++;
                        }
                        $remaining = $count - $shown;
                        if ($remaining > 0) {
                            echo "<span style='font-size:9px;color:var(--color-primary);font-weight:600;'>+$remaining more</span>";
                        }
                    }
                    echo "</td>";
                    $cell++;
                    if ($cell % 7 === 0 && $i < $total_cells - 1) echo "</tr><tr>";
                }
                echo "</tr>";
            @endphp
            </tbody>
        </table>

        <!-- LEGEND -->
        <div class="cal-legend">
            @if($is_public)
                <div class="legend-item"><span class="legend-dot legend-dot--mine"></span>Your request</div>
                <div class="legend-item"><span class="legend-dot legend-dot--others"></span>Others' request</div>
                <div class="legend-item"><span class="legend-dot legend-dot--busy-low"></span>Open day</div>
                <div class="legend-item"><span class="legend-dot legend-dot--busy-high"></span>Busy day</div>
                <div class="legend-item"><span class="legend-dot legend-dot--today"></span>Today</div>
            @else
                <div class="legend-item"><span class="legend-dot legend-dot--mine"></span>Scheduled</div>
                <div class="legend-item"><span class="legend-dot legend-dot--posted"></span>Posted</div>
                <div class="legend-item"><span class="legend-dot legend-dot--today"></span>Today</div>
            @endif
        </div>
    </div>

    <!-- UPCOMING -->
    <div class="upcoming-card">
        <h3>{{ $is_public ? 'Upcoming Posts — Next 7 Days (All Users)' : 'Your Upcoming Posts — Next 7 Days' }}</h3>
        @if($upcoming->isEmpty())
            <div class="upcoming-empty">
                <svg width="36" height="36" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <p>No upcoming scheduled posts in the next 7 days</p>
            </div>
        @else
            @foreach($upcoming as $up)
                @php
                    $st      = strtolower($up->status);
                    $is_mine = $up->is_mine ?? true;
                    $dot_class = !$is_mine ? "upcoming-dot upcoming-dot--others"
                               : (str_contains($st, "posted") ? "upcoming-dot upcoming-dot--posted" : "upcoming-dot");
                    $date_fmt = $is_public
                        ? \Carbon\Carbon::parse($up->preferred_date)->format('M j, Y')
                        : \Carbon\Carbon::parse($up->created_at)->format('M j, Y · g:i A');
                @endphp
                <div class="upcoming-item">
                    <span class="{{ $dot_class }}"></span>
                    <div>
                        <div class="upcoming-title">
                            {{ $up->title }}
                            @if($is_public)
                                @if($is_mine)
                                    <span class="upcoming-mine-tag">Yours</span>
                                @else
                                    <span class="upcoming-others-tag">Others</span>
                                @endif
                            @endif
                        </div>
                        <div class="upcoming-meta">{{ $date_fmt }} · {{ $up->status }}</div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

</main>
@endsection