<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
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
            $payload['is_verified'] = 1;
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
            ->get(['id', 'request_id', 'title', 'status', 'created_at'])
            ->map(function ($r) {
                $status = trim((string) ($r->status ?? ''));
                return [
                    'id' => (int) $r->id,
                    'request_id' => (string) ($r->request_id ?? ''),
                    'title' => (string) ($r->title ?? ''),
                    'status' => $status !== '' ? $status : 'Pending',
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
            'status' => 'Pending',
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
                'status' => 'Pending',
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

        $rows = DB::table('notifications')
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'message', 'type', 'is_read', 'created_at']);

        $notifications = $rows->map(function ($n) {
            return [
                'id' => (int) $n->id,
                'request_id' => null,
                'title' => (string) ($n->title ?? ''),
                'message' => (string) ($n->message ?? ''),
                'type' => (string) ($n->type ?? 'status_update'),
                'is_read' => (bool) ($n->is_read ?? false),
                'created_at' => (string) ($n->created_at ?? ''),
                'request_status' => null,
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
}
