<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

// Controllers
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Requestor\DashboardController;
use App\Http\Controllers\Requestor\RequestController;
use App\Http\Controllers\Requestor\ProfileController;
use App\Http\Controllers\Requestor\NotificationController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\RequestManagementController;
use App\Http\Controllers\Admin\FacebookAnalyticsController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ReportsController;

// Default Landing Page
Route::get('/', function () {
    return view('landing');
})->name('landing');

// Authentication Routes
Route::middleware('guest.nupost')->group(function () {
    Route::get('/login',         [LoginController::class,    'index'])->name('login');
    Route::post('/login',        [LoginController::class,    'store'])->name('login.store');
    Route::get('/register',      [RegisterController::class, 'index'])->name('register');
    Route::post('/register',     [RegisterController::class, 'store'])->name('register.store');

    // OTP
    Route::get('/verify',        [OtpController::class, 'index'])->name('otp.index');
    Route::post('/verify',       [OtpController::class, 'store'])->name('otp.store');
    Route::get('/verify/resend', [OtpController::class, 'resend'])->name('otp.resend');
    Route::get('/verify/{email}/{token}', [OtpController::class, 'verifyLink'])->name('verify.link');

    // Password Recovery
    Route::get('/forgot-password',         [ForgotPasswordController::class, 'index'])->name('password.forgot');
    Route::post('/forgot-password',        [ForgotPasswordController::class, 'sendOtp'])->name('password.send');
    Route::get('/forgot-password/verify',  [ForgotPasswordController::class, 'verifyIndex'])->name('password.verify');
    Route::post('/forgot-password/verify', [ForgotPasswordController::class, 'verifyOtp'])->name('password.verify.store');
    Route::get('/forgot-password/resend',  [ForgotPasswordController::class, 'resendOtp'])->name('password.resend');
    Route::get('/forgot-password/reset',   [ForgotPasswordController::class, 'resetIndex'])->name('password.reset');
    Route::post('/forgot-password/reset',  [ForgotPasswordController::class, 'resetPassword'])->name('password.reset.store');
});

Route::match(['get', 'post'], '/logout', [LoginController::class, 'destroy'])->name('logout');

// ─── REQUESTOR ROUTES ──────────────────────────────────────────────────────
Route::middleware('auth.nupost:requestor')->prefix('requestor')->name('requestor.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Calendar Routes
    Route::get('/calendar',                    [RequestController::class, 'calendar'])->name('calendar');
    Route::get('/calendar/requests-by-date',   [RequestController::class, 'requestsByDate'])->name('calendar.requests-by-date');

    // Request Management
    Route::get('/requests',             [RequestController::class, 'index'])->name('requests');
    Route::get('/requests/create',      [RequestController::class, 'create'])->name('requests.create');
    Route::post('/requests',            [RequestController::class, 'store'])->middleware('throttle:15,1')->name('requests.store');

    Route::get('/requests/{id}',        [RequestController::class, 'show'])->name('requests.show');

    Route::get('/requests/{id}/edit',   [RequestController::class, 'edit'])->name('requests.edit');
    Route::post('/requests/{id}/edit',  [RequestController::class, 'update'])->name('requests.update');
    Route::delete('/requests/{id}',     [RequestController::class, 'destroy'])->name('requests.destroy');

    // Chat System
    Route::get('/requests/{id}/chat',   [RequestController::class, 'chat'])->name('requests.chat');
    Route::post('/requests/{id}/chat',  [RequestController::class, 'sendChat'])->name('requests.chat.send');

    // AI Tools
    Route::post('/requests/{id}/generate-caption', [RequestController::class, 'generateCaption'])->name('requests.generate-caption');
    Route::post('/requests/{id}/save-caption',     [RequestController::class, 'saveCaption'])->name('requests.save-caption');

    // Notifications & Profile
    Route::get('/notifications',        [NotificationController::class, 'index'])->name('notifications');
    Route::get('/notifications/fetch',  [NotificationController::class, 'fetch'])->name('notifications.fetch');
    Route::post('/notifications/read',  [NotificationController::class, 'markRead'])->name('notifications.markread');

    Route::get('/profile',              [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/edit',         [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/edit',        [ProfileController::class, 'update'])->name('profile.update');

    // Settings
    Route::get('/settings',             [ProfileController::class, 'settings'])->name('settings');
    Route::post('/settings',            [ProfileController::class, 'saveSettings'])->name('settings.save');
    Route::post('/settings/password',   [ProfileController::class, 'updatePassword'])->name('settings.password');
});

