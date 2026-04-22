import 'dart:async';
import 'package:flutter/material.dart';
import '../app_bottom_nav.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';
import '../widgets/floating_message_button.dart';
import 'request_tracking_screen.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});
  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen>
    with SingleTickerProviderStateMixin {
  final int _currentNavIndex = 3;
  List<_Notif> _notifications = [];
  int _unreadCount = 0;
  bool _isLoading = true;
  Timer? _pollTimer;

  late final AnimationController _entryCtrl;
  late final Animation<double> _entryFade;

  @override
  void initState() {
    super.initState();
    _entryCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 600),
    )..forward();
    _entryFade = CurvedAnimation(
      parent: _entryCtrl,
      curve: const Interval(0.0, 0.7, curve: Curves.easeOut),
    );
    _loadNotifications();
    _pollTimer = Timer.periodic(
      const Duration(seconds: 10),
      (_) => _loadNotifications(showLoading: false),
    );
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _entryCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadNotifications({bool showLoading = true}) async {
    if (showLoading && mounted) setState(() => _isLoading = true);
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
              .map(_Notif.fromJson)
              .toList();
          _unreadCount = unread;
          _isLoading = false;
        });
      } else if (mounted) {
        setState(() => _isLoading = false);
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _markGroupAsRead(_NotifGroup group) async {
    final userId = SessionStore.userId;
    if (userId == null || userId == 0) return;
    final unreadIds = group.items
        .where((n) => !n.isRead)
        .map((n) => n.id)
        .toList();
    if (unreadIds.isEmpty) return;
    try {
      for (final id in unreadIds)
        await ApiService.markNotificationRead(
          userId: userId,
          notificationId: id,
        );
      if (!mounted) return;
      setState(() {
        _notifications = _notifications
            .map((n) => unreadIds.contains(n.id) ? n.copyWith(isRead: true) : n)
            .toList();
        _unreadCount = (_unreadCount - unreadIds.length).clamp(
          0,
          _notifications.length,
        );
      });
    } catch (_) {}
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
    } catch (_) {}
  }

  List<_NotifGroup> _groupNotifications() {
    final grouped = <String, List<_Notif>>{};
    for (final n in _notifications) {
      final key = n.requestId != null
          ? 'req:${n.requestId}'
          : 'type:${n.type}|title:${n.title}';
      grouped.putIfAbsent(key, () => <_Notif>[]).add(n);
    }
    final groups = grouped.entries.map((e) {
      final items = e.value..sort((a, b) => b.createdAt.compareTo(a.createdAt));
      return _NotifGroup(items: items);
    }).toList();
    groups.sort((a, b) => b.latest.createdAt.compareTo(a.latest.createdAt));
    return groups;
  }

  bool _isMsg(String type) {
    final n = type.toLowerCase();
    return n == 'comment' || n == 'message';
  }

  bool _isTracking(String type) {
    final n = type.toLowerCase();
    return [
      'approved',
      'rejected',
      'posted',
      'review',
      'under_review',
      'received',
      'status_update',
    ].contains(n);
  }

  Future<void> _handleGroupTap(_NotifGroup group) async {
    if (group.unreadCount > 0) await _markGroupAsRead(group);
    if (!mounted) return;
    final latest = group.latest;
    if (_isMsg(latest.type)) {
      await Navigator.of(context).pushNamed('/messages');
      if (mounted) _loadNotifications(showLoading: false);
      return;
    }
    if (latest.requestId != null && latest.requestId! > 0) {
      await _openTracking(latest);
      if (mounted) _loadNotifications(showLoading: false);
      return;
    }
    if (_isTracking(latest.type)) {
      await Navigator.of(context).pushNamed('/requests');
      if (mounted) _loadNotifications(showLoading: false);
    }
  }

  Future<void> _openTracking(_Notif n) async {
    final requestId = n.requestId;
    if (requestId == null || requestId <= 0 || !mounted) return;
    bool loaderOpen = true;
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(
        child: CircularProgressIndicator(color: Color(0xFF002366)),
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
      final currentStatus = (request['status'] ?? n.requestStatus ?? 'Pending')
          .toString();
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => RequestTrackingScreen(
            requestNumber: requestCode.isEmpty ? 'REQ-$requestId' : requestCode,
            requestTitle: requestTitle,
            currentStatus: currentStatus,
            currentStatusMessage: _statusMsg(currentStatus),
            events: _buildEvents(request, activities),
          ),
        ),
      );
    } catch (e) {
      if (!mounted) return;
      if (loaderOpen) Navigator.of(context).pop();
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text('Error: $e')));
    }
  }

  List<TrackingEvent> _buildEvents(
    Map<String, dynamic> request,
    List activities,
  ) {
    final events = <TrackingEvent>[
      TrackingEvent(
        icon: Icons.send_outlined,
        title: 'Request Submitted',
        subtitle: 'Your request was sent successfully.',
        timestamp: _fmt(request['created_at'] ?? ''),
      ),
    ];
    for (final raw in activities.whereType<Map>()) {
      final a = raw.cast<String, dynamic>();
      final action = (a['action'] ?? '').toString();
      IconData icon = Icons.info_outline;
      String title = 'Update';
      String sub = action;
      if (action.contains('Under Review')) {
        icon = Icons.rate_review_outlined;
        title = 'Under Review';
        sub = 'Marketing team is evaluating your request.';
      } else if (action.contains('Approved')) {
        icon = Icons.check_circle_outline;
        title = 'Approved';
        sub = 'Your request has been approved.';
      } else if (action.contains('Rejected')) {
        icon = Icons.cancel_outlined;
        title = 'Rejected';
        sub = 'Your request was not approved.';
      } else if (action.contains('Posted')) {
        icon = Icons.publish;
        title = 'Posted';
        sub = 'Your content has been published.';
      } else if (action.contains('Pending')) {
        icon = Icons.hourglass_empty;
        title = 'Pending';
        sub = 'Your request is waiting for review.';
      }
      events.add(
        TrackingEvent(
          icon: icon,
          title: title,
          subtitle: sub,
          timestamp: _fmt(a['created_at'] ?? ''),
        ),
      );
    }
    return events;
  }

  String _statusMsg(String s) {
    if (s == 'Pending') return 'Your request is currently queued for review.';
    if (s == 'Approved')
      return 'Your request has been approved by the Marketing Office.';
    if (s == 'Posted') return 'Your content has been successfully published.';
    if (s == 'Rejected')
      return 'Your request was not approved. Please check feedback.';
    return 'Your request is being processed.';
  }

  String _fmt(String dt) {
    if (dt.isEmpty) return '';
    try {
      final d = DateTime.parse(dt);
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
      final h = d.hour > 12 ? d.hour - 12 : (d.hour == 0 ? 12 : d.hour);
      return '${m[d.month - 1]} ${d.day}, ${d.year} · $h:${d.minute.toString().padLeft(2, '0')} ${d.hour >= 12 ? 'PM' : 'AM'}';
    } catch (_) {
      return dt;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFE9EDF6),
      body: FadeTransition(
        opacity: _entryFade,
        child: Stack(
          children: [
            Column(
              children: [
                _buildHeader(),
                Expanded(
                  child: _isLoading
                      ? const Center(
                          child: CircularProgressIndicator(
                            color: Color(0xFF002366),
                          ),
                        )
                      : _notifications.isEmpty
                      ? _buildEmpty()
                      : _buildList(),
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
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(bottom: BorderSide(color: Color(0x0F000000))),
        boxShadow: [
          BoxShadow(
            color: Color(0x07001540),
            blurRadius: 12,
            offset: Offset(0, 1),
          ),
        ],
      ),
      padding: EdgeInsets.fromLTRB(
        20,
        MediaQuery.of(context).padding.top + 18,
        20,
        18,
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
                    fontWeight: FontWeight.w900,
                    fontSize: 24,
                    color: Color(0xFF002366),
                    letterSpacing: -0.5,
                  ),
                ),
                SizedBox(height: 3),
                Text(
                  'Stay updated on your requests',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 13,
                    color: Color(0xFF9AA3B2),
                  ),
                ),
              ],
            ),
          ),
          if (_unreadCount > 0)
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                GestureDetector(
                  onTap: _markAllAsRead,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 7,
                    ),
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF001540), Color(0xFF0032A0)],
                      ),
                      borderRadius: BorderRadius.circular(10),
                      boxShadow: const [
                        BoxShadow(
                          color: Color(0x30001540),
                          blurRadius: 8,
                          offset: Offset(0, 3),
                        ),
                      ],
                    ),
                    child: const Text(
                      'Mark all read',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w700,
                        fontSize: 11,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 6),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFF3B30),
                    borderRadius: BorderRadius.circular(99),
                    boxShadow: const [
                      BoxShadow(
                        color: Color(0x40FF3B30),
                        blurRadius: 8,
                        offset: Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Text(
                    _unreadCount > 99 ? '99+' : '$_unreadCount',
                    style: const TextStyle(
                      fontFamily: 'DM Sans',
                      fontWeight: FontWeight.w900,
                      fontSize: 11,
                      color: Colors.white,
                    ),
                  ),
                ),
              ],
            ),
        ],
      ),
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.only(bottom: 80),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 72,
              height: 72,
              decoration: BoxDecoration(
                color: const Color(0xFF9AA3B2).withOpacity(0.08),
                borderRadius: BorderRadius.circular(24),
              ),
              child: const Icon(
                Icons.notifications_none_rounded,
                size: 36,
                color: Color(0xFF9AA3B2),
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'No notifications yet',
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w700,
                fontSize: 15,
                color: Color(0xFF3D4A63),
              ),
            ),
            const SizedBox(height: 6),
            const Text(
              "You'll be notified about your request updates here",
              textAlign: TextAlign.center,
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 12.5,
                color: Color(0xFF9AA3B2),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildList() {
    final groups = _groupNotifications();
    return RefreshIndicator(
      onRefresh: () => _loadNotifications(showLoading: false),
      color: const Color(0xFF002366),
      child: ListView.separated(
        padding: const EdgeInsets.fromLTRB(16, 14, 16, 12),
        itemCount: groups.length,
        separatorBuilder: (_, __) => const SizedBox(height: 10),
        itemBuilder: (_, i) => _NotifCard(
          group: groups[i],
          onTap: () => _handleGroupTap(groups[i]),
        ),
      ),
    );
  }
}

