import 'package:flutter/material.dart';
import '../app_bottom_nav.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';

class PostCalendarScreen extends StatefulWidget {
  const PostCalendarScreen({super.key});

  @override
  State<PostCalendarScreen> createState() => _PostCalendarScreenState();
}

class _PostCalendarScreenState extends State<PostCalendarScreen> {
  // Current displayed month
  DateTime _focusedMonth = DateTime(DateTime.now().year, DateTime.now().month);
  bool _isMonthView = true; // Today / Month toggle

  // All posts for the current month with both request and scheduled dates
  final List<_CalendarPost> _posts = [];
  bool _isPublicCalendar = false;

  @override
  void initState() {
    super.initState();
    _loadScheduledPosts();
  }

  Future<void> _loadScheduledPosts({bool? forcePublicView, bool updateToggle = false}) async {
    final publicView = forcePublicView ?? _isPublicCalendar;

    try {
      final userId = SessionStore.userId;
      if (userId == null || userId == 0) {
        return;
      }

      final calendarData = await ApiService.fetchCalendar(
        userId: userId,
        month: _focusedMonth.month,
        year: _focusedMonth.year,
        publicView: publicView,
      );

      if (calendarData['success'] == true) {
        final data = calendarData['data'] ?? {};
        final postsList = data['posts'] ?? [];
        final nextPosts = <_CalendarPost>[];

        print('PUBLIC VIEW: $publicView, Posts count: ${postsList is List ? postsList.length : 0}');

        if (postsList is List) {
          for (var post in postsList) {
            final status = post['status'] ?? 'Pending';
            final requester = (post['requester'] ?? '').toString();
            final isMine =
                requester.isNotEmpty && requester == (SessionStore.name ?? '');

            DateTime? requestDate;
            DateTime? scheduledDate;

            if (post['request_date'] != null && post['request_date'] != '') {
              requestDate = DateTime.tryParse(post['request_date']);
            }
            if (post['scheduled_date'] != null &&
                post['scheduled_date'] != '') {
              scheduledDate = DateTime.tryParse(post['scheduled_date']);
            }

            nextPosts.add(
              _CalendarPost(
                label: post['title'] ?? 'Post',
                status: status,
                isMine: isMine,
                color: _CalendarPost.getStatusColor(status),
                requestDate: requestDate,
                scheduledDate: scheduledDate,
              ),
            );
          }
        }

        if (mounted) {
          // Update BOTH posts AND toggle in ONE setState
          setState(() {
            _posts
              ..clear()
              ..addAll(nextPosts);
            if (updateToggle && forcePublicView != null) {
              _isPublicCalendar = forcePublicView;
            }
          });
        }
      }
    } catch (e) {
      print('Error: $e');
    }
  }

  DateTime get _today => DateTime.now();

  void _prevMonth() {
    setState(() {
      _focusedMonth = DateTime(_focusedMonth.year, _focusedMonth.month - 1);
    });
    _loadScheduledPosts();
  }

  void _nextMonth() {
    setState(() {
      _focusedMonth = DateTime(_focusedMonth.year, _focusedMonth.month + 1);
    });
    _loadScheduledPosts();
  }

  void _goToToday() {
    setState(() {
      _focusedMonth = DateTime(_today.year, _today.month);
      _isMonthView = false;
    });
  }

