import 'dart:async';

import 'package:flutter/material.dart';

import '../services/api_service.dart';
import '../services/session_store.dart';

class FloatingMessageButton extends StatefulWidget {
  final double bottom;
  final double right;

  const FloatingMessageButton({super.key, this.bottom = 108, this.right = 16});

  @override
  State<FloatingMessageButton> createState() => _FloatingMessageButtonState();
}

class _FloatingMessageButtonState extends State<FloatingMessageButton>
    with SingleTickerProviderStateMixin {
  int _unreadCount = 0;
  Timer? _timer;
  late final AnimationController _floatController;
  late final Animation<double> _floatAnim;

  @override
  void initState() {
    super.initState();

    // Gentle floating animation
    _floatController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 2800),
    )..repeat(reverse: true);

    _floatAnim = Tween<double>(begin: 0, end: -5).animate(
      CurvedAnimation(parent: _floatController, curve: Curves.easeInOut),
    );

    _loadUnreadCount();
    _timer = Timer.periodic(const Duration(seconds: 10), (_) {
      _loadUnreadCount();
    });
  }

  @override
  void dispose() {
    _floatController.dispose();
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
      // Silent fail
    }
  }

  @override
  Widget build(BuildContext context) {
    return Positioned(
      right: widget.right,
      bottom: widget.bottom,
      child: GestureDetector(
        onTap: () async {
          await Navigator.pushNamed(context, '/messages');
          if (!mounted) return;
          _loadUnreadCount();
        },
        child: AnimatedBuilder(
          animation: _floatAnim,
          builder: (context, child) {
            return Transform.translate(
              offset: Offset(0, _floatAnim.value),
              child: child,
            );
          },
          child: Stack(
            clipBehavior: Clip.none,
            children: [
              // Pulse ring when there are unread messages
              if (_unreadCount > 0) Positioned.fill(child: _PulseRing()),

              // Main button
              Container(
                width: 54,
                height: 54,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [Color(0xFF001540), Color(0xFF002D80)],
                  ),
                  borderRadius: BorderRadius.circular(18),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF001540).withOpacity(0.42),
                      blurRadius: 20,
                      offset: const Offset(0, 8),
                    ),
                    BoxShadow(
                      color: const Color(0xFF001540).withOpacity(0.2),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: const Icon(
                  Icons.chat_bubble_rounded,
                  color: Colors.white,
                  size: 24,
                ),
              ),

              // Unread badge
              if (_unreadCount > 0)
                Positioned(
                  right: -5,
                  top: -5,
                  child: Container(
                    constraints: const BoxConstraints(
                      minWidth: 20,
                      minHeight: 20,
                    ),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 5,
                      vertical: 2,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xFFFF3B30),
                      borderRadius: BorderRadius.circular(99),
                      border: Border.all(color: Colors.white, width: 2.5),
                      boxShadow: const [
                        BoxShadow(
                          color: Color(0x40FF3B30),
                          blurRadius: 8,
                          offset: Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Text(
                      _unreadCount > 9 ? '9+' : '$_unreadCount',
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w900,
                        fontSize: 9.5,
                        color: Colors.white,
                        letterSpacing: -0.3,
                      ),
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

// Animated pulse ring widget
class _PulseRing extends StatefulWidget {
  @override
  State<_PulseRing> createState() => _PulseRingState();
}

class _PulseRingState extends State<_PulseRing>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;
  late final Animation<double> _scale;
  late final Animation<double> _opacity;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1800),
    )..repeat();
    _scale = Tween<double>(
      begin: 1.0,
      end: 1.65,
    ).animate(CurvedAnimation(parent: _ctrl, curve: Curves.easeOut));
    _opacity = Tween<double>(
      begin: 0.55,
      end: 0.0,
    ).animate(CurvedAnimation(parent: _ctrl, curve: Curves.easeOut));
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _ctrl,
      builder: (_, __) => Transform.scale(
        scale: _scale.value,
        child: Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(18),
            border: Border.all(
              color: const Color(0xFFFF3B30).withOpacity(_opacity.value),
              width: 2,
            ),
          ),
        ),
      ),
    );
  }
}
