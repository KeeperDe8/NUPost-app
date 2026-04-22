import 'package:flutter/material.dart';

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
    with SingleTickerProviderStateMixin {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;
  bool _isLoggingIn = false;

  late final AnimationController _btnScale = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 150),
    lowerBound: 0.0,
    upperBound: 1.0,
  );

  @override
  void dispose() {
    _btnScale.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _onLogin() async {
    if (_isLoggingIn) return;

    final email = _emailController.text.trim();
    final password = _passwordController.text;

    if (email.isEmpty || password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter email and password.')),
      );
      return;
    }

    setState(() => _isLoggingIn = true);
    try {
      final result = await ApiService.login(email: email, password: password);
      final data = (result['data'] as Map<String, dynamic>?) ?? {};
      final userId = (data['id'] as num?)?.toInt();
      final name = (data['name'] ?? '').toString();
      final userEmail = (data['email'] ?? email).toString();

      if (userId == null) {
        throw Exception('Invalid login response');
      }

      SessionStore.setUser(id: userId, userName: name, userEmail: userEmail);
      if (!mounted) return;
      Navigator.of(
        context,
      ).pushReplacement(MaterialPageRoute(builder: (_) => const HomeScreen()));
    } catch (e) {
      if (!mounted) return;
      final msg = e.toString().replaceFirst('Exception: ', '');
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text('Login failed: $msg')));
    } finally {
      if (mounted) {
        setState(() => _isLoggingIn = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.primaryDark,
      body: Stack(
        children: [
          Positioned.fill(
            child: Image.asset(
              'assets/bg.png',
              fit: BoxFit.cover,
              errorBuilder: (_, __, ___) =>
                  Container(color: AppColors.primaryDark),
            ),
          ),
          SafeArea(
            child: Align(
              alignment: Alignment.topCenter,
              child: Padding(
                padding: const EdgeInsets.only(top: 32),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Image.asset(
                      'assets/nu_shield.png',
                      width: 90,
                      height: 60,
                      fit: BoxFit.contain,
                      errorBuilder: (_, __, ___) => const Icon(
                        Icons.shield,
                        size: 60,
                        color: Color(0xFF29286A),
                      ),
                    ),
                    const SizedBox(height: 8),
                    RichText(
                      text: const TextSpan(
                        children: [
                          TextSpan(
                            text: 'NU',
                            style: TextStyle(fontFamily: 'DM Sans', 
                              fontSize: 28,
                              fontWeight: FontWeight.w900,
                              color: AppColors.gold,
                              letterSpacing: 1.5,
                            ),
                          ),
                          TextSpan(
                            text: 'POST',
                            style: TextStyle(fontFamily: 'DM Sans', 
                              fontSize: 28,
                              fontWeight: FontWeight.w900,
                              color: Colors.white,
                              letterSpacing: 1.5,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          Align(
            alignment: Alignment.bottomCenter,
            child: Container(
              width: double.infinity,
              margin: const EdgeInsets.symmetric(horizontal: 22),
              padding: const EdgeInsets.only(bottom: 40),
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(AppRadius.lg),
                  boxShadow: const [
                    BoxShadow(
                      color: Color(0x14000000),
                      blurRadius: 24,
                      offset: Offset(0, 12),
                    ),
                  ],
                ),
                padding: const EdgeInsets.fromLTRB(21, 36, 21, 32),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'EMAIL ADDRESS:',
                      style: TextStyle(fontFamily: 'DM Sans', 
                        fontWeight: FontWeight.w300,
                        fontSize: 12,
                        color: Colors.black,
                        letterSpacing: 0.3,
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextField(
                      controller: _emailController,
                      keyboardType: TextInputType.emailAddress,
                      style: const TextStyle(fontFamily: 'DM Sans', 
                        fontWeight: FontWeight.w100,
                        fontSize: 12,
                        color: Colors.black,
                      ),
                      decoration: _fieldDecoration(
                        hintText: 'your.email@nu-lipa.edu.ph',
                      ),
                    ),
                    const SizedBox(height: 20),
                    const Text(
                      'PASSWORD:',
                      style: TextStyle(fontFamily: 'DM Sans', 
                        fontWeight: FontWeight.w300,
                        fontSize: 12,
                        color: Colors.black,
                        letterSpacing: 0.3,
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextField(
                      controller: _passwordController,
                      obscureText: _obscurePassword,
                      style: const TextStyle(fontFamily: 'DM Sans', 
                        fontSize: 16,
                        color: Color(0x800A0A0A),
                        letterSpacing: 4,
                      ),
                      decoration: _fieldDecoration(
                        hintText: '••••••••',
                        suffixIcon: IconButton(
                          icon: Icon(
                            _obscurePassword
                                ? Icons.visibility_off_outlined
                                : Icons.visibility_outlined,
                            size: 18,
                            color: Colors.black38,
                          ),
                          onPressed: () => setState(
                            () => _obscurePassword = !_obscurePassword,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 28),
                    Center(
                      child: GestureDetector(
                        onTapDown: (_) => _btnScale.forward(),
                        onTapUp: (_) => _btnScale.reverse(),
                        onTapCancel: () => _btnScale.reverse(),
                        child: ScaleTransition(
                          scale: Tween<double>(begin: 1.0, end: 0.95).animate(
                            CurvedAnimation(
                              parent: _btnScale,
                              curve: Curves.easeOut,
                            ),
                          ),
                          child: SizedBox(
                            width: 140,
                            height: 44,
                            child: ElevatedButton(
                              onPressed: _isLoggingIn ? null : _onLogin,
                              style: ElevatedButton.styleFrom(
                                backgroundColor: AppColors.primary,
                                foregroundColor: Colors.white,
                                elevation: 0,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(AppRadius.md),
                                ),
                                padding: EdgeInsets.zero,
                              ),
                              child: _isLoggingIn
                                  ? const SizedBox(
                                      width: 16,
                                      height: 16,
                                      child: CircularProgressIndicator(
                                        strokeWidth: 2,
                                        valueColor:
                                            AlwaysStoppedAnimation<Color>(
                                              Colors.white,
                                            ),
                                      ),
                                    )
                                  : const Text(
                                      'LOGIN',
                                      style: TextStyle(
                                        fontFamily: 'DM Sans',
                                        fontWeight: FontWeight.w700,
                                        fontSize: 13,
                                        color: Colors.white,
                                        letterSpacing: 0.5,
                                      ),
                                    ),
                            ),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),
                    Center(
                      child: RichText(
                        text: TextSpan(
                          style: const TextStyle(fontFamily: 'DM Sans', 
                            fontWeight: FontWeight.w300,
                            fontSize: 12,
                            color: Colors.black,
                          ),
                          children: [
                            const TextSpan(text: "Don't have an account? "),
                            WidgetSpan(
                              child: GestureDetector(
                                onTap: () {
                                  Navigator.of(context).push(
                                    MaterialPageRoute(
                                      builder: (_) => const RegisterScreen(),
                                    ),
                                  );
                                },
                                child: const Text(
                                  'Sign up',
                                  style: TextStyle(fontFamily: 'DM Sans', 
                                    fontWeight: FontWeight.w300,
                                    fontSize: 12,
                                    color: AppColors.accent,
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
        ],
      ),
    );
  }

  InputDecoration _fieldDecoration({
    required String hintText,
    Widget? suffixIcon,
  }) {
    return InputDecoration(
      hintText: hintText,
      hintStyle: const TextStyle(fontFamily: 'DM Sans', 
        fontWeight: FontWeight.w100,
        fontSize: 12,
        color: Colors.black,
      ),
      contentPadding: const EdgeInsets.symmetric(horizontal: 15, vertical: 14),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(AppRadius.md),
        borderSide: const BorderSide(color: AppColors.border, width: 1),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(AppRadius.md),
        borderSide: const BorderSide(color: AppColors.accent, width: 1.5),
      ),
      suffixIcon: suffixIcon,
    );
  }
}

