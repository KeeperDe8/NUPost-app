<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class GuestNupost
{
    public function handle(Request $request, Closure $next)
    {
        if (session('role')) {
            if (session('role') === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('requestor.dashboard');
        }
        return $next($request);
    }
}