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



        // Rate limit check
        if (LoginAttempt::isRateLimited($email)) {
            return back()->withInput()->with('error', 'Too many failed attempts. Please wait 15 minutes.');
        }

        // Database login check
        $user = User::where('email', $email)->first();

        $pwHash = (string) ($user->password ?? '');
        $pwMatch = false;

        if ($user) {
            $info = password_get_info($pwHash);
            if ($info && isset($info['algoName']) && $info['algoName'] !== 'unknown') {
                $pwMatch = Hash::check($password, $pwHash);
            } else {
                $pwMatch = ($pwHash === $password);
            }
        }

        if ($user && $pwMatch) {
            // Check role
            if ($user->role === 'admin') {
                LoginAttempt::create(['email' => $email, 'ip_address' => $request->ip(), 'success' => true, 'attempted_at' => now()]);
                session()->regenerate();
                session([
                    'role'        => 'admin',
                    'admin_id'    => $user->id,
                    'admin_email' => $user->email,
                    'admin_name'  => $user->name,
                ]);
                \App\Models\AuditLog::record('admin_login', $user->id);
                return redirect()->route('admin.dashboard');
            }

            // For requestors, require email verification
            if (!$user->is_verified) {
                LoginAttempt::create(['email' => $email, 'ip_address' => $request->ip(), 'success' => false, 'attempted_at' => now()]);

                // Check if a valid OTP already exists
                $existingOtp = \App\Models\OtpCode::where('user_id', $user->id)
                    ->where('is_used', false)
                    ->where('expires_at', '>', now())
                    ->orderByDesc('id')
                    ->first();

                // Store user details in session so they can verify via the form
                session([
                    'reg_user_id'  => $user->id,
                    'reg_email'    => $user->email,
                    'reg_name'     => $user->name,
                    'otp_sent'     => true,
                    'masked_email' => $this->maskEmail($user->email),
                ]);

                if (!$existingOtp) {
                    $otp        = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
                    $expires_at = now()->addMinutes(10);

                    \App\Models\OtpCode::create([
                        'user_id'    => $user->id,
                        'email'      => $user->email,
                        'otp_code'   => $otp,
                        'expires_at' => $expires_at,
                    ]);

                    try {
                        \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($user, $otp) {
                            $message->to($user->email, $user->name)
                                ->subject('Your NUPost Verification Link')
                                ->html(\App\Http\Controllers\Auth\OtpController::getOtpEmailHtml($user->name, $otp, $user->email));
                        });
                    } catch (\Exception $e) {
                        \Log::error('[NUPost] Login auto-resend OTP failed: ' . $e->getMessage());
                    }

                    return redirect()->route('otp.index')->with('error', 'Please verify your email first. A new verification code has been sent to your inbox.');
                }

                return redirect()->route('otp.index')->with('error', 'Please verify your email first. We sent a verification code to your inbox.');
            }

            LoginAttempt::create(['email' => $email, 'ip_address' => $request->ip(), 'success' => true, 'attempted_at' => now()]);
            session()->regenerate();
            session([
                'role'    => 'requestor',
                'user_id' => $user->id,
                'name'    => $user->name,
            ]);

            \App\Models\AuditLog::record('user_login', $user->id);

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
        $role = session('role');
        if ($role === 'admin') {
            $user = User::where('email', session('admin_email'))->first();
            if ($user) {
                \App\Models\AuditLog::record('admin_logout', $user->id);
            }
        } elseif ($role === 'requestor' && session('user_id')) {
            \App\Models\AuditLog::record('user_logout', session('user_id'));
        }

        if ($request->cookie('remember_token')) {
            RememberedDevice::where('token', $request->cookie('remember_token'))->delete();
            cookie()->queue(cookie()->forget('remember_token'));
        }

        if (\Illuminate\Support\Facades\Auth::check()) {
            \Illuminate\Support\Facades\Auth::logout();
        }

        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing')
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sun, 02 Jan 1990 00:00:00 GMT');
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email);
        $masked = substr($local, 0, 2) . str_repeat('*', max(strlen($local) - 3, 2)) . substr($local, -1);
        return $masked . '@' . $domain;
    }
}