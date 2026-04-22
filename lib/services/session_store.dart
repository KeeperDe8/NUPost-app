class SessionStore {
  static int? userId;
  static String? name;
  static String? email;

  static bool get isLoggedIn => userId != null;

  static void setUser({
    required int id,
    required String userName,
    required String userEmail,
  }) {
    userId = id;
    name = userName;
    email = userEmail;
  }

  static void clear() {
    userId = null;
    name = null;
    email = null;
  }
}

