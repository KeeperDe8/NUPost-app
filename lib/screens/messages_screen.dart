import 'package:flutter/material.dart';

import '../app_bottom_nav.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';
import '../theme/app_theme.dart';
import 'message_thread_screen.dart';

class MessagesScreen extends StatefulWidget {
  const MessagesScreen({super.key});

  @override
  State<MessagesScreen> createState() => _MessagesScreenState();
}

class _MessagesScreenState extends State<MessagesScreen>
    with SingleTickerProviderStateMixin {
  bool _isLoading = true;
  String? _error;
  List<_ThreadItem> _threads = const [];

  late final AnimationController _staggerCtrl;

  @override
  void initState() {
    super.initState();
    _staggerCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 800),
    );
    _loadThreads();
  }

  @override
  void dispose() {
    _staggerCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadThreads() async {
    final userId = SessionStore.userId;
    if (userId == null || userId == 0) {
      setState(() {
        _isLoading = false;
        _error = 'Please login first.';
      });
      return;
    }
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final result = await ApiService.fetchMessageThreads(userId: userId);
      final rows = (result['data'] as List?) ?? const [];
      setState(() {
        _threads = rows
            .whereType<Map<String, dynamic>>()
            .map(_ThreadItem.fromJson)
            .toList();
      });
      _staggerCtrl.reset();
      _staggerCtrl.forward();
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
                    : _error != null
                    ? _buildError()
                    : _threads.isEmpty
                    ? _buildEmpty()
                    : RefreshIndicator(
                        onRefresh: _loadThreads,
                        color: AppColors.primary,
                        child: ListView.separated(
                          padding: const EdgeInsets.fromLTRB(16, 14, 16, 100),
                          itemCount: _threads.length,
                          separatorBuilder: (_, __) =>
                              const SizedBox(height: 10),
                          itemBuilder: (_, i) => _StaggerItem(
                            controller: _staggerCtrl,
                            index: i,
                            total: _threads.length,
                            child: _ThreadCard(
                              item: _threads[i],
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
                                _loadThreads();
                              },
                            ),
                          ),
                        ),
                      ),
              ),
            ],
          ),
          const Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: AppBottomNav(currentIndex: -1),
          ),
        ],
      ),
    );
  }

  // ── Header ────────────────────────────────────────────────────────────────
  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(bottom: BorderSide(color: Color(0x0F000000), width: 1)),
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
        MediaQuery.of(context).padding.top + 16,
        16,
        16,
      ),
      child: Row(
        children: [
          // Back button
          GestureDetector(
            onTap: () => Navigator.of(context).pop(),
            child: Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: const Color(0xFF002366).withOpacity(0.07),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(
                Icons.arrow_back_ios_new_rounded,
                size: 16,
                color: Color(0xFF002366),
              ),
            ),
          ),
          const SizedBox(width: 14),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Messages',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w900,
                    fontSize: 22,
                    color: Color(0xFF002366),
                    letterSpacing: -0.4,
                  ),
                ),
                Text(
                  'Request conversations with admin',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w400,
                    fontSize: 12,
                    color: Color(0xFF9AA3B2),
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
                color: const Color(0xFF002366).withOpacity(0.07),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(
                Icons.refresh_rounded,
                size: 18,
                color: Color(0xFF002366),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Error ─────────────────────────────────────────────────────────────────
  Widget _buildError() {
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
              style: const TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 14,
                color: Color(0xFF3D4A63),
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
  Widget _buildEmpty() {
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
                color: const Color(0xFF9AA3B2).withOpacity(0.08),
                borderRadius: BorderRadius.circular(24),
              ),
              child: const Icon(
                Icons.forum_rounded,
                size: 34,
                color: Color(0xFF9AA3B2),
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'No conversations yet',
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w700,
                fontSize: 16,
                color: Color(0xFF3D4A63),
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'When admin replies to your requests, conversations will appear here.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w400,
                fontSize: 13,
                color: Color(0xFF9AA3B2),
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
  final VoidCallback onTap;
  const _ThreadCard({required this.item, required this.onTap});

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

    return Hero(
      tag: 'thread-${item.requestId}',
      flightShuttleBuilder: (_, __, ___, ____, _____) => Material(
        color: Colors.transparent,
        child: Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
          ),
        ),
      ),
      child: Material(
        color: Colors.transparent,
        child: GestureDetector(
          onTap: onTap,
          child: _threadCard(item, hasUnread),
        ),
      ),
    );
  }

  Widget _threadCard(_ThreadItem item, bool hasUnread) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: hasUnread ? const Color(0xFFF0F5FF) : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: hasUnread
              ? const Color(0xFF2B5CE6).withOpacity(0.2)
              : const Color(0x0E000000),
          width: hasUnread ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF001540).withOpacity(hasUnread ? 0.07 : 0.04),
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
                          color: const Color(0xFF080F1E),
                          letterSpacing: -0.1,
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      _formatTime(item.lastMessageAt),
                      style: const TextStyle(
                        fontFamily: 'DM Sans',
                        fontSize: 10.5,
                        color: Color(0xFF9AA3B2),
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    // Request code
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 7,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xFFE9EDF6),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        item.requestCode.isEmpty
                            ? 'REQ-${item.requestId.toString().padLeft(5, '0')}'
                            : item.requestCode,
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontSize: 9.5,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF3D4A63),
                          letterSpacing: 0.3,
                        ),
                      ),
                    ),
                    const SizedBox(width: 6),
                    // Status dot
                    Container(
                      width: 6,
                      height: 6,
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        color: _statusColor(item.requestStatus),
                      ),
                    ),
                    const SizedBox(width: 4),
                    Text(
                      item.requestStatus,
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontSize: 10.5,
                        fontWeight: FontWeight.w600,
                        color: _statusColor(item.requestStatus),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 7),
                Text(
                  item.lastMessage,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: hasUnread ? FontWeight.w600 : FontWeight.w400,
                    fontSize: 12.5,
                    color: hasUnread
                        ? const Color(0xFF3D4A63)
                        : const Color(0xFF9AA3B2),
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
                  color: const Color(0xFF002366).withOpacity(0.06),
                  borderRadius: BorderRadius.circular(9),
                ),
                child: const Icon(
                  Icons.arrow_forward_ios_rounded,
                  size: 12,
                  color: Color(0xFF002366),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

// ── Stagger animation ─────────────────────────────────────────────────────────
class _StaggerItem extends StatelessWidget {
  final AnimationController controller;
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
    final count = total.clamp(1, 20);
    final slot = index.clamp(0, count - 1);
    final start = (slot / (count + 4)).clamp(0.0, 0.85);
    final end = (start + 0.55).clamp(0.0, 1.0);
    final curve = CurvedAnimation(
      parent: controller,
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
  final String lastMessage;
  final String lastMessageAt;
  final int unreadCount;

  const _ThreadItem({
    required this.requestId,
    required this.requestCode,
    required this.requestTitle,
    required this.requestStatus,
    required this.lastMessage,
    required this.lastMessageAt,
    required this.unreadCount,
  });

  factory _ThreadItem.fromJson(Map<String, dynamic> json) => _ThreadItem(
    requestId: (json['request_id'] as num?)?.toInt() ?? 0,
    requestCode: (json['request_code'] ?? '').toString(),
    requestTitle: (json['request_title'] ?? '').toString(),
    requestStatus: (json['request_status'] ?? 'Pending').toString(),
    lastMessage: (json['last_message'] ?? '').toString(),
    lastMessageAt: (json['last_message_at'] ?? '').toString(),
    unreadCount: (json['unread_count'] as num?)?.toInt() ?? 0,
  );
}
