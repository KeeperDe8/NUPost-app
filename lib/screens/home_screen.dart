import 'package:flutter/material.dart';
import 'create_request_screen.dart';
import '../app_bottom_nav.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';
import '../theme/app_theme.dart';
import '../widgets/floating_message_button.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen>
    with SingleTickerProviderStateMixin {
  final int _currentIndex = 0;
  int _pendingCount = 0;
  int _approvedCount = 0;
  int _postedCount = 0;

  late final AnimationController _entryController;
  late final Animation<double> _fadeAnim;
  late final Animation<Offset> _slideAnim;

  List<Map<String, dynamic>> _recentRequests = [];
  bool _isLoadingRequests = true;

  @override
  void initState() {
    super.initState();
    _entryController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 700),
    )..forward();

    _fadeAnim = CurvedAnimation(
      parent: _entryController,
      curve: const Interval(0.0, 0.7, curve: Curves.easeOut),
    );
    _slideAnim = Tween<Offset>(begin: const Offset(0, 0.04), end: Offset.zero)
        .animate(
          CurvedAnimation(
            parent: _entryController,
            curve: const Interval(0.0, 0.8, curve: Curves.easeOutCubic),
          ),
        );

    _loadStats();
  }

  @override
  void dispose() {
    _entryController.dispose();
    super.dispose();
  }

  Future<void> _loadStats() async {
    try {
      final userId = SessionStore.userId;
      if (userId == null || userId == 0) {
        if (mounted) setState(() => _isLoadingRequests = false);
        return;
      }
      final profileData = await ApiService.fetchProfile(userId: userId);
      if (profileData['success'] == true) {
        final stats = profileData['data']['stats'] ?? {};
        if (mounted) {
          setState(() {
            _pendingCount = stats['pending'] ?? 0;
            _approvedCount = stats['approved'] ?? 0;
            _postedCount = stats['total'] ?? 0;
          });
        }
      }

      final requests = await ApiService.fetchRequests(userId: userId);
      if (mounted) {
        setState(() {
          _recentRequests = requests.take(3).toList();
          _isLoadingRequests = false;
        });
      }
    } catch (e) {
      debugPrint('Error loading home stats: $e');
      if (mounted) setState(() => _isLoadingRequests = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.pageBg,
      body: Stack(
        children: [
          FadeTransition(
            opacity: _fadeAnim,
            child: SlideTransition(
              position: _slideAnim,
              child: Column(
                children: [
                  Expanded(
                    child: SingleChildScrollView(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          _buildHero(),
                          const SizedBox(height: 18),
                          _buildApprovalBanner(),
                          const SizedBox(height: 18),
                          _buildQuickActions(),
                          _buildSectionHeader('Recent Requests', 'View all'),
                          _buildRecentRequests(),
                          const SizedBox(height: 100),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: AppBottomNav(currentIndex: _currentIndex),
          ),
          const FloatingMessageButton(),
        ],
      ),
    );
  }

  // ── Hero ──────────────────────────────────────────────────────────────────
  Widget _buildHero() {
    final topPad = MediaQuery.of(context).padding.top;
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
          bottomLeft: Radius.circular(36),
          bottomRight: Radius.circular(36),
        ),
      ),
      child: Stack(
        children: [
          // Decorative orb top-right
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
            bottom: -30,
            left: -20,
            child: Container(
              width: 140,
              height: 140,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: const Color(0xFFF59E0B).withOpacity(0.07),
              ),
            ),
          ),
          Padding(
            padding: EdgeInsets.fromLTRB(22, topPad + 18, 22, 26),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Top row: title + avatar
                Row(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                width: 6,
                                height: 6,
                                decoration: const BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: Color(0xFFF59E0B),
                                  boxShadow: [
                                    BoxShadow(
                                      color: Color(0x80F59E0B),
                                      blurRadius: 6,
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(width: 7),
                              const Text(
                                'NU LIPA MARKETING',
                                style: TextStyle(
                                  fontFamily: 'DM Sans',
                                  fontSize: 10,
                                  fontWeight: FontWeight.w700,
                                  color: Color(0x80FFFFFF),
                                  letterSpacing: 1.2,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          const Text(
                            'NUPost',
                            style: TextStyle(
                              fontFamily: 'DM Sans',
                              fontSize: 30,
                              fontWeight: FontWeight.w900,
                              color: Colors.white,
                              letterSpacing: -0.8,
                              height: 1.1,
                            ),
                          ),
                          const SizedBox(height: 3),
                          Text(
                            'Welcome back 👋',
                            style: TextStyle(
                              fontFamily: 'DM Sans',
                              fontSize: 13,
                              fontWeight: FontWeight.w400,
                              color: Colors.white.withOpacity(0.5),
                            ),
                          ),
                        ],
                      ),
                    ),
                    // Avatar
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(16),
                        color: Colors.white.withOpacity(0.12),
                        border: Border.all(
                          color: Colors.white.withOpacity(0.18),
                          width: 1.5,
                        ),
                      ),
                      alignment: Alignment.center,
                      child: Text(
                        _getInitials(),
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          color: Colors.white,
                          fontSize: 15,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 22),

                // Stat cards
                Row(
                  children: [
                    _StatCard(
                      label: 'Pending',
                      value: '$_pendingCount',
                      accentColor: const Color(0xFFF59E0B),
                      icon: Icons.hourglass_empty_rounded,
                    ),
                    const SizedBox(width: 10),
                    _StatCard(
                      label: 'Approved',
                      value: '$_approvedCount',
                      accentColor: const Color(0xFF05C46B),
                      icon: Icons.check_circle_outline_rounded,
                    ),
                    const SizedBox(width: 10),
                    _StatCard(
                      label: 'Posted',
                      value: '$_postedCount',
                      accentColor: const Color(0xFF4B7BF5),
                      icon: Icons.send_rounded,
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  String _getInitials() {
    final name = SessionStore.name ?? '';
    final parts = name
        .trim()
        .split(RegExp(r'\s+'))
        .where((e) => e.isNotEmpty)
        .toList();
    if (parts.isEmpty) return 'NP';
    if (parts.length == 1) return parts[0][0].toUpperCase();
    return '${parts[0][0]}${parts[1][0]}'.toUpperCase();
  }

  // ── Approval Banner ───────────────────────────────────────────────────────
  Widget _buildApprovalBanner() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Container(
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [Color(0xFFFFFBEB), Color(0xFFFEF3C7)],
          ),
          border: Border.all(color: const Color(0xFFF59E0B).withOpacity(0.22)),
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: const Color(0xFFF59E0B).withOpacity(0.08),
              blurRadius: 12,
              offset: const Offset(0, 4),
            ),
          ],
        ),
        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
        child: Row(
          children: [
            Container(
              width: 44,
              height: 44,
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFFF59E0B), Color(0xFFFBBF24)],
                ),
                borderRadius: BorderRadius.circular(14),
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x40F59E0B),
                    blurRadius: 12,
                    offset: Offset(0, 4),
                  ),
                ],
              ),
              child: const Icon(
                Icons.trending_up_rounded,
                color: Colors.white,
                size: 22,
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  Text(
                    'AVG. APPROVAL TIME',
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      fontSize: 10,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF92400E),
                      letterSpacing: 0.8,
                    ),
                  ),
                  SizedBox(height: 3),
                  Text(
                    '— days',
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      fontSize: 22,
                      fontWeight: FontWeight.w900,
                      color: Color(0xFF78350F),
                      letterSpacing: -0.5,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Quick Actions ─────────────────────────────────────────────────────────
  Widget _buildQuickActions() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Row(
        children: [
          Expanded(
            flex: 2,
            child: GestureDetector(
              onTap: () => Navigator.of(context).push(
                MaterialPageRoute(builder: (_) => const CreateRequestScreen()),
              ),
              child: Container(
                height: 48,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [Color(0xFF001540), Color(0xFF003080)],
                  ),
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: const [
                    BoxShadow(
                      color: Color(0x45001540),
                      blurRadius: 16,
                      offset: Offset(0, 5),
                    ),
                  ],
                ),
                alignment: Alignment.center,
                child: const Text(
                  '+ New Request',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    color: Colors.white,
                    fontSize: 14,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 0.2,
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: GestureDetector(
              onTap: () => Navigator.of(context).pushNamed('/calendar'),
              child: Container(
                height: 48,
                decoration: BoxDecoration(
                  color: Colors.white,
                  border: Border.all(
                    color: const Color(0xFF002366).withOpacity(0.15),
                    width: 1.5,
                  ),
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: const [
                    BoxShadow(
                      color: Color(0x0A001540),
                      blurRadius: 8,
                      offset: Offset(0, 3),
                    ),
                  ],
                ),
                alignment: Alignment.center,
                child: const Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      Icons.calendar_month_rounded,
                      size: 16,
                      color: Color(0xFF002366),
                    ),
                    SizedBox(width: 5),
                    Text(
                      'Cal',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        color: Color(0xFF002366),
                        fontSize: 14,
                        fontWeight: FontWeight.w800,
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

  // ── Section Header ────────────────────────────────────────────────────────
  Widget _buildSectionHeader(String title, String action) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 22, 20, 10),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            title.toUpperCase(),
            style: const TextStyle(
              fontFamily: 'DM Sans',
              fontSize: 11,
              fontWeight: FontWeight.w800,
              color: Color(0xFF9AA3B2),
              letterSpacing: 1.0,
            ),
          ),
          Text(
            '$action →',
            style: const TextStyle(
              fontFamily: 'DM Sans',
              fontSize: 12,
              fontWeight: FontWeight.w700,
              color: Color(0xFF2B5CE6),
            ),
          ),
        ],
      ),
    );
  }

  // ── Recent Requests ────────────────────────────────────────────────────────
  Widget _buildRecentRequests() {
    if (_isLoadingRequests) {
      return const Padding(
        padding: EdgeInsets.symmetric(horizontal: 16, vertical: 20),
        child: Center(
          child: CircularProgressIndicator(
            valueColor: AlwaysStoppedAnimation(Color(0xFF001540)),
          ),
        ),
      );
    }
    if (_recentRequests.isEmpty) {
      return Padding(
        padding: const EdgeInsets.symmetric(horizontal: 16),
        child: Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(vertical: 42),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: const Color(0x0F000000)),
            boxShadow: const [
              BoxShadow(
                color: Color(0x08001540),
                blurRadius: 12,
                offset: Offset(0, 4),
              ),
            ],
          ),
          child: const Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.inbox_rounded, size: 42, color: Color(0xFF9AA3B2)),
              SizedBox(height: 12),
              Text(
                'No requests yet',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF3D4A63),
                ),
              ),
            ],
          ),
        ),
      );
    }

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        children: _recentRequests.map((req) {
          final id = (req['id'] as num?)?.toInt() ?? 0;
          final reqNo = (req['request_id'] ?? '').toString();
          final title = (req['title'] ?? '').toString();
          final status = (req['status'] ?? 'Pending').toString();
          final dt = (req['created_at'] ?? '').toString();
          final String priority = (req['priority'] ?? 'Low').toString();

          List<String> platformsList = [];
          if (req['platform'] != null) {
            final platStr = req['platform'].toString();
            if (platStr.isNotEmpty)
              platformsList = platStr.split(',').map((x) => x.trim()).toList();
          }
          if (platformsList.isEmpty) platformsList.add('Facebook');

          return Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: GestureDetector(
              onTap: () {
                // Navigate to requests/tracking or similar?
                // Using bottom nav index jump isn't trivial here without callback,
                // so we just push to the RequestsScreen or similar if needed.
                // We'll leave it simple for now, or just leave it as aesthetic
              },
              child: _HomeRequestCard(
                id: id,
                number: reqNo.isEmpty ? 'REQ-$id' : reqNo,
                title: title,
                status: status,
                submittedAt: dt,
                priority: priority,
                platforms: platformsList,
              ),
            ),
          );
        }).toList(),
      ),
    );
  }
}

