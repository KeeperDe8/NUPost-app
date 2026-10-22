<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthNupost
{
    public function handle(Request $request, Closure $next, string $role = 'requestor')
    {
        if (!session('role')) {
            return redirect()->route('login')
                ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
        }

        if (session('role') !== $role) {
            if (session('role') === 'admin') {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('requestor.dashboard');
        }

        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');

        return $response;
    }
}