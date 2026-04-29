import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';

class EditProfileScreen extends StatefulWidget {
  const EditProfileScreen({super.key});

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen>
    with SingleTickerProviderStateMixin {
  final _formKey = GlobalKey<FormState>();
  bool _isLoading = false;

  late TextEditingController _nameCtrl;
  late TextEditingController _emailCtrl;
  late TextEditingController _phoneCtrl;
  late TextEditingController _orgCtrl;
  late TextEditingController _deptCtrl;
  late TextEditingController _bioCtrl;

  late final AnimationController _entryCtrl;
  late final Animation<double> _entryFade;
  late final Animation<Offset> _entrySlide;

  // Which field is currently focused — for highlight effect
  String? _focusedField;

  String get _initials {
    final name = _nameCtrl.text.trim();
    final parts = name
        .split(RegExp(r'\s+'))
        .where((e) => e.isNotEmpty)
        .toList();
    if (parts.isEmpty) return 'NP';
    if (parts.length == 1) return parts[0][0].toUpperCase();
    return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
  }

  @override
  void initState() {
    super.initState();
    _nameCtrl = TextEditingController(text: SessionStore.name);
    _emailCtrl = TextEditingController(text: SessionStore.email);
    _phoneCtrl = TextEditingController();
    _orgCtrl = TextEditingController();
    _deptCtrl = TextEditingController();
    _bioCtrl = TextEditingController();

    _entryCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 650),
    )..forward();
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