// ── Notification Card ─────────────────────────────────────────────────────────
class _NotifCard extends StatelessWidget {
  final _NotifGroup group;
  final VoidCallback onTap;
  const _NotifCard({required this.group, required this.onTap});

  Color _accent(String type) {
    switch (type) {
      case 'approved':
        return const Color(0xFF05C46B);
      case 'rejected':
        return const Color(0xFFFF3B30);
      case 'posted':
      case 'comment':
        return const Color(0xFF4B7BF5);
      case 'review':
      case 'under_review':
      case 'received':
        return const Color(0xFFF59E0B);
      default:
        return const Color(0xFF2B5CE6);
    }
  }

  IconData _icon(String type) {
    switch (type) {
      case 'approved':
        return Icons.check_circle_rounded;
      case 'rejected':
        return Icons.cancel_rounded;
      case 'posted':
        return Icons.send_rounded;
      case 'comment':
        return Icons.chat_bubble_rounded;
      case 'review':
      case 'under_review':
      case 'received':
        return Icons.access_time_rounded;
      default:
        return Icons.notifications_rounded;
    }
  }

  String _timeAgo(DateTime t) {
    final diff = DateTime.now().difference(t);
    if (diff.inMinutes < 1) return 'Just now';
    if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
    if (diff.inHours < 24) return '${diff.inHours}h ago';
    if (diff.inDays < 7) return '${diff.inDays}d ago';
    return '${t.month}/${t.day}/${t.year}';
  }

