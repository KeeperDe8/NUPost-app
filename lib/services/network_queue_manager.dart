import 'dart:async';
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

/// Manages network queues, timeout tracking in the last 60 seconds,
/// adaptive connection timeouts, and response caching for offline / poor Wi-Fi.
class NetworkQueueManager {
  static final NetworkQueueManager instance = NetworkQueueManager._internal();
  NetworkQueueManager._internal();

  // ── In-flight Request Queue ───────────────────────────────────────────────
  int _inFlightRequests = 0;
  int get inFlightRequests => _inFlightRequests;

  // ── 60-Second Sliding Window for Timeouts ──────────────────────────────────
  final List<DateTime> _recentTimeouts = [];

  // In-memory cache for ultra-fast instant UI rendering
  final Map<String, Map<String, dynamic>> _memCache = {};

  void trackRequestStart() {
    _inFlightRequests++;
  }

  void trackRequestEnd() {
    if (_inFlightRequests > 0) {
      _inFlightRequests--;
    }
  }

  /// Records a timeout event and prunes events older than 60 seconds.
  void recordTimeout() {
    final now = DateTime.now();
    _recentTimeouts.add(now);
    _pruneTimeouts(now);
  }

  /// Records a successful response, clearing historical timeout pressure.
  void recordSuccess() {
    if (_recentTimeouts.isNotEmpty) {
      _recentTimeouts.removeAt(0);
    }
  }

  void _pruneTimeouts(DateTime now) {
    _recentTimeouts.removeWhere(
      (t) => now.difference(t).inSeconds > 60,
    );
  }

  /// Number of timeouts that occurred in the last 60 seconds.
  int get timeoutsInLastMinute {
    _pruneTimeouts(DateTime.now());
    return _recentTimeouts.length;
  }

  /// Indicates degraded network conditions (multiple timeouts or queue backup).
  bool get isDegraded {
    return timeoutsInLastMinute >= 2 || _inFlightRequests > 3;
  }

  /// Indicates severe network breakdown (repeated timeouts in succession).
  bool get isOfflineMode {
    return timeoutsInLastMinute >= 4;
  }

  /// Adaptive timeout duration:
  /// - Healthy network: Short 6-second timeout for quick failure detection & fast retry.
  /// - Degraded/Slow network: 10-second timeout to allow slow packets to complete.
  Duration get adaptiveTimeout {
    if (isDegraded) {
      return const Duration(seconds: 10);
    }
    return const Duration(seconds: 6);
  }

  /// Adaptive retries:
  /// - Faster/Healthy network: Up to 2 retries (rapid recovery from transient loss).
  /// - Degraded network: 1 retry (avoid spamming a congested link).
  int get maxRetries {
    if (isDegraded) {
      return 1;
    }
    return 2;
  }

  // ── Local Response Cache (Offline / Stale-While-Revalidate) ────────────────

  String _cacheKey(String uri) => 'nupost_cache_${uri.hashCode}';

  /// Saves a successful JSON response to in-memory and persistent disk cache.
  Future<void> cacheResponse(String uri, Map<String, dynamic> data) async {
    _memCache[uri] = data;
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_cacheKey(uri), jsonEncode(data));
    } catch (_) {}
  }

  /// Retrieves cached response instantly (from memory first, then disk).
  Future<Map<String, dynamic>?> getCachedResponse(String uri) async {
    if (_memCache.containsKey(uri)) {
      return _memCache[uri];
    }
    try {
      final prefs = await SharedPreferences.getInstance();
      final raw = prefs.getString(_cacheKey(uri));
      if (raw != null && raw.isNotEmpty) {
        final decoded = jsonDecode(raw);
        if (decoded is Map<String, dynamic>) {
          _memCache[uri] = decoded;
          return decoded;
        }
      }
    } catch (_) {}
    return null;
  }

  /// Synchronous memory-only cache read for 0ms instant widget initialization.
  Map<String, dynamic>? getSyncCached(String uri) {
    return _memCache[uri];
  }

  /// Completely flushes in-memory and on-disk response caches (on logout or user switch).
  Future<void> clearCache() async {
    _memCache.clear();
    try {
      final prefs = await SharedPreferences.getInstance();
      final keys = prefs.getKeys().where((k) => k.startsWith('nupost_cache_')).toList();
      for (final key in keys) {
        await prefs.remove(key);
      }
    } catch (_) {}
  }
}
