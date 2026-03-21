import 'package:flutter/material.dart';
import 'create_request_screen.dart';
import '../app_bottom_nav.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  int _currentIndex = 0;
  int _pendingCount = 0;
  int _approvedCount = 0;
  int _postedCount = 0;

  @override
  void initState() {
    super.initState();
    _loadStats();
  }

  Future<void> _loadStats() async {
    try {
      final userId = SessionStore.userId;
      if (userId == null || userId == 0) {
        return;
      }

      final profileData = await ApiService.fetchProfile(userId: userId);
      if (profileData['success'] == true) {
        final stats = profileData['data']['stats'] ?? {};
        setState(() {
          _pendingCount = stats['pending'] ?? 0;
          _approvedCount = stats['approved'] ?? 0;
          _postedCount = stats['total'] ?? 0;
        });
      }
    } catch (e) {
      print('Error loading home stats: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF9FAFB),
      body: Stack(
        children: [
          // ── Scrollable content ───────────────────────────
          Column(
            children: [
              Expanded(
                child: SingleChildScrollView(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // ── Header ─────────────────────────
                      _buildHeader(),

                      const SizedBox(height: 16),

                      // ── Stat cards row ─────────────────
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: _buildStatCards(),
                      ),

                      const SizedBox(height: 16),

                      // ── Average Approval Time banner ───
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: _buildApprovalTimeBanner(),
                      ),

                      const SizedBox(height: 16),

                      // ── Create New Request button ──────
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: _buildCreateButton(),
                      ),
                      const SizedBox(height: 12),

                      // ── View Post Calendar button ──────
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: _buildCalendarButton(),
                      ),
                      const SizedBox(height: 24),

                      // ── Recent Requests heading ────────
                      const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 16),
                        child: Text(
                          'Recent Requests',
                          style: TextStyle(
                            fontFamily: 'Inter',
                            fontWeight: FontWeight.w500,
                            fontSize: 16.5,
                            color: Color(0xFF1E2939),
                          ),
                        ),
                      ),

                      const SizedBox(height: 12),

                      // ── Empty recent requests ──────────
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: _buildEmptyRequests(),
                      ),

                      // Bottom padding for nav bar
                      const SizedBox(height: 100),
                    ],
                  ),
                ),
              ),
            ],
          ),

          // ── Bottom Nav Bar ───────────────────────────────
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: AppBottomNav(currentIndex: _currentIndex),
          ),
        ],
      ),
    );
  }

  // ── Header ──────────────────────────────────────────────
  Widget _buildHeader() {
    return Container(
      width: double.infinity,
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
        left: 24,
        right: 24,
        top: MediaQuery.of(context).padding.top + 20,
        bottom: 20,
      ),
      child: const Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'NUPost',
            style: TextStyle(
              fontFamily: 'Inter',
              fontWeight: FontWeight.w500,
              fontSize: 22.5,
              color: Colors.white,
            ),
          ),
          SizedBox(height: 4),
          Text(
            'NU Lipa Marketing Office',
            style: TextStyle(
              fontFamily: 'Inter',
              fontWeight: FontWeight.w400,
              fontSize: 13.1,
              color: Color(0xFFDBEAFE),
            ),
          ),
        ],
      ),
    );
  }

  // ── Stat Cards ──────────────────────────────────────────
  Widget _buildStatCards() {
    return Row(
      children: [
        Expanded(
          child: _StatCard(
            icon: _buildClockIcon(),
            value: '$_pendingCount',
            label: 'Pending',
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: _StatCard(
            icon: _buildCheckIcon(),
            value: '$_approvedCount',
            label: 'Approved',
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: _StatCard(
            icon: _buildShareIcon(),
            value: '$_postedCount',
            label: 'Posted',
          ),
        ),
      ],
    );
  }

  // Clock icon (orange)
  Widget _buildClockIcon() {
    return SizedBox(
      width: 32,
      height: 32,
      child: CustomPaint(painter: _ClockIconPainter()),
    );
  }

  // Check icon (green)
  Widget _buildCheckIcon() {
    return SizedBox(
      width: 32,
      height: 32,
      child: CustomPaint(painter: _CheckIconPainter()),
    );
  }

  // Share icon (blue)
  Widget _buildShareIcon() {
    return SizedBox(
      width: 32,
      height: 32,
      child: CustomPaint(painter: _ShareIconPainter()),
    );
  }

  // ── Approval Time Banner ─────────────────────────────────
  Widget _buildApprovalTimeBanner() {
    return Container(
      height: 82,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFFFFFBEB), Color(0xFFFEFCE8)],
        ),
        border: Border.all(color: const Color(0xFFFEE685)),
        borderRadius: BorderRadius.circular(14),
        boxShadow: const [
          BoxShadow(
            color: Color(0x1A000000),
            blurRadius: 6,
            offset: Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.symmetric(horizontal: 17),
      child: Row(
        children: [
          // Trending up icon
          SizedBox(
            width: 40,
            height: 40,
            child: CustomPaint(painter: _TrendingIconPainter()),
          ),
          const SizedBox(width: 12),
          Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: const [
              Text(
                'Average Approval Time',
                style: TextStyle(
                  fontFamily: 'Inter',
                  fontWeight: FontWeight.w400,
                  fontSize: 13.2,
                  color: Color(0xFF4A5565),
                ),
              ),
              SizedBox(height: 2),
              Text(
                '— days',
                style: TextStyle(
                  fontFamily: 'Inter',
                  fontWeight: FontWeight.w400,
                  fontSize: 17.8,
                  color: Color(0xFFBB4D00),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ── Create New Request Button ────────────────────────────
  Widget _buildCreateButton() {
    return SizedBox(
      width: double.infinity,
      height: 48,
      child: ElevatedButton(
        onPressed: () {
          Navigator.of(context).push(
            MaterialPageRoute(builder: (_) => const CreateRequestScreen()),
          );
        },
        style: ElevatedButton.styleFrom(
          backgroundColor: const Color(0xFF003366),
          foregroundColor: Colors.white,
          elevation: 4,
          shadowColor: const Color(0x1A000000),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        ),
        child: const Text(
          '+ Create New Request',
          style: TextStyle(
            fontFamily: 'Inter',
            fontWeight: FontWeight.w500,
            fontSize: 15,
            color: Colors.white,
          ),
        ),
      ),
    );
  }

  // ── View Post Calendar Button ────────────────────────────
  Widget _buildCalendarButton() {
    return SizedBox(
      width: double.infinity,
      height: 48,
      child: OutlinedButton(
        onPressed: () {
          Navigator.of(context).pushNamed('/calendar');
        },
        style: OutlinedButton.styleFrom(
          side: const BorderSide(color: Color(0xFF003366), width: 1.5),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        ),
        child: const Text(
          'View Post Calendar',
          style: TextStyle(
            fontFamily: 'Inter',
            fontWeight: FontWeight.w500,
            fontSize: 15,
            color: Color(0xFF003366),
          ),
        ),
      ),
    );
  }

  // ── Empty Recent Requests ────────────────────────────────
  Widget _buildEmptyRequests() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 40),
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
      child: const Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.inbox_outlined, size: 40, color: Color(0xFF99A1AF)),
          SizedBox(height: 12),
          Text(
            'No requests yet',
            style: TextStyle(
              fontFamily: 'Inter',
              fontWeight: FontWeight.w400,
              fontSize: 13,
              color: Color(0xFF4A5565),
            ),
          ),
        ],
      ),
    );
  }

  // ── Bottom Nav Bar ───────────────────────────────────────
}

// ── Stat Card Widget ─────────────────────────────────────────────────────────
class _StatCard extends StatelessWidget {
  final Widget icon;
  final String value;
  final String label;

  const _StatCard({
    required this.icon,
    required this.value,
    required this.label,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 110,
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
          icon,
          const SizedBox(height: 10),
          Text(
            value,
            style: const TextStyle(
              fontFamily: 'Inter',
              fontWeight: FontWeight.w400,
              fontSize: 24,
              color: Color(0xFF0A0A0A),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: const TextStyle(
              fontFamily: 'Inter',
              fontWeight: FontWeight.w400,
              fontSize: 11.1,
              color: Color(0xFF4A5565),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Custom Icon Painters ─────────────────────────────────────────────────────

class _ClockIconPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFFFD9A00)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2.67
      ..strokeCap = StrokeCap.round;
    // Circle
    canvas.drawCircle(
      Offset(size.width / 2, size.height / 2),
      size.width * 0.42,
      paint,
    );
    // Hour hand
    canvas.drawLine(
      Offset(size.width / 2, size.height / 2),
      Offset(size.width / 2, size.height * 0.25),
      paint,
    );
    // Minute hand
    canvas.drawLine(
      Offset(size.width / 2, size.height / 2),
      Offset(size.width * 0.67, size.height / 2),
      paint,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}

class _CheckIconPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFF00C950)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2.67
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;
    // Circle
    canvas.drawCircle(
      Offset(size.width / 2, size.height / 2),
      size.width * 0.42,
      paint,
    );
    // Checkmark
    final path = Path()
      ..moveTo(size.width * 0.25, size.height * 0.5)
      ..lineTo(size.width * 0.42, size.height * 0.67)
      ..lineTo(size.width * 0.75, size.height * 0.33);
    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}

class _ShareIconPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFF2B7FFF)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 2.67
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;
    // Three dots
    final dotPaint = Paint()
      ..color = const Color(0xFF2B7FFF)
      ..style = PaintingStyle.fill;
    canvas.drawCircle(
      Offset(size.width * 0.75, size.height * 0.2),
      3,
      dotPaint,
    );
    canvas.drawCircle(
      Offset(size.width * 0.25, size.height * 0.5),
      3,
      dotPaint,
    );
    canvas.drawCircle(
      Offset(size.width * 0.75, size.height * 0.8),
      3,
      dotPaint,
    );
    // Lines
    canvas.drawLine(
      Offset(size.width * 0.75, size.height * 0.2),
      Offset(size.width * 0.25, size.height * 0.5),
      paint,
    );
    canvas.drawLine(
      Offset(size.width * 0.25, size.height * 0.5),
      Offset(size.width * 0.75, size.height * 0.8),
      paint,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}

class _TrendingIconPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFFE17100)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3.33
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;
    final path = Path()
      ..moveTo(size.width * 0.1, size.height * 0.65)
      ..lineTo(size.width * 0.35, size.height * 0.4)
      ..lineTo(size.width * 0.55, size.height * 0.55)
      ..lineTo(size.width * 0.9, size.height * 0.25);
    canvas.drawPath(path, paint);
    // Arrow head
    final arrow = Path()
      ..moveTo(size.width * 0.72, size.height * 0.25)
      ..lineTo(size.width * 0.9, size.height * 0.25)
      ..lineTo(size.width * 0.9, size.height * 0.43);
    canvas.drawPath(arrow, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}
