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

        $userFilter = function ($q) use ($user_name, $user_id) {
            if (\Illuminate\Support\Facades\Schema::hasColumn('post_requests', 'user_id') && $user_id) {
                $q->where('user_id', $user_id)->orWhere('requester', $user_name);
            } else {
                $q->where('requester', $user_name);
            }
        };

        $pending  = PostRequest::where($userFilter)->whereIn('status', ['Pending', 'Pending Review'])->count();
        $review   = PostRequest::where($userFilter)->where('status', 'Under Review')->count();
        $approved = PostRequest::where($userFilter)->where('status', 'Approved')->count();
        $posted   = PostRequest::where($userFilter)->where('status', 'Posted')->count();

        $recent = PostRequest::where($userFilter)
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