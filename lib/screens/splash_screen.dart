import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'login_screen.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with TickerProviderStateMixin {
  late final AnimationController _mainCtrl;
  late final AnimationController _pulseCtrl;
  late final AnimationController _shimmerCtrl;

  late final Animation<double> _logoScale;
  late final Animation<double> _logoFade;
  late final Animation<double> _wordmarkFade;
  late final Animation<Offset> _wordmarkSlide;
  late final Animation<double> _taglineFade;
  late final Animation<double> _pulse;
  late final Animation<double> _shimmer;

  @override
  void initState() {
    super.initState();

    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: Brightness.light,
      ),
    );

    _mainCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1800),
    );

    _pulseCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 2000),
    )..repeat(reverse: true);

    _shimmerCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1600),
    );

    // Logo: scale from 0.6→1.0 + fade in
    _logoScale = Tween<double>(begin: 0.6, end: 1.0).animate(
      CurvedAnimation(
        parent: _mainCtrl,
        curve: const Interval(0.0, 0.5, curve: Curves.easeOutBack),
      ),
    );
    _logoFade = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _mainCtrl,
        curve: const Interval(0.0, 0.4, curve: Curves.easeOut),
      ),
    );

    // Wordmark slides up + fades in after logo
    _wordmarkFade = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _mainCtrl,
        curve: const Interval(0.35, 0.65, curve: Curves.easeOut),
      ),
    );
    _wordmarkSlide =
        Tween<Offset>(begin: const Offset(0, 0.4), end: Offset.zero).animate(
          CurvedAnimation(
            parent: _mainCtrl,
            curve: const Interval(0.35, 0.70, curve: Curves.easeOutCubic),
          ),
        );

    // Tagline fades last
    _taglineFade = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _mainCtrl,
        curve: const Interval(0.60, 0.85, curve: Curves.easeOut),
      ),
    );

    // Subtle breathing pulse on the glow ring
    _pulse = Tween<double>(
      begin: 0.5,
      end: 1.0,
    ).animate(CurvedAnimation(parent: _pulseCtrl, curve: Curves.easeInOut));

    // Shimmer sweep across wordmark
    _shimmer = Tween<double>(begin: -1.0, end: 2.0).animate(
      CurvedAnimation(
        parent: _shimmerCtrl,
        curve: const Interval(0.0, 1.0, curve: Curves.easeInOut),
      ),
    );

    // Sequence: start main, then shimmer, then navigate
    _mainCtrl.forward().then((_) {
      _shimmerCtrl.forward();
      Future.delayed(const Duration(milliseconds: 1200), _goToLogin);
    });
  }

  void _goToLogin() {
    if (!mounted) return;
    Navigator.of(context).pushReplacement(
      PageRouteBuilder(
        pageBuilder: (_, __, ___) => const LoginScreen(),
        transitionsBuilder: (_, anim, __, child) =>
            FadeTransition(opacity: anim, child: child),
        transitionDuration: const Duration(milliseconds: 600),
      ),
    );
  }

  @override
  void dispose() {
    _mainCtrl.dispose();
    _pulseCtrl.dispose();
    _shimmerCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF001540),
      body: Stack(
        children: [
          // ── Background gradient ──────────────────────────────────────────
          Container(
            decoration: const BoxDecoration(
              gradient: RadialGradient(
                center: Alignment(0.0, -0.3),
                radius: 1.4,
                colors: [
                  Color(0xFF002D80),
                  Color(0xFF001540),
                  Color(0xFF00091A),
                ],
                stops: [0.0, 0.55, 1.0],
              ),
            ),
          ),

          // ── Decorative orbs ──────────────────────────────────────────────
          Positioned(
            top: -80,
            right: -80,
            child: AnimatedBuilder(
              animation: _pulse,
              builder: (_, __) => Opacity(
                opacity: _pulse.value * 0.15,
                child: Container(
                  width: 320,
                  height: 320,
                  decoration: const BoxDecoration(
                    shape: BoxShape.circle,
                    color: Color(0xFF4B7BF5),
                  ),
                ),
              ),
            ),
          ),
          Positioned(
            bottom: -60,
            left: -60,
            child: AnimatedBuilder(
              animation: _pulse,
              builder: (_, __) => Opacity(
                opacity: (1.0 - _pulse.value) * 0.12,
                child: Container(
                  width: 240,
                  height: 240,
                  decoration: const BoxDecoration(
                    shape: BoxShape.circle,
                    color: Color(0xFFF59E0B),
                  ),
                ),
              ),
            ),
          ),

          // ── Main content ─────────────────────────────────────────────────
          Center(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Logo with glow ring
                FadeTransition(
                  opacity: _logoFade,
                  child: ScaleTransition(
                    scale: _logoScale,
                    child: Stack(
                      alignment: Alignment.center,
                      children: [
                        // Outer glow ring
                        AnimatedBuilder(
                          animation: _pulse,
                          builder: (_, __) => Container(
                            width: 140,
                            height: 140,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              boxShadow: [
                                BoxShadow(
                                  color: const Color(
                                    0xFF4B7BF5,
                                  ).withOpacity(_pulse.value * 0.35),
                                  blurRadius: 40,
                                  spreadRadius: 10,
                                ),
                              ],
                            ),
                          ),
                        ),
                        // Shield container
                        Container(
                          width: 110,
                          height: 110,
                          decoration: BoxDecoration(
                            shape: BoxShape.circle,
                            color: Colors.white.withOpacity(0.06),
                            border: Border.all(
                              color: Colors.white.withOpacity(0.12),
                              width: 1.5,
                            ),
                          ),
                          child: Center(
                            child: Image.asset(
                              'assets/nu_shield.png',
                              width: 70,
                              height: 70,
                              fit: BoxFit.contain,
                              errorBuilder: (_, __, ___) =>
                                  const _FallbackShield(),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),

                const SizedBox(height: 28),

                // Wordmark: NU + POST
                FadeTransition(
                  opacity: _wordmarkFade,
                  child: SlideTransition(
                    position: _wordmarkSlide,
                    child: AnimatedBuilder(
                      animation: _shimmer,
                      builder: (_, child) {
                        return ShaderMask(
                          shaderCallback: (bounds) {
                            return LinearGradient(
                              begin: Alignment(_shimmer.value - 0.6, 0),
                              end: Alignment(_shimmer.value + 0.6, 0),
                              colors: const [
                                Colors.transparent,
                                Colors.white,
                                Colors.transparent,
                              ],
                              stops: const [0.0, 0.5, 1.0],
                            ).createShader(bounds);
                          },
                          blendMode: BlendMode.srcATop,
                          child: child,
                        );
                      },
                      child: RichText(
                        text: const TextSpan(
                          children: [
                            TextSpan(
                              text: 'NU',
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontSize: 44,
                                fontWeight: FontWeight.w900,
                                color: Color(0xFFF59E0B),
                                letterSpacing: 3,
                              ),
                            ),
                            TextSpan(
                              text: 'POST',
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontSize: 44,
                                fontWeight: FontWeight.w900,
                                color: Colors.white,
                                letterSpacing: 3,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),

                const SizedBox(height: 10),

                // Tagline
                FadeTransition(
                  opacity: _taglineFade,
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 24,
                        height: 1,
                        color: Colors.white.withOpacity(0.25),
                      ),
                      const SizedBox(width: 10),
                      Text(
                        'NU Lipa Marketing Office',
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontSize: 12.5,
                          fontWeight: FontWeight.w500,
                          color: Colors.white.withOpacity(0.45),
                          letterSpacing: 0.8,
                        ),
                      ),
                      const SizedBox(width: 10),
                      Container(
                        width: 24,
                        height: 1,
                        color: Colors.white.withOpacity(0.25),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // ── Bottom loading dots ──────────────────────────────────────────
          Positioned(
            bottom: 60,
            left: 0,
            right: 0,
            child: FadeTransition(
              opacity: _taglineFade,
              child: const _LoadingDots(),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Loading Dots ──────────────────────────────────────────────────────────────
class _LoadingDots extends StatefulWidget {
  const _LoadingDots();

  @override
  State<_LoadingDots> createState() => _LoadingDotsState();
}

class _LoadingDotsState extends State<_LoadingDots>
    with SingleTickerProviderStateMixin {
  late final AnimationController _ctrl;

  @override
  void initState() {
    super.initState();
    _ctrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    )..repeat();
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: List.generate(3, (i) {
        final start = i * 0.25;
        final end = start + 0.5;
        final anim = Tween<double>(begin: 0.3, end: 1.0).animate(
          CurvedAnimation(
            parent: _ctrl,
            curve: Interval(
              start.clamp(0.0, 1.0),
              end.clamp(0.0, 1.0),
              curve: Curves.easeInOut,
            ),
          ),
        );
        return AnimatedBuilder(
          animation: anim,
          builder: (_, __) => Container(
            margin: const EdgeInsets.symmetric(horizontal: 4),
            width: 6,
            height: 6,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: Colors.white.withOpacity(anim.value),
            ),
          ),
        );
      }),
    );
  }
}

// ── Fallback Shield ───────────────────────────────────────────────────────────
class _FallbackShield extends StatelessWidget {
  const _FallbackShield();

  @override
  Widget build(BuildContext context) {
    return const Text(
      'NU',
      style: TextStyle(
        fontFamily: 'DM Sans',
        fontWeight: FontWeight.w900,
        fontSize: 32,
        color: Color(0xFFF59E0B),
      ),
    );
  }
}
