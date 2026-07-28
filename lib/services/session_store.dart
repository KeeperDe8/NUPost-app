class SessionStore {
  static int? userId;
  static String? name;
  static String? email;
  static String? role;

  static bool get isLoggedIn => userId != null;
  static bool get isAdmin => role == 'admin' || (email != null && email!.toLowerCase().contains('admin'));

  static void setUser({
    required int id,
    required String userName,
    required String userEmail,
    String? userRole,
  }) {
    userId = id;
    name = userName;
    email = userEmail;
    role = userRole ?? (userEmail.toLowerCase().contains('admin') ? 'admin' : 'staff');
  }

  static void clear() {
    userId = null;
    name = null;
    email = null;
    role = null;
  }
}

