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

class _MessagesScreenState extends State<MessagesScreen> {
  bool _isLoading = true;
  String? _error;
  List<_ThreadItem> _threads = const [];

  @override
  void initState() {
    super.initState();
    _loadThreads();
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
    } catch (e) {
      setState(() {
        _error = e.toString().replaceFirst('Exception: ', '');
      });
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
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
              _buildHeader(context),
              Expanded(
                child: _isLoading
                    ? const Center(child: CircularProgressIndicator())
                    : _error != null
                    ? _buildError()
                    : _threads.isEmpty
                    ? _buildEmpty()
                    : RefreshIndicator(
                        onRefresh: _loadThreads,
                        child: ListView.separated(
                          padding: const EdgeInsets.fromLTRB(16, 12, 16, 100),
                          itemCount: _threads.length,
                          separatorBuilder: (_, __) =>
                              const SizedBox(height: 10),
                          itemBuilder: (_, i) => _buildThreadCard(_threads[i]),
                        ),
                      ),
              ),
              const SizedBox(height: 90),
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

  Widget _buildHeader(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: EdgeInsets.only(
        left: 16,
        right: 16,
        top: MediaQuery.of(context).padding.top + 14,
        bottom: 14,
      ),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(bottom: BorderSide(color: AppColors.border)),
      ),
      child: Row(
        children: [
          GestureDetector(
            onTap: () => Navigator.of(context).pop(),
            child: const Icon(Icons.arrow_back, color: AppColors.primary),
          ),
          const SizedBox(width: 12),
          const Expanded(
            child: Text(
              'Messages',
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w700,
                fontSize: 22,
                color: AppColors.primary,
              ),
            ),
          ),
          IconButton(
            onPressed: _loadThreads,
            icon: const Icon(Icons.refresh_rounded, color: AppColors.primary),
          ),
        ],
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              _error ?? 'Something went wrong.',
              textAlign: TextAlign.center,
              style: const TextStyle(color: AppColors.inkMid),
            ),
            const SizedBox(height: 12),
            OutlinedButton(onPressed: _loadThreads, child: const Text('Retry')),
          ],
        ),
      ),
    );
  }

  Widget _buildEmpty() {
    return const Center(
      child: Padding(
        padding: EdgeInsets.symmetric(horizontal: 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.forum_outlined, size: 48, color: AppColors.inkMute),
            SizedBox(height: 12),
            Text(
              'No request conversations yet',
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w500,
                fontSize: 14,
                color: AppColors.inkMid,
              ),
            ),
            SizedBox(height: 6),
            Text(
              'When admin replies to your requests, they will appear here.',
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

  Widget _buildThreadCard(_ThreadItem item) {
    return Hero(
      tag: 'thread-${item.requestId}',
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(AppRadius.lg),
          onTap: () async {
            await Navigator.of(context).push(
              MaterialPageRoute(
                builder: (_) => MessageThreadScreen(
                  requestId: item.requestId,
                  requestCode: item.requestCode,
                  requestTitle: item.requestTitle,
                  requestStatus: item.requestStatus,
                ),
              ),
            );
            _loadThreads();
          },
          child: Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(AppRadius.lg),
              border: Border.all(color: AppColors.border),
              boxShadow: const [
                BoxShadow(
                  color: Color(0x0F000000),
                  blurRadius: 10,
                  offset: Offset(0, 4),
                ),
              ],
            ),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item.requestTitle,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w700,
                          fontSize: 15,
                          color: AppColors.ink,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        item.requestCode.isEmpty
                            ? 'REQ-${item.requestId.toString().padLeft(5, '0')}'
                            : item.requestCode,
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w500,
                          fontSize: 11.5,
                          color: AppColors.inkMute,
                        ),
                      ),
                      const SizedBox(height: 10),
                      Text(
                        item.lastMessage,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w400,
                          fontSize: 13,
                          color: AppColors.inkMid,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        _formatTime(item.lastMessageAt),
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontSize: 11,
                          color: AppColors.inkMute,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                Column(
                  children: [
                    if (item.unreadCount > 0)
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: AppColors.accent,
                          borderRadius: BorderRadius.circular(999),
                        ),
                        child: Text(
                          item.unreadCount > 99
                              ? '99+'
                              : '${item.unreadCount}',
                          style: const TextStyle(
                            fontFamily: 'DM Sans',
                            fontWeight: FontWeight.w700,
                            fontSize: 11,
                            color: Colors.white,
                          ),
                        ),
                      )
                    else
                      const SizedBox(height: 20),
                    const SizedBox(height: 18),
                    const Icon(
                      Icons.chevron_right_rounded,
                      color: AppColors.inkMute,
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
}

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

  factory _ThreadItem.fromJson(Map<String, dynamic> json) {
    return _ThreadItem(
      requestId: (json['request_id'] as num?)?.toInt() ?? 0,
      requestCode: (json['request_code'] ?? '').toString(),
      requestTitle: (json['request_title'] ?? '').toString(),
      requestStatus: (json['request_status'] ?? 'Pending').toString(),
      lastMessage: (json['last_message'] ?? '').toString(),
      lastMessageAt: (json['last_message_at'] ?? '').toString(),
      unreadCount: (json['unread_count'] as num?)?.toInt() ?? 0,
    );
  }
}
