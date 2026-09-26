import 'dart:async';
import 'package:flutter/material.dart';
import 'api_service.dart';
import 'app_memory_cache.dart';
import 'session_store.dart';
import '../screens/notifications_screen.dart';

final GlobalKey<NavigatorState> rootNavigatorKey = GlobalKey<NavigatorState>();

class InAppNotificationService {
  static Timer? _pollTimer;
  static final Set<int> _knownNotificationIds = {};
  static bool _initialFetchDone = false;
  static OverlayEntry? _currentOverlay;
  static Timer? _dismissTimer;

  static void startListening() {
    stopListening();
    _knownNotificationIds.clear();
    _initialFetchDone = false;

    // Fast 3-second polling for near-instant notification delivery & live status sync
    _pollTimer = Timer.periodic(const Duration(seconds: 3), (_) => _checkNewNotifications());
    // Initial fetch
    _checkNewNotifications();
  }

  static void stopListening() {
    _pollTimer?.cancel();
    _pollTimer = null;
    _dismissCurrent();
  }

  static void _dismissCurrent() {
    _dismissTimer?.cancel();
    _dismissTimer = null;
    _currentOverlay?.remove();
    _currentOverlay = null;
  }

  static Future<void> _checkNewNotifications() async {
    final userId = SessionStore.userId;
    if (userId == null || userId == 0) return;

    try {
      final response = await ApiService.fetchNotifications(userId: userId);
      if (response['success'] != true) return;
      final data = response['data'] ?? {};
      final notifList = (data['notifications'] as List? ?? []).whereType<Map<String, dynamic>>().toList();

      if (!_initialFetchDone) {
        for (final n in notifList) {
          final id = (n['id'] as num?)?.toInt() ?? 0;
          if (id > 0) _knownNotificationIds.add(id);
        }
        _initialFetchDone = true;
        return;
      }

      // Check for newly arrived unread notifications
      for (final n in notifList) {
        final id = (n['id'] as num?)?.toInt() ?? 0;
        final isRead = n['is_read'] == true || n['is_read'] == 1;
        if (id > 0 && !_knownNotificationIds.contains(id)) {
          _knownNotificationIds.add(id);
          if (!isRead) {
            final title = (n['title'] ?? 'NUPost Update').toString();
            final message = (n['message'] ?? '').toString();
            final type = (n['type'] ?? 'info').toString();

            // Real-time status update: invalidate cached requests & stats so active screens reload immediately
            AppMemoryCache.invalidateRequests();
            AppMemoryCache.invalidateNotifications();

            showBanner(
              title: title,
              message: message,
              type: type,
              onTap: () {
                final nav = rootNavigatorKey.currentState;
                if (nav != null) {
                  nav.push(MaterialPageRoute(builder: (_) => const NotificationsScreen()));
                }
              },
            );
            break; // Show latest first, don't spam
          }
        }
      }
    } catch (_) {}
  }

  /// Manually trigger a floating heads-up banner across the app
  static void showBanner({
    required String title,
    required String message,
    String type = 'message',
    VoidCallback? onTap,
    Duration duration = const Duration(seconds: 4),
  }) {
    final overlayState = rootNavigatorKey.currentState?.overlay;
    if (overlayState == null) return;

    _dismissCurrent();

    late OverlayEntry entry;
    entry = OverlayEntry(
      builder: (context) => _FloatingHeadsUpBanner(
        title: title,
        message: message,
        type: type,
        onTap: () {
          _dismissCurrent();
          onTap?.call();
        },
        onDismiss: () {
          _dismissCurrent();
        },
      ),
    );

    _currentOverlay = entry;
    overlayState.insert(entry);

    _dismissTimer = Timer(duration, () {
      _dismissCurrent();
    });
  }
}

class _FloatingHeadsUpBanner extends StatefulWidget {
  final String title;
  final String message;
  final String type;
  final VoidCallback onTap;
  final VoidCallback onDismiss;

  const _FloatingHeadsUpBanner({
    required this.title,
    required this.message,
    required this.type,
    required this.onTap,
    required this.onDismiss,
  });

  @override
  State<_FloatingHeadsUpBanner> createState() => _FloatingHeadsUpBannerState();
}