// ── Stat Card ─────────────────────────────────────────────────────────────────
class _StatCard extends StatelessWidget {
  final String label;
  final String value;
  final Color accentColor;
  final IconData icon;

  const _StatCard({
    required this.label,
    required this.value,
    required this.accentColor,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.fromLTRB(12, 14, 12, 12),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.09),
          border: Border.all(color: Colors.white.withOpacity(0.13)),
          borderRadius: BorderRadius.circular(18),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Top accent line
            Container(
              height: 2.5,
              decoration: BoxDecoration(
                color: accentColor,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const SizedBox(height: 10),
            Icon(icon, color: accentColor, size: 18),
            const SizedBox(height: 8),
            Text(
              value,
              style: const TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 26,
                fontWeight: FontWeight.w900,
                color: Colors.white,
                letterSpacing: -1.0,
                height: 1.0,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              label.toUpperCase(),
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 9.5,
                fontWeight: FontWeight.w700,
                color: Colors.white.withOpacity(0.5),
                letterSpacing: 0.7,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _HomeRequestCard extends StatelessWidget {
  final int id;
  final String number, title, status, submittedAt, priority;
  final List<String> platforms;

  const _HomeRequestCard({
    required this.id,
    required this.number,
    required this.title,
    required this.status,
    required this.submittedAt,
    required this.priority,
    required this.platforms,
  });

  Color get _statusColor {
    switch (status) {
      case 'Approved':
        return const Color(0xFF05C46B);
      case 'Posted':
        return const Color(0xFF8B5CF6);
      case 'Rejected':
        return const Color(0xFFFF3B30);
      default:
        return const Color(0xFFF59E0B);
    }
  }

  String _fmt(String dt) {
    if (dt.isEmpty) return 'Recent';
    try {
      final d = DateTime.parse(dt);
      const m = [
        'Jan',
        'Feb',
        'Mar',
        'Apr',
        'May',
        'Jun',
        'Jul',
        'Aug',
        'Sep',
        'Oct',
        'Nov',
        'Dec',
      ];
      return '${m[d.month - 1]} ${d.day}';
    } catch (_) {
      return 'Recent';
    }
  }

  @override
  Widget build(BuildContext context) => Container(
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(20),
      border: Border.all(color: const Color(0x0E000000)),
      boxShadow: const [
        BoxShadow(
          color: Color(0x07001540),
          blurRadius: 12,
          offset: Offset(0, 4),
        ),
      ],
    ),
    child: IntrinsicHeight(
      child: Row(
        children: [
          Container(
            width: 4,
            decoration: BoxDecoration(
              color: _statusColor,
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(20),
                bottomLeft: Radius.circular(20),
              ),
            ),
          ),
          Expanded(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(14, 14, 16, 14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 7,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          color: const Color(0xFFE9EDF6),
                          borderRadius: BorderRadius.circular(7),
                        ),
                        child: Text(
                          number,
                          style: const TextStyle(
                            fontFamily: 'DM Sans',
                            fontWeight: FontWeight.w700,
                            fontSize: 10,
                            color: Color(0xFF9AA3B2),
                            letterSpacing: 0.3,
                          ),
                        ),
                      ),
                      const Spacer(),
                      _HomeChip(status: status),
                    ],
                  ),
                  const SizedBox(height: 9),
                  Text(
                    title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontFamily: 'DM Sans',
                      fontWeight: FontWeight.w800,
                      fontSize: 16,
                      color: Color(0xFF080F1E),
                      letterSpacing: -0.2,
                      height: 1.3,
                    ),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      const Icon(
                        Icons.calendar_today_rounded,
                        size: 13,
                        color: Color(0xFF9AA3B2),
                      ),
                      const SizedBox(width: 4),
                      Text(
                        '${_fmt(submittedAt)}  •  ${platforms.isNotEmpty ? platforms.first : "Facebook"}',
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontSize: 11.5,
                          fontWeight: FontWeight.w500,
                          color: Color(0xFF9AA3B2),
                        ),
                      ),
                      const Spacer(),
                      _PriorityBadge(priority: priority),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    ),
  );
}

class _HomeChip extends StatelessWidget {
  final String status;
  const _HomeChip({required this.status});
  @override
  Widget build(BuildContext context) {
    Color bg, fg;
    switch (status) {
      case 'Approved':
        bg = const Color(0xFFD1FAE5);
        fg = const Color(0xFF065F46);
        break;
      case 'Posted':
        bg = const Color(0xFFEDE9FE);
        fg = const Color(0xFF5B21B6);
        break;
      case 'Rejected':
        bg = const Color(0xFFFEE2E2);
        fg = const Color(0xFF991B1B);
        break;
      default:
        bg = const Color(0xFFFEF3C7);
        fg = const Color(0xFF92400E);
    }
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(99),
      ),
      child: Text(
        status,
        style: TextStyle(
          fontFamily: 'DM Sans',
          fontWeight: FontWeight.w800,
          fontSize: 10.5,
          color: fg,
        ),
      ),
    );
  }
}

class _PriorityBadge extends StatelessWidget {
  final String priority;
  const _PriorityBadge({required this.priority});

  @override
  Widget build(BuildContext context) {
    Color bg = const Color(0xFFF3F4F6); // Low
    Color fg = const Color(0xFF4B5563);

    if (priority == 'Medium') {
      bg = const Color(0xFFFEF3C7);
      fg = const Color(0xFFD97706);
    } else if (priority == 'High') {
      bg = const Color(0xFFFEE2E2);
      fg = const Color(0xFFDC2626);
    } else if (priority == 'Urgent') {
      bg = const Color(0xFFFECACA);
      fg = const Color(0xFF991B1B);
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(99),
      ),
      child: Text(
        priority.isEmpty ? 'Low' : priority,
        style: TextStyle(
          fontFamily: 'DM Sans',
          fontSize: 10,
          fontWeight: FontWeight.bold,
          color: fg,
        ),
      ),
    );
  }
}
