import 'dart:async';

import 'package:flutter/material.dart';

import '../services/api_service.dart';
import '../services/session_store.dart';
import '../services/chat_read_store.dart';
import '../theme/app_theme.dart';

class MessageThreadScreen extends StatefulWidget {
  final int requestId;
  final String requestCode;
  final String requestTitle;
  final String requestStatus;

  const MessageThreadScreen({
    super.key,
    required this.requestId,
    required this.requestCode,
    required this.requestTitle,
    required this.requestStatus,
  });

  @override
  State<MessageThreadScreen> createState() => _MessageThreadScreenState();
}

class _MessageThreadScreenState extends State<MessageThreadScreen> {
  final TextEditingController _input = TextEditingController();
  final ScrollController _scroll = ScrollController();

  bool _loading = true;
  bool _sending = false;
  String? _loadError;
  List<_ChatMessage> _messages = const [];
  Timer? _poll;

  @override
  void initState() {
    super.initState();
    _loadThread();
    _poll = Timer.periodic(const Duration(seconds: 4), (_) {
      _loadThread(showLoader: false);
    });
  }

  @override
  void dispose() {
    _poll?.cancel();
    _input.dispose();
    _scroll.dispose();
    super.dispose();
  }

  Future<void> _loadThread({bool showLoader = true}) async {
    final userId = SessionStore.userId;
    if (userId == null || userId == 0) {
      if (mounted) setState(() => _loading = false);
      return;
    }
    if (showLoader && mounted) setState(() => _loading = true);

    try {
      final result = await ApiService.fetchMessageThread(
        userId: userId,
        requestId: widget.requestId,
      );
      final rows =
          ((result['data'] as Map<String, dynamic>?)?['messages'] as List?) ??
          ((result['messages'] as List?) ?? const []);
      if (!mounted) return;

      final incoming = rows
          .whereType<Map<String, dynamic>>()
          .map(_ChatMessage.fromJson)
          .toList();

      final hadNew = incoming.length > _messages.length;
      setState(() {
        _messages = incoming;
        _loadError = null;
      });

      if (hadNew) _scrollToBottom();

      // Mark as read locally
      if (incoming.isNotEmpty) {
        await ChatReadStore.markAsRead(widget.requestId, incoming.last.id);
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _loadError = e.toString().replaceFirst('Exception: ', '');
      });
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _send() async {
    final text = _input.text.trim();
    if (text.isEmpty || _sending) return;

    final userId = SessionStore.userId;
    if (userId == null || userId == 0) return;

    setState(() => _sending = true);
    _input.clear();

    try {
      final res = await ApiService.sendMessage(
        userId: userId,
        requestId: widget.requestId,
        message: text,
      );
      if (!mounted) return;

      final isAdmin = SessionStore.role?.toLowerCase() == 'admin';
      final newMsg = _ChatMessage(
        id: (res['data']?['id'] as num?)?.toInt() ?? DateTime.now().millisecondsSinceEpoch,
        senderRole: isAdmin ? 'admin' : 'requestor',
        senderName: SessionStore.name ?? (isAdmin ? 'Admin' : 'Requester'),
        message: text,
        createdAt: DateTime.now().toIso8601String(),
      );

      setState(() {
        _messages = [..._messages, newMsg];
      });
      _scrollToBottom();
      await ChatReadStore.markAsRead(widget.requestId, newMsg.id);
    } catch (e) {
      if (!mounted) return;
      final msg = e.toString().replaceFirst('Exception: ', '');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(msg),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      );
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  void _scrollToBottom() {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!_scroll.hasClients) return;
      _scroll.animateTo(
        _scroll.position.maxScrollExtent + 120,
        duration: const Duration(milliseconds: 300),
        curve: Curves.easeOut,
      );
    });
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
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final pageBg = isDark ? const Color(0xFF090D16) : AppColors.pageBg;

