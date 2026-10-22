<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostRequest;
use App\Models\RequestComment;
use App\Models\RequestActivity;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RequestManagementController extends Controller
{
    // ── LIST ALL REQUESTS ────────────────────────────────────────
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $filter = $request->input('filter', 'all');
        $sort   = $request->input('sort', 'urgency');

        $pending  = PostRequest::whereIn('status', ['Pending', 'Pending Review'])->count();
        $review   = PostRequest::where('status', 'Under Review')->count();
        $approved = PostRequest::where('status', 'Approved')->count();
        $posted   = PostRequest::where('status', 'Posted')->count();
        $rejected = PostRequest::where('status', 'Rejected')->count();

        // Formula for SLA Slack: (preferred_date - current_date) - sla_days
        $isSqlite = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite';
        $slackSql = $isSqlite
            ? "(julianday(COALESCE(preferred_date, date('now', '+3 days'))) - julianday('now')) - (CASE category WHEN 'Event Documentation' THEN 30 WHEN 'New Campaign / Creative Concept' THEN 10 WHEN 'Multiple Collaterals / Tarpaulins' THEN 5 WHEN 'Standard PubMat' THEN 3 WHEN 'Template-Based PubMat' THEN 2 ELSE 1 END)"
            : "DATEDIFF(COALESCE(preferred_date, DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY)), CURRENT_DATE) - (CASE category WHEN 'Event Documentation' THEN 30 WHEN 'New Campaign / Creative Concept' THEN 10 WHEN 'Multiple Collaterals / Tarpaulins' THEN 5 WHEN 'Standard PubMat' THEN 3 WHEN 'Template-Based PubMat' THEN 2 ELSE 1 END)";

        $at_risk_count = PostRequest::whereIn('status', ['Pending', 'Pending Review', 'Under Review'])
            ->whereRaw("($slackSql) < 0")
            ->count();
        $due_soon_count = PostRequest::whereIn('status', ['Pending', 'Pending Review', 'Under Review'])
            ->whereRaw("($slackSql) >= 0 AND ($slackSql) <= 1")
            ->count();

        $query = PostRequest::query();

        if ($filter !== 'all') {
            if ($filter === 'pending') {
                $query->whereIn('status', ['Pending', 'Pending Review']);
            } elseif ($filter === 'at_risk') {
                $query->whereIn('status', ['Pending', 'Pending Review', 'Under Review'])
                      ->whereRaw("($slackSql) < 0");
            } elseif ($filter === 'due_soon') {
                $query->whereIn('status', ['Pending', 'Pending Review', 'Under Review'])
                      ->whereRaw("($slackSql) >= 0 AND ($slackSql) <= 1");
            } else {
                $status_map = [
                    'review'   => 'Under Review',
                    'approved' => 'Approved',
                    'posted'   => 'Posted',
                    'rejected' => 'Rejected',
                ];
                if (isset($status_map[$filter])) {
                    $query->where('status', $status_map[$filter]);
                }
            }
        }

        if ($search !== '') {
            $cleanSearch = ltrim($search, '#');
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('requester', 'like', "%$search%")
                  ->orWhere('category', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%")
                  ->orWhere('platform', 'like', "%$search%")
                  ->orWhere('priority', 'like', "%$search%")
                  ->orWhere('request_id', 'like', "%$search%")
                  ->orWhere('id', 'like', "%$cleanSearch%");
            });
        }

        // Normalize legacy 'Urgent' priority to 'High'
        try {
            PostRequest::where('priority', 'Urgent')->update(['priority' => 'High']);
        } catch (\Throwable $e) {}

        if ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sort === 'priority') {
            $query->orderByRaw("CASE WHEN status IN ('Pending', 'Pending Review', 'Under Review') THEN 0 ELSE 1 END ASC")
                  ->orderByRaw("FIELD(priority, 'High', 'Medium', 'Low') ASC")
                  ->orderBy('created_at', 'desc');
        } elseif ($sort === 'newest') {
            $query->orderBy('created_at', 'desc');
        } else {
            // Default: Urgency / SLA Slack with "Actionable First"
            // Active requests (Pending / Under Review) come first (0), finalized (Approved / Posted / Rejected) sink to the back (1)
            $query->orderByRaw("CASE WHEN status IN ('Pending', 'Pending Review', 'Under Review') THEN 0 ELSE 1 END ASC")
                  ->orderByRaw("CASE WHEN status IN ('Pending', 'Pending Review', 'Under Review') THEN ($slackSql) ELSE 9999 END ASC")
                  ->orderByRaw("FIELD(priority, 'High', 'Medium', 'Low') ASC")
                  ->orderBy('created_at', 'desc');
        }

        $requests = $query->paginate(10)->withQueryString();
        $total    = $query->toBase()->getCountForPagination();

        return view('admin.requests', compact(
            'requests', 'total', 'search', 'filter', 'sort',
            'pending', 'review', 'approved', 'posted', 'rejected',
            'at_risk_count', 'due_soon_count'
        ));
    }

    // ── SHOW REQUEST DETAIL ──────────────────────────────────────
    public function show($id)
    {
        $req        = PostRequest::findOrFail($id);
        $comments   = RequestComment::where('request_id', $id)->orderBy('created_at', 'asc')->get();
        $activities = RequestActivity::where('request_id', $id)->orderBy('created_at', 'desc')->get();

        $request = $req;

        // Auto-mark as seen by admin when viewing request
        try {
            $req->update(['admin_seen' => true]);
            RequestComment::where('request_id', $id)->where('sender_role', 'requestor')->update(['admin_seen' => true]);
            if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'request_id')) {
                Notification::where('request_id', $id)->where('is_read', false)->update(['is_read' => true]);
            }
        } catch (\Throwable $e) {}

        return view('admin.request_info', compact('req', 'request', 'comments', 'activities'));
    }

    // ── UPDATE STATUS ────────────────────────────────────────────
    public function updateStatus(Request $request)
    {
        $id         = $request->input('request_id');
        $new_status = $request->input('status');
        $note       = trim($request->input('note', ''));

        $allowed = ['Pending Review', 'Under Review', 'Approved', 'Posted', 'Rejected'];
        if (!in_array($new_status, $allowed)) {
            return back()->with('error', 'Invalid status.');
        }

        $req = PostRequest::find($id);
        if (!$req) {
            return back()->with('error', 'Request not found.');
        }

        $old_status = $req->status;

        // Don't update if status hasn't changed
        if ($old_status === $new_status && !$note) {
            return back()->with('error', 'Status is already set to ' . $new_status . '.');
        }

        $req->update(['status' => $new_status]);

        if ($new_status === 'Approved' || $new_status === 'Posted') {
            \App\Models\AuditLog::record('post_request_approved');
        } elseif ($new_status === 'Rejected') {
            \App\Models\AuditLog::record('post_request_rejected');
        }

        RequestActivity::create([
            'request_id' => $id,
            'actor'      => session('admin_name', session('admin_email', 'Admin')),
            'action'     => "Status changed from \"$old_status\" to \"$new_status\"",
        ]);

        if ($note) {
            RequestActivity::create([
                'request_id' => $id,
                'actor'      => session('admin_name', session('admin_email', 'Admin')),
                'action'     => "Admin note: $note",
            ]);
        }

        $user = null;
        if (!empty($req->user_id)) {
            $user = User::find($req->user_id);
        }
        if (!$user && !empty($req->requester)) {
            $user = User::where('name', $req->requester)->first();
        }
        if ($user) {
            $notif_data = $this->getNotifData($new_status, $req->title, $note);
            Notification::create([
                'user_id' => $user->id,
                'title'   => $notif_data['title'],
                'message' => $notif_data['message'],
                'type'    => $notif_data['type'],
                'is_read' => false,
            ]);

            // Always send email on admin-initiated status change
            if ($user->email) {
                try {
                    $html = $this->getStatusEmailHtml($user->name, $req->title, $new_status, $note);
                    Mail::send([], [], function ($message) use ($user, $new_status, $html) {
                        $message->to($user->email, $user->name)
                            ->subject("NUPost: Your request has been $new_status")
                            ->html($html);
                    });
                    Log::info("[NUPost] Status email sent to {$user->email} — {$req->title} → {$new_status}");
                } catch (\Exception $e) {
                    Log::error('[NUPost] Status email failed: ' . $e->getMessage());
                }
            }
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Status updated to $new_status.",
                'status'  => $new_status,
            ]);
        }

        $redirect = $request->input('redirect_to');
        if ($redirect) {
            return redirect($redirect)->with('success', "Status updated to $new_status.");
        }

        return back()->with('success', "Status updated to $new_status.");
    }

    public function updateCaption(Request $request, $id)
    {
        $req = PostRequest::findOrFail($id);
        $caption = trim($request->input('caption', ''));

        $req->update(['caption' => $caption]);

        $adminEmail = session('admin_name', session('admin_email', 'Admin'));
        RequestActivity::create([
            'request_id' => $id,
            'actor'      => $adminEmail,
            'action'     => "Admin updated the caption draft",
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Caption draft updated successfully.',
                'caption' => $caption,
            ]);
        }

        return back()->with('success', 'Caption draft updated successfully.');
    }

    // ── AI CAPTION GENERATION (GEMINI) ───────────────────────────
    public function generateCaption(Request $httpRequest, $id)
    {
        $postRequest = PostRequest::findOrFail($id);

        $userPrompt  = $httpRequest->input('prompt', '');
        $title       = $httpRequest->input('title', $postRequest->title);
        $description = $httpRequest->input('description', $postRequest->description ?? '');

        $apiKey = \App\Models\Setting::get('gemini_api_key', config('services.gemini.key') ?: env('GEMINI_API_KEY'));
        if (!$apiKey) {
            return response()->json(['error' => 'Gemini API key not configured. Add GEMINI_API_KEY in Settings or .env'], 500);
        }

        $basePrompt = "Write a compelling social media caption for a post with the following details:\n"
            . "Title: {$title}\n"
            . ($description ? "Description: {$description}\n" : '')
            . ($postRequest->category ? "Category: {$postRequest->category}\n" : '')
            . "Make it engaging, concise (2-4 sentences), and suitable for social media platforms like Facebook or Instagram.\n"
            . "Include 3-5 relevant hashtags at the end.\n";

        if ($userPrompt) {
            $basePrompt .= "Additional instruction: {$userPrompt}\n";
        }

        $basePrompt .= "Return ONLY the caption text with hashtags. No explanations, no quotes.";

        try {
            $response = Http::withoutVerifying()->post(
                "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent?key={$apiKey}",
                [
                    'contents' => [
                        ['parts' => [['text' => $basePrompt]]]
                    ],
                    'generationConfig' => [
                        'temperature'     => 0.9,
                        'maxOutputTokens' => 300,
                    ]
                ]
            );

            if ($response->successful()) {
                $data    = $response->json();
                $caption = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                if ($caption) {
                    return response()->json(['caption' => trim($caption)]);
                }
            }

            return response()->json(['error' => 'Gemini returned no caption. Try again.'], 500);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to connect to Gemini: ' . $e->getMessage()], 500);
        }
    }

    // ── SAVE AI CAPTION ──────────────────────────────────────────
    public function saveCaption(Request $httpRequest, $id)
    {
        $postRequest = PostRequest::findOrFail($id);
        $filename    = $httpRequest->input('filename', 'default');
        $caption     = $httpRequest->input('caption', '');

        $captions              = json_decode($postRequest->ai_captions ?? '{}', true) ?: [];
        $captions[$filename]   = $caption;
        $postRequest->ai_captions = json_encode($captions);
        $postRequest->save();

        return response()->json(['success' => true]);
    }

    // ── DOWNLOAD MEDIA FILE ──────────────────────────────────────
    public function downloadFile(Request $httpRequest, $id, $filename)
    {
        // Sanitize filename — prevent path traversal
        $filename = basename($filename);

        // Verify the file belongs to this request
        $postRequest = PostRequest::findOrFail($id);
        $mediaFiles  = array_map('trim', explode(',', $postRequest->media_file ?? ''));
        if (!in_array($filename, $mediaFiles)) {
            abort(404, 'File not found on this request.');
        }

        $path = public_path('uploads/' . $filename);
        if (!file_exists($path)) {
            $altPath = base_path('public_html/uploads/' . $filename);
            if (file_exists($altPath)) {
                $path = $altPath;
            } else {
                abort(404, 'File not found on server.');
            }
        }

        return response()->download($path, $filename);
    }

    // ── COMMENTS ─────────────────────────────────────────────────
    public function postComment(Request $request)
    {
        $id      = $request->input('request_id');
        $message = trim($request->input('message', ''));

        if (!$message) {
            return response()->json(['success' => false, 'message' => 'Message cannot be empty.']);
        }

        $req = PostRequest::find($id);
        if (!$req) {
            return response()->json(['success' => false, 'message' => 'Request not found.']);
        }

        // Deduplication guard: duplicate comment submitted within 5 seconds
        $duplicate = RequestComment::where('request_id', $id)
            ->where('sender_role', 'admin')
            ->where('message', $message)
            ->where('created_at', '>=', now()->subSeconds(5))
            ->exists();

        if ($duplicate) {
            return response()->json(['success' => true, 'message' => 'Comment posted.']);
        }

        RequestComment::create([
            'request_id'  => $id,
            'sender_role' => 'admin',
            'sender_name' => session('admin_name', session('admin_email', 'Admin')),
            'message'     => $message,
        ]);

        $user = ($req->user_id ? User::find($req->user_id) : null) ?: User::where('name', $req->requester)->first();
        if ($user) {
            $notifData = [
                'user_id' => $user->id,
                'title'   => 'New Comment from Admin',
                'message' => "Admin commented on your request \"{$req->title}\": $message",
                'type'    => 'comment',
                'is_read' => false,
            ];
            if (\Illuminate\Support\Facades\Schema::hasColumn('notifications', 'request_id')) {
                $notifData['request_id'] = $id;
            }
            Notification::create($notifData);

            // Send email notification for admin comment
            if ($user->email) {
                try {
                    $html = $this->getCommentEmailHtml($user->name, $req->title, $message);
                    Mail::send([], [], function ($msg) use ($user, $req, $html) {
                        $msg->to($user->email, $user->name)
                            ->subject("NUPost: New comment on \"{$req->title}\"")
                            ->html($html);
                    });
                    Log::info("[NUPost] Comment email sent to {$user->email} — {$req->title}");
                } catch (\Exception $e) {
                    Log::error('[NUPost] Comment email failed: ' . $e->getMessage());
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'Comment posted.']);
    }

    public function getComments($id)
    {
        $comments = RequestComment::where('request_id', $id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($c) {
                return [
                    'id'          => $c->id,
                    'sender_role' => $c->sender_role,
                    'sender_name' => $c->sender_name,
                    'message'     => $c->message,
                    'created_at'  => $c->created_at->format('M j, Y g:i A'),
                ];
            });

        return response()->json(['comments' => $comments]);
    }

    // ── CALENDAR ─────────────────────────────────────────────────
    public function calendar(Request $request)
    {
        return view('admin.calendar');
    }

    // ── PRIVATE HELPERS ──────────────────────────────────────────
    private function getNotifData(string $status, string $title, string $note = ''): array
    {
        $note_suffix = $note ? " Admin note: $note" : '';
        return match($status) {
            'Under Review' => [
                'title'   => 'Request Under Review',
                'message' => "Your request \"$title\" is now being reviewed by our team.$note_suffix",
                'type'    => 'review',
            ],
            'Approved' => [
                'title'   => 'Request Approved! 🎉',
                'message' => "Great news! Your request \"$title\" has been approved and is ready for posting.$note_suffix",
                'type'    => 'approved',
            ],
            'Posted' => [
                'title'   => 'Request Posted! 🚀',
                'message' => "Your request \"$title\" has been successfully published on the platform.$note_suffix",
                'type'    => 'posted',
            ],
            'Rejected' => [
                'title'   => 'Request Rejected',
                'message' => "Unfortunately, your request \"$title\" was not approved. You may submit a revised one.$note_suffix",
                'type'    => 'rejected',
            ],
            default => [
                'title'   => 'Request Status Updated',
                'message' => "Your request \"$title\" status has been updated to $status.$note_suffix",
                'type'    => 'review',
            ],
        };
    }

    private function getStatusEmailHtml(string $name, string $title, string $status, string $note = ''): string
    {
        $color = match($status) {
            'Approved'     => '#10b981',
            'Posted'       => '#8b5cf6',
            'Rejected'     => '#ef4444',
            'Under Review' => '#f59e0b',
            default        => '#6b7280',
        };

        $note_section = $note
            ? "<div style='background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:12px 16px;margin-top:16px;font-size:13px;color:#92400e;'><strong>Admin Note:</strong> $note</div>"
            : '';

        return "<!DOCTYPE html><html><body style='margin:0;padding:0;background:#f5f6fa;font-family:Arial,sans-serif;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background:#f5f6fa;padding:40px 0;'>
        <tr><td align='center'>
        <table width='520' cellpadding='0' cellspacing='0' style='background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);'>
        <tr><td style='background:#002366;padding:28px 40px;text-align:center;'>
            <div style='font-size:22px;font-weight:700;color:white;'>NUPost</div>
            <div style='font-size:13px;color:rgba(255,255,255,0.7);margin-top:4px;'>NU Lipa Social Media Request System</div>
        </td></tr>
        <tr><td style='padding:32px 40px;'>
            <p style='font-size:14px;color:#374151;margin:0 0 16px;'>Hi <strong>$name</strong>,</p>
            <p style='font-size:14px;color:#374151;margin:0 0 20px;'>Your request has been updated:</p>
            <div style='background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;margin-bottom:16px;'>
                <div style='font-size:13px;color:#6b7280;margin-bottom:4px;'>Request</div>
                <div style='font-size:15px;font-weight:600;color:#111827;'>$title</div>
            </div>
            <div style='text-align:center;margin:20px 0;'>
                <span style='display:inline-block;background:$color;color:white;padding:10px 28px;border-radius:20px;font-size:15px;font-weight:700;'>$status</span>
            </div>
            $note_section
        </td></tr>
        <tr><td style='background:#f5f6fa;padding:20px 40px;text-align:center;border-top:1px solid #e5e7eb;'>
            <p style='font-size:12px;color:#9ca3af;margin:0;'>© " . date('Y') . " NUPost — NU Lipa</p>
        </td></tr>
        </table></td></tr></table></body></html>";
    }

    private function getCommentEmailHtml(string $name, string $title, string $comment): string
    {
        $escapedComment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');

        return "<!DOCTYPE html><html><body style='margin:0;padding:0;background:#f5f6fa;font-family:Arial,sans-serif;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background:#f5f6fa;padding:40px 0;'>
        <tr><td align='center'>
        <table width='520' cellpadding='0' cellspacing='0' style='background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);'>
        <tr><td style='background:#002366;padding:28px 40px;text-align:center;'>
            <div style='font-size:22px;font-weight:700;color:white;'>NUPost</div>
            <div style='font-size:13px;color:rgba(255,255,255,0.7);margin-top:4px;'>NU Lipa Social Media Request System</div>
        </td></tr>
        <tr><td style='padding:32px 40px;'>
            <p style='font-size:14px;color:#374151;margin:0 0 16px;'>Hi <strong>$name</strong>,</p>
            <p style='font-size:14px;color:#374151;margin:0 0 20px;'>The admin has posted a new comment on your request:</p>
            <div style='background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;margin-bottom:16px;'>
                <div style='font-size:13px;color:#6b7280;margin-bottom:4px;'>Request</div>
                <div style='font-size:15px;font-weight:600;color:#111827;'>$title</div>
            </div>
            <div style='background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px 20px;margin-bottom:16px;'>
                <div style='font-size:11px;font-weight:700;color:#1e40af;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;'>💬 Admin Comment</div>
                <div style='font-size:14px;color:#1e3a5f;line-height:1.6;'>$escapedComment</div>
            </div>
            <p style='font-size:13px;color:#6b7280;margin:0;'>Log in to NUPost to view and reply to this comment.</p>
        </td></tr>
        <tr><td style='background:#f5f6fa;padding:20px 40px;text-align:center;border-top:1px solid #e5e7eb;'>
            <p style='font-size:12px;color:#9ca3af;margin:0;'>© " . date('Y') . " NUPost — NU Lipa</p>
        </td></tr>
        </table></td></tr></table></body></html>";
    }
}