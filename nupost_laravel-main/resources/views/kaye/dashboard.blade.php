<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ms. Kaye's Dashboard - NUPost</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/clearlogo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/clearlogo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy: #002366;
            --navy-mid: #0a3380;
            --navy-pale: #f0f4ff;
            --gold: #FFD700;
            --gold-dark: #b89600;
            --gold-pale: #fefbeb;
            --border: #e2e8f0;
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --text-muted: #64748b;
            --success: #10b981;
            --pending: #64748b;
            --review: #d97706;
            --rejected: #ef4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        /* HEADER */
        header {
            background: var(--navy);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .header-title h1 {
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-title h1 span {
            color: var(--gold);
        }

        .header-title p {
            font-size: 12.5px;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 2px;
        }

        .header-meta {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .session-badge {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: var(--gold);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-logout {
            background: transparent;
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 10px;
            padding: 8px 16px;
            font-size: 12.5px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: rgba(255,255,255,0.1);
            border-color: white;
        }

        /* MAIN CONTAINER */
        .container {
            max-width: 1300px;
            margin: 30px auto;
            padding: 0 24px;
        }

        /* SUMMARY CARDS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card-info h3 {
            font-size: 12.5px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .stat-card-info p {
            font-size: 32px;
            font-weight: 800;
            color: var(--navy);
            letter-spacing: -1px;
        }

        /* CONTROLS (SEARCH & FILTER) */
        .controls-row {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
        }

        .search-form {
            display: flex;
            gap: 10px;
            flex-grow: 1;
            max-width: 600px;
            width: 100%;
        }

        .search-form input {
            flex-grow: 1;
            height: 42px;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 0 16px;
            font-size: 13.5px;
            outline: none;
            background: #f8fafc;
        }

        .search-form input:focus {
            border-color: var(--navy);
            background: white;
        }

        .btn-search {
            height: 42px;
            background: var(--navy);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0 20px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn-search:hover {
            background: var(--navy-mid);
        }

        .filter-tabs {
            display: flex;
            gap: 8px;
            overflow-x: auto;
        }

        .filter-tab {
            padding: 8px 16px;
            background: #f1f5f9;
            color: var(--text-muted);
            border: none;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .filter-tab:hover {
            background: #cbd5e1;
            color: var(--text);
        }

        .filter-tab.active {
            background: var(--navy-pale);
            color: var(--navy);
            border: 1px solid rgba(0, 35, 102, 0.15);
        }

        /* VIEW TOGGLE */
        .view-switch {
            display: flex;
            background: #e2e8f0;
            padding: 4px;
            border-radius: 12px;
            gap: 4px;
            margin-bottom: 24px;
            width: fit-content;
        }

        .view-btn {
            border: none;
            background: transparent;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s;
        }

        .view-btn.active {
            background: white;
            color: var(--navy);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        /* TABLE VIEW */
        .table-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            padding: 14px 20px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 16px 20px;
            font-size: 13.5px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: #f9faff;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-pending { background: #f1f5f9; color: #64748b; }
        .badge-review { background: var(--gold-pale); color: var(--gold-dark); border: 1px solid #fde68a; }
        .badge-approved { background: #dcfce7; color: var(--success); }
        .badge-posted { background: #ede9fe; color: #7c3aed; }
        .badge-rejected { background: #fee2e2; color: var(--rejected); }

        .btn-view-details {
            background: #f1f5f9;
            color: var(--navy);
            border: none;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-view-details:hover {
            background: var(--navy-pale);
        }

        /* CALENDAR VIEW */
        .calendar-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
            display: none;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-header h2 {
            font-size: 18px;
            font-weight: 700;
            color: var(--navy);
        }

        .calendar-nav-btn {
            background: #f1f5f9;
            border: none;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .calendar-nav-btn:hover {
            background: #cbd5e1;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #cbd5e1;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            overflow: hidden;
        }

        .calendar-day-header {
            background: #f8fafc;
            padding: 10px;
            text-align: center;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        .calendar-cell {
            background: white;
            min-height: 100px;
            padding: 8px;
            position: relative;
        }

        .calendar-cell.inactive {
            background: #f8fafc;
        }

        .calendar-cell-num {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .calendar-event {
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 4px;
            margin-bottom: 3px;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-weight: 600;
        }

        .calendar-event-pending { background: #f1f5f9; color: #64748b; }
        .calendar-event-review { background: #fef3c7; color: #d97706; }
        .calendar-event-approved { background: #dcfce7; color: #16a34a; }
        .calendar-event-posted { background: #ede9fe; color: #7c3aed; }
        .calendar-event-rejected { background: #fee2e2; color: #dc2626; }

        /* MODAL */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: fadeIn 0.2s ease-out;
        }

        .modal-content {
            background: white;
            border-radius: 24px;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255,255,255,0.2);
            animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { transform: translateY(24px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            padding: 24px 30px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--navy-pale);
        }

        .modal-title h2 {
            font-size: 18px;
            font-weight: 800;
            color: var(--navy);
        }

        .modal-title p {
            font-size: 12.5px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .modal-close {
            background: transparent;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: var(--text-muted);
        }

        .modal-close:hover {
            color: var(--text);
        }

        .modal-body {
            padding: 30px;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 24px;
        }

        .detail-item h4 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .detail-item p {
            font-size: 14px;
            font-weight: 500;
        }

        .caption-box {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            font-family: inherit;
            font-size: 13.5px;
            line-height: 1.6;
            margin-bottom: 24px;
            white-space: pre-line;
        }

        .media-box h4 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 10px;
            letter-spacing: 0.5px;
        }

        .media-gallery {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .media-preview-container {
            width: 120px;
            height: 120px;
            border-radius: 12px;
            border: 1px solid var(--border);
            overflow: hidden;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .media-preview-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .media-preview-container video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* HISTORY TIMELINE */
        .history-box h4 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 12px;
            letter-spacing: 0.5px;
        }

        .timeline {
            display: flex;
            flex-direction: column;
            gap: 14px;
            border-left: 2px solid #cbd5e1;
            padding-left: 20px;
            margin-left: 6px;
        }

        .timeline-item {
            position: relative;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            background: var(--navy);
            border-radius: 50%;
            left: -26px;
            top: 4px;
        }

        .timeline-meta {
            font-size: 11px;
            color: var(--text-muted);
            margin-bottom: 2px;
        }

        .timeline-content {
            font-size: 13px;
            font-weight: 500;
        }

        /* EMPTY STATE */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: var(--text-muted);
            font-size: 14px;
        }

        .empty-state svg {
            margin: 0 auto 12px;
            display: block;
            color: #cbd5e1;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-title">
            <h1>NUPost<span>.</span> Stakeholder</h1>
            <p>NU Lipa Social Media Requests Directory (Read-Only stakeholder view)</p>
        </div>
        <div class="header-meta">
            @php
                $exp = session('expires_at') ? \Carbon\Carbon::parse(session('expires_at')) : null;
                $days = $exp ? max(0, now()->diffInDays($exp, false)) : 0;
            @endphp
            <div class="session-badge">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Access Expires in {{ $days }} day{{ $days !== 1 ? 's' : '' }}
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn-logout">Sign Out</button>
            </form>
        </div>
    </header>

    <div class="container">
        <!-- STATS ROW -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-card-info">
                    <h3>All Requests</h3>
                    <p>{{ $requests->count() }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-info">
                    <h3>Pending Review</h3>
                    <p style="color:var(--pending)">{{ $pending }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-info">
                    <h3>Under Review</h3>
                    <p style="color:var(--review)">{{ $review }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-info">
                    <h3>Approved</h3>
                    <p style="color:var(--success)">{{ $approved }}</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-card-info">
                    <h3>Posted</h3>
                    <p style="color: #7c3aed;">{{ $posted }}</p>
                </div>
            </div>
        </div>

        <!-- CONTROLS -->
        <div class="controls-row">
            <form method="GET" action="{{ route('kaye.dashboard') }}" class="search-form">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search requests by title, category, requester...">
                <button type="submit" class="btn-search">Search</button>
            </form>

            <div class="filter-tabs">
                <a href="{{ route('kaye.dashboard') }}?filter=all&search={{ $search }}" class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">All Status</a>
                <a href="{{ route('kaye.dashboard') }}?filter=pending&search={{ $search }}" class="filter-tab {{ $filter === 'pending' ? 'active' : '' }}">Pending</a>
                <a href="{{ route('kaye.dashboard') }}?filter=review&search={{ $search }}" class="filter-tab {{ $filter === 'review' ? 'active' : '' }}">Under Review</a>
                <a href="{{ route('kaye.dashboard') }}?filter=approved&search={{ $search }}" class="filter-tab {{ $filter === 'approved' ? 'active' : '' }}">Approved</a>
                <a href="{{ route('kaye.dashboard') }}?filter=posted&search={{ $search }}" class="filter-tab {{ $filter === 'posted' ? 'active' : '' }}">Posted</a>
                <a href="{{ route('kaye.dashboard') }}?filter=rejected&search={{ $search }}" class="filter-tab {{ $filter === 'rejected' ? 'active' : '' }}">Rejected</a>
            </div>
        </div>

        <!-- VIEW SWITCH -->
        <div class="view-switch">
            <button class="view-btn active" id="btnList" onclick="toggleView('list')">List View</button>
            <button class="view-btn" id="btnCalendar" onclick="toggleView('calendar')">Calendar View</button>
        </div>

        <!-- LIST VIEW -->
        <div class="table-card" id="listView">
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Title</th>
                        <th>Requester</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Preferred Post Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        @php
                            $st = strtolower($req->status);
                            $badgeCls = match(true) {
                                str_contains($st, 'approved') => 'badge-approved',
                                str_contains($st, 'posted') => 'badge-posted',
                                str_contains($st, 'under review') => 'badge-review',
                                str_contains($st, 'rejected') => 'badge-rejected',
                                default => 'badge-pending',
                            };
                        @endphp
                        <tr>
                            <td style="font-weight: 700; color: var(--navy);">{{ $req->request_id ?? 'N/A' }}</td>
                            <td style="font-weight: 600;">{{ $req->title }}</td>
                            <td>{{ $req->requester }}</td>
                            <td>{{ $req->category }}</td>
                            <td>
                                @php
                                    $pc = match(strtolower($req->priority ?? '')) {
                                        'urgent'=>'#ea580c','high'=>'#dc2626','medium'=>'#d97706',default=>'#64748b'
                                    };
                                @endphp
                                <span style="font-weight: 700; color: {{ $pc }}">{{ strtoupper($req->priority) }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $badgeCls }}">{{ $req->status }}</span>
                            </td>
                            <td style="color: var(--text-muted);">
                                {{ $req->preferred_date ? $req->preferred_date->format('M j, Y') : 'Not scheduled' }}
                            </td>
                            <td>
                                <button class="btn-view-details" onclick="openDetailsModal({{ $req->id }})">Details</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="empty-state">
                                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9z"/></svg>
                                    No requests found matching your filters.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- CALENDAR VIEW -->
        <div class="calendar-card" id="calendarView">
            @php
                $currentMonth = (int) date('m');
                $currentYear = (int) date('Y');
                
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
                $firstDayIndex = (int) date('w', strtotime("$currentYear-$currentMonth-01"));
                
                $monthName = date('F Y', strtotime("$currentYear-$currentMonth-01"));
            @endphp
            <div class="calendar-header">
                <h2>{{ $monthName }}</h2>
                <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">Scheduled Publication Calendar</div>
            </div>
            
            <div class="calendar-grid">
                <!-- Day Headers -->
                <div class="calendar-day-header">Sun</div>
                <div class="calendar-day-header">Mon</div>
                <div class="calendar-day-header">Tue</div>
                <div class="calendar-day-header">Wed</div>
                <div class="calendar-day-header">Thu</div>
                <div class="calendar-day-header">Fri</div>
                <div class="calendar-day-header">Sat</div>
                
                <!-- Blank days -->
                @for($i = 0; $i < $firstDayIndex; $i++)
                    <div class="calendar-cell inactive"></div>
                @endfor
                
                <!-- Days of month -->
                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $dayStr = str_pad($day, 2, '0', STR_PAD_LEFT);
                        $dateStr = "$currentYear-" . str_pad($currentMonth, 2, '0', STR_PAD_LEFT) . "-$dayStr";
                        
                        $dayEvents = $calendarRequests->filter(function($r) use ($dateStr) {
                            if($r->preferred_date) {
                                return $r->preferred_date->format('Y-m-d') === $dateStr;
                            }
                            return false;
                        });
                    @endphp
                    <div class="calendar-cell">
                        <div class="calendar-cell-num">{{ $day }}</div>
                        @foreach($dayEvents as $e)
                            @php
                                $st = strtolower($e->status);
                                $eCls = match(true) {
                                    str_contains($st, 'approved') => 'calendar-event-approved',
                                    str_contains($st, 'posted') => 'calendar-event-posted',
                                    str_contains($st, 'under review') => 'calendar-event-review',
                                    str_contains($st, 'rejected') => 'calendar-event-rejected',
                                    default => 'calendar-event-pending',
                                };
                            @endphp
                            <div class="calendar-event {{ $eCls }}" onclick="openDetailsModal({{ $e->id }})" title="{{ $e->title }}">
                                {{ $e->title }}
                            </div>
                        @endforeach
                    </div>
                @endfor
                
                <!-- Padding days after -->
                @php
                    $totalCells = $firstDayIndex + $daysInMonth;
                    $remainingCells = (7 - ($totalCells % 7)) % 7;
                @endphp
                @for($i = 0; $i < $remainingCells; $i++)
                    <div class="calendar-cell inactive"></div>
                @endfor
            </div>
        </div>
    </div>

    <!-- REQUEST DETAIL MODAL -->
    <div class="modal" id="detailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">
                    <h2 id="modalReqTitle">Loading Request...</h2>
                    <p id="modalReqCode">REQ-00000</p>
                </div>
                <button class="modal-close" onclick="closeDetailsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <h4>Requester</h4>
                        <p id="modalRequester">—</p>
                    </div>
                    <div class="detail-item">
                        <h4>Category</h4>
                        <p id="modalCategory">—</p>
                    </div>
                    <div class="detail-item">
                        <h4>Priority</h4>
                        <p id="modalPriority">—</p>
                    </div>
                    <div class="detail-item">
                        <h4>Status</h4>
                        <p id="modalStatus">—</p>
                    </div>
                    <div class="detail-item">
                        <h4>Target Platforms</h4>
                        <p id="modalPlatforms">—</p>
                    </div>
                    <div class="detail-item">
                        <h4>Preferred Date</h4>
                        <p id="modalPreferredDate">—</p>
                    </div>
                </div>

                <div class="detail-item" style="margin-bottom: 20px;">
                    <h4>Description</h4>
                    <p id="modalDescription" style="line-height: 1.6; color: #334155;"></p>
                </div>

                <div style="margin-bottom: 24px;">
                    <h4 style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px; letter-spacing: 0.5px;">Caption Content</h4>
                    <div class="caption-box" id="modalCaption"></div>
                </div>

                <div class="media-box" id="modalMediaSection" style="display: none;">
                    <h4>Attached Media Materials</h4>
                    <div class="media-gallery" id="modalMediaGallery"></div>
                </div>

                <div class="history-box">
                    <h4>Request Timeline & History</h4>
                    <div class="timeline" id="modalTimeline"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleView(view) {
            const listBtn = document.getElementById('btnList');
            const calBtn = document.getElementById('btnCalendar');
            const listView = document.getElementById('listView');
            const calView = document.getElementById('calendarView');

            if (view === 'list') {
                listBtn.classList.add('active');
                calBtn.classList.remove('active');
                listView.style.display = 'block';
                calView.style.display = 'none';
            } else {
                listBtn.classList.remove('active');
                calBtn.classList.add('active');
                listView.style.display = 'none';
                calView.style.display = 'block';
            }
        }

        function openDetailsModal(id) {
            const modal = document.getElementById('detailsModal');
            modal.style.display = 'flex';
            
            // Set loading text
            document.getElementById('modalReqTitle').innerText = 'Loading request...';
            document.getElementById('modalReqCode').innerText = '';
            document.getElementById('modalRequester').innerText = '—';
            document.getElementById('modalCategory').innerText = '—';
            document.getElementById('modalPriority').innerText = '—';
            document.getElementById('modalStatus').innerText = '—';
            document.getElementById('modalPlatforms').innerText = '—';
            document.getElementById('modalPreferredDate').innerText = '—';
            document.getElementById('modalDescription').innerText = '—';
            document.getElementById('modalCaption').innerText = '';
            document.getElementById('modalMediaSection').style.display = 'none';
            document.getElementById('modalMediaGallery').innerHTML = '';
            document.getElementById('modalTimeline').innerHTML = 'Loading timeline...';

            fetch(`/api/request_details.php?request_id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const req = data.data.request;
                        
                        document.getElementById('modalReqTitle').innerText = req.title;
                        document.getElementById('modalReqCode').innerText = req.request_id || `REQ-${String(req.id).padStart(5, '0')}`;
                        document.getElementById('modalRequester').innerText = req.requester || 'N/A';
                        document.getElementById('modalCategory').innerText = req.category || 'N/A';
                        document.getElementById('modalPriority').innerText = req.priority ? req.priority.toUpperCase() : 'N/A';
                        document.getElementById('modalStatus').innerHTML = `<span class="badge badge-${req.status.toLowerCase().replace(' ', '-')}" style="padding: 2px 8px; font-size: 11px;">${req.status}</span>`;
                        document.getElementById('modalPlatforms').innerText = req.platform || 'N/A';
                        
                        let dateText = 'Not scheduled';
                        if (req.preferred_date) {
                            const d = new Date(req.preferred_date);
                            dateText = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        }
                        document.getElementById('modalPreferredDate').innerText = dateText;
                        document.getElementById('modalDescription').innerText = req.description || 'No description provided.';
                        
                        document.getElementById('modalCaption').innerText = req.caption || 'No caption text submitted.';

                        // Media Gallery
                        if (req.media_file) {
                            const files = req.media_file.split(',').filter(f => f.trim() !== '');
                            if (files.length > 0) {
                                const gallery = document.getElementById('modalMediaGallery');
                                gallery.innerHTML = '';
                                files.forEach(file => {
                                    const preview = document.createElement('div');
                                    preview.className = 'media-preview-container';
                                    
                                    const isVideo = file.toLowerCase().endsWith('.mp4') || file.toLowerCase().endsWith('.mov');
                                    if (isVideo) {
                                        preview.innerHTML = `<video src="/uploads/${file}" muted preload="metadata"></video>`;
                                    } else {
                                        preview.innerHTML = `<img src="/uploads/${file}" alt="Media Item">`;
                                    }

                                    preview.onclick = () => window.open(`/uploads/${file}`, '_blank');
                                    gallery.appendChild(preview);
                                });
                                document.getElementById('modalMediaSection').style.display = 'block';
                            }
                        }

                        // Timeline / Activity
                        const timeline = document.getElementById('modalTimeline');
                        timeline.innerHTML = '';
                        if (data.data.activities && data.data.activities.length > 0) {
                            data.data.activities.forEach(act => {
                                const item = document.createElement('div');
                                item.className = 'timeline-item';
                                
                                const date = new Date(act.created_at);
                                const dateStr = date.toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true });

                                item.innerHTML = `
                                    <div class="timeline-meta"><strong>${act.actor}</strong> &bull; ${dateStr}</div>
                                    <div class="timeline-content">${act.action}</div>
                                `;
                                timeline.appendChild(item);
                            });
                        } else {
                            timeline.innerHTML = '<div style="font-size: 13px; color: var(--text-muted);">No activity recorded for this request.</div>';
                        }
                    } else {
                        alert('Error loading request details: ' + data.message);
                        closeDetailsModal();
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error connecting to NUPost servers.');
                    closeDetailsModal();
                });
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }

        // Close on clicking outside modal content
        window.onclick = function(event) {
            const modal = document.getElementById('detailsModal');
            if (event.target === modal) {
                closeDetailsModal();
            }
        }

        // Prevent viewing authenticated pages via back button after logout (bfcache)
        window.addEventListener('pageshow', function (event) {
            if (event.persisted || (window.performance && window.performance.navigation && window.performance.navigation.type === 2)) {
                window.location.reload();
            }
        });
    </script>
</body>
</html>
