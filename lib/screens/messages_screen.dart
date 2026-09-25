import 'package:flutter/material.dart';

import '../services/api_service.dart';
import '../services/session_store.dart';
import '../services/app_memory_cache.dart';
import '../theme/app_theme.dart';
import 'message_thread_screen.dart';
import '../services/chat_read_store.dart';
import '../widgets/skeleton_loader.dart';

class MessagesScreen extends StatefulWidget {
  const MessagesScreen({super.key});

  @override
  State<MessagesScreen> createState() => _MessagesScreenState();
}

class _MessagesScreenState extends State<MessagesScreen>
    with TickerProviderStateMixin {
  bool _isLoading = true;
  String? _error;
  List<_ThreadItem> _threads = const [];

  AnimationController? _staggerCtrl;
  late final AnimationController _entryCtrl;
  late final Animation<double> _entryFade;
  late final Animation<Offset> _entrySlide;

  @override
  void initState() {
    super.initState();
    _entryCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 650))..forward();
    _entryFade = CurvedAnimation(parent: _entryCtrl, curve: const Interval(0.0, 0.7, curve: Curves.easeOut));
    _entrySlide = Tween<Offset>(begin: const Offset(0, 0.03), end: Offset.zero)
        .animate(CurvedAnimation(parent: _entryCtrl, curve: const Interval(0.0, 0.8, curve: Curves.easeOutCubic)));
    _staggerCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 800),
    );

    // Warm-start from session cache: 0ms instant display, no skeleton if already loaded
    if (AppMemoryCache.hasMessageThreads) {
      _threads = AppMemoryCache.messageThreads!
          .map(_ThreadItem.fromJson)
          .where((t) =>
              t.lastMessageId > 0 &&
              t.lastMessage.trim().isNotEmpty &&
              !t.lastMessage.startsWith('No messages yet'))
          .toList();
      _isLoading = false;
    }

    _loadThreads(showLoading: !AppMemoryCache.hasMessageThreads);
  }

  @override
  void dispose() {
    _entryCtrl.dispose();
    _staggerCtrl?.dispose();
    super.dispose();
  }

  Future<void> _loadThreads({bool showLoading = true}) async {
    final userId = SessionStore.userId;
    if (userId == null || userId == 0) {
      setState(() {
        _isLoading = false;
        _error = 'Please login first.';
      });
      return;
    }
    if (showLoading && mounted) {
      setState(() {
        _isLoading = true;
        _error = null;
      });
    }
    try {
      final result = await ApiService.fetchMessageThreads(userId: userId);
      final rows = (result['data'] as List?) ?? const [];
      final fetchedThreads = rows
          .whereType<Map<String, dynamic>>()
          .map(_ThreadItem.fromJson)
          .toList();

      AppMemoryCache.messageThreads = rows.whereType<Map<String, dynamic>>().toList();

      // Calculate unread counts locally & filter empty conversation threads
      final isAdmin = SessionStore.role?.toLowerCase() == 'admin';
      final updatedThreads = <_ThreadItem>[];
      for (var t in fetchedThreads) {
        if (t.lastMessageId <= 0 ||
            t.lastMessage.trim().isEmpty ||
            t.lastMessage.startsWith('No messages yet')) {
          continue;
        }

        final lastReadId = await ChatReadStore.getLastReadId(t.requestId);
        final isUnread = t.lastMessageId > lastReadId &&
            (isAdmin ? t.lastSenderRole != 'admin' : t.lastSenderRole == 'admin');
        updatedThreads.add(t.copyWith(unreadCount: isUnread ? 1 : 0));
      }

      setState(() {
        _threads = updatedThreads;
      });
      _staggerCtrl?.reset();
      _staggerCtrl?.forward();
    } catch (e) {
      setState(() {
        _error = e.toString().replaceFirst('Exception: ', '');
      });
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final pageBg = isDark ? const Color(0xFF090D16) : AppColors.pageBg;

    return Scaffold(
      backgroundColor: pageBg,
      body: Stack(
        children: [
          FadeTransition(
            opacity: _entryFade,
            child: SlideTransition(
              position: _entrySlide,
              child: Column(
                children: [
                  _buildHeader(isDark),
                  Expanded(
                    child: _isLoading
                        ? ListView.separated(
                            padding: const EdgeInsets.fromLTRB(16, 14, 16, 100),
                            itemCount: 5,
                            separatorBuilder: (_, __) => const SizedBox(height: 10),
                            itemBuilder: (_, __) => const MessageThreadSkeleton(),
                          )
                        : _error != null
                        ? _buildError(isDark)
                        : _threads.isEmpty
                        ? _buildEmpty(isDark)
                        : RefreshIndicator(
                            onRefresh: _loadThreads,
                            color: AppColors.accent,
                            child: ListView.separated(
                              padding: const EdgeInsets.fromLTRB(16, 14, 16, 100),
                              itemCount: _threads.length,
                              separatorBuilder: (_, __) =>
                                  const SizedBox(height: 10),
                              itemBuilder: (_, i) => RepaintBoundary(
                                child: _StaggerItem(
                                  controller: _staggerCtrl,
                                  index: i,
                                  total: _threads.length,
                                  child: _ThreadCard(
                                    item: _threads[i],
                                    isDark: isDark,
                                    onTap: () async {
                                      await Navigator.of(context).push(
                                        MaterialPageRoute(
                                          builder: (_) => MessageThreadScreen(
                                            requestId: _threads[i].requestId,
                                            requestCode: _threads[i].requestCode,
                                            requestTitle: _threads[i].requestTitle,
                                            requestStatus: _threads[i].requestStatus,
                                          ),
                                        ),
                                      );
                                      _loadThreads(showLoading: false);
                                    },
                                  ),
                                ),
                              ),
                            ),
                          ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Header ────────────────────────────────────────────────────────────────
  Widget _buildHeader(bool isDark) {
    final isAdmin = SessionStore.role?.toLowerCase() == 'admin';

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF131D31) : Colors.white,
        border: Border(
          bottom: BorderSide(
            color: isDark ? const Color(0xFF1E2B45) : const Color(0x0F000000),
            width: 1,
          ),
        ),
        boxShadow: [
          BoxShadow(
            color: isDark ? const Color(0x40000000) : const Color(0x07001540),
            blurRadius: 12,
            offset: const Offset(0, 1),
          ),
        ],
      ),
      padding: EdgeInsets.fromLTRB(
        20,
        MediaQuery.of(context).padding.top + 16,
        16,
        16,
      ),
      child: Row(
        children: [
          // Back button
          GestureDetector(
            behavior: HitTestBehavior.opaque,
            onTap: () {
              if (Navigator.of(context).canPop()) {
                Navigator.of(context).pop();
              } else {
                Navigator.of(context, rootNavigator: true).maybePop();
              }
            },
            child: Container(
              width: 42,
              height: 42,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: isDark
                    ? const Color(0xFF1E2B45)
                    : const Color(0xFF002366).withOpacity(0.07),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(
                Icons.arrow_back_ios_new_rounded,
                size: 16,
                color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF002366),
              ),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Messages',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w900,
                    fontSize: 22,
                    color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF002366),
                    letterSpacing: -0.4,
                  ),
                ),
                Text(
                  isAdmin
                      ? 'Conversations with requesters'
                      : 'Request conversations with admin',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w400,
                    fontSize: 12,
                    color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF9AA3B2),
                  ),
                ),
              ],
            ),
          ),
          // Refresh button
          GestureDetector(
            onTap: _loadThreads,
            child: Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: isDark
                    ? const Color(0xFF1E2B45)
                    : const Color(0xFF002366).withOpacity(0.07),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(
                Icons.refresh_rounded,
                size: 18,
                color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF002366),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Error ─────────────────────────────────────────────────────────────────
  Widget _buildError(bool isDark) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: BoxDecoration(
                color: const Color(0xFFFF3B30).withOpacity(0.08),
                borderRadius: BorderRadius.circular(22),
              ),
              child: const Icon(
                Icons.error_outline_rounded,
                size: 32,
                color: Color(0xFFFF3B30),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              _error ?? 'Something went wrong.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 14,
                color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF3D4A63),
                fontWeight: FontWeight.w500,
              ),
            ),
            const SizedBox(height: 16),
            GestureDetector(
              onTap: _loadThreads,
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 12,
                ),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF001540), Color(0xFF0032A0)],
                  ),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Text(
                  'Retry',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w800,
                    fontSize: 13,
                    color: Colors.white,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Empty ─────────────────────────────────────────────────────────────────
  Widget _buildEmpty(bool isDark) {
    final isAdmin = SessionStore.role?.toLowerCase() == 'admin';

    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 36),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 72,
              height: 72,
              decoration: BoxDecoration(
                color: isDark
                    ? const Color(0xFF1E2B45).withOpacity(0.5)
                    : const Color(0xFF9AA3B2).withOpacity(0.08),
                borderRadius: BorderRadius.circular(24),
              ),
              child: Icon(
                Icons.forum_rounded,
                size: 34,
                color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF9AA3B2),
              ),
            ),
            const SizedBox(height: 16),
            Text(
              'No conversations yet',
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w700,
                fontSize: 16,
                color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF3D4A63),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              isAdmin
                  ? 'When requestors send messages or comments, their conversation threads will appear here for you to reply.'
                  : 'When admin replies to your requests, conversations will appear here.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w400,
                fontSize: 13,
                color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF9AA3B2),
                height: 1.55,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Thread Card ───────────────────────────────────────────────────────────────
