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
        $pending  = PostRequest::whereIn('status', ['Pending', 'Pending Review'])->count();
        $review   = PostRequest::where('status', 'Under Review')->count();
        $approved = PostRequest::where('status', 'Approved')->count();
        $posted   = PostRequest::where('status', 'Posted')->count();
        $rejected = PostRequest::where('status', 'Rejected')->count();
        $users    = User::count();

        // Calculate SLA urgency
        $slackSql = "DATEDIFF(COALESCE(preferred_date, DATE_ADD(CURRENT_DATE, INTERVAL 3 DAY)), CURRENT_DATE) - (CASE category WHEN 'Event Documentation' THEN 30 WHEN 'New Campaign / Creative Concept' THEN 10 WHEN 'Multiple Collaterals / Tarpaulins' THEN 5 WHEN 'Standard PubMat' THEN 3 WHEN 'Template-Based PubMat' THEN 2 ELSE 1 END)";
        $at_risk = PostRequest::whereIn('status', ['Pending', 'Pending Review', 'Under Review'])
            ->whereRaw("($slackSql) < 0")
            ->count();
        $due_soon = PostRequest::whereIn('status', ['Pending', 'Pending Review', 'Under Review'])
            ->whereRaw("($slackSql) >= 0 AND ($slackSql) <= 1")
            ->count();

        $recent = PostRequest::orderBy('created_at', 'desc')->limit(10)->get();

        // Fetch Facebook / Meta Graph API data
        $fbController = new FacebookAnalyticsController();
        $fb = $fbController->getData();

        return view('admin.dashboard', compact(
            'total', 'pending', 'review', 'approved', 'posted', 'rejected', 'users', 'recent', 'fb', 'at_risk', 'due_soon'
        ));
    }
}