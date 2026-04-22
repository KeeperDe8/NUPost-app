import 'dart:async';

import 'package:flutter/material.dart';

import '../services/api_service.dart';
import '../services/session_store.dart';
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
  final GlobalKey<AnimatedListState> _listKey = GlobalKey<AnimatedListState>();

  bool _loading = true;
  bool _sending = false;
  String? _loadError;
  List<_ChatMessage> _messages = const [];
  Timer? _poll;

  @override
  void initState() {
    super.initState();
    _loadThread();
    _poll = Timer.periodic(const Duration(seconds: 8), (_) {
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
          const [];
      if (!mounted) return;

      final incoming = rows
          .whereType<Map<String, dynamic>>()
          .map(_ChatMessage.fromJson)
          .toList();

      final prevLen = _messages.length;
      _messages = incoming;

      if (prevLen == 0 && incoming.isNotEmpty) {
        setState(() => _loadError = null);
        _scrollToBottom();
      } else if (incoming.length > prevLen) {
        setState(() => _loadError = null);
        for (var i = prevLen; i < incoming.length; i++) {
          _listKey.currentState?.insertItem(
            i,
            duration: const Duration(milliseconds: 300),
          );
        }
        _scrollToBottom();
      } else {
        setState(() => _loadError = null);
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
    final userId = SessionStore.userId;
    if (_sending || text.isEmpty || userId == null || userId == 0) return;

    setState(() => _sending = true);
    try {
      await ApiService.sendMessageToThread(
        userId: userId,
        requestId: widget.requestId,
        message: text,
      );
      _input.clear();
      await _loadThread(showLoader: false);
    } catch (e) {
      if (!mounted) return;
      var msg = e.toString().replaceFirst('Exception: ', '');
      if (msg.contains('HTTP 404') && msg.contains('message_thread.php')) {
        msg = 'Message endpoint not found. Check API server.';
      }
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
    return Scaffold(
      backgroundColor: AppColors.pageBg,
      body: SafeArea(
        child: Column(
          children: [
            // ── Header ───────────────────────────────────────────────────
            _buildHeader(),

            // ── Message list ─────────────────────────────────────────────
            Expanded(child: _buildBody()),

            // ── Composer ─────────────────────────────────────────────────
            _buildComposer(),
          ],
        ),
      ),
    );
  }

  // ── Header ────────────────────────────────────────────────────────────────
  Widget _buildHeader() {
    return Hero(
      tag: 'thread-${widget.requestId}',
      child: Material(
        color: Colors.white,
        child: Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            border: Border(
              bottom: BorderSide(color: Color(0x0F000000), width: 1),
            ),
            boxShadow: [
              BoxShadow(
                color: Color(0x07001540),
                blurRadius: 12,
                offset: Offset(0, 1),
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
                    icon: const Icon(
                      Icons.arrow_back_ios_new_rounded,
                      color: Color(0xFF002366),
                      size: 18,
                    ),
                  ),
                  const Expanded(
                    child: Text(
                      'Comments with Admin',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w900,
                        fontSize: 18,
                        color: Color(0xFF080F1E),
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
                  color: const Color(0xFFF1F4FB),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0x0E000000)),
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
                            style: const TextStyle(
                              fontFamily: 'DM Sans',
                              fontWeight: FontWeight.w700,
                              fontSize: 13.5,
                              color: Color(0xFF080F1E),
                              letterSpacing: -0.1,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            widget.requestCode.isEmpty
                                ? 'REQ-${widget.requestId.toString().padLeft(5, '0')}'
                                : widget.requestCode,
                            style: const TextStyle(
                              fontFamily: 'DM Sans',
                              fontSize: 11,
                              color: Color(0xFF9AA3B2),
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
                        ).withOpacity(0.1),
                        border: Border.all(
                          color: _statusColor(
                            widget.requestStatus,
                          ).withOpacity(0.25),
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
  Widget _buildBody() {
    if (_loading) {
      return const Center(
        child: CircularProgressIndicator(color: AppColors.primary),
      );
    }
    if (_loadError != null && _messages.isEmpty) {
      return _buildLoadError();
    }
    if (_messages.isEmpty) {
      return _buildEmpty();
    }

    return AnimatedList(
      key: _listKey,
      controller: _scroll,
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 16),
      initialItemCount: _messages.length,
      itemBuilder: (_, i, anim) {
        return SlideTransition(
          position: Tween<Offset>(
            begin: const Offset(0, 0.2),
            end: Offset.zero,
          ).animate(CurvedAnimation(parent: anim, curve: Curves.easeOutCubic)),
          child: FadeTransition(
            opacity: anim,
            child: _buildBubble(_messages[i]),
          ),
        );
      },
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(
              color: const Color(0xFF9AA3B2).withOpacity(0.08),
              borderRadius: BorderRadius.circular(22),
            ),
            child: const Icon(
              Icons.forum_rounded,
              size: 30,
              color: Color(0xFF9AA3B2),
            ),
          ),
          const SizedBox(height: 14),
          const Text(
            'No messages yet',
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontWeight: FontWeight.w700,
              fontSize: 15,
              color: Color(0xFF3D4A63),
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            'Ask admin a question below!',
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontSize: 13,
              color: Color(0xFF9AA3B2),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLoadError() {
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
              style: const TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 13,
                color: Color(0xFF3D4A63),
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
  Widget _buildBubble(_ChatMessage m) {
    final isMine = m.senderRole == 'requestor';

    return Align(
      alignment: isMine ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.74,
        ),
        margin: const EdgeInsets.only(bottom: 10),
        child: isMine ? _outBubble(m) : _inBubble(m),
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
        borderRadius: BorderRadius.all(Radius.circular(22)),
        boxShadow: [
          BoxShadow(
            color: Color(0x30001540),
            blurRadius: 10,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: _bubbleContent(m, Colors.white, Colors.white60),
    );
  }

  Widget _inBubble(_ChatMessage m) {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: const BorderRadius.all(Radius.circular(22)),
        border: Border.all(color: const Color(0x0E000000)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x08001540),
            blurRadius: 8,
            offset: Offset(0, 3),
          ),
        ],
      ),
      child: _bubbleContent(
        m,
        const Color(0xFF080F1E),
        const Color(0xFF9AA3B2),
      ),
    );
  }

  Widget _bubbleContent(_ChatMessage m, Color textColor, Color timeColor) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
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

  // ── Composer ──────────────────────────────────────────────────────────────
  Widget _buildComposer() {
    return Container(
      padding: const EdgeInsets.fromLTRB(12, 10, 12, 12),
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: Color(0x0E000000), width: 1)),
        boxShadow: [
          BoxShadow(
            color: Color(0x08001540),
            blurRadius: 12,
            offset: Offset(0, -3),
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
                color: const Color(0xFFF1F4FB),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: const Color(0x0A000000)),
              ),
              child: TextField(
                controller: _input,
                minLines: 1,
                maxLines: 5,
                style: const TextStyle(
                  fontFamily: 'DM Sans',
                  fontSize: 14,
                  color: Color(0xFF080F1E),
                ),
                decoration: const InputDecoration(
                  hintText: 'Message admin…',
                  hintStyle: TextStyle(
                    fontFamily: 'DM Sans',
                    color: Color(0xFF9AA3B2),
                    fontSize: 14,
                  ),
                  filled: false,
                  border: InputBorder.none,
                  enabledBorder: InputBorder.none,
                  focusedBorder: InputBorder.none,
                  contentPadding: EdgeInsets.symmetric(
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
                color: _sending ? const Color(0xFFE9EDF6) : null,
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

  String _formatTime(String raw) {
    final dt = DateTime.tryParse(raw);
    if (dt == null) return '';
    final hour = dt.hour > 12 ? dt.hour - 12 : (dt.hour == 0 ? 12 : dt.hour);
    final ampm = dt.hour >= 12 ? 'PM' : 'AM';
    final min = dt.minute.toString().padLeft(2, '0');
    return '$hour:$min $ampm';
  }
}

// ── Data model ────────────────────────────────────────────────────────────────
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
