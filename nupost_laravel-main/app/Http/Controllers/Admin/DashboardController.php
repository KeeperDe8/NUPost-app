<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostRequest;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $total    = PostRequest::count();
        $pending  = PostRequest::where('status', 'Pending Review')->count();
        $review   = PostRequest::where('status', 'Under Review')->count();
        $approved = PostRequest::where('status', 'Approved')->count();
        $posted   = PostRequest::where('status', 'Posted')->count();
        $rejected = PostRequest::where('status', 'Rejected')->count();
        $users    = User::count();

        $recent = PostRequest::orderBy('created_at', 'desc')->limit(10)->get();

        return view('admin.dashboard', compact(
            'total', 'pending', 'review', 'approved', 'posted', 'rejected', 'users', 'recent'
        ));
    }
}