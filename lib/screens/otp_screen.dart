import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../services/api_service.dart';
import '../services/session_store.dart';
import 'home_screen.dart';

class OtpScreen extends StatefulWidget {
  final String email;
  final String password;
  final bool autoSendOtp;

  const OtpScreen({
    super.key,
    required this.email,
    required this.password,
    this.autoSendOtp = false,
  });

  @override
  State<OtpScreen> createState() => _OtpScreenState();
}

class _OtpScreenState extends State<OtpScreen> with TickerProviderStateMixin {
  final _otpCtrl = TextEditingController();
  final _focusNode = FocusNode();
  bool _isVerifying = false;

  // ── Animations ─────────────────────────────────────────────────────────────
  late final AnimationController _entryCtrl;
  late final Animation<double> _cardFade;
  late final Animation<Offset> _cardSlide;

  // Success animation
  late final AnimationController _successCtrl;
  late final Animation<double> _successScale;
  late final Animation<double> _successFade;

  // Shake animation for wrong OTP
  late final AnimationController _shakeCtrl;
  late final Animation<double> _shakeAnim;

  // Resend cooldown
  int _resendCooldown = 0;
  Timer? _cooldownTimer;

  @override
  void initState() {
    super.initState();
    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: Brightness.light,
      ),
    );

    // Entry
    _entryCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    )..forward();
    _cardFade = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _entryCtrl,
        curve: const Interval(0.20, 0.70, curve: Curves.easeOut),
      ),
    );
    _cardSlide = Tween<Offset>(begin: const Offset(0, 0.06), end: Offset.zero)
        .animate(
          CurvedAnimation(
            parent: _entryCtrl,
            curve: const Interval(0.20, 0.75, curve: Curves.easeOutCubic),
          ),
        );

    // Success
    _successCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 600),
    );
    _successScale = Tween<double>(
      begin: 0.5,
      end: 1.0,
    ).animate(CurvedAnimation(parent: _successCtrl, curve: Curves.elasticOut));
    _successFade = CurvedAnimation(parent: _successCtrl, curve: Curves.easeOut);

    // Shake
    _shakeCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 400),
    );
    _shakeAnim = TweenSequence<double>([
      TweenSequenceItem(tween: Tween(begin: 0, end: -8), weight: 1),
      TweenSequenceItem(tween: Tween(begin: -8, end: 8), weight: 2),
      TweenSequenceItem(tween: Tween(begin: 8, end: -6), weight: 2),
      TweenSequenceItem(tween: Tween(begin: -6, end: 6), weight: 2),
      TweenSequenceItem(tween: Tween(begin: 6, end: 0), weight: 1),
    ]).animate(CurvedAnimation(parent: _shakeCtrl, curve: Curves.easeInOut));

    _otpCtrl.addListener(() {
      if (_otpCtrl.text.length == 6 && !_isVerifying) _verifyOtp();
      setState(() {});
    });

    if (widget.autoSendOtp) {
      Future.delayed(const Duration(milliseconds: 300), () {
        if (mounted) _resendOtp(showSuccess: false);
      });
    }
  }

  @override
  void dispose() {
    _otpCtrl.dispose();
    _focusNode.dispose();
    _entryCtrl.dispose();
    _successCtrl.dispose();
    _shakeCtrl.dispose();
    _cooldownTimer?.cancel();
    super.dispose();
  }

  void _startCooldown() {
    setState(() => _resendCooldown = 60);
    _cooldownTimer = Timer.periodic(const Duration(seconds: 1), (t) {
      if (!mounted) {
        t.cancel();
        return;
      }
      setState(() {
        _resendCooldown--;
        if (_resendCooldown <= 0) t.cancel();
      });
    });
  }

  // ── All original logic preserved ─────────────────────────────────────────
  Future<void> _verifyOtp() async {
    if (_isVerifying || _otpCtrl.text.length < 6) return;
    setState(() => _isVerifying = true);
    try {
      await ApiService.verifyOtp(email: widget.email, otp: _otpCtrl.text);

      // Show success flash
      _successCtrl.forward();
      await Future.delayed(const Duration(milliseconds: 700));

      final loginResult = await ApiService.login(
        email: widget.email,
        password: widget.password,
      );

      final data =
          (loginResult['data'] as Map<String, dynamic>?) ??
          (loginResult['user'] as Map<String, dynamic>?) ??
          loginResult;
      final userId =
          (data['id'] as num?)?.toInt() ?? (data['user_id'] as num?)?.toInt();
      final name = (data['name'] ?? loginResult['name'] ?? '').toString();
      final userEmail = (data['email'] ?? loginResult['email'] ?? widget.email)
          .toString();

      if (userId == null) throw Exception('Invalid login response after OTP');
      SessionStore.setUser(id: userId, userName: name, userEmail: userEmail);
      if (!mounted) return;

      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const HomeScreen()),
        (_) => false,
      );
    } catch (e) {
      if (!mounted) return;
      _shakeCtrl.forward(from: 0);
      _showSnack(
        'Verification failed: ${e.toString().replaceFirst('Exception: ', '')}',
      );
      _otpCtrl.clear();
      _focusNode.requestFocus();
    } finally {
      if (mounted) setState(() => _isVerifying = false);
    }
  }

  Future<void> _resendOtp({bool showSuccess = true}) async {
    if (_resendCooldown > 0) return;
    try {
      await ApiService.resendOtp(email: widget.email);
      if (!mounted) return;
      _startCooldown();
      _showSnack(
        showSuccess
            ? 'Verification code resent successfully.'
            : 'A new verification code has been sent to your email.',
      );
    } catch (e) {
      if (!mounted) return;
      _showSnack(
        'Failed to resend code: ${e.toString().replaceFirst('Exception: ', '')}',
      );
    }
  }

  void _showSnack(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          msg,
          style: const TextStyle(
            fontFamily: 'DM Sans',
            fontWeight: FontWeight.w600,
            fontSize: 13,
          ),
        ),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        margin: const EdgeInsets.fromLTRB(16, 0, 16, 16),
      ),
    );
  }

  // ── BUILD ─────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF001540),
      resizeToAvoidBottomInset: true,
      body: Stack(
        children: [
          // ── Background gradient ──────────────────────────────────────────
          Container(
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [
                  Color(0xFF001540),
                  Color(0xFF002878),
                  Color(0xFF001030),
                ],
                stops: [0.0, 0.5, 1.0],
              ),
            ),
          ),

          // Orbs
          Positioned(
            top: -60,
            right: -60,
            child: Container(
              width: 260,
              height: 260,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: const Color(0xFF4B7BF5).withOpacity(0.12),
              ),
            ),
          ),
          Positioned(
            bottom: MediaQuery.of(context).size.height * 0.35,
            left: -40,
            child: Container(
              width: 160,
              height: 160,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: const Color(0xFFF59E0B).withOpacity(0.07),
              ),
            ),
          ),

          // ── Main content ─────────────────────────────────────────────────
          SafeArea(
            child: Column(
              children: [
                Expanded(
                  child: Center(
                    child: SingleChildScrollView(
                      physics: const BouncingScrollPhysics(),
                      padding: const EdgeInsets.symmetric(horizontal: 24),
                      child: FadeTransition(
                        opacity: _cardFade,
                        child: SlideTransition(
                          position: _cardSlide,
                          child: Container(
                            width: double.infinity,
                            padding: const EdgeInsets.fromLTRB(28, 34, 28, 34),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(28),
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(
                                    0xFF001540,
                                  ).withOpacity(0.3),
                                  blurRadius: 40,
                                  offset: const Offset(0, 20),
                                ),
                                BoxShadow(
                                  color: const Color(
                                    0xFF001540,
                                  ).withOpacity(0.1),
                                  blurRadius: 10,
                                  offset: const Offset(0, 4),
                                ),
                              ],
                            ),
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                // Icon
                                _buildIconBadge(),
                                const SizedBox(height: 22),

                                // Title
                                const Text(
                                  'Verify Your Email',
                                  style: TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontSize: 24,
                                    fontWeight: FontWeight.w900,
                                    color: Color(0xFF080F1E),
                                    letterSpacing: -0.5,
                                  ),
                                  textAlign: TextAlign.center,
                                ),
                                const SizedBox(height: 10),

                                // Subtitle
                                RichText(
                                  textAlign: TextAlign.center,
                                  text: TextSpan(
                                    style: const TextStyle(
                                      fontFamily: 'DM Sans',
                                      fontSize: 13.5,
                                      color: Color(0xFF9AA3B2),
                                      height: 1.55,
                                    ),
                                    children: [
                                      const TextSpan(
                                        text: 'We sent a 6-digit code to\n',
                                      ),
                                      TextSpan(
                                        text: widget.email,
                                        style: const TextStyle(
                                          fontWeight: FontWeight.w800,
                                          color: Color(0xFF080F1E),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),

                                const SizedBox(height: 30),

                                // OTP boxes
                                _buildOtpBoxes(),

                                // Hidden keyboard input
                                Opacity(
                                  opacity: 0,
                                  child: SizedBox(
                                    height: 0,
                                    width: 0,
                                    child: TextField(
                                      controller: _otpCtrl,
                                      focusNode: _focusNode,
                                      keyboardType: TextInputType.number,
                                      inputFormatters: [
                                        FilteringTextInputFormatter.digitsOnly,
                                      ],
                                      maxLength: 6,
                                      autofocus: true,
                                    ),
                                  ),
                                ),

                                const SizedBox(height: 28),

                                // Verify button
                                _buildVerifyButton(),

                                const SizedBox(height: 22),

                                // Resend row
                                _buildResendRow(),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),

          // ── Success overlay ──────────────────────────────────────────────
          AnimatedBuilder(
            animation: _successFade,
            builder: (_, __) {
              if (_successFade.value == 0) return const SizedBox.shrink();
              return Positioned.fill(
                child: Container(
                  color: Colors.black.withOpacity(0.35 * _successFade.value),
                  child: Center(
                    child: ScaleTransition(
                      scale: _successScale,
                      child: FadeTransition(
                        opacity: _successFade,
                        child: Container(
                          width: 150,
                          height: 150,
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(30),
                            boxShadow: const [
                              BoxShadow(
                                color: Color(0x40000000),
                                blurRadius: 36,
                                offset: Offset(0, 14),
                              ),
                            ],
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                width: 56,
                                height: 56,
                                decoration: BoxDecoration(
                                  gradient: const LinearGradient(
                                    colors: [
                                      Color(0xFF34D399),
                                      Color(0xFF05C46B),
                                    ],
                                  ),
                                  shape: BoxShape.circle,
                                  boxShadow: const [
                                    BoxShadow(
                                      color: Color(0x4005C46B),
                                      blurRadius: 14,
                                      offset: Offset(0, 5),
                                    ),
                                  ],
                                ),
                                child: const Icon(
                                  Icons.check_rounded,
                                  color: Colors.white,
                                  size: 28,
                                ),
                              ),
                              const SizedBox(height: 12),
                              const Text(
                                'Verified!',
                                style: TextStyle(
                                  fontFamily: 'DM Sans',
                                  fontWeight: FontWeight.w900,
                                  fontSize: 16,
                                  color: Color(0xFF080F1E),
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
            },
          ),
        ],
      ),
    );
  }

  // ── Icon badge ────────────────────────────────────────────────────────────
  Widget _buildIconBadge() {
    return Stack(
      alignment: Alignment.center,
      children: [
        // Outer glow ring
        Container(
          width: 80,
          height: 80,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: const Color(0xFF002366).withOpacity(0.06),
          ),
        ),
        // Inner circle
        Container(
          width: 64,
          height: 64,
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Color(0xFF001540), Color(0xFF0032A0)],
            ),
            shape: BoxShape.circle,
            boxShadow: const [
              BoxShadow(
                color: Color(0x40001540),
                blurRadius: 16,
                offset: Offset(0, 6),
              ),
            ],
          ),
          child: const Icon(
            Icons.shield_rounded,
            color: Colors.white,
            size: 30,
          ),
        ),
      ],
    );
  }

  // ── OTP boxes ─────────────────────────────────────────────────────────────
  Widget _buildOtpBoxes() {
    return AnimatedBuilder(
      animation: _shakeAnim,
      builder: (_, child) => Transform.translate(
        offset: Offset(_shakeAnim.value, 0),
        child: child,
      ),
      child: GestureDetector(
        onTap: () => _focusNode.requestFocus(),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: List.generate(6, (i) {
            final text = _otpCtrl.text;
            final char = text.length > i ? text[i] : '';
            final isFocused = text.length == i && _focusNode.hasFocus;
            final isFilled = text.length > i;

            return AnimatedContainer(
              duration: const Duration(milliseconds: 180),
              curve: Curves.easeOutCubic,
              width: 44,
              height: 54,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                gradient: isFilled
                    ? const LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: [Color(0xFFF0F5FF), Color(0xFFE8F0FF)],
                      )
                    : null,
                color: isFilled ? null : const Color(0xFFF8FAFE),
                border: Border.all(
                  color: isFocused
                      ? const Color(0xFF2B5CE6)
                      : isFilled
                      ? const Color(0xFF002366)
                      : const Color(0xFFE4E8F0),
                  width: isFocused || isFilled ? 2 : 1.5,
                ),
                borderRadius: BorderRadius.circular(14),
                boxShadow: isFilled
                    ? [
                        BoxShadow(
                          color: const Color(0xFF002366).withOpacity(0.1),
                          blurRadius: 8,
                          offset: const Offset(0, 3),
                        ),
                      ]
                    : null,
              ),
              child: char.isNotEmpty
                  ? Text(
                      char,
                      style: const TextStyle(
                        fontFamily: 'DM Sans',
                        fontSize: 22,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFF002366),
                        letterSpacing: -0.5,
                      ),
                    )
                  : isFocused
                  // Blinking cursor dot
                  ? _BlinkingCursor()
                  : null,
            );
          }),
        ),
      ),
    );
  }

  // ── Verify button ─────────────────────────────────────────────────────────
  Widget _buildVerifyButton() {
    final isReady = _otpCtrl.text.length == 6 && !_isVerifying;

    return AnimatedContainer(
      duration: const Duration(milliseconds: 250),
      width: double.infinity,
      height: 52,
      decoration: BoxDecoration(
        gradient: isReady
            ? const LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [Color(0xFF001540), Color(0xFF0032A0)],
              )
            : null,
        color: isReady ? null : const Color(0xFFF1F4FB),
        borderRadius: BorderRadius.circular(16),
        boxShadow: isReady
            ? const [
                BoxShadow(
                  color: Color(0x40001540),
                  blurRadius: 16,
                  offset: Offset(0, 6),
                ),
              ]
            : null,
      ),
      child: ElevatedButton(
        onPressed: isReady ? _verifyOtp : null,
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.transparent,
          shadowColor: Colors.transparent,
          disabledBackgroundColor: Colors.transparent,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
        ),
        child: _isVerifying
            ? const SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(
                  strokeWidth: 2.5,
                  valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                ),
              )
            : Text(
                'Verify Code',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontSize: 15.5,
                  fontWeight: FontWeight.w900,
                  color: isReady ? Colors.white : const Color(0xFF9AA3B2),
                  letterSpacing: 0.1,
                ),
              ),
      ),
    );
  }

  // ── Resend row ────────────────────────────────────────────────────────────
  Widget _buildResendRow() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        const Text(
          "Didn't receive the code? ",
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontSize: 13,
            color: Color(0xFF9AA3B2),
          ),
        ),
        GestureDetector(
          onTap: _resendCooldown > 0 ? null : _resendOtp,
          child: AnimatedDefaultTextStyle(
            duration: const Duration(milliseconds: 200),
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontSize: 13,
              fontWeight: FontWeight.w800,
              color: _resendCooldown > 0
                  ? const Color(0xFF9AA3B2)
                  : const Color(0xFF2B5CE6),
            ),
            child: Text(
              _resendCooldown > 0 ? 'Resend in ${_resendCooldown}s' : 'Resend',
            ),
          ),
        ),
      ],
    );
  }
}

// ── Blinking cursor ───────────────────────────────────────────────────────────
class _BlinkingCursor extends StatefulWidget {
  @override
  State<_BlinkingCursor> createState() => _BlinkingCursorState();
}

class _BlinkingCursorState extends State<_BlinkingCursor>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 600),
    )..repeat(reverse: true);
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => FadeTransition(
    opacity: _ctrl,
    child: Container(
      width: 2,
      height: 22,
      decoration: BoxDecoration(
        color: const Color(0xFF002366),
        borderRadius: BorderRadius.circular(1),
      ),
    ),
  );
}
