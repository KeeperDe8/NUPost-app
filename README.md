# NUPost &nbsp;·&nbsp; ![Flutter](https://img.shields.io/badge/Flutter-3.x-02569B?logo=flutter&logoColor=white) ![Dart](https://img.shields.io/badge/Dart-3.10-0175C2?logo=dart&logoColor=white) ![Laravel](https://img.shields.io/badge/Laravel-API-FF2D20?logo=laravel&logoColor=white) ![Hostinger](https://img.shields.io/badge/Hostinger-Live-7209B7?logo=hostinger&logoColor=white) ![License](https://img.shields.io/badge/License-Academic-gold)

<div align="center">

<img src="assets/nu_shield.png" width="90" alt="NU Lipa Shield"/>

### **Optimizing University Social Media with Centralized Request Management**

*A Capstone Project — Bachelor of Science in Information Technology*  
*NU Lipa · School of Architecture, Computing, and Engineering · 2026*  
🌐 **Live Production API:** [https://nupost.site](https://nupost.site)

---

**[✦ Overview](#-overview) &nbsp;·&nbsp; [Features](#-features) &nbsp;·&nbsp; [Tech Stack](#-tech-stack) &nbsp;·&nbsp; [Getting Started](#-getting-started) &nbsp;·&nbsp; [Production & APK](#-production--apk-export) &nbsp;·&nbsp; [Project Structure](#-project-structure) &nbsp;·&nbsp; [Team](#-team)**

</div>

---

## ✦ Overview

**NUPost** is a mobile-first social media posting request management system built for the **NU Lipa Marketing Office**. It replaces fragmented multi-platform workflows (Viber, Messenger, Outlook, email) with a single, governed, transparent pipeline — from request submission all the way to published post.

> _"No centralized platform existed specifically for managing posting requests within an academic governance framework."_

NUPost solves this by unifying **request submission**, **structured approvals & admin rejection feedback**, **re-submission workflow**, **AI-assisted caption generation**, **calendar-based scheduling**, and **Meta Graph API analytics** into one cohesive system.

---

## ✦ Features

| # | Feature | Description |
|---|---------|-------------|
| 1 | **📋 Posting Request Submission** | Standardized forms with title, description, platform selection, category & priority tagging, preferred date, and media upload |
| 2 | **✅ Automated Approval Workflow** | Status tracking across `Pending` → `Under Review` → `Approved` / `Posted` / `Rejected` with real-time notifications |
| 3 | **🔄 Re-submission & Rejection Feedback** | Admins can reject with feedback notes. Requestors receive an **Admin Rejection Reason** card, edit details/media, and **Re-submit** back to `Pending` review |
| 4 | **✨ AI Caption Generator** | Google Gemini API generates context-aware, editable caption suggestions based on event details and uploaded media |
| 5 | **📅 Post Calendar** | Visual calendar showing scheduled posts, conflict detection, and public/private toggle for department-wide visibility |
| 6 | **🔔 Real-Time Notifications** | Push alerts for every status change with grouped notification history and unread counters |
| 7 | **💬 Floating Chat & Messages** | Floating message drawer and per-request admin ↔ requester message threads |
| 8 | **📊 Admin Dashboard & Analytics** | Staff status actions, request review filters, and Meta Graph API reach/engagement tracking |
| 9 | **🔐 Role-Based Access** | Governed views tailored for **Requestors** (mobile request flow) and **Marketing Staff / Admins** |

---

## ✦ Tech Stack

### Mobile Application
```
Flutter 3.x (Dart 3.10)   Cross-platform mobile framework
DM Sans / Google Fonts     Typography & modern UI styling
HTTP package               REST API communication
File Picker                Media upload (images & video)
Shimmer                    Skeleton loading states
```

### Backend API & Production Hosting
```
Laravel (PHP)              MVC REST API backend
Hostinger                  Production cloud hosting (https://nupost.site)
MySQL                      Relational database
Laravel Sanctum            Auth & session security
Google Gemini API          AI caption generation
Meta Graph API v19+        Facebook post analytics
```

### Infrastructure & Tools
```
GitHub                     Version control & collaboration
Figma                      UI/UX design & prototyping
Android Studio             Emulator testing & APK bundling
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

### 3 · Run the App (Local / Emulator)

```bash
# Android Emulator (Laravel local backend)
flutter run --dart-define=API_BASE_URL=http://10.0.2.2:8000/api

# Connect directly to Production Hostinger Backend
flutter run --dart-define=API_BASE_URL=https://nupost.site/api
```

---

## ✦ Production & APK Export

### Building the Release APK (Connected to Hostinger)

To export the production release APK configured for **`https://nupost.site`**:

```powershell
flutter build apk --release --dart-define=API_BASE_URL=https://nupost.site/api
```

#### 📁 APK Output Path:
```
build/app/outputs/flutter-apk/app-release.apk
```

---

## ✦ Backend Setup (Laravel / Hostinger)

```bash
cd nupost_laravel-main
composer install
cp .env.example .env
php artisan key:generate

# Configure database credentials in .env, then:
php artisan migrate --seed
php artisan serve              # runs on http://localhost:8000
```

Required `.env` variables:

```env
APP_URL=https://nupost.site
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
│   ├── main_shell.dart                # Dynamic role shell & navigation container
│   ├── app_bottom_nav.dart            # Custom bottom navigation bar
│   ├── screens/
│   │   ├── splash_screen.dart
│   │   ├── login_screen.dart
│   │   ├── register_screen.dart
│   │   ├── otp_screen.dart
│   │   ├── home_screen.dart
│   │   ├── requests_screen.dart
│   │   ├── create_request_screen.dart # Request creation & Re-submission form
│   │   ├── request_tracking_screen.dart # Status tracking & Admin Rejection Note
│   │   ├── notifications_screen.dart
│   │   ├── profile_screen.dart
│   │   ├── edit_profile_screen.dart
│   │   ├── account_security_screen.dart
│   │   ├── post_calendar_screen.dart
│   │   ├── messages_screen.dart
│   │   ├── message_thread_screen.dart
│   │   └── admin/
│   │       └── admin_dashboard_screen.dart # Admin management dashboard
│   ├── services/
│   │   ├── api_service.dart           # Production & local REST API handler
│   │   ├── session_store.dart         # Role & session management
│   │   └── chat_read_store.dart       # Read state persistence
│   ├── theme/
│   │   └── app_theme.dart             # DM Sans typography & color system
│   └── widgets/
│       ├── floating_message_button.dart
│       ├── intensity_date_picker.dart
│       ├── media_preview_gallery.dart
│       └── skeleton_loader.dart
├── assets/
│   ├── nu_shield.png
│   └── bg.png
├── nupost_laravel-main/              # Laravel REST API backend codebase
├── pubspec.yaml
└── README.md
```

---

## ✦ Navigation Map

```
SplashScreen
    ├── LoginScreen ──── RegisterScreen ──── OtpScreen
    │       └── (Unverified) ─────────────── OtpScreen
    └── MainShell
            ├── Requestor Mode:
            │       ├── HomeScreen
            │       ├── RequestsScreen ── RequestTrackingScreen (With Re-submit & Rejection Reason)
            │       ├── CreateRequestScreen (New & Edit/Resubmit)
            │       ├── NotificationsScreen
            │       ├── ProfileScreen ── EditProfileScreen / AccountSecurityScreen
            │       └── MessagesScreen ── MessageThreadScreen
            └── Admin Mode:
                    └── AdminDashboardScreen ── RequestTrackingScreen (Admin Status Actions)
```

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
| **Baral, Mike Roan M.** | UI/UX Designer · Back-End Developer |
| **Cerezo, Anielle Dane B.** | Front-End Developer · Software Tester |
| **Magat, Jamel Kim T.** | Front-End Developer · Back-End Developer |

**Adviser:** Mr. Jei Q. Pastrana  
**Client:** Ms. Kayecelyn Desingaño — NU Lipa Marketing Coordinator

---

## ✦ License

This project is developed as an academic capstone for **NU Lipa — School of Architecture, Computing, and Engineering** in partial fulfillment of the requirements for the degree **Bachelor of Science in Information Technology with Specialization in Mobile and Web Applications**.

All rights reserved · 2026

---

<div align="center">

**NUPost** · NU Lipa Marketing Office · Lipa City, Batangas, Philippines

*Built with Flutter · Powered by Laravel & Hostinger · AI by Google Gemini · Analytics by Meta Graph API*

</div>
