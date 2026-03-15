import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';
import 'home_screen.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _fullNameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _obscurePassword = true;
  bool _obscureConfirm = true;
  bool _isSubmitting = false;

  @override
  void dispose() {
    _fullNameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _onCreateAccount() async {
    if (_isSubmitting) return;

    final fullName = _fullNameController.text.trim();
    final email = _emailController.text.trim();
    final password = _passwordController.text;
    final confirmPassword = _confirmPasswordController.text;

    if (fullName.isEmpty ||
        email.isEmpty ||
        password.isEmpty ||
        confirmPassword.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please fill in all fields.')),
      );
      return;
    }

    if (password != confirmPassword) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Passwords do not match.')));
      return;
    }

    setState(() => _isSubmitting = true);
    try {
      final result = await ApiService.register(
        name: fullName,
        email: email,
        password: password,
      );

      final data = (result['data'] as Map<String, dynamic>?) ?? {};
      final id = (data['id'] as num?)?.toInt();
      if (id == null) {
        throw Exception('Invalid registration response');
      }

      SessionStore.setUser(
        id: id,
        userName: (data['name'] ?? fullName).toString(),
        userEmail: (data['email'] ?? email).toString(),
      );

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Account created successfully.')),
      );

      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const HomeScreen()),
        (_) => false,
      );
    } catch (e) {
      if (!mounted) return;
      final msg = e.toString().replaceFirst('Exception: ', '');
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text('Registration failed: $msg')));
    } finally {
      if (mounted) {
        setState(() => _isSubmitting = false);
      }
    }
  }

  // Reusable input field decoration
  InputDecoration _inputDecoration({String? hint, bool isDots = false}) {
    return InputDecoration(
      hintText: hint,
      hintStyle: TextStyle(
        fontFamily: isDots ? 'Arimo' : 'Inter',
        fontWeight: isDots ? FontWeight.w400 : FontWeight.w100,
        fontSize: isDots ? 16 : 12,
        color: isDots ? const Color(0x800A0A0A) : Colors.black,
        letterSpacing: isDots ? 4 : 0,
      ),
      contentPadding: const EdgeInsets.symmetric(horizontal: 13, vertical: 13),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(5),
        borderSide: BorderSide(color: Colors.black.withOpacity(0.5), width: 1),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(5),
        borderSide: const BorderSide(color: Color(0xFF002366), width: 1.5),
      ),
    );
  }

  // Reusable field label
  Widget _label(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Text(
        text,
        style: const TextStyle(
          fontFamily: 'Inter',
          fontWeight: FontWeight.w300,
          fontSize: 12,
          color: Colors.black,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          // ── Background image ──────────────────────────────
          Positioned.fill(
            child: Image.asset(
              'assets/bg.png',
              fit: BoxFit.cover,
              errorBuilder: (_, __, ___) =>
                  Container(color: const Color(0xFF29286A)),
            ),
          ),

          // ── White card ───────────────────────────────────
          // Figma: Rectangle 3 — 357×787, left:36 top:72, radius:20
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.symmetric(
                  horizontal: 36,
                  vertical: 20,
                ),
                child: Container(
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  padding: const EdgeInsets.fromLTRB(27, 28, 27, 32),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // ── NU Shield logo (top right of card) ──
                      // Figma: image 1 — 160×106, positioned right side
                      Align(
                        alignment: Alignment.centerRight,
                        child: Image.asset(
                          'assets/nu_shield.png',
                          width: 80,
                          height: 53,
                          fit: BoxFit.contain,
                          errorBuilder: (_, __, ___) => const Icon(
                            Icons.shield,
                            size: 50,
                            color: Color(0xFF29286A),
                          ),
                        ),
                      ),

                      const SizedBox(height: 8),

                      // ── "Sign up to" heading ─────────────
                      // Figma: font-size:20 weight:400
                      const Text(
                        'Sign up to',
                        style: TextStyle(
                          fontFamily: 'Inter',
                          fontWeight: FontWeight.w400,
                          fontSize: 20,
                          color: Colors.black,
                        ),
                      ),

                      const SizedBox(height: 20),

                      // ── FULL NAME ────────────────────────
                      _label('FULL NAME'),
                      TextField(
                        controller: _fullNameController,
                        style: const TextStyle(
                          fontFamily: 'Inter',
                          fontWeight: FontWeight.w100,
                          fontSize: 12,
                          color: Colors.black,
                        ),
                        decoration: _inputDecoration(hint: 'Juan Dela Cruz'),
                      ),

                      const SizedBox(height: 16),

                      // ── EMAIL ADDRESS ────────────────────
                      _label('EMAIL ADDRESS'),
                      TextField(
                        controller: _emailController,
                        keyboardType: TextInputType.emailAddress,
                        style: const TextStyle(
                          fontFamily: 'Inter',
                          fontWeight: FontWeight.w100,
                          fontSize: 12,
                          color: Colors.black,
                        ),
                        decoration: _inputDecoration(
                          hint: 'your.email@nu-lipa.edu.ph',
                        ),
                      ),

                      const SizedBox(height: 16),

                      // ── PASSWORD ─────────────────────────
                      _label('PASSWORD'),
                      TextField(
                        controller: _passwordController,
                        obscureText: _obscurePassword,
                        style: const TextStyle(
                          fontFamily: 'Inter',
                          fontSize: 16,
                          color: Color(0x800A0A0A),
                          letterSpacing: 4,
                        ),
                        decoration:
                            _inputDecoration(
                              hint: '••••••••',
                              isDots: true,
                            ).copyWith(
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

                      const SizedBox(height: 16),

                      // ── CONFIRM PASSWORD ─────────────────
                      _label('CONFIRM PASSWORD'),
                      TextField(
                        controller: _confirmPasswordController,
                        obscureText: _obscureConfirm,
                        style: const TextStyle(
                          fontFamily: 'Inter',
                          fontSize: 16,
                          color: Color(0x800A0A0A),
                          letterSpacing: 4,
                        ),
                        decoration:
                            _inputDecoration(
                              hint: '••••••••',
                              isDots: true,
                            ).copyWith(
                              suffixIcon: IconButton(
                                icon: Icon(
                                  _obscureConfirm
                                      ? Icons.visibility_off_outlined
                                      : Icons.visibility_outlined,
                                  size: 18,
                                  color: Colors.black38,
                                ),
                                onPressed: () => setState(
                                  () => _obscureConfirm = !_obscureConfirm,
                                ),
                              ),
                            ),
                      ),

                      const SizedBox(height: 28),

                      // ── CREATE ACCOUNT button ────────────
                      // Figma: Rectangle 2 — #002366, 304×39, radius:5
                      SizedBox(
                        width: double.infinity,
                        height: 39,
                        child: ElevatedButton(
                          onPressed: _isSubmitting ? null : _onCreateAccount,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF002366),
                            foregroundColor: Colors.white,
                            elevation: 0,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(5),
                            ),
                          ),
                          child: _isSubmitting
                              ? const SizedBox(
                                  width: 18,
                                  height: 18,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    valueColor: AlwaysStoppedAnimation<Color>(
                                      Colors.white,
                                    ),
                                  ),
                                )
                              : const Text(
                                  'CREATE ACCOUNT',
                                  style: TextStyle(
                                    fontFamily: 'Inter',
                                    fontWeight: FontWeight.w700,
                                    fontSize: 12,
                                    color: Colors.white,
                                    letterSpacing: 0.5,
                                  ),
                                ),
                        ),
                      ),

                      const SizedBox(height: 20),

                      // ── Already have an account? Log in ──
                      Center(
                        child: RichText(
                          text: TextSpan(
                            style: const TextStyle(
                              fontFamily: 'Inter',
                              fontWeight: FontWeight.w300,
                              fontSize: 12,
                              color: Colors.black,
                            ),
                            children: [
                              const TextSpan(text: 'Already have an account? '),
                              WidgetSpan(
                                child: GestureDetector(
                                  onTap: () {
                                    Navigator.of(context).pop();
                                  },
                                  child: const Text(
                                    'Log in',
                                    style: TextStyle(
                                      fontFamily: 'Inter',
                                      fontWeight: FontWeight.w300,
                                      fontSize: 12,
                                      color: Color(0xFF007AFF),
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
    );
  }
}
