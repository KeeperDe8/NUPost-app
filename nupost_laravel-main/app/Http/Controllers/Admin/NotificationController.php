<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostRequest;
use App\Models\RequestComment;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class NotificationController extends Controller
{
    private function getAdminId(): ?int
    {
        $adminId = session('admin_id');
        if (!$adminId && session('admin_email')) {
            $adminId = User::where('email', session('admin_email'))->value('id');
        }
        if (!$adminId && auth()->check() && (auth()->user()->role ?? '') === 'admin') {
            $adminId = auth()->id();
        }
        return $adminId ? (int) $adminId : null;
    }

    // ── Full notifications page ──────────────────────────────────
    public function index()
    {
        $notifications = $this->collectAllAdminNotifications(50);
        return view('admin.notifications', compact('notifications'));
    }

    // ── AJAX: fetch admin notifications for bell dropdown ─────────
    public function fetch()
    {
        $all = $this->collectAllAdminNotifications(50);
        $unread_count = $all->where('is_read', false)->count();

        return response()->json([
            'notifications' => $all->take(20)->values(),
            'unread_count'  => $unread_count,
        ]);
    }

    private function collectAllAdminNotifications(int $limit = 50)
    {
        $adminId   = $this->getAdminId();
        $adminIds  = User::where('role', 'admin')->pluck('id')->toArray();
        if ($adminId && !in_array($adminId, $adminIds)) {
            $adminIds[] = $adminId;
        }

        $hasReqCol = \Illuminate\Support\Facades\Schema::hasColumn('notifications', 'request_id');

        $notifications = collect();
        $seenRequests  = [];
        $seenComments  = [];

        // 1. All notifications targeted to admin or global system alerts
        $dbNotifs = Notification::where(function ($q) use ($adminId, $adminIds) {
                if ($adminId) {
                    $q->where('user_id', $adminId)
                      ->orWhereNull('user_id')
                      ->orWhere('user_id', 0);
                } else {
                    $q->whereIn('user_id', $adminIds)
                      ->orWhereNull('user_id')
                      ->orWhere('user_id', 0);
                }
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        foreach ($dbNotifs as $n) {
            $reqId = $hasReqCol ? $n->request_id : null;
            if ($reqId) {
                $seenRequests[$reqId] = true;
                if ($n->type === 'comment' || $n->type === 'message') {
                    $seenComments[$reqId] = true;
                }
            }
            $notifications->push([
                'id'         => 'notif_' . $n->id,
                'title'      => $n->title,
                'message'    => $n->message,
                'type'       => $n->type ?: 'system',
                'is_read'    => (bool) $n->is_read,
                'request_id' => $reqId,
                'created_at' => $n->created_at,
            ]);
        }

        // 2. Any recent comments from requestors
        $new_comments = RequestComment::where('sender_role', 'requestor')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        foreach ($new_comments as $c) {
            $isUnread = !($c->admin_seen ?? false);
            if (!empty($seenComments[$c->request_id]) && !$isUnread) {
                continue;
            }
            $alreadyExists = $notifications->contains(function ($item) use ($c) {
                return $item['type'] === 'comment' && ($item['request_id'] ?? null) == $c->request_id && (bool)$item['is_read'] === (bool)($c->admin_seen ?? false);
            });
            if ($alreadyExists) {
                continue;
            }

            $notifications->push([
                'id'         => 'cmt_' . $c->id,
                'title'      => 'Reply from ' . $c->sender_name,
                'message'    => Str::limit($c->message, 80),
                'type'       => 'comment',
                'is_read'    => !$isUnread,
                'request_id' => $c->request_id,
                'created_at' => $c->created_at,
            ]);
            $seenComments[$c->request_id] = true;
        }

        // 3. Any active requests needing review
        $new_requests = PostRequest::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        foreach ($new_requests as $r) {
            $isUnread = !($r->admin_seen ?? false);
            if (!empty($seenRequests[$r->id]) && !$isUnread) {
                continue;
            }
            $alreadyExists = $notifications->contains(function ($item) use ($r) {
                return in_array($item['type'], ['new_request', 'status_update']) && ($item['request_id'] ?? null) == $r->id;
            });
            if ($alreadyExists) {
                continue;
            }

            $notifications->push([
                'id'         => 'req_' . $r->id,
                'title'      => 'New Request: ' . $r->title,
                'message'    => $r->requester . ' submitted a new post request (' . ucfirst($r->priority ?? 'Low') . ' priority).',
                'type'       => 'new_request',
                'is_read'    => !$isUnread,
                'request_id' => $r->id,
                'created_at' => $r->created_at,
            ]);
            $seenRequests[$r->id] = true;
        }

        // 4. Automated SLA Due Soon & Overdue Reminders for Active Requests
        $activeRequests = PostRequest::whereIn('status', ['Pending', 'Pending Review', 'Under Review'])->get();
        foreach ($activeRequests as $ar) {
            $slack = $ar->slack_days;
            $isUnread = !($ar->admin_seen ?? false);

            if ($slack < 0) {
                // Overdue
                $notifications->push([
                    'id'         => 'sla_overdue_' . $ar->id,
                    'title'      => 'Overdue SLA Alert: ' . $ar->title,
                    'message'    => '"' . $ar->title . '" is ' . abs($slack) . ' day' . (abs($slack) > 1 ? 's' : '') . ' overdue for production! Preferred post date is ' . ($ar->preferred_date ? Carbon::parse($ar->preferred_date)->format('M j') : 'N/A') . '.',
                    'type'       => 'overdue',
                    'is_read'    => !$isUnread,
                    'request_id' => $ar->id,
                    'created_at' => $ar->created_at,
                ]);
            } elseif ($slack >= 0 && $slack <= 1) {
                // Due Soon
                $notifications->push([
                    'id'         => 'sla_due_' . $ar->id,
                    'title'      => 'Due Soon: ' . $ar->title,
                    'message'    => 'Production for "' . $ar->title . '" must start ' . ($slack === 0 ? 'today' : 'within 1 day') . ' to meet SLA lead time.',
                    'type'       => 'due_soon',
                    'is_read'    => !$isUnread,
                    'request_id' => $ar->id,
                    'created_at' => $ar->created_at,
                ]);
            }
        }

        return $notifications->sortByDesc('created_at')->values();
    }

    // ── AJAX: mark as read ────────────────────────────────────────
    public function markRead(Request $request)
    {
        $hasReqCol = \Illuminate\Support\Facades\Schema::hasColumn('notifications', 'request_id');
        $hasPostReqSeen = \Illuminate\Support\Facades\Schema::hasColumn('post_requests', 'admin_seen');
        $hasCommentSeen = \Illuminate\Support\Facades\Schema::hasColumn('request_comments', 'admin_seen');

        if ($request->input('all')) {
            // Mark all requests as seen with NULL safety if column exists
            if ($hasPostReqSeen) {
                try {
                    PostRequest::where(function ($q) {
                        $q->where('admin_seen', false)->orWhereNull('admin_seen');
                    })->update(['admin_seen' => true]);
                } catch (\Throwable $e) {}
            }

            // Mark all requestor comments as seen with NULL safety if column exists
            if ($hasCommentSeen) {
                try {
                    RequestComment::where('sender_role', 'requestor')
                        ->where(function ($q) {
                            $q->where('admin_seen', false)->orWhereNull('admin_seen');
                        })->update(['admin_seen' => true]);
                } catch (\Throwable $e) {}
            }

            // Mark all notifications as read with NULL safety
            Notification::where(function ($q) {
                $q->where('is_read', false)->orWhereNull('is_read');
            })->update(['is_read' => true]);

        } elseif ($id = $request->input('id')) {
            if (str_starts_with($id, 'req_') || str_starts_with($id, 'sla_overdue_') || str_starts_with($id, 'sla_due_')) {
                $reqId = preg_replace('/^(req_|sla_overdue_|sla_due_)/', '', $id);
                if ($hasPostReqSeen) {
                    try { PostRequest::where('id', $reqId)->update(['admin_seen' => true]); } catch (\Throwable $e) {}
                }
                if ($hasReqCol) {
                    Notification::where('request_id', $reqId)->update(['is_read' => true]);
                }
            } elseif (str_starts_with($id, 'cmt_')) {
                $cmtId = str_replace('cmt_', '', $id);
                $comment = RequestComment::find($cmtId);
                if ($comment) {
                    if ($hasCommentSeen) {
                        try { $comment->update(['admin_seen' => true]); } catch (\Throwable $e) {}
                    }
                    if ($hasReqCol) {
                        Notification::where('request_id', $comment->request_id)->update(['is_read' => true]);
                    }
                }
            } elseif (str_starts_with($id, 'notif_')) {
                $notifId = str_replace('notif_', '', $id);
                $notif = Notification::find($notifId);
                if ($notif) {
                    $notif->update(['is_read' => true]);
                    $reqId = $hasReqCol ? $notif->request_id : null;
                    if ($reqId) {
                        if ($hasPostReqSeen) {
                            try { PostRequest::where('id', $reqId)->update(['admin_seen' => true]); } catch (\Throwable $e) {}
                        }
                        if ($hasCommentSeen) {
                            try { RequestComment::where('request_id', $reqId)->where('sender_role', 'requestor')->update(['admin_seen' => true]); } catch (\Throwable $e) {}
                        }
                    }
                }
            } else {
                $notif = Notification::find($id);
                if ($notif) {
                    $notif->update(['is_read' => true]);
                    $reqId = $hasReqCol ? $notif->request_id : null;
                    if ($reqId) {
                        if ($hasPostReqSeen) {
                            try { PostRequest::where('id', $reqId)->update(['admin_seen' => true]); } catch (\Throwable $e) {}
                        }
                        if ($hasCommentSeen) {
                            try { RequestComment::where('request_id', $reqId)->where('sender_role', 'requestor')->update(['admin_seen' => true]); } catch (\Throwable $e) {}
                        }
                    }
                }
            }
        }

        return response()->json(['success' => true]);
    }
}