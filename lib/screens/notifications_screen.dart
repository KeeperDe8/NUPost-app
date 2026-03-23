import 'dart:async';
import 'package:flutter/material.dart';
import '../app_bottom_nav.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  final int _currentNavIndex = 3;

  List<_Notification> _notifications = [];
  int _unreadCount = 0;
  bool _isLoading = true;
  Timer? _pollTimer;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
    // Poll every 10 seconds for real-time updates
    _pollTimer = Timer.periodic(const Duration(seconds: 10), (_) {
      _loadNotifications(showLoading: false);
    });
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadNotifications({bool showLoading = true}) async {
    if (showLoading && mounted) {
      setState(() => _isLoading = true);
    }

    try {
      final userId = SessionStore.userId;
      if (userId == null || userId == 0) return;

      final response = await ApiService.fetchNotifications(userId: userId);
      if (response['success'] == true && mounted) {
        final data = response['data'] ?? {};
        final notifList = data['notifications'] as List? ?? [];
        final unread = data['unread_count'] as int? ?? 0;

        setState(() {
          _notifications = notifList
              .map((n) => _Notification.fromJson(n))
              .toList();
          _unreadCount = unread;
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _markAsRead(int notificationId) async {
    try {
      final userId = SessionStore.userId;
      if (userId == null || userId == 0) return;

      await ApiService.markNotificationRead(
        userId: userId,
        notificationId: notificationId,
      );

      setState(() {
        final idx = _notifications.indexWhere((n) => n.id == notificationId);
        if (idx != -1 && !_notifications[idx].isRead) {
          _notifications[idx] = _notifications[idx].copyWith(isRead: true);
          _unreadCount = (_unreadCount - 1).clamp(0, _notifications.length);
        }
      });
    } catch (e) {
      // Silent fail
    }
  }

  Future<void> _markAllAsRead() async {
    try {
      final userId = SessionStore.userId;
      if (userId == null || userId == 0) return;

      await ApiService.markAllNotificationsRead(userId: userId);

      setState(() {
        _notifications = _notifications
            .map((n) => n.copyWith(isRead: true))
            .toList();
        _unreadCount = 0;
      });
    } catch (e) {
      // Silent fail
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF9FAFB),
      body: Stack(
        children: [
          Column(
            children: [
              _buildHeader(),
              Expanded(
                child: _isLoading
                    ? const Center(
                        child: CircularProgressIndicator(
                          color: Color(0xFF003366),
                        ),
                      )
                    : _notifications.isEmpty
                    ? _buildEmptyState()
                    : _buildNotificationsList(),
              ),
              const SizedBox(height: 90),
            ],
          ),
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: AppBottomNav(currentIndex: _currentNavIndex),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      padding: EdgeInsets.only(
        left: 20,
        right: 20,
        top: MediaQuery.of(context).padding.top + 18,
        bottom: 18,
      ),
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(bottom: BorderSide(color: Color(0xFFE5E7EB), width: 1)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: const [
                Text(
                  'Notifications',
                  style: TextStyle(
                    fontFamily: 'Inter',
                    fontWeight: FontWeight.w700,
                    fontSize: 24,
                    color: Color(0xFF003366),
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  'Stay updated on your requests',
                  style: TextStyle(
                    fontFamily: 'Inter',
                    fontWeight: FontWeight.w400,
                    fontSize: 13.5,
                    color: Color(0xFF6B7280),
                  ),
                ),
              ],
            ),
          ),
          Row(
            children: [
              if (_unreadCount > 0)
                GestureDetector(
                  onTap: _markAllAsRead,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 6,
                    ),
                    margin: const EdgeInsets.only(right: 8),
                    decoration: BoxDecoration(
                      color: const Color(0xFF003366),
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: const Text(
                      'Mark all read',
                      style: TextStyle(
                        fontFamily: 'Inter',
                        fontWeight: FontWeight.w500,
                        fontSize: 11,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              _UnreadBadge(count: _unreadCount),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.only(bottom: 80),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: const [
            Icon(
              Icons.notifications_none_outlined,
              size: 48,
              color: Color(0xFF99A1AF),
            ),
            SizedBox(height: 12),
            Text(
              'No notifications yet',
              style: TextStyle(
                fontFamily: 'Inter',
                fontWeight: FontWeight.w400,
                fontSize: 14,
                color: Color(0xFF4A5565),
              ),
            ),
            SizedBox(height: 6),
            Text(
              'You\'ll be notified about your request updates here',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontFamily: 'Inter',
                fontWeight: FontWeight.w400,
                fontSize: 12,
                color: Color(0xFF99A1AF),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNotificationsList() {
    return RefreshIndicator(
      onRefresh: () => _loadNotifications(showLoading: false),
      color: const Color(0xFF003366),
      child: ListView.separated(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        itemCount: _notifications.length,
        separatorBuilder: (_, __) => const SizedBox(height: 8),
        itemBuilder: (context, index) {
          final notif = _notifications[index];
          return _NotificationCard(
            notification: notif,
            onTap: () {
              if (!notif.isRead) {
                _markAsRead(notif.id);
              }
            },
          );
        },
      ),
    );
  }
}

class _UnreadBadge extends StatelessWidget {
  final int count;
  const _UnreadBadge({required this.count});

  @override
  Widget build(BuildContext context) {
    if (count == 0) return const SizedBox.shrink();
    return Container(
      width: 24.67,
      height: 24,
      decoration: BoxDecoration(
        color: const Color(0xFFFB2C36),
        borderRadius: BorderRadius.circular(8),
      ),
      alignment: Alignment.center,
      child: Text(
        count > 99 ? '99+' : '$count',
        style: const TextStyle(
          fontFamily: 'Inter',
          fontWeight: FontWeight.w500,
          fontSize: 12,
          color: Colors.white,
        ),
      ),
    );
  }
}

class _NotificationCard extends StatelessWidget {
  final _Notification notification;
  final VoidCallback onTap;

  const _NotificationCard({required this.notification, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: notification.isRead ? Colors.white : const Color(0xFFF0F7FF),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: notification.isRead
                ? const Color(0xFFE5E7EB)
                : const Color(0xFF3B82F6).withValues(alpha: 0.3),
            width: 1,
          ),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: _getTypeColor().withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Icon(_getTypeIcon(), size: 20, color: _getTypeColor()),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          notification.title,
                          style: TextStyle(
                            fontFamily: 'Inter',
                            fontWeight: notification.isRead
                                ? FontWeight.w500
                                : FontWeight.w600,
                            fontSize: 14,
                            color: const Color(0xFF1F2937),
                          ),
                        ),
                      ),
                      if (!notification.isRead)
                        Container(
                          width: 8,
                          height: 8,
                          decoration: const BoxDecoration(
                            color: Color(0xFF3B82F6),
                            shape: BoxShape.circle,
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    notification.message,
                    style: const TextStyle(
                      fontFamily: 'Inter',
                      fontWeight: FontWeight.w400,
                      fontSize: 13,
                      color: Color(0xFF6B7280),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    _formatTime(notification.createdAt),
                    style: const TextStyle(
                      fontFamily: 'Inter',
                      fontWeight: FontWeight.w400,
                      fontSize: 11,
                      color: Color(0xFF99A1AF),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Color _getTypeColor() {
    switch (notification.type) {
      case 'approved':
        return const Color(0xFF059669);
      case 'rejected':
        return const Color(0xFFDC2626);
      case 'posted':
      case 'comment':
        return const Color(0xFF7C3AED);
      case 'under_review':
      case 'received':
        return const Color(0xFFD97706);
      default:
        return const Color(0xFF3B82F6);
    }
  }

  IconData _getTypeIcon() {
    switch (notification.type) {
      case 'approved':
        return Icons.check_circle;
      case 'rejected':
        return Icons.cancel;
      case 'posted':
        return Icons.share;
      case 'comment':
        return Icons.chat_bubble_outline;
      case 'under_review':
      case 'received':
        return Icons.access_time;
      default:
        return Icons.notifications_outlined;
    }
  }

  String _formatTime(DateTime time) {
    final now = DateTime.now();
    final diff = now.difference(time);

    if (diff.inMinutes < 1) {
      return 'Just now';
    } else if (diff.inMinutes < 60) {
      return '${diff.inMinutes}m ago';
    } else if (diff.inHours < 24) {
      return '${diff.inHours}h ago';
    } else if (diff.inDays < 7) {
      return '${diff.inDays}d ago';
    } else {
      return '${time.month}/${time.day}/${time.year}';
    }
  }
}

class _Notification {
  final int id;
  final int? requestId;
  final String title;
  final String message;
  final String type;
  final bool isRead;
  final DateTime createdAt;
  final String? requestStatus;

  const _Notification({
    required this.id,
    required this.requestId,
    required this.title,
    required this.message,
    required this.type,
    required this.isRead,
    required this.createdAt,
    this.requestStatus,
  });

  factory _Notification.fromJson(Map<String, dynamic> json) {
    return _Notification(
      id: json['id'] ?? 0,
      requestId: json['request_id'],
      title: json['title'] ?? '',
      message: json['message'] ?? '',
      type: json['type'] ?? 'status_update',
      isRead: json['is_read'] == true,
      createdAt: DateTime.tryParse(json['created_at'] ?? '') ?? DateTime.now(),
      requestStatus: json['request_status'],
    );
  }

  _Notification copyWith({bool? isRead}) {
    return _Notification(
      id: id,
      requestId: requestId,
      title: title,
      message: message,
      type: type,
      isRead: isRead ?? this.isRead,
      createdAt: createdAt,
      requestStatus: requestStatus,
    );
  }
}
