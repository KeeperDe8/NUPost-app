import 'dart:convert';
import 'dart:io';

import 'package:file_picker/file_picker.dart';
import 'package:http/http.dart' as http;

class ApiService {
  // Android emulator: 10.0.2.2 maps to localhost on your PC.
  // You can force one URL via --dart-define=API_BASE_URL=http://10.0.2.2/nupost-main/api
  static const String _configuredBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
  );

  static String get _baseUrl {
    if (_configuredBaseUrl.isNotEmpty) {
      return _configuredBaseUrl;
    }

    return 'http://10.0.2.2/nupost-main/api';
  }

  static Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    final uri = _buildUri(_baseUrl, 'login.php', null);
    return _postJson(uri, {
      'email': email,
      'password': password,
    }, fallbackMessage: 'Login failed');
  }

  static Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
  }) async {
    final uri = _buildUri(_baseUrl, 'register.php', null);
    return _postJson(uri, {
      'name': name,
      'email': email,
      'password': password,
    }, fallbackMessage: 'Registration failed');
  }

  static Future<Map<String, dynamic>> fetchProfile({
    required int userId,
  }) async {
    final uri = _buildUri(_baseUrl, 'profile.php', {'user_id': '$userId'});
    return _getJson(uri, fallbackMessage: 'Failed to load profile');
  }

  static Future<List<Map<String, dynamic>>> fetchRequests({
    required int userId,
    String? status,
  }) async {
    final params = <String, String>{'user_id': '$userId'};
    if (status != null && status.isNotEmpty) {
      params['status'] = status;
    }

    final uri = _buildUri(_baseUrl, 'requests.php', params);
    final json = await _getJson(
      uri,
      fallbackMessage: 'Failed to load requests',
    );

    final list = (json['data'] as List?) ?? const [];
    return list.cast<Map<String, dynamic>>();
  }

  static Future<Map<String, dynamic>> createRequest({
    required int userId,
    required String title,
    required String description,
    required String category,
    required String priority,
    required List<String> platforms,
    required String preferredDate,
    required String caption,
    List<PlatformFile> mediaFiles = const [],
  }) async {
    final uri = _buildUri(_baseUrl, 'create_request.php', null);
    final request = http.MultipartRequest('POST', uri)
      ..fields['user_id'] = '$userId'
      ..fields['title'] = title
      ..fields['description'] = description
      ..fields['category'] = category
      ..fields['priority'] = priority
      ..fields['preferred_date'] = preferredDate
      ..fields['caption'] = caption
      ..fields['platforms_json'] = jsonEncode(platforms);

    final limitedMedia = mediaFiles.take(4);
    for (final media in limitedMedia) {
      if (media.path != null) {
        final file = File(media.path!);
        if (await file.exists()) {
          request.files.add(
            await http.MultipartFile.fromPath('media[]', media.path!),
          );
          continue;
        }
      }

      if (media.bytes != null) {
        request.files.add(
          http.MultipartFile.fromBytes(
            'media[]',
            media.bytes!,
            filename: media.name,
          ),
        );
      }
    }

    final streamed = await request.send();
    final response = await http.Response.fromStream(streamed);
    return _parseResponse(
      response,
      uri: uri,
      fallbackMessage: 'Failed to submit request',
    );
  }

  static Future<String> generateCaption({
    required String title,
    required String description,
    required String category,
    required List<String> platforms,
  }) async {
    final uri = _buildUri(_baseUrl, 'generate_caption.php', null);
    final json = await _postJson(uri, {
      'title': title,
      'description': description,
      'category': category,
      'platforms': platforms.join(', '),
    }, fallbackMessage: 'Caption generation failed');

    if ((json['error'] ?? '').toString().trim().isNotEmpty) {
      throw Exception(json['error'].toString());
    }

    final candidates = json['candidates'];
    if (candidates is List && candidates.isNotEmpty) {
      final first = candidates.first;
      if (first is Map<String, dynamic>) {
        final content = first['content'];
        if (content is Map<String, dynamic>) {
          final parts = content['parts'];
          if (parts is List && parts.isNotEmpty) {
            final p0 = parts.first;
            if (p0 is Map<String, dynamic>) {
              final text = (p0['text'] ?? '').toString().trim();
              if (text.isNotEmpty) return text;
            }
          }
        }
      }
    }

    throw Exception('No caption text returned by AI');
  }

  static Future<Map<String, dynamic>> fetchCalendar({
    required int userId,
    int? month,
    int? year,
    bool publicView = false,
  }) async {
    final now = DateTime.now();
    final m = month ?? now.month;
    final y = year ?? now.year;

    final uri = _buildUri(_baseUrl, 'calendar.php', {
      'user_id': '$userId',
      'month': '$m',
      'year': '$y',
      'public': publicView ? '1' : '0',
    });
    return _getJson(uri, fallbackMessage: 'Failed to load calendar');
  }

  static Future<Map<String, dynamic>> updatePublicProfile({
    required int userId,
    required bool isPublic,
  }) async {
    final uri = _buildUri(_baseUrl, 'update_profile.php', null);
    return _postJson(uri, {
      'user_id': '$userId',
      'public_profile': isPublic ? '1' : '0',
    }, fallbackMessage: 'Failed to update profile');
  }

  static Future<Map<String, dynamic>> updatePublicCalendar({
    required int userId,
    required bool isPublic,
  }) async {
    final uri = _buildUri(_baseUrl, 'update_profile.php', null);
    return _postJson(uri, {
      'user_id': '$userId',
      'public_calendar': isPublic ? '1' : '0',
    }, fallbackMessage: 'Failed to update calendar');
  }

  static Future<Map<String, dynamic>> fetchNotifications({
    required int userId,
  }) async {
    final uri = _buildUri(_baseUrl, 'notifications.php', {
      'user_id': '$userId',
    });
    return _getJson(uri, fallbackMessage: 'Failed to load notifications');
  }

  static Future<Map<String, dynamic>> markNotificationRead({
    required int userId,
    required int notificationId,
  }) async {
    final uri = _buildUri(_baseUrl, 'mark_notification_read.php', null);
    return _postJson(uri, {
      'user_id': userId,
      'notification_id': notificationId,
    }, fallbackMessage: 'Failed to mark notification as read');
  }

  static Future<Map<String, dynamic>> markAllNotificationsRead({
    required int userId,
  }) async {
    final uri = _buildUri(_baseUrl, 'mark_notification_read.php', null);
    return _postJson(uri, {
      'user_id': userId,
      'mark_all': true,
    }, fallbackMessage: 'Failed to mark notifications as read');
  }

  static Future<Map<String, dynamic>> fetchRequestDetails({
    required int requestId,
  }) async {
    final uri = _buildUri(_baseUrl, 'request_details.php', {
      'request_id': '$requestId',
    });
    return _getJson(uri, fallbackMessage: 'Failed to load request details');
  }

  static Uri _buildUri(
    String base,
    String endpoint,
    Map<String, String>? queryParameters,
  ) {
    return Uri.parse(
      '$base/$endpoint',
    ).replace(queryParameters: queryParameters);
  }

  static Future<Map<String, dynamic>> _getJson(
    Uri uri, {
    required String fallbackMessage,
  }) async {
    final response = await http.get(uri);
    return _parseResponse(response, uri: uri, fallbackMessage: fallbackMessage);
  }

  static Future<Map<String, dynamic>> _postJson(
    Uri uri,
    Map<String, dynamic> payload, {
    required String fallbackMessage,
  }) async {
    final response = await http.post(
      uri,
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode(payload),
    );
    return _parseResponse(response, uri: uri, fallbackMessage: fallbackMessage);
  }

  static Map<String, dynamic> _parseResponse(
    http.Response response, {
    required Uri uri,
    required String fallbackMessage,
  }) {
    final json = _decodeBody(response.body);
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return json;
    }

    final msg = (json['message'] ?? '').toString().trim();
    if (msg.isNotEmpty) {
      throw Exception(msg);
    }

    final snippet = response.body.trim().replaceAll(RegExp(r'\s+'), ' ');
    final shortSnippet = snippet.length > 120
        ? '${snippet.substring(0, 120)}...'
        : snippet;
    throw Exception(
      '$fallbackMessage (HTTP ${response.statusCode}) at $uri. Response: $shortSnippet',
    );
  }

  static Map<String, dynamic> _decodeBody(String body) {
    if (body.isEmpty) return <String, dynamic>{};
    try {
      final decoded = jsonDecode(body);
      if (decoded is Map<String, dynamic>) {
        return decoded;
      }
    } catch (_) {
      return <String, dynamic>{};
    }
    return <String, dynamic>{};
  }
}