    return Scaffold(
      backgroundColor: pageBg,
      body: SafeArea(
        child: Column(
          children: [
            // ── Header ───────────────────────────────────────────────────
            _buildHeader(isDark),

            // ── Message list ─────────────────────────────────────────────
            Expanded(child: _buildBody(isDark)),

            // ── Composer ─────────────────────────────────────────────────
            _buildComposer(isDark),
          ],
        ),
      ),
    );
  }

  // ── Header ────────────────────────────────────────────────────────────────
  Widget _buildHeader(bool isDark) {
    final isAdmin = SessionStore.role?.toLowerCase() == 'admin';

    final headerBg = isDark ? const Color(0xFF131D31) : Colors.white;
    final borderColor = isDark ? const Color(0xFF1E2B45) : const Color(0x0F000000);
    final pillBg = isDark ? const Color(0xFF0D1527) : const Color(0xFFF1F4FB);
    final pillBorder = isDark ? const Color(0xFF1E2B45) : const Color(0x0E000000);

    return Hero(
      tag: 'thread-${widget.requestId}',
      flightShuttleBuilder: (_, __, ___, ____, _____) => Material(
        color: Colors.transparent,
        child: Container(
          decoration: BoxDecoration(
            color: headerBg,
            borderRadius: BorderRadius.circular(16),
          ),
        ),
      ),
      child: Material(
        color: headerBg,
        child: Container(
          decoration: BoxDecoration(
            color: headerBg,
            border: Border(
              bottom: BorderSide(color: borderColor, width: 1),
            ),
            boxShadow: [
              BoxShadow(
                color: isDark ? const Color(0x40000000) : const Color(0x07001540),
                blurRadius: 12,
                offset: const Offset(0, 1),
              ),
            ],
          ),
          padding: const EdgeInsets.fromLTRB(8, 10, 16, 12),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  IconButton(
                    onPressed: () => Navigator.of(context).pop(),
                    icon: Icon(
                      Icons.arrow_back_ios_new_rounded,
                      color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF002366),
                      size: 18,
                    ),
                  ),
                  Expanded(
                    child: Text(
                      isAdmin ? 'Conversation with Requestor' : 'Comments with Admin',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w900,
                        fontSize: 18,
                        color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF080F1E),
                        letterSpacing: -0.3,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              // Request info pill
              Container(
                margin: const EdgeInsets.symmetric(horizontal: 8),
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 10,
                ),
                decoration: BoxDecoration(
                  color: pillBg,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: pillBorder),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            widget.requestTitle,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontFamily: 'DM Sans',
                              fontWeight: FontWeight.w700,
                              fontSize: 13.5,
                              color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF080F1E),
                              letterSpacing: -0.1,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            widget.requestCode.isEmpty
                                ? 'REQ-${widget.requestId.toString().padLeft(5, '0')}'
                                : widget.requestCode,
                            style: TextStyle(
                              fontFamily: 'DM Sans',
                              fontSize: 11,
                              color: isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2),
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                    ),
                    // Status chip
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 5,
                      ),
                      decoration: BoxDecoration(
                        color: _statusColor(
                          widget.requestStatus,
                        ).withOpacity(0.12),
                        border: Border.all(
                          color: _statusColor(
                            widget.requestStatus,
                          ).withOpacity(0.3),
                        ),
                        borderRadius: BorderRadius.circular(99),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Container(
                            width: 6,
                            height: 6,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: _statusColor(widget.requestStatus),
                            ),
                          ),
                          const SizedBox(width: 5),
                          Text(
                            widget.requestStatus,
                            style: TextStyle(
                              fontFamily: 'DM Sans',
                              fontWeight: FontWeight.w700,
                              fontSize: 11,
                              color: _statusColor(widget.requestStatus),
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
        ),
      ),
    );
  }

  // ── Body ──────────────────────────────────────────────────────────────────
  Widget _buildBody(bool isDark) {
    if (_loading) {
      return const Center(
        child: CircularProgressIndicator(color: AppColors.accent),
      );
    }
    if (_loadError != null && _messages.isEmpty) {
      return _buildLoadError(isDark);
    }
    if (_messages.isEmpty) {
      return _buildEmpty(isDark);
    }

    return ListView.builder(
      controller: _scroll,
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 16),
      itemCount: _messages.length,
      itemBuilder: (_, i) => _buildBubble(_messages[i], isDark),
    );
  }

  Widget _buildEmpty(bool isDark) {
    final isAdmin = SessionStore.role?.toLowerCase() == 'admin';

    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(
              color: isDark
                  ? const Color(0xFF1E2B45).withOpacity(0.5)
                  : const Color(0xFF9AA3B2).withOpacity(0.08),
              borderRadius: BorderRadius.circular(22),
            ),
            child: Icon(
              Icons.forum_rounded,
              size: 30,
              color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF9AA3B2),
            ),
          ),
          const SizedBox(height: 14),
          Text(
            'No messages yet',
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontWeight: FontWeight.w700,
              fontSize: 15,
              color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF3D4A63),
            ),
          ),
          const SizedBox(height: 6),
          Text(
            isAdmin
                ? 'Send a message or note to the requestor below.'
                : 'Ask admin a question or share instructions below!',
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontSize: 13,
              color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF9AA3B2),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLoadError(bool isDark) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 28),
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
                size: 30,
                color: Color(0xFFFF3B30),
              ),
            ),
            const SizedBox(height: 14),
            Text(
              _loadError ?? 'Failed to load messages.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 13,
                color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF3D4A63),
                height: 1.5,
              ),
            ),
            const SizedBox(height: 14),
            GestureDetector(
              onTap: () => _loadThread(),
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 11,
                ),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF001540), Color(0xFF0032A0)],
                  ),
                  borderRadius: BorderRadius.circular(12),
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

  // ── Chat Bubble ───────────────────────────────────────────────────────────
  Widget _buildBubble(_ChatMessage m, bool isDark) {
    final isAdmin = SessionStore.role?.toLowerCase() == 'admin';
    final isMine = isAdmin ? (m.senderRole == 'admin') : (m.senderRole != 'admin');

    return Align(
      alignment: isMine ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.76,
        ),
        margin: const EdgeInsets.only(bottom: 10),
        child: isMine ? _outBubble(m) : _inBubble(m, isDark),
      ),
    );
  }

  Widget _outBubble(_ChatMessage m) {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 10),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF001540), Color(0xFF1A4FCC)],
        ),
        borderRadius: BorderRadius.all(Radius.circular(20)),
        boxShadow: [
          BoxShadow(
            color: Color(0x30001540),
            blurRadius: 10,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: _bubbleContent(m, Colors.white, Colors.white70),
    );
  }

  Widget _inBubble(_ChatMessage m, bool isDark) {
    final bubbleBg = isDark ? const Color(0xFF1E2B45) : Colors.white;
    final borderColor = isDark ? const Color(0xFF2B3A5A) : const Color(0x0E000000);
    final textColor = isDark ? const Color(0xFFF1F5F9) : const Color(0xFF080F1E);
    final timeColor = isDark ? const Color(0xFF94A3B8) : const Color(0xFF9AA3B2);

    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 10),
      decoration: BoxDecoration(
        color: bubbleBg,
        borderRadius: const BorderRadius.all(Radius.circular(20)),
        border: Border.all(color: borderColor),
        boxShadow: [
          BoxShadow(
            color: isDark ? const Color(0x20000000) : const Color(0x08001540),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: _bubbleContent(m, textColor, timeColor),
    );
  }

  Widget _bubbleContent(_ChatMessage m, Color textColor, Color timeColor) {
    final roleTag = m.senderRole == 'admin' ? 'ADMIN' : 'REQUESTOR';

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              m.senderName.isNotEmpty ? m.senderName : roleTag,
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 11,
                fontWeight: FontWeight.w700,
                color: timeColor,
              ),
            ),
            const SizedBox(width: 6),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
              decoration: BoxDecoration(
                color: (m.senderRole == 'admin' ? Colors.amber : Colors.blue)
                    .withOpacity(0.2),
                borderRadius: BorderRadius.circular(4),
              ),
              child: Text(
                roleTag,
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontSize: 9,
                  fontWeight: FontWeight.w800,
                  color: m.senderRole == 'admin' ? const Color(0xFFF59E0B) : const Color(0xFF60A5FA),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 4),
        Text(
          m.message,
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontSize: 14,
            color: textColor,
            fontWeight: FontWeight.w400,
            height: 1.45,
          ),
        ),
        const SizedBox(height: 5),
        Text(
          _formatTime(m.createdAt),
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontSize: 10,
            color: timeColor,
            fontWeight: FontWeight.w500,
          ),
        ),
      ],
    );
  }

  String _formatTime(String raw) {
    if (raw.isEmpty) return '';
    final dt = DateTime.tryParse(raw);
    if (dt == null) return raw;
    final hour = dt.hour % 12 == 0 ? 12 : dt.hour % 12;
    final min = dt.minute.toString().padLeft(2, '0');
    final ampm = dt.hour >= 12 ? 'PM' : 'AM';
    return '$hour:$min $ampm';
  }

  // ── Composer ──────────────────────────────────────────────────────────────
  Widget _buildComposer(bool isDark) {
    final isAdmin = SessionStore.role?.toLowerCase() == 'admin';
    final composerBg = isDark ? const Color(0xFF131D31) : Colors.white;
    final borderColor = isDark ? const Color(0xFF1E2B45) : const Color(0x0E000000);
    final fieldBg = isDark ? const Color(0xFF0D1527) : const Color(0xFFF1F4FB);
    final fieldBorder = isDark ? const Color(0xFF1E2B45) : const Color(0x0A000000);
    final textColor = isDark ? const Color(0xFFF1F5F9) : const Color(0xFF080F1E);
    final hintColor = isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2);

    return Container(
      padding: const EdgeInsets.fromLTRB(12, 10, 12, 12),
      decoration: BoxDecoration(
        color: composerBg,
        border: Border(top: BorderSide(color: borderColor, width: 1)),
        boxShadow: [
          BoxShadow(
            color: isDark ? const Color(0x40000000) : const Color(0x08001540),
            blurRadius: 12,
            offset: const Offset(0, -3),
          ),
        ],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          Expanded(
            child: Container(
              constraints: const BoxConstraints(maxHeight: 120),
              decoration: BoxDecoration(
                color: fieldBg,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: fieldBorder),
              ),
              child: TextField(
                controller: _input,
                minLines: 1,
                maxLines: 5,
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontSize: 14,
                  color: textColor,
                ),
                decoration: InputDecoration(
                  hintText: isAdmin ? 'Reply to requestor…' : 'Message admin…',
                  hintStyle: TextStyle(
                    fontFamily: 'DM Sans',
                    color: hintColor,
                    fontSize: 14,
                  ),
                  filled: false,
                  border: InputBorder.none,
                  enabledBorder: InputBorder.none,
                  focusedBorder: InputBorder.none,
                  contentPadding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 11,
                  ),
                  isDense: true,
                ),
              ),
            ),
          ),
          const SizedBox(width: 8),
          // Send button
          GestureDetector(
            onTap: _sending ? null : _send,
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              width: 46,
              height: 46,
              decoration: BoxDecoration(
                gradient: _sending
                    ? null
                    : const LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: [Color(0xFF001540), Color(0xFF1A4FCC)],
                      ),
                color: _sending ? (isDark ? const Color(0xFF1E2B45) : const Color(0xFFE9EDF6)) : null,
                borderRadius: BorderRadius.circular(16),
                boxShadow: _sending
                    ? null
                    : const [
                        BoxShadow(
                          color: Color(0x35001540),
                          blurRadius: 10,
                          offset: Offset(0, 4),
                        ),
                      ],
              ),
              child: Center(
                child: _sending
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation(Color(0xFF9AA3B2)),
                        ),
                      )
                    : const Icon(
                        Icons.arrow_upward_rounded,
                        color: Colors.white,
                        size: 20,
                      ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ChatMessage {
  final int id;
  final String senderRole;
  final String senderName;
  final String message;
  final String createdAt;

  const _ChatMessage({
    required this.id,
    required this.senderRole,
    required this.senderName,
    required this.message,
    required this.createdAt,
  });

  factory _ChatMessage.fromJson(Map<String, dynamic> json) => _ChatMessage(
    id: (json['id'] as num?)?.toInt() ?? 0,
    senderRole: (json['sender_role'] ?? '').toString(),
    senderName: (json['sender_name'] ?? '').toString(),
    message: (json['message'] ?? '').toString(),
    createdAt: (json['created_at'] ?? '').toString(),
  );
}