  String _monthLabel() {
    const months = [
      'January',
      'February',
      'March',
      'April',
      'May',
      'June',
      'July',
      'August',
      'September',
      'October',
      'November',
      'December',
    ];
    return '${months[_focusedMonth.month - 1]} ${_focusedMonth.year}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF9FAFB),
      body: Stack(
        children: [
          Column(
            children: [
              _buildHeader(),
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 90),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // ── Calendar card ──────────────────
                      _buildCalendarCard(),

                      const SizedBox(height: 16),

                      // ── Upcoming Scheduled Posts ───────
                      Text(
                        _isPublicCalendar
                            ? 'Upcoming Public Posts'
                            : 'Your Upcoming Posts',
                        style: const TextStyle(
                          fontFamily: 'Inter',
                          fontWeight: FontWeight.w600,
                          fontSize: 13,
                          color: Color(0xFF4A5565),
                        ),
                      ),

                      const SizedBox(height: 12),

                      _buildUpcomingEmpty(),
                    ],
                  ),
                ),
              ),
            ],
          ),

          // ── Bottom Nav ─────────────────────────────────
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: const AppBottomNav(currentIndex: -1),
          ),
        ],
      ),
    );
  }

  // ── Header ────────────────────────────────────────────
  Widget _buildHeader() {
    return Container(
      padding: EdgeInsets.only(
        top: MediaQuery.of(context).padding.top + 12,
        bottom: 16,
        left: 24,
        right: 24,
      ),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: const [
          BoxShadow(
            color: Color(0x1A000000),
            blurRadius: 3,
            offset: Offset(0, 1),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  GestureDetector(
                    onTap: () => Navigator.of(context).pop(),
                    child: const Icon(
                      Icons.arrow_back,
                      size: 24,
                      color: Color(0xFF003366),
                    ),
                  ),
                  const SizedBox(width: 12),
                  const Text(
                    'Post Calendar',
                    style: TextStyle(
                      fontFamily: 'Inter',
                      fontWeight: FontWeight.w600,
                      fontSize: 18.3,
                      color: Color(0xFF003366),
                    ),
                  ),
                ],
              ),
              Transform.scale(
                scale: 0.75,
                child: Switch(
                  value: _isPublicCalendar,
                  onChanged: (val) async {
                    // Update backend FIRST so the query returns correct data
                    try {
                      await ApiService.updatePublicCalendar(
                        userId: SessionStore.userId ?? 0,
                        isPublic: val,
                      );
                    } catch (_) {}

                    // THEN load data (database is now updated)
                    await _loadScheduledPosts(forcePublicView: val, updateToggle: true);
                  },
                  activeThumbColor: const Color(0xFF003366),
                ),
              ),
            ],
          ),
          const SizedBox(height: 4),
          const Padding(
            padding: EdgeInsets.only(left: 36),
            child: Text(
              'Schedule and track posts',
              style: TextStyle(
                fontFamily: 'Inter',
                fontWeight: FontWeight.w400,
                fontSize: 12.8,
                color: Color(0xFF4A5565),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Calendar card ─────────────────────────────────────
  Widget _buildCalendarCard() {
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
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          // Month nav row
          _buildMonthNav(),

          const SizedBox(height: 16),

          // Today / Month toggle
          _buildToggle(),

          const SizedBox(height: 16),

          // Calendar grid
          _buildCalendarGrid(),

          const SizedBox(height: 16),

          // Legend
          _buildLegend(),
        ],
      ),
    );
  }

  // ── Month navigation ──────────────────────────────────
  Widget _buildMonthNav() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        GestureDetector(
          onTap: _prevMonth,
          child: Container(
            width: 36,
            height: 36,
            decoration: const BoxDecoration(shape: BoxShape.circle),
            child: const Icon(
              Icons.chevron_left,
              color: Color(0xFF003366),
              size: 20,
            ),
          ),
        ),
        Text(
          _monthLabel(),
          style: const TextStyle(
            fontFamily: 'Inter',
            fontWeight: FontWeight.w600,
            fontSize: 16.3,
            color: Color(0xFF003366),
          ),
        ),
        GestureDetector(
          onTap: _nextMonth,
          child: Container(
            width: 36,
            height: 36,
            decoration: const BoxDecoration(shape: BoxShape.circle),
            child: const Icon(
              Icons.chevron_right,
              color: Color(0xFF003366),
              size: 20,
            ),
          ),
        ),
      ],
    );
  }

  // ── Today / Month toggle ──────────────────────────────
  Widget _buildToggle() {
    return Row(
      children: [
        // Today button
        Expanded(
          child: GestureDetector(
            onTap: _goToToday,
            child: Container(
              height: 36,
              decoration: BoxDecoration(
                color: !_isMonthView ? const Color(0xFF003366) : Colors.white,
                border: Border.all(color: Colors.black.withOpacity(0.1)),
                borderRadius: BorderRadius.circular(8),
              ),
              alignment: Alignment.center,
              child: Text(
                'Today',
                style: TextStyle(
                  fontFamily: 'Inter',
                  fontWeight: FontWeight.w600,
                  fontSize: 12.8,
                  color: !_isMonthView ? Colors.white : const Color(0xFF0A0A0A),
                ),
              ),
            ),
          ),
        ),
        const SizedBox(width: 8),
        // Month button
        Expanded(
          child: GestureDetector(
            onTap: () => setState(() => _isMonthView = true),
            child: Container(
              height: 36,
              decoration: BoxDecoration(
                color: _isMonthView ? const Color(0xFF003366) : Colors.white,
                border: Border.all(color: Colors.black.withOpacity(0.1)),
                borderRadius: BorderRadius.circular(8),
              ),
              alignment: Alignment.center,
              child: Text(
                'Month',
                style: TextStyle(
                  fontFamily: 'Inter',
                  fontWeight: FontWeight.w600,
                  fontSize: 13.7,
                  color: _isMonthView ? Colors.white : const Color(0xFF0A0A0A),
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }

  // ── Calendar grid ─────────────────────────────────────
  Widget _buildCalendarGrid() {
    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    // First day of month (0=Sun)
    final firstDay = DateTime(_focusedMonth.year, _focusedMonth.month, 1);
    final startOffset = firstDay.weekday % 7;

    // Days in month
    final daysInMonth = DateTime(
      _focusedMonth.year,
      _focusedMonth.month + 1,
      0,
    ).day;

    return Column(
      children: [
        // Day headers
        Row(
          children: days
              .map(
                (d) => Expanded(
                  child: Center(
                    child: Text(
                      d,
                      style: const TextStyle(
                        fontFamily: 'Inter',
                        fontWeight: FontWeight.w400,
                        fontSize: 11.1,
                        color: Color(0xFF4A5565),
                      ),
                    ),
                  ),
                ),
              )
              .toList(),
        ),

        const SizedBox(height: 4),

        // Grid
        LayoutBuilder(
          builder: (context, constraints) {
            final cellWidth = constraints.maxWidth / 7;
            const cellHeight = 80.0;
            final rows = ((startOffset + daysInMonth) / 7).ceil();

            return SizedBox(
              height: cellHeight * rows,
              child: Stack(
                children: List.generate(rows * 7, (index) {
                  final col = index % 7;
                  final row = index ~/ 7;
                  final dayNum = index - startOffset + 1;
                  final isValidDay = dayNum >= 1 && dayNum <= daysInMonth;

                  final isToday =
                      isValidDay &&
                      _focusedMonth.year == _today.year &&
                      _focusedMonth.month == _today.month &&
                      dayNum == _today.day;

                  // Get posts for this specific day
                  final cellDate = DateTime(_focusedMonth.year, _focusedMonth.month, dayNum);
                  final scheduledPosts = isValidDay
                      ? _posts.where((p) =>
                          p.scheduledDate != null &&
                          p.scheduledDate!.year == cellDate.year &&
                          p.scheduledDate!.month == cellDate.month &&
                          p.scheduledDate!.day == cellDate.day
                        ).toList()
                      : <_CalendarPost>[];

                  final requestPosts = isValidDay && !_isPublicCalendar
                      ? _posts.where((p) =>
                          p.requestDate != null &&
                          p.requestDate!.year == cellDate.year &&
                          p.requestDate!.month == cellDate.month &&
                          p.requestDate!.day == cellDate.day
                        ).toList()
                      : <_CalendarPost>[];

                  final showPublicCount = _isPublicCalendar && isValidDay;
                  final publicCount = scheduledPosts.length;
                  final dayLoadColor = _publicCountColor(publicCount);

                  return Positioned(
                    left: col * cellWidth,
                    top: row * cellHeight,
                    width: cellWidth,
                    height: cellHeight,
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        border: Border.all(
                          color: const Color(0xFFF3F4F6),
                          width: 1,
                        ),
                      ),
                      padding: const EdgeInsets.all(4),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (showPublicCount && publicCount > 0)
                            Container(
                              height: 3,
                              decoration: BoxDecoration(
                                color: dayLoadColor,
                                borderRadius: BorderRadius.circular(2),
                              ),
                            ),
                          if (showPublicCount && publicCount > 0)
                            const SizedBox(height: 2),
                          // Day number
                          if (isValidDay)
                            Container(
                              width: double.infinity,
                              alignment: Alignment.center,
                              decoration: isToday
                                  ? BoxDecoration(
                                      border: Border.all(
                                        color: const Color(0xFF2B7FFF),
                                        width: 2,
                                      ),
                                      borderRadius: BorderRadius.circular(4),
                                    )
                                  : null,
                              child: Text(
                                '$dayNum',
                                style: TextStyle(
                                  fontFamily: 'Inter',
                                  fontWeight: FontWeight.w400,
                                  fontSize: 12,
                                  color: isToday
                                      ? const Color(0xFF2B7FFF)
                                      : const Color(0xFF364153),
                                ),
                              ),
                            ),
                          // Public count badge
                          if (showPublicCount && publicCount > 0)
                            Container(
                              height: 3,
                              decoration: BoxDecoration(
                                color: dayLoadColor,
                                borderRadius: BorderRadius.circular(2),
                              ),
                            ),
                          // Public view: show scheduled posts with status colors
                          if (_isPublicCalendar)
                            ...scheduledPosts
                                .take(1)
                                .map(
                                  (post) => Container(
                                    margin: const EdgeInsets.only(top: 2),
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 4,
                                      vertical: 2,
                                    ),
                                    decoration: BoxDecoration(
                                      color: post.color, // Status color
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: Text(
                                      post.label,
                                      style: const TextStyle(
                                        fontFamily: 'Inter',
                                        fontWeight: FontWeight.w400,
                                        fontSize: 6.9,
                                        color: Colors.white,
                                      ),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                ),
                          // Private view: show request dates (grey) and scheduled dates (blue)
                          if (!_isPublicCalendar)
                            ...requestPosts
                                .take(1)
                                .map(
                                  (post) => Container(
                                    margin: const EdgeInsets.only(top: 2),
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 4,
                                      vertical: 2,
                                    ),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFF9CA3AF), // Grey for request date
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: Text(
                                      post.label,
                                      style: const TextStyle(
                                        fontFamily: 'Inter',
                                        fontWeight: FontWeight.w400,
                                        fontSize: 6.9,
                                        color: Colors.white,
                                      ),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                ),
                          if (!_isPublicCalendar)
                            ...scheduledPosts
                                .take(1)
                                .map(
                                  (post) => Container(
                                    margin: const EdgeInsets.only(top: 2),
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 4,
                                      vertical: 2,
                                    ),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFF3B82F6), // Blue for scheduled date
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: Text(
                                      post.label,
                                      style: const TextStyle(
                                        fontFamily: 'Inter',
                                        fontWeight: FontWeight.w400,
                                        fontSize: 6.9,
                                        color: Colors.white,
                                      ),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                ),
                          // Public view: show scheduled posts from others (status-based colors)
                          if (_isPublicCalendar)
                            ...scheduledPosts
                                .take(1)
                                .map(
                                  (post) => Container(
                                    margin: const EdgeInsets.only(top: 2),
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 4,
                                      vertical: 2,
                                    ),
                                    decoration: BoxDecoration(
                                      color: post.color, // Status-based color
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: Text(
                                      post.label,
                                      style: const TextStyle(
                                        fontFamily: 'Inter',
                                        fontWeight: FontWeight.w400,
                                        fontSize: 6.9,
                                        color: Colors.white,
                                      ),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                ),
                        ],
                      ),
                    ),
                  );
                }),
              ),
            );
          },
        ),
      ],
    );
  }

  Color _publicCountColor(int count) {
    if (count >= 5) return const Color(0xFFDC2626);
    if (count >= 3) return const Color(0xFFF59E0B);
    return const Color(0xFF16A34A);
  }

  // ── Legend ────────────────────────────────────────────
  Widget _buildLegend() {
    return Container(
      padding: const EdgeInsets.only(top: 16),
      decoration: const BoxDecoration(
        border: Border(top: BorderSide(color: Color(0xFFE5E7EB))),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (!_isPublicCalendar) ...[
            Row(
              children: [
                _LegendItem(
                  color: const Color(0xFF9CA3AF),
                  label: 'Request Date',
                  isFilled: true,
                ),
                const SizedBox(width: 16),
                _LegendItem(
                  color: const Color(0xFF3B82F6),
                  label: 'Scheduled Date',
                  isFilled: true,
                ),
              ],
            ),
          ] else ...[
            Row(
              children: [
                _LegendItem(
                  color: const Color(0xFF059669),
                  label: 'Approved',
                  isFilled: true,
                ),
                const SizedBox(width: 16),
                _LegendItem(
                  color: const Color(0xFF7C3AED),
                  label: 'Posted',
                  isFilled: true,
                ),
                const SizedBox(width: 16),
                _LegendItem(
                  color: const Color(0xFFD97706),
                  label: 'Review',
                  isFilled: true,
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  // ── Upcoming empty state ──────────────────────────────
  Widget _buildUpcomingEmpty() {
    return Container(
      width: double.infinity,
      height: 170,
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
        children: const [
          Icon(
            Icons.calendar_today_outlined,
            size: 48,
            color: Color(0xFFD1D5DC),
          ),
          SizedBox(height: 12),
          Text(
            'No upcoming posts scheduled for the next 7 days',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontFamily: 'Inter',
              fontWeight: FontWeight.w400,
              fontSize: 13,
              color: Color(0xFF6A7282),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Calendar Post model ───────────────────────────────────────────────────────
class _CalendarPost {
  final String label;
  final String status;
  final Color color;
  final bool isMine;
  final DateTime? requestDate;
  final DateTime? scheduledDate;

  const _CalendarPost({
    required this.label,
    required this.status,
    required this.color,
    required this.isMine,
    this.requestDate,
    this.scheduledDate,
  });

  static Color getStatusColor(String status) {
    final st = status.toLowerCase();
    if (st.contains('posted')) return const Color(0xFF7C3AED); // Purple
    if (st.contains('approved')) return const Color(0xFF059669); // Green
    if (st.contains('under review')) return const Color(0xFFD97706); // Orange
    return const Color(0xFF6B7280); // Gray (Pending)
  }
}

// ── Legend Item ───────────────────────────────────────────────────────────────
class _LegendItem extends StatelessWidget {
  final Color color;
  final String label;
  final bool isFilled;

  const _LegendItem({
    required this.color,
    required this.label,
    required this.isFilled,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 12,
          height: 12,
          decoration: BoxDecoration(
            color: isFilled ? color : Colors.transparent,
            border: isFilled ? null : Border.all(color: color, width: 2),
            borderRadius: BorderRadius.circular(4),
          ),
        ),
        const SizedBox(width: 8),
        Text(
          label,
          style: const TextStyle(
            fontFamily: 'Inter',
            fontWeight: FontWeight.w400,
            fontSize: 10.9,
            color: Color(0xFF4A5565),
          ),
        ),
      ],
    );
  }
}

// ── Nav Item ──────────────────────────────────────────────────────────────────
