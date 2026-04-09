<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LoginAttempt;
use App\Models\RememberedDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function index(Request $request)
    {
        $success = session()->pull('reg_success', null);
        return view('auth.login', compact('success'));
    }

    public function store(Request $request)
    {
        // CSRF is handled by Laravel automatically

        $email    = trim($request->input('email', ''));
        $password = trim($request->input('password', ''));

        // Admin login
        if ($email === 'admin@nupost.com' && $password === 'admin123') {
            session()->regenerate();
            session(['role' => 'admin', 'admin_email' => $email]);
            return redirect()->route('admin.dashboard');
        }

        // Rate limit check
        if (LoginAttempt::isRateLimited($email)) {
            return back()->withInput()->with('error', 'Too many failed attempts. Please wait 15 minutes.');
        }

        // Requestor login
        $user = User::where('email', $email)->first();

        if ($user && (Hash::check($password, $user->password) || $user->password === $password)) {
            if (!$user->is_verified) {
                LoginAttempt::create(['email' => $email, 'ip_address' => $request->ip(), 'success' => false, 'attempted_at' => now()]);
                return back()->withInput()->with('error', 'Please verify your email first.');
            }

            LoginAttempt::create(['email' => $email, 'ip_address' => $request->ip(), 'success' => true, 'attempted_at' => now()]);
            session()->regenerate();
            session([
                'role'    => 'requestor',
                'user_id' => $user->id,
                'name'    => $user->name,
            ]);

            // Remember Me
            if ($request->has('remember_me')) {
                $token      = bin2hex(random_bytes(32));
                $expires_at = now()->addDays(7);
                RememberedDevice::where('user_id', $user->id)->delete();
                RememberedDevice::create(['user_id' => $user->id, 'token' => $token, 'expires_at' => $expires_at]);
                cookie()->queue('remember_token', $token, 60 * 24 * 7, '/', null, false, true);
            }

            return redirect()->route('requestor.dashboard');
        }

        LoginAttempt::create(['email' => $email, 'ip_address' => $request->ip(), 'success' => false, 'attempted_at' => now()]);
        $remaining = max(0, 5 - LoginAttempt::countRecent($email));
        return back()->withInput()->with('error', "Invalid email or password. {$remaining} attempt(s) remaining.");
    }

    public function destroy(Request $request)
    {
        if ($request->cookie('remember_token')) {
            RememberedDevice::where('token', $request->cookie('remember_token'))->delete();
            cookie()->queue(cookie()->forget('remember_token'));
        }
        session()->flush();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('login');
    }
}