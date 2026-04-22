import 'dart:async';

import 'package:flutter/material.dart';

import '../services/api_service.dart';
import '../services/session_store.dart';

class FloatingMessageButton extends StatefulWidget {
  final double bottom;
  final double right;

  const FloatingMessageButton({super.key, this.bottom = 92, this.right = 16});

  @override
  State<FloatingMessageButton> createState() => _FloatingMessageButtonState();
}

class _FloatingMessageButtonState extends State<FloatingMessageButton> {
  int _unreadCount = 0;
  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _loadUnreadCount();
    _timer = Timer.periodic(const Duration(seconds: 10), (_) {
      _loadUnreadCount();
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  Future<void> _loadUnreadCount() async {
    final userId = SessionStore.userId;
    if (userId == null || userId == 0) return;

    try {
      final result = await ApiService.fetchMessageThreads(userId: userId);
      final meta = (result['meta'] as Map<String, dynamic>?) ?? {};
      final unread = (meta['total_unread'] as num?)?.toInt() ?? 0;
      if (!mounted) return;
      setState(() => _unreadCount = unread);
    } catch (_) {
      // Silent fail to avoid UI disruptions.
    }
  }

  @override
  Widget build(BuildContext context) {
    return Positioned(
      right: widget.right,
      bottom: widget.bottom + MediaQuery.of(context).padding.bottom,
      child: GestureDetector(
        onTap: () async {
          await Navigator.pushNamed(context, '/messages');
          if (!mounted) return;
          _loadUnreadCount();
        },
        child: Stack(
          clipBehavior: Clip.none,
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                color: const Color(0xFF001A4D),
                borderRadius: BorderRadius.circular(18),
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x33000000),
                    blurRadius: 18,
                    offset: Offset(0, 8),
                  ),
                ],
              ),
              child: const Icon(
                Icons.chat_bubble_rounded,
                color: Colors.white,
                size: 26,
              ),
            ),
            if (_unreadCount > 0)
              Positioned(
                right: -4,
                top: -4,
                child: Container(
                  constraints: const BoxConstraints(
                    minWidth: 22,
                    minHeight: 22,
                  ),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 6,
                    vertical: 3,
                  ),
                  decoration: BoxDecoration(
                    color: const Color(0xFFE11D48),
                    borderRadius: BorderRadius.circular(999),
                    border: Border.all(color: Colors.white, width: 2),
                  ),
                  child: Text(
                    _unreadCount > 99 ? '99+' : '$_unreadCount',
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      fontFamily: 'Inter',
                      fontWeight: FontWeight.w700,
                      fontSize: 10,
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
}