class _FloatingHeadsUpBannerState extends State<_FloatingHeadsUpBanner>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<Offset> _slideAnim;
  late final Animation<double> _fadeAnim;
  double _dragOffsetY = 0.0;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 400),
    )..forward();

    _slideAnim = Tween<Offset>(
      begin: const Offset(0, -1.2),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _ctrl, curve: Curves.easeOutCubic));

    _fadeAnim = CurvedAnimation(parent: _ctrl, curve: Curves.easeOut);
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  void _dismissWithAnim() async {
    await _ctrl.reverse();
    widget.onDismiss();
  }

  @override
  Widget build(BuildContext context) {
    final mediaQuery = MediaQuery.of(context);
    final topPad = mediaQuery.padding.top;
    final isDark = SessionStore.isDarkModeNotifier.value;

    IconData iconData = Icons.chat_bubble_rounded;
    Color iconBg = const Color(0xFF002366);
    Color iconColor = Colors.white;

    final lower = widget.type.toLowerCase();
    if (lower.contains('approved') || lower.contains('posted')) {
      iconData = Icons.check_circle_rounded;
      iconBg = const Color(0xFF05C46B);
    } else if (lower.contains('review') || lower.contains('pending')) {
      iconData = Icons.hourglass_top_rounded;
      iconBg = const Color(0xFFF59E0B);
    } else if (lower.contains('admin')) {
      iconData = Icons.shield_rounded;
      iconBg = const Color(0xFF1D4ED8);
    }

    return Positioned(
      top: topPad + 10 + _dragOffsetY,
      left: 14,
      right: 14,
      child: Material(
        color: Colors.transparent,
        child: SlideTransition(
          position: _slideAnim,
          child: FadeTransition(
            opacity: _fadeAnim,
            child: GestureDetector(
              onTap: widget.onTap,
              onVerticalDragUpdate: (details) {
                if (details.primaryDelta != null && details.primaryDelta! < 0) {
                  setState(() {
                    _dragOffsetY += details.primaryDelta!;
                  });
                }
              },
              onVerticalDragEnd: (details) {
                if (_dragOffsetY < -20 || (details.primaryVelocity ?? 0) < -200) {
                  _dismissWithAnim();
                } else {
                  setState(() => _dragOffsetY = 0.0);
                }
              },
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF131D31) : Colors.white,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: isDark
                        ? const Color(0x33F59E0B)
                        : const Color(0x18002366),
                    width: 1.2,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: isDark
                          ? const Color(0x66000000)
                          : const Color(0x22001540),
                      blurRadius: 20,
                      offset: const Offset(0, 8),
                      spreadRadius: 2,
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    // Icon / Avatar circle
                    Container(
                      width: 42,
                      height: 42,
                      decoration: BoxDecoration(
                        color: iconBg,
                        borderRadius: BorderRadius.circular(14),
                        boxShadow: [
                          BoxShadow(
                            color: iconBg.withOpacity(0.35),
                            blurRadius: 8,
                            offset: const Offset(0, 3),
                          ),
                        ],
                      ),
                      alignment: Alignment.center,
                      child: Icon(iconData, size: 22, color: iconColor),
                    ),
                    const SizedBox(width: 12),
                    // Content
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Row(
                            children: [
                              Expanded(
                                child: Text(
                                  widget.title,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontWeight: FontWeight.w800,
                                    fontSize: 13.5,
                                    color: isDark
                                        ? const Color(0xFFF1F5F9)
                                        : const Color(0xFF080F1E),
                                  ),
                                ),
                              ),
                              const SizedBox(width: 6),
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 6,
                                  vertical: 2,
                                ),
                                decoration: BoxDecoration(
                                  color: isDark
                                      ? const Color(0x22F59E0B)
                                      : const Color(0xFFF0F4FF),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  'NOW',
                                  style: TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontWeight: FontWeight.w800,
                                    fontSize: 9.5,
                                    color: isDark
                                        ? const Color(0xFFF59E0B)
                                        : const Color(0xFF1D4ED8),
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 3),
                          Text(
                            widget.message,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontFamily: 'DM Sans',
                              fontWeight: FontWeight.w500,
                              fontSize: 12,
                              color: isDark
                                  ? const Color(0xFF94A3B8)
                                  : const Color(0xFF4A5568),
                              height: 1.25,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
