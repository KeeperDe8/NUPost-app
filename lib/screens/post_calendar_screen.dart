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

class _PostCalendarScreenState extends State<PostCalendarScreen>
    with TickerProviderStateMixin {
  DateTime _focusedMonth = DateTime(DateTime.now().year, DateTime.now().month);
  DateTime? _selectedDate;
  bool _isMonthView = true;
  final List<_CalendarPost> _posts = [];
  bool _isPublicCalendar = false;
  bool _isLoading = false;
  String? _loadError;

  late final AnimationController _entryCtrl;
  late final Animation<double> _entryFade;

  // Selected-date panel animation
  late final AnimationController _panelCtrl;
  late final Animation<double> _panelFade;
  late final Animation<Offset> _panelSlide;

  // Stagger for list items
  late final AnimationController _listCtrl;

  @override
  void initState() {
    super.initState();

    _entryCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 600),
    )..forward();
    _entryFade = CurvedAnimation(
      parent: _entryCtrl,
      curve: const Interval(0.0, 0.7, curve: Curves.easeOut),
    );

    _panelCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 320),
    );
    _panelFade = CurvedAnimation(parent: _panelCtrl, curve: Curves.easeOut);
    _panelSlide = Tween<Offset>(
      begin: const Offset(0, -0.08),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _panelCtrl, curve: Curves.easeOutCubic));

    _listCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 700),
    );

    _loadScheduledPosts();
  }

  @override
  void dispose() {
    _entryCtrl.dispose();
    _panelCtrl.dispose();
    _listCtrl.dispose();
    super.dispose();
  }

  // ── All original logic preserved ─────────────────────────────────────────
  Future<void> _loadScheduledPosts({
    bool? forcePublicView,
    bool updateToggle = false,
  }) async {
    final publicView = forcePublicView ?? _isPublicCalendar;
    setState(() {
      _isLoading = true;
      _loadError = null;
    });
    try {
      final userId = SessionStore.userId;
      if (userId == null || userId == 0) {
        setState(() => _isLoading = false);
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

        if (postsList is List) {
          for (final post in postsList) {
            final status = post['status'] ?? 'Pending';
            final priority = post['priority'] ?? 'normal';
            final requester = (post['requester'] ?? '').toString();
            final isMine =
                requester.isNotEmpty && requester == (SessionStore.name ?? '');
            DateTime? requestDate;
            DateTime? scheduledDate;
            if (post['request_date'] != null && post['request_date'] != '')
              requestDate = DateTime.tryParse(post['request_date']);
            if (post['scheduled_date'] != null && post['scheduled_date'] != '')
              scheduledDate = DateTime.tryParse(post['scheduled_date']);
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
          setState(() {
            _posts
              ..clear()
              ..addAll(nextPosts);
            _loadError = null;
            _isLoading = false;
            if (updateToggle && forcePublicView != null)
              _isPublicCalendar = forcePublicView;
          });
          _listCtrl.reset();
          _listCtrl.forward();
        }
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _loadError = e.toString().replaceFirst('Exception: ', '');
        _isLoading = false;
      });
    }
  }

  DateTime get _today => DateTime.now();

  void _prevMonth() {
    setState(() {
      _focusedMonth = DateTime(_focusedMonth.year, _focusedMonth.month - 1);
      _selectedDate = null;
    });
    _panelCtrl.reverse();
    _loadScheduledPosts();
  }

  void _nextMonth() {
    setState(() {
      _focusedMonth = DateTime(_focusedMonth.year, _focusedMonth.month + 1);
      _selectedDate = null;
    });
    _panelCtrl.reverse();
    _loadScheduledPosts();
  }

  void _goToToday() {
    setState(() {
      _focusedMonth = DateTime(_today.year, _today.month);
      _isMonthView = false;
    });
  }

  void _selectDate(DateTime date) {
    final isSame =
        _selectedDate != null &&
        _selectedDate!.year == date.year &&
        _selectedDate!.month == date.month &&
        _selectedDate!.day == date.day;

    if (isSame) {
      setState(() => _selectedDate = null);
      _panelCtrl.reverse();
    } else {
      setState(() => _selectedDate = date);
      _panelCtrl.forward(from: 0);
    }
  }

  void _clearSelection() {
    setState(() => _selectedDate = null);
    _panelCtrl.reverse();
  }

  bool _isSelected(DateTime date) =>
      _selectedDate != null &&
      _selectedDate!.year == date.year &&
      _selectedDate!.month == date.month &&
      _selectedDate!.day == date.day;

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

  bool get _isCurrentMonth =>
      _focusedMonth.year == _today.year && _focusedMonth.month == _today.month;

  List<_CalendarPost> _postsForDate(DateTime date) => _posts
      .where(
        (p) =>
            (p.scheduledDate != null &&
                p.scheduledDate!.year == date.year &&
                p.scheduledDate!.month == date.month &&
                p.scheduledDate!.day == date.day) ||
            (p.requestDate != null &&
                p.requestDate!.year == date.year &&
                p.requestDate!.month == date.month &&
                p.requestDate!.day == date.day),
      )
      .toList();

  // ── BUILD ─────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.pageBg,
      body: GestureDetector(
        onTap: _clearSelection,
        behavior: HitTestBehavior.translucent,
        child: FadeTransition(
          opacity: _entryFade,
          child: Stack(
            children: [
              Column(
                children: [
                  _buildHeader(),
                  Expanded(
                    child: RefreshIndicator(
                      color: const Color(0xFF002366),
                      onRefresh: () => _loadScheduledPosts(),
                      child: SingleChildScrollView(
                        physics: const AlwaysScrollableScrollPhysics(),
                        padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            // Public banner
                            if (_isPublicCalendar) _buildPublicBanner(),

                            // Calendar card
                            _buildCalendarCard(),

                            // Error
                            if (_loadError != null) _buildErrorBanner(),

                            const SizedBox(height: 18),

                            // Selected date panel
                            if (_selectedDate != null)
                              _buildSelectedDatePanel(),

                            // Upcoming header
                            AnimatedCrossFade(
                              duration: const Duration(milliseconds: 250),
                              crossFadeState: _selectedDate != null
                                  ? CrossFadeState.showFirst
                                  : CrossFadeState.showSecond,
                              firstChild: _SectionLabel(
                                text: _isPublicCalendar
                                    ? 'PUBLIC POSTS ON ${_selectedDate?.month}/${_selectedDate?.day}'
                                    : 'YOUR POSTS ON ${_selectedDate?.month}/${_selectedDate?.day}',
                              ),
                              secondChild: _SectionLabel(
                                text: _isPublicCalendar
                                    ? 'UPCOMING PUBLIC POSTS'
                                    : 'YOUR UPCOMING POSTS',
                              ),
                            ),
                            const SizedBox(height: 10),

                            _buildUpcomingList(),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              const Positioned(
                left: 0,
                right: 0,
                bottom: 0,
                child: AppBottomNav(currentIndex: -1),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ── Header ────────────────────────────────────────────────────────────────
  Widget _buildHeader() {
    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(bottom: BorderSide(color: Color(0x0F000000), width: 1)),
        boxShadow: [
          BoxShadow(
            color: Color(0x07001540),
            blurRadius: 10,
            offset: Offset(0, 1),
          ),
        ],
      ),
      padding: EdgeInsets.fromLTRB(
        20,
        MediaQuery.of(context).padding.top + 14,
        16,
        14,
      ),
      child: Row(
        children: [
          GestureDetector(
            onTap: () => Navigator.of(context).pop(),
            child: Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: const Color(0xFF002366).withOpacity(0.07),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(
                Icons.arrow_back_ios_new_rounded,
                size: 16,
                color: Color(0xFF002366),
              ),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Text(
                      'Post Calendar',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w900,
                        fontSize: 20,
                        color: Color(0xFF002366),
                        letterSpacing: -0.3,
                      ),
                    ),
                    if (_isLoading) ...[
                      const SizedBox(width: 8),
                      const SizedBox(
                        width: 14,
                        height: 14,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Color(0xFF002366),
                        ),
                      ),
                    ],
                  ],
                ),
                const Text(
                  'Schedule and track your posts',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 12,
                    color: Color(0xFF9AA3B2),
                  ),
                ),
              ],
            ),
          ),
          // Public toggle
          Row(
            children: [
              AnimatedDefaultTextStyle(
                duration: const Duration(milliseconds: 200),
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w700,
                  fontSize: 12,
                  color: _isPublicCalendar
                      ? const Color(0xFF002366)
                      : const Color(0xFF9AA3B2),
                ),
                child: const Text('Public'),
              ),
              Transform.scale(
                scale: 0.8,
                child: Switch(
                  value: _isPublicCalendar,
                  onChanged: (val) async {
                    try {
                      await ApiService.updatePublicCalendar(
                        userId: SessionStore.userId ?? 0,
                        isPublic: val,
                      );
                    } catch (_) {}
                    await _loadScheduledPosts(
                      forcePublicView: val,
                      updateToggle: true,
                    );
                  },
                  activeColor: const Color(0xFF002366),
                  activeTrackColor: const Color(0xFF002366).withOpacity(0.3),
                  inactiveThumbColor: const Color(0xFF9AA3B2),
                  inactiveTrackColor: const Color(0xFFE9EDF6),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ── Public banner ─────────────────────────────────────────────────────────
  Widget _buildPublicBanner() {
    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
      decoration: BoxDecoration(
        color: const Color(0xFFFFFBEB),
        border: Border.all(color: const Color(0xFFF59E0B).withOpacity(0.25)),
        borderRadius: BorderRadius.circular(14),
      ),
      child: const Row(
        children: [
          Icon(Icons.public_rounded, size: 16, color: Color(0xFF92400E)),
          SizedBox(width: 8),
          Expanded(
            child: Text(
              "Public view — Showing all users' scheduled posts",
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: Color(0xFF92400E),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Error banner ──────────────────────────────────────────────────────────
  Widget _buildErrorBanner() {
    return Container(
      margin: const EdgeInsets.only(top: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFFFF1F2),
        border: Border.all(color: const Color(0xFFFDA4AF)),
        borderRadius: BorderRadius.circular(14),
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
                    fontSize: 12,
                    color: Color(0xFF9F1239),
                  ),
                ),
                const SizedBox(height: 6),
                GestureDetector(
                  onTap: () => _loadScheduledPosts(),
                  child: const Text(
                    'Retry',
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      fontWeight: FontWeight.w700,
                      fontSize: 12,
                      color: Color(0xFF2B5CE6),
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

  // ── Calendar card ─────────────────────────────────────────────────────────
  Widget _buildCalendarCard() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0x0E000000)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x08001540),
            blurRadius: 14,
            offset: Offset(0, 5),
          ),
        ],
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          _buildMonthNav(),
          const SizedBox(height: 14),
          _buildToggle(),
          const SizedBox(height: 14),
          _buildCalendarGrid(),
          const SizedBox(height: 14),
          _buildLegend(),
        ],
      ),
    );
  }

  // ── Month nav ─────────────────────────────────────────────────────────────
  Widget _buildMonthNav() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        GestureDetector(
          onTap: _prevMonth,
          child: Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: const Color(0xFF002366).withOpacity(0.07),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(
              Icons.chevron_left_rounded,
              color: Color(0xFF002366),
              size: 22,
            ),
          ),
        ),
        Column(
          children: [
            Text(
              _monthLabel(),
              style: const TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w800,
                fontSize: 16,
                color: Color(0xFF080F1E),
                letterSpacing: -0.3,
              ),
            ),
            // Today dot indicator when viewing current month
            AnimatedContainer(
              duration: const Duration(milliseconds: 300),
              width: _isCurrentMonth ? 5 : 0,
              height: _isCurrentMonth ? 5 : 0,
              margin: EdgeInsets.only(top: _isCurrentMonth ? 3 : 0),
              decoration: const BoxDecoration(
                color: Color(0xFF002366),
                shape: BoxShape.circle,
              ),
            ),
          ],
        ),
        GestureDetector(
          onTap: _nextMonth,
          child: Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: const Color(0xFF002366).withOpacity(0.07),
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(
              Icons.chevron_right_rounded,
              color: Color(0xFF002366),
              size: 22,
            ),
          ),
        ),
      ],
    );
  }

  // ── Toggle ────────────────────────────────────────────────────────────────
  Widget _buildToggle() {
    return Container(
      height: 38,
      decoration: BoxDecoration(
        color: const Color(0xFFF1F4FB),
        borderRadius: BorderRadius.circular(12),
      ),
      padding: const EdgeInsets.all(3),
      child: Row(
        children: [
          _ToggleBtn(
            label: 'Today',
            isActive: !_isMonthView,
            onTap: _goToToday,
          ),
          _ToggleBtn(
            label: 'Month',
            isActive: _isMonthView,
            onTap: () => setState(() => _isMonthView = true),
          ),
        ],
      ),
    );
  }

  // ── Calendar grid ─────────────────────────────────────────────────────────
  Widget _buildCalendarGrid() {
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    final firstDay = DateTime(_focusedMonth.year, _focusedMonth.month, 1);
    final startOffset = firstDay.weekday % 7;
    final daysInMonth = DateTime(
      _focusedMonth.year,
      _focusedMonth.month + 1,
      0,
    ).day;

    return Column(
      children: [
        // Day headers
        Row(
          children: dayNames
              .map(
                (d) => Expanded(
                  child: Center(
                    child: Text(
                      d,
                      style: const TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w700,
                        fontSize: 10.5,
                        color: Color(0xFF9AA3B2),
                        letterSpacing: 0.3,
                      ),
                    ),
                  ),
                ),
              )
              .toList(),
        ),
        const SizedBox(height: 6),

        LayoutBuilder(
          builder: (context, constraints) {
            final cellWidth = constraints.maxWidth / 7;
            const cellHeight = 78.0;
            final rows = ((startOffset + daysInMonth) / 7).ceil();

            return SizedBox(
              height: cellHeight * rows,
              child: Stack(
                children: List.generate(rows * 7, (index) {
                  final col = index % 7;
                  final row = index ~/ 7;
                  final dayNum = index - startOffset + 1;
                  final isValid = dayNum >= 1 && dayNum <= daysInMonth;
                  final isToday =
                      isValid &&
                      _focusedMonth.year == _today.year &&
                      _focusedMonth.month == _today.month &&
                      dayNum == _today.day;

                  final cellDate = DateTime(
                    _focusedMonth.year,
                    _focusedMonth.month,
                    isValid ? dayNum : 1,
                  );
                  final isSelected = isValid && _isSelected(cellDate);

                  final scheduledPosts = isValid
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
                  final requestPosts = isValid
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

                  final totalPosts =
                      scheduledPosts.length + requestPosts.length;
                  final showOverflow = totalPosts > 2;

                  return Positioned(
                    left: col * cellWidth,
                    top: row * cellHeight,
                    width: cellWidth,
                    height: cellHeight,
                    child: GestureDetector(
                      onTap: () {
                        if (isValid) _selectDate(cellDate);
                      },
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 180),
                        curve: Curves.easeOutCubic,
                        decoration: BoxDecoration(
                          color: isSelected
                              ? const Color(0xFF2B5CE6).withOpacity(0.1)
                              : isToday
                              ? const Color(0xFF002366).withOpacity(0.04)
                              : Colors.transparent,
                          border: Border.all(
                            color: isSelected
                                ? const Color(0xFF2B5CE6).withOpacity(0.5)
                                : const Color(0x08000000),
                            width: isSelected ? 1.5 : 1,
                          ),
                          borderRadius: (isToday || isSelected)
                              ? BorderRadius.circular(10)
                              : BorderRadius.zero,
                        ),
                        padding: const EdgeInsets.all(3),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.center,
                          children: [
                            if (isValid)
                              AnimatedContainer(
                                duration: const Duration(milliseconds: 200),
                                width: 24,
                                height: 24,
                                decoration: isToday
                                    ? BoxDecoration(
                                        color: const Color(0xFF002366),
                                        borderRadius: BorderRadius.circular(8),
                                      )
                                    : null,
                                alignment: Alignment.center,
                                child: Text(
                                  '$dayNum',
                                  style: TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontWeight: isToday
                                        ? FontWeight.w900
                                        : FontWeight.w400,
                                    fontSize: 11.5,
                                    color: isToday
                                        ? Colors.white
                                        : const Color(0xFF080F1E),
                                  ),
                                ),
                              ),
                            // Request pill (grey)
                            ...requestPosts
                                .take(1)
                                .map(
                                  (p) => _PostPill(
                                    label: p.label,
                                    color: const Color(0xFF9AA3B2),
                                  ),
                                ),
                            // Scheduled pill (priority color)
                            ...scheduledPosts
                                .take(1)
                                .map(
                                  (p) => _PostPill(
                                    label: p.label,
                                    color: _isPublicCalendar && !p.isMine
                                        ? const Color(0xFF9AA3B2)
                                        : p.priorityColor,
                                  ),
                                ),
                            // Overflow badge
                            if (showOverflow && isValid)
                              Container(
                                margin: const EdgeInsets.only(top: 2),
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 4,
                                  vertical: 1,
                                ),
                                decoration: BoxDecoration(
                                  color: const Color(
                                    0xFF9AA3B2,
                                  ).withOpacity(0.2),
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: Text(
                                  '+${totalPosts - 2}',
                                  style: const TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontSize: 6.5,
                                    color: Color(0xFF6B7280),
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                              ),
                          ],
                        ),
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

  // ── Legend ────────────────────────────────────────────────────────────────
  Widget _buildLegend() {
    return Container(
      padding: const EdgeInsets.only(top: 14),
      decoration: const BoxDecoration(
        border: Border(top: BorderSide(color: Color(0x0A000000), width: 1)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (!_isPublicCalendar) ...[
            Row(
              children: [
                _LegendDot(
                  color: const Color(0xFF9AA3B2),
                  label: 'Request Date',
                ),
                const SizedBox(width: 16),
                _LegendDot(
                  color: const Color(0xFF002366),
                  label: 'Today',
                  bordered: true,
                ),
              ],
            ),
            const SizedBox(height: 8),
            const Text(
              'PRIORITY:',
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w800,
                fontSize: 9.5,
                color: Color(0xFF9AA3B2),
                letterSpacing: 0.7,
              ),
            ),
            const SizedBox(height: 6),
            Row(
              children: [
                _LegendDot(color: const Color(0xFF2B5CE6), label: 'Normal'),
                const SizedBox(width: 14),
                _LegendDot(color: const Color(0xFFF59E0B), label: 'High'),
                const SizedBox(width: 14),
                _LegendDot(color: const Color(0xFFFF3B30), label: 'Urgent'),
              ],
            ),
          ] else ...[
            Row(
              children: [
                _LegendDot(color: const Color(0xFF2B5CE6), label: 'Your post'),
                const SizedBox(width: 14),
                _LegendDot(
                  color: const Color(0xFF9AA3B2),
                  label: "Others' post",
                ),
                const SizedBox(width: 14),
                _LegendDot(
                  color: const Color(0xFF002366),
                  label: 'Today',
                  bordered: true,
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  // ── Selected date panel ───────────────────────────────────────────────────
  Widget _buildSelectedDatePanel() {
    if (_selectedDate == null) return const SizedBox.shrink();

    const months = [
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
    final dayPosts = _postsForDate(_selectedDate!);

    return FadeTransition(
      opacity: _panelFade,
      child: SlideTransition(
        position: _panelSlide,
        child: Container(
          margin: const EdgeInsets.only(bottom: 16),
          decoration: BoxDecoration(
            color: const Color(0xFFF0F5FF),
            border: Border.all(
              color: const Color(0xFF2B5CE6).withOpacity(0.2),
              width: 1.5,
            ),
            borderRadius: BorderRadius.circular(18),
          ),
          padding: const EdgeInsets.fromLTRB(16, 14, 16, 14),
          child: Row(
            children: [
              // Date badge
              Container(
                width: 46,
                height: 50,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [Color(0xFF001540), Color(0xFF0032A0)],
                  ),
                  borderRadius: BorderRadius.circular(14),
                  boxShadow: const [
                    BoxShadow(
                      color: Color(0x35001540),
                      blurRadius: 10,
                      offset: Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      '${_selectedDate!.day}',
                      style: const TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w900,
                        fontSize: 20,
                        color: Colors.white,
                        height: 1.0,
                        letterSpacing: -0.5,
                      ),
                    ),
                    Text(
                      months[_selectedDate!.month - 1].toUpperCase(),
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontSize: 8,
                        fontWeight: FontWeight.w700,
                        color: Colors.white.withOpacity(0.65),
                        letterSpacing: 0.5,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      dayPosts.isEmpty
                          ? 'No posts this day'
                          : '${dayPosts.length} post${dayPosts.length > 1 ? 's' : ''} scheduled',
                      style: const TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w800,
                        fontSize: 13.5,
                        color: Color(0xFF080F1E),
                        letterSpacing: -0.1,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      dayPosts.isEmpty
                          ? 'Tap a different date to check'
                          : dayPosts.map((p) => p.label).take(2).join(' · '),
                      style: const TextStyle(
                        fontFamily: 'DM Sans',
                        fontSize: 12,
                        color: Color(0xFF3D4A63),
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
              // Dismiss
              GestureDetector(
                onTap: _clearSelection,
                child: Container(
                  width: 28,
                  height: 28,
                  decoration: BoxDecoration(
                    color: const Color(0xFF2B5CE6).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(9),
                  ),
                  child: const Icon(
                    Icons.close_rounded,
                    size: 15,
                    color: Color(0xFF2B5CE6),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ── Upcoming list ─────────────────────────────────────────────────────────
  Widget _buildUpcomingList() {
    final now = DateTime.now();
    final limit = now.add(const Duration(days: 7));

    final List<_CalendarPost> upcoming;
    if (_selectedDate != null) {
      upcoming =
          _posts
              .where(
                (p) =>
                    p.scheduledDate != null &&
                    p.scheduledDate!.year == _selectedDate!.year &&
                    p.scheduledDate!.month == _selectedDate!.month &&
                    p.scheduledDate!.day == _selectedDate!.day,
              )
              .toList()
            ..sort((a, b) => a.scheduledDate!.compareTo(b.scheduledDate!));
    } else {
      upcoming =
          _posts
              .where(
                (p) =>
                    p.scheduledDate != null &&
                    p.scheduledDate!.isAfter(now) &&
                    p.scheduledDate!.isBefore(
                      limit.add(const Duration(days: 1)),
                    ),
              )
              .toList()
            ..sort((a, b) => a.scheduledDate!.compareTo(b.scheduledDate!));
    }

    if (upcoming.isEmpty) {
      return Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: const Color(0x0E000000)),
          boxShadow: const [
            BoxShadow(
              color: Color(0x06001540),
              blurRadius: 10,
              offset: Offset(0, 4),
            ),
          ],
        ),
        padding: const EdgeInsets.symmetric(vertical: 40),
        child: Column(
          children: [
            Container(
              width: 54,
              height: 54,
              decoration: BoxDecoration(
                color: const Color(0xFF9AA3B2).withOpacity(0.08),
                borderRadius: BorderRadius.circular(18),
              ),
              child: const Icon(
                Icons.event_available_rounded,
                size: 28,
                color: Color(0xFF9AA3B2),
              ),
            ),
            const SizedBox(height: 12),
            Text(
              _selectedDate != null
                  ? 'No posts scheduled on ${_selectedDate!.month}/${_selectedDate!.day}'
                  : 'No posts scheduled for the next 7 days',
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 13,
                color: Color(0xFF9AA3B2),
              ),
            ),
          ],
        ),
      );
    }

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0x0E000000)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x06001540),
            blurRadius: 10,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: ListView.separated(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        itemCount: upcoming.length,
        separatorBuilder: (_, __) => Container(
          height: 1,
          margin: const EdgeInsets.symmetric(horizontal: 16),
          color: const Color(0x08000000),
        ),
        itemBuilder: (_, i) {
          final post = upcoming[i];

          // Stagger animation per item
          final start = (i * 0.12).clamp(0.0, 0.7);
          final end = (start + 0.4).clamp(0.0, 1.0);
          final itemAnim = Tween<double>(begin: 0.0, end: 1.0).animate(
            CurvedAnimation(
              parent: _listCtrl,
              curve: Interval(start, end, curve: Curves.easeOutCubic),
            ),
          );

          return AnimatedBuilder(
            animation: itemAnim,
            builder: (_, child) => Opacity(
              opacity: itemAnim.value,
              child: Transform.translate(
                offset: Offset(0, (1 - itemAnim.value) * 12),
                child: child,
              ),
            ),
            child: _UpcomingPostItem(post: post, isPublic: _isPublicCalendar),
          );
        },
      ),
    );
  }
}

// ── Section Label ─────────────────────────────────────────────────────────────
class _SectionLabel extends StatelessWidget {
  final String text;
  const _SectionLabel({required this.text});
  @override
  Widget build(BuildContext context) => Text(
    text,
    style: const TextStyle(
      fontFamily: 'DM Sans',
      fontWeight: FontWeight.w800,
      fontSize: 11,
      color: Color(0xFF9AA3B2),
      letterSpacing: 0.9,
    ),
  );
}

// ── Upcoming post item ────────────────────────────────────────────────────────
class _UpcomingPostItem extends StatelessWidget {
  final _CalendarPost post;
  final bool isPublic;
  const _UpcomingPostItem({required this.post, required this.isPublic});

  Color get _dotColor =>
      post.isMine ? post.priorityColor : const Color(0xFF9AA3B2);

  String _statusLabel() {
    final s = post.status.toLowerCase();
    if (s.contains('posted')) return 'Posted';
    if (s.contains('approved')) return 'Approved';
    if (s.contains('under review')) return 'In Review';
    return 'Pending';
  }

  Color _statusColor() {
    final s = post.status.toLowerCase();
    if (s.contains('posted')) return const Color(0xFF8B5CF6);
    if (s.contains('approved')) return const Color(0xFF05C46B);
    if (s.contains('under review')) return const Color(0xFFF59E0B);
    return const Color(0xFFF59E0B);
  }

  String get _dateStr {
    if (post.scheduledDate == null) return '';
    final d = post.scheduledDate!;
    const months = [
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
    return '${months[d.month - 1]} ${d.day}';
  }

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      child: Row(
        children: [
          // Priority / ownership color bar
          Container(
            width: 4,
            height: 42,
            decoration: BoxDecoration(
              color: _dotColor,
              borderRadius: BorderRadius.circular(99),
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
                    fontWeight: FontWeight.w700,
                    fontSize: 13.5,
                    color: Color(0xFF080F1E),
                    letterSpacing: -0.1,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                Row(
                  children: [
                    // Status chip
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 7,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: _statusColor().withOpacity(0.1),
                        border: Border.all(
                          color: _statusColor().withOpacity(0.25),
                        ),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        _statusLabel(),
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w700,
                          fontSize: 9.5,
                          color: _statusColor(),
                        ),
                      ),
                    ),
                    if (isPublic && !post.isMine) ...[
                      const SizedBox(width: 6),
                      const Text(
                        'Others',
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontSize: 10.5,
                          color: Color(0xFF9AA3B2),
                        ),
                      ),
                    ] else if (post.isMine) ...[
                      const SizedBox(width: 6),
                      const Text(
                        'Your post',
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontSize: 10.5,
                          color: Color(0xFF9AA3B2),
                        ),
                      ),
                    ],
                  ],
                ),
              ],
            ),
          ),
          // Date badge
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(
              color: const Color(0xFF002366).withOpacity(0.06),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text(
              _dateStr,
              style: const TextStyle(
                fontFamily: 'DM Sans',
                fontWeight: FontWeight.w800,
                fontSize: 11.5,
                color: Color(0xFF002366),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Toggle Button ─────────────────────────────────────────────────────────────
class _ToggleBtn extends StatelessWidget {
  final String label;
  final bool isActive;
  final VoidCallback onTap;
  const _ToggleBtn({
    required this.label,
    required this.isActive,
    required this.onTap,
  });
  @override
  Widget build(BuildContext context) => Expanded(
    child: GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        height: double.infinity,
        decoration: BoxDecoration(
          color: isActive ? const Color(0xFF002366) : Colors.transparent,
          borderRadius: BorderRadius.circular(10),
          boxShadow: isActive
              ? const [
                  BoxShadow(
                    color: Color(0x30002366),
                    blurRadius: 8,
                    offset: Offset(0, 2),
                  ),
                ]
              : null,
        ),
        alignment: Alignment.center,
        child: Text(
          label,
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontWeight: FontWeight.w800,
            fontSize: 12.5,
            color: isActive ? Colors.white : const Color(0xFF9AA3B2),
          ),
        ),
      ),
    ),
  );
}

// ── Post Pill ─────────────────────────────────────────────────────────────────
class _PostPill extends StatelessWidget {
  final String label;
  final Color color;
  const _PostPill({required this.label, required this.color});
  @override
  Widget build(BuildContext context) => Container(
    margin: const EdgeInsets.only(top: 2),
    padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
    decoration: BoxDecoration(
      color: color,
      borderRadius: BorderRadius.circular(4),
    ),
    child: Text(
      label,
      style: const TextStyle(
        fontFamily: 'DM Sans',
        fontSize: 7,
        color: Colors.white,
        fontWeight: FontWeight.w600,
      ),
      overflow: TextOverflow.ellipsis,
    ),
  );
}

// ── Legend Dot ────────────────────────────────────────────────────────────────
class _LegendDot extends StatelessWidget {
  final Color color;
  final String label;
  final bool bordered;
  const _LegendDot({
    required this.color,
    required this.label,
    this.bordered = false,
  });
  @override
  Widget build(BuildContext context) => Row(
    children: [
      Container(
        width: 10,
        height: 10,
        decoration: BoxDecoration(
          color: bordered ? Colors.transparent : color,
          border: bordered ? Border.all(color: color, width: 2) : null,
          borderRadius: BorderRadius.circular(3),
        ),
      ),
      const SizedBox(width: 6),
      Text(
        label,
        style: const TextStyle(
          fontFamily: 'DM Sans',
          fontSize: 11,
          color: Color(0xFF9AA3B2),
          fontWeight: FontWeight.w500,
        ),
      ),
    ],
  );
}

// ── Calendar Post model (unchanged) ──────────────────────────────────────────
class _CalendarPost {
  final String label, status, priority;
  final Color color;
  final bool isMine;
  final DateTime? requestDate, scheduledDate;

  const _CalendarPost({
    required this.label,
    required this.status,
    required this.priority,
    required this.color,
    required this.isMine,
    this.requestDate,
    this.scheduledDate,
  });

  Color get priorityColor {
    final p = priority.toLowerCase();
    if (p == 'urgent') return const Color(0xFFFF3B30);
    if (p == 'high') return const Color(0xFFF59E0B);
    return const Color(0xFF2B5CE6);
  }

  static Color getStatusColor(String status) {
    final s = status.toLowerCase();
    if (s.contains('posted')) return AppColors.posted;
    if (s.contains('approved')) return AppColors.approved;
    if (s.contains('under review')) return AppColors.pending;
    return AppColors.inkMid;
  }
}
