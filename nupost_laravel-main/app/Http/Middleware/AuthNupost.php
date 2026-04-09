<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthNupost
{
    public function handle(Request $request, Closure $next, string $role = 'requestor')
    {
        if (!session('role')) {
            return redirect()->route('login');
        }

        if (session('role') !== $role) {
            if (session('role') === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('requestor.dashboard');
        }

        return $next($request);
    }
}