    _loadProfileDetails();
  }

  @override
  void dispose() {
    _entryCtrl.dispose();
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _phoneCtrl.dispose();
    _orgCtrl.dispose();
    _deptCtrl.dispose();
    _bioCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadProfileDetails() async {
    setState(() => _isLoading = true);
    try {
      final res = await ApiService.fetchProfile(
        userId: SessionStore.userId ?? 0,
      );
      if (res['success'] == true) {
        final data = res['data'] ?? {};
        setState(() {
          _nameCtrl.text = data['name'] ?? SessionStore.name ?? '';
          _emailCtrl.text = data['email'] ?? SessionStore.email ?? '';
          _phoneCtrl.text = data['phone'] ?? '';
          _orgCtrl.text = data['organization'] ?? '';
          _deptCtrl.text = data['department'] ?? '';
          _bioCtrl.text = data['bio'] ?? '';
        });
      }
    } catch (_) {
      // silent
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _saveChanges() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);
    try {
      final res = await ApiService.updateProfile(
        userId: SessionStore.userId ?? 0,
        name: _nameCtrl.text.trim(),
        email: _emailCtrl.text.trim(),
        phone: _phoneCtrl.text.trim(),
        bio: _bioCtrl.text.trim(),
        organization: _orgCtrl.text.trim(),
        department: _deptCtrl.text.trim(),
      );
      if (res['success'] == true) {
        SessionStore.name = _nameCtrl.text.trim();
        SessionStore.email = _emailCtrl.text.trim();
        if (mounted) {
          _showSnack('Profile updated successfully!', success: true);
          Navigator.pop(context, true);
        }
      } else {
        if (mounted) _showSnack(res['message'] ?? 'Update failed.');
      }
    } catch (e) {
      if (mounted) _showSnack('Error: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _showSnack(String msg, {bool success = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            Icon(
              success ? Icons.check_circle_rounded : Icons.info_rounded,
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
                ),
              ),
            ),
          ],
        ),
        backgroundColor: success
            ? const Color(0xFF05C46B)
            : const Color(0xFF3D4A63),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        margin: const EdgeInsets.all(16),
      ),
    );
  }

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
              // ── Header ─────────────────────────────────────────────────
              _buildHeader(topPad),

              // ── Content ─────────────────────────────────────────────────
              Expanded(
                child: _isLoading
                    ? const Center(
                        child: CircularProgressIndicator(
                          color: Color(0xFF002366),
                        ),
                      )
                    : SingleChildScrollView(
                        padding: const EdgeInsets.fromLTRB(16, 0, 16, 40),
                        child: Form(
                          key: _formKey,
                          child: Column(
                            children: [
                              // Avatar card
                              _buildAvatarCard(),
                              const SizedBox(height: 20),

                              // Form card
                              _buildFormCard(),
                              const SizedBox(height: 20),

                              // Save button
                              _buildSaveButton(),
                            ],
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

  // ── Header ──────────────────────────────────────────────────────────────
  Widget _buildHeader(double topPad) {
    return Container(
      width: double.infinity,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF001540), Color(0xFF002878), Color(0xFF1243B0)],
          stops: [0.0, 0.5, 1.0],
        ),
        borderRadius: BorderRadius.only(
          bottomLeft: Radius.circular(32),
          bottomRight: Radius.circular(32),
        ),
      ),
      child: Stack(
        children: [
          // Decorative orb
          Positioned(
            top: -40,
            right: -40,
            child: Container(
              width: 180,
              height: 180,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withOpacity(0.05),
              ),
            ),
          ),
          Padding(
            padding: EdgeInsets.fromLTRB(8, topPad + 8, 16, 20),
            child: Row(
              children: [
                // Back button
                IconButton(
                  onPressed: () => Navigator.of(context).pop(),
                  icon: const Icon(
                    Icons.arrow_back_ios_new_rounded,
                    color: Colors.white,
                    size: 18,
                  ),
                ),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Edit Profile',
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w900,
                          fontSize: 22,
                          color: Colors.white,
                          letterSpacing: -0.4,
                        ),
                      ),
                      SizedBox(height: 2),
                      Text(
                        'Update your personal information',
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontSize: 12.5,
                          color: Color(0x80FFFFFF),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ── Avatar Card ──────────────────────────────────────────────────────────
  Widget _buildAvatarCard() {
    return Container(
      margin: const EdgeInsets.only(top: 20),
      padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0x0E000000)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x0A001540),
            blurRadius: 14,
            offset: Offset(0, 5),
          ),
        ],
      ),
      child: Row(
        children: [
          // Avatar circle with gradient
          AnimatedBuilder(
            animation: _nameCtrl,
            builder: (_, __) => Container(
              width: 66,
              height: 66,
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [Color(0xFF001540), Color(0xFF1A4FCC)],
                ),
                borderRadius: BorderRadius.circular(22),
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x45001540),
                    blurRadius: 16,
                    offset: Offset(0, 6),
                  ),
                ],
              ),
              alignment: Alignment.center,
              child: Text(
                _initials,
                style: const TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w900,
                  fontSize: 22,
                  color: Colors.white,
                  letterSpacing: -0.5,
                ),
              ),
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                AnimatedBuilder(
                  animation: _nameCtrl,
                  builder: (_, __) => Text(
                    _nameCtrl.text.isEmpty ? 'Your Name' : _nameCtrl.text,
                    style: const TextStyle(
                      fontFamily: 'DM Sans',
                      fontWeight: FontWeight.w800,
                      fontSize: 16,
                      color: Color(0xFF080F1E),
                      letterSpacing: -0.2,
                    ),
                  ),
                ),
                const SizedBox(height: 3),
                AnimatedBuilder(
                  animation: _emailCtrl,
                  builder: (_, __) => Text(
                    _emailCtrl.text.isEmpty
                        ? 'your@email.com'
                        : _emailCtrl.text,
                    style: const TextStyle(
                      fontFamily: 'DM Sans',
                      fontSize: 12.5,
                      color: Color(0xFF9AA3B2),
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFFFBEB),
                    border: Border.all(
                      color: const Color(0xFFF59E0B).withOpacity(0.28),
                    ),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Text(
                    '✦ Verified Requester',
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      fontWeight: FontWeight.w800,
                      fontSize: 10.5,
                      color: Color(0xFF92400E),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ── Form Card ────────────────────────────────────────────────────────────
  Widget _buildFormCard() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0x0E000000)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x0A001540),
            blurRadius: 14,
            offset: Offset(0, 5),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Section label
          const Text(
            'PERSONAL INFORMATION',
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontWeight: FontWeight.w800,
              fontSize: 10.5,
              color: Color(0xFF9AA3B2),
              letterSpacing: 1.0,
            ),
          ),
          const SizedBox(height: 18),

          _FormField(
            label: 'Full Name',
            controller: _nameCtrl,
            hint: 'Juan Dela Cruz',
            prefixIcon: Icons.person_outline_rounded,
            isFocused: _focusedField == 'name',
            onFocus: (v) => setState(() => _focusedField = v ? 'name' : null),
            validator: (v) =>
                (v ?? '').isEmpty ? 'Full name is required' : null,
          ),
          const SizedBox(height: 16),

          _FormField(
            label: 'Email Address',
            controller: _emailCtrl,
            hint: 'your.email@nu-lipa.edu.ph',
            prefixIcon: Icons.email_outlined,
            keyboardType: TextInputType.emailAddress,
            isFocused: _focusedField == 'email',
            onFocus: (v) => setState(() => _focusedField = v ? 'email' : null),
            validator: (v) {
              if ((v ?? '').isEmpty) return 'Email is required';
              if (!v!.contains('@')) return 'Enter a valid email';
              return null;
            },
          ),
          const SizedBox(height: 16),

          _FormField(
            label: 'Phone Number',
            controller: _phoneCtrl,
            hint: '09XX XXX XXXX',
            prefixIcon: Icons.phone_outlined,
            keyboardType: TextInputType.phone,
            isFocused: _focusedField == 'phone',
            onFocus: (v) => setState(() => _focusedField = v ? 'phone' : null),
          ),
          const SizedBox(height: 16),

          _FormField(
            label: 'Organization',
            controller: _orgCtrl,
            hint: 'e.g., Student Council',
            prefixIcon: Icons.group_outlined,
            isFocused: _focusedField == 'org',
            onFocus: (v) => setState(() => _focusedField = v ? 'org' : null),
          ),
          const SizedBox(height: 16),

          _FormField(
            label: 'Department',
            controller: _deptCtrl,
            hint: 'e.g., College of Computing',
            prefixIcon: Icons.business_outlined,
            isFocused: _focusedField == 'dept',
            onFocus: (v) => setState(() => _focusedField = v ? 'dept' : null),
          ),

          const SizedBox(height: 20),

          // Divider
          Container(height: 1, color: const Color(0x08000000)),
          const SizedBox(height: 20),

          // Bio section label
          const Text(
            'BIO',
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontWeight: FontWeight.w800,
              fontSize: 10.5,
              color: Color(0xFF9AA3B2),
              letterSpacing: 1.0,
            ),
          ),
          const SizedBox(height: 14),

          _FormField(
            label: 'Short Bio',
            controller: _bioCtrl,
            hint: 'Tell us a little about yourself…',
            prefixIcon: Icons.notes_rounded,
            maxLines: 4,
            isFocused: _focusedField == 'bio',
            onFocus: (v) => setState(() => _focusedField = v ? 'bio' : null),
          ),
        ],
      ),
    );
  }

  // ── Save Button ──────────────────────────────────────────────────────────
  Widget _buildSaveButton() {
    return GestureDetector(
      onTap: _isLoading ? null : _saveChanges,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        width: double.infinity,
        height: 54,
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [Color(0xFF001540), Color(0xFF0032A0)],
          ),
          borderRadius: BorderRadius.circular(18),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFF001540).withOpacity(0.38),
              blurRadius: 20,
              offset: const Offset(0, 8),
            ),
            BoxShadow(
              color: const Color(0xFF001540).withOpacity(0.15),
              blurRadius: 6,
              offset: const Offset(0, 2),
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
                    Icon(Icons.check_rounded, color: Colors.white, size: 20),
                    SizedBox(width: 8),
                    Text(
                      'Save Changes',
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
    );
  }
}

