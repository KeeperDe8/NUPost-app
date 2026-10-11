class AppDateTimeUtils {
  /// Parses any server timestamp string (UTC with or without Z/offset, ISO-8601, etc.)
  /// and accurately converts it to the user's local phone time.
  static DateTime parseServerDateTime(dynamic raw) {
    if (raw == null) return DateTime.now();
    final str = raw.toString().trim();
    if (str.isEmpty) return DateTime.now();

    // 1. If it already has ISO-8601 'Z' or explicit timezone offset (+08:00 / -05:00)
    if (str.endsWith('Z') || RegExp(r'[+-]\d{2}:?\d{2}$').hasMatch(str)) {
      final parsed = DateTime.tryParse(str);
      if (parsed != null) return parsed.toLocal();
    }

    // 2. MySQL / Laravel format without timezone offset (e.g. "2026-10-11 15:08:45"):
    // The server stores timestamps in UTC, so append 'Z' to parse as UTC then convert to local.
    final withZ = '${str.replaceAll(' ', 'T')}Z';
    final parsedUtc = DateTime.tryParse(withZ);
    if (parsedUtc != null) {
      return parsedUtc.toLocal();
    }

    return (DateTime.tryParse(str) ?? DateTime.now()).toLocal();
  }

  /// Formats raw timestamp as local "YYYY-MM-DD HH:mm:ss" matching the requested display.
  static String formatSubmitted(dynamic raw) {
    if (raw == null) return '—';
    final str = raw.toString().trim();
    if (str.isEmpty) return '—';

    try {
      final dt = parseServerDateTime(str);
      final y = dt.year;
      final m = dt.month.toString().padLeft(2, '0');
      final d = dt.day.toString().padLeft(2, '0');
      final hh = dt.hour.toString().padLeft(2, '0');
      final mm = dt.minute.toString().padLeft(2, '0');
      final ss = dt.second.toString().padLeft(2, '0');
      return '$y-$m-$d $hh:$mm:$ss';
    } catch (_) {
      return str;
    }
  }

  /// Formats relative time ("Just now", "2m ago", "1h ago", etc.) safely handling clock skews.
  static String formatTimeAgo(DateTime dt) {
    final now = DateTime.now();
    final diff = now.difference(dt);
    if (diff.isNegative || diff.inSeconds < 45) return 'Just now';
    if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
    if (diff.inHours < 24) return '${diff.inHours}h ago';
    if (diff.inDays < 7) return '${diff.inDays}d ago';
    final h = dt.hour % 12 == 0 ? 12 : dt.hour % 12;
    final ampm = dt.hour >= 12 ? 'PM' : 'AM';
    return '${dt.month}/${dt.day}/${dt.year} $h:${dt.minute.toString().padLeft(2, '0')} $ampm';
  }

  /// Formats event/timeline timestamp as "Oct 11, 2026 · 11:08 PM" in local time.
  static String formatEventTimestamp(dynamic raw) {
    if (raw == null) return '';
    final str = raw.toString().trim();
    if (str.isEmpty) return '';

    try {
      final dt = parseServerDateTime(str);
      const m = [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'May',
        'Jun',
        'Jul',
        'Aug',
        'Sep',
        'Oct',
        'Nov',
        'Dec',
      ];
      final h = dt.hour % 12 == 0 ? 12 : dt.hour % 12;
      final min = dt.minute.toString().padLeft(2, '0');
      final ampm = dt.hour >= 12 ? 'PM' : 'AM';
      return '${m[dt.month - 1]} ${dt.day}, ${dt.year} · $h:$min $ampm';
    } catch (_) {
      return str;
    }
  }

  /// Formats short date as "Oct 11" in local time.
  static String formatShortDate(dynamic raw) {
    if (raw == null) return 'Recent';
    final str = raw.toString().trim();
    if (str.isEmpty) return 'Recent';

    try {
      final dt = parseServerDateTime(str);
      const m = [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'May',
        'Jun',
        'Jul',
        'Aug',
        'Sep',
        'Oct',
        'Nov',
        'Dec',
      ];
      return '${m[dt.month - 1]} ${dt.day}';
    } catch (_) {
      return 'Recent';
    }
  }

  /// Formats time only as "11:08 PM" in local time.
  static String formatTimeOnly(dynamic raw) {
    if (raw == null) return '';
    final str = raw.toString().trim();
    if (str.isEmpty) return '';

    try {
      final dt = parseServerDateTime(str);
      final hour = dt.hour % 12 == 0 ? 12 : dt.hour % 12;
      final min = dt.minute.toString().padLeft(2, '0');
      final ampm = dt.hour >= 12 ? 'PM' : 'AM';
      return '$hour:$min $ampm';
    } catch (_) {
      return str;
    }
  }
}
