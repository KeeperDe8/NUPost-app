# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run app (Android emulator)
flutter run

# Run with a custom API base URL (e.g., physical device on same LAN)
flutter run --dart-define=API_BASE_URL=http://192.168.1.x/nupost-main/api

# Analyze / lint
flutter analyze

# Install dependencies
flutter pub get

# Run tests
flutter test

# Run a single test file
flutter test test/widget_test.dart
```

## Architecture

**NUPost** is a Flutter mobile app for submitting and tracking social media post requests at a university (National University). It talks to a PHP/Laravel backend served via Laragon (local dev).

### Backend connection

`lib/services/api_service.dart` — all HTTP calls live here as static methods. Default base URL is `http://10.0.2.2/nupost-main/api` (Android emulator localhost proxy). Override at build time with `--dart-define=API_BASE_URL=<url>`. The backend is a separate repo/project not in this directory.

### Session management

`lib/services/session_store.dart` — in-memory singleton (`static` fields). Holds `userId`, `name`, `email` for the duration of the app session. No persistence; users must log in each launch.

### Navigation

`main.dart` defines named routes. `AppBottomNav` (`lib/app_bottom_nav.dart`) is the shared bottom bar used by all main screens — pass `currentIndex` (0=Home, 1=Requests, 2=Create, 3=Notifications, 4=Profile).

App flow: `SplashScreen` → `LoginScreen` → `HomeScreen` (and sibling screens via bottom nav).

### Screens

All screens are in `lib/screens/`. Each screen is a `StatefulWidget` that calls `ApiService` directly in `initState`/event handlers and stores results in local `setState`. There is no state management library (no Provider, Riverpod, BLoC, etc.).

### Widgets

- `AppBottomNav` — shared bottom navigation bar
- `FloatingMessageButton` — FAB that polls unread message count every 10 seconds
- `SkeletonLoader` (`lib/widgets/skeleton_loader.dart`) — shimmer placeholder while loading

### Theme

- Font: DM Sans (`google_fonts`)
- Primary accent: `#3B6EF5`
- Page background: `#E8ECF4`
- Nav bar background: `#111827`
- Splash/brand background: `#29286A` (NU navy), gold: `#FFC72C`
- App is locked to portrait mode

### Assets

`assets/nu_shield.png` and `assets/bg.png` — declared in `pubspec.yaml`. Screens fall back to a `CustomPaint` shield if the asset is missing.