// ── Reusable Form Field ───────────────────────────────────────────────────────
class _FormField extends StatefulWidget {
  final String label;
  final TextEditingController controller;
  final String hint;
  final IconData prefixIcon;
  final TextInputType keyboardType;
  final int maxLines;
  final bool isFocused;
  final ValueChanged<bool> onFocus;
  final String? Function(String?)? validator;

  const _FormField({
    required this.label,
    required this.controller,
    required this.hint,
    required this.prefixIcon,
    this.keyboardType = TextInputType.text,
    this.maxLines = 1,
    required this.isFocused,
    required this.onFocus,
    this.validator,
  });

  @override
  State<_FormField> createState() => _FormFieldState();
}

class _FormFieldState extends State<_FormField> {
  late final FocusNode _focusNode;

  @override
  void initState() {
    super.initState();
    _focusNode = FocusNode();
    _focusNode.addListener(() {
      widget.onFocus(_focusNode.hasFocus);
    });
  }

  @override
  void dispose() {
    _focusNode.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Label row
        Row(
          children: [
            Container(
              width: 28,
              height: 28,
              decoration: BoxDecoration(
                color: widget.isFocused
                    ? const Color(0xFF002366).withOpacity(0.1)
                    : const Color(0xFF9AA3B2).withOpacity(0.08),
                borderRadius: BorderRadius.circular(9),
              ),
              child: Icon(
                widget.prefixIcon,
                size: 15,
                color: widget.isFocused
                    ? const Color(0xFF002366)
                    : const Color(0xFF9AA3B2),
              ),
            ),
            const SizedBox(width: 10),
            Text(
              widget.label,
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w700,
                fontSize: 13,
                color: widget.isFocused
                    ? const Color(0xFF080F1E)
                    : const Color(0xFF3D4A63),
              ),
            ),
          ],
        ),
        const SizedBox(height: 9),

