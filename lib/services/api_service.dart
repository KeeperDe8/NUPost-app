import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:file_picker/file_picker.dart';
import 'package:http/http.dart' as http;
import 'network_queue_manager.dart';

class ApiService {
  // Android emulator: 10.0.2.2 maps to localhost on your PC.
  // You can force one URL via --dart-define=API_BASE_URL=http://10.0.2.2:8000/api
  static const String _configuredBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
  );

  static const String _defaultBaseUrl = 'https://nupost.site/api';

  static String get _baseUrl {
    if (_configuredBaseUrl.isNotEmpty) {
      return _configuredBaseUrl;
    }

    return _defaultBaseUrl;
  }

  static String resolveMediaUrl(String rawPath) {
    final path = rawPath.trim();
    if (path.isEmpty) return '';

    String domain = _baseUrl.replaceAll(RegExp(r'/api/?$'), '');

    if (path.startsWith('http://') || path.startsWith('https://')) {
      final uri = Uri.tryParse(path);
      if (uri != null) {
        final host = uri.host.toLowerCase();
        if (host == 'localhost' || host == '127.0.0.1' || host == '10.0.2.2') {
          final domainUri = Uri.parse(domain);
          return uri.replace(
            scheme: domainUri.scheme,
            host: domainUri.host,
            port: domainUri.hasPort ? domainUri.port : null,
          ).toString();
        }
      }
      return path;
    }

    if (path.startsWith('/api/') || path.startsWith('api/')) {
      final clean = path.startsWith('/') ? path.substring(1) : path;
      return '$domain/$clean';
    }

    if (path.startsWith('/uploads/') || path.startsWith('uploads/')) {
      final clean = path.startsWith('/') ? path.substring(1) : path;
      return '$domain/$clean';
    }

    final filename = path.split('/').last.split('\\').last;
    return '$_baseUrl/media.php?file=${Uri.encodeComponent(filename)}';
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

  static Future<Map<String, dynamic>> updatePassword({
    required int userId,
    required String currentPassword,
    required String newPassword,
  }) async {
    final uri = _buildUri(_baseUrl, 'update_password.php', null);
    return _postJson(uri, {
      'user_id': '$userId',
      'current_password': currentPassword,
      'new_password': newPassword,
    }, fallbackMessage: 'Failed to update password');
  }

  static Future<Map<String, dynamic>> verifyOtp({
    required String email,
    required String otp,
  }) async {
    final uri = _buildUri(_baseUrl, 'otp_verify.php', null);
    return _postJson(uri, {
      'email': email,
      'otp': otp,
    }, fallbackMessage: 'OTP Verification failed');
  }

  static Future<Map<String, dynamic>> resendOtp({
    required String email,
    String? purpose,
  }) async {
    final uri = _buildUri(_baseUrl, 'resend_otp.php', null);
    final body = <String, dynamic>{'email': email};
    if (purpose != null) body['purpose'] = purpose;

    return _postJson(uri, body, fallbackMessage: 'Failed to resend OTP');
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

  static Future<Map<String, dynamic>> updateRequest({
    required int requestId,
    required int userId,
    required String title,
    required String description,
    required String category,
    required String priority,
    required List<String> platforms,
    required String preferredDate,
    required String caption,
    List<PlatformFile> mediaFiles = const [],
    bool keepExistingMedia = true,
  }) async {
    final uri = _buildUri(_baseUrl, 'update_request.php', null);
    final request = http.MultipartRequest('POST', uri)
      ..fields['request_id'] = '$requestId'
      ..fields['user_id'] = '$userId'
      ..fields['title'] = title
      ..fields['description'] = description
      ..fields['category'] = category
      ..fields['priority'] = priority
      ..fields['preferred_date'] = preferredDate
      ..fields['caption'] = caption
      ..fields['keep_existing_media'] = keepExistingMedia ? '1' : '0'
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
      fallbackMessage: 'Failed to update request',
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

  static Future<Map<String, dynamic>> updateNotificationSettings({
    required int userId,
    required bool emailNotif,
    required bool statusUpdates,
  }) async {
    final uri = _buildUri(_baseUrl, 'update_profile.php', null);
    return _postJson(uri, {
      'user_id': '$userId',
      'email_notif': emailNotif ? '1' : '0',
      'status_updates': statusUpdates ? '1' : '0',
    }, fallbackMessage: 'Failed to update notification settings');
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

  static Future<Map<String, dynamic>> fetchMessageThreads({
    required int userId,
  }) async {
    final uri = _buildUri(_baseUrl, 'messages.php', {'user_id': '$userId'});
    return _getJson(uri, fallbackMessage: 'Failed to load messages');
  }

  static Future<Map<String, dynamic>> fetchMessageThread({
    required int userId,
    required int requestId,
  }) async {
    final uri = _buildUri(_baseUrl, 'message_thread.php', {
      'user_id': '$userId',
      'request_id': '$requestId',
    });
    return _getJson(uri, fallbackMessage: 'Failed to load chat');
  }

  static Future<Map<String, dynamic>> sendMessageToThread({
    required int userId,
    required int requestId,
    required String message,
  }) async {
    final uri = _buildUri(_baseUrl, 'message_thread.php', null);
    return _postJson(uri, {
      'user_id': userId,
      'request_id': requestId,
      'message': message,
    }, fallbackMessage: 'Failed to send message');
  }

  static Future<Map<String, dynamic>> sendMessage({
    required int userId,
    required int requestId,
    required String message,
  }) =>
      sendMessageToThread(
        userId: userId,
        requestId: requestId,
        message: message,
      );

  static Future<void> markThreadRead({
    required int userId,
    required int requestId,
  }) async {
    try {
      final uri = _buildUri(_baseUrl, 'mark_messages_read.php', null);
      await _postJson(uri, {
        'user_id': userId,
        'request_id': requestId,
      }, fallbackMessage: 'mark read');
    } catch (_) {
      // Silent — endpoint may not exist yet
    }
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
    bool enableCache = true,
  }) async {
    final netMgr = NetworkQueueManager.instance;
    netMgr.trackRequestStart();
    final cacheKey = uri.toString();
    final maxRetries = netMgr.maxRetries;
    final timeout = netMgr.adaptiveTimeout;

    for (int attempt = 0; attempt <= maxRetries; attempt++) {
      try {
        final response = await http.get(uri).timeout(timeout);
        netMgr.recordSuccess();
        final parsed = _parseResponse(
          response,
          uri: uri,
          fallbackMessage: fallbackMessage,
        );
        if (enableCache) {
          unawaited(netMgr.cacheResponse(cacheKey, parsed));
        }
        netMgr.trackRequestEnd();
        return parsed;
      } on TimeoutException {
        netMgr.recordTimeout();
        if (attempt < maxRetries) {
          await Future.delayed(Duration(milliseconds: 250 * (attempt + 1)));
          continue;
        }
        // Check if offline/local cache exists to keep user experience smooth
        if (enableCache) {
          final cached = await netMgr.getCachedResponse(cacheKey);
          if (cached != null) {
            netMgr.trackRequestEnd();
            return cached;
          }
        }
        netMgr.trackRequestEnd();
        throw Exception(
          'Connection timed out. Please check your internet and try again.',
        );
      } on SocketException {
        netMgr.recordTimeout();
        if (attempt < maxRetries) {
          await Future.delayed(Duration(milliseconds: 250 * (attempt + 1)));
          continue;
        }
        if (enableCache) {
          final cached = await netMgr.getCachedResponse(cacheKey);
          if (cached != null) {
            netMgr.trackRequestEnd();
            return cached;
          }
        }
        netMgr.trackRequestEnd();
        throw Exception(
          'No internet connection. Please check your Wi-Fi or mobile data.',
        );
      } catch (e) {
        if (e is Exception && e.toString().contains('Exception: ')) {
          netMgr.trackRequestEnd();
          rethrow;
        }
        if (attempt < maxRetries) {
          await Future.delayed(Duration(milliseconds: 250 * (attempt + 1)));
          continue;
        }
        if (enableCache) {
          final cached = await netMgr.getCachedResponse(cacheKey);
          if (cached != null) {
            netMgr.trackRequestEnd();
            return cached;
          }
        }
        netMgr.trackRequestEnd();
        throw Exception('Network error. Please try again.');
      }
    }

    netMgr.trackRequestEnd();
    throw Exception(
      'No internet connection. Please check your Wi-Fi or mobile data.',
    );
  }

  static Future<Map<String, dynamic>> _postJson(
    Uri uri,
    Map<String, dynamic> payload, {
    required String fallbackMessage,
  }) async {
    final netMgr = NetworkQueueManager.instance;
    netMgr.trackRequestStart();
    final maxRetries = netMgr.maxRetries;
    final timeout = netMgr.adaptiveTimeout;

    for (int attempt = 0; attempt <= maxRetries; attempt++) {
      try {
        final response = await http
            .post(
              uri,
              headers: {'Content-Type': 'application/json'},
              body: jsonEncode(payload),
            )
            .timeout(timeout);
        netMgr.recordSuccess();
        final parsed = _parseResponse(
          response,
          uri: uri,
          fallbackMessage: fallbackMessage,
        );
        netMgr.trackRequestEnd();
        return parsed;
      } on TimeoutException {
        netMgr.recordTimeout();
        if (attempt < maxRetries) {
          await Future.delayed(Duration(milliseconds: 300 * (attempt + 1)));
          continue;
        }
        netMgr.trackRequestEnd();
        throw Exception(
          'Connection timed out. Please check your internet and try again.',
        );
      } on SocketException {
        netMgr.recordTimeout();
        if (attempt < maxRetries) {
          await Future.delayed(Duration(milliseconds: 300 * (attempt + 1)));
          continue;
        }
        netMgr.trackRequestEnd();
        throw Exception(
          'No internet connection. Please check your Wi-Fi or mobile data.',
        );
      } catch (e) {
        if (e is Exception && e.toString().contains('Exception: ')) {
          netMgr.trackRequestEnd();
          rethrow;
        }
        if (attempt < maxRetries) {
          await Future.delayed(Duration(milliseconds: 300 * (attempt + 1)));
          continue;
        }
        netMgr.trackRequestEnd();
        throw Exception('Network error. Please try again.');
      }
    }

    netMgr.trackRequestEnd();
    throw Exception(
      'No internet connection. Please check your Wi-Fi or mobile data.',
    );
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
      final snippet = body.trim().replaceAll(RegExp(r'\s+'), ' ');
      final shortSnippet = snippet.length > 100
          ? '${snippet.substring(0, 100)}...'
          : snippet;
      throw Exception('Server returned non-JSON response: $shortSnippet');
    }
    return <String, dynamic>{};
  }

  static Future<Map<String, dynamic>> updateProfile({
    required int userId,
    required String name,
    required String email,
    required String phone,
    required String bio,
    required String organization,
    required String department,
  }) async {
    final uri = Uri.parse('$_baseUrl/update_profile.php');
    try {
      final response = await http
          .post(
            uri,
            body: {
              'user_id': userId.toString(),
              'name': name,
              'email': email,
              'phone': phone,
              'bio': bio,
              'organization': organization,
              'department': department,
            },
          )
          .timeout(NetworkQueueManager.instance.adaptiveTimeout);

      return _parseResponse(
        response,
        uri: uri,
        fallbackMessage: 'Failed to update profile',
      );
    } catch (e) {
      throw Exception('Failed to reach server: $e');
    }
  }

  static Future<List<Map<String, dynamic>>> fetchAdminRequests({
    String? status,
    String? search,
  }) async {
    final params = <String, String>{};
    if (status != null && status.isNotEmpty) params['status'] = status;
    if (search != null && search.isNotEmpty) params['search'] = search;

    final uri = _buildUri(_baseUrl, 'admin_requests.php', params);
    final json = await _getJson(
      uri,
      fallbackMessage: 'Failed to load admin requests',
    );

    final list = (json['data'] as List?) ?? const [];
    return list.cast<Map<String, dynamic>>();
  }

  static Future<Map<String, dynamic>> updateRequestStatus({
    required int requestId,
    required String status,
    String? note,
    String? adminName,
  }) async {
    final uri = _buildUri(_baseUrl, 'update_request_status.php', null);
    final body = <String, dynamic>{
      'request_id': requestId,
      'status': status,
    };
    if (note != null && note.isNotEmpty) body['note'] = note;
    if (adminName != null && adminName.isNotEmpty) body['admin_name'] = adminName;

    return _postJson(
      uri,
      body,
      fallbackMessage: 'Failed to update request status',
    );
  }

  static Future<Map<String, dynamic>> fetchAdminStats() async {
    final uri = _buildUri(_baseUrl, 'admin_stats.php', null);
    return _getJson(
      uri,
      fallbackMessage: 'Failed to load admin analytics',
    );
  }
}
