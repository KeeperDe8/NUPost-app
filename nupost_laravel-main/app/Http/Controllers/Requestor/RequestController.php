<?php

namespace App\Http\Controllers\Requestor;

use App\Http\Controllers\Controller;
use App\Models\PostRequest;
use App\Models\RequestComment;
use App\Models\Notification;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    private function getUnreadCount(): int
    {
        return Notification::where('user_id', session('user_id'))->where('is_read', false)->count();
    }

    public function index(Request $request)
    {
        $user_name = session('name');
        $search    = trim($request->input('search', ''));
        $filter    = $request->input('filter', 'all');

        $allowed_filters = ['all', 'pending', 'seen', 'approved', 'posted'];
        if (!in_array($filter, $allowed_filters)) $filter = 'all';

        $status_map = [
            'pending'  => 'Pending Review',
            'seen'     => 'Under Review',
            'approved' => 'Approved',
            'posted'   => 'Posted',
        ];

        $query = PostRequest::where('requester', $user_name);

        if ($filter !== 'all') {
            $query->where('status', $status_map[$filter]);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhere('category', 'like', "%$search%");
            });
        }

        $requests     = $query->orderBy('created_at', 'desc')->get();
        $total        = $requests->count();
        $unread_count = $this->getUnreadCount();

        return view('requestor.requests', compact(
            'requests', 'total', 'search', 'filter', 'unread_count'
        ));
    }

    public function create()
    {
        $unread_count = $this->getUnreadCount();
        return view('requestor.create_request', compact('unread_count'));
    }

    public function store(Request $request)
    {
        $user_name = session('name');

        $title       = trim($request->input('title', ''));
        $description = trim($request->input('description', ''));
        $category    = trim($request->input('category', ''));
        $priority    = trim($request->input('priority', ''));
        $post_date   = trim($request->input('post_date', ''));
        $caption     = trim($request->input('caption', ''));
        $platforms   = $request->input('platforms', []);
        $platform    = implode(',', $platforms);

        if (!$title || !$description || !$category || !$priority) {
            return back()->withInput()->with('error', 'Please fill in all required fields.');
        }

        $media_file = '';
        if ($request->hasFile('media')) {
            $upload_dir    = public_path('uploads');
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/quicktime'];
            $max_size      = 10 * 1024 * 1024;
            $uploaded      = [];

            foreach (array_slice($request->file('media'), 0, 4) as $file) {
                if (!$file->isValid()) continue;
                if ($file->getSize() > $max_size) continue;
                if (!in_array($file->getMimeType(), $allowed_types)) continue;
                $filename  = 'media_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($upload_dir, $filename);
                $uploaded[] = $filename;
            }
            $media_file = implode(',', $uploaded);
        }

        PostRequest::create([
            'title'          => $title,
            'requester'      => $user_name,
            'category'       => $category,
            'priority'       => $priority,
            'status'         => 'Pending Review',
            'description'    => $description,
            'platform'       => $platform,
            'caption'        => $caption,
            'preferred_date' => $post_date ?: null,
            'media_file'     => $media_file,
        ]);

        return back()->with('success', 'Request submitted successfully!');
    }

    public function chat(Request $request, $id)
    {
        $user_name = session('name');
        $req       = PostRequest::where('id', $id)->where('requester', $user_name)->firstOrFail();
        $chat_messages = RequestComment::where('request_id', $id)->orderBy('created_at', 'asc')->get();
        $unread_count  = $this->getUnreadCount();

        return view('requestor.request_chat', compact('req', 'chat_messages', 'unread_count'));
    }

    public function sendChat(Request $request, $id)
    {
        $user_name = session('name');
        $req       = PostRequest::where('id', $id)->where('requester', $user_name)->firstOrFail();

        $message = trim($request->input('chat_message', ''));
        if ($message !== '') {
            RequestComment::create([
                'request_id'  => $id,
                'sender_role' => 'requestor',
                'sender_name' => $user_name,
                'message'     => $message,
            ]);
        }

        return redirect()->route('requestor.requests.chat', $id);
    }

    public function calendar(Request $request)
    {
        $user_name = session('name');
        $user_id   = session('user_id');

        $month = (int)($request->input('month', date('n')));
        $year  = (int)($request->input('year',  date('Y')));
        $is_public = (bool)session('cal_public', false);

        if ($request->has('toggle_public')) {
            session(['cal_public' => !$is_public]);
            return redirect()->route('requestor.calendar', [
                'month' => $month, 'year' => $year
            ]);
        }

        if ($month < 1)  { $month = 12; $year--; }
        if ($month > 12) { $month = 1;  $year++; }

        $prev_month = $month - 1; $prev_year = $year;
        if ($prev_month < 1) { $prev_month = 12; $prev_year--; }
        $next_month = $month + 1; $next_year = $year;
        if ($next_month > 12) { $next_month = 1; $next_year++; }

        if ($is_public) {
            $all_events = PostRequest::whereNotNull('preferred_date')
                ->whereMonth('preferred_date', $month)
                ->whereYear('preferred_date', $year)
                ->orderBy('preferred_date')
                ->get();
        } else {
            $all_events = PostRequest::where('requester', $user_name)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->orderBy('created_at')
                ->get();
        }

        $events = [];
        foreach ($all_events as $ev) {
            $day = $is_public
                ? (int)date('j', strtotime($ev->preferred_date))
                : (int)date('j', strtotime($ev->created_at));
            $events[$day][] = $ev;
        }

        $today     = now()->format('Y-m-d');
        $in7days   = now()->addDays(7)->format('Y-m-d');

        if ($is_public) {
            $upcoming = PostRequest::whereNotNull('preferred_date')
                ->whereBetween('preferred_date', [$today, $in7days])
                ->orderBy('preferred_date')
                ->get()
                ->map(function ($r) use ($user_name) {
                    $r->is_mine = $r->requester === $user_name;
                    return $r;
                });
        } else {
            $upcoming = PostRequest::where('requester', $user_name)
                ->whereDate('created_at', '>=', $today)
                ->whereDate('created_at', '<=', $in7days)
                ->orderBy('created_at')
                ->get()
                ->map(function ($r) { $r->is_mine = true; return $r; });
        }

        $unread_count   = $this->getUnreadCount();
        $today_day      = (int)date('j');
        $today_month    = (int)date('n');
        $today_year     = (int)date('Y');
        $first_day      = (int)date('w', mktime(0,0,0,$month,1,$year));
        $days_in_month  = (int)date('t', mktime(0,0,0,$month,1,$year));
        $days_in_prev   = (int)date('t', mktime(0,0,0,$prev_month,1,$prev_year));
        $month_name     = date('F Y', mktime(0,0,0,$month,1,$year));

        return view('requestor.calendar', compact(
            'events', 'upcoming', 'month', 'year', 'month_name',
            'prev_month', 'prev_year', 'next_month', 'next_year',
            'today_day', 'today_month', 'today_year',
            'first_day', 'days_in_month', 'days_in_prev',
            'is_public', 'unread_count'
        ));
    }
}