import 'package:flutter/foundation.dart';

/// In-memory session cache for instant (0ms) screen transitions.
///
/// Data in this cache persists in RAM while the app process is alive.
/// When the user closes/swipes away the app from recent apps (killing the process),
/// this memory is cleared so that the skeleton loader shows again on the next cold start.
class AppMemoryCache {
  // ── Live Change Notifiers ──────────────────────────────────────────────────
  static final ValueNotifier<int> requestsRevision = ValueNotifier<int>(0);
  static final ValueNotifier<int> notificationsRevision = ValueNotifier<int>(0);

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
    requestsRevision.value++;
  }

  static void invalidateMessages() {
    messageThreads = null;
  }

  static void invalidateNotifications() {
    notifications = null;
    notificationsRevision.value++;
  }

  // ── Requestor Sequence Map (Request #1, #2...) ───────────────────────────
  static final Map<int, int> userRequestSequenceMap = {};

  static void updateUserRequestSequence(List<Map<String, dynamic>> allUserRequests) {
    final sorted = List<Map<String, dynamic>>.from(allUserRequests)
      ..sort((a, b) {
        final idA = (a['id'] as num?)?.toInt() ?? 0;
        final idB = (b['id'] as num?)?.toInt() ?? 0;
        return idA.compareTo(idB);
      });
    for (int i = 0; i < sorted.length; i++) {
      final id = (sorted[i]['id'] as num?)?.toInt() ?? 0;
      if (id > 0) {
        userRequestSequenceMap[id] = i + 1; // 1-indexed
      }
    }
  }

  static String getDisplayRequestNumber({
    required int id,
    required String rawNumber,
    required bool isAdmin,
  }) {
    if (isAdmin) {
      return rawNumber.isEmpty ? 'REQ-$id' : rawNumber;
    }
    final seq = userRequestSequenceMap[id];
    if (seq != null) {
      return 'Request #$seq';
    }
    return rawNumber.isEmpty ? 'REQ-$id' : rawNumber;
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
    userRequestSequenceMap.clear();
  }
}