// ─── ADMIN ROUTES ──────────────────────────────────────────────────────────
Route::middleware('auth.nupost:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',  [AdminDashboardController::class, 'index'])->name('dashboard');

    // Requests
    Route::get('/requests',           [RequestManagementController::class, 'index'])->name('requests');
    Route::get('/calendar',           [RequestManagementController::class, 'calendar'])->name('calendar');
    Route::post('/requests/status',   [RequestManagementController::class, 'updateStatus'])->name('requests.status');
    Route::post('/requests/comment',  [RequestManagementController::class, 'postComment'])->name('requests.comment');

    // Admin Analytics & Reports
    Route::get('/analytics',          [FacebookAnalyticsController::class, 'index'])->name('analytics');
    Route::get('/analytics/refresh',  [FacebookAnalyticsController::class, 'refresh'])->name('analytics.refresh');
    Route::get('/analytics/export',   [FacebookAnalyticsController::class, 'exportCsv'])->name('analytics.export');
    Route::get('/reports',            [ReportsController::class, 'index'])->name('reports');
    Route::get('/reports/export',     [ReportsController::class, 'export'])->name('reports.export');
    Route::get('/settings',           [SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/profile',  [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/kaye-token/generate', [SettingsController::class, 'generateKayeToken'])->name('settings.kaye-token.generate');
    Route::post('/settings/kaye-token/revoke', [SettingsController::class, 'revokeKayeToken'])->name('settings.kaye-token.revoke');
    Route::post('/settings/ai-keys',     [SettingsController::class, 'updateAiKeys'])->name('settings.ai-keys');
    Route::post('/settings/test-gemini', [SettingsController::class, 'testGeminiKey'])->name('settings.test-gemini');
    Route::post('/settings/test-groq',   [SettingsController::class, 'testGroqKey'])->name('settings.test-groq');

    // Audit Logs
    Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs');

    // Notifications
    Route::get('/notifications',        [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications');
    Route::get('/notifications/fetch',  [\App\Http\Controllers\Admin\NotificationController::class, 'fetch'])->name('notifications.fetch');
    Route::post('/notifications/read',  [\App\Http\Controllers\Admin\NotificationController::class, 'markRead'])->name('notifications.read');

    // Dynamic Admin Routes
    Route::get('/requests/{id}',            [RequestManagementController::class, 'show'])->name('requests.show');
    Route::get('/requests/{id}/comments',   [RequestManagementController::class, 'getComments'])->name('requests.comments');
    Route::post('/requests/{id}/generate-caption', [RequestManagementController::class, 'generateCaption'])->name('requests.generate-caption');
    Route::post('/requests/{id}/save-caption',     [RequestManagementController::class, 'saveCaption'])->name('requests.save-caption');
    Route::post('/requests/{id}/caption',          [RequestManagementController::class, 'updateCaption'])->name('requests.caption');
    Route::get('/requests/{id}/download/{filename}', [RequestManagementController::class, 'downloadFile'])->name('requests.download')->where('filename', '.*');
});

// Direct uploads route fallback (ensures images and videos display even if web server rewrites to index.php or on Hostinger public_html setups)
Route::get('/uploads/{filename}', function ($filename) {
    $filename = basename($filename);
    $candidates = [
        public_path('uploads/' . $filename),
        base_path('public_html/uploads/' . $filename),
        base_path('uploads/' . $filename),
        storage_path('app/public/uploads/' . $filename),
        public_path('uploads/' . $filename . '.jpg'),
        public_path('uploads/' . $filename . '.png'),
        public_path('uploads/' . $filename . '.jpeg'),
        public_path('uploads/' . $filename . '.jfif'),
        public_path('uploads/' . $filename . '.webp'),
        base_path('public_html/uploads/' . $filename . '.jpg'),
        base_path('public_html/uploads/' . $filename . '.png'),
        base_path('public_html/uploads/' . $filename . '.jpeg'),
        base_path('public_html/uploads/' . $filename . '.jfif'),
        base_path('public_html/uploads/' . $filename . '.webp'),
        storage_path('app/public/uploads/' . $filename . '.jpg'),
        storage_path('app/public/uploads/' . $filename . '.png'),
        storage_path('app/public/uploads/' . $filename . '.jpeg'),
    ];

    $filePath = null;
    foreach ($candidates as $path) {
        if (file_exists($path) && !is_dir($path)) {
            $filePath = $path;
            break;
        }
    }

    if (!$filePath) {
        $matches = glob(public_path('uploads/' . $filename . '*')) 
                ?: glob(base_path('public_html/uploads/' . $filename . '*'))
                ?: glob(storage_path('app/public/uploads/' . $filename . '*'));
        if (!empty($matches) && file_exists($matches[0]) && !is_dir($matches[0])) {
            $filePath = $matches[0];
        }
    }

    if (!$filePath) {
        abort(404, 'Media file not found');
    }

    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $mimeTypes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jfif' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        'mp4'  => 'video/mp4',
        'mov'  => 'video/quicktime',
        'webm' => 'video/webm',
    ];

    $contentType = $mimeTypes[$ext] ?? (mime_content_type($filePath) ?: 'application/octet-stream');

    return response()->file($filePath, [
        'Content-Type'  => $contentType,
        'Cache-Control' => 'public, max-age=604800, immutable',
    ]);
})->where('filename', '.*');

