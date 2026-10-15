<?php

namespace App\Http\Controllers\Requestor;

use App\Http\Controllers\Controller;
use App\Models\PostRequest;
use App\Models\RequestComment;
use App\Models\RequestActivity;
use App\Models\Notification;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    /**
     * Helper to get unread notification count
     */
    private function getUnreadCount(): int
    {
        return Notification::where('user_id', session('user_id'))
                           ->where('is_read', false)
                           ->count();
    }

    /**
     * Display list of requests
     */
    public function index(Request $request)
    {
        $user_name = session('name');
        $search    = trim($request->input('search', ''));
        $filter    = $request->input('filter', 'all');

        $status_map = [
            'pending'  => 'Pending Review',
            'seen'     => 'Under Review',
            'approved' => 'Approved',
            'posted'   => 'Posted',
        ];

        $user_id   = session('user_id');
        $query = PostRequest::where(function ($q) use ($user_name, $user_id) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('post_requests', 'user_id') && $user_id) {
                $q->where('user_id', $user_id)->orWhere('requester', $user_name);
            } else {
                $q->where('requester', $user_name);
            }
        });

        if ($filter !== 'all') {
            if ($filter === 'pending') {
                $query->whereIn('status', ['Pending Review', 'Pending']);
            } elseif (isset($status_map[$filter])) {
                $query->where('status', $status_map[$filter]);
            }
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

    /**
     * Show create request form
     */
    public function create()
    {
        $unread_count = $this->getUnreadCount();
        return view('requestor.create_request', compact('unread_count'));
    }

    /**
     * Store new request
     */
    public function store(Request $request)
    {
        $user_name = session('name');
        
        $request->validate([
            'title'       => 'required|string|min:5|max:255',
            'description' => 'required|string|min:15',
            'category'    => 'required|in:Checking of Materials,Posting with Ready-Made PubMat,Template-Based PubMat,Standard PubMat,Multiple Collaterals / Tarpaulins,New Campaign / Creative Concept,Event Documentation',
            'priority'    => 'required|in:Low,Medium,High,Urgent',
            'platforms'   => 'required|array|min:1',
            'post_date'   => 'required|date|after:today',
        ]);

        // 🛡️ Exact Duplicate Hash Check (within 24h)
        $title = trim((string) $request->input('title', ''));
        $description = trim((string) $request->input('description', ''));
        $contentHash = md5(strtolower($title) . '|' . strtolower($description));
        $recentDuplicates = PostRequest::where('status', '!=', 'Rejected')
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        foreach ($recentDuplicates as $dup) {
            $dupHash = md5(strtolower(trim($dup->title ?? '')) . '|' . strtolower(trim($dup->description ?? '')));
            if ($dupHash === $contentHash) {
                if ($request->expectsJson() || $request->is('api/*')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Duplicate submission detected. An identical request was submitted within the last 24 hours.',
                    ], 422);
                }
                return back()->withInput()->with('error', 'Duplicate submission detected. An identical request was submitted within the last 24 hours.');
            }
        }

        $media_file = '';
        if ($request->hasFile('media')) {
            $upload_dir    = public_path('uploads');
            $altUploadDir  = base_path('public_html/uploads');
            $mirrorNeeded  = is_dir($altUploadDir) && realpath($altUploadDir) !== realpath($upload_dir);

            foreach (array_slice($request->file('media'), 0, 10) as $file) {
                if (!$file->isValid()) continue;
                $origExt   = strtolower($file->getClientOriginalExtension());
                // Normalize jfif to standard jpg so web templates and web servers serve it as image/jpeg
                $saveExt   = ($origExt === 'jfif') ? 'jpg' : $origExt;
                $filename  = 'media_' . uniqid() . '.' . $saveExt;
                $file->move($upload_dir, $filename);

                // If public_html/uploads exists and is separate from public_path('uploads'), mirror file
                if ($mirrorNeeded) {
                    @copy($upload_dir . '/' . $filename, $altUploadDir . '/' . $filename);
                }

                // Image Optimization
                if (in_array($saveExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    \App\Services\ImageOptimizer::optimize($upload_dir . '/' . $filename);
                    if ($mirrorNeeded) {
                        @copy($upload_dir . '/' . $filename, $altUploadDir . '/' . $filename);
                    }
                }

                $uploaded[] = $filename;
            }
            $media_file = implode(',', $uploaded);
        }

        $payload = [
            'title'          => $request->title,
            'requester'      => $user_name,
            'category'       => $request->category,
            'priority'       => $request->priority,
            'status'         => 'Pending Review',
            'description'    => $request->description,
            'platform'       => implode(',', $request->input('platforms', [])),
            'caption'        => $request->caption,
            'preferred_date' => $request->post_date ?: null,
            'media_file'     => $media_file,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('post_requests', 'user_id') && session('user_id')) {
            $payload['user_id'] = session('user_id');
        }

        $req = PostRequest::create($payload);

        if (\Illuminate\Support\Facades\Schema::hasColumn('post_requests', 'request_id') && empty($req->request_id)) {
            $reqCode = 'REQ-' . str_pad((string) $req->id, 5, '0', STR_PAD_LEFT);
            $req->update(['request_id' => $reqCode]);
        }

        \App\Models\AuditLog::record('post_request_created');

        RequestActivity::create([
            'request_id' => $req->id,
            'actor'      => $user_name,
            'action'     => 'Request submitted',
        ]);

        // Send email to all admins
        $admins = \App\Models\User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            if ($admin->email) {
                try {
                    $html = $this->getNewRequestAdminEmailHtml($admin->name, $req->title, $user_name);
                    \Illuminate\Support\Facades\Mail::send([], [], function ($msg) use ($admin, $html) {
                        $msg->to($admin->email, $admin->name)
                            ->subject("NUPost Admin: New Request Submitted")
                            ->html($html);
                    });
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('[NUPost] New request admin email failed: ' . $e->getMessage());
                }
            }
        }

        return redirect()->route('requestor.requests')->with('success', 'Request submitted successfully!');
    }

    /**
     * View Request Tracking (The "Show" page)
     * FIXED: Added chat_messages to prevent Undefined Variable error
     */
    public function show($id)
    {
        $user_name = session('name');
        
        $req = PostRequest::where('id', $id)
                          ->where('requester', $user_name)
                          ->firstOrFail();

        $chat_messages = RequestComment::where('request_id', $id)
                                       ->orderBy('created_at', 'asc')
                                       ->get();
                                   
        $activities = RequestActivity::where('request_id', $id)
                                     ->orderBy('created_at', 'desc')
                                     ->get();
                                     
        $unread_count = $this->getUnreadCount();

        return view('requestor.request_tracking', compact('req', 'activities', 'unread_count', 'chat_messages'));
    }

 /**
     * Show Edit Form
     * Only accessible if the status is exactly 'Pending Review'
     */
    public function edit($id)
    {
        $user_name = session('name');
        
        // Gagamit tayo ng firstOrFail para kung hindi 'Pending Review', 
        // automatic 404 error para hindi ma-bypass ang lock.
        $req = PostRequest::where('id', $id)
                          ->where('requester', $user_name)
                          ->where('status', 'Pending Review')
                          ->firstOrFail();
                                
        $unread_count = $this->getUnreadCount();
        
        return view('requestor.edit_request', compact('req', 'unread_count'));
    }

    /**
     * Update existing request
     */
    public function update(Request $request, $id)
    {
        $user_name = session('name');
        
        // Security check: Siguraduhin na ang ina-update ay 'Pending Review' pa rin
        $req = PostRequest::where('id', $id)
                          ->where('requester', $user_name)
                          ->where('status', 'Pending Review')
                          ->firstOrFail();

        // Basic Validation para hindi mag-crash ang database
        $request->validate([
            'title'       => 'required|string|min:5|max:255',
            'description' => 'required|string|min:15',
            'category'    => 'required|in:Checking of Materials,Posting with Ready-Made PubMat,Template-Based PubMat,Standard PubMat,Multiple Collaterals / Tarpaulins,New Campaign / Creative Concept,Event Documentation',
            'priority'    => 'required|in:Low,Medium,High,Urgent',
            'platforms'   => 'required|array|min:1',
            'post_date'   => 'required|date|after:today',
        ]);

        $data = [
            'title'          => $request->title,
            'description'    => $request->description,
            'category'       => $request->category,
            'priority'       => $request->priority,
            'caption'        => $request->caption,
            'platform'       => implode(',', $request->input('platforms', [])),
            'preferred_date' => $request->post_date ?: null,
        ];

        // Media Upload Logic (Limit to 10 files)
        if ($request->hasFile('media')) {
            $uploaded = [];
            // Burahin ang lumang files kung gusto mo ng fresh (Optional)
            // if($req->media_file) { /* delete logic here */ }

            $upload_dir    = public_path('uploads');
            $altUploadDir  = base_path('public_html/uploads');
            $mirrorNeeded  = is_dir($altUploadDir) && realpath($altUploadDir) !== realpath($upload_dir);

            foreach (array_slice($request->file('media'), 0, 10) as $file) {
                if ($file->isValid()) {
                    $origExt   = strtolower($file->getClientOriginalExtension());
                    $saveExt   = ($origExt === 'jfif') ? 'jpg' : $origExt;
                    $filename  = 'media_' . uniqid() . '.' . $saveExt;
                    $file->move($upload_dir, $filename);

                    if ($mirrorNeeded) {
                        @copy($upload_dir . '/' . $filename, $altUploadDir . '/' . $filename);
                    }

                    // Image Optimization
                    if (in_array($saveExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        \App\Services\ImageOptimizer::optimize($upload_dir . '/' . $filename);
                        if ($mirrorNeeded) {
                            @copy($upload_dir . '/' . $filename, $altUploadDir . '/' . $filename);
                        }
                    }

                    $uploaded[] = $filename;
                }
            }
            
            if (!empty($uploaded)) {
                $data['media_file'] = implode(',', $uploaded);
            }
        }

        // Execute Update
        $req->update($data);

        \App\Models\AuditLog::record('post_request_edited');

        // Record Activity
        RequestActivity::create([
            'request_id' => $id,
            'actor'      => $user_name,
            'action'     => 'Request details updated',
        ]);

        return redirect()->route('requestor.requests')
                         ->with('success', 'Request updated successfully!');
    }

    /**
     * Delete request
     */
    public function destroy($id)
    {
        $user_name = session('name');
        $req       = PostRequest::where('id', $id)
                                ->where('requester', $user_name)
                                ->where('status', 'Pending Review')
                                ->firstOrFail();

        if ($req->media_file) {
            foreach (explode(',', $req->media_file) as $file) {
                $path = public_path('uploads/' . trim($file));
                if (file_exists($path)) unlink($path);
            }
        }

        $req->delete();
        return redirect()->route('requestor.requests')->with('success', 'Request deleted successfully!');
    }

    /**
     * Request Chat Page
     */
    public function chat($id)
    {
        $user_name     = session('name');
        $req           = PostRequest::where('id', $id)->where('requester', $user_name)->firstOrFail();
        $chat_messages = RequestComment::where('request_id', $id)->orderBy('created_at', 'asc')->get();
        $unread_count  = $this->getUnreadCount();

        return view('requestor.request_chat', compact('req', 'chat_messages', 'unread_count'));
    }

    /**
     * Send Chat Message
     */
    public function sendChat(Request $request, $id)
    {
        $user_name = session('name');
        $req       = PostRequest::where('id', $id)->where('requester', $user_name)->firstOrFail();

        $message = trim($request->input('chat_message', ''));
        if ($message !== '') {
            // Deduplication guard: duplicate message submitted within 5 seconds
            $duplicate = RequestComment::where('request_id', $id)
                ->where('sender_role', 'requestor')
                ->where('sender_name', $user_name)
                ->where('message', $message)
                ->where('created_at', '>=', now()->subSeconds(5))
                ->exists();

            if ($duplicate) {
                return redirect()->route('requestor.requests.chat', $id);
            }

            RequestComment::create([
                'request_id'  => $id,
                'sender_role' => 'requestor',
                'sender_name' => $user_name,
                'message'     => $message,
            ]);

            RequestActivity::create([
                'request_id' => $id,
                'actor'      => $user_name,
                'action'     => 'Sent a message to admin',
            ]);

            // Send email to all admins
            $admins = \App\Models\User::where('role', 'admin')->get();
            foreach ($admins as $admin) {
                if ($admin->email) {
                    try {
                        $html = $this->getNewChatAdminEmailHtml($admin->name, $req->title, $user_name, $message);
                        \Illuminate\Support\Facades\Mail::send([], [], function ($msg) use ($admin, $req, $html) {
                            $msg->to($admin->email, $admin->name)
                                ->subject("NUPost Admin: New chat from " . $req->requester)
                                ->html($html);
                        });
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('[NUPost] Admin chat email failed: ' . $e->getMessage());
                    }
                }
            }
        }

        return redirect()->route('requestor.requests.chat', $id);
    }

    /**
    /**
     * Calendar View
     */
    public function calendar(Request $request)
{
    $user_name = session('name');
    $month     = (int)($request->input('month', date('n')));
    $year      = (int)($request->input('year',  date('Y')));

    // Toggle public/private mode
    if ($request->has('toggle_public')) {
        $current = session('cal_public', false);
        session(['cal_public' => !$current]);
        return redirect()->route('requestor.calendar', [
            'month' => $month,
            'year'  => $year,
        ]);
    }

    $is_public = (bool)session('cal_public', false);

    if ($month < 1)  { $month = 12; $year--; }
    if ($month > 12) { $month = 1;  $year++; }

    $prev_month = $month - 1; $prev_year = $year;
    if ($prev_month < 1)  { $prev_month = 12; $prev_year--; }
    $next_month = $month + 1; $next_year = $year;
    if ($next_month > 12) { $next_month = 1;  $next_year++; }

    $query = PostRequest::whereNotNull('preferred_date')
                        ->whereMonth('preferred_date', $month)
                        ->whereYear('preferred_date', $year);

    if (!$is_public) {
        $query->where('requester', $user_name);
    }

    $all_events = $query->orderBy('preferred_date')->get();

    $events = [];
    foreach ($all_events as $ev) {
        $day = (int)date('j', strtotime($ev->preferred_date));
        $events[$day][] = $ev;
    }

    $upcoming_query = PostRequest::whereNotNull('preferred_date')
        ->where('preferred_date', '>=', now()->startOfDay())
        ->where('preferred_date', '<=', now()->addDays(7)->endOfDay());

    if (!$is_public) {
        $upcoming_query->where('requester', $user_name);
    }

    $upcoming = $upcoming_query->orderBy('preferred_date', 'asc')->get();

    $unread_count  = $this->getUnreadCount();
    $today_day     = (int)date('j');
    $today_month   = (int)date('n');
    $today_year    = (int)date('Y');
    $first_day     = (int)date('w', mktime(0, 0, 0, $month, 1, $year));
    $days_in_month = (int)date('t', mktime(0, 0, 0, $month, 1, $year));
    $days_in_prev  = (int)date('t', mktime(0, 0, 0, $prev_month, 1, $prev_year));
    $month_name    = date('F Y', mktime(0, 0, 0, $month, 1, $year));

    return view('requestor.calendar', compact(
        'events', 'month', 'year', 'month_name',
        'prev_month', 'prev_year', 'next_month', 'next_year',
        'today_day', 'today_month', 'today_year',
        'first_day', 'days_in_month', 'days_in_prev',
        'is_public', 'unread_count', 'upcoming'
    ));
}
    /**
     * Get requests by specific date (AJAX)
     */
    public function requestsByDate(Request $request)
    {
        $date = trim($request->query('date', ''));
        if (!$date) return response()->json(['total' => 0, 'requests' => []]);

        $requests = PostRequest::whereDate('preferred_date', $date)
                                ->select('title', 'status')
                                ->get();

        return response()->json([
            'total'    => $requests->count(),
            'requests' => $requests,
        ]);
    }

    private function getNewRequestAdminEmailHtml($adminName, $requestTitle, $requestorName)
    {
        return "<!DOCTYPE html><html><body style='margin:0;padding:0;background:#f5f6fa;font-family:Arial,sans-serif;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background:#f5f6fa;padding:40px 0;'>
        <tr><td align='center'>
        <table width='520' cellpadding='0' cellspacing='0' style='background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);'>
        <tr><td style='background:#002366;padding:28px 40px;text-align:center;'>
            <div style='font-size:22px;font-weight:700;color:white;'>NUPost Admin</div>
            <div style='font-size:13px;color:rgba(255,255,255,0.7);margin-top:4px;'>New Request Submitted</div>
        </td></tr>
        <tr><td style='padding:32px 40px;'>
            <p style='font-size:14px;color:#374151;margin:0 0 16px;'>Hi <strong>{$adminName}</strong>,</p>
            <p style='font-size:14px;color:#374151;margin:0 0 20px;'>A new social media request has been submitted by <strong>{$requestorName}</strong>:</p>
            <div style='background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;margin-bottom:16px;'>
                <div style='font-size:15px;font-weight:600;color:#111827;'>{$requestTitle}</div>
            </div>
            <p style='font-size:13px;color:#6b7280;margin:0;'>Log in to the NUPost Admin Panel to review this request.</p>
        </td></tr>
        </table></td></tr></table></body></html>";
    }

    private function getNewChatAdminEmailHtml($adminName, $requestTitle, $requestorName, $messageText)
    {
        $escapedMessage = htmlspecialchars($messageText, ENT_QUOTES, 'UTF-8');
        return "<!DOCTYPE html><html><body style='margin:0;padding:0;background:#f5f6fa;font-family:Arial,sans-serif;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background:#f5f6fa;padding:40px 0;'>
        <tr><td align='center'>
        <table width='520' cellpadding='0' cellspacing='0' style='background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);'>
        <tr><td style='background:#002366;padding:28px 40px;text-align:center;'>
            <div style='font-size:22px;font-weight:700;color:white;'>NUPost Admin</div>
            <div style='font-size:13px;color:rgba(255,255,255,0.7);margin-top:4px;'>New Chat Message</div>
        </td></tr>
        <tr><td style='padding:32px 40px;'>
            <p style='font-size:14px;color:#374151;margin:0 0 16px;'>Hi <strong>{$adminName}</strong>,</p>
            <p style='font-size:14px;color:#374151;margin:0 0 20px;'><strong>{$requestorName}</strong> has posted a new comment on a request:</p>
            <div style='background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;margin-bottom:16px;'>
                <div style='font-size:13px;color:#6b7280;margin-bottom:4px;'>Request</div>
                <div style='font-size:15px;font-weight:600;color:#111827;'>{$requestTitle}</div>
            </div>
            <div style='background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px 20px;margin-bottom:16px;'>
                <div style='font-size:11px;font-weight:700;color:#1e40af;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;'>💬 Message</div>
                <div style='font-size:14px;color:#1e3a5f;line-height:1.6;'>{$escapedMessage}</div>
            </div>
            <p style='font-size:13px;color:#6b7280;margin:0;'>Log in to NUPost Admin Panel to view and reply to this comment.</p>
        </td></tr>
        </table></td></tr></table></body></html>";
    }
}