<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function index()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $name     = trim($request->input('name', ''));
        $email    = trim($request->input('email', ''));
        $password = trim($request->input('password', ''));
        $confirm  = trim($request->input('confirm_password', ''));

        if (!$name || !$email || !$password || !$confirm)
            return back()->withInput()->with('error', 'Please fill in all fields.');

        if ($password !== $confirm)
            return back()->withInput()->with('error', 'Passwords do not match.');

        if (strlen($password) < 8)
            return back()->withInput()->with('error', 'Password must be at least 8 characters.');

        if (!preg_match('/[A-Z]/', $password))
            return back()->withInput()->with('error', 'Password must contain at least one uppercase letter.');

        if (!preg_match('/[0-9]/', $password))
            return back()->withInput()->with('error', 'Password must contain at least one number.');

        if (!preg_match('/[\W_]/', $password))
            return back()->withInput()->with('error', 'Password must contain at least one special character.');

        if (User::where('email', $email)->exists())
            return back()->withInput()->with('error', 'Email is already registered.');

        $user = User::create([
            'name'       => $name,
            'email'      => $email,
            'password'   => Hash::make($password),
            'is_verified'=> false,
        ]);

        // Generate OTP
        $otp        = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $expires_at = now()->addMinutes(10);

        OtpCode::where('email', $email)->delete();
        OtpCode::create([
            'user_id'    => $user->id,
            'email'      => $email,
            'otp_code'   => $otp,
            'expires_at' => $expires_at,
        ]);

        // Send OTP email
        $sent = false;
        try {
            Mail::send([], [], function ($message) use ($email, $name, $otp) {
                $message->to($email, $name)
                    ->subject('Your NUPost Verification Code')
                    ->html(self::getOtpEmailHtml($name, $otp));
            });
            $sent = true;
        } catch (\Exception $e) {
            \Log::error('[NUPost] OTP email failed: ' . $e->getMessage());
        }

        // Store in session
        session([
            'reg_user_id'    => $user->id,
            'reg_email'      => $email,
            'reg_name'       => $name,
            'otp_sent'       => $sent,
            'masked_email'   => $this->maskEmail($email),
        ]);

        return redirect()->route('otp.index');
    }

    private function maskEmail(string $email): string
    {
        [$local, $domain] = explode('@', $email);
        $masked = substr($local, 0, 2) . str_repeat('*', max(strlen($local) - 3, 2)) . substr($local, -1);
        return $masked . '@' . $domain;
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