// Cache Clearing Helper for Deployment
Route::get('/clear-cache', function () {
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    return '<h3>All caches (view, route, config, app cache) have been cleared successfully!</h3><p><a href="/admin/requests/1">Go back to Request 1</a></p>';
});

// ─── GEMINI AI API ────────────────────────────────────────────────────────
Route::post('/api/generate-caption', function (Request $request) {
    $apiKey = \App\Models\Setting::get('gemini_api_key', config('services.gemini.key') ?: env('GEMINI_API_KEY'));

    if (!$apiKey) {
        return response()->json(['error' => 'API Key is missing in .env file or Settings.'], 500);
    }

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent?key=" . $apiKey;

    $promptText = "Write a short, engaging social media caption for this university post:
    Title: {$request->title}
    Description: {$request->description}
    Category: {$request->category}
    Platforms: {$request->platforms}
    Provide ONLY the caption text. No labels, no explanations, just the caption.";

    try {
        $response = Http::withHeaders(['Content-Type' => 'application/json'])
            ->timeout(60)
            ->withoutVerifying()
            ->post($url, [
                'contents' => [
                    ['parts' => [['text' => $promptText]]]
                ],
                'generationConfig' => [
                    'temperature'     => 0.7,
                    'maxOutputTokens' => 500,
                ],
            ]);

        $data = $response->json();

        if ($response->successful() && isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return response()->json(['caption' => trim($data['candidates'][0]['content']['parts'][0]['text'])]);
        }

        return response()->json(['error' => 'Google API Error: ' . ($data['error']['message'] ?? 'Unknown')], 400);

    } catch (\Exception $e) {
        return response()->json(['error' => 'Server Connection Error: ' . $e->getMessage()], 500);
    }
})->middleware('auth.nupost:requestor');


Route::post('/api/bulldog-chat', function (Illuminate\Http\Request $request) {

    $apiKey = \App\Models\Setting::get('groq_api_key', config('services.groq.key') ?: env('GROQ_API_KEY'));

    if (!$apiKey) {
        return response()->json([
            'error' => 'GROQ_API_KEY is missing in .env or Settings — get a free key at https://console.groq.com'
        ], 500);
    }

    $messages = $request->input('messages', []);

    if (empty($messages)) {
        return response()->json(['error' => 'No messages provided.'], 400);
    }

    $candidateModels = array_values(array_unique(array_filter([
        config('services.groq.model'),
        env('GROQ_MODEL'),
        'qwen/qwen3.8-27b',
        'openai/gpt-oss-20b',
        'openai/gpt-oss-120b',
        'llama-3.3-70b-versatile',
        'llama-3.1-8b-instant',
    ])));

    $lastError = null;

    foreach ($candidateModels as $model) {
        try {
            $response = Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type'  => 'application/json',
            ])
            ->timeout(25)
            ->withoutVerifying()
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model'       => $model,
                'messages'    => $messages,
                'max_tokens'  => 350,
                'temperature' => 0.7,
            ]);

            $data = $response->json();

            if ($response->successful() && !empty($data['choices'][0]['message']['content'])) {
                return response()->json([
                    'reply' => trim($data['choices'][0]['message']['content']),
                    'model' => $model,
                ]);
            }

            if (isset($data['error']['message'])) {
                $lastError = $data['error']['message'];
                // If model does not exist or was decommissioned, try next candidate
                if (str_contains(strtolower($lastError), 'does not exist') || str_contains(strtolower($lastError), 'decommissioned')) {
                    continue;
                }
            }
        } catch (\Exception $e) {
            $lastError = $e->getMessage();
        }
    }

    return response()->json(['error' => 'Groq API Error: ' . ($lastError ?? 'Unknown Groq API error')], 400);
})->middleware('auth.nupost:admin');

// ─── MS KAYE'S ROUTES ──────────────────────────────────────────────
use App\Http\Controllers\Kaye\DashboardController as KayeDashboardController;

Route::get('/kaye/login/{token}', [KayeDashboardController::class, 'login'])->name('kaye.login');
Route::get('/kaye/login-expired', [KayeDashboardController::class, 'loginExpired'])->name('kaye.login.expired');

Route::middleware('auth.kaye')->group(function () {
    Route::get('/kaye/dashboard', [KayeDashboardController::class, 'index'])->name('kaye.dashboard');
});