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

  // Scheduled posts map: day → list of post labels (empty = no posts)
  final Map<int, List<_CalendarPost>> _posts = {};
  bool _isPublicCalendar = false;

  @override
  void initState() {
    super.initState();
    _loadScheduledPosts();
    _loadPublicCalendarStatus();
  }

  Future<void> _loadPublicCalendarStatus() async {
    final userId = SessionStore.userId;
    if (userId == null || userId == 0) return;

    try {
      final result = await ApiService.fetchProfile(userId: userId);
      final data = (result['data'] as Map<String, dynamic>?) ?? {};
      setState(() {
        _isPublicCalendar =
            ((data['public_calendar'] as num?)?.toInt() ?? 0) == 1;
      });
      await _loadScheduledPosts();
    } catch (e) {
      print('Error loading public calendar status: $e');
    }
  }

  Future<void> _loadScheduledPosts() async {
    try {
      final userId = SessionStore.userId;
      if (userId == null || userId == 0) {
        return;
      }

      final calendarData = await ApiService.fetchCalendar(
        userId: userId,
        month: _focusedMonth.month,
        year: _focusedMonth.year,
        publicView: _isPublicCalendar,
      );

      if (calendarData['success'] == true) {
        final data = calendarData['data'] ?? {};
        final postsMap = data['posts'] ?? {};

        setState(() {
          _posts.clear();
          // postsMap is keyed by day number, with list of posts
          postsMap.forEach((dayStr, postsList) {
            final day = int.tryParse(dayStr.toString()) ?? 0;
            if (day > 0 && postsList is List) {
              _posts[day] = [];
              for (var post in postsList) {
                final status = post['status'] ?? 'Pending';
                final requester = (post['requester'] ?? '').toString();
                final isMine =
                    requester.isNotEmpty &&
                    requester == (SessionStore.name ?? '');
                _posts[day]!.add(
                  _CalendarPost(
                    label: post['title'] ?? 'Post',
                    status: status,
                    isMine: isMine,
                    color: _CalendarPost.getStatusColor(status),
                  ),
                );
              }
            }
          });
        });
      }
    } catch (e) {
      print('Error loading calendar posts: $e');
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
            child: const AppBottomNav(currentIndex: 0),
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
                    setState(() => _isPublicCalendar = val);
                    await _loadScheduledPosts();
                    try {
                      await ApiService.updatePublicCalendar(
                        userId: SessionStore.userId ?? 0,
                        isPublic: val,
                      );
                    } catch (e) {
                      print('Error updating public calendar: $e');
                    }
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
            const cellHeight = 60.0;
            const rows = 5;

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

                  final posts = isValidDay ? (_posts[dayNum] ?? []) : [];
                  final showPublicCount = _isPublicCalendar && isValidDay;
                  final publicCount = posts.length;
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

                          // Post pills
                          if (showPublicCount && publicCount > 0)
                            Container(
                              margin: const EdgeInsets.only(top: 2),
                              padding: const EdgeInsets.symmetric(
                                horizontal: 4,
                                vertical: 2,
                              ),
                              decoration: BoxDecoration(
                                color: dayLoadColor,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                publicCount == 1
                                    ? '1 request'
                                    : '$publicCount requests',
                                style: const TextStyle(
                                  fontFamily: 'Inter',
                                  fontWeight: FontWeight.w600,
                                  fontSize: 7.1,
                                  color: Colors.white,
                                ),
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ...posts
                              .take(2)
                              .map(
                                (post) => Container(
                                  margin: const EdgeInsets.only(top: 2),
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 4,
                                    vertical: 2,
                                  ),
                                  decoration: BoxDecoration(
                                    color: post.color,
                                    border: _isPublicCalendar && post.isMine
                                        ? Border.all(
                                            color: const Color(0xFFF97316),
                                            width: 1,
                                          )
                                        : null,
                                    borderRadius: BorderRadius.circular(4),
                                  ),
                                  child: Text(
                                    post.label,
                                    style: const TextStyle(
                                      fontFamily: 'Inter',
                                      fontWeight: FontWeight.w400,
                                      fontSize: 7.1,
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
      child: Row(
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

  const _CalendarPost({
    required this.label,
    required this.status,
    required this.color,
    required this.isMine,
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
