import 'dart:async';
import 'dart:ui';

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

    if (showLoader && mounted) {
      setState(() => _loading = true);
    }

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
        // Initial load — populate list without animation
        setState(() {
          _loadError = null;
        });
        _scrollToBottom();
      } else if (incoming.length > prevLen) {
        // New messages appended — animate each in
        setState(() {
          _loadError = null;
        });
        for (var i = prevLen; i < incoming.length; i++) {
          _listKey.currentState?.insertItem(
            i,
            duration: const Duration(milliseconds: 350),
          );
        }
        _scrollToBottom();
      } else {
        setState(() {
          _loadError = null;
        });
      }
    } catch (e) {
      if (!mounted) return;
      final msg = e.toString().replaceFirst('Exception: ', '');
      setState(() {
        _loadError = msg;
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
        msg =
            'Message endpoint not found. Run Laravel API and use API_BASE_URL=http://10.0.2.2:8000/api';
      }
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
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

  @override
  Widget build(BuildContext context) {
    return Theme(
      data: AppTheme.chatLight,
      child: Builder(
        builder: (ctx) {
          final g =
              Theme.of(ctx).extension<GlassColors>() ?? GlassColors.defaults;
          return Scaffold(
            backgroundColor: g.bg,
            body: SafeArea(
              child: Stack(
                children: [
                  // ── Message list scrolls behind header + pill ──────────
                  Positioned.fill(
                    child: _loading
                        ? const Center(
                            child: CircularProgressIndicator(
                              color: ChatColors.outBubble2,
                            ),
                          )
                        : _loadError != null && _messages.isEmpty
                        ? _buildLoadError(g)
                        : _messages.isEmpty
                        ? _buildEmpty(g)
                        : AnimatedList(
                            key: _listKey,
                            controller: _scroll,
                            padding: const EdgeInsets.fromLTRB(
                              16,
                              104,
                              16,
                              120,
                            ),
                            initialItemCount: _messages.length,
                            itemBuilder: (_, i, animation) =>
                                _animatedBubble(_messages[i], animation, g),
                          ),
                  ),

                  // ── Glass header ──────────────────────────────────────
                  Positioned(
                    top: 0,
                    left: 0,
                    right: 0,
                    child: Hero(
                      tag: 'thread-${widget.requestId}',
                      child: Material(
                        color: Colors.transparent,
                        child: _buildTop(g),
                      ),
                    ),
                  ),

                  // ── Floating pill composer ────────────────────────────
                  Positioned(
                    bottom: 16,
                    left: 16,
                    right: 16,
                    child: _buildComposer(g),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildTop(GlassColors g) {
    return ClipRect(
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 14, sigmaY: 14),
        child: Container(
          decoration: BoxDecoration(
            color: g.glassWhite,
            border: Border(bottom: BorderSide(color: g.glassBorder)),
          ),
          padding: const EdgeInsets.fromLTRB(8, 12, 16, 14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  IconButton(
                    onPressed: () => Navigator.of(context).pop(),
                    icon: Icon(
                      Icons.arrow_back_ios_new_rounded,
                      color: g.inkOnDark,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 2),
                  Expanded(
                    child: Text(
                      'Comments with Admin',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w700,
                        fontSize: 18,
                        color: g.inkOnDark,
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Container(
                margin: const EdgeInsets.symmetric(horizontal: 8),
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 12,
                ),
                decoration: BoxDecoration(
                  color: g.surface1,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: g.glassBorder),
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
                              fontSize: 14,
                              color: g.inkOnDark,
                            ),
                          ),
                          const SizedBox(height: 3),
                          Text(
                            widget.requestCode.isEmpty
                                ? 'REQ-${widget.requestId.toString().padLeft(5, '0')}'
                                : widget.requestCode,
                            style: TextStyle(
                              fontFamily: 'DM Sans',
                              fontSize: 11,
                              color: g.inkMuteDark,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 5,
                      ),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [
                            ChatColors.outBubble1,
                            ChatColors.outBubble2,
                          ],
                        ),
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: Text(
                        widget.requestStatus,
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w600,
                          fontSize: 11,
                          color: Colors.white,
                        ),
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

  Widget _buildEmpty(GlassColors g) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.forum_outlined, size: 44, color: g.inkMuteDark),
          const SizedBox(height: 12),
          Text(
            'No messages yet.\nAsk admin a question here!',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontSize: 14,
              color: g.inkMuteDark,
              height: 1.5,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLoadError(GlassColors g) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.error_outline_rounded, size: 44, color: g.inkMuteDark),
            const SizedBox(height: 12),
            Text(
              _loadError ?? 'Failed to load messages.',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 13,
                color: g.inkMuteDark,
                height: 1.5,
              ),
            ),
            const SizedBox(height: 12),
            OutlinedButton(
              onPressed: () => _loadThread(),
              child: const Text('Retry'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _animatedBubble(
    _ChatMessage m,
    Animation<double> animation,
    GlassColors g,
  ) {
    return SlideTransition(
      position: Tween<Offset>(
        begin: const Offset(0, 0.25),
        end: Offset.zero,
      ).animate(CurvedAnimation(parent: animation, curve: Curves.easeOutCubic)),
      child: FadeTransition(opacity: animation, child: _bubble(m, g)),
    );
  }

  Widget _bubble(_ChatMessage m, GlassColors g) {
    final isMine = m.senderRole == 'requestor';

    return Align(
      alignment: isMine ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        constraints: const BoxConstraints(maxWidth: 300),
        margin: const EdgeInsets.only(bottom: 10),
        child: isMine ? _outBubble(m, g) : _inBubble(m, g),
      ),
    );
  }

  Widget _outBubble(_ChatMessage m, GlassColors g) {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 10),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [ChatColors.outBubble1, ChatColors.outBubble2],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.all(Radius.circular(28)),
      ),
      child: _bubbleContent(m, Colors.white, Colors.white70),
    );
  }

  Widget _inBubble(_ChatMessage m, GlassColors g) {
    return ClipRRect(
      borderRadius: const BorderRadius.all(Radius.circular(28)),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 10, sigmaY: 10),
        child: Container(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 10),
          decoration: BoxDecoration(
            color: g.glassWhite,
            borderRadius: const BorderRadius.all(Radius.circular(28)),
            border: Border.all(color: g.glassBorder),
          ),
          child: _bubbleContent(m, g.inkOnDark, g.inkMuteDark),
        ),
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
            height: 1.4,
          ),
        ),
        const SizedBox(height: 6),
        Text(
          _time(m.createdAt),
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontSize: 10,
            color: timeColor,
          ),
        ),
      ],
    );
  }

  Widget _buildComposer(GlassColors g) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(32),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 20, sigmaY: 20),
        child: Container(
          padding: const EdgeInsets.fromLTRB(16, 8, 8, 8),
          decoration: BoxDecoration(
            color: g.inputFill,
            borderRadius: BorderRadius.circular(32),
            border: Border.all(color: g.glassBorder),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Expanded(
                child: TextField(
                  controller: _input,
                  minLines: 1,
                  maxLines: 4,
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 14,
                    color: g.inkOnDark,
                  ),
                  decoration: InputDecoration(
                    hintText: 'Message admin…',
                    hintStyle: TextStyle(
                      fontFamily: 'DM Sans',
                      color: g.inkMuteDark,
                    ),
                    filled: false,
                    border: InputBorder.none,
                    enabledBorder: InputBorder.none,
                    focusedBorder: InputBorder.none,
                    contentPadding: const EdgeInsets.symmetric(vertical: 10),
                    isDense: true,
                  ),
                ),
              ),
              const SizedBox(width: 8),
              GestureDetector(
                onTap: _sending ? null : _send,
                child: Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    gradient: _sending
                        ? null
                        : const LinearGradient(
                            colors: [
                              ChatColors.outBubble1,
                              ChatColors.outBubble2,
                            ],
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                          ),
                    color: _sending ? ChatColors.surface2 : null,
                    shape: BoxShape.circle,
                  ),
                  child: _sending
                      ? const Padding(
                          padding: EdgeInsets.all(12),
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation<Color>(
                              Colors.white,
                            ),
                          ),
                        )
                      : const Icon(
                          Icons.arrow_upward_rounded,
                          color: Colors.white,
                          size: 20,
                        ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _time(String raw) {
    final dt = DateTime.tryParse(raw);
    if (dt == null) return '';
    final hour = dt.hour > 12 ? dt.hour - 12 : (dt.hour == 0 ? 12 : dt.hour);
    final ampm = dt.hour >= 12 ? 'PM' : 'AM';
    final min = dt.minute.toString().padLeft(2, '0');
    return '$hour:$min $ampm';
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

  factory _ChatMessage.fromJson(Map<String, dynamic> json) {
    return _ChatMessage(
      id: (json['id'] as num?)?.toInt() ?? 0,
      senderRole: (json['sender_role'] ?? '').toString(),
      senderName: (json['sender_name'] ?? '').toString(),
      message: (json['message'] ?? '').toString(),
      createdAt: (json['created_at'] ?? '').toString(),
    );
  }
}