class _ThreadCard extends StatelessWidget {
  final _ThreadItem item;
  final bool isDark;
  final VoidCallback onTap;
  const _ThreadCard({
    required this.item,
    required this.isDark,
    required this.onTap,
  });

  String _formatTime(String raw) {
    if (raw.isEmpty) return '';
    final dt = DateTime.tryParse(raw);
    if (dt == null) return raw;
    final now = DateTime.now();
    final diff = now.difference(dt);
    if (diff.inMinutes < 1) return 'Just now';
    if (diff.inMinutes < 60) return '${diff.inMinutes}m ago';
    if (diff.inHours < 24) return '${diff.inHours}h ago';
    if (diff.inDays < 7) return '${diff.inDays}d ago';
    return '${dt.month}/${dt.day}/${dt.year}';
  }

  Color _statusColor(String status) {
    switch (status.toLowerCase()) {
      case 'approved':
        return const Color(0xFF05C46B);
      case 'posted':
        return const Color(0xFF8B5CF6);
      case 'rejected':
        return const Color(0xFFFF3B30);
      default:
        return const Color(0xFFF59E0B);
    }
  }

  @override
  Widget build(BuildContext context) {
    final hasUnread = item.unreadCount > 0;
    final isAdmin = SessionStore.role?.toLowerCase() == 'admin';

    final cardBg = isDark
        ? (hasUnread ? const Color(0xFF1E2D4A) : const Color(0xFF131D31))
        : (hasUnread ? const Color(0xFFF0F5FF) : Colors.white);

    final borderColor = isDark
        ? (hasUnread ? const Color(0xFF3B82F6) : const Color(0xFF1E2B45))
        : (hasUnread ? const Color(0xFF2B5CE6).withOpacity(0.2) : const Color(0x0E000000));

    final titleColor = isDark ? const Color(0xFFF1F5F9) : const Color(0xFF080F1E);
    final snippetColor = isDark
        ? const Color(0xFF94A3B8)
        : (hasUnread ? const Color(0xFF3D4A63) : const Color(0xFF9AA3B2));

    return Hero(
      tag: 'thread-${item.requestId}',
      flightShuttleBuilder: (_, __, ___, ____, _____) => Material(
        color: Colors.transparent,
        child: Container(
          decoration: BoxDecoration(
            color: cardBg,
            borderRadius: BorderRadius.circular(20),
          ),
        ),
      ),
      child: Material(
        color: Colors.transparent,
        child: GestureDetector(
          onTap: onTap,
          child: Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: cardBg,
              borderRadius: BorderRadius.circular(20),
              border: Border.all(
                color: borderColor,
                width: hasUnread ? 1.5 : 1,
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
              children: [
                // Avatar with initials
                Container(
                  width: 46,
                  height: 46,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [Color(0xFF001540), Color(0xFF1A4FCC)],
                    ),
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: const [
                      BoxShadow(
                        color: Color(0x28001540),
                        blurRadius: 8,
                        offset: Offset(0, 3),
                      ),
                    ],
                  ),
                  alignment: Alignment.center,
                  child: const Icon(
                    Icons.chat_bubble_rounded,
                    color: Colors.white,
                    size: 20,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              item.requestTitle,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontWeight: hasUnread
                                    ? FontWeight.w800
                                    : FontWeight.w700,
                                fontSize: 14.5,
                                color: titleColor,
                                letterSpacing: -0.1,
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Text(
                            _formatTime(item.lastMessageAt),
                            style: TextStyle(
                              fontFamily: 'DM Sans',
                              fontSize: 10.5,
                              color: isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2),
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 5),
                      // Meta row: Requester name + Request Code + Status
                      Row(
                        children: [
                          if (isAdmin && item.requester.isNotEmpty) ...[
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              margin: const EdgeInsets.only(right: 6),
                              decoration: BoxDecoration(
                                color: isDark
                                    ? const Color(0xFF1E293B)
                                    : const Color(0xFFEEF2FF),
                                borderRadius: BorderRadius.circular(5),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    Icons.person_rounded,
                                    size: 10,
                                    color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF3B82F6),
                                  ),
                                  const SizedBox(width: 3),
                                  Text(
                                    item.requester,
                                    style: TextStyle(
                                      fontFamily: 'DM Sans',
                                      fontWeight: FontWeight.w700,
                                      fontSize: 10,
                                      color: isDark ? const Color(0xFF93C5FD) : const Color(0xFF1D4ED8),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                          if (item.requestCode.isNotEmpty) ...[
                            Text(
                              item.requestCode,
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontWeight: FontWeight.w600,
                                fontSize: 11,
                                color: isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2),
                              ),
                            ),
                            const SizedBox(width: 6),
                            Container(
                              width: 3,
                              height: 3,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: isDark ? const Color(0xFF475569) : const Color(0xFFCBD5E1),
                              ),
                            ),
                            const SizedBox(width: 6),
                          ],
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                            decoration: BoxDecoration(
                              color: _statusColor(item.requestStatus).withOpacity(0.12),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text(
                              item.requestStatus,
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontWeight: FontWeight.w800,
                                fontSize: 9.5,
                                color: _statusColor(item.requestStatus),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text(
                        item.lastMessage,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: hasUnread ? FontWeight.w600 : FontWeight.w400,
                          fontSize: 12.5,
                          color: snippetColor,
                          height: 1.4,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 10),
                Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    if (hasUnread)
                      Container(
                        constraints: const BoxConstraints(
                          minWidth: 22,
                          minHeight: 22,
                        ),
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: const Color(0xFF2B5CE6),
                          borderRadius: BorderRadius.circular(99),
                          boxShadow: const [
                            BoxShadow(
                              color: Color(0x402B5CE6),
                              blurRadius: 8,
                              offset: Offset(0, 2),
                            ),
                          ],
                        ),
                        child: Text(
                          item.unreadCount > 99 ? '99+' : '${item.unreadCount}',
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            fontFamily: 'DM Sans',
                            fontWeight: FontWeight.w900,
                            fontSize: 10,
                            color: Colors.white,
                          ),
                        ),
                      )
                    else
                      const SizedBox(height: 22),
                    const SizedBox(height: 10),
                    Container(
                      width: 28,
                      height: 28,
                      decoration: BoxDecoration(
                        color: isDark
                            ? const Color(0xFF1E2B45)
                            : const Color(0xFF002366).withOpacity(0.06),
                        borderRadius: BorderRadius.circular(9),
                      ),
                      child: Icon(
                        Icons.arrow_forward_ios_rounded,
                        size: 12,
                        color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF002366),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ── Stagger animation ─────────────────────────────────────────────────────────
class _StaggerItem extends StatelessWidget {
  final AnimationController? controller;
  final int index;
  final int total;
  final Widget child;

  const _StaggerItem({
    required this.controller,
    required this.index,
    required this.total,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    final ctrl = controller;
    if (ctrl == null) return child;

    final count = total.clamp(1, 20);
    final slot = index.clamp(0, count - 1);
    final start = (slot / (count + 4)).clamp(0.0, 0.85);
    final end = (start + 0.55).clamp(0.0, 1.0);
    final curve = CurvedAnimation(
      parent: ctrl,
      curve: Interval(start, end, curve: Curves.easeOutCubic),
    );
    return AnimatedBuilder(
      animation: curve,
      builder: (_, inner) => Opacity(
        opacity: curve.value,
        child: Transform.translate(
          offset: Offset(0, (1 - curve.value) * 16),
          child: inner,
        ),
      ),
      child: child,
    );
  }
}

// ── Data model ────────────────────────────────────────────────────────────────
class _ThreadItem {
  final int requestId;
  final String requestCode;
  final String requestTitle;
  final String requestStatus;
  final String requester;
  final String category;
  final String lastMessage;
  final String lastMessageAt;
  final int lastMessageId;
  final String lastSenderRole;
  final int unreadCount;

  const _ThreadItem({
    required this.requestId,
    required this.requestCode,
    required this.requestTitle,
    required this.requestStatus,
    required this.requester,
    required this.category,
    required this.lastMessage,
    required this.lastMessageAt,
    required this.lastMessageId,
    required this.lastSenderRole,
    required this.unreadCount,
  });

  _ThreadItem copyWith({int? unreadCount}) => _ThreadItem(
    requestId: requestId,
    requestCode: requestCode,
    requestTitle: requestTitle,
    requestStatus: requestStatus,
    requester: requester,
    category: category,
    lastMessage: lastMessage,
    lastMessageAt: lastMessageAt,
    lastMessageId: lastMessageId,
    lastSenderRole: lastSenderRole,
    unreadCount: unreadCount ?? this.unreadCount,
  );

  factory _ThreadItem.fromJson(Map<String, dynamic> json) => _ThreadItem(
    requestId: (json['request_id'] as num?)?.toInt() ?? 0,
    requestCode: (json['request_code'] ?? '').toString(),
    requestTitle: (json['request_title'] ?? '').toString(),
    requestStatus: (json['request_status'] ?? 'Pending').toString(),
    requester: (json['requester'] ?? '').toString(),
    category: (json['category'] ?? '').toString(),
    lastMessage: (json['last_message'] ?? '').toString(),
    lastMessageAt: (json['last_message_at'] ?? '').toString(),
    lastMessageId: (json['last_message_id'] as num?)?.toInt() ?? 0,
    lastSenderRole: (json['last_sender_role'] ?? '').toString(),
    unreadCount: 0, // Calculated locally
  );
}
