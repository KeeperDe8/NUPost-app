@extends('layouts.admin')

@section('title', 'Reports')

@section('head-styles')
<style>
.page-header { margin-bottom: 24px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; }
.page-header h1 { font-size: 22px; font-weight: 800; color: var(--text); letter-spacing: -0.5px; }
.page-header p  { font-size: 13px; color: var(--text-muted); margin-top: 3px; }
.btn-export {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 20px; background: var(--primary); color: white;
    border: none; border-radius: 12px; font-size: 13px; font-weight: 600;
    cursor: pointer; font-family: var(--font); text-decoration: none;
    transition: background .15s; white-space: nowrap; flex-shrink: 0;
}
.btn-export:hover { background: var(--primary-mid); }

/* FILTER */
.filter-panel {
    background: white; border-radius: 18px; border: 1.5px solid var(--border);
    box-shadow: var(--shadow-sm); padding: 20px 22px; margin-bottom: 20px;
}
.filter-panel__title { font-size: 14px; font-weight: 700; color: var(--text); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.filter-row { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.filter-row select, .filter-row input {
    height: 40px; background: var(--bg); border: 1.5px solid var(--border);
    border-radius: 10px; padding: 0 14px; font-size: 13px;
    font-family: var(--font); color: var(--text); outline: none;
    transition: border-color .15s;
}
.filter-row select:focus, .filter-row input:focus { border-color: var(--primary-light); }
.btn-filter {
    height: 40px; padding: 0 18px; background: var(--primary); color: white;
    border: none; border-radius: 10px; font-size: 13px; font-weight: 600;
    cursor: pointer; font-family: var(--font); transition: background .15s;
}
.btn-filter:hover { background: var(--primary-mid); }
.btn-reset-filter {
    height: 40px; padding: 0 16px; background: white; color: var(--text-muted);
    border: 1.5px solid var(--border); border-radius: 10px; font-size: 13px;
    cursor: pointer; font-family: var(--font); text-decoration: none;
    display: flex; align-items: center;
}

/* SUMMARY CARDS */
.summary-row { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; margin-bottom: 20px; }
.sum-card {
    background: white; border-radius: 16px; border: 1.5px solid var(--border);
    box-shadow: var(--shadow-sm); padding: 18px; text-align: center;
}
.sum-card__num { font-size: 28px; font-weight: 800; letter-spacing: -1px; margin-bottom: 4px; }
.sum-card__lbl { font-size: 12px; color: var(--text-muted); font-weight: 500; }

/* TABLE */
.table-panel { background: white; border-radius: 18px; border: 1.5px solid var(--border); box-shadow: var(--shadow-sm); overflow: hidden; }
.table-panel__head { padding: 16px 22px; border-bottom: 1.5px solid #f0f2f8; display: flex; align-items: center; justify-content: space-between; }
.table-panel__title { font-size: 14px; font-weight: 700; color: var(--text); }
.table-panel__meta  { font-size: 12.5px; color: var(--text-muted); }

table { width: 100%; border-collapse: collapse; }
thead tr { border-bottom: 1.5px solid #f0f2f8; }
th { padding: 10px 16px; text-align: left; font-size: 10px; font-weight: 700; color: var(--text-light); text-transform: uppercase; letter-spacing: 0.7px; white-space: nowrap; }
tbody tr { border-bottom: 1px solid #f4f6fb; transition: background .1s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #f9faff; }
td { padding: 12px 16px; font-size: 12.5px; vertical-align: middle; }

.badge { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 20px; font-size: 10.5px; font-weight: 600; white-space: nowrap; }
.badge--pending      { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
.badge--under-review { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
.badge--approved     { background: #dcfce7; color: #16a34a; }
.badge--posted       { background: #ede9fe; color: #7c3aed; }
.badge--rejected     { background: #fee2e2; color: #dc2626; }
.badge--high         { background: #fee2e2; color: #dc2626; }
.badge--urgent       { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
.badge--medium       { background: #fef3c7; color: #d97706; }
.badge--low          { background: #f1f5f9; color: #64748b; }

.empty-state { padding: 48px; text-align: center; color: var(--text-light); font-size: 13px; }
</style>
@endsection

@section('content')

@php
    $filter_status   = request('status', 'all');
    $filter_category = request('category', 'all');
    $filter_priority = request('priority', 'all');
    $date_from       = request('date_from', '');
    $date_to         = request('date_to', '');

    $query = \App\Models\PostRequest::query();

    if ($filter_status !== 'all') {
        $status_map = [
            'pending'  => 'Pending Review', 'review' => 'Under Review',
            'approved' => 'Approved',       'posted' => 'Posted', 'rejected' => 'Rejected',
        ];
        if (isset($status_map[$filter_status])) $query->where('status', $status_map[$filter_status]);
    }
    if ($filter_category !== 'all') $query->where('category', $filter_category);
    if ($filter_priority !== 'all') $query->where('priority', ucfirst($filter_priority));
    if ($date_from) $query->whereDate('created_at', '>=', $date_from);
    if ($date_to)   $query->whereDate('created_at', '<=', $date_to);

    $requests = $query->orderByDesc('created_at')->get();
    $total    = $requests->count();

    $r_pending  = $requests->where('status','Pending Review')->count();
    $r_approved = $requests->whereIn('status',['Approved','Posted'])->count();
    $r_rejected = $requests->where('status','Rejected')->count();

    $categories = \App\Models\PostRequest::selectRaw('category')->distinct()->orderBy('category')->pluck('category');
@endphp

{{-- PAGE HEADER --}}
<div class="page-header">
    <div>
        <h1>Reports</h1>
        <p>Filter, view, and export request data</p>
    </div>
    <div style="display:flex; gap:10px;">
        <a href="{{ route('admin.reports.export') }}?type=details&status={{ $filter_status }}&category={{ $filter_category }}&priority={{ $filter_priority }}&date_from={{ $date_from }}&date_to={{ $date_to }}"
           class="btn-export" style="background:#002366;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export Details & History
        </a>
        <a href="{{ route('admin.reports.export') }}?type=performance&status={{ $filter_status }}&category={{ $filter_category }}&priority={{ $filter_priority }}&date_from={{ $date_from }}&date_to={{ $date_to }}"
           class="btn-export" style="background:#b89600;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            Export Performance
        </a>
    </div>
</div>

{{-- FILTER --}}
<div class="filter-panel">
    <div class="filter-panel__title">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        Filter Reports
    </div>
    <form method="GET" action="{{ route('admin.reports') }}" class="filter-row">
        <select name="status">
            <option value="all"      {{ $filter_status === 'all'      ? 'selected' : '' }}>All Statuses</option>
            <option value="pending"  {{ $filter_status === 'pending'  ? 'selected' : '' }}>Pending Review</option>
            <option value="review"   {{ $filter_status === 'review'   ? 'selected' : '' }}>Under Review</option>
            <option value="approved" {{ $filter_status === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="posted"   {{ $filter_status === 'posted'   ? 'selected' : '' }}>Posted</option>
            <option value="rejected" {{ $filter_status === 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
        <select name="category">
            <option value="all">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ $filter_category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <select name="priority">
            <option value="all"    {{ $filter_priority === 'all'    ? 'selected' : '' }}>All Priorities</option>
            <option value="urgent" {{ $filter_priority === 'urgent' ? 'selected' : '' }}>Urgent</option>
            <option value="high"   {{ $filter_priority === 'high'   ? 'selected' : '' }}>High</option>
            <option value="medium" {{ $filter_priority === 'medium' ? 'selected' : '' }}>Medium</option>
            <option value="low"    {{ $filter_priority === 'low'    ? 'selected' : '' }}>Low</option>
        </select>
        <input type="date" name="date_from" value="{{ $date_from }}" placeholder="From date">
        <input type="date" name="date_to"   value="{{ $date_to }}"   placeholder="To date">
        <button type="submit" class="btn-filter">Apply Filter</button>
        @if($filter_status !== 'all' || $filter_category !== 'all' || $filter_priority !== 'all' || $date_from || $date_to)
            <a href="{{ route('admin.reports') }}" class="btn-reset-filter">Reset</a>
        @endif
    </form>
</div>

{{-- SUMMARY CARDS --}}
<div class="summary-row">
    <div class="sum-card">
        <div class="sum-card__num" style="color:var(--primary-light);">{{ $total }}</div>
        <div class="sum-card__lbl">Matching Requests</div>
    </div>
    <div class="sum-card">
        <div class="sum-card__num" style="color:#d97706;">{{ $r_pending }}</div>
        <div class="sum-card__lbl">Pending Review</div>
    </div>
    <div class="sum-card">
        <div class="sum-card__num" style="color:#10b981;">{{ $r_approved }}</div>
        <div class="sum-card__lbl">Approved / Posted</div>
    </div>
    <div class="sum-card">
        <div class="sum-card__num" style="color:#ef4444;">{{ $r_rejected }}</div>
        <div class="sum-card__lbl">Rejected</div>
    </div>
</div>

{{-- TABLE --}}
<div class="table-panel">
    <div class="table-panel__head">
        <div class="table-panel__title">Request Report</div>
        <div class="table-panel__meta">
            Showing <strong>{{ $total }}</strong> result{{ $total !== 1 ? 's' : '' }}
        </div>
    </div>
    <div style="overflow-x:auto;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Title</th>
                    <th>Requestor</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Platforms</th>
                    <th>Preferred Date</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
            @forelse($requests as $i => $req)
                @php
                    $sl = strtolower($req->status);
                    $sc = match(true) {
                        str_contains($sl,'approved')     => 'approved',
                        str_contains($sl,'posted')       => 'posted',
                        str_contains($sl,'under review') => 'under-review',
                        str_contains($sl,'rejected')     => 'rejected',
                        default                          => 'pending',
                    };
                    $pc = match(strtolower($req->priority ?? '')) {
                        'urgent'=>'urgent','high'=>'high','medium'=>'medium',default=>'low'
                    };
                    $plats = $req->platforms_array ?? [];
                @endphp
                <tr onclick="window.location='{{ route('admin.requests.show', $req->id) }}'" style="cursor:pointer;">
                    <td style="color:var(--text-light);font-size:12px;">{{ $i + 1 }}</td>
                    <td>
                        <div style="font-weight:600;font-size:12.5px;color:var(--text);">{{ Str::limit($req->title,30) }}</div>
                        <div style="font-size:11px;color:var(--text-muted);">{{ Str::limit($req->description,45) }}</div>
                    </td>
                    <td style="font-size:12.5px;font-weight:500;">{{ $req->requester }}</td>
                    <td style="font-size:12px;color:var(--text-muted);">{{ $req->category }}</td>
                    <td><span class="badge badge--{{ $pc }}">{{ strtoupper($req->priority) }}</span></td>
                    <td><span class="badge badge--{{ $sc }}">{{ $req->status }}</span></td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            @foreach(array_slice($plats,0,2) as $p)
                                <span style="font-size:10px;padding:2px 7px;background:var(--primary-pale);color:var(--primary-light);border-radius:10px;font-weight:600;">{{ $p }}</span>
                            @endforeach
                            @if(count($plats) > 2)
                                <span style="font-size:10px;color:var(--text-light);">+{{ count($plats)-2 }}</span>
                            @endif
                        </div>
                    </td>
                    <td style="font-size:11.5px;color:var(--text-muted);">
                        {{ $req->preferred_date ? \Carbon\Carbon::parse($req->preferred_date)->format('M j, Y') : '—' }}
                    </td>
                    <td style="font-size:11.5px;color:var(--text-muted);white-space:nowrap;">
                        {{ $req->created_at->format('M j, Y') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
                        <div class="empty-state">
                            <svg width="36" height="36" fill="none" stroke="#d1d5db" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:8px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            <div>No requests match your filters.</div>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection