import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';
import '../services/app_memory_cache.dart';
import '../services/network_queue_manager.dart';
import '../widgets/skeleton_loader.dart';
import 'login_screen.dart';
import 'edit_profile_screen.dart';
import 'account_security_screen.dart';
import 'notification_settings_screen.dart';
import 'help_center_screen.dart';
import 'terms_guidelines_screen.dart';
import '../widgets/sla_guidelines_sheet.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});
  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen>
    with SingleTickerProviderStateMixin {
  String _name = SessionStore.name ?? '';
  String _role = SessionStore.role ?? 'Requestor';
  String _organization = '';
  String _email = SessionStore.email ?? '';
  String _contact = '';
  int _totalRequests = 0, _approved = 0, _pending = 0;
  bool _isLoading = false;
  bool _showSlaNotice = true;

  late final AnimationController _entryCtrl;
  late final Animation<double> _entryFade;
  late final Animation<Offset> _entrySlide;

  String get _initials {
    final parts = _name
        .trim()
        .split(RegExp(r'\s+'))
        .where((e) => e.isNotEmpty)
        .toList();
    if (parts.isEmpty) return 'NP';
    if (parts.length == 1) return parts[0][0].toUpperCase();
    return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
  }

  String get _displayRole {
    final normalized = _role.trim().toLowerCase();
    return (normalized == 'admin') ? 'Admin' : 'Requestor';
  }

  String get _profileSubtitle {
    final org = _organization.trim();
    if (org.isEmpty) return _displayRole;
    return '$_displayRole · $org';
  }

  @override
  void initState() {
    super.initState();
    _name = SessionStore.name ?? '';
    _role = SessionStore.role ?? 'Requestor';
    _email = SessionStore.email ?? '';

    _entryCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 650),
    )..forward();
    _entryFade = CurvedAnimation(
      parent: _entryCtrl,
      curve: const Interval(0.0, 0.7, curve: Curves.easeOut),
    );
    _entrySlide = Tween<Offset>(begin: const Offset(0, 0.03), end: Offset.zero)
        .animate(
          CurvedAnimation(
            parent: _entryCtrl,
            curve: const Interval(0.0, 0.8, curve: Curves.easeOutCubic),
          ),
        );
    _loadProfile();
  }

  @override
  void dispose() {
    _entryCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadProfile() async {
    final userId = SessionStore.userId;
    if (userId == null) {
      setState(() => _isLoading = false);
      return;
    }
    try {
      final result = await ApiService.fetchProfile(userId: userId);
      final data = (result['data'] as Map<String, dynamic>?) ?? {};
      final stats = (data['stats'] as Map<String, dynamic>?) ?? {};
      setState(() {
        final serverName = (data['name'] ?? SessionStore.name ?? '').toString();
        _name = serverName;
        if (serverName.isNotEmpty) {
          SessionStore.name = serverName;
        }
        _role = (data['role'] ?? SessionStore.role ?? 'Requestor').toString();
        _organization = (data['organization'] ?? '').toString();
        _email = (data['email'] ?? SessionStore.email ?? '').toString();
        _contact = (data['phone'] ?? '').toString();
        _totalRequests = (stats['total'] as num?)?.toInt() ?? 0;
        _approved = (stats['approved'] as num?)?.toInt() ?? 0;
        _pending = (stats['pending'] as num?)?.toInt() ?? 0;
        _isLoading = false;
      });
      final slaSetting = await SessionStore.getPermanentSlaNotice();
      if (mounted) setState(() => _showSlaNotice = slaSetting);
    } catch (_) {
      setState(() => _isLoading = false);
    }
  }

  void _onLogOut() {
    showDialog(
      context: context,
      useRootNavigator: false,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Text(
          'Log Out',
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontWeight: FontWeight.w800,
            fontSize: 18,
          ),
        ),
        content: const Text(
          'Are you sure you want to log out?',
          style: TextStyle(fontFamily: 'DM Sans', fontSize: 14),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text(
              'Cancel',
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w600,
                color: Color(0xFF9AA3B2),
              ),
            ),
          ),
          TextButton(
            onPressed: () async {
              Navigator.pop(context);
              await SessionStore.clear();
              await NetworkQueueManager.instance.clearCache();
              AppMemoryCache.clear();
              if (!mounted) return;
              Navigator.of(context, rootNavigator: true).pushAndRemoveUntil(
                MaterialPageRoute(builder: (_) => const LoginScreen()),
                (_) => false,
              );
            },
            child: const Text(
              'Log Out',
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w800,
                color: Color(0xFFFF3B30),
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final topPad = MediaQuery.of(context).padding.top;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Scaffold(
      resizeToAvoidBottomInset: false,
      backgroundColor: Theme.of(context).scaffoldBackgroundColor,
      body: Stack(
        children: [
          FadeTransition(
            opacity: _entryFade,
            child: SlideTransition(
              position: _entrySlide,
              child: SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // ── HERO ─────────────────────────────────────────────────────
                    SizedBox(
                      width: double.infinity,
                      child: Stack(
                        clipBehavior: Clip.none,
                        children: [
                          // Gradient bg
                          Container(
                            width: double.infinity,
                            height: 200,
                            decoration: const BoxDecoration(
                              gradient: LinearGradient(
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight,
                                colors: [
                                  Color(0xFF001540),
                                  Color(0xFF002878),
                                  Color(0xFF1243B0),
                                ],
                                stops: [0.0, 0.5, 1.0],
                              ),
                              borderRadius: BorderRadius.only(
                                bottomLeft: Radius.circular(36),
                                bottomRight: Radius.circular(36),
                              ),
                            ),
                            child: Stack(
                              children: [
                                Positioned(
                                  top: -50,
                                  right: -50,
                                  child: Container(
                                    width: 220,
                                    height: 220,
                                    decoration: BoxDecoration(
                                      shape: BoxShape.circle,
                                      color: Colors.white.withOpacity(0.05),
                                    ),
                                  ),
                                ),
                                Positioned(
                                  bottom: -20,
                                  left: -30,
                                  child: Container(
                                    width: 140,
                                    height: 140,
                                    decoration: BoxDecoration(
                                      shape: BoxShape.circle,
                                      color: const Color(
                                        0xFFF59E0B,
                                      ).withOpacity(0.07),
                                    ),
                                  ),
                                ),
                                Padding(
                                  padding: EdgeInsets.only(
                                    top: topPad + 16,
                                    left: 20,
                                  ),
                                  child: Text(
                                    'PROFILE',
                                    style: TextStyle(
                                      fontFamily: 'DM Sans',
                                      fontSize: 18,
                                      fontWeight: FontWeight.w800,
                                      color: Colors.white.withOpacity(0.55),
                                      letterSpacing: 0.8,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          // Overlapping profile card
                          Positioned(
                            top: topPad + 74,
                            left: 20,
                            right: 20,
                            child: Container(
                              decoration: BoxDecoration(
                                color: isDark ? const Color(0xFF131D31) : Colors.white,
                                borderRadius: BorderRadius.circular(24),
                                border: Border.all(
                                  color: isDark ? const Color(0xFF1E2B45) : const Color(0x0E000000),
                                ),
                                boxShadow: [
                                  BoxShadow(
                                    color: isDark ? const Color(0x40000000) : const Color(0x12001540),
                                    blurRadius: 16,
                                    offset: const Offset(0, 7),
                                  ),
                                ],
                              ),
                              padding: const EdgeInsets.symmetric(
                                horizontal: 20,
                                vertical: 16,
                              ),
                              child: Row(
                                children: [
                                  // Avatar
                                  Container(
                                    width: 66,
                                    height: 66,
                                    decoration: BoxDecoration(
                                      gradient: const LinearGradient(
                                        begin: Alignment.topLeft,
                                        end: Alignment.bottomRight,
                                        colors: [
                                          Color(0xFF0D2A7E),
                                          Color(0xFF174EBA),
                                        ],
                                      ),
                                      borderRadius: BorderRadius.circular(21),
                                      boxShadow: const [
                                        BoxShadow(
                                          color: Color(0x2A001540),
                                          blurRadius: 10,
                                          offset: Offset(0, 5),
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
                                  const SizedBox(width: 14),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          _name.isEmpty ? 'NUPost User' : _name,
                                          style: TextStyle(
                                            fontFamily: 'DM Sans',
                                            fontWeight: FontWeight.w800,
                                            fontSize: 17.5,
                                            color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF080F1E),
                                            letterSpacing: -0.3,
                                          ),
                                        ),
                                        const SizedBox(height: 3),
                                        Text(
                                          _profileSubtitle,
                                          style: TextStyle(
                                            fontFamily: 'DM Sans',
                                            fontSize: 13,
                                            color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF9AA3B2),
                                          ),
                                        ),
                                        const SizedBox(height: 8),
                                        Container(
                                          padding: const EdgeInsets.symmetric(
                                            horizontal: 10,
                                            vertical: 4,
                                          ),
                                          decoration: BoxDecoration(
                                            color: SessionStore.isAdmin
                                                ? (isDark ? const Color(0x331D4ED8) : const Color(0xFFEFF6FF))
                                                : (isDark ? const Color(0x33F59E0B) : const Color(0xFFFFFBEB)),
                                            border: Border.all(
                                              color: SessionStore.isAdmin
                                                  ? (isDark ? const Color(0xFF3B82F6) : const Color(0xFF3B82F6).withOpacity(0.35))
                                                  : (isDark ? const Color(0xFFF59E0B) : const Color(0xFFF59E0B).withOpacity(0.28)),
                                            ),
                                            borderRadius: BorderRadius.circular(
                                              10,
                                            ),
                                          ),
                                          child: Text(
                                            SessionStore.isAdmin
                                                ? '✦ System Administrator'
                                                : '✦ Verified Requestor',
                                            style: TextStyle(
                                              fontFamily: 'DM Sans',
                                              fontWeight: FontWeight.w800,
                                              fontSize: 10.5,
                                              color: SessionStore.isAdmin
                                                  ? (isDark ? const Color(0xFF93C5FD) : const Color(0xFF1D4ED8))
                                                  : (isDark ? const Color(0xFFFDE68A) : const Color(0xFF92400E)),
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  GestureDetector(
                                    onTap: () async {
                                      final didUpdate =
                                          await Navigator.push<bool>(
                                            context,
                                            MaterialPageRoute(
                                              builder: (context) =>
                                                  const EditProfileScreen(),
                                            ),
                                          );
                                      if (didUpdate == true) {
                                        _loadProfile();
                                      }
                                    },
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 12,
                                        vertical: 7,
                                      ),
                                      decoration: BoxDecoration(
                                        color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFF5F8FF),
                                        border: Border.all(
                                          color: isDark
                                              ? const Color(0xFF3B82F6).withOpacity(0.4)
                                              : const Color(0xFF002366).withOpacity(0.2),
                                          width: 1.2,
                                        ),
                                        borderRadius: BorderRadius.circular(11),
                                      ),
                                      child: Text(
                                        'Edit',
                                        style: TextStyle(
                                          fontFamily: 'DM Sans',
                                          fontWeight: FontWeight.w800,
                                          fontSize: 12,
                                          color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF2B5CE6),
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                          // Height placeholder
                          SizedBox(height: topPad + 74 + 136),
                        ],
                      ),
                    ),

                    if (_name.isEmpty && _isLoading)
                      const Padding(
                        padding: EdgeInsets.only(top: 16),
                        child: ProfileSkeleton(),
                      )
                    else ...[
                      const SizedBox(height: 16),

                      // ── STAT CARDS ────────────────────────────────────────────
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: Row(
                          children: [
                            _StatCard(
                              value: '$_totalRequests',
                              label: SessionStore.isAdmin ? 'Total' : 'Total',
                              color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF002366),
                            ),
                            const SizedBox(width: 10),
                            _StatCard(
                              value: '$_approved',
                              label: SessionStore.isAdmin ? 'Approved' : 'Approved',
                              color: const Color(0xFF05C46B),
                            ),
                            const SizedBox(width: 10),
                            _StatCard(
                              value: '$_pending',
                              label: SessionStore.isAdmin ? 'Pending' : 'Pending',
                              color: const Color(0xFFF59E0B),
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 16),

                      // ── CONTACT CARD ──────────────────────────────────────────
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: _buildContactCard(),
                      ),

                      const SizedBox(height: 20),

                      // ── SETTINGS ─────────────────────────────────────────────
                      _SectionLabel(text: 'Settings'),
                      const SizedBox(height: 10),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: _MenuCard(
                          items: [
                            _MenuItem(
                              icon: Icons.shield_outlined,
                              label: 'Account Security',
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) =>
                                        const AccountSecurityScreen(),
                                  ),
                                );
                              },
                            ),
                            _MenuItem(
                              icon: Icons.dark_mode_outlined,
                              label: 'Dark Mode',
                              trailing: Switch.adaptive(
                                value: isDark,
                                activeColor: const Color(0xFFF59E0B),
                                onChanged: (val) async {
                                  await SessionStore.setDarkMode(val);
                                  if (mounted) setState(() {});
                                },
                              ),
                              onTap: () async {
                                final newVal = !isDark;
                                await SessionStore.setDarkMode(newVal);
                                if (mounted) setState(() {});
                              },
                            ),
                            _MenuItem(
                              icon: Icons.schedule_rounded,
                              label: 'SLA Notice on Request',
                              trailing: Switch.adaptive(
                                value: _showSlaNotice,
                                activeColor: const Color(0xFF002366),
                                onChanged: (val) async {
                                  setState(() => _showSlaNotice = val);
                                  await SessionStore.setPermanentSlaNotice(val);
                                  if (val) SessionStore.sessionSlaDismissed = false;
                                },
                              ),
                              onTap: () async {
                                final newVal = !_showSlaNotice;
                                setState(() => _showSlaNotice = newVal);
                                await SessionStore.setPermanentSlaNotice(newVal);
                                if (newVal) SessionStore.sessionSlaDismissed = false;
                              },
                            ),
                            _MenuItem(
                              icon: Icons.notifications_outlined,
                              label: 'Notification Settings',
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) =>
                                        const NotificationSettingsScreen(),
                                  ),
                                );
                              },
                            ),
                            _MenuItem(
                              icon: Icons.person_outline_rounded,
                              label: 'Edit Profile',
                              onTap: () async {
                                final didUpdate = await Navigator.push<bool>(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) =>
                                        const EditProfileScreen(),
                                  ),
                                );
                                if (didUpdate == true) {
                                  _loadProfile();
                                }
                              },
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 20),

                      // ── HELP ─────────────────────────────────────────────────
                      _SectionLabel(text: 'Help & Support'),
                      const SizedBox(height: 10),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: _MenuCard(
                          items: [
                            _MenuItem(
                              icon: Icons.fact_check_outlined,
                              label: 'Creative SLA Guidelines',
                              onTap: () {
                                SlaGuidelinesSheet.show(
                                  context,
                                  isReferenceMode: true,
                                );
                              },
                            ),
                            _MenuItem(
                              icon: Icons.help_outline_rounded,
                              label: 'Help Center',
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) => const HelpCenterScreen(),
                                  ),
                                );
                              },
                            ),
                            _MenuItem(
                              icon: Icons.description_outlined,
                              label: 'Terms & Guidelines',
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (_) =>
                                        const TermsGuidelinesScreen(),
                                  ),
                                );
                              },
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 20),

                      // ── LOG OUT ───────────────────────────────────────────────
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: GestureDetector(
                          onTap: _onLogOut,
                          child: Container(
                            height: 52,
                            decoration: BoxDecoration(
                              color: Colors.white,
                              border: Border.all(
                                color: const Color(
                                  0xFFFF3B30,
                                ).withOpacity(0.55),
                              ),
                              borderRadius: BorderRadius.circular(18),
                              boxShadow: const [
                                BoxShadow(
                                  color: Color(0x0A000000),
                                  blurRadius: 8,
                                  offset: Offset(0, 3),
                                ),
                              ],
                            ),
                            child: const Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  Icons.logout_rounded,
                                  size: 18,
                                  color: Color(0xFFFF3B30),
                                ),
                                SizedBox(width: 9),
                                Text(
                                  'Log Out',
                                  style: TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontWeight: FontWeight.w800,
                                    fontSize: 14,
                                    color: Color(0xFFFF3B30),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),

                      const SizedBox(height: 14),
                      const Center(
                        child: Text(
                          'NUPost v1.0.0 · NU Lipa Marketing Office',
                          style: TextStyle(
                            fontFamily: 'DM Sans',
                            fontSize: 11,
                            color: Color(0xFF9AA3B2),
                          ),
                        ),
                      ),
                      const SizedBox(height: 100),
                    ],
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildContactCard() {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Container(
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF131D31) : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isDark ? const Color(0xFF1E2B45) : const Color(0x0E000000),
        ),
        boxShadow: [
          BoxShadow(
            color: isDark ? const Color(0x40000000) : const Color(0x07001540),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          _ContactRow(
            icon: Icons.business_outlined,
            label: 'Organization',
            value: _organization.isEmpty ? 'Not set' : _organization,
          ),
          _Divider(),
          _ContactRow(
            icon: Icons.email_outlined,
            label: 'Email',
            value: _email.isEmpty ? 'Not set' : _email,
          ),
          _Divider(),
          _ContactRow(
            icon: Icons.phone_outlined,
            label: 'Contact',
            value: _contact.isEmpty ? 'Not set' : _contact,
          ),
        ],
      ),
    );
  }
}

// ── Reusable Widgets ──────────────────────────────────────────────────────────

class _StatCard extends StatelessWidget {
  final String value, label;
  final Color color;
  const _StatCard({
    required this.value,
    required this.label,
    required this.color,
  });
  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Expanded(
      child: Container(
        height: 90,
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF131D31) : Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: isDark ? const Color(0xFF1E2B45) : const Color(0x0E000000),
          ),
          boxShadow: [
            BoxShadow(
              color: isDark ? const Color(0x40000000) : const Color(0x07001540),
              blurRadius: 10,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              value,
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w900,
                fontSize: 26,
                color: color,
                letterSpacing: -1.0,
              ),
            ),
            const SizedBox(height: 5),
            Text(
              label.toUpperCase(),
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w700,
                fontSize: 9.5,
                color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF9AA3B2),
                letterSpacing: 0.7,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ContactRow extends StatelessWidget {
  final IconData icon;
  final String label, value;
  const _ContactRow({
    required this.icon,
    required this.label,
    required this.value,
  });
  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: isDark
                  ? const Color(0xFF1E2B45)
                  : const Color(0xFF002366).withOpacity(0.07),
              borderRadius: BorderRadius.circular(11),
            ),
            child: Icon(
              icon,
              size: 18,
              color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF002366),
            ),
          ),
          const SizedBox(width: 13),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  label.toUpperCase(),
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w700,
                    fontSize: 9.5,
                    color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF9AA3B2),
                    letterSpacing: 0.7,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  value,
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w600,
                    fontSize: 13.5,
                    color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF080F1E),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _Divider extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Container(
      height: 1,
      margin: const EdgeInsets.symmetric(horizontal: 18),
      color: isDark ? const Color(0x1AFFFFFF) : const Color(0x08000000),
    );
  }
}

class _SectionLabel extends StatelessWidget {
  final String text;
  const _SectionLabel({required this.text});
  @override
  Widget build(BuildContext context) => Padding(
    padding: const EdgeInsets.symmetric(horizontal: 24),
    child: Text(
      text.toUpperCase(),
      style: const TextStyle(
        fontFamily: 'DM Sans',
        fontWeight: FontWeight.w800,
        fontSize: 10.5,
        color: Color(0xFF9AA3B2),
        letterSpacing: 1.0,
      ),
    ),
  );
}

class _MenuCard extends StatelessWidget {
  final List<_MenuItem> items;
  const _MenuCard({required this.items});
  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Container(
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF131D31) : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isDark ? const Color(0xFF1E2B45) : const Color(0x0E000000),
        ),
        boxShadow: [
          BoxShadow(
            color: isDark ? const Color(0x40000000) : const Color(0x07001540),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: items.asMap().entries.map((e) {
          final i = e.key;
          final item = e.value;
          return Column(
            children: [
              InkWell(
                onTap: item.onTap,
                borderRadius: i == 0
                    ? const BorderRadius.vertical(top: Radius.circular(20))
                    : i == items.length - 1
                    ? const BorderRadius.vertical(bottom: Radius.circular(20))
                    : BorderRadius.zero,
                child: Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 18,
                    vertical: 16,
                  ),
                  child: Row(
                    children: [
                      Container(
                        width: 36,
                        height: 36,
                        decoration: BoxDecoration(
                          color: isDark
                              ? const Color(0xFF1E2B45)
                              : const Color(0xFF9AA3B2).withOpacity(0.09),
                          borderRadius: BorderRadius.circular(11),
                        ),
                        child: Icon(
                          item.icon,
                          size: 18,
                          color: isDark ? const Color(0xFFCBD5E1) : const Color(0xFF3D4A63),
                        ),
                      ),
                      const SizedBox(width: 13),
                      Expanded(
                        child: Text(
                          item.label,
                          style: TextStyle(
                            fontFamily: 'DM Sans',
                            fontWeight: FontWeight.w700,
                            fontSize: 14.5,
                            color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF080F1E),
                          ),
                        ),
                      ),
                      item.trailing ??
                          Icon(
                            Icons.arrow_forward_ios_rounded,
                            size: 13,
                            color: isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2),
                          ),
                    ],
                  ),
                ),
              ),
              if (i < items.length - 1)
                Container(
                  height: 1,
                  margin: const EdgeInsets.symmetric(horizontal: 18),
                  color: isDark ? const Color(0x1AFFFFFF) : const Color(0x08000000),
                ),
            ],
          );
        }).toList(),
      ),
    );
  }
}

class _MenuItem {
  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final Widget? trailing;
  const _MenuItem({
    required this.icon,
    required this.label,
    required this.onTap,
    this.trailing,
  });
}
