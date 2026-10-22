<?php

namespace App\Http\Controllers\Kaye;

use App\Http\Controllers\Controller;
use App\Models\KayeToken;
use App\Models\PostRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function login($tokenStr)
    {
        $token = KayeToken::where('token', $tokenStr)->first();

        if (!$token || !$token->isValid()) {
            return redirect()->route('kaye.login.expired', ['reason' => 'expired']);
        }

        session([
            'role' => 'kaye',
            'kaye_token' => $tokenStr,
            'expires_at' => $token->expires_at,
        ]);

        return redirect()->route('kaye.dashboard');
    }

    public function loginExpired(Request $request)
    {
        $reason = $request->query('reason', 'expired');
        return view('kaye.login', compact('reason'));
    }

    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $filter = $request->input('filter', 'all');

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

        $requests = $query->orderBy('created_at', 'desc')->get();
        $total = $requests->count();

        $pending  = PostRequest::where('status', 'Pending Review')->count();
        $review   = PostRequest::where('status', 'Under Review')->count();
        $approved = PostRequest::where('status', 'Approved')->count();
        $posted   = PostRequest::where('status', 'Posted')->count();
        $rejected = PostRequest::where('status', 'Rejected')->count();

        // Get requests for calendar view
        $calendarRequests = PostRequest::whereNotNull('preferred_date')
            ->orWhereNotNull('created_at')
            ->get(['id', 'title', 'status', 'preferred_date', 'created_at']);

        return view('kaye.dashboard', compact(
            'requests', 'total', 'search', 'filter',
            'pending', 'review', 'approved', 'posted', 'rejected', 'calendarRequests'
        ));
    }
}