        // Input
        AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          decoration: BoxDecoration(
            color: widget.isFocused ? Colors.white : const Color(0xFFF1F4FB),
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
              color: widget.isFocused
                  ? const Color(0xFF002366).withOpacity(0.5)
                  : Colors.transparent,
              width: 1.5,
            ),
            boxShadow: widget.isFocused
                ? [
                    BoxShadow(
                      color: const Color(0xFF002366).withOpacity(0.08),
                      blurRadius: 12,
                      offset: const Offset(0, 4),
                    ),
                  ]
                : null,
          ),
          child: TextFormField(
            controller: widget.controller,
            focusNode: _focusNode,
            keyboardType: widget.keyboardType,
            maxLines: widget.maxLines,
            textAlignVertical: widget.maxLines > 1
                ? TextAlignVertical.top
                : TextAlignVertical.center,
            validator: widget.validator,
            style: const TextStyle(
              fontFamily: 'DM Sans',
              fontSize: 14,
              fontWeight: FontWeight.w500,
              color: Color(0xFF080F1E),
            ),
            decoration: InputDecoration(
              hintText: widget.hint,
              hintStyle: const TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 13.5,
                color: Color(0xFFBFC5D0),
                fontWeight: FontWeight.w400,
              ),
              filled: false,
              border: InputBorder.none,
              enabledBorder: InputBorder.none,
              focusedBorder: InputBorder.none,
              errorBorder: InputBorder.none,
              focusedErrorBorder: InputBorder.none,
              contentPadding: EdgeInsets.symmetric(
                horizontal: 16,
                vertical: widget.maxLines > 1 ? 14 : 14,
              ),
              errorStyle: const TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 11,
                color: Color(0xFFFF3B30),
              ),
            ),
          ),
        ),
      ],
    );
  }
}
