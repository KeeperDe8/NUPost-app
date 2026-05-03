import 'package:shared_preferences/shared_preferences.dart';

class ChatReadStore {
  static const String _keyPrefix = 'last_read_id_';

  /// Save the latest message ID seen by the user for a specific request thread.
  static Future<void> markAsRead(int requestId, int lastMessageId) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt('${_keyPrefix}$requestId', lastMessageId);
  }

  /// Get the latest message ID seen by the user for a specific request thread.
  static Future<int> getLastReadId(int requestId) async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getInt('${_keyPrefix}$requestId') ?? 0;
  }
}
