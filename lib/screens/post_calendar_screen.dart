import 'package:flutter/material.dart';
import '../app_bottom_nav.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';
import '../theme/app_theme.dart';

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
  String? _loadError;

  @override
  void initState() {
    super.initState();
    _loadScheduledPosts();
  }

  Future<void> _loadScheduledPosts({
    bool? forcePublicView,
    bool updateToggle = false,
  }) async {
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

        print(
          'PUBLIC VIEW: $publicView, Posts count: ${postsList is List ? postsList.length : 0}',
        );

        if (postsList is List) {
          for (var post in postsList) {
            final status = post['status'] ?? 'Pending';
            final priority = post['priority'] ?? 'normal';
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
                priority: priority,
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
            _loadError = null;
            if (updateToggle && forcePublicView != null) {
              _isPublicCalendar = forcePublicView;
            }
          });
        }
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _loadError = e.toString().replaceFirst('Exception: ', '');
      });
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
      backgroundColor: AppColors.pageBg,
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
                      if (_isPublicCalendar)
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(12),
                          margin: const EdgeInsets.only(bottom: 16),
                          decoration: BoxDecoration(
                            color: AppColors.goldBg,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: AppColors.accent.withOpacity(0.3),
                            ),
                          ),
                          child: Row(
                            children: [
                              const Icon(
                                Icons.info_outline,
                                color: AppColors.accent,
                                size: 18,
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  'Public view is ON — Showing all users\' scheduled posts',
                                  style: const TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontWeight: FontWeight.w400,
                                    fontSize: 12,
                                    color: AppColors.primary,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      _buildCalendarCard(),

                      if (_loadError != null) ...[
                        const SizedBox(height: 10),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFFF1F2),
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: const Color(0xFFFDA4AF)),
                          ),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Icon(
                                Icons.error_outline_rounded,
                                color: Color(0xFFBE123C),
                                size: 18,
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      _loadError!,
                                      style: const TextStyle(
                                        fontFamily: 'DM Sans',
                                        fontWeight: FontWeight.w500,
                                        fontSize: 11.5,
                                        color: Color(0xFF9F1239),
                                      ),
                                    ),
                                    const SizedBox(height: 6),
                                    TextButton(
                                      style: TextButton.styleFrom(
                                        minimumSize: Size.zero,
                                        padding: EdgeInsets.zero,
                                        tapTargetSize:
                                            MaterialTapTargetSize.shrinkWrap,
                                      ),
                                      onPressed: () => _loadScheduledPosts(),
                                      child: const Text('Retry'),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],

                      const SizedBox(height: 16),

                      // ── Upcoming Scheduled Posts ───────
                      Text(
                        _isPublicCalendar
                            ? 'Upcoming Public Posts'
                            : 'Your Upcoming Posts',
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w600,
                          fontSize: 13,
                          color: AppColors.inkMid,
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
                      color: AppColors.accent,
                    ),
                  ),
                  const SizedBox(width: 12),
                  const Text(
                    'Post Calendar',
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      fontWeight: FontWeight.w600,
                      fontSize: 18.3,
                      color: AppColors.accent,
                    ),
                  ),
                ],
              ),
              Row(
                children: [
                  Text(
                    'Public',
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      fontWeight: FontWeight.w500,
                      fontSize: 12,
                      color: _isPublicCalendar
                          ? AppColors.accent
                          : AppColors.inkMid,
                    ),
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
                        await _loadScheduledPosts(
                          forcePublicView: val,
                          updateToggle: true,
                        );
                      },
                      activeThumbColor: AppColors.accent,
                      activeTrackColor: AppColors.primary.withValues(
                        alpha: 0.5,
                      ),
                      inactiveThumbColor: AppColors.inkMute,
                      inactiveTrackColor: AppColors.border,
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 4),
          Padding(
            padding: const EdgeInsets.only(left: 36),
            child: Text(
              _isPublicCalendar
                  ? "Showing all users' scheduled posts"
                  : 'Schedule and track your posts',
              style: const TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w400,
                fontSize: 12.8,
                color: AppColors.inkMid,
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
              color: AppColors.accent,
              size: 20,
            ),
          ),
        ),
        Text(
          _monthLabel(),
          style: const TextStyle(
            fontFamily: 'DM Sans',
            fontWeight: FontWeight.w600,
            fontSize: 16.3,
            color: AppColors.accent,
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
              color: AppColors.accent,
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
                color: !_isMonthView ? AppColors.accent : Colors.white,
                border: Border.all(color: Colors.black.withOpacity(0.1)),
                borderRadius: BorderRadius.circular(8),
              ),
              alignment: Alignment.center,
              child: Text(
                'Today',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w600,
                  fontSize: 12.8,
                  color: !_isMonthView ? Colors.white : AppColors.ink,
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
                color: _isMonthView ? AppColors.accent : Colors.white,
                border: Border.all(color: Colors.black.withOpacity(0.1)),
                borderRadius: BorderRadius.circular(8),
              ),
              alignment: Alignment.center,
              child: Text(
                'Month',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w600,
                  fontSize: 13.7,
                  color: _isMonthView ? Colors.white : AppColors.ink,
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
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w400,
                        fontSize: 11.1,
                        color: AppColors.inkMid,
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
                  final cellDate = DateTime(
                    _focusedMonth.year,
                    _focusedMonth.month,
                    dayNum,
                  );
                  final scheduledPosts = isValidDay
                      ? _posts
                            .where(
                              (p) =>
                                  p.scheduledDate != null &&
                                  p.scheduledDate!.year == cellDate.year &&
                                  p.scheduledDate!.month == cellDate.month &&
                                  p.scheduledDate!.day == cellDate.day,
                            )
                            .toList()
                      : <_CalendarPost>[];

                  final requestPosts = isValidDay
                      ? _posts
                            .where(
                              (p) =>
                                  p.requestDate != null &&
                                  p.requestDate!.year == cellDate.year &&
                                  p.requestDate!.month == cellDate.month &&
                                  p.requestDate!.day == cellDate.day,
                            )
                            .toList()
                      : <_CalendarPost>[];

                  final showPublicCount = _isPublicCalendar && isValidDay;
                  final requestCount = requestPosts.length;
                  final dayLoadColor = _publicCountColor(requestCount);

                  return Positioned(
                    left: col * cellWidth,
                    top: row * cellHeight,
                    width: cellWidth,
                    height: cellHeight,
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        border: Border.all(color: AppColors.border, width: 1),
                      ),
                      padding: const EdgeInsets.all(4),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if (showPublicCount && requestCount > 0)
                            Container(
                              height: 3,
                              decoration: BoxDecoration(
                                color: dayLoadColor,
                                borderRadius: BorderRadius.circular(2),
                              ),
                            ),
                          if (showPublicCount && requestCount > 0)
                            const SizedBox(height: 2),
                          // Day number
                          if (isValidDay)
                            Container(
                              width: double.infinity,
                              alignment: Alignment.center,
                              decoration: isToday
                                  ? BoxDecoration(
                                      border: Border.all(
                                        color: AppColors.accent,
                                        width: 2,
                                      ),
                                      borderRadius: BorderRadius.circular(4),
                                    )
                                  : null,
                              child: Text(
                                '$dayNum',
                                style: TextStyle(
                                  fontFamily: 'DM Sans',
                                  fontWeight: FontWeight.w400,
                                  fontSize: 12,
                                  color: isToday
                                      ? AppColors.accent
                                      : AppColors.ink,
                                ),
                              ),
                            ),
                          // Request count badge
                          if (showPublicCount && requestCount > 0)
                            Container(
                              height: 3,
                              decoration: BoxDecoration(
                                color: dayLoadColor,
                                borderRadius: BorderRadius.circular(2),
                              ),
                            ),
                          // Request count text
                          if (showPublicCount && requestCount > 0)
                            Padding(
                              padding: const EdgeInsets.only(top: 2),
                              child: Text(
                                '$requestCount request${requestCount > 1 ? 's' : ''}',
                                style: const TextStyle(
                                  fontFamily: 'DM Sans',
                                  fontWeight: FontWeight.w400,
                                  fontSize: 7.5,
                                  color: AppColors.inkMid,
                                ),
                                textAlign: TextAlign.center,
                              ),
                            ),
                          // Show posts with proper styling
                          if (_isPublicCalendar)
                            // Public view: show request posts (grey) and scheduled posts
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
                                      color: AppColors
                                          .inkMute, // Grey for request date
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: Text(
                                      post.label,
                                      style: const TextStyle(
                                        fontFamily: 'DM Sans',
                                        fontWeight: FontWeight.w400,
                                        fontSize: 6.9,
                                        color: Colors.white,
                                      ),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                ),
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
                                      color: post.isMine
                                          ? post
                                                .priorityColor // Priority color for mine
                                          : AppColors
                                                .inkMute, // Grey for others
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: Text(
                                      post.label,
                                      style: const TextStyle(
                                        fontFamily: 'DM Sans',
                                        fontWeight: FontWeight.w400,
                                        fontSize: 6.9,
                                        color: Colors.white,
                                      ),
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                ),
                          // Private view: grey for request date, priority color for scheduled date
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
                                      color: AppColors
                                          .inkMute, // Grey for request date
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: Text(
                                      post.label,
                                      style: const TextStyle(
                                        fontFamily: 'DM Sans',
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
                                      color: post
                                          .priorityColor, // Priority-based color
                                      borderRadius: BorderRadius.circular(4),
                                    ),
                                    child: Text(
                                      post.label,
                                      style: const TextStyle(
                                        fontFamily: 'DM Sans',
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
    if (count >= 5) return AppColors.rejected;
    if (count >= 3) return AppColors.pending;
    return AppColors.approved;
  }

  // ── Legend ────────────────────────────────────────────
  Widget _buildLegend() {
    return Container(
      padding: const EdgeInsets.only(top: 16),
      decoration: const BoxDecoration(
        border: Border(top: BorderSide(color: AppColors.border)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (!_isPublicCalendar) ...[
            Row(
              children: [
                _LegendItem(
                  color: AppColors.inkMute,
                  label: 'Request Date',
                  isFilled: true,
                ),
                const SizedBox(width: 16),
                _LegendItem(
                  color: AppColors.accent,
                  label: 'Today',
                  isFilled: false,
                ),
              ],
            ),
            const SizedBox(height: 8),
            const Text(
              'Post Priority:',
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w500,
                fontSize: 10,
                color: AppColors.inkMid,
              ),
            ),
            const SizedBox(height: 4),
            Row(
              children: [
                _LegendItem(
                  color: AppColors.accent,
                  label: 'Normal',
                  isFilled: true,
                ),
                const SizedBox(width: 12),
                _LegendItem(
                  color: AppColors.pending,
                  label: 'High',
                  isFilled: true,
                ),
                const SizedBox(width: 12),
                _LegendItem(
                  color: AppColors.rejected,
                  label: 'Urgent',
                  isFilled: true,
                ),
              ],
            ),
          ] else ...[
            Row(
              children: [
                _LegendItem(
                  color: AppColors.accent,
                  label: 'Your request',
                  isFilled: true,
                ),
                const SizedBox(width: 16),
                _LegendItem(
                  color: AppColors.inkMute,
                  label: "Others' request",
                  isFilled: true,
                ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                _LegendItem(
                  color: AppColors.approved,
                  label: 'Open day',
                  isFilled: true,
                ),
                const SizedBox(width: 16),
                _LegendItem(
                  color: AppColors.rejected,
                  label: 'Busy day',
                  isFilled: true,
                ),
                const SizedBox(width: 16),
                _LegendItem(
                  color: AppColors.accent,
                  label: 'Today',
                  isFilled: false,
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  // ── Upcoming posts list ────────────────────────────────
  Widget _buildUpcomingEmpty() {
    final now = DateTime.now();
    final sevenDaysFromNow = now.add(const Duration(days: 7));

    // Get scheduled posts for next 7 days
    final upcomingPosts = _posts
        .where(
          (p) =>
              p.scheduledDate != null &&
              p.scheduledDate!.isAfter(now) &&
              p.scheduledDate!.isBefore(
                sevenDaysFromNow.add(const Duration(days: 1)),
              ),
        )
        .toList();

    // Sort by scheduled date
    upcomingPosts.sort((a, b) => a.scheduledDate!.compareTo(b.scheduledDate!));

    if (upcomingPosts.isEmpty) {
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
              color: AppColors.inkMute,
            ),
            SizedBox(height: 12),
            Text(
              'No upcoming posts scheduled for the next 7 days',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w400,
                fontSize: 13,
                color: AppColors.inkMute,
              ),
            ),
          ],
        ),
      );
    }

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
      child: ListView.separated(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        itemCount: upcomingPosts.length,
        separatorBuilder: (_, __) => Divider(
          color: Colors.black.withOpacity(0.05),
          height: 1,
          indent: 16,
          endIndent: 16,
        ),
        itemBuilder: (context, index) {
          final post = upcomingPosts[index];
          final dateStr = post.scheduledDate != null
              ? '${post.scheduledDate!.month}/${post.scheduledDate!.day}'
              : '';

          return Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            child: Row(
              children: [
                Container(
                  width: 12,
                  height: 12,
                  decoration: BoxDecoration(
                    color: post.isMine ? post.priorityColor : AppColors.inkMute,
                    borderRadius: BorderRadius.circular(3),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        post.label,
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w500,
                          fontSize: 12,
                          color: AppColors.ink,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 2),
                      Text(
                        post.isMine
                            ? 'Your post'
                            : 'By ${post.isMine ? 'you' : 'other'}',
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w400,
                          fontSize: 11,
                          color: AppColors.inkMid,
                        ),
                      ),
                    ],
                  ),
                ),
                Text(
                  dateStr,
                  style: const TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w500,
                    fontSize: 12,
                    color: AppColors.inkMid,
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

// ── Calendar Post model ───────────────────────────────────────────────────────
class _CalendarPost {
  final String label;
  final String status;
  final String priority;
  final Color color;
  final bool isMine;
  final DateTime? requestDate;
  final DateTime? scheduledDate;

  const _CalendarPost({
    required this.label,
    required this.status,
    required this.priority,
    required this.color,
    required this.isMine,
    this.requestDate,
    this.scheduledDate,
  });

  /// Returns color based on priority for scheduled date display
  Color get priorityColor {
    final p = priority.toLowerCase();
    if (p == 'urgent') return AppColors.rejected; // Red
    if (p == 'high') return AppColors.pending; // Orange
    return AppColors.accent; // Blue (normal/default)
  }

  static Color getStatusColor(String status) {
    final st = status.toLowerCase();
    if (st.contains('posted')) return AppColors.posted; // Purple
    if (st.contains('approved')) return AppColors.approved; // Green
    if (st.contains('under review')) return AppColors.pending; // Orange
    return AppColors.inkMid; // Gray (Pending)
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
            fontFamily: 'DM Sans',
            fontWeight: FontWeight.w400,
            fontSize: 10.9,
            color: AppColors.inkMid,
          ),
        ),
      ],
    );
  }
}

// ── Nav Item ──────────────────────────────────────────────────────────────────
