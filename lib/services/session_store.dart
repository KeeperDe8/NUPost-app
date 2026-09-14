import 'package:shared_preferences/shared_preferences.dart';

class SessionStore {
  static int? userId;
  static String? name;
  static String? email;
  static String? role;

  static const _keyUserId = 'nupost_user_id';
  static const _keyName = 'nupost_user_name';
  static const _keyEmail = 'nupost_user_email';
  static const _keyRole = 'nupost_user_role';

  static bool get isLoggedIn => userId != null && (userId ?? 0) > 0;
  static bool get isAdmin {
    final r = (role ?? '').toLowerCase().trim();
    final e = (email ?? '').toLowerCase().trim();
    final n = (name ?? '').toLowerCase().trim();
    return r == 'admin' ||
        r == 'administrator' ||
        r == 'marketing' ||
        r == 'marketing staff' ||
        r.contains('admin') ||
        e.contains('admin') ||
        n.contains('admin') ||
        e == 'admin@nupost.com';
  }

  /// Loads saved user session from local storage.
  /// Returns true if a valid user session was restored.
  static Future<bool> loadSession() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final id = prefs.getInt(_keyUserId);
      final storedEmail = prefs.getString(_keyEmail);
      final storedName = prefs.getString(_keyName);
      final storedRole = prefs.getString(_keyRole);

      if (id != null && id > 0 && storedEmail != null && storedEmail.isNotEmpty) {
        userId = id;
        name = storedName ?? 'User';
        email = storedEmail;
        role = (storedRole == null || storedRole.isEmpty || storedRole == 'staff')
            ? 'requestor'
            : storedRole;
        return true;
      }
    } catch (_) {}
    return false;
  }

  static void setUser({
    required int id,
    required String userName,
    required String userEmail,
    String? userRole,
  }) {
    userId = id;
    name = userName;
    email = userEmail;
    final r = (userRole ?? '').toLowerCase().trim();
    if (r.isNotEmpty) {
      role = (r == 'staff') ? 'requestor' : r;
    } else {
      final isAdm = userEmail.toLowerCase().contains('admin') || userName.toLowerCase().contains('admin');
      role = isAdm ? 'admin' : 'requestor';
    }

    _persistSession();
  }

  static Future<void> _persistSession() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      if (userId != null && (userId ?? 0) > 0) {
        await prefs.setInt(_keyUserId, userId!);
        await prefs.setString(_keyName, name ?? '');
        await prefs.setString(_keyEmail, email ?? '');
        await prefs.setString(_keyRole, role ?? '');
      }
    } catch (_) {}
  }

  static Future<void> clear() async {
    userId = null;
    name = null;
    email = null;
    role = null;

    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_keyUserId);
      await prefs.remove(_keyName);
      await prefs.remove(_keyEmail);
      await prefs.remove(_keyRole);
    } catch (_) {}
  }
}