  @override
  Widget build(BuildContext context) {
    final n = group.latest;
    final accent = _accent(n.type);
    final hasUnread = group.unreadCount > 0;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: hasUnread ? const Color(0xFFF0F5FF) : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: hasUnread
                ? const Color(0xFF2B5CE6).withOpacity(0.22)
                : const Color(0x0E000000),
            width: hasUnread ? 1.5 : 1,
          ),
          boxShadow: [
            BoxShadow(
              color: const Color(
                0xFF001540,
              ).withOpacity(hasUnread ? 0.07 : 0.04),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: IntrinsicHeight(
          child: Row(
            children: [
              // Left accent bar
              AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                width: hasUnread ? 4 : 0,
                decoration: BoxDecoration(
                  color: accent,
                  borderRadius: const BorderRadius.only(
                    topLeft: Radius.circular(20),
                    bottomLeft: Radius.circular(20),
                  ),
                ),
              ),
              Expanded(
                child: Padding(
                  padding: EdgeInsets.fromLTRB(hasUnread ? 14 : 18, 14, 18, 14),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Icon
                      Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          color: accent.withOpacity(0.1),
                          border: Border.all(
                            color: accent.withOpacity(0.18),
                            width: 1.5,
                          ),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        child: Icon(_icon(n.type), color: accent, size: 20),
                      ),
                      const SizedBox(width: 13),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    n.title,
                                    style: TextStyle(
                                      fontFamily: 'DM Sans',
                                      fontWeight: hasUnread
                                          ? FontWeight.w900
                                          : FontWeight.w700,
                                      fontSize: 13.5,
                                      color: const Color(0xFF080F1E),
                                      letterSpacing: -0.1,
                                    ),
                                  ),
                                ),
                                if (group.totalCount > 1)
                                  Container(
                                    margin: const EdgeInsets.only(left: 6),
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 7,
                                      vertical: 3,
                                    ),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFFE9EDF6),
                                      borderRadius: BorderRadius.circular(99),
                                    ),
                                    child: Text(
                                      '${group.totalCount}',
                                      style: const TextStyle(
                                        fontFamily: 'DM Sans',
                                        fontWeight: FontWeight.w700,
                                        fontSize: 10,
                                        color: Color(0xFF3D4A63),
                                      ),
                                    ),
                                  ),
                                if (hasUnread)
                                  Container(
                                    margin: const EdgeInsets.only(left: 6),
                                    width: 7,
                                    height: 7,
                                    decoration: BoxDecoration(
                                      color: accent,
                                      shape: BoxShape.circle,
                                      boxShadow: [
                                        BoxShadow(
                                          color: accent.withOpacity(0.5),
                                          blurRadius: 4,
                                        ),
                                      ],
                                    ),
                                  ),
                              ],
                            ),
                            const SizedBox(height: 4),
                            Text(
                              n.message,
                              style: const TextStyle(
                                fontFamily: 'DM Sans',
                                fontWeight: FontWeight.w400,
                                fontSize: 12.5,
                                color: Color(0xFF3D4A63),
                                height: 1.5,
                              ),
                            ),
                            const SizedBox(height: 7),
                            Text(
                              _timeAgo(n.createdAt),
                              style: const TextStyle(
                                fontFamily: 'DM Sans',
                                fontWeight: FontWeight.w600,
                                fontSize: 10.5,
                                color: Color(0xFF9AA3B2),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Data models ───────────────────────────────────────────────────────────────
class _Notif {
  final int id;
  final int? requestId;
  final String title, message, type;
  final bool isRead;
  final DateTime createdAt;
  final String? requestStatus;
  const _Notif({
    required this.id,
    required this.requestId,
    required this.title,
    required this.message,
    required this.type,
    required this.isRead,
    required this.createdAt,
    required this.requestStatus,
  });
  factory _Notif.fromJson(Map<String, dynamic> json) {
    int? parseInt(dynamic v) {
      if (v == null) return null;
      if (v is int) return v;
      if (v is num) return v.toInt();
      return int.tryParse(v.toString());
    }

    final s = (json['request_status'] ?? '').toString().trim();
    return _Notif(
      id: (json['id'] as num?)?.toInt() ?? 0,
      requestId: parseInt(json['request_id']),
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
      requestStatus: s.isEmpty ? null : s,
    );
  }
  _Notif copyWith({bool? isRead}) => _Notif(
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

class _NotifGroup {
  final List<_Notif> items;
  const _NotifGroup({required this.items});
  _Notif get latest => items.first;
  int get totalCount => items.length;
  int get unreadCount => items.where((n) => !n.isRead).length;
}
