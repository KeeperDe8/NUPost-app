<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\OtpCode;
use App\Models\OtpAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class OtpController extends Controller
{
    public function index()
    {
        if (!session('reg_user_id')) {
            return redirect()->route('register');
        }
        return view('auth.verify');
    }

    public function store(Request $request)
    {
        if (!session('reg_user_id')) {
            return redirect()->route('register');
        }

        $user_id = session('reg_user_id');

        $recent = OtpAttempt::where('user_id', $user_id)
            ->where('success', false)
            ->where('attempted_at', '>', now()->subMinutes(15))
            ->count();

        if ($recent >= 5) {
            return back()->with('error', 'Too many failed attempts. Please wait 15 minutes.');
        }

        $digits = [];
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
            OtpAttempt::create(['user_id' => $user_id, 'success' => true, 'attempted_at' => now()]);
            $otp_row->update(['is_used' => true]);
            User::where('id', $user_id)->update(['is_verified' => true]);

            session()->forget(['reg_user_id', 'reg_email', 'reg_name', 'otp_sent', 'masked_email']);
            session(['reg_success' => 'Account verified! You can now log in.']);

            return redirect()->route('login');
        }

        OtpAttempt::create(['user_id' => $user_id, 'success' => false, 'attempted_at' => now()]);
        $remaining = max(0, 5 - ($recent + 1));
        return back()->with('error', "Invalid code. $remaining attempt(s) remaining.");
    }

    public function resend()
    {
        if (!session('reg_user_id')) {
            return redirect()->route('register');
        }

        $user_id = session('reg_user_id');
        $email   = session('reg_email');
        $name    = session('reg_name');

        $otp        = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires_at = now()->addMinutes(10);

        OtpCode::where('user_id', $user_id)->delete();
        OtpCode::create([
            'user_id'    => $user_id,
            'email'      => $email,
            'otp_code'   => $otp,
            'expires_at' => $expires_at,
        ]);

        try {
            Mail::send([], [], function ($message) use ($email, $name, $otp) {
                $message->to($email, $name)
                    ->subject('Your NUPost Verification Code')
                    ->html(self::getOtpEmailHtml($name, $otp));
            });
            return redirect()->route('otp.index')->with('success', 'A new code has been sent to your email.');
        } catch (\Exception $e) {
            \Log::error('[NUPost] Resend OTP failed: ' . $e->getMessage());
            return redirect()->route('otp.index')->with('error', 'Email could not be sent. Please check the OTP in the database.');
        }
    }

    private static function getOtpEmailHtml(string $name, string $otp): string
    {
        $digits = str_split($otp);
        $boxes  = '';
        foreach ($digits as $d) {
            $boxes .= "<span style='display:inline-block;width:48px;height:56px;background:#f0f4ff;border:2px solid #002366;border-radius:10px;font-size:28px;font-weight:700;color:#002366;line-height:56px;text-align:center;margin:0 4px;font-family:monospace;'>$d</span>";
        }
        return "<!DOCTYPE html><html><body style='margin:0;padding:0;background:#f5f6fa;font-family:Arial,sans-serif;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background:#f5f6fa;padding:40px 0;'>
        <tr><td align='center'>
        <table width='520' cellpadding='0' cellspacing='0' style='background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);'>
        <tr><td style='background:#002366;padding:32px 40px;text-align:center;'>
            <div style='font-size:22px;font-weight:700;color:white;'>NUPost</div>
            <div style='font-size:13px;color:rgba(255,255,255,0.7);margin-top:4px;'>NU Lipa Social Media Request System</div>
        </td></tr>
        <tr><td style='padding:36px 40px;text-align:center;'>
            <h1 style='font-size:20px;font-weight:700;color:#111827;margin:0 0 8px;'>Verify Your Identity</h1>
            <p style='font-size:14px;color:#6b7280;margin:0 0 28px;'>Hi <strong style='color:#111827;'>$name</strong>, use the code below to complete your registration.</p>
            <div style='background:#f8faff;border:1.5px solid #e0e8ff;border-radius:12px;padding:28px 20px;display:inline-block;'>
                <div style='font-size:11px;font-weight:600;color:#6b7280;letter-spacing:1px;text-transform:uppercase;margin-bottom:16px;'>Your Verification Code</div>
                <div>$boxes</div>
            </div>
            <div style='margin-top:20px;background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:10px 18px;font-size:13px;color:#92400e;font-weight:500;display:inline-block;'>
                ⏱️ This code expires in <strong>10 minutes</strong>
            </div>
        </td></tr>
        <tr><td style='background:#f5f6fa;padding:20px 40px;text-align:center;border-top:1px solid #e5e7eb;'>
            <p style='font-size:12px;color:#9ca3af;margin:0;'>© " . date('Y') . " NUPost — NU Lipa. Automated message, do not reply.</p>
        </td></tr>
        </table></td></tr></table></body></html>";
    }
}