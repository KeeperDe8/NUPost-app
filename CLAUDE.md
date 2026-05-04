# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run app (Android emulator)
flutter run

# Run on physical device on same LAN
flutter run --dart-define=API_BASE_URL=http://192.168.1.x/nupost-main/api

# Build release APK
flutter build apk --release
# Output: build/app/outputs/flutter-apk/app-release.apk

# Analyze / lint
flutter analyze

# Install dependencies
flutter pub get

# Run tests / single test
flutter test
flutter test test/widget_test.dart
```

## Architecture

**NUPost** is a Flutter mobile app (portrait-locked, Android) for submitting and tracking social media post requests at a university (National University). Backend is PHP on Hostinger (`https://nupost.site/api`).

### Navigation

App flow: `SplashScreen` → `LoginScreen` (or `OtpScreen`) → `MainShell`.

**`lib/main_shell.dart`** is the persistent shell for all 5 main tabs. It owns:
- `AnimatedSwitcher` that swaps screen content with a fade+slide transition
- `AppBottomNav` in the Scaffold's `bottomNavigationBar` slot (stays locked, never animates)
- `FloatingMessageButton` FAB

Tab indices: 0=Home, 1=Requests, 2=Create, 3=Notifications, 4=Profile.

To switch tabs from any descendant widget: `MainShell.switchTo(context, index)` — uses `findAncestorStateOfType`.

Sub-screens pushed on top of the shell (Messages, Calendar, RequestTracking) use `Navigator.pushNamed` and own their own `AppBottomNav(currentIndex: -1)`.

**`lib/app_bottom_nav.dart`** — accepts an optional `ValueChanged<int>? onTap` callback. When provided (shell context), it calls the callback instead of navigating. When absent (sub-screen context), falls back to `pushReplacementNamed`.

**`lib/main.dart`** routes: all 5 tab routes map to `MainShell(initialIndex: N)`. `/messages` and `/calendar` remain separate routes pushed on top.

### Backend & Services

**`lib/services/api_service.dart`** — all HTTP calls as static methods. Production URL is `https://nupost.site/api` (Hostinger). Override at build time with `--dart-define=API_BASE_URL=<url>`. The backend is a separate PHP project not in this repo.

**`lib/services/session_store.dart`** — in-memory singleton (`static` fields). Holds `userId`, `name`, `email`. No persistence — users log in each launch.

**`lib/services/chat_read_store.dart`** — uses `SharedPreferences` to persist the last-read message ID per request thread (the only persistent store in the app).

### Screens

All screens live in `lib/screens/`. Each is a `StatefulWidget` that calls `ApiService` directly in `initState`/event handlers and stores results in `setState`. No state management library.

Every screen has a consistent entry animation pattern:
```dart
late final AnimationController _entryCtrl;
late final Animation<double> _entryFade;
late final Animation<Offset> _entrySlide;
// initState: _entryCtrl = AnimationController(vsync: this, duration: 650ms)..forward()
// build: FadeTransition(opacity: _entryFade, child: SlideTransition(position: _entrySlide, ...))
```

### Theme

All color/radius tokens live in **`lib/theme/app_theme.dart`**:
- `AppColors` — full palette (primary `#002366`, accent `#3B6EF5`, pageBg `#E8ECF4`, status colors)
- `AppRadius` — `sm/md/lg/xl` border radius constants
- `ChatColors` / `GlassColors` — dark palette for the Messages screen
- `AppTheme.light` — Material3 theme with DM Sans font
- `AppTheme.chatDark` / `AppTheme.chatLight` — used by MessageThreadScreen

Always use `AppColors.*` constants, not raw hex values.

### Widgets

- **`lib/app_bottom_nav.dart`** — shared nav bar; pass `onTap` in shell context
- **`lib/widgets/floating_message_button.dart`** — FAB; polls unread count every 10s; `bottom: 108` default positions it above the nav bar
- **`lib/widgets/skeleton_loader.dart`** — shimmer placeholder while loading
- **`lib/widgets/intensity_date_picker.dart`** — custom date picker that shows post volume heatmap (used in CreateRequestScreen)

### Assets

`assets/nu_shield.png` and `assets/bg.png` declared in `pubspec.yaml`. Screens fall back to a `CustomPaint` shield if the asset is missing.
