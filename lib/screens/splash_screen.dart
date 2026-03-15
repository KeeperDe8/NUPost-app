import 'package:flutter/material.dart';
import 'login_screen.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _fadeAnim;

  @override
  void initState() {
    super.initState();

    // Fade-in animation over 800ms
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 800),
    );
    _fadeAnim = CurvedAnimation(parent: _controller, curve: Curves.easeIn);
    _controller.forward();

    // Navigate to Login after 2.5 seconds
    Future.delayed(const Duration(milliseconds: 2500), _goToLogin);
  }

  void _goToLogin() {
    if (!mounted) return;
    Navigator.of(context).pushReplacement(
      PageRouteBuilder(
        pageBuilder: (_, __, ___) => const LoginScreen(),
        transitionsBuilder: (_, animation, __, child) =>
            FadeTransition(opacity: animation, child: child),
        transitionDuration: const Duration(milliseconds: 500),
      ),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      // Background colour from Figma: #29286A
      backgroundColor: const Color(0xFF29286A),
      body: FadeTransition(
        opacity: _fadeAnim,
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // ── NU Shield logo ─────────────────────────────
              // Asset: assets/images/nu_shield.png
              // Size from Figma: 344×230 → scaled down to fit mobile centre
              Image.asset(
                'assets/nu_shield.png',
                width: 150,
                height: 100,
                fit: BoxFit.contain,
                errorBuilder: (_, __, ___) => const _ShieldFallback(),
              ),

              const SizedBox(height: 20),

              // ── NUPOST wordmark ────────────────────────────
              RichText(
                text: const TextSpan(
                  children: [
                    TextSpan(
                      text: 'NU',
                      style: TextStyle(
                        fontSize: 38,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFFFFC72C), // Gold
                        letterSpacing: 2,
                      ),
                    ),
                    TextSpan(
                      text: 'POST',
                      style: TextStyle(
                        fontSize: 38,
                        fontWeight: FontWeight.w900,
                        color: Colors.white,
                        letterSpacing: 2,
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
}

// ── Fallback painted shield (shown if asset is missing) ─────────────────────
class _ShieldFallback extends StatelessWidget {
  const _ShieldFallback();

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 100,
      height: 100,
      child: CustomPaint(painter: _ShieldPainter()),
    );
  }
}

class _ShieldPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final w = size.width;
    final h = size.height;

    // White shield body
    final fill = Paint()
      ..color = Colors.white
      ..style = PaintingStyle.fill;
    final path = Path()
      ..moveTo(w * 0.5, h * 0.97)
      ..cubicTo(w * 0.5, h * 0.97, w * 0.05, h * 0.70, w * 0.05, h * 0.42)
      ..cubicTo(w * 0.05, h * 0.17, w * 0.15, h * 0.06, w * 0.5, h * 0.04)
      ..cubicTo(w * 0.85, h * 0.06, w * 0.95, h * 0.17, w * 0.95, h * 0.42)
      ..cubicTo(w * 0.95, h * 0.70, w * 0.5, h * 0.97, w * 0.5, h * 0.97)
      ..close();
    canvas.drawPath(path, fill);

    // Dark blue inner fill
    final inner = Paint()
      ..color = const Color(0xFF29286A)
      ..style = PaintingStyle.fill;
    final innerPath = Path()
      ..moveTo(w * 0.5, h * 0.92)
      ..cubicTo(w * 0.5, h * 0.92, w * 0.10, h * 0.68, w * 0.10, h * 0.43)
      ..cubicTo(w * 0.10, h * 0.21, w * 0.19, h * 0.11, w * 0.5, h * 0.09)
      ..cubicTo(w * 0.81, h * 0.11, w * 0.90, h * 0.21, w * 0.90, h * 0.43)
      ..cubicTo(w * 0.90, h * 0.68, w * 0.5, h * 0.92, w * 0.5, h * 0.92)
      ..close();
    canvas.drawPath(innerPath, inner);

    // Gold "NU" text
    TextPainter(
        text: const TextSpan(
          text: 'NU',
          style: TextStyle(
            color: Color(0xFFFFC72C),
            fontSize: 30,
            fontWeight: FontWeight.w900,
          ),
        ),
        textDirection: TextDirection.ltr,
      )
      ..layout()
      ..paint(canvas, Offset(w * 0.18, h * 0.14));

    // Gold "1900" text
    TextPainter(
        text: const TextSpan(
          text: '1900',
          style: TextStyle(
            color: Color(0xFFFFC72C),
            fontSize: 10,
            fontWeight: FontWeight.w600,
            letterSpacing: 1,
          ),
        ),
        textDirection: TextDirection.ltr,
      )
      ..layout()
      ..paint(canvas, Offset(w * 0.28, h * 0.72));
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}
