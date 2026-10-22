import 'dart:async';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';
import '../services/app_memory_cache.dart';
import 'request_tracking_screen.dart';
import 'message_thread_screen.dart';
import '../utils/app_date_time_utils.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});
  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen>
    with TickerProviderStateMixin {
  List<_Notif> _notifications = [];
  int _unreadCount = 0;
  bool _isLoading = true;
  Timer? _pollTimer;
  bool _isNavigating = false;

  // Filter tabs: 0=All, 1=Unread, 2=Status, 3=Messages
  int _filterIndex = 0;

  late final AnimationController _entryCtrl;
  late final Animation<double> _entryFade;

  late final AnimationController _listCtrl;

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

    _listCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 800),
    );

    // Warm-start from session cache: 0ms instant display, no skeleton if already loaded
    if (AppMemoryCache.hasNotifications) {
      _notifications = AppMemoryCache.notifications!
          .whereType<Map<String, dynamic>>()
          .map(_Notif.fromJson)
          .toList();
      _unreadCount = AppMemoryCache.notificationsUnreadCount;
      _isLoading = false;
    }

    _loadNotifications(showLoading: !AppMemoryCache.hasNotifications);
    _pollTimer = Timer.periodic(
      const Duration(seconds: 10),
      (_) => _loadNotifications(showLoading: false),
    );
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _entryCtrl.dispose();
    _listCtrl.dispose();
    super.dispose();
  }

  // ── All original logic preserved ─────────────────────────────────────────
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
        final listMaps = notifList.whereType<Map<String, dynamic>>().toList();
        AppMemoryCache.notifications = listMaps;
        AppMemoryCache.notificationsUnreadCount = unread;
        setState(() {
          _notifications = listMaps
              .map(_Notif.fromJson)
              .toList();
          _unreadCount = unread;
          _isLoading = false;
        });
        _listCtrl.reset();
        _listCtrl.forward();
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

    // 1. Instant optimistic UI update (0ms latency feedback)
    if (mounted) {
      setState(() {
        _notifications = _notifications
            .map((n) => unreadIds.contains(n.id) ? n.copyWith(isRead: true) : n)
            .toList();
        _unreadCount = (_unreadCount - unreadIds.length).clamp(
          0,
          _notifications.length,
        );
      });
      AppMemoryCache.notificationsUnreadCount = _unreadCount;
    }

    // 2. Parallel network persistence in the background
    try {
      await Future.wait(
        unreadIds.map(
          (id) => ApiService.markNotificationRead(
            userId: userId,
            notificationId: id,
          ),
        ),
      );
    } catch (_) {}
  }

  void _dismissNotif(int id) {
    setState(() {
      _notifications = _notifications.where((n) => n.id != id).toList();
    });
  }

  Future<void> _markAllAsRead() async {
    final userId = SessionStore.userId;
    if (userId == null || userId == 0) return;

    if (mounted) {
      setState(() {
        _notifications = _notifications
            .map((n) => n.copyWith(isRead: true))
            .toList();
        _unreadCount = 0;
      });
      AppMemoryCache.notificationsUnreadCount = 0;
    }

    try {
      await ApiService.markAllNotificationsRead(userId: userId);
    } catch (_) {}
  }

  List<_NotifGroup> _groupNotifications() {
    // Apply filter
    final filtered = _filteredNotifications();

    final grouped = <String, List<_Notif>>{};
    for (final n in filtered) {
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

  List<_Notif> _filteredNotifications() {
    switch (_filterIndex) {
      case 1: // Unread
        return _notifications.where((n) => !n.isRead).toList();
      case 2: // Status updates
        return _notifications.where((n) => _isTracking(n.type)).toList();
      case 3: // Messages/comments
        return _notifications.where((n) => _isMsg(n.type)).toList();
      default:
        return _notifications;
    }
  }

  // Count per tab for badges
  int _countFor(int tab) {
    switch (tab) {
      case 1:
        return _notifications.where((n) => !n.isRead).length;
      case 2:
        return _notifications.where((n) => _isTracking(n.type)).length;
      case 3:
        return _notifications.where((n) => _isMsg(n.type)).length;
      default:
        return _notifications.length;
    }
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
    // Prevent rapid consecutive taps from opening multiple screens
    if (_isNavigating) return;
    _isNavigating = true;

    try {
      if (group.unreadCount > 0) {
        unawaited(_markGroupAsRead(group));
      }
      final latest = group.latest;
      if (_isMsg(latest.type)) {
        if (latest.requestId != null && latest.requestId! > 0) {
          await Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) => MessageThreadScreen(
                requestId: latest.requestId!,
                requestCode: 'REQ-${latest.requestId}',
                requestTitle: latest.title,
                requestStatus: latest.requestStatus ?? 'Pending Review',
              ),
            ),
          );
        } else {
          await Navigator.of(context).pushNamed('/messages');
        }
      } else if (latest.requestId != null && latest.requestId! > 0) {
        await _openTracking(latest);
      } else if (_isTracking(latest.type)) {
        await Navigator.of(context).pushNamed('/requests');
      }
      if (mounted) {
        _loadNotifications(showLoading: false);
      }
    } finally {
      _isNavigating = false;
    }
  }

  Future<void> _openTracking(_Notif n) async {
    final requestId = n.requestId;
    if (requestId == null || requestId <= 0 || !mounted) return;
    final currentStatus = n.requestStatus ?? 'Pending';
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => RequestTrackingScreen(
          requestId: requestId,
          requestNumber: 'REQ-$requestId',
          requestTitle: n.title,
          currentStatus: currentStatus,
          currentStatusMessage: _statusMsg(currentStatus),
        ),
      ),
    );
  }

  String _statusMsg(String s) {
    if (s == 'Pending') return 'Your request is currently queued for review.';
    if (s == 'Approved') {
      return 'Your request has been approved by the Marketing Office.';
    }
    if (s == 'Posted') return 'Your content has been successfully published.';
    if (s == 'Rejected') {
      return 'Your request was not approved. Please check feedback.';
    }
    return 'Your request is being processed.';
  }


  // ── BUILD ─────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final scaffoldBg = isDark ? const Color(0xFF0A0F1D) : const Color(0xFFE9EDF6);

    return Scaffold(
      resizeToAvoidBottomInset: false,
      backgroundColor: scaffoldBg,
      body: Stack(
        children: [
          FadeTransition(
            opacity: _entryFade,
            child: Column(
              children: [
                _buildHeader(isDark),
                _buildFilterTabs(isDark),
                Expanded(child: _isLoading ? _buildSkeleton(isDark) : _buildList()),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ── Header ────────────────────────────────────────────────────────────────
  Widget _buildHeader(bool isDark) {
    final headerBg = isDark ? const Color(0xFF131D31) : Colors.white;
    final borderCol = isDark ? const Color(0xFF1E2B45) : const Color(0x0F000000);
    final titleCol = isDark ? Colors.white : const Color(0xFF002366);
    final subCol = isDark ? const Color(0xFF94A3B8) : const Color(0xFF9AA3B2);

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: headerBg,
        border: Border(bottom: BorderSide(color: borderCol)),
        boxShadow: [
          BoxShadow(
            color: isDark ? Colors.black26 : const Color(0x07001540),
            blurRadius: 12,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      padding: EdgeInsets.fromLTRB(
        20,
        MediaQuery.of(context).padding.top + 18,
        20,
        14,
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Notifications',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w900,
                    fontSize: 24,
                    color: titleCol,
                    letterSpacing: -0.5,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  'Stay updated on your requests',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 13,
                    color: subCol,
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
                      gradient: LinearGradient(
                        colors: isDark
                            ? const [Color(0xFF1E3A8A), Color(0xFF2563EB)]
                            : const [Color(0xFF001540), Color(0xFF0032A0)],
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

  // ── Filter tabs ───────────────────────────────────────────────────────────
  Widget _buildFilterTabs(bool isDark) {
    const labels = ['All', 'Unread', 'Status', 'Messages'];
    const icons = [
      Icons.notifications_rounded,
      Icons.mark_email_unread_rounded,
      Icons.track_changes_rounded,
      Icons.chat_bubble_rounded,
    ];

    final containerBg = isDark ? const Color(0xFF131D31) : Colors.white;
    final activeBg = isDark ? const Color(0xFF2563EB) : const Color(0xFF002366);
    final inactiveBg = isDark ? const Color(0xFF1E2B45) : const Color(0xFFF1F4FB);
    final inactiveText = isDark ? const Color(0xFF94A3B8) : const Color(0xFF9AA3B2);

    return Container(
      color: containerBg,
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 14),
      child: Row(
        children: List.generate(labels.length, (i) {
          final isActive = _filterIndex == i;
          final count = _countFor(i);

          return Expanded(
            child: GestureDetector(
              onTap: () {
                if (_filterIndex != i) {
                  setState(() => _filterIndex = i);
                  _listCtrl.reset();
                  _listCtrl.forward();
                }
              },
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 220),
                curve: Curves.easeOutCubic,
                margin: EdgeInsets.only(right: i < labels.length - 1 ? 8 : 0),
                padding: const EdgeInsets.symmetric(vertical: 8),
                decoration: BoxDecoration(
                  color: isActive ? activeBg : inactiveBg,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: isActive
                      ? [
                          BoxShadow(
                            color: (isDark ? const Color(0xFF2563EB) : const Color(0xFF002366)).withOpacity(0.3),
                            blurRadius: 8,
                            offset: const Offset(0, 3),
                          ),
                        ]
                      : null,
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      icons[i],
                      size: 16,
                      color: isActive ? Colors.white : inactiveText,
                    ),
                    const SizedBox(height: 3),
                    Text(
                      labels[i],
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: isActive
                            ? FontWeight.w800
                            : FontWeight.w500,
                        fontSize: 10,
                        color: isActive ? Colors.white : inactiveText,
                        letterSpacing: 0.1,
                      ),
                    ),
                    // Count badge
                    if (count > 0) ...[
                      const SizedBox(height: 2),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 5,
                          vertical: 1,
                        ),
                        decoration: BoxDecoration(
                          color: isActive
                              ? Colors.white.withOpacity(0.25)
                              : (isDark
                                  ? const Color(0x333B82F6)
                                  : const Color(0xFF002366).withOpacity(0.1)),
                          borderRadius: BorderRadius.circular(99),
                        ),
                        child: Text(
                          count > 99 ? '99+' : '$count',
                          style: TextStyle(
                            fontFamily: 'DM Sans',
                            fontWeight: FontWeight.w800,
                            fontSize: 8.5,
                            color: isActive
                                ? Colors.white
                                : (isDark ? const Color(0xFF93C5FD) : const Color(0xFF002366)),
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          );
        }),
      ),
    );
  }

  // ── Skeleton loader ───────────────────────────────────────────────────────
  Widget _buildSkeleton(bool isDark) {
    final cardBg = isDark ? const Color(0xFF131D31) : Colors.white;
    final cardBorder = isDark ? const Color(0xFF1E2B45) : const Color(0x0E000000);
    final shimmerBase = isDark ? const Color(0xFF1E2B45) : const Color(0xFFE9EDF6);

    return ListView.separated(
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 12),
      itemCount: 5,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (_, i) {
        // Staggered opacity
        return TweenAnimationBuilder<double>(
          tween: Tween(begin: 0.3, end: 0.8),
          duration: Duration(milliseconds: 800 + (i * 100)),
          curve: Curves.easeInOut,
          builder: (_, val, __) => Opacity(
            opacity: val,
            child: Container(
              height: 90,
              decoration: BoxDecoration(
                color: cardBg,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: cardBorder),
              ),
              child: Row(
                children: [
                  const SizedBox(width: 18),
                  // Icon placeholder
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: shimmerBase,
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                  const SizedBox(width: 13),
                  Expanded(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          height: 12,
                          width: 160,
                          decoration: BoxDecoration(
                            color: shimmerBase,
                            borderRadius: BorderRadius.circular(6),
                          ),
                        ),
                        const SizedBox(height: 8),
                        Container(
                          height: 10,
                          width: 220,
                          decoration: BoxDecoration(
                            color: shimmerBase,
                            borderRadius: BorderRadius.circular(6),
                          ),
                        ),
                        const SizedBox(height: 6),
                        Container(
                          height: 10,
                          width: 100,
                          decoration: BoxDecoration(
                            color: shimmerBase,
                            borderRadius: BorderRadius.circular(6),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 18),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  // ── Main list ─────────────────────────────────────────────────────────────
  Widget _buildList() {
    final groups = _groupNotifications();
    final isDark = Theme.of(context).brightness == Brightness.dark;

    if (groups.isEmpty) return _buildEmpty(isDark);

    return RefreshIndicator(
      onRefresh: () => _loadNotifications(showLoading: false),
      color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF002366),
      child: ListView.separated(
        padding: const EdgeInsets.fromLTRB(16, 14, 16, 12),
        itemCount: groups.length,
        separatorBuilder: (_, __) => const SizedBox(height: 10),
        itemBuilder: (_, i) {
          final group = groups[i];

          // Stagger per item
          final start = (i * 0.10).clamp(0.0, 0.7);
          final end = (start + 0.45).clamp(0.0, 1.0);
          final anim = Tween<double>(begin: 0.0, end: 1.0).animate(
            CurvedAnimation(
              parent: _listCtrl,
              curve: Interval(start, end, curve: Curves.easeOutCubic),
            ),
          );

          return RepaintBoundary(
            child: AnimatedBuilder(
              animation: anim,
              builder: (_, child) => Opacity(
                opacity: anim.value,
                child: Transform.translate(
                  offset: Offset(0, (1 - anim.value) * 14),
                  child: child,
                ),
              ),
              child: _SwipeablNotifCard(
                group: group,
                onTap: () => _handleGroupTap(group),
                onMarkRead: group.unreadCount > 0
                    ? () => _markGroupAsRead(group)
                    : null,
                onDismiss: () => _dismissNotif(group.latest.id),
              ),
            ),
          );
        },
      ),
    );
  }

  // ── Empty state (per filter) ──────────────────────────────────────────────
  Widget _buildEmpty(bool isDark) {
    final labels = [
      'notifications',
      'unread notifications',
      'status updates',
      'messages',
    ];
    final icons = [
      Icons.notifications_none_rounded,
      Icons.mark_email_read_rounded,
      Icons.check_circle_outline_rounded,
      Icons.forum_outlined,
    ];

    final iconBg = isDark ? const Color(0xFF1E2B45) : const Color(0xFF9AA3B2).withOpacity(0.08);
    final iconColor = isDark ? const Color(0xFF94A3B8) : const Color(0xFF9AA3B2);
    final titleColor = isDark ? Colors.white : const Color(0xFF3D4A63);
    final subColor = isDark ? const Color(0xFF94A3B8) : const Color(0xFF9AA3B2);
    final btnBorder = isDark ? const Color(0xFF2563EB).withOpacity(0.4) : const Color(0xFF002366).withOpacity(0.2);
    final btnText = isDark ? const Color(0xFF60A5FA) : const Color(0xFF002366);

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
                color: iconBg,
                borderRadius: BorderRadius.circular(24),
              ),
              child: Icon(
                icons[_filterIndex],
                size: 36,
                color: iconColor,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              'No ${labels[_filterIndex]}',
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w700,
                fontSize: 15,
                color: titleColor,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              _filterIndex == 0
                  ? "You'll be notified about your request updates here"
                  : 'Try switching to a different filter above',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 12.5,
                color: subColor,
              ),
            ),
            if (_filterIndex != 0) ...[
              const SizedBox(height: 16),
              GestureDetector(
                onTap: () {
                  setState(() => _filterIndex = 0);
                  _listCtrl.reset();
                  _listCtrl.forward();
                },
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 20,
                    vertical: 10,
                  ),
                  decoration: BoxDecoration(
                    border: Border.all(
                      color: btnBorder,
                      width: 1.5,
                    ),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    'Show all',
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      fontWeight: FontWeight.w700,
                      fontSize: 13,
                      color: btnText,
                    ),
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

// ── Swipeable notification card ───────────────────────────────────────────────
class _SwipeablNotifCard extends StatelessWidget {
  final _NotifGroup group;
  final VoidCallback onTap;
  final VoidCallback? onMarkRead;
  final VoidCallback onDismiss;

  const _SwipeablNotifCard({
    required this.group,
    required this.onTap,
    required this.onMarkRead,
    required this.onDismiss,
  });

  @override
  Widget build(BuildContext context) {
    return Dismissible(
      key: ValueKey('notif-${group.latest.id}'),
      direction: DismissDirection.endToStart,
      background: Container(
        alignment: Alignment.centerRight,
        padding: const EdgeInsets.only(right: 20),
        decoration: BoxDecoration(
          color: const Color(0xFFFF3B30).withOpacity(0.12),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFFF3B30).withOpacity(0.2)),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: const [
            Icon(
              Icons.delete_outline_rounded,
              color: Color(0xFFFF3B30),
              size: 22,
            ),
            SizedBox(height: 4),
            Text(
              'Dismiss',
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w700,
                fontSize: 10,
                color: Color(0xFFFF3B30),
              ),
            ),
          ],
        ),
      ),
      confirmDismiss: (_) async {
        // If unread, mark read instead of dismiss first time
        if (onMarkRead != null) {
          onMarkRead!();
          return false;
        }
        return true;
      },
      onDismissed: (_) => onDismiss(),
      child: _NotifCard(group: group, onTap: onTap),
    );
  }
}

// ── Notification Card ─────────────────────────────────────────────────────────
class _NotifCard extends StatelessWidget {
  final _NotifGroup group;
  final VoidCallback onTap;
  const _NotifCard({required this.group, required this.onTap});

  Color _accent(_Notif n) {
    // If notification has a request status attached, match the exact color from Requests & Home screens!
    final status = (n.requestStatus ?? '').toLowerCase().trim();
    if (status.contains('approved')) return const Color(0xFF10B981);
    if (status.contains('rejected')) return const Color(0xFFEF4444);
    if (status.contains('posted')) return const Color(0xFF8B5CF6);
    if (status.contains('pending') ||
        status.contains('review') ||
        status.contains('received')) {
      return const Color(0xFFF59E0B);
    }

    final tit = n.title.toLowerCase();
    if (tit.contains('posting request') ||
        tit.contains('new request') ||
        tit.contains('submitted')) {
      return const Color(0xFFF59E0B);
    }

    switch (n.type.toLowerCase()) {
      case 'approved':
        return const Color(0xFF10B981);
      case 'rejected':
        return const Color(0xFFEF4444);
      case 'posted':
        return const Color(0xFF8B5CF6);
      case 'comment':
      case 'message':
        return const Color(0xFF3B82F6);
      case 'new_request':
        return const Color(0xFFF59E0B);
      case 'api_key':
        return const Color(0xFF6366F1);
      case 'review':
      case 'under_review':
      case 'received':
      case 'pending':
      case 'status_update':
        return const Color(0xFFF59E0B);
      default:
        return const Color(0xFF3B82F6);
    }
  }

  IconData _icon(String type, String title) {
    final t = type.toLowerCase();
    final tit = title.toLowerCase();
    if (tit.contains('posting request') || tit.contains('new request')) {
      return Icons.assignment_outlined;
    }
    switch (t) {
      case 'approved':
        return Icons.check_circle_rounded;
      case 'rejected':
        return Icons.cancel_rounded;
      case 'posted':
        return Icons.send_rounded;
      case 'comment':
      case 'message':
        return Icons.chat_bubble_rounded;
      case 'new_request':
        return Icons.assignment_turned_in_outlined;
      case 'api_key':
        return Icons.key_rounded;
      case 'review':
      case 'under_review':
      case 'received':
        return Icons.access_time_rounded;
      case 'status_update':
        return Icons.assignment_outlined;
      default:
        return Icons.notifications_rounded;
    }
  }

  String _timeAgo(DateTime t) => AppDateTimeUtils.formatTimeAgo(t);

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final n = group.latest;
    final accent = _accent(n);
    final hasUnread = group.unreadCount > 0;

    final cardBg = isDark
        ? (hasUnread ? const Color(0xFF162032) : const Color(0xFF131D31))
        : (hasUnread ? const Color(0xFFF0F5FF) : Colors.white);
    final cardBorder = isDark
        ? (hasUnread ? accent.withOpacity(0.35) : const Color(0xFF1E2B45))
        : (hasUnread ? accent.withOpacity(0.25) : const Color(0x0E000000));
    final titleColor = isDark ? Colors.white : const Color(0xFF080F1E);
    final msgColor = isDark ? const Color(0xFFCBD5E1) : const Color(0xFF3D4A63);
    final badgeBg = isDark ? const Color(0xFF1E2B45) : const Color(0xFFE9EDF6);
    final badgeText = isDark ? const Color(0xFFE2E8F0) : const Color(0xFF3D4A63);
    final timeColor = isDark ? const Color(0xFF94A3B8) : const Color(0xFF9AA3B2);

    return Material(
      color: Colors.transparent,
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: onTap,
        splashColor: accent.withOpacity(0.12),
        highlightColor: accent.withOpacity(0.06),
        child: Container(
          decoration: BoxDecoration(
            color: cardBg,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: cardBorder,
              width: 1,
            ),
            boxShadow: [
              BoxShadow(
                color: isDark
                    ? const Color(0x30000000)
                    : const Color(0xFF001540).withOpacity(hasUnread ? 0.07 : 0.04),
                blurRadius: 12,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              // Left accent pill (matches homescreen and requests screen exactly!)
              Container(
                width: 4,
                height: 56,
                decoration: BoxDecoration(
                  color: hasUnread ? accent : Colors.transparent,
                  borderRadius: const BorderRadius.only(
                    topRight: Radius.circular(4),
                    bottomRight: Radius.circular(4),
                  ),
                ),
              ),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(14, 14, 16, 14),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Icon box
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
                        child: Icon(_icon(n.type, n.title), color: accent, size: 20),
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
                                      color: titleColor,
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
                                      color: badgeBg,
                                      borderRadius: BorderRadius.circular(99),
                                    ),
                                    child: Text(
                                      '${group.totalCount}',
                                      style: TextStyle(
                                        fontFamily: 'DM Sans',
                                        fontWeight: FontWeight.w700,
                                        fontSize: 10,
                                        color: badgeText,
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
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontWeight: FontWeight.w400,
                                fontSize: 12.5,
                                color: msgColor,
                                height: 1.5,
                              ),
                            ),
                            const SizedBox(height: 7),
                            Row(
                              children: [
                                Icon(
                                  Icons.access_time_rounded,
                                  size: 11,
                                  color: timeColor,
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  _timeAgo(n.createdAt),
                                  style: TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontWeight: FontWeight.w600,
                                    fontSize: 10.5,
                                    color: timeColor,
                                  ),
                                ),
                                const Spacer(),
                                if (hasUnread)
                                  Row(
                                    children: [
                                      Icon(
                                        Icons.swipe_left_alt_rounded,
                                        size: 12,
                                        color: timeColor,
                                      ),
                                      const SizedBox(width: 3),
                                      Text(
                                        'swipe to mark read',
                                        style: TextStyle(
                                          fontFamily: 'DM Sans',
                                          fontSize: 9.5,
                                          color: timeColor,
                                        ),
                                      ),
                                    ],
                                  ),
                              ],
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

// ── Data models (unchanged) ───────────────────────────────────────────────────
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
          AppDateTimeUtils.parseServerDateTime(json['created_at']),
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
