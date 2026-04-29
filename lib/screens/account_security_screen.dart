import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';

class AccountSecurityScreen extends StatefulWidget {
  const AccountSecurityScreen({super.key});

  @override
  State<AccountSecurityScreen> createState() => _AccountSecurityScreenState();
}

class _AccountSecurityScreenState extends State<AccountSecurityScreen>
    with TickerProviderStateMixin {
  // ── Controllers ────────────────────────────────────────────────────────────
  final _currentCtrl = TextEditingController();
  final _newCtrl = TextEditingController();
  final _confirmCtrl = TextEditingController();

  bool _obscureCurrent = true;
  bool _obscureNew = true;
  bool _obscureConfirm = true;
  bool _isLoading = false;

  // Settings
  bool _publicProfile = false;
  bool _emailNotif = true;
  bool _statusUpdates = true;

  // Password strength
  double _strength = 0.0;
  String _strengthLabel = '';
  Color _strengthColor = const Color(0xFF9AA3B2);

  // Entry animation
  late final AnimationController _entryCtrl;
  late final Animation<double> _entryFade;
  late final Animation<Offset> _entrySlide;

  // Button press animation
  late final AnimationController _btnCtrl;
  late final Animation<double> _btnScale;

  @override
  void initState() {
    super.initState();

    _entryCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 650),
    )..forward();

    _loadSettings();

    _entryFade = CurvedAnimation(
      parent: _entryCtrl,
      curve: const Interval(0.0, 0.7, curve: Curves.easeOut),
    );
    _entrySlide = Tween<Offset>(begin: const Offset(0, 0.04), end: Offset.zero)
        .animate(
          CurvedAnimation(
            parent: _entryCtrl,
            curve: const Interval(0.0, 0.8, curve: Curves.easeOutCubic),
          ),
        );

    _btnCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 130),
      lowerBound: 0.0,
      upperBound: 1.0,
    );
    _btnScale = Tween<double>(
      begin: 1.0,
      end: 0.95,
    ).animate(CurvedAnimation(parent: _btnCtrl, curve: Curves.easeOut));

    _newCtrl.addListener(_evaluateStrength);
  }

  @override
  void dispose() {
    _entryCtrl.dispose();
    _btnCtrl.dispose();
    _currentCtrl.dispose();
    _newCtrl.dispose();
    _confirmCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadSettings() async {
    try {
      final res = await ApiService.fetchProfile(
        userId: SessionStore.userId ?? 0,
      );
      if (res['success'] == true) {
        final data = res['data'] ?? {};
        if (mounted) {
          setState(() {
            _publicProfile = (data['public_profile'] == 1 || data['public_profile'] == true);
            _emailNotif = (data['email_notif'] == 1 || data['email_notif'] == true);
            _statusUpdates = (data['status_updates'] == 1 || data['status_updates'] == true);
          });
        }
      }
    } catch (_) {}
  }

  // ── Password strength evaluator ──────────────────────────────────────────
  void _evaluateStrength() {
    final p = _newCtrl.text;
    double score = 0;
    if (p.isEmpty) {
      setState(() {
        _strength = 0;
        _strengthLabel = '';
        _strengthColor = const Color(0xFF9AA3B2);
      });
      return;
    }
    if (p.length >= 8) score += 0.25;
    if (p.length >= 12) score += 0.15;
    if (p.contains(RegExp(r'[A-Z]'))) score += 0.2;
    if (p.contains(RegExp(r'[0-9]'))) score += 0.2;
    if (p.contains(RegExp(r'[!@#\$%^&*(),.?":{}|<>]'))) score += 0.2;

    String label;
    Color color;
    if (score < 0.35) {
      label = 'Weak';
      color = const Color(0xFFFF3B30);
    } else if (score < 0.65) {
      label = 'Fair';
      color = const Color(0xFFF59E0B);
    } else if (score < 0.85) {
      label = 'Good';
      color = const Color(0xFF05C46B);
    } else {
      label = 'Strong';
      color = const Color(0xFF002366);
    }

    setState(() {
      _strength = score.clamp(0.0, 1.0);
      _strengthLabel = label;
      _strengthColor = color;
    });
  }

  // ── Validation ────────────────────────────────────────────────────────────
  String? _validate() {
    if (_currentCtrl.text.trim().isEmpty) return 'Enter your current password.';
    if (_newCtrl.text.length < 8)
      return 'New password must be at least 8 characters.';
    if (_newCtrl.text != _confirmCtrl.text) return 'Passwords do not match.';
    return null;
  }

  // ── Submit ────────────────────────────────────────────────────────────────
  Future<void> _updatePassword() async {
    final error = _validate();
    if (error != null) {
      _showSnack(error, isError: true);
      return;
    }

    setState(() => _isLoading = true);
    try {
      final email = SessionStore.email ?? '';
      if (email.isEmpty) {
        _showSnack('Email not found in session.', isError: true);
        setState(() => _isLoading = false);
        return;
      }

      // 1. Request OTP
      final res = await ApiService.resendOtp(
        email: email,
        purpose: 'password_reset',
      );
      if (res['success'] != true) {
        _showSnack(res['message'] ?? 'Failed to send OTP', isError: true);
        setState(() => _isLoading = false);
        return;
      }

      setState(() => _isLoading = false);

      // 2. Show floating OTP dialog
      if (!mounted) return;
      _showOtpDialog(email);

    } catch (e) {
      if (!mounted) return;
      _showSnack(
        'Failed: ${e.toString().replaceFirst('Exception: ', '')}',
        isError: true,
      );
      setState(() => _isLoading = false);
    }
  }

  void _showOtpDialog(String email) {
    final otpCtrl = TextEditingController();
    bool isVerifying = false;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) {
          return AlertDialog(
            backgroundColor: Colors.white,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            title: const Text(
              'Security Verification',
              style: TextStyle(fontFamily: 'DM Sans', fontWeight: FontWeight.w800, fontSize: 18, color: Color(0xFF080F1E)),
            ),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text(
                  'To update your password, please enter the 6-digit OTP sent to your email.',
                  style: TextStyle(fontFamily: 'DM Sans', fontSize: 13.5, color: Color(0xFF3D4A63), height: 1.4),
                ),
                const SizedBox(height: 20),
                TextField(
                  controller: otpCtrl,
                  keyboardType: TextInputType.number,
                  maxLength: 6,
                  style: const TextStyle(fontFamily: 'DM Sans', fontWeight: FontWeight.w600, fontSize: 16, letterSpacing: 2.0),
                  textAlign: TextAlign.center,
                  decoration: InputDecoration(
                    hintText: '000000',
                    hintStyle: const TextStyle(letterSpacing: 2.0, color: Color(0xFFBFC5D0)),
                    filled: true,
                    fillColor: const Color(0xFFF1F4FB),
                    counterText: '',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide.none),
                    contentPadding: const EdgeInsets.symmetric(vertical: 16),
                  ),
                ),
              ],
            ),
            actions: [
              TextButton(
                onPressed: isVerifying ? null : () => Navigator.of(ctx).pop(),
                child: const Text(
                  'Cancel',
                  style: TextStyle(fontFamily: 'DM Sans', fontWeight: FontWeight.w700, color: Color(0xFF9AA3B2)),
                ),
              ),
              ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF001A6E),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                  elevation: 0,
                ),
                onPressed: isVerifying
                    ? null
                    : () async {
                        final otp = otpCtrl.text.trim();
                        if (otp.length != 6) return;

                        setDialogState(() => isVerifying = true);

                        try {
                          final verifyRes = await ApiService.verifyOtp(email: email, otp: otp);
                          if (verifyRes['success'] != true) {
                            setDialogState(() => isVerifying = false);
                            if (mounted) _showSnack(verifyRes['message'] ?? 'Invalid OTP', isError: true);
                            return;
                          }

                          // OTP valid, update password
                          final updateRes = await ApiService.updatePassword(
                            userId: SessionStore.userId ?? 0,
                            currentPassword: _currentCtrl.text,
                            newPassword: _newCtrl.text,
                          );

                          if (updateRes['success'] == true) {
                            if (mounted) {
                              Navigator.of(ctx).pop();
                              _currentCtrl.clear();
                              _newCtrl.clear();
                              _confirmCtrl.clear();
                              _showSnack('Password updated successfully!', isError: false);
                            }
                          } else {
                            setDialogState(() => isVerifying = false);
                            if (mounted) _showSnack(updateRes['message'] ?? 'Failed to update', isError: true);
                          }
                        } catch (e) {
                          setDialogState(() => isVerifying = false);
                          if (mounted) _showSnack('Error: $e', isError: true);
                        }
                      },
                child: isVerifying
                    ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Text(
                        'Confirm',
                        style: TextStyle(fontFamily: 'DM Sans', fontWeight: FontWeight.w700, fontSize: 14, color: Colors.white),
                      ),
              ),
            ],
          );
        },
      ),
    );
  }

  void _showSnack(String msg, {required bool isError}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(
              isError
                  ? Icons.error_outline_rounded
                  : Icons.check_circle_rounded,
              color: Colors.white,
              size: 18,
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                msg,
                style: const TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w600,
                  fontSize: 13,
                ),
              ),
            ),
          ],
        ),
        backgroundColor: isError
            ? const Color(0xFFFF3B30)
            : const Color(0xFF05C46B),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        margin: const EdgeInsets.fromLTRB(16, 0, 16, 16),
      ),
    );
  }

  // ── BUILD ─────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final topPad = MediaQuery.of(context).padding.top;

    return Scaffold(
      backgroundColor: const Color(0xFFE9EDF6),
      body: FadeTransition(
        opacity: _entryFade,
        child: SlideTransition(
          position: _entrySlide,
          child: Column(
            children: [
              // ── Header ───────────────────────────────────────────────────
              _buildHeader(topPad),

              // ── Body ─────────────────────────────────────────────────────
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(16, 20, 16, 40),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Shield info card
                      _buildInfoCard(),
                      const SizedBox(height: 22),

                      // Form card
                      _buildFormCard(),
                      const SizedBox(height: 20),

                      // Notification Preferences
                      _buildNotificationCard(),
                      const SizedBox(height: 20),

                      // Privacy Settings
                      _buildPrivacyCard(),
                      const SizedBox(height: 20),

                      // Submit button (Password only)
                      _buildSubmitButton(),

                      const SizedBox(height: 24),

                      // Security tips
                      _buildTipsCard(),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ── Header ────────────────────────────────────────────────────────────────
  Widget _buildHeader(double topPad) {
    return Container(
      width: double.infinity,
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(bottom: BorderSide(color: Color(0x0F000000), width: 1)),
        boxShadow: [
          BoxShadow(
            color: Color(0x07001540),
            blurRadius: 12,
            offset: Offset(0, 1),
          ),
        ],
      ),
      padding: EdgeInsets.fromLTRB(8, topPad + 10, 16, 14),
      child: Row(
        children: [
          IconButton(
            onPressed: () => Navigator.of(context).pop(),
            icon: const Icon(
              Icons.arrow_back_ios_new_rounded,
              color: Color(0xFF002366),
              size: 18,
            ),
          ),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: const [
                Text(
                  'Account Security',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w900,
                    fontSize: 20,
                    color: Color(0xFF080F1E),
                    letterSpacing: -0.4,
                  ),
                ),
                Text(
                  'Manage your password and security',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 12,
                    color: Color(0xFF9AA3B2),
                  ),
                ),
              ],
            ),
          ),
          // Lock icon badge
          Container(
            width: 38,
            height: 38,
            decoration: BoxDecoration(
              color: const Color(0xFF002366).withOpacity(0.07),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(
              Icons.lock_rounded,
              size: 18,
              color: Color(0xFF002366),
            ),
          ),
        ],
      ),
    );
  }

  // ── Info card ─────────────────────────────────────────────────────────────
  Widget _buildInfoCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF001540), Color(0xFF0032A0), Color(0xFF1A4FCC)],
          stops: [0.0, 0.5, 1.0],
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: const [
          BoxShadow(
            color: Color(0x40001540),
            blurRadius: 20,
            offset: Offset(0, 8),
          ),
        ],
      ),
      child: Stack(
        children: [
          Positioned(
            top: -20,
            right: -20,
            child: Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withOpacity(0.05),
              ),
            ),
          ),
          Row(
            children: [
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.15),
                  border: Border.all(
                    color: Colors.white.withOpacity(0.2),
                    width: 1.5,
                  ),
                  borderRadius: BorderRadius.circular(15),
                ),
                child: const Icon(
                  Icons.shield_rounded,
                  color: Colors.white,
                  size: 22,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Change Password',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w900,
                        fontSize: 16,
                        color: Colors.white,
                        letterSpacing: -0.3,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      'Keep your account safe with a strong password',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontSize: 12,
                        color: Colors.white.withOpacity(0.6),
                        height: 1.4,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ── Form card ─────────────────────────────────────────────────────────────
  Widget _buildFormCard() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0x0E000000)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x07001540),
            blurRadius: 12,
            offset: Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Current password
          _FieldLabel(text: 'Current Password'),
          const SizedBox(height: 8),
          _PasswordField(
            controller: _currentCtrl,
            hint: 'Enter your current password',
            obscure: _obscureCurrent,
            onToggle: () => setState(() => _obscureCurrent = !_obscureCurrent),
            prefixIcon: Icons.lock_outline_rounded,
          ),

          const SizedBox(height: 18),
          _Divider(),
          const SizedBox(height: 18),

          // New password
          _FieldLabel(text: 'New Password'),
          const SizedBox(height: 8),
          _PasswordField(
            controller: _newCtrl,
            hint: 'Enter a new password',
            obscure: _obscureNew,
            onToggle: () => setState(() => _obscureNew = !_obscureNew),
            prefixIcon: Icons.vpn_key_rounded,
          ),

          // Strength indicator
          if (_newCtrl.text.isNotEmpty) ...[
            const SizedBox(height: 10),
            _buildStrengthIndicator(),
          ],

          const SizedBox(height: 18),

          // Confirm password
          _FieldLabel(text: 'Confirm New Password'),
          const SizedBox(height: 8),
          _PasswordField(
            controller: _confirmCtrl,
            hint: 'Re-enter your new password',
            obscure: _obscureConfirm,
            onToggle: () => setState(() => _obscureConfirm = !_obscureConfirm),
            prefixIcon: Icons.lock_reset_rounded,
            // Show match indicator
            suffix: _confirmCtrl.text.isNotEmpty
                ? Icon(
                    _confirmCtrl.text == _newCtrl.text
                        ? Icons.check_circle_rounded
                        : Icons.cancel_rounded,
                    size: 18,
                    color: _confirmCtrl.text == _newCtrl.text
                        ? const Color(0xFF05C46B)
                        : const Color(0xFFFF3B30),
                  )
                : null,
          ),
        ],
      ),
    );
  }

  // ── Strength bar ──────────────────────────────────────────────────────────
  Widget _buildStrengthIndicator() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'Password strength',
              style: const TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 10.5,
                fontWeight: FontWeight.w600,
                color: Color(0xFF9AA3B2),
              ),
            ),
            Text(
              _strengthLabel,
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 10.5,
                fontWeight: FontWeight.w800,
                color: _strengthColor,
              ),
            ),
          ],
        ),
        const SizedBox(height: 6),
        ClipRRect(
          borderRadius: BorderRadius.circular(99),
          child: TweenAnimationBuilder<double>(
            tween: Tween(begin: 0, end: _strength),
            duration: const Duration(milliseconds: 400),
            curve: Curves.easeOutCubic,
            builder: (_, val, __) => Stack(
              children: [
                Container(height: 5, color: const Color(0xFFE9EDF6)),
                FractionallySizedBox(
                  widthFactor: val,
                  child: Container(
                    height: 5,
                    decoration: BoxDecoration(
                      color: _strengthColor,
                      borderRadius: BorderRadius.circular(99),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  // ── Submit button ─────────────────────────────────────────────────────────
  Widget _buildSubmitButton() {
    return GestureDetector(
      onTapDown: (_) {
        if (!_isLoading) _btnCtrl.forward();
      },
      onTapUp: (_) {
        _btnCtrl.reverse();
        _updatePassword();
      },
      onTapCancel: () => _btnCtrl.reverse(),
      child: ScaleTransition(
        scale: _btnScale,
        child: Container(
          width: double.infinity,
          height: 54,
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Color(0xFF001540), Color(0xFF0032A0)],
            ),
            borderRadius: BorderRadius.circular(18),
            boxShadow: const [
              BoxShadow(
                color: Color(0x45001540),
                blurRadius: 18,
                offset: Offset(0, 7),
              ),
            ],
          ),
          child: Center(
            child: _isLoading
                ? const SizedBox(
                    width: 22,
                    height: 22,
                    child: CircularProgressIndicator(
                      strokeWidth: 2.5,
                      valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                    ),
                  )
                : const Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(Icons.lock_rounded, color: Colors.white, size: 18),
                      SizedBox(width: 9),
                      Text(
                        'Update Password',
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w900,
                          fontSize: 15.5,
                          color: Colors.white,
                          letterSpacing: 0.1,
                        ),
                      ),
                    ],
                  ),
          ),
        ),
      ),
    );
  }

  // ── Notification Preferences ───────────────────────────────────────────────
  Widget _buildNotificationCard() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0x0E000000)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x07001540),
            blurRadius: 12,
            offset: Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF3C7),
                  borderRadius: BorderRadius.circular(9),
                ),
                child: const Icon(
                  Icons.notifications_active_rounded,
                  size: 16,
                  color: Color(0xFFD97706),
                ),
              ),
              const SizedBox(width: 12),
              const Text(
                'NOTIFICATION PREFERENCES',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w800,
                  fontSize: 10.5,
                  color: Color(0xFF9AA3B2),
                  letterSpacing: 0.9,
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          _buildToggleRow(
            title: 'Email Notifications',
            desc: 'Receive all notifications via email when enabled',
            value: _emailNotif,
            onChanged: (val) async {
              setState(() => _emailNotif = val);
              if (!val) setState(() => _statusUpdates = false);
              await _saveNotificationSettings();
            },
          ),
          const SizedBox(height: 12),
          _Divider(),
          const SizedBox(height: 12),
          Opacity(
            opacity: _emailNotif ? 1.0 : 0.5,
            child: _buildToggleRow(
              title: 'Request Status Updates',
              desc: 'Get an email when your request is approved, posted, or rejected',
              value: _statusUpdates,
              onChanged: _emailNotif
                  ? (val) async {
                      setState(() => _statusUpdates = val);
                      await _saveNotificationSettings();
                    }
                  : null,
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _saveNotificationSettings() async {
    try {
      await ApiService.updateNotificationSettings(
        userId: SessionStore.userId ?? 0,
        emailNotif: _emailNotif,
        statusUpdates: _statusUpdates,
      );
    } catch (e) {
      _showSnack('Failed to update notifications.', isError: true);
    }
  }

  // ── Privacy Settings ───────────────────────────────────────────────────────
  Widget _buildPrivacyCard() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0x0E000000)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x07001540),
            blurRadius: 12,
            offset: Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: const Color(0xFFDCFCE7),
                  borderRadius: BorderRadius.circular(9),
                ),
                child: const Icon(
                  Icons.shield_rounded,
                  size: 16,
                  color: Color(0xFF16A34A),
                ),
              ),
              const SizedBox(width: 12),
              const Text(
                'PRIVACY SETTINGS',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w800,
                  fontSize: 10.5,
                  color: Color(0xFF9AA3B2),
                  letterSpacing: 0.9,
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          _buildToggleRow(
            title: 'Public Profile',
            desc: 'Make your profile visible to all users in NUPost',
            value: _publicProfile,
            onChanged: (val) async {
              setState(() => _publicProfile = val);
              try {
                await ApiService.updatePublicProfile(
                  userId: SessionStore.userId ?? 0,
                  isPublic: val,
                );
              } catch (e) {
                _showSnack('Failed to update privacy.', isError: true);
              }
            },
          ),
        ],
      ),
    );
  }

  Widget _buildToggleRow({
    required String title,
    required String desc,
    required bool value,
    required ValueChanged<bool>? onChanged,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w700,
                  fontSize: 14.5,
                  color: Color(0xFF080F1E),
                ),
              ),
              const SizedBox(height: 3),
              Text(
                desc,
                style: const TextStyle(
                  fontFamily: 'DM Sans',
                  fontSize: 12.5,
                  color: Color(0xFF3D4A63),
                  height: 1.4,
                ),
              ),
            ],
          ),
        ),
        const SizedBox(width: 16),
        Switch(
          value: value,
          onChanged: onChanged,
          activeColor: Colors.white,
          activeTrackColor: const Color(0xFF001A6E),
          inactiveThumbColor: Colors.white,
          inactiveTrackColor: const Color(0xFFD1D5DB),
        ),
      ],
    );
  }

  // ── Tips card ─────────────────────────────────────────────────────────────
  Widget _buildTipsCard() {
    const tips = [
      ('Use at least 8 characters', Icons.check_circle_outline_rounded),
      (
        'Mix uppercase, lowercase & numbers',
        Icons.check_circle_outline_rounded,
      ),
      ('Add special characters (!@#\$)', Icons.check_circle_outline_rounded),
      ('Avoid using your name or birthdate', Icons.info_outline_rounded),
    ];

    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFFF0F5FF),
        border: Border.all(color: const Color(0xFF2B5CE6).withOpacity(0.15)),
        borderRadius: BorderRadius.circular(18),
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 30,
                height: 30,
                decoration: BoxDecoration(
                  color: const Color(0xFF2B5CE6).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  Icons.tips_and_updates_rounded,
                  size: 16,
                  color: Color(0xFF2B5CE6),
                ),
              ),
              const SizedBox(width: 10),
              const Text(
                'Password Tips',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w800,
                  fontSize: 13,
                  color: Color(0xFF080F1E),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          ...tips.map(
            (tip) => Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(tip.$2, size: 14, color: const Color(0xFF2B5CE6)),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      tip.$1,
                      style: const TextStyle(
                        fontFamily: 'DM Sans',
                        fontSize: 12.5,
                        color: Color(0xFF3D4A63),
                        height: 1.4,
                      ),
                    ),
                  ),
                ],
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

// ── Password field ────────────────────────────────────────────────────────────
class _PasswordField extends StatelessWidget {
  final TextEditingController controller;
  final String hint;
  final bool obscure;
  final VoidCallback onToggle;
  final IconData prefixIcon;
  final Widget? suffix;

  const _PasswordField({
    required this.controller,
    required this.hint,
    required this.obscure,
    required this.onToggle,
    required this.prefixIcon,
    this.suffix,
  });

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller,
      obscureText: obscure,
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
        suffixIcon: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (suffix != null) ...[suffix!, const SizedBox(width: 4)],
            GestureDetector(
              onTap: onToggle,
              child: Padding(
                padding: const EdgeInsets.only(right: 14),
                child: Icon(
                  obscure
                      ? Icons.visibility_off_outlined
                      : Icons.visibility_outlined,
                  size: 18,
                  color: const Color(0xFF9AA3B2),
                ),
              ),
            ),
          ],
        ),
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

// ── Divider ───────────────────────────────────────────────────────────────────
class _Divider extends StatelessWidget {
  @override
  Widget build(BuildContext context) =>
      Container(height: 1, color: const Color(0x08000000));
}
