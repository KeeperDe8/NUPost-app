<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    public function index()
    {
        return view('auth.forgot_password');
    }

    public function sendOtp(Request $request)
    {
        $email = trim($request->input('email', ''));

        if (!$email) {
            return back()->with('error', 'Please enter your email address.');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            // Don't reveal if email exists
            return back()->with('success', 'If that email is registered, a reset code has been sent.');
        }

        $otp        = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires_at = now()->addMinutes(10);

        OtpCode::where('email', $email)->delete();
        OtpCode::create([
            'user_id'    => $user->id,
            'email'      => $email,
            'otp_code'   => $otp,
            'expires_at' => $expires_at,
        ]);

        try {
            Mail::send([], [], function ($message) use ($email, $user, $otp) {
                $message->to($email, $user->name)
                    ->subject('NUPost Password Reset Code')
                    ->html($this->getResetEmailHtml($user->name, $otp));
            });
        } catch (\Exception $e) {
            \Log::error('[NUPost] Reset email failed: ' . $e->getMessage());
        }

        session([
            'reset_user_id'    => $user->id,
            'reset_email'      => $email,
            'reset_name'       => $user->name,
            'reset_masked'     => $this->maskEmail($email),
        ]);

        return redirect()->route('password.verify');
    }

    public function verifyIndex()
    {
        if (!session('reset_user_id')) {
            return redirect()->route('password.forgot');
        }
        return view('auth.reset_verify');
    }

    public function verifyOtp(Request $request)
    {
        if (!session('reset_user_id')) {
            return redirect()->route('password.forgot');
        }

        $user_id = session('reset_user_id');
        $digits  = [];
        for ($i = 1; $i <= 6; $i++) {
            $digits[] = preg_replace('/\D/', '', $request->input("d$i", ''));
        }
        $entered_otp = implode('', $digits);

        if (strlen($entered_otp) !== 6) {
            return back()->with('error', 'Please enter all 6 digits.');
        }

        $otp_row = OtpCode::where('user_id', $user_id)
            ->where('is_used', false)
            ->where('expires_at', '>', now())
            ->first();

        if ($otp_row && hash_equals($otp_row->otp_code, $entered_otp)) {
            $otp_row->update(['is_used' => true]);
            session(['reset_verified' => true]);
            return redirect()->route('password.reset');
        }

        return back()->with('error', 'Invalid or expired code. Please try again.');
    }

    public function resetIndex()
    {
        if (!session('reset_user_id') || !session('reset_verified')) {
            return redirect()->route('password.forgot');
        }
        return view('auth.reset_password');
    }

    public function resetPassword(Request $request)
    {
        if (!session('reset_user_id') || !session('reset_verified')) {
            return redirect()->route('password.forgot');
        }

        $new_pw     = trim($request->input('new_password', ''));
        $confirm_pw = trim($request->input('confirm_password', ''));

        if (!$new_pw || !$confirm_pw) {
            return back()->with('error', 'Please fill in all fields.');
        }
        if ($new_pw !== $confirm_pw) {
            return back()->with('error', 'Passwords do not match.');
        }
        if (strlen($new_pw) < 8) {
            return back()->with('error', 'Password must be at least 8 characters.');
        }
        if (!preg_match('/[A-Z]/', $new_pw)) {
            return back()->with('error', 'Password must contain at least one uppercase letter.');
        }
        if (!preg_match('/[0-9]/', $new_pw)) {
            return back()->with('error', 'Password must contain at least one number.');
        }
        if (!preg_match('/[\W_]/', $new_pw)) {
            return back()->with('error', 'Password must contain at least one special character.');
        }

        User::where('id', session('reset_user_id'))->update([
            'password' => Hash::make($new_pw),
        ]);

        session()->forget(['reset_user_id', 'reset_email', 'reset_name', 'reset_masked', 'reset_verified']);
        session(['reg_success' => 'Password reset successfully! You can now log in.']);

        return redirect()->route('login');
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email);
        $masked = substr($local, 0, 2) . str_repeat('*', max(strlen($local) - 3, 2)) . substr($local, -1);
        return $masked . '@' . $domain;
    }

    private function getResetEmailHtml(string $name, string $otp): string
    {
        $digits = str_split($otp);
        $boxes  = '';
        foreach ($digits as $d) {
            $boxes .= "<span style='display:inline-block;width:48px;height:56px;background:#fff0f0;border:2px solid #ef4444;border-radius:10px;font-size:28px;font-weight:700;color:#dc2626;line-height:56px;text-align:center;margin:0 4px;font-family:monospace;'>$d</span>";
        }
        return "<!DOCTYPE html><html><body style='margin:0;padding:0;background:#f5f6fa;font-family:Arial,sans-serif;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background:#f5f6fa;padding:40px 0;'>
        <tr><td align='center'>
        <table width='520' cellpadding='0' cellspacing='0' style='background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);'>
        <tr><td style='background:#002366;padding:32px 40px;text-align:center;'>
            <div style='font-size:22px;font-weight:700;color:white;'>NUPost</div>
            <div style='font-size:13px;color:rgba(255,255,255,0.7);margin-top:4px;'>Password Reset Request</div>
        </td></tr>
        <tr><td style='padding:36px 40px;text-align:center;'>
            <h1 style='font-size:20px;font-weight:700;color:#111827;margin:0 0 8px;'>Reset Your Password</h1>
            <p style='font-size:14px;color:#6b7280;margin:0 0 28px;'>Hi <strong style='color:#111827;'>$name</strong>, use this code to reset your password.</p>
            <div style='background:#fff5f5;border:1.5px solid #fecaca;border-radius:12px;padding:28px 20px;display:inline-block;'>
                <div style='font-size:11px;font-weight:600;color:#6b7280;letter-spacing:1px;text-transform:uppercase;margin-bottom:16px;'>Password Reset Code</div>
                <div>$boxes</div>
            </div>
            <div style='margin-top:20px;background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:10px 18px;font-size:13px;color:#92400e;font-weight:500;display:inline-block;'>
                ⏱️ This code expires in <strong>10 minutes</strong>
            </div>
            <p style='font-size:12px;color:#9ca3af;margin-top:20px;'>If you did not request a password reset, please ignore this email.</p>
        </td></tr>
        <tr><td style='background:#f5f6fa;padding:20px 40px;text-align:center;border-top:1px solid #e5e7eb;'>
            <p style='font-size:12px;color:#9ca3af;margin:0;'>© " . date('Y') . " NUPost — NU Lipa</p>
        </td></tr>
        </table></td></tr></table></body></html>";
    }
}