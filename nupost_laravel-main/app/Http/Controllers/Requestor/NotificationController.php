<?php

namespace App\Http\Controllers\Requestor;

use App\Http\Controllers\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $user_id = session('user_id');

        Notification::where('user_id', $user_id)->update(['is_read' => true]);

        $notifications = Notification::where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $unread_count = 0;

        return view('requestor.notifications', compact('notifications', 'unread_count'));
    }
}