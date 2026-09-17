/// In-memory session cache for instant (0ms) screen transitions.
///
/// Data in this cache persists in RAM while the app process is alive.
/// When the user closes/swipes away the app from recent apps (killing the process),
/// this memory is cleared so that the skeleton loader shows again on the next cold start.
class AppMemoryCache {
  // ── Requests Cache ────────────────────────────────────────────────────────
  static List<Map<String, dynamic>>? requests;
  static bool get hasRequests => requests != null;

  // ── Home Stats & Recent Requests Cache ────────────────────────────────────
  static Map<String, dynamic>? homeStats;
  static List<Map<String, dynamic>>? homeRecentRequests;
  static bool get hasHomeData => homeRecentRequests != null;

  // ── Notifications Cache ───────────────────────────────────────────────────
  static List<Map<String, dynamic>>? notifications;
  static int notificationsUnreadCount = 0;
  static bool get hasNotifications => notifications != null;

  // ── Messages Cache ────────────────────────────────────────────────────────
  static List<Map<String, dynamic>>? messageThreads;
  static bool get hasMessageThreads => messageThreads != null;

  // ── Calendar Cache ────────────────────────────────────────────────────────
  static List<Map<String, dynamic>>? calendarPosts;
  static int? calendarMonth;
  static int? calendarYear;
  static bool hasCalendar(int month, int year) =>
      calendarPosts != null && calendarMonth == month && calendarYear == year;

  // ── Invalidation (Triggered when creating/editing requests) ─────────────────
  static void invalidateRequests() {
    requests = null;
    homeRecentRequests = null;
    homeStats = null;
    calendarPosts = null;
  }

  static void invalidateMessages() {
    messageThreads = null;
  }

  static void invalidateNotifications() {
    notifications = null;
  }

  // ── Complete Purge (On Logout) ────────────────────────────────────────────
  static void clear() {
    requests = null;
    homeStats = null;
    homeRecentRequests = null;
    notifications = null;
    notificationsUnreadCount = 0;
    messageThreads = null;
    calendarPosts = null;
    calendarMonth = null;
    calendarYear = null;
  }
}
