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

class _HomeScreenState extends State<HomeScreen> {
  final int _currentIndex = 0;
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
      backgroundColor: AppColors.pageBg,
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
                            fontFamily: 'DM Sans',
                            fontWeight: FontWeight.w600,
                            fontSize: 17,
                            color: AppColors.ink,
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
          const FloatingMessageButton(),
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
          colors: [
            AppColors.primaryDark,
            AppColors.primary,
            AppColors.primaryLight,
          ],
        ),
        borderRadius: BorderRadius.only(
          bottomLeft: Radius.circular(AppRadius.xl),
          bottomRight: Radius.circular(AppRadius.xl),
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
              fontFamily: 'DM Sans',
              fontWeight: FontWeight.w700,
              fontSize: 24,
              color: Colors.white,
              letterSpacing: 0.2,
            ),
          ),
          SizedBox(height: 4),
          Text(
            'NU Lipa Marketing Office',
            style: TextStyle(
              fontFamily: 'DM Sans',
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
          colors: [Color(0xFFFFFBEB), AppColors.goldBg],
        ),
        border: Border.all(color: AppColors.goldLight),
        borderRadius: BorderRadius.circular(AppRadius.lg),
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
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w500,
                  fontSize: 13.2,
                  color: AppColors.inkMid,
                ),
              ),
              SizedBox(height: 2),
              Text(
                '— days',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w700,
                  fontSize: 18,
                  color: AppColors.goldDark,
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
          backgroundColor: AppColors.primary,
          foregroundColor: Colors.white,
          elevation: 4,
          shadowColor: const Color(0x33002366),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppRadius.md),
          ),
        ),
        child: const Text(
          '+ Create New Request',
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontWeight: FontWeight.w600,
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
          side: const BorderSide(color: AppColors.primary, width: 1.5),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppRadius.md),
          ),
        ),
        child: const Text(
          'View Post Calendar',
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontWeight: FontWeight.w600,
            fontSize: 15,
            color: AppColors.primary,
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
        color: AppColors.surface,
        border: Border.all(color: AppColors.border),
        borderRadius: BorderRadius.circular(AppRadius.lg),
        boxShadow: const [
          BoxShadow(
            color: Color(0x14000000),
            blurRadius: 10,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: const Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.inbox_outlined, size: 40, color: AppColors.inkMute),
          SizedBox(height: 12),
          Text(
            'No requests yet',
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontWeight: FontWeight.w500,
              fontSize: 13,
              color: AppColors.inkMid,
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
        color: AppColors.surface,
        border: Border.all(color: AppColors.border),
        borderRadius: BorderRadius.circular(AppRadius.lg),
        boxShadow: const [
          BoxShadow(
            color: Color(0x14000000),
            blurRadius: 10,
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
              fontFamily: 'DM Sans',
              fontWeight: FontWeight.w700,
              fontSize: 24,
              color: AppColors.ink,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: const TextStyle(
              fontFamily: 'DM Sans',
              fontWeight: FontWeight.w500,
              fontSize: 11.1,
              color: AppColors.inkMid,
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
