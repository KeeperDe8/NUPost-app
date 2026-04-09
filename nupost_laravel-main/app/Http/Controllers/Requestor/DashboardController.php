<?php

namespace App\Http\Controllers\Requestor;

use App\Http\Controllers\Controller;
use App\Models\PostRequest;
use App\Models\Notification;

class DashboardController extends Controller
{
    public function index()
    {
        $user_name    = session('name');
        $user_id      = session('user_id');

        $pending  = PostRequest::where('requester', $user_name)->where('status', 'Pending Review')->count();
        $review   = PostRequest::where('requester', $user_name)->where('status', 'Under Review')->count();
        $approved = PostRequest::where('requester', $user_name)->where('status', 'Approved')->count();
        $posted   = PostRequest::where('requester', $user_name)->where('status', 'Posted')->count();

        $recent = PostRequest::where('requester', $user_name)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $unread_count = Notification::where('user_id', $user_id)
            ->where('is_read', false)
            ->count();

        return view('requestor.dashboard', compact(
            'pending', 'review', 'approved', 'posted', 'recent', 'unread_count'
        ));
    }
}