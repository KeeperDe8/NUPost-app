<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $category = trim((string) $request->query('category', 'all'));

        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        // Filter by category
        if ($category !== 'all' && $category !== '') {
            if ($category === 'auth') {
                $query->whereIn('action_type', ['user_login', 'admin_login', 'user_logout', 'admin_logout']);
            } elseif ($category === 'requests') {
                $query->whereIn('action_type', ['post_request_created', 'post_request_edited', 'post_request_approved', 'post_request_rejected']);
            } elseif ($category === 'system') {
                $query->whereIn('action_type', ['api_key_updated', 'settings_updated']);
            }
        }

        // Filter by search
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('action_type', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Notice: IP address is deliberately omitted from display
        $logs = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => AuditLog::count(),
            'logins' => AuditLog::whereIn('action_type', ['user_login', 'admin_login'])->count(),
            'approvals' => AuditLog::where('action_type', 'post_request_approved')->count(),
            'rejections' => AuditLog::where('action_type', 'post_request_rejected')->count(),
        ];

        return view('admin.audit_logs', compact('logs', 'stats', 'search', 'category'));
    }
}
