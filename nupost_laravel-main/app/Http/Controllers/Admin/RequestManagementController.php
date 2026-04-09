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

class RequestManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $filter = $request->input('filter', 'all');
        $sort   = $request->input('sort', 'newest');

        // Stat counts
        $pending  = PostRequest::where('status', 'Pending Review')->count();
        $review   = PostRequest::where('status', 'Under Review')->count();
        $approved = PostRequest::where('status', 'Approved')->count();
        $posted   = PostRequest::where('status', 'Posted')->count();
        $rejected = PostRequest::where('status', 'Rejected')->count();

        $query = PostRequest::query();

        if ($filter !== 'all') {
            $status_map = [
                'pending'  => 'Pending Review',
                'review'   => 'Under Review',
                'approved' => 'Approved',
                'posted'   => 'Posted',
                'rejected' => 'Rejected',
            ];
            if (isset($status_map[$filter])) {
                $query->where('status', $status_map[$filter]);
            }
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('requester', 'like', "%$search%")
                  ->orWhere('category', 'like', "%$search%");
            });
        }

        if ($sort === 'oldest') {
            $query->orderBy('created_at', 'asc');
        } elseif ($sort === 'priority') {
            $query->orderByRaw("FIELD(priority, 'Urgent', 'High', 'Medium', 'Low')");
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $requests = $query->get();
        $total    = $requests->count();

        return view('admin.requests', compact(
            'requests', 'total', 'search', 'filter', 'sort',
            'pending', 'review', 'approved', 'posted', 'rejected'
        ));
    }

    public function show($id)
    {
        $req        = PostRequest::findOrFail($id);
        $comments   = RequestComment::where('request_id', $id)->orderBy('created_at', 'asc')->get();
        $activities = RequestActivity::where('request_id', $id)->orderBy('created_at', 'desc')->get();

        return view('admin.request_info', compact('req', 'comments', 'activities'));
    }

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
        $req->update(['status' => $new_status]);

        // Log activity
        RequestActivity::create([
            'request_id' => $id,
            'actor'      => 'admin@nupost.com',
            'action'     => "Status changed from \"$old_status\" to \"$new_status\"",
        ]);

        if ($note) {
            RequestActivity::create([
                'request_id' => $id,
                'actor'      => 'admin@nupost.com',
                'action'     => "Internal note: $note",
            ]);
        }

        // Send notification to requestor
        $user = User::where('name', $req->requester)->first();
        if ($user) {
            $notif_data = $this->getNotifData($new_status, $req->title, $note);
            Notification::create([
                'user_id' => $user->id,
                'title'   => $notif_data['title'],
                'message' => $notif_data['message'],
                'type'    => $notif_data['type'],
                'is_read' => false,
            ]);

            // Email notification
            if ($user->email_notif && $user->status_updates) {
                try {
                    $html = $this->getStatusEmailHtml($user->name, $req->title, $new_status, $note);
                    Mail::send([], [], function ($message) use ($user, $new_status, $html) {
                        $message->to($user->email, $user->name)
                            ->subject("NUPost: Your request has been $new_status")
                            ->html($html);
                    });
                } catch (\Exception $e) {
                    \Log::error('[NUPost] Status email failed: ' . $e->getMessage());
                }
            }
        }

        return back()->with('success', "Status updated to $new_status.");
    }

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

        RequestComment::create([
            'request_id'  => $id,
            'sender_role' => 'admin',
            'sender_name' => 'admin@nupost.com',
            'message'     => $message,
        ]);

        // Notify requestor
        $user = User::where('name', $req->requester)->first();
        if ($user) {
            Notification::create([
                'user_id' => $user->id,
                'title'   => 'New Comment from Admin',
                'message' => "Admin commented on your request \"{$req->title}\": $message",
                'type'    => 'comment',
                'is_read' => false,
            ]);
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
}