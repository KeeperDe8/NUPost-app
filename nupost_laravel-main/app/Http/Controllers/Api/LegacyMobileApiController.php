<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LegacyMobileApiController extends Controller
{
    private function requestsTable(): string
    {
        if (Schema::hasTable('requests')) {
            return 'requests';
        }

        return 'post_requests';
    }

    private function ensureRequestsTableExists(): ?JsonResponse
    {
        $table = $this->requestsTable();
        if (!Schema::hasTable($table)) {
            return response()->json([
                'success' => false,
                'message' => 'Requests table is missing',
            ], 500);
        }

        return null;
    }

    private function normalizeRequestStatus(?string $status): ?string
    {
        $clean = trim((string) $status);
        if ($clean === '') {
            return null;
        }

        $lower = strtolower($clean);
        return match ($lower) {
            'pending', 'pending review' => 'Pending Review',
            'under review' => 'Under Review',
            'approved' => 'Approved',
            'posted' => 'Posted',
            'rejected' => 'Rejected',
            default => $clean,
        };
    }

    private function statusFromNotificationType(string $type): ?string
    {
        $lower = strtolower(trim($type));

        return match ($lower) {
            'review', 'under_review', 'received', 'status_update' => 'Under Review',
            'approved' => 'Approved',
            'posted' => 'Posted',
            'rejected' => 'Rejected',
            default => null,
        };
    }

    private function extractQuotedRequestTitle(string $text): ?string
    {
        if (preg_match('/"([^"]+)"/', $text, $matches) === 1) {
            $title = trim((string) ($matches[1] ?? ''));
            return $title === '' ? null : $title;
        }

        return null;
    }

    public function login(Request $request): JsonResponse
    {
        $email = trim((string) $request->input('email', ''));
        $password = trim((string) $request->input('password', ''));

        if ($email === '' || $password === '') {
            return response()->json([
                'success' => false,
                'message' => 'Email and password are required',
            ], 422);
        }

        $user = DB::table('users')
            ->select('id', 'name', 'email', 'password', 'is_verified')
            ->where('email', $email)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        $pwHash = (string) ($user->password ?? '');
        $pwMatch = $pwHash === $password || Hash::check($password, $pwHash);

        if (!$pwMatch) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        if (isset($user->is_verified) && (int) $user->is_verified === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email first',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (int) $user->id,
                'name' => (string) ($user->name ?? ''),
                'email' => (string) ($user->email ?? ''),
            ],
        ], 200);
    }

    public function register(Request $request): JsonResponse
    {
        $name = trim((string) $request->input('name', ''));
        $email = trim((string) $request->input('email', ''));
        $password = trim((string) $request->input('password', ''));

        if ($name === '' || $email === '' || $password === '') {
            return response()->json([
                'success' => false,
                'message' => 'Name, email, and password are required',
            ], 422);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email format',
            ], 422);
        }

        if (strlen($password) < 6) {
            return response()->json([
                'success' => false,
                'message' => 'Password must be at least 6 characters',
            ], 422);
        }

        $exists = DB::table('users')->where('email', $email)->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Email already exists',
            ], 409);
        }

        $payload = [
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ];

        if (Schema::hasColumn('users', 'is_verified')) {
            $payload['is_verified'] = 0;
        }

        if (Schema::hasColumn('users', 'role')) {
            $payload['role'] = 'staff';
        }

        if (Schema::hasColumn('users', 'created_at')) {
            $payload['created_at'] = now();
        }

        if (Schema::hasColumn('users', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        $newId = (int) DB::table('users')->insertGetId($payload);

        // Generate OTP
        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        DB::table('otp_codes')->insert([
            'user_id' => $newId,
            'email' => $email,
            'otp_code' => $otp,
            'expires_at' => now()->addMinutes(10),
            'is_used' => 0,
        ]);

        $this->sendOtpEmail($email, $name, $otp);

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully',
            'data' => [
                'id' => $newId,
                'name' => $name,
                'email' => $email,
            ],
        ], 201);
    }

    private function sendOtpEmail(string $email, string $name, string $otp): void
    {
        $digits = str_split($otp);
        $boxes  = '';
        foreach ($digits as $d) {
            $boxes .= "<span style='display:inline-block;width:48px;height:56px;background:#f0f4ff;border:2px solid #002366;border-radius:10px;font-size:28px;font-weight:700;color:#002366;line-height:56px;text-align:center;margin:0 4px;font-family:monospace;'>$d</span>";
        }

        $htmlBody = "<!DOCTYPE html><html><body style='margin:0;padding:0;background:#f5f6fa;font-family:Arial,sans-serif;'>
        <table width='100%' cellpadding='0' cellspacing='0' style='background:#f5f6fa;padding:40px 0;'>
        <tr><td align='center'>
        <table width='520' cellpadding='0' cellspacing='0' style='background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,0.08);'>
        <tr><td style='background:#002366;padding:32px 40px;text-align:center;'>
            <div style='font-size:22px;font-weight:700;color:white;'>NUPost</div>
            <div style='font-size:13px;color:rgba(255,255,255,0.7);margin-top:4px;'>NU Lipa Social Media Request System</div>
        </td></tr>
        <tr><td style='padding:36px 40px;text-align:center;'>
            <h1 style='font-size:20px;font-weight:700;color:#111827;margin:0 0 8px;'>Verify Your Identity</h1>
            <p style='font-size:14px;color:#6b7280;margin:0 0 28px;'>Hi <strong style='color:#111827;'>$name</strong>, use the code below.</p>
            <div style='background:#f8faff;border:1.5px solid #e0e8ff;border-radius:12px;padding:28px 20px;display:inline-block;'>
                <div style='font-size:11px;font-weight:600;color:#6b7280;letter-spacing:1px;text-transform:uppercase;margin-bottom:16px;'>Your Verification Code</div>
                <div>$boxes</div>
            </div>
            <div style='margin-top:30px;background:#fef3c7;border:1px solid #fde68a;border-radius:8px;padding:10px 18px;font-size:13px;color:#92400e;font-weight:500;display:inline-block;'>
                This code expires in <strong>10 minutes</strong>
            </div>
        </td></tr>
        <tr><td style='background:#f5f6fa;padding:20px 40px;text-align:center;border-top:1px solid #e5e7eb;'>
            <p style='font-size:12px;color:#9ca3af;margin:0;'>&copy; " . date('Y') . " NUPost &mdash; NU Lipa. Automated message, do not reply.</p>
        </td></tr>
        </table></td></tr></table></body></html>";

        try {
            Mail::html($htmlBody, function ($message) use ($email, $name) {
                $message->to($email, $name)
                        ->subject('Your NUPost Verification Code');
            });
        } catch (\Exception $e) {
            // Fails silently, logging error
            \Illuminate\Support\Facades\Log::error('Failed to send OTP email: ' . $e->getMessage());
        }
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $email = trim((string) $request->input('email', ''));
        $otp = trim((string) $request->input('otp', ''));

        if ($email === '' || $otp === '') {
            return response()->json([
                'success' => false,
                'message' => 'Email and OTP are required',
            ], 422);
        }

        $record = DB::table('otp_codes')
            ->where('email', $email)
            ->where('otp_code', $otp)
            ->where('is_used', 0)
            ->where('expires_at', '>', now())
            ->orderByDesc('id')
            ->first();

        if ($record) {
            DB::table('otp_codes')->where('id', $record->id)->update(['is_used' => 1]);
            DB::table('users')->where('email', $email)->update(['is_verified' => 1]);

            return response()->json([
                'success' => true,
                'message' => 'OTP Verified Successfully'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid or expired OTP'
        ], 400);
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $email = trim((string) $request->input('email', ''));

        if ($email === '') {
            return response()->json([
                'success' => false,
                'message' => 'Email is required'
            ], 422);
        }

        $user = DB::table('users')->where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $purpose = trim((string) $request->input('purpose', ''));
        if (isset($user->is_verified) && (int) $user->is_verified === 1 && $purpose !== 'password_reset') {
            return response()->json([
                'success' => false,
                'message' => 'Account is already verified'
            ], 400);
        }

        $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        DB::table('otp_codes')->insert([
            'user_id' => $user->id,
            'email' => $email,
            'otp_code' => $otp,
            'expires_at' => now()->addMinutes(10),
            'is_used' => 0,
        ]);

        $this->sendOtpEmail($email, $user->name ?? 'User', $otp);

        return response()->json([
            'success' => true,
            'message' => 'OTP resent successfully'
        ], 200);
    }

    public function profile(Request $request): JsonResponse
    {
        $userId = (int) $request->query('user_id', 0);
        if ($userId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'user_id is required',
            ], 422);
        }

        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($err = $this->ensureRequestsTableExists()) {
            return $err;
        }

        $table = $this->requestsTable();
        $requester = (string) ($user->name ?? '');

        $total = DB::table($table)->where('requester', $requester)->count();
        $approved = DB::table($table)
            ->where('requester', $requester)
            ->where('status', 'Approved')
            ->count();
        $pending = DB::table($table)
            ->where('requester', $requester)
            ->where(function ($q) {
                $q->where('status', 'Pending')
                    ->orWhere('status', 'Pending Review')
                    ->orWhere('status', 'Under Review')
                    ->orWhereNull('status')
                    ->orWhere('status', '');
            })
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => (int) $user->id,
                'name' => (string) ($user->name ?? ''),
                'email' => (string) ($user->email ?? ''),
                'phone' => (string) ($user->phone ?? ''),
                'organization' => (string) ($user->organization ?? ''),
                'role' => (string) ($user->role ?? 'staff'),
                'public_profile' => (int) ($user->public_profile ?? 0),
                'public_calendar' => (int) ($user->public_calendar ?? 0),
                'stats' => [
                    'total' => $total,
                    'approved' => $approved,
                    'pending' => $pending,
                ],
            ],
        ], 200);
    }

    public function requests(Request $request): JsonResponse
    {
        $userId = (int) $request->query('user_id', 0);
        $status = trim((string) $request->query('status', ''));

        if ($userId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'user_id is required',
            ], 422);
        }

        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($err = $this->ensureRequestsTableExists()) {
            return $err;
        }

        $table = $this->requestsTable();
        $query = DB::table($table)->where('requester', (string) ($user->name ?? ''));
        if ($status !== '' && strtolower($status) !== 'all') {
            if (strtolower($status) === 'pending') {
                $query->where(function ($q) {
                    $q->where('status', 'Pending')
                        ->orWhere('status', 'Pending Review')
                        ->orWhere('status', 'Under Review')
                        ->orWhereNull('status')
                        ->orWhere('status', '');
                });
            } else {
                $query->where('status', $status);
            }
        }

        $rows = $query
            ->orderByDesc('created_at')
            ->get(['id', 'request_id', 'title', 'status', 'created_at', 'priority', 'platform'])
            ->map(function ($r) {
                $status = trim((string) ($r->status ?? ''));
                return [
                    'id' => (int) $r->id,
                    'request_id' => (string) ($r->request_id ?? ''),
                    'title' => (string) ($r->title ?? ''),
                    'status' => $status !== '' ? $status : 'Pending',
                    'priority' => $r->priority ?? 'Low',
                    'platform' => $r->platform ?? 'Facebook',
                    'created_at' => (string) ($r->created_at ?? ''),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $rows,
        ], 200);
    }

    public function createRequest(Request $request): JsonResponse
    {
        $userId = (int) $request->input('user_id', 0);
        $title = trim((string) $request->input('title', ''));
        $description = trim((string) $request->input('description', ''));
        $category = trim((string) $request->input('category', ''));
        $priority = trim((string) $request->input('priority', ''));
        $preferredDate = trim((string) $request->input('preferred_date', ''));
        $caption = trim((string) $request->input('caption', ''));

        if ($request->has('platforms') && is_array($request->input('platforms'))) {
            $platforms = $request->input('platforms');
        } elseif ($request->has('platforms_json')) {
            $decoded = json_decode((string) $request->input('platforms_json'), true);
            $platforms = is_array($decoded) ? $decoded : [];
        } else {
            $platforms = [];
        }

        if ($userId <= 0 || $title === '' || $description === '' || $category === '' || $priority === '') {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields',
            ], 422);
        }

        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($err = $this->ensureRequestsTableExists()) {
            return $err;
        }

        $mediaNames = [];
        $files = $request->file('media', []);
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }
        if (!is_array($files)) {
            $files = [];
        }

        if (!empty($files)) {
            $uploadDir = public_path('uploads');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'video/mp4',
                'video/quicktime',
            ];
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'mov'];
            $maxSize = 10 * 1024 * 1024;

            foreach (array_slice($files, 0, 4) as $file) {
                if (!$file instanceof UploadedFile) {
                    continue;
                }

                if (!$file->isValid()) {
                    continue;
                }

                if ($file->getSize() > $maxSize) {
                    continue;
                }

                $ext = strtolower((string) $file->getClientOriginalExtension());
                $type = (string) $file->getMimeType();
                $validType = in_array($type, $allowedTypes, true);
                $validExt = in_array($ext, $allowedExtensions, true);
                if (!$validType && !$validExt) {
                    continue;
                }

                $newName = uniqid('media_', true) . ($ext !== '' ? ".{$ext}" : '');
                $file->move($uploadDir, $newName);
                $mediaNames[] = $newName;
            }
        }

        $table = $this->requestsTable();
        $payload = [
            'title' => $title,
            'requester' => (string) ($user->name ?? ''),
            'category' => $category,
            'priority' => $priority,
            'status' => 'Pending Review',
            'description' => $description,
            'media_file' => implode(',', $mediaNames),
            'platform' => is_array($platforms) ? implode(',', $platforms) : '',
            'caption' => $caption,
            'preferred_date' => $preferredDate !== '' ? $preferredDate : null,
        ];

        if (Schema::hasColumn($table, 'created_at')) {
            $payload['created_at'] = now();
        }
        if (Schema::hasColumn($table, 'updated_at')) {
            $payload['updated_at'] = now();
        }

        $newId = (int) DB::table($table)->insertGetId($payload);
        $reqCode = 'REQ-' . str_pad((string) $newId, 5, '0', STR_PAD_LEFT);
        if (Schema::hasColumn($table, 'request_id')) {
            DB::table($table)->where('id', $newId)->update(['request_id' => $reqCode]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $newId,
                'request_id' => $reqCode,
                'status' => 'Pending Review',
            ],
        ], 201);
    }

    public function generateCaption(Request $request): JsonResponse
    {
        if (empty($request->all())) {
            return response()->json(['error' => 'No data received']);
        }

        $apiKey = (string) env('GEMINI_API_KEY', '');
        if ($apiKey === '') {
            return response()->json(['error' => 'API key is missing']);
        }

        $title = (string) $request->input('title', '');
        $description = (string) $request->input('description', '');
        $category = (string) $request->input('category', 'General');
        $platforms = $request->input('platforms', 'Social Media');
        if (is_array($platforms)) {
            $platforms = implode(', ', $platforms);
        }

        $prompt = "Write a short, engaging social media caption for a university/college post. Keep it under 150 words. Be catchy, use 2-3 relevant emojis, and make it appropriate for a college audience.\n\n"
            . "Event/Post Title: {$title}\n"
            . "Description: {$description}\n"
            . "Category: {$category}\n"
            . "Target Platforms: {$platforms}\n\n"
            . "Reply with ONLY the caption text. No explanations, no labels, just the caption.";

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-lite-latest:generateContent?key=' . $apiKey;

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->timeout(30)
                ->withoutVerifying()
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                    'generationConfig' => [
                        'temperature' => 0.8,
                        'maxOutputTokens' => 300,
                    ],
                ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Connection error: ' . $e->getMessage()]);
        }

        if (!$response->successful()) {
            $message = (string) data_get($response->json(), 'error.message', 'API returned status ' . $response->status());
            return response()->json(['error' => $message]);
        }

        return response()->json($response->json(), 200);
    }

    public function calendar(Request $request): JsonResponse
    {
        $userId = (int) $request->query('user_id', 0);
        $month = (int) $request->query('month', date('m'));
        $year = (int) $request->query('year', date('Y'));
        $publicView = (int) $request->query('public', 0) === 1;

        if ($userId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'user_id is required',
            ], 422);
        }

        if ($month < 1 || $month > 12) {
            $month = (int) date('m');
        }
        if ($year < 2000 || $year > 2100) {
            $year = (int) date('Y');
        }

        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($err = $this->ensureRequestsTableExists()) {
            return $err;
        }

        $table = $this->requestsTable();
        $requester = (string) ($user->name ?? '');

        if ($publicView) {
            $query = DB::table($table)
                ->select(['id', 'title', 'status', 'priority', 'created_at', 'preferred_date', 'requester'])
                ->where(function ($q) use ($month, $year) {
                    $q->where(function ($sq) use ($month, $year) {
                        $sq->whereNotNull('preferred_date')
                            ->whereMonth('preferred_date', $month)
                            ->whereYear('preferred_date', $year);
                    })->orWhere(function ($sq) use ($month, $year) {
                        $sq->whereNotNull('created_at')
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $year);
                    });
                })
                ->orderBy('preferred_date')
                ->orderBy('created_at');
        } else {
            $query = DB::table($table)
                ->select(['id', 'title', 'status', 'priority', 'created_at', 'preferred_date'])
                ->where('requester', $requester)
                ->where(function ($q) use ($month, $year) {
                    $q->where(function ($sq) use ($month, $year) {
                        $sq->whereNotNull('created_at')
                            ->whereMonth('created_at', $month)
                            ->whereYear('created_at', $year);
                    })->orWhere(function ($sq) use ($month, $year) {
                        $sq->whereNotNull('preferred_date')
                            ->whereMonth('preferred_date', $month)
                            ->whereYear('preferred_date', $year);
                    });
                })
                ->orderBy('created_at');
        }

        $posts = $query->get()->map(function ($row) use ($publicView) {
            return [
                'id' => (int) $row->id,
                'title' => (string) ($row->title ?? ''),
                'status' => (string) ($row->status ?? 'Pending'),
                'priority' => (string) ($row->priority ?? 'normal'),
                'request_date' => (string) ($row->created_at ?? ''),
                'scheduled_date' => (string) ($row->preferred_date ?? ''),
                'requester' => $publicView ? (string) ($row->requester ?? '') : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'month' => $month,
                'year' => $year,
                'posts' => $posts,
                'public_view' => $publicView,
            ],
        ], 200);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $userId = (int) $request->input('user_id', 0);
        $publicProfile = $request->input('public_profile', null);
        $publicCalendar = $request->input('public_calendar', null);
        
        $name = $request->input('name');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $bio = $request->input('bio');
        $organization = $request->input('organization');
        $department = $request->input('department');

        $emailNotif = $request->input('email_notif');
        $statusUpdates = $request->input('status_updates');

        if ($userId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'user_id is required',
            ], 422);
        }

        $updates = [];
        $responseData = [];

        if ($publicProfile !== null && Schema::hasColumn('users', 'public_profile')) {
            $profileVal = ((string) $publicProfile === '1' || $publicProfile === 1 || $publicProfile === true) ? 1 : 0;
            $updates['public_profile'] = $profileVal;
            $responseData['public_profile'] = $profileVal;
        }

        if ($publicCalendar !== null && Schema::hasColumn('users', 'public_calendar')) {
            $calendarVal = ((string) $publicCalendar === '1' || $publicCalendar === 1 || $publicCalendar === true) ? 1 : 0;
            $updates['public_calendar'] = $calendarVal;
            $responseData['public_calendar'] = $calendarVal;
        }

        // Handle basic profile fields
        $fields = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'bio' => $bio,
            'organization' => $organization,
            'department' => $department,
            'email_notif' => $emailNotif !== null ? ((string)$emailNotif === '1' || $emailNotif === 1 || $emailNotif === true ? 1 : 0) : null,
            'status_updates' => $statusUpdates !== null ? ((string)$statusUpdates === '1' || $statusUpdates === 1 || $statusUpdates === true ? 1 : 0) : null,
        ];

        foreach ($fields as $col => $val) {
            if ($val !== null && Schema::hasColumn('users', $col)) {
                $updates[$col] = $val;
                $responseData[$col] = $val;
            }
        }

        if (empty($updates)) {
            return response()->json([
                'success' => false,
                'message' => 'No fields to update',
            ], 422);
        }

        DB::table('users')->where('id', $userId)->limit(1)->update($updates);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated',
            'data' => $responseData,
        ], 200);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $userId = (int) $request->input('user_id', 0);
        $currentPassword = (string) $request->input('current_password', '');
        $newPassword = (string) $request->input('new_password', '');

        if ($userId <= 0 || $currentPassword === '' || $newPassword === '') {
            return response()->json([
                'success' => false,
                'message' => 'Missing required fields',
            ], 400);
        }

        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if (!Hash::check($currentPassword, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 400);
        }

        DB::table('users')->where('id', $userId)->update([
            'password' => Hash::make($newPassword)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully',
        ]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $userId = (int) $request->query('user_id', 0);
        if ($userId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'user_id is required',
            ], 422);
        }

        if (!Schema::hasTable('notifications')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'notifications' => [],
                    'unread_count' => 0,
                ],
            ], 200);
        }

        $hasNotificationRequestId = Schema::hasColumn('notifications', 'request_id');
        $hasNotificationRequestStatus = Schema::hasColumn('notifications', 'request_status');

        $selectColumns = ['id', 'title', 'message', 'type', 'is_read', 'created_at'];
        if ($hasNotificationRequestId) {
            $selectColumns[] = 'request_id';
        }
        if ($hasNotificationRequestStatus) {
            $selectColumns[] = 'request_status';
        }

        $rows = DB::table('notifications')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get($selectColumns);

        $requestsByTitle = [];
        $requestStatusById = [];

        $requestTable = $this->requestsTable();
        if (Schema::hasTable($requestTable)) {
            $requestQuery = DB::table($requestTable)->orderByDesc('id');

            $requesterName = trim((string) DB::table('users')->where('id', $userId)->value('name'));
            if ($requesterName !== '' && Schema::hasColumn($requestTable, 'requester')) {
                $requestQuery->where('requester', $requesterName);
            }

            foreach ($requestQuery->get(['id', 'title', 'status']) as $req) {
                $reqId = (int) ($req->id ?? 0);
                if ($reqId <= 0) {
                    continue;
                }

                $normalizedStatus = $this->normalizeRequestStatus((string) ($req->status ?? ''));
                if ($normalizedStatus !== null) {
                    $requestStatusById[$reqId] = $normalizedStatus;
                }

                $titleKey = strtolower(trim((string) ($req->title ?? '')));
                if ($titleKey !== '' && !array_key_exists($titleKey, $requestsByTitle)) {
                    $requestsByTitle[$titleKey] = [
                        'id' => $reqId,
                        'status' => $normalizedStatus,
                    ];
                }
            }
        }

        $notifications = $rows->map(function ($n) use (
            $hasNotificationRequestId,
            $hasNotificationRequestStatus,
            $requestsByTitle,
            $requestStatusById
        ) {
            $type = (string) ($n->type ?? 'status_update');

            $requestId = null;
            if ($hasNotificationRequestId) {
                $rawRequestId = $n->request_id ?? null;
                if (is_numeric($rawRequestId)) {
                    $parsed = (int) $rawRequestId;
                    $requestId = $parsed > 0 ? $parsed : null;
                }
            }

            $requestStatus = null;
            if ($hasNotificationRequestStatus) {
                $requestStatus = $this->normalizeRequestStatus((string) ($n->request_status ?? ''));
            }
            if ($requestStatus === null) {
                $requestStatus = $this->statusFromNotificationType($type);
            }

            if ($requestId === null) {
                $message = (string) ($n->message ?? '');
                $title = (string) ($n->title ?? '');
                $quotedTitle = $this->extractQuotedRequestTitle($message)
                    ?? $this->extractQuotedRequestTitle($title);

                if ($quotedTitle !== null) {
                    $titleKey = strtolower(trim($quotedTitle));
                    if ($titleKey !== '' && isset($requestsByTitle[$titleKey])) {
                        $requestId = (int) ($requestsByTitle[$titleKey]['id'] ?? 0);
                        if ($requestId <= 0) {
                            $requestId = null;
                        }

                        if ($requestStatus === null) {
                            $requestStatus = $requestsByTitle[$titleKey]['status'] ?? null;
                        }
                    }
                }
            }

            if ($requestStatus === null && $requestId !== null && isset($requestStatusById[$requestId])) {
                $requestStatus = $requestStatusById[$requestId];
            }

            return [
                'id' => (int) $n->id,
                'request_id' => $requestId,
                'title' => (string) ($n->title ?? ''),
                'message' => (string) ($n->message ?? ''),
                'type' => $type,
                'is_read' => (bool) ($n->is_read ?? false),
                'created_at' => (string) ($n->created_at ?? ''),
                'request_status' => $requestStatus,
            ];
        })->values();

        $unreadCount = DB::table('notifications')
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
            ],
        ], 200);
    }

    public function markNotificationRead(Request $request): JsonResponse
    {
        $userId = (int) $request->input('user_id', 0);
        if ($userId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'user_id is required',
            ], 422);
        }

        if (!Schema::hasTable('notifications')) {
            return response()->json([
                'success' => true,
                'message' => 'No notifications table found',
            ], 200);
        }

        $markAllRaw = $request->input('mark_all', false);
        $markAll = ($markAllRaw === true || $markAllRaw === 1 || $markAllRaw === '1' || $markAllRaw === 'true');

        if ($markAll) {
            DB::table('notifications')
                ->where('user_id', $userId)
                ->where('is_read', 0)
                ->update(['is_read' => 1]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read',
            ], 200);
        }

        $notificationId = (int) $request->input('notification_id', 0);
        if ($notificationId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'notification_id is required',
            ], 422);
        }

        DB::table('notifications')
            ->where('id', $notificationId)
            ->where('user_id', $userId)
            ->limit(1)
            ->update(['is_read' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ], 200);
    }

    public function requestDetails(Request $request): JsonResponse
    {
        $requestId = (int) $request->query('request_id', 0);
        if ($requestId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'request_id is required',
            ], 422);
        }

        if ($err = $this->ensureRequestsTableExists()) {
            return $err;
        }

        $table = $this->requestsTable();
        $requestRow = DB::table($table)
            ->where('id', $requestId)
            ->first();

        if (!$requestRow) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found',
            ], 404);
        }

        $activities = collect();
        if (Schema::hasTable('request_activity')) {
            $activities = $activities->merge(
                DB::table('request_activity')
                    ->where('request_id', $requestId)
                    ->orderBy('created_at')
                    ->get(['actor', 'action', 'created_at'])
                    ->map(function ($a) {
                        return [
                            'actor' => (string) ($a->actor ?? ''),
                            'action' => (string) ($a->action ?? ''),
                            'created_at' => (string) ($a->created_at ?? ''),
                        ];
                    })
            );
        }

        if (Schema::hasTable('request_comments')) {
            $activities = $activities->merge(
                DB::table('request_comments')
                    ->where('request_id', $requestId)
                    ->orderBy('created_at')
                    ->get(['sender_name', 'message', 'created_at'])
                    ->map(function ($c) {
                        return [
                            'actor' => (string) ($c->sender_name ?? ''),
                            'action' => 'Internal note: ' . (string) ($c->message ?? ''),
                            'created_at' => (string) ($c->created_at ?? ''),
                        ];
                    })
            );
        }

        $activities = $activities
            ->sortBy('created_at')
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'request' => [
                    'id' => (int) $requestRow->id,
                    'request_id' => (string) ($requestRow->request_id ?? ''),
                    'title' => (string) ($requestRow->title ?? ''),
                    'status' => trim((string) ($requestRow->status ?? '')) !== ''
                        ? (string) $requestRow->status
                        : 'Pending',
                    'description' => (string) ($requestRow->description ?? ''),
                    'created_at' => (string) ($requestRow->created_at ?? ''),
                    'preferred_date' => (string) ($requestRow->preferred_date ?? ''),
                    'priority' => (string) ($requestRow->priority ?? ''),
                    'category' => (string) ($requestRow->category ?? ''),
                ],
                'activities' => $activities,
            ],
        ], 200);
    }

    public function messageThreads(Request $request): JsonResponse
    {
        $userId = (int) $request->query('user_id', 0);
        if ($userId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'user_id is required',
            ], 422);
        }

        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($err = $this->ensureRequestsTableExists()) {
            return $err;
        }

        if (!Schema::hasTable('request_comments')) {
            return response()->json([
                'success' => true,
                'data' => [],
                'meta' => [
                    'total_unread' => 0,
                ],
            ], 200);
        }

        $table = $this->requestsTable();
        $requests = DB::table($table)
            ->where('requester', (string) ($user->name ?? ''))
            ->get(['id', 'request_id', 'title', 'status']);

        $threads = [];
        $totalUnread = 0;

        foreach ($requests as $req) {
            $latest = DB::table('request_comments')
                ->where('request_id', (int) $req->id)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->first(['id', 'sender_role', 'sender_name', 'message', 'created_at']);

            if (!$latest) {
                continue;
            }

            $unreadCount = 0;
            $totalUnread = 0; // App will track this locally now

            $threads[] = [
                'request_id' => (int) $req->id,
                'request_code' => (string) ($req->request_id ?? ''),
                'request_title' => (string) ($req->title ?? ''),
                'request_status' => trim((string) ($req->status ?? '')) !== ''
                    ? (string) $req->status
                    : 'Pending',
                'last_message_id' => (int) ($latest->id ?? 0),
                'last_message' => (string) ($latest->message ?? ''),
                'last_sender_role' => (string) ($latest->sender_role ?? ''),
                'last_sender_name' => (string) ($latest->sender_name ?? ''),
                'last_message_at' => (string) ($latest->created_at ?? ''),
                'unread_count' => $unreadCount,
            ];
        }

        usort($threads, function (array $a, array $b): int {
            return strcmp((string) ($b['last_message_at'] ?? ''), (string) ($a['last_message_at'] ?? ''));
        });

        return response()->json([
            'success' => true,
            'data' => array_values($threads),
            'meta' => [
                'total_unread' => $totalUnread,
            ],
        ], 200);
    }

    public function messageThread(Request $request): JsonResponse
    {
        $userId = (int) $request->query('user_id', 0);
        $requestId = (int) $request->query('request_id', 0);

        if ($userId <= 0 || $requestId <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'user_id and request_id are required',
            ], 422);
        }

        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($err = $this->ensureRequestsTableExists()) {
            return $err;
        }

        if (!Schema::hasTable('request_comments')) {
            return response()->json([
                'success' => true,
                'data' => [
                    'messages' => [],
                ],
            ], 200);
        }

        $table = $this->requestsTable();
        $req = DB::table($table)
            ->where('id', $requestId)
            ->where('requester', (string) ($user->name ?? ''))
            ->first(['id', 'request_id', 'title', 'status']);

        if (!$req) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found',
            ], 404);
        }

        $messages = DB::table('request_comments')
            ->where('request_id', $requestId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get(['id', 'sender_role', 'sender_name', 'message', 'created_at'])
            ->map(function ($m) {
                return [
                    'id' => (int) $m->id,
                    'sender_role' => (string) ($m->sender_role ?? ''),
                    'sender_name' => (string) ($m->sender_name ?? ''),
                    'message' => (string) ($m->message ?? ''),
                    'created_at' => (string) ($m->created_at ?? ''),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'request' => [
                    'id' => (int) $req->id,
                    'request_id' => (string) ($req->request_id ?? ''),
                    'title' => (string) ($req->title ?? ''),
                    'status' => trim((string) ($req->status ?? '')) !== ''
                        ? (string) $req->status
                        : 'Pending',
                ],
                'messages' => $messages,
            ],
        ], 200);
    }

    public function sendMessage(Request $request): JsonResponse
    {
        $userId = (int) $request->input('user_id', 0);
        $requestId = (int) $request->input('request_id', 0);
        $message = trim((string) $request->input('message', ''));

        if ($userId <= 0 || $requestId <= 0 || $message === '') {
            return response()->json([
                'success' => false,
                'message' => 'user_id, request_id, and message are required',
            ], 422);
        }

        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        if ($err = $this->ensureRequestsTableExists()) {
            return $err;
        }

        if (!Schema::hasTable('request_comments')) {
            return response()->json([
                'success' => false,
                'message' => 'request_comments table is missing',
            ], 500);
        }

        $table = $this->requestsTable();
        $req = DB::table($table)
            ->where('id', $requestId)
            ->where('requester', (string) ($user->name ?? ''))
            ->first(['id', 'title']);

        if (!$req) {
            return response()->json([
                'success' => false,
                'message' => 'Request not found',
            ], 404);
        }

        $payload = [
            'request_id' => $requestId,
            'sender_role' => 'requestor',
            'sender_name' => (string) ($user->name ?? ''),
            'message' => $message,
            'created_at' => now(),
        ];

        if (Schema::hasColumn('request_comments', 'updated_at')) {
            $payload['updated_at'] = now();
        }

        $newId = (int) DB::table('request_comments')->insertGetId($payload);

        if (Schema::hasTable('request_activity')) {
            $activity = [
                'request_id' => $requestId,
                'actor' => (string) ($user->name ?? ''),
                'action' => 'Requestor message: ' . $message,
                'created_at' => now(),
            ];

            if (Schema::hasColumn('request_activity', 'updated_at')) {
                $activity['updated_at'] = now();
            }

            DB::table('request_activity')->insert($activity);
        }

        return response()->json([
            'success' => true,
            'message' => 'Message sent',
            'data' => [
                'id' => $newId,
                'request_id' => $requestId,
                'sender_role' => 'requestor',
                'sender_name' => (string) ($user->name ?? ''),
                'message' => $message,
                'created_at' => now()->toDateTimeString(),
            ],
        ], 201);
    }

    public function markMessagesRead(Request $request)
    {
        $userId = $request->input('user_id');
        $requestId = $request->input('request_id');

        // Logic moved to local app storage to avoid database column dependencies
        return response()->json(['success' => true]);
    }
}

