# NUPost &nbsp;·&nbsp; ![Flutter](https://img.shields.io/badge/Flutter-3.x-02569B?logo=flutter&logoColor=white) ![Dart](https://img.shields.io/badge/Dart-3.10-0175C2?logo=dart&logoColor=white) ![Laravel](https://img.shields.io/badge/Laravel-API-FF2D20?logo=laravel&logoColor=white) ![License](https://img.shields.io/badge/License-Academic-gold)

<div align="center">

<img src="assets/nu_shield.png" width="90" alt="NU Lipa Shield"/>

### **Optimizing University Social Media with Centralized Request Management**

*A Capstone Project — Bachelor of Science in Information Technology*  
*NU Lipa · School of Architecture, Computing, and Engineering · 2026*

---

**[✦ Overview](#-overview) &nbsp;·&nbsp; [Features](#-features) &nbsp;·&nbsp; [Tech Stack](#-tech-stack) &nbsp;·&nbsp; [Getting Started](#-getting-started) &nbsp;·&nbsp; [Project Structure](#-project-structure) &nbsp;·&nbsp; [Team](#-team)**

</div>

---

## ✦ Overview

**NUPost** is a mobile-first social media posting request management system built for the **NU Lipa Marketing Office**. It replaces fragmented multi-platform workflows (Viber, Messenger, Outlook, email) with a single, governed, transparent pipeline — from request submission all the way to published post.

> _"No centralized platform existed specifically for managing posting requests within an academic governance framework."_

NUPost solves this by unifying **request submission**, **structured approvals**, **AI-assisted caption generation**, **calendar-based scheduling**, and **Meta Graph API analytics** into one cohesive system.

---

## ✦ Features

| # | Feature | Description |
|---|---------|-------------|
| 1 | **📋 Posting Request Submission** | Standardized forms with title, description, platform selection, category & priority tagging, preferred date, and media upload (up to 4 files) |
| 2 | **✅ Automated Approval Workflow** | Status tracking across Pending → Under Review → Approved → Posted with real-time notifications |
| 3 | **✨ AI Caption Generator** | Google Gemini API generates context-aware, editable caption suggestions based on event details and uploaded media |
| 4 | **📅 Post Calendar** | Visual calendar showing scheduled posts, conflict detection, and public/private toggle for department-wide visibility |
| 5 | **🔔 Real-Time Notifications** | Push alerts for every status change with grouped notification history and unread counters |
| 6 | **💬 Comment Threads** | Per-request admin↔requester messaging with real-time polling |
| 7 | **📊 Analytics & Reports** | Meta Graph API integration — reach, engagement, and engagement rate per post *(web admin panel)* |
| 8 | **🔐 Role-Based Access** | Separate flows for **Requestors** (mobile) and **Marketing Staff** (web admin) |

---

## ✦ Tech Stack

### Mobile Application
```
Flutter 3.x (Dart 3.10)   Cross-platform mobile framework
DM Sans / Google Fonts     Typography
HTTP package               REST API communication
File Picker                Media upload (images & video)
Shimmer                    Skeleton loading states
```

### Backend API
```
Laravel (PHP)              MVC REST API
MySQL                      Relational database
Laravel Sanctum            Auth & session tokens
Google Gemini API          AI caption generation
Meta Graph API v19+        Facebook post analytics
```

### Infrastructure & Tools
```
GitHub                     Version control & collaboration
Figma                      UI/UX design & prototyping
Android Studio             Emulator testing
Visual Studio Code         Primary IDE
```

---

## ✦ Getting Started

### Prerequisites

| Tool | Version |
|------|---------|
| Flutter SDK | `≥ 3.x` (stable channel) |
| Dart | `≥ 3.10` |
| Android SDK | API 21+ |
| PHP | `≥ 8.1` |
| Composer | Latest |
| MySQL | `≥ 8.0` |

---

### 1 · Clone the Repository

```bash
git clone https://github.com/your-org/nupost_app.git
cd nupost_app
```

### 2 · Install Flutter Dependencies

```bash
flutter pub get
```

### 3 · Configure API Base URL

The app auto-detects between **Laravel** (`http://10.0.2.2:8000/api`) and **Legacy PHP** (`http://10.0.2.2/nupost-main/api`).

To force a specific URL, use a `--dart-define` flag at run time:

```bash
# Android Emulator (Laravel)
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api

# Physical Device (use your machine's LAN IP)
flutter run --dart-define=API_BASE_URL=http://192.168.1.x:8000/api
```

### 4 · Run the App

```bash
flutter run                  # debug mode
flutter run --release        # release mode
```

---

### Backend Setup (Laravel)

```bash
cd nupost-api
composer install
cp .env.example .env
php artisan key:generate

# Configure your .env database credentials, then:
php artisan migrate --seed
php artisan serve              # runs on http://localhost:8000
```

Add these keys to `.env`:

```env
GEMINI_API_KEY=your_google_gemini_api_key
META_GRAPH_TOKEN=your_meta_graph_api_token
META_PAGE_ID=your_facebook_page_id
```

---

## ✦ Project Structure

```
nupost_app/
├── lib/
│   ├── main.dart                      # App entry point & routes
│   ├── app_bottom_nav.dart            # Shared navigation bar
│   ├── screens/
│   │   ├── splash_screen.dart
│   │   ├── login_screen.dart
│   │   ├── register_screen.dart
│   │   ├── otp_screen.dart
│   │   ├── home_screen.dart
│   │   ├── requests_screen.dart
│   │   ├── create_request_screen.dart
│   │   ├── request_tracking_screen.dart
│   │   ├── notifications_screen.dart
│   │   ├── profile_screen.dart
│   │   ├── edit_profile_screen.dart
│   │   ├── account_security_screen.dart
│   │   ├── post_calendar_screen.dart
│   │   ├── messages_screen.dart
│   │   └── message_thread_screen.dart
│   ├── services/
│   │   ├── api_service.dart           # All HTTP calls
│   │   └── session_store.dart         # User session management
│   ├── theme/
│   │   └── app_theme.dart             # Colors, typography, theme data
│   └── widgets/
│       ├── floating_message_button.dart
│       ├── intensity_date_picker.dart
│       └── skeleton_loader.dart
├── assets/
│   ├── nu_shield.png
│   └── bg.png
├── pubspec.yaml
└── README.md
```

---

## ✦ Navigation Map

```
SplashScreen
    ├── LoginScreen ──── RegisterScreen ──── OtpScreen
    │       └── (Unverified) ─────────────── OtpScreen
    └── HomeScreen
                    ├── RequestsScreen ── RequestTrackingScreen
                    ├── CreateRequestScreen
                    ├── NotificationsScreen
                    ├── ProfileScreen ── EditProfileScreen
                    │                └── AccountSecurityScreen
                    ├── PostCalendarScreen
                    └── MessagesScreen ── MessageThreadScreen
```

---

## ✦ Key Dependencies

```yaml
dependencies:
  flutter:
    sdk: flutter
  http: ^1.2.2              # REST API calls
  file_picker: ^8.1.2       # Media upload
  google_fonts: ^8.0.2      # DM Sans typography
  shimmer: ^3.0.0           # Skeleton loaders
  cupertino_icons: ^1.0.8
```

---

## ✦ Database

The project ships with a MySQL dump at `nupost_laravel.sql`. Import it to get started:

```bash
mysql -u root -p nupost_laravel < nupost_laravel.sql
```

**Core Tables**

| Table | Purpose |
|-------|---------|
| `users` | Requestor accounts with role, org, phone |
| `otp_codes` | OTP verification codes and expiration tracking |
| `post_requests` | All posting requests with status, platform, caption |
| `request_comments` | Admin ↔ requester message threads |
| `request_activity` | Full audit log per request |
| `notifications` | Per-user notification records |
| `login_attempts` | Security audit log |

---

## ✦ ISO/IEC 25010 Evaluation

This system is evaluated against the following quality characteristics:

```
✦ Functional Suitability    All features operate as specified
✦ Performance Efficiency    Response times under normal and peak load  
✦ Usability                 Learnability, operability, UI aesthetics
✦ Reliability               Fault tolerance, availability
✦ Security                  Authentication, authorization, data integrity
✦ Accessibility             Support for diverse users and devices
```

---

## ✦ Team

| Name | Role |
|------|------|
| **Guce, Denmark I.** | Project Manager · Documentation Specialist |
| **Baral, Mike Roan B.** | UI/UX Designer · Back-End Developer |
| **Cerezo, Anielle Dane B.** | Front-End Developer · Software Tester |
| **Magat, Jamel Kim T.** | Front-End Developer · Back-End Developer |

**Adviser:** Mr. Jei Q. Pastrana  
**Client:** Ms. Kayecelyn Desingaño — NU Lipa Marketing Coordinator

---

## ✦ Related Systems Comparison

| Feature | Outlook | Jira | Trello | Hootsuite | **NUPost** |
|---------|:-------:|:----:|:------:|:---------:|:---------:|
| Priority & Category Tagging | ✓ | ✓ | ✓ | ✓ | **✓** |
| Automated Approval Workflow | ✗ | ✓ | ✗ | ✗ | **✓** |
| AI Caption Generator | ✗ | ✗ | ✗ | ✗ | **✓** |
| Analytics & Reports | ✗ | ✓ | ✗ | ✓ | **✓** |
| Template-Driven Submissions | ✓ | ✓ | ✓ | ✓ | **✓** |
| Academic Governance Context | ✗ | ✗ | ✗ | ✗ | **✓** |

---

## ✦ License

This project is developed as an academic capstone for **NU Lipa — School of Architecture, Computing, and Engineering** in partial fulfillment of the requirements for the degree **Bachelor of Science in Information Technology with Specialization in Mobile and Web Applications**.

All rights reserved · February 2026

---

<div align="center">

**NUPost** · NU Lipa Marketing Office · Lipa City, Batangas, Philippines

*Built with Flutter · Powered by Laravel · AI by Google Gemini · Analytics by Meta Graph API*

</div>
