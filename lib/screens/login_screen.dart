import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../services/api_service.dart';
import '../services/session_store.dart';
import '../theme/app_theme.dart';
import 'home_screen.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen>
    with TickerProviderStateMixin {
  final _emailCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  bool _obscurePassword = true;
  bool _isLoggingIn = false;

  late final AnimationController _entryCtrl;
  late final AnimationController _btnCtrl;

  late final Animation<double> _cardFade;
  late final Animation<Offset> _cardSlide;
  late final Animation<double> _logoFade;
  late final Animation<double> _logoScale;
  late final Animation<double> _btnScale;

  @override
  void initState() {
    super.initState();

    SystemChrome.setSystemUIOverlayStyle(
      const SystemUiOverlayStyle(
        statusBarColor: Colors.transparent,
        statusBarIconBrightness: Brightness.light,
      ),
    );

    _entryCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    )..forward();

    _btnCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 130),
      lowerBound: 0.0,
      upperBound: 1.0,
    );

    _logoFade = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _entryCtrl,
        curve: const Interval(0.0, 0.5, curve: Curves.easeOut),
      ),
    );
    _logoScale = Tween<double>(begin: 0.7, end: 1.0).animate(
      CurvedAnimation(
        parent: _entryCtrl,
        curve: const Interval(0.0, 0.55, curve: Curves.easeOutBack),
      ),
    );
    _cardFade = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _entryCtrl,
        curve: const Interval(0.30, 0.80, curve: Curves.easeOut),
      ),
    );
    _cardSlide = Tween<Offset>(begin: const Offset(0, 0.06), end: Offset.zero)
        .animate(
          CurvedAnimation(
            parent: _entryCtrl,
            curve: const Interval(0.30, 0.85, curve: Curves.easeOutCubic),
          ),
        );
    _btnScale = Tween<double>(
      begin: 1.0,
      end: 0.94,
    ).animate(CurvedAnimation(parent: _btnCtrl, curve: Curves.easeOut));
  }

  @override
  void dispose() {
    _entryCtrl.dispose();
    _btnCtrl.dispose();
    _emailCtrl.dispose();
    _passwordCtrl.dispose();
    super.dispose();
  }

  Future<void> _onLogin() async {
    if (_isLoggingIn) return;
    final email = _emailCtrl.text.trim();
    final password = _passwordCtrl.text;

    if (email.isEmpty || password.isEmpty) {
      _showSnack('Please enter email and password.');
      return;
    }

    setState(() => _isLoggingIn = true);
    try {
      final result = await ApiService.login(email: email, password: password);
      final data = (result['data'] as Map<String, dynamic>?) ?? {};
      final userId = (data['id'] as num?)?.toInt();
      final name = (data['name'] ?? '').toString();
      final userEmail = (data['email'] ?? email).toString();

      if (userId == null) throw Exception('Invalid login response');

      SessionStore.setUser(id: userId, userName: name, userEmail: userEmail);
      if (!mounted) return;
      Navigator.of(
        context,
      ).pushReplacement(MaterialPageRoute(builder: (_) => const HomeScreen()));
    } catch (e) {
      if (!mounted) return;
      _showSnack(
        'Login failed: ${e.toString().replaceFirst('Exception: ', '')}',
      );
    } finally {
      if (mounted) setState(() => _isLoggingIn = false);
    }
  }

  void _showSnack(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;

    return Scaffold(
      backgroundColor: const Color(0xFF001540),
      resizeToAvoidBottomInset: true,
      body: Stack(
        children: [
          // ── Background ───────────────────────────────────────────────────
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

          // Decorative orbs
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
            bottom: size.height * 0.35,
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

          // ── Content ──────────────────────────────────────────────────────
          SafeArea(
            child: SingleChildScrollView(
              physics: const ClampingScrollPhysics(),
              child: ConstrainedBox(
                constraints: BoxConstraints(
                  minHeight:
                      size.height -
                      MediaQuery.of(context).padding.top -
                      MediaQuery.of(context).padding.bottom,
                ),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      // Logo section
                      Padding(
                        padding: const EdgeInsets.only(top: 48),
                        child: FadeTransition(
                          opacity: _logoFade,
                          child: ScaleTransition(
                            scale: _logoScale,
                            child: Column(
                              children: [
                                Container(
                                  width: 90,
                                  height: 90,
                                  decoration: BoxDecoration(
                                    shape: BoxShape.circle,
                                    color: Colors.white.withOpacity(0.07),
                                    border: Border.all(
                                      color: Colors.white.withOpacity(0.12),
                                      width: 1.5,
                                    ),
                                  ),
                                  child: Center(
                                    child: Image.asset(
                                      'assets/nu_shield.png',
                                      width: 56,
                                      height: 56,
                                      fit: BoxFit.contain,
                                      errorBuilder: (_, __, ___) => const Text(
                                        'NU',
                                        style: TextStyle(
                                          fontFamily: 'DM Sans',
                                          fontWeight: FontWeight.w900,
                                          fontSize: 26,
                                          color: Color(0xFFF59E0B),
                                        ),
                                      ),
                                    ),
                                  ),
                                ),
                                const SizedBox(height: 18),
                                RichText(
                                  text: const TextSpan(
                                    children: [
                                      TextSpan(
                                        text: 'NU',
                                        style: TextStyle(
                                          fontFamily: 'DM Sans',
                                          fontSize: 36,
                                          fontWeight: FontWeight.w900,
                                          color: Color(0xFFF59E0B),
                                          letterSpacing: 2.5,
                                        ),
                                      ),
                                      TextSpan(
                                        text: 'POST',
                                        style: TextStyle(
                                          fontFamily: 'DM Sans',
                                          fontSize: 36,
                                          fontWeight: FontWeight.w900,
                                          color: Colors.white,
                                          letterSpacing: 2.5,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                                const SizedBox(height: 6),
                                Text(
                                  'NU Lipa Marketing Office',
                                  style: TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontSize: 12,
                                    fontWeight: FontWeight.w500,
                                    color: Colors.white.withOpacity(0.4),
                                    letterSpacing: 0.8,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),

                      // Login card
                      Padding(
                        padding: const EdgeInsets.only(top: 40, bottom: 32),
                        child: FadeTransition(
                          opacity: _cardFade,
                          child: SlideTransition(
                            position: _cardSlide,
                            child: Container(
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
                              padding: const EdgeInsets.all(28),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  // Card header
                                  const Text(
                                    'Welcome back',
                                    style: TextStyle(
                                      fontFamily: 'DM Sans',
                                      fontWeight: FontWeight.w900,
                                      fontSize: 24,
                                      color: Color(0xFF080F1E),
                                      letterSpacing: -0.5,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    'Sign in to your account',
                                    style: TextStyle(
                                      fontFamily: 'DM Sans',
                                      fontWeight: FontWeight.w400,
                                      fontSize: 13,
                                      color: const Color(
                                        0xFF080F1E,
                                      ).withOpacity(0.45),
                                    ),
                                  ),

                                  const SizedBox(height: 28),

                                  // Email field
                                  _FieldLabel(text: 'Email Address'),
                                  const SizedBox(height: 8),
                                  _InputField(
                                    controller: _emailCtrl,
                                    hint: 'your.email@nu-lipa.edu.ph',
                                    keyboardType: TextInputType.emailAddress,
                                    prefixIcon: Icons.email_outlined,
                                  ),

                                  const SizedBox(height: 18),

                                  // Password field
                                  _FieldLabel(text: 'Password'),
                                  const SizedBox(height: 8),
                                  _InputField(
                                    controller: _passwordCtrl,
                                    hint: '••••••••',
                                    obscureText: _obscurePassword,
                                    prefixIcon: Icons.lock_outline_rounded,
                                    suffixIcon: GestureDetector(
                                      onTap: () => setState(
                                        () => _obscurePassword =
                                            !_obscurePassword,
                                      ),
                                      child: Icon(
                                        _obscurePassword
                                            ? Icons.visibility_off_outlined
                                            : Icons.visibility_outlined,
                                        size: 18,
                                        color: const Color(0xFF9AA3B2),
                                      ),
                                    ),
                                  ),

                                  const SizedBox(height: 30),

                                  // Login button
                                  GestureDetector(
                                    onTapDown: (_) => _btnCtrl.forward(),
                                    onTapUp: (_) {
                                      _btnCtrl.reverse();
                                      _onLogin();
                                    },
                                    onTapCancel: () => _btnCtrl.reverse(),
                                    child: ScaleTransition(
                                      scale: _btnScale,
                                      child: Container(
                                        width: double.infinity,
                                        height: 52,
                                        decoration: BoxDecoration(
                                          gradient: const LinearGradient(
                                            begin: Alignment.topLeft,
                                            end: Alignment.bottomRight,
                                            colors: [
                                              Color(0xFF001540),
                                              Color(0xFF0032A0),
                                            ],
                                          ),
                                          borderRadius: BorderRadius.circular(
                                            16,
                                          ),
                                          boxShadow: [
                                            BoxShadow(
                                              color: const Color(
                                                0xFF001540,
                                              ).withOpacity(0.38),
                                              blurRadius: 18,
                                              offset: const Offset(0, 7),
                                            ),
                                          ],
                                        ),
                                        child: Center(
                                          child: _isLoggingIn
                                              ? const SizedBox(
                                                  width: 20,
                                                  height: 20,
                                                  child: CircularProgressIndicator(
                                                    strokeWidth: 2,
                                                    valueColor:
                                                        AlwaysStoppedAnimation(
                                                          Colors.white,
                                                        ),
                                                  ),
                                                )
                                              : const Text(
                                                  'LOGIN',
                                                  style: TextStyle(
                                                    fontFamily: 'DM Sans',
                                                    fontWeight: FontWeight.w900,
                                                    fontSize: 14,
                                                    color: Colors.white,
                                                    letterSpacing: 1.2,
                                                  ),
                                                ),
                                        ),
                                      ),
                                    ),
                                  ),

                                  const SizedBox(height: 22),

                                  // Sign up link
                                  Center(
                                    child: RichText(
                                      text: TextSpan(
                                        style: const TextStyle(
                                          fontFamily: 'DM Sans',
                                          fontSize: 13,
                                          color: Color(0xFF9AA3B2),
                                          fontWeight: FontWeight.w400,
                                        ),
                                        children: [
                                          const TextSpan(
                                            text: "Don't have an account? ",
                                          ),
                                          WidgetSpan(
                                            child: GestureDetector(
                                              onTap: () =>
                                                  Navigator.of(context).push(
                                                    MaterialPageRoute(
                                                      builder: (_) =>
                                                          const RegisterScreen(),
                                                    ),
                                                  ),
                                              child: const Text(
                                                'Sign up',
                                                style: TextStyle(
                                                  fontFamily: 'DM Sans',
                                                  fontWeight: FontWeight.w800,
                                                  fontSize: 13,
                                                  color: Color(0xFF2B5CE6),
                                                ),
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Shared field label ────────────────────────────────────────────────────────
class _FieldLabel extends StatelessWidget {
  final String text;
  const _FieldLabel({required this.text});

  @override
  Widget build(BuildContext context) {
    return Text(
      text.toUpperCase(),
      style: const TextStyle(
        fontFamily: 'DM Sans',
        fontWeight: FontWeight.w800,
        fontSize: 10.5,
        color: Color(0xFF9AA3B2),
        letterSpacing: 0.9,
      ),
    );
  }
}

// ── Shared input field ────────────────────────────────────────────────────────
class _InputField extends StatelessWidget {
  final TextEditingController controller;
  final String hint;
  final bool obscureText;
  final TextInputType keyboardType;
  final IconData prefixIcon;
  final Widget? suffixIcon;

  const _InputField({
    required this.controller,
    required this.hint,
    this.obscureText = false,
    this.keyboardType = TextInputType.text,
    required this.prefixIcon,
    this.suffixIcon,
  });

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller,
      obscureText: obscureText,
      keyboardType: keyboardType,
      style: const TextStyle(
        fontFamily: 'DM Sans',
        fontSize: 14,
        fontWeight: FontWeight.w500,
        color: Color(0xFF080F1E),
      ),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: const TextStyle(
          fontFamily: 'DM Sans',
          fontSize: 13.5,
          color: Color(0xFFBFC5D0),
          fontWeight: FontWeight.w400,
        ),
        prefixIcon: Padding(
          padding: const EdgeInsets.only(left: 14, right: 10),
          child: Icon(prefixIcon, size: 18, color: const Color(0xFF9AA3B2)),
        ),
        prefixIconConstraints: const BoxConstraints(minWidth: 0, minHeight: 0),
        suffixIcon: suffixIcon != null
            ? Padding(
                padding: const EdgeInsets.only(right: 14),
                child: suffixIcon,
              )
            : null,
        filled: true,
        fillColor: const Color(0xFFF1F4FB),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(14),
          borderSide: const BorderSide(color: Color(0xFF2B5CE6), width: 1.5),
        ),
        contentPadding: const EdgeInsets.symmetric(
          horizontal: 16,
          vertical: 15,
        ),
      ),
    );
  }
}
