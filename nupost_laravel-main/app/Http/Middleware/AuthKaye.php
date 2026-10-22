<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\KayeToken;

class AuthKaye
{
    public function handle(Request $request, Closure $next)
    {
        if (session('role') !== 'kaye' || !session('kaye_token')) {
            return redirect()->route('kaye.login.expired', ['reason' => 'missing'])
                ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
        }

        $tokenStr = session('kaye_token');
        $token = KayeToken::where('token', $tokenStr)->first();

        if (!$token || !$token->isValid()) {
            session()->forget(['role', 'kaye_token', 'expires_at']);
            return redirect()->route('kaye.login.expired', ['reason' => 'expired'])
                ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
        }

        $response = $next($request);

        $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');

        return $response;
    }
}
