import 'dart:async';

import 'package:flutter/material.dart';

import '../app_bottom_nav.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';
import '../theme/app_theme.dart';
import '../widgets/floating_message_button.dart';
import 'request_tracking_screen.dart';

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
              .whereType<Map<String, dynamic>>()
              .map(_Notification.fromJson)
              .toList();
          _unreadCount = unread;
          _isLoading = false;
        });
      } else if (mounted) {
        setState(() => _isLoading = false);
      }
    } catch (_) {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }

  Future<void> _markGroupAsRead(_NotificationGroup group) async {
    final userId = SessionStore.userId;
    if (userId == null || userId == 0) return;

    final unreadIds = group.items
        .where((n) => !n.isRead)
        .map((n) => n.id)
        .toList(growable: false);
    if (unreadIds.isEmpty) return;

    try {
      for (final id in unreadIds) {
        await ApiService.markNotificationRead(
          userId: userId,
          notificationId: id,
        );
      }

      if (!mounted) return;
      setState(() {
        _notifications = _notifications.map((n) {
          if (unreadIds.contains(n.id)) {
            return n.copyWith(isRead: true);
          }
          return n;
        }).toList();
        _unreadCount = (_unreadCount - unreadIds.length).clamp(
          0,
          _notifications.length,
        );
      });
    } catch (_) {
      // Silent fail.
    }
  }

  Future<void> _markAllAsRead() async {
    try {
      final userId = SessionStore.userId;
      if (userId == null || userId == 0) return;

      await ApiService.markAllNotificationsRead(userId: userId);

      if (!mounted) return;
      setState(() {
        _notifications = _notifications
            .map((n) => n.copyWith(isRead: true))
            .toList();
        _unreadCount = 0;
      });
    } catch (_) {
      // Silent fail.
    }
  }

  List<_NotificationGroup> _groupNotifications() {
    final grouped = <String, List<_Notification>>{};
    for (final n in _notifications) {
      final key = n.requestId != null
          ? 'req:${n.requestId}'
          : 'type:${n.type}|title:${n.title}';
      grouped.putIfAbsent(key, () => <_Notification>[]).add(n);
    }

    final groups = grouped.entries.map((entry) {
      final items = entry.value;
      items.sort((a, b) => b.createdAt.compareTo(a.createdAt));
      return _NotificationGroup(items: items);
    }).toList();

    groups.sort((a, b) => b.latest.createdAt.compareTo(a.latest.createdAt));
    return groups;
  }

  bool _isMessageNotification(String type) {
    final normalized = type.toLowerCase();
    return normalized == 'comment' || normalized == 'message';
  }

  bool _isTrackingNotification(String type) {
    final normalized = type.toLowerCase();
    return normalized == 'approved' ||
        normalized == 'rejected' ||
        normalized == 'posted' ||
        normalized == 'review' ||
        normalized == 'under_review' ||
        normalized == 'received' ||
        normalized == 'status_update';
  }

  Future<void> _handleGroupTap(_NotificationGroup group) async {
    if (group.unreadCount > 0) {
      await _markGroupAsRead(group);
    }

    if (!mounted) return;
    final latest = group.latest;

    if (_isMessageNotification(latest.type)) {
      await Navigator.of(context).pushNamed('/messages');
      if (mounted) {
        _loadNotifications(showLoading: false);
      }
      return;
    }

    if (latest.requestId != null && latest.requestId! > 0) {
      await _openRequestTracking(latest);
      if (mounted) {
        _loadNotifications(showLoading: false);
      }
      return;
    }

    if (_isTrackingNotification(latest.type)) {
      await Navigator.of(context).pushNamed('/requests');
      if (mounted) {
        _loadNotifications(showLoading: false);
      }
    }
  }

  Future<void> _openRequestTracking(_Notification notification) async {
    final requestId = notification.requestId;
    if (requestId == null || requestId <= 0 || !mounted) return;

    bool loaderOpen = true;
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(
        child: CircularProgressIndicator(color: AppColors.primary),
      ),
    );

    try {
      final response = await ApiService.fetchRequestDetails(
        requestId: requestId,
      );

      if (!mounted) return;
      if (loaderOpen) {
        Navigator.of(context).pop();
        loaderOpen = false;
      }

      if (response['success'] != true) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Unable to open request tracking.')),
        );
        return;
      }

      final data = response['data'] ?? {};
      final request = (data['request'] as Map?)?.cast<String, dynamic>() ?? {};
      final activities = (data['activities'] as List?) ?? const [];

      final requestCode = (request['request_id'] ?? '').toString().trim();
      final requestTitle = (request['title'] ?? '').toString();
      final currentStatus =
          (request['status'] ?? notification.requestStatus ?? 'Pending')
              .toString();
      final fallbackNumber = 'REQ-$requestId';

      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => RequestTrackingScreen(
            requestNumber: requestCode.isEmpty ? fallbackNumber : requestCode,
            requestTitle: requestTitle,
            currentStatus: currentStatus,
            currentStatusMessage: _statusMessage(currentStatus),
            events: _buildTrackingEvents(request, activities),
          ),
        ),
      );
    } catch (e) {
      if (!mounted) return;
      if (loaderOpen) {
        Navigator.of(context).pop();
      }
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text('Error: ${e.toString()}')));
    }
  }

  List<TrackingEvent> _buildTrackingEvents(
    Map<String, dynamic> request,
    List activities,
  ) {
    final events = <TrackingEvent>[];

    final createdAt = (request['created_at'] ?? '').toString();
    events.add(
      TrackingEvent(
        icon: Icons.send_outlined,
        title: 'Request Submitted',
        subtitle: 'Your request was sent successfully.',
        timestamp: _formatTimestamp(createdAt),
      ),
    );

    for (final raw in activities.whereType<Map>()) {
      final activity = raw.cast<String, dynamic>();
      final action = (activity['action'] ?? '').toString();
      final activityTime = (activity['created_at'] ?? '').toString();

      IconData icon = Icons.info_outline;
      String title = 'Update';
      String subtitle = action;

      if (action.contains('Under Review')) {
        icon = Icons.rate_review_outlined;
        title = 'Under Review';
        subtitle = 'Marketing team is evaluating your request.';
      } else if (action.contains('Approved')) {
        icon = Icons.check_circle_outline;
        title = 'Approved';
        subtitle = 'Your request has been approved.';
      } else if (action.contains('Rejected')) {
        icon = Icons.cancel_outlined;
        title = 'Rejected';
        subtitle = 'Your request was not approved.';
      } else if (action.contains('Posted')) {
        icon = Icons.publish;
        title = 'Posted';
        subtitle = 'Your content has been published.';
      } else if (action.contains('Pending')) {
        icon = Icons.hourglass_empty;
        title = 'Pending Review';
        subtitle = 'Your request is waiting for review.';
      } else if (action.contains('Internal note')) {
        icon = Icons.comment_outlined;
        title = 'Note Added';
        subtitle = action.replaceFirst('Internal note: ', '');
      }

      events.add(
        TrackingEvent(
          icon: icon,
          title: title,
          subtitle: subtitle,
          timestamp: _formatTimestamp(activityTime),
        ),
      );
    }

    return events;
  }

  String _statusMessage(String status) {
    if (status == 'Pending') {
      return 'Your request is currently queued for review.';
    }
    if (status == 'Approved') {
      return 'Your request has been approved by the Marketing Office.';
    }
    if (status == 'Posted') {
      return 'Your content has been successfully published.';
    }
    if (status == 'Rejected') {
      return 'Your request was not approved. Please check feedback.';
    }
    return 'Your request is being processed.';
  }

  String _formatTimestamp(String datetime) {
    if (datetime.isEmpty) return '';
    try {
      final dt = DateTime.parse(datetime);
      final months = [
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
      final hour = dt.hour > 12 ? dt.hour - 12 : (dt.hour == 0 ? 12 : dt.hour);
      final ampm = dt.hour >= 12 ? 'PM' : 'AM';
      final min = dt.minute.toString().padLeft(2, '0');
      return '${months[dt.month - 1]} ${dt.day}, ${dt.year} • $hour:$min $ampm';
    } catch (_) {
      return datetime;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.pageBg,
      body: Stack(
        children: [
          Column(
            children: [
              _buildHeader(),
              Expanded(
                child: _isLoading
                    ? const Center(
                        child: CircularProgressIndicator(
                          color: AppColors.primary,
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
          const FloatingMessageButton(),
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
        color: AppColors.surface,
        border: Border(bottom: BorderSide(color: AppColors.border, width: 1)),
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
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w700,
                    fontSize: 24,
                    color: AppColors.primary,
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  'Stay updated on your requests',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w400,
                    fontSize: 13.5,
                    color: AppColors.inkMid,
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
                      color: AppColors.primary,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: const Text(
                      'Mark all read',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
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
              color: AppColors.inkMute,
            ),
            SizedBox(height: 12),
            Text(
              'No notifications yet',
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w400,
                fontSize: 14,
                color: AppColors.inkMid,
              ),
            ),
            SizedBox(height: 6),
            Text(
              'You\'ll be notified about your request updates here',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w400,
                fontSize: 12,
                color: AppColors.inkMute,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildNotificationsList() {
    final groups = _groupNotifications();

    return RefreshIndicator(
      onRefresh: () => _loadNotifications(showLoading: false),
      color: AppColors.primary,
      child: ListView.separated(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        itemCount: groups.length,
        separatorBuilder: (_, __) => const SizedBox(height: 8),
        itemBuilder: (context, index) {
          final group = groups[index];
          return _NotificationCard(
            group: group,
            onTap: () => _handleGroupTap(group),
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
        color: AppColors.accent,
        borderRadius: BorderRadius.circular(8),
      ),
      alignment: Alignment.center,
      child: Text(
        count > 99 ? '99+' : '$count',
        style: const TextStyle(
          fontFamily: 'DM Sans',
          fontWeight: FontWeight.w500,
          fontSize: 12,
          color: Colors.white,
        ),
      ),
    );
  }
}

class _NotificationCard extends StatelessWidget {
  final _NotificationGroup group;
  final VoidCallback onTap;

  const _NotificationCard({required this.group, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final notification = group.latest;

    return GestureDetector(
      onTap: onTap,
      child: Stack(
        children: [
          if (group.totalCount > 1)
            Positioned(
              left: 4,
              right: 4,
              bottom: 0,
              child: Container(
                height: 10,
                decoration: BoxDecoration(
                  color: AppColors.border,
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
          if (group.totalCount > 1)
            Positioned(
              left: 2,
              right: 2,
              bottom: 4,
              child: Container(
                height: 10,
                decoration: BoxDecoration(
                  color: AppColors.pageBg,
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
          Container(
            padding: const EdgeInsets.all(14),
            margin: EdgeInsets.only(bottom: group.totalCount > 1 ? 8 : 0),
            decoration: BoxDecoration(
              color: group.unreadCount == 0
                  ? AppColors.surface
                  : AppColors.goldBg,
              borderRadius: BorderRadius.circular(AppRadius.md),
              border: Border.all(
                color: group.unreadCount == 0
                    ? AppColors.border
                    : AppColors.accent.withValues(alpha: 0.3),
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
                    color: _getTypeColor(
                      notification.type,
                    ).withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(
                    _getTypeIcon(notification.type),
                    size: 20,
                    color: _getTypeColor(notification.type),
                  ),
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
                                fontFamily: 'DM Sans',
                                fontWeight: group.unreadCount == 0
                                    ? FontWeight.w500
                                    : FontWeight.w600,
                                fontSize: 14,
                                color: AppColors.ink,
                              ),
                            ),
                          ),
                          if (group.totalCount > 1)
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 7,
                                vertical: 3,
                              ),
                              margin: const EdgeInsets.only(right: 6),
                              decoration: BoxDecoration(
                                color: AppColors.border,
                                borderRadius: BorderRadius.circular(999),
                              ),
                              child: Text(
                                '${group.totalCount}',
                                style: const TextStyle(
                                  fontFamily: 'DM Sans',
                                  fontWeight: FontWeight.w600,
                                  fontSize: 10,
                                  color: AppColors.inkMid,
                                ),
                              ),
                            ),
                          if (group.unreadCount > 0)
                            Container(
                              width: 8,
                              height: 8,
                              decoration: const BoxDecoration(
                                color: AppColors.accent,
                                shape: BoxShape.circle,
                              ),
                            ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        notification.message,
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w400,
                          fontSize: 13,
                          color: AppColors.inkMid,
                        ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        _formatTime(notification.createdAt),
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w400,
                          fontSize: 11,
                          color: AppColors.inkMute,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Color _getTypeColor(String type) {
    switch (type) {
      case 'approved':
        return AppColors.approved;
      case 'rejected':
        return AppColors.rejected;
      case 'posted':
      case 'comment':
        return AppColors.posted;
      case 'review':
      case 'under_review':
      case 'received':
        return AppColors.pending;
      default:
        return AppColors.accent;
    }
  }

  IconData _getTypeIcon(String type) {
    switch (type) {
      case 'approved':
        return Icons.check_circle;
      case 'rejected':
        return Icons.cancel;
      case 'posted':
        return Icons.share;
      case 'comment':
        return Icons.chat_bubble_outline;
      case 'review':
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
    }
    if (diff.inMinutes < 60) {
      return '${diff.inMinutes}m ago';
    }
    if (diff.inHours < 24) {
      return '${diff.inHours}h ago';
    }
    if (diff.inDays < 7) {
      return '${diff.inDays}d ago';
    }
    return '${time.month}/${time.day}/${time.year}';
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
    required this.requestStatus,
  });

  factory _Notification.fromJson(Map<String, dynamic> json) {
    int? parseNullableInt(dynamic value) {
      if (value == null) return null;
      if (value is int) return value;
      if (value is num) return value.toInt();
      return int.tryParse(value.toString());
    }

    final statusRaw = (json['request_status'] ?? '').toString().trim();

    return _Notification(
      id: (json['id'] as num?)?.toInt() ?? 0,
      requestId: parseNullableInt(json['request_id']),
      title: (json['title'] ?? '').toString(),
      message: (json['message'] ?? '').toString(),
      type: (json['type'] ?? 'status_update').toString(),
      isRead:
          json['is_read'] == true ||
          json['is_read'] == 1 ||
          json['is_read'] == '1',
      createdAt:
          DateTime.tryParse((json['created_at'] ?? '').toString()) ??
          DateTime.now(),
      requestStatus: statusRaw.isEmpty ? null : statusRaw,
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

class _NotificationGroup {
  final List<_Notification> items;

  const _NotificationGroup({required this.items});

  _Notification get latest => items.first;
  int get totalCount => items.length;
  int get unreadCount => items.where((n) => !n.isRead).length;
}
