<?php

namespace App\Http\Controllers\Requestor;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    private function resolveUserId(): ?int
    {
        $id = session('user_id') ?: auth()->id();
        if (!$id && session('name')) {
            $id = User::where('name', session('name'))->value('id');
        }
        return $id ? (int) $id : null;
    }

    /**
     * Display the notifications page with pagination
     */
    public function index()
    {
        $user_id = $this->resolveUserId();

        $notifications = Notification::where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $unread_count = $user_id ? Notification::where('user_id', $user_id)
            ->where('is_read', false)
            ->count() : 0;

        return view('requestor.notifications', compact('notifications', 'unread_count'));
    }

    /**
     * AJAX: Fetch notifications for the bell icon dropdown
     */
    public function fetch()
    {
        $user_id = $this->resolveUserId();

        if (!$user_id) {
            return response()->json([
                'notifications' => [],
                'unread_count'  => 0,
            ]);
        }

        $notifications = Notification::where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $unread_count = Notification::where('user_id', $user_id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unread_count,
        ]);
    }

    /**
     * AJAX: Mark notification as read
     */
    public function markRead(Request $request)
    {
        $user_id = $this->resolveUserId();
        if (!$user_id) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $id = $request->input('id');

        if ($request->input('all')) {
            Notification::where('user_id', $user_id)->update(['is_read' => true]);
        } elseif ($id) {
            Notification::where('id', $id)
                ->where('user_id', $user_id)
                ->update(['is_read' => true]);
        }

        return response()->json(['success' => true]);
    }
}