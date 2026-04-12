import 'package:flutter/material.dart';
import '../app_bottom_nav.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';
import 'login_screen.dart';
import '../widgets/floating_message_button.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  String _name = '';
  String _role = '';
  String _organization = '';
  String _email = '';
  String _contact = '';
  int _totalRequests = 0;
  int _approved = 0;
  int _pending = 0;
  bool _isLoading = true;

  String get _initials {
    final parts = _name
        .trim()
        .split(RegExp(r'\s+'))
        .where((e) => e.isNotEmpty)
        .toList();
    if (parts.isEmpty) return 'NP';
    if (parts.length == 1) return parts.first.substring(0, 1).toUpperCase();
    return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
  }

  @override
  void initState() {
    super.initState();
    _loadProfile();
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
        _name = (data['name'] ?? SessionStore.name ?? '').toString();
        _role = (data['role'] ?? 'staff').toString();
        _organization = (data['organization'] ?? '').toString();
        _email = (data['email'] ?? SessionStore.email ?? '').toString();
        _contact = (data['phone'] ?? '').toString();
        _totalRequests = (stats['total'] as num?)?.toInt() ?? 0;
        _approved = (stats['approved'] as num?)?.toInt() ?? 0;
        _pending = (stats['pending'] as num?)?.toInt() ?? 0;
        _isLoading = false;
      });
    } catch (_) {
      setState(() => _isLoading = false);
    }
  }

  void _onLogOut() {
    showDialog(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        title: const Text(
          'Log Out',
          style: TextStyle(fontFamily: 'Inter', fontWeight: FontWeight.w600),
        ),
        content: const Text(
          'Are you sure you want to log out?',
          style: TextStyle(fontFamily: 'Inter'),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text(
              'Cancel',
              style: TextStyle(fontFamily: 'Inter', color: Color(0xFF4A5565)),
            ),
          ),
          TextButton(
            onPressed: () {
              Navigator.pop(context);
              SessionStore.clear();
              Navigator.of(context).pushAndRemoveUntil(
                MaterialPageRoute(builder: (_) => const LoginScreen()),
                (_) => false,
              );
            },
            child: const Text(
              'Log Out',
              style: TextStyle(
                fontFamily: 'Inter',
                color: Color(0xFFE7000B),
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF9FAFB),
      body: Stack(
        children: [
          // ── Scrollable content ───────────────────────
          SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // ── Blue gradient header + profile card ──
                _buildHeaderWithCard(),

                if (_isLoading)
                  const Padding(
                    padding: EdgeInsets.only(top: 12),
                    child: Center(child: CircularProgressIndicator()),
                  ),

                const SizedBox(height: 16),

                // ── Contact info card ────────────────────
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: _buildContactCard(),
                ),

                const SizedBox(height: 16),

                // ── Stat cards ───────────────────────────
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: _buildStatCards(),
                ),

                const SizedBox(height: 20),

                // ── Settings section ─────────────────────
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  child: Text(
                    'Settings',
                    style: const TextStyle(
                      fontFamily: 'Inter',
                      fontWeight: FontWeight.w500,
                      fontSize: 13,
                      color: Color(0xFF6A7282),
                    ),
                  ),
                ),
                const SizedBox(height: 8),

                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: _buildMenuCard(
                    items: [
                      _MenuItem(
                        icon: Icons.shield_outlined,
                        label: 'Account Security',
                        onTap: () {},
                      ),
                      _MenuItem(
                        icon: Icons.notifications_outlined,
                        label: 'Notification Settings',
                        onTap: () {},
                      ),
                      _MenuItem(
                        icon: Icons.person_outline,
                        label: 'Edit Profile',
                        onTap: () {},
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 20),

                // ── Help & Support section ────────────────
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 24),
                  child: Text(
                    'Help & Support',
                    style: const TextStyle(
                      fontFamily: 'Inter',
                      fontWeight: FontWeight.w500,
                      fontSize: 13.7,
                      color: Color(0xFF6A7282),
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: _buildMenuCard(
                    items: [
                      _MenuItem(
                        icon: Icons.help_outline,
                        label: 'Help Center',
                        onTap: () {},
                      ),
                      _MenuItem(
                        icon: Icons.description_outlined,
                        label: 'Terms & Guidelines',
                        onTap: () {},
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 20),

                // ── Log Out button ────────────────────────
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: _buildLogOutButton(),
                ),

                const SizedBox(height: 16),

                // ── Version footer ────────────────────────
                const Center(
                  child: Text(
                    'NUPost v1.0.0 • NU Lipa Marketing Office',
                    style: TextStyle(
                      fontFamily: 'Inter',
                      fontWeight: FontWeight.w400,
                      fontSize: 11.1,
                      color: Color(0xFF99A1AF),
                    ),
                  ),
                ),

                // Space for nav bar
                const SizedBox(height: 80),
              ],
            ),
          ),

          // ── Bottom Nav ───────────────────────────────
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: AppBottomNav(currentIndex: 4),
          ),
          const FloatingMessageButton(),
        ],
      ),
    );
  }

  // ── Blue header + profile card ────────────────────────
  Widget _buildHeaderWithCard() {
    return Stack(
      clipBehavior: Clip.none,
      children: [
        // Blue gradient bg
        Container(
          height: 180,
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Color(0xFF003366), Color(0xFF004D99)],
            ),
            borderRadius: BorderRadius.only(
              bottomLeft: Radius.circular(24),
              bottomRight: Radius.circular(24),
            ),
          ),
          padding: EdgeInsets.only(
            top: MediaQuery.of(context).padding.top + 16,
            left: 24,
          ),
          child: const Align(
            alignment: Alignment.topLeft,
            child: Text(
              'Profile',
              style: TextStyle(
                fontFamily: 'Inter',
                fontWeight: FontWeight.w500,
                fontSize: 22.9,
                color: Colors.white,
              ),
            ),
          ),
        ),

        // White profile card overlapping the header
        Positioned(
          top: MediaQuery.of(context).padding.top + 60,
          left: 24,
          right: 24,
          child: Container(
            height: 124,
            decoration: BoxDecoration(
              color: Colors.white,
              border: Border.all(color: Colors.black.withOpacity(0.1)),
              borderRadius: BorderRadius.circular(14),
              boxShadow: const [
                BoxShadow(
                  color: Color(0x1A000000),
                  blurRadius: 15,
                  offset: Offset(0, 10),
                ),
              ],
            ),
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
            child: Row(
              children: [
                // Avatar circle
                Container(
                  width: 64,
                  height: 64,
                  decoration: BoxDecoration(
                    color: const Color(0xFF003366),
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: const Color(0xFF003366),
                      width: 2,
                    ),
                  ),
                  alignment: Alignment.center,
                  child: Text(
                    _initials,
                    style: const TextStyle(
                      fontFamily: 'Inter',
                      fontWeight: FontWeight.w400,
                      fontSize: 16.9,
                      color: Colors.white,
                    ),
                  ),
                ),

                const SizedBox(width: 12),

                // Name / role / badge
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        _name.isEmpty ? 'NUPost User' : _name,
                        style: const TextStyle(
                          fontFamily: 'Inter',
                          fontWeight: FontWeight.w500,
                          fontSize: 16.7,
                          color: Color(0xFF101828),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _role.isEmpty ? 'Requester' : _role,
                        style: const TextStyle(
                          fontFamily: 'Inter',
                          fontWeight: FontWeight.w400,
                          fontSize: 12.8,
                          color: Color(0xFF4A5565),
                        ),
                      ),
                      const SizedBox(height: 6),
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: const Color(0xFFDBEAFE),
                          border: Border.all(color: const Color(0xFF8EC5FF)),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Text(
                          'Verified Requester',
                          style: TextStyle(
                            fontFamily: 'Inter',
                            fontWeight: FontWeight.w500,
                            fontSize: 11.1,
                            color: Color(0xFF1447E6),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                // NUPost logo top-right of card
                Image.asset(
                  'assets/images/nu_shield.png',
                  width: 48,
                  height: 32,
                  fit: BoxFit.contain,
                  errorBuilder: (_, __, ___) => const SizedBox(
                    width: 48,
                    child: Text(
                      'NU\nPOST',
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        fontFamily: 'Inter',
                        fontWeight: FontWeight.w900,
                        fontSize: 10,
                        color: Color(0xFF003366),
                      ),
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

  // ── Contact info card ─────────────────────────────────
  Widget _buildContactCard() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: Colors.black.withOpacity(0.1)),
        borderRadius: BorderRadius.circular(14),
        boxShadow: const [
          BoxShadow(
            color: Color(0x1A000000),
            blurRadius: 6,
            offset: Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(17),
      child: Column(
        children: [
          _ContactRow(
            icon: Icons.business_outlined,
            label: 'Organization',
            value: _organization.isEmpty ? 'Not set' : _organization,
          ),
          const SizedBox(height: 16),
          _ContactRow(
            icon: Icons.email_outlined,
            label: 'Email',
            value: _email.isEmpty ? 'Not set' : _email,
          ),
          const SizedBox(height: 16),
          _ContactRow(
            icon: Icons.phone_outlined,
            label: 'Contact Number',
            value: _contact.isEmpty ? 'Not set' : _contact,
          ),
        ],
      ),
    );
  }

  // ── Stat cards ────────────────────────────────────────
  Widget _buildStatCards() {
    return Row(
      children: [
        Expanded(
          child: _StatCard(
            value: '$_totalRequests',
            label: 'Total Requests',
            valueColor: const Color(0xFF003366),
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: _StatCard(
            value: '$_approved',
            label: 'Approved',
            valueColor: const Color(0xFF00A63E),
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: _StatCard(
            value: '$_pending',
            label: 'Pending',
            valueColor: const Color(0xFFE17100),
          ),
        ),
      ],
    );
  }

  // ── Menu card (settings / help) ───────────────────────
  Widget _buildMenuCard({required List<_MenuItem> items}) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: Colors.black.withOpacity(0.1)),
        borderRadius: BorderRadius.circular(14),
        boxShadow: const [
          BoxShadow(
            color: Color(0x1A000000),
            blurRadius: 6,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: items.asMap().entries.map((entry) {
          final i = entry.key;
          final item = entry.value;
          return Column(
            children: [
              InkWell(
                onTap: item.onTap,
                borderRadius: i == 0
                    ? const BorderRadius.vertical(top: Radius.circular(14))
                    : i == items.length - 1
                    ? const BorderRadius.vertical(bottom: Radius.circular(14))
                    : BorderRadius.zero,
                child: SizedBox(
                  height: 57,
                  child: Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 16),
                    child: Row(
                      children: [
                        Icon(
                          item.icon,
                          size: 20,
                          color: const Color(0xFF4A5565),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Text(
                            item.label,
                            style: const TextStyle(
                              fontFamily: 'Inter',
                              fontWeight: FontWeight.w500,
                              fontSize: 15,
                              color: Color(0xFF0A0A0A),
                            ),
                          ),
                        ),
                        const Icon(
                          Icons.chevron_right,
                          size: 20,
                          color: Color(0xFF99A1AF),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
              if (i < items.length - 1)
                Divider(
                  height: 1,
                  thickness: 1,
                  color: Colors.black.withOpacity(0.06),
                ),
            ],
          );
        }).toList(),
      ),
    );
  }

  // ── Log Out button ────────────────────────────────────
  Widget _buildLogOutButton() {
    return GestureDetector(
      onTap: _onLogOut,
      child: Container(
        height: 48,
        decoration: BoxDecoration(
          color: Colors.white,
          border: Border.all(color: const Color(0xFFFFA2A2)),
          borderRadius: BorderRadius.circular(8),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: const [
            Icon(Icons.logout, size: 16, color: Color(0xFFE7000B)),
            SizedBox(width: 8),
            Text(
              'Log Out',
              style: TextStyle(
                fontFamily: 'Inter',
                fontWeight: FontWeight.w500,
                fontSize: 13.6,
                color: Color(0xFFE7000B),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Contact Row ───────────────────────────────────────────────────────────────
class _ContactRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;

  const _ContactRow({
    required this.icon,
    required this.label,
    required this.value,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 20, color: const Color(0xFF003366)),
        const SizedBox(width: 12),
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              label,
              style: const TextStyle(
                fontFamily: 'Inter',
                fontWeight: FontWeight.w400,
                fontSize: 11.3,
                color: Color(0xFF6A7282),
              ),
            ),
            const SizedBox(height: 2),
            Text(
              value,
              style: const TextStyle(
                fontFamily: 'Inter',
                fontWeight: FontWeight.w400,
                fontSize: 14.8,
                color: Color(0xFF0A0A0A),
              ),
            ),
          ],
        ),
      ],
    );
  }
}

// ── Stat Card ─────────────────────────────────────────────────────────────────
class _StatCard extends StatelessWidget {
  final String value;
  final String label;
  final Color valueColor;

  const _StatCard({
    required this.value,
    required this.label,
    required this.valueColor,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 96,
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: Colors.black.withOpacity(0.1)),
        borderRadius: BorderRadius.circular(14),
        boxShadow: const [
          BoxShadow(
            color: Color(0x1A000000),
            blurRadius: 6,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            value,
            style: TextStyle(
              fontFamily: 'Inter',
              fontWeight: FontWeight.w400,
              fontSize: 24,
              color: valueColor,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            label,
            style: const TextStyle(
              fontFamily: 'Inter',
              fontWeight: FontWeight.w400,
              fontSize: 10.7,
              color: Color(0xFF4A5565),
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }
}

// ── Menu Item model ───────────────────────────────────────────────────────────
class _MenuItem {
  final IconData icon;
  final String label;
  final VoidCallback onTap;
  const _MenuItem({
    required this.icon,
    required this.label,
    required this.onTap,
  });
}
