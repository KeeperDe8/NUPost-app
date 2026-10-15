# 🚀 NUPost Project Handover & Technical Master Summary

> **Target Audience:** AI Agents & Developers  
> **Project:** NUPost — Centralized Social Media Request Management System  
> **Client:** NU Lipa Marketing Office  
> **Academic Capstone:** NU Lipa · School of Architecture, Computing, and Engineering (2026)  
> **Production Website & API:** [https://nupost.site](https://nupost.site)  
> **Production API Base:** `https://nupost.site/api`  
> **Local Workspace Path:** `c:\src\nupost_app`

---

## 1. 🌐 GitHub Repositories & Access

The project uses two GitHub repositories:

| Repository | GitHub Remote URL | Default Branch | Description & Role |
| :--- | :--- | :--- | :--- |
| **Main / Mobile Repo** | `https://github.com/KeeperDe8/NUPost-app.git` | `main` | **Primary Monorepo / Flutter App:** Contains the full mobile codebase (`lib/`), assets, Android Gradle configs, release APKs, and the local backend copy (`nupost_laravel-main/`). Local workspace: `c:\src\nupost_app`. |
| **Web Production Repo** | `https://github.com/Nielqttt/nupost.git` | `main` | **Hostinger Web App & Live Backend:** Hosts the production Laravel web application running on Hostinger. Automated GitHub Actions (`.github/workflows/deploy.yml`) triggers on every push to `main` and deploys via SSH to Hostinger's `public_html`. |

### Access & Git Clone Commands:
```bash
# Clone the Main Mobile Repository (Flutter App & Local Backend)
git clone https://github.com/KeeperDe8/NUPost-app.git
cd NUPost-app

# Clone the Web Production Repository (Hostinger Live Server Codebase)
git clone https://github.com/Nielqttt/nupost.git
cd nupost
```

---

## 2. 🛠️ How to Build, Run & Export the System

### Prerequisites
- **Flutter SDK:** `≥ 3.x` (Dart `≥ 3.10`)
- **Android SDK:** API 21+ (JDK 17 recommended)
- **PHP:** `≥ 8.1` & Composer (for local backend development)
- **MySQL:** `≥ 8.0`

---

### Step A: Flutter Mobile App Setup
```powershell
# Navigate to the project root
cd c:\src\nupost_app

# Fetch Flutter packages
flutter pub get

# Verify Flutter setup
flutter doctor
```

---

### Step B: Running the Mobile App (Development)

The mobile app's `lib/services/api_service.dart` defaults directly to the live production server `https://nupost.site/api`.

```powershell
# Run against the Live Production Hostinger Backend (Default)
flutter run

# Or explicitly specifying the API URL:
flutter run --dart-define=API_BASE_URL=https://nupost.site/api

# Run against Localhost Backend on Android Emulator:
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api
```

---

### Step C: How to Build & Export the Release APK

To generate the optimized production Android APK connected to `https://nupost.site`:

```powershell
# Build command (Generates optimized release APK):
flutter build apk --release --dart-define=API_BASE_URL=https://nupost.site/api

# (Optional) If you want split APKs per CPU architecture (ARM64, ARMv7, x86_64):
flutter build apk --release --split-per-abi --dart-define=API_BASE_URL=https://nupost.site/api
```

#### 📁 APK Output Locations:
1. **Default Gradle Build Output:**  
   `c:\src\nupost_app\build\app\outputs\flutter-apk\app-release.apk`
2. **Convenience Root Distribution Copies (Ready to distribute to client/testers):**  
   - `c:\src\nupost_app\nupost-release.apk` (~25.36 MB)
   - `c:\src\nupost_app\app-release.apk` (~25.36 MB)

To refresh the root APK copies after building:
```powershell
Copy-Item "c:\src\nupost_app\build\app\outputs\flutter-apk\app-release.apk" "c:\src\nupost_app\nupost-release.apk" -Force
Copy-Item "c:\src\nupost_app\build\app\outputs\flutter-apk\app-release.apk" "c:\src\nupost_app\app-release.apk" -Force
```

---

### Step D: Running the Backend Locally (Laravel)
```powershell
cd c:\src\nupost_app\nupost_laravel-main
composer install
cp .env.example .env
php artisan key:generate

# Configure DB_DATABASE, DB_USERNAME, DB_PASSWORD in .env
php artisan migrate --seed
php artisan serve   # Runs at http://localhost:8000
```

---

## 3. 🏗️ Architecture & Core System Features

```
NUPost Architecture
├── Flutter Client (Android / Mobile)
│   ├── Presentation: Material 3 + Custom Brand Theme (DM Sans, NU Gold/Blue)
│   ├── State & Auth: SharedPreferences SessionStore (persistent login & OTP)
│   ├── Network: ApiService + NetworkQueueManager (circuit breaker, retry backoff, offline cache)
│   └── Timezone: AppDateTimeUtils (UTC -> Asia/Manila / UTC+8 auto-conversion)
│
├── Production Server (Hostinger Cloud: https://nupost.site)
│   ├── Framework: Laravel 10/11 REST API
│   ├── Live Webhooks & Deployment: GitHub Actions auto-deploy via Nielqttt/nupost (main)
│   ├── Media Pipeline: /api/media.php with triple-fallback & transparent PNG failover
│   └── Database: MySQL (UTC timestamps, relational requests, feedback notes, timeline events)
│
└── External Integrations
    ├── Google Gemini API (AI context-aware caption generation)
    └── Meta Graph API v19+ (Facebook reach, engagement & impression analytics)
```

---

## 4. 🔑 Critical Technical Gotchas & Applied Solutions

Any developer or agent continuing work must be aware of these implemented patterns:

### 1. Timezone Synchronization (`Asia/Manila` / UTC+8)
- **Problem:** MySQL and Laravel store timestamps in UTC without an explicit `Z` or timezone offset. The mobile app previously displayed submission times 8 hours behind, and relative notification times showed "8 hours ago" for just-submitted posts.
- **Solution:** `lib/utils/app_date_time_utils.dart` provides `parseServerDateTime(rawString)`. It checks if the string ends in `Z` or an offset; if missing, it assumes UTC and calls `.toLocal()`.
- **Rule:** Never use raw `DateTime.parse()` on backend date strings. Always use `AppDateTimeUtils.parseServerDateTime()` and format via `AppDateTimeUtils.formatPhilippinesDateTime()`.

### 2. Notification Double-Tap & Slowness Fix
- **Problem:** Rapid multiple taps on a notification card opened multiple overlapping instances of the Message Thread or Request Details screen. The status update was blocking the UI.
- **Solution:** 
  1. Added a navigation lock (`_isNavigating = true`) in `notifications_screen.dart` to drop duplicate taps.
  2. Implemented **optimistic UI updates (0ms)**: the notification is marked read in local state instantly while the API sync runs in the background via `Future.wait`.
  3. Replaced the full-height left border line with a centered, rounded pill indicator (`width: 4, height: 56`) matching the design on `home_screen.dart`.

### 3. Media Upload & Serving Pipeline on Hostinger
- **Problem:** Hostinger file structure splits `public_html/` and Laravel's internal root, causing 404 image errors or `ImageCodecException` in Flutter when images were requested.
- **Solution:**
  - Uploads in `createRequest` and `updateRequest` are saved to `public_path('uploads')` and mirrored to `base_path('../public_html/uploads')`.
  - Normalization: `.jfif` files from Windows / mobile cameras are automatically renamed and converted to `.jpg`.
  - Media Endpoint: `https://nupost.site/api/media.php?file=<filename>` checks all candidate paths (`public_html/uploads`, `public/uploads`, `storage/app/public/uploads`). If missing, it outputs a 1x1 transparent PNG fallback with `Cache-Control: no-cache` so Flutter `Image.network` never crashes.

### 4. Re-Submission Workflow
- When an admin rejects a request, an `admin_feedback` note is attached.
- In `RequestTrackingScreen`, the user sees an **Admin Rejection Reason** alert box and a **Re-Submit Request** button.
- Tapping Re-Submit pre-populates `CreateRequestScreen` in edit mode (`isEdit: true`). Submitting updates the request and resets the status back to `Pending` review.

---

## 5. 📂 Key Files & Directory Cheat Sheet

```
c:\src\nupost_app/
├── lib/
│   ├── main.dart                          # Route definitions & entry point
│   ├── services/
│   │   ├── api_service.dart               # Central REST API handler (defaults to https://nupost.site/api)
│   │   ├── session_store.dart             # SharedPreferences auth state & user model
│   │   └── network_queue_manager.dart     # Circuit breaker, queue & offline cache
│   ├── utils/
│   │   └── app_date_time_utils.dart       # UTC to Asia/Manila time converter
│   ├── screens/
│   │   ├── splash_screen.dart             # Auto-login session restore
│   │   ├── notifications_screen.dart      # Notification list, tap debounce & optimistic read
│   │   ├── requests_screen.dart           # User request list with local Philippine timestamps
│   │   ├── create_request_screen.dart     # New request & re-submission form
│   │   ├── request_tracking_screen.dart   # Status stepper & Admin Rejection Note
│   │   ├── messages_screen.dart           # Chat threads & unread badges
│   │   └── admin/
│   │       └── admin_dashboard_screen.dart # Marketing staff management dashboard
│   └── widgets/
│       ├── floating_message_button.dart   # Global floating message drawer
│       └── skeleton_loader.dart           # Shimmer loading placeholders
├── nupost_laravel-main/                   # Embedded Laravel API backend
├── app-release.apk                        # Production APK binary copy (~25.36 MB)
├── nupost-release.apk                     # Production APK binary copy (~25.36 MB)
├── pubspec.yaml                           # Flutter dependencies
└── README.md                              # Comprehensive capstone documentation
```

---

## 6. 📋 Brief for Web Repository Agent (`Nielqttt/nupost`)

> **Target Repository:** `https://github.com/Nielqttt/nupost.git` (Branch: `main`)  
> **Target File:** `app/Http/Controllers/Api/LegacyMobileApiController.php`  
> **Target Method:** `public function adminRequests(Request $request): JsonResponse`

### Objective
Update the `adminRequests` API endpoint so that it automatically calculates SLA slack days, tags urgent and at-risk requests, and returns the admin queue auto-sorted by urgency.

### 1. SLA Lead Times by Category
In the request mapping logic (`->map(function ($r) { ... })`), determine `$slaDays`:
```php
$category = (string) ($r->category ?? 'General');
$slaDays = match($category) {
    'Event Documentation' => 30,
    'New Campaign / Creative Concept' => 10,
    'Multiple Collaterals / Tarpaulins' => 5,
    'Standard PubMat' => 3,
    'Template-Based PubMat' => 2,
    default => 1,
};
```

### 2. Slack & Urgency Calculation Formula
```php
$prefDate = $r->preferred_date ? \Carbon\Carbon::parse($r->preferred_date)->startOfDay() : now()->addDays(3);
$latestStart = $prefDate->copy()->subDays($slaDays);
$today = now()->startOfDay();
$slackDays = (int) $today->diffInDays($latestStart, false); // negative = overdue

$isAtRisk = $slackDays < 0;
$isDueSoon = $slackDays >= 0 && $slackDays <= 1;

$prioStr = strtolower(trim((string) ($r->priority ?? 'Low')));
$prioWeight = match($prioStr) {
    'urgent' => 0,
    'high' => 1,
    'medium' => 2,
    default => 3,
};
```

### 3. Return Payload Fields
Include in the mapped item:
```php
'slack_days'   => $slackDays,
'is_at_risk'   => $isAtRisk,
'is_due_soon'  => $isDueSoon,
'_prio_weight' => $prioWeight,
```

### 4. Auto-Sorting Queue
Sort before returning the JSON response:
```php
->sort(function ($a, $b) {
    // 1. Tightest/negative slack first
    if ($a['slack_days'] !== $b['slack_days']) {
        return $a['slack_days'] <=> $b['slack_days'];
    }
    // 2. Higher priority first (Urgent=0, High=1, Med=2, Low=3)
    if ($a['_prio_weight'] !== $b['_prio_weight']) {
        return $a['_prio_weight'] <=> $b['_prio_weight'];
    }
    // 3. Newest submissions first
    return strcmp($b['created_at'], $a['created_at']);
})
->values();
```

### 5. Automated Hostinger Deployment
Pushing commits to the `main` branch of `Nielqttt/nupost` triggers GitHub Actions (`.github/workflows/deploy.yml`) which syncs the changes to `https://nupost.site` via SSH.

---

## 7. 🛡️ 24-Hour Exact Duplicate Hash Check Protection

> **Applied to:** Both Mobile API (`LegacyMobileApiController.php`) and Web Requestor (`RequestController.php`)  
> **Status:** Live on `Nielqttt/nupost` (`main`) and `KeeperDe8/NUPost-app` (`main`)

### Objective
Prevent accidental spam or double submissions when a user submits identical request content multiple times within 24 hours.

### Implementation Logic
```php
// 🛡️ Exact Duplicate Hash Check (within 24h)
$contentHash = md5(strtolower(trim($title)) . '|' . strtolower(trim($description)));
$recentDuplicates = DB::table('post_requests')
    ->where('status', '!=', 'Rejected')
    ->where('created_at', '>=', now()->subHours(24))
    ->get();

foreach ($recentDuplicates as $dup) {
    $dupHash = md5(strtolower(trim($dup->title ?? '')) . '|' . strtolower(trim($dup->description ?? '')));
    if ($dupHash === $contentHash) {
        return response()->json([
            'success' => false,
            'message' => 'Duplicate submission detected. An identical request was submitted within the last 24 hours.',
        ], 422);
    }
}
```
- Only matches against non-rejected requests within the last 24 hours.
- Does NOT apply arbitrary rate limits or quotas (users can still submit different requests freely).


