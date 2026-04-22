import 'package:flutter/material.dart';
import '../app_bottom_nav.dart';

// ── Public data model (used by other screens) ─────────────────────────────────
class TrackingEvent {
  final IconData icon;
  final String title;
  final String subtitle;
  final String timestamp;

  const TrackingEvent({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.timestamp,
  });
}

// ── Screen ────────────────────────────────────────────────────────────────────
class RequestTrackingScreen extends StatefulWidget {
  final String requestNumber;
  final String requestTitle;
  final List<TrackingEvent> events;
  final String currentStatus;
  final String currentStatusMessage;
  final String? heroTag;

  const RequestTrackingScreen({
    super.key,
    this.requestNumber = '',
    this.requestTitle = '',
    this.events = const [],
    this.currentStatus = '',
    this.currentStatusMessage = '',
    this.heroTag,
  });

  @override
  State<RequestTrackingScreen> createState() => _RequestTrackingScreenState();
}

class _RequestTrackingScreenState extends State<RequestTrackingScreen>
    with SingleTickerProviderStateMixin {
  late final AnimationController _entryCtrl;
  late final Animation<double> _entryFade;
  late final Animation<Offset> _entrySlide;

  @override
  void initState() {
    super.initState();
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
  }

  @override
  void dispose() {
    _entryCtrl.dispose();
    super.dispose();
  }

  // ── Status helpers ────────────────────────────────────────────────────────
  Color get _statusColor {
    switch (widget.currentStatus.toLowerCase()) {
      case 'approved':
        return const Color(0xFF05C46B);
      case 'posted':
        return const Color(0xFF8B5CF6);
      case 'rejected':
        return const Color(0xFFFF3B30);
      case 'pending':
        return const Color(0xFFF59E0B);
      default:
        return const Color(0xFF2B5CE6);
    }
  }

  IconData get _statusIcon {
    switch (widget.currentStatus.toLowerCase()) {
      case 'approved':
        return Icons.check_circle_rounded;
      case 'posted':
        return Icons.send_rounded;
      case 'rejected':
        return Icons.cancel_rounded;
      case 'pending':
        return Icons.hourglass_empty_rounded;
      default:
        return Icons.info_rounded;
    }
  }

  int get _progressStep {
    switch (widget.currentStatus.toLowerCase()) {
      case 'approved':
        return 2;
      case 'posted':
        return 3;
      case 'rejected':
        return -1; // special
      default:
        return 1; // pending / under review
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFE9EDF6),
      body: FadeTransition(
        opacity: _entryFade,
        child: SlideTransition(
          position: _entrySlide,
          child: Stack(
            children: [
              Column(
                children: [
                  _buildHeader(),
                  Expanded(
                    child: SingleChildScrollView(
                      padding: const EdgeInsets.fromLTRB(16, 20, 16, 110),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Status card
                          _buildStatusCard(),
                          const SizedBox(height: 20),

                          // Progress stepper (only when not rejected)
                          if (widget.currentStatus.toLowerCase() !=
                              'rejected') ...[
                            _buildProgressStepper(),
                            const SizedBox(height: 20),
                          ],

                          // Timeline header
                          if (widget.events.isNotEmpty) ...[
                            const _SectionLabel(text: 'Activity Timeline'),
                            const SizedBox(height: 12),
                            _buildTimeline(),
                          ] else
                            _buildEmptyState(),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
              Positioned(
                left: 0,
                right: 0,
                bottom: 0,
                child: const AppBottomNav(currentIndex: 1),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ── Header ────────────────────────────────────────────────────────────────
  Widget _buildHeader() {
    final topPad = MediaQuery.of(context).padding.top;
    return Container(
      width: double.infinity,
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(bottom: BorderSide(color: Color(0x0F000000), width: 1)),
        boxShadow: [
          BoxShadow(
            color: Color(0x07001540),
            blurRadius: 12,
            offset: Offset(0, 1),
          ),
        ],
      ),
      padding: EdgeInsets.fromLTRB(8, topPad + 10, 16, 14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Back + title row
          Row(
            children: [
              IconButton(
                onPressed: () => Navigator.of(context).pop(),
                icon: const Icon(
                  Icons.arrow_back_ios_new_rounded,
                  color: Color(0xFF002366),
                  size: 18,
                ),
              ),
              const Expanded(
                child: Text(
                  'Request Tracking',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w900,
                    fontSize: 20,
                    color: Color(0xFF080F1E),
                    letterSpacing: -0.4,
                  ),
                ),
              ),
              // Status chip in header
              if (widget.currentStatus.isNotEmpty)
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 5,
                  ),
                  decoration: BoxDecoration(
                    color: _statusColor.withOpacity(0.12),
                    border: Border.all(
                      color: _statusColor.withOpacity(0.28),
                      width: 1.5,
                    ),
                    borderRadius: BorderRadius.circular(99),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 6,
                        height: 6,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: _statusColor,
                        ),
                      ),
                      const SizedBox(width: 5),
                      Text(
                        widget.currentStatus,
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w800,
                          fontSize: 11.5,
                          color: _statusColor,
                        ),
                      ),
                    ],
                  ),
                ),
            ],
          ),

          // Request info
          if (widget.requestNumber.isNotEmpty || widget.requestTitle.isNotEmpty)
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 6, 0, 0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (widget.requestNumber.isNotEmpty)
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xFFE9EDF6),
                        borderRadius: BorderRadius.circular(7),
                      ),
                      child: Text(
                        widget.requestNumber,
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w700,
                          fontSize: 10,
                          color: Color(0xFF3D4A63),
                          letterSpacing: 0.4,
                        ),
                      ),
                    ),
                  if (widget.requestTitle.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(
                      widget.requestTitle,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w600,
                        fontSize: 13.5,
                        color: Color(0xFF080F1E),
                        height: 1.3,
                      ),
                    ),
                  ],
                ],
              ),
            ),
        ],
      ),
    );
  }

  // ── Status card ───────────────────────────────────────────────────────────
  Widget _buildStatusCard() {
    final isRejected = widget.currentStatus.toLowerCase() == 'rejected';

    Widget card = Container(
      width: double.infinity,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: isRejected
              ? [const Color(0xFFFF3B30), const Color(0xFFCC1A10)]
              : [
                  const Color(0xFF001540),
                  const Color(0xFF0032A0),
                  const Color(0xFF1A4FCC),
                ],
          stops: isRejected ? const [0.0, 1.0] : const [0.0, 0.5, 1.0],
        ),
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color:
                (isRejected ? const Color(0xFFFF3B30) : const Color(0xFF001540))
                    .withOpacity(0.38),
            blurRadius: 24,
            offset: const Offset(0, 10),
          ),
          BoxShadow(
            color:
                (isRejected ? const Color(0xFFFF3B30) : const Color(0xFF001540))
                    .withOpacity(0.15),
            blurRadius: 6,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Stack(
        children: [
          // Decorative orb
          Positioned(
            top: -30,
            right: -30,
            child: Container(
              width: 130,
              height: 130,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withOpacity(0.06),
              ),
            ),
          ),
          Positioned(
            bottom: -20,
            left: 20,
            child: Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withOpacity(0.04),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(22),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Icon + label row
                Row(
                  children: [
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.15),
                        borderRadius: BorderRadius.circular(15),
                        border: Border.all(
                          color: Colors.white.withOpacity(0.2),
                          width: 1.5,
                        ),
                      ),
                      child: Icon(_statusIcon, color: Colors.white, size: 22),
                    ),
                    const SizedBox(width: 12),
                    Text(
                      'Current Status',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w600,
                        fontSize: 12.5,
                        color: Colors.white.withOpacity(0.65),
                        letterSpacing: 0.3,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                Text(
                  widget.currentStatus.isEmpty
                      ? 'Processing'
                      : widget.currentStatus,
                  style: const TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w900,
                    fontSize: 32,
                    color: Colors.white,
                    letterSpacing: -0.8,
                    height: 1.0,
                  ),
                ),
                const SizedBox(height: 10),
                Container(height: 1, color: Colors.white.withOpacity(0.15)),
                const SizedBox(height: 10),
                Text(
                  widget.currentStatusMessage.isEmpty
                      ? 'Your request is being processed.'
                      : widget.currentStatusMessage,
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w400,
                    fontSize: 13,
                    color: Colors.white.withOpacity(0.72),
                    height: 1.55,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );

    // Wrap with Hero if heroTag provided
    if (widget.heroTag != null) {
      return Hero(
        tag: widget.heroTag!,
        child: Material(color: Colors.transparent, child: card),
      );
    }
    return card;
  }

  // ── Progress Stepper ──────────────────────────────────────────────────────
  Widget _buildProgressStepper() {
    const steps = ['Submitted', 'In Review', 'Approved', 'Posted'];
    final current = _progressStep;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0x0E000000)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x07001540),
            blurRadius: 10,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'PROGRESS',
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontWeight: FontWeight.w800,
              fontSize: 10,
              color: Color(0xFF9AA3B2),
              letterSpacing: 1.0,
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: steps.asMap().entries.map((entry) {
              final i = entry.key;
              final label = entry.value;
              final isDone = i <= current;
              final isActive = i == current;
              final isLast = i == steps.length - 1;

              return Expanded(
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        children: [
                          // Step circle
                          AnimatedContainer(
                            duration: const Duration(milliseconds: 300),
                            width: isActive ? 34 : 28,
                            height: isActive ? 34 : 28,
                            decoration: BoxDecoration(
                              gradient: isDone
                                  ? const LinearGradient(
                                      begin: Alignment.topLeft,
                                      end: Alignment.bottomRight,
                                      colors: [
                                        Color(0xFF001540),
                                        Color(0xFF1A4FCC),
                                      ],
                                    )
                                  : null,
                              color: isDone ? null : const Color(0xFFE9EDF6),
                              shape: BoxShape.circle,
                              boxShadow: isActive
                                  ? [
                                      BoxShadow(
                                        color: const Color(
                                          0xFF001540,
                                        ).withOpacity(0.3),
                                        blurRadius: 10,
                                        offset: const Offset(0, 4),
                                      ),
                                    ]
                                  : null,
                            ),
                            child: Center(
                              child: isDone
                                  ? Icon(
                                      isActive
                                          ? Icons.radio_button_checked_rounded
                                          : Icons.check_rounded,
                                      color: Colors.white,
                                      size: isActive ? 18 : 14,
                                    )
                                  : Text(
                                      '${i + 1}',
                                      style: const TextStyle(
                                        fontFamily: 'DM Sans',
                                        fontWeight: FontWeight.w700,
                                        fontSize: 11,
                                        color: Color(0xFF9AA3B2),
                                      ),
                                    ),
                            ),
                          ),
                          const SizedBox(height: 7),
                          Text(
                            label,
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              fontFamily: 'DM Sans',
                              fontWeight: isDone
                                  ? FontWeight.w700
                                  : FontWeight.w500,
                              fontSize: 9.5,
                              color: isDone
                                  ? const Color(0xFF080F1E)
                                  : const Color(0xFF9AA3B2),
                              letterSpacing: 0.1,
                            ),
                          ),
                        ],
                      ),
                    ),
                    // Connector line
                    if (!isLast)
                      Expanded(
                        child: Container(
                          height: 2.5,
                          margin: const EdgeInsets.only(bottom: 22),
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(2),
                            gradient: i < current
                                ? const LinearGradient(
                                    colors: [
                                      Color(0xFF001540),
                                      Color(0xFF1A4FCC),
                                    ],
                                  )
                                : null,
                            color: i < current ? null : const Color(0xFFE9EDF6),
                          ),
                        ),
                      ),
                  ],
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  // ── Timeline ──────────────────────────────────────────────────────────────
  Widget _buildTimeline() {
    return Column(
      children: widget.events.asMap().entries.map((entry) {
        final i = entry.key;
        final event = entry.value;
        final isLast = i == widget.events.length - 1;
        final isFirst = i == 0;

        return _TimelineItem(
          event: event,
          isLast: isLast,
          isFirst: isFirst,
          index: i,
          entryController: _entryCtrl,
        );
      }).toList(),
    );
  }

  // ── Empty State ───────────────────────────────────────────────────────────
  Widget _buildEmptyState() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 48, horizontal: 24),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0x0E000000)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x07001540),
            blurRadius: 10,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(
              color: const Color(0xFF9AA3B2).withOpacity(0.08),
              borderRadius: BorderRadius.circular(22),
            ),
            child: const Icon(
              Icons.timeline_rounded,
              size: 30,
              color: Color(0xFF9AA3B2),
            ),
          ),
          const SizedBox(height: 16),
          const Text(
            'No tracking history yet',
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontWeight: FontWeight.w700,
              fontSize: 15,
              color: Color(0xFF3D4A63),
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            'Updates will appear here once your\nrequest is reviewed by the Marketing Office',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontSize: 12.5,
              color: Color(0xFF9AA3B2),
              height: 1.55,
            ),
          ),
        ],
      ),
    );
  }
}

// ── Section Label ─────────────────────────────────────────────────────────────
class _SectionLabel extends StatelessWidget {
  final String text;
  const _SectionLabel({required this.text});

  @override
  Widget build(BuildContext context) {
    return Text(
      text.toUpperCase(),
      style: const TextStyle(
        fontFamily: 'DM Sans',
        fontWeight: FontWeight.w800,
        fontSize: 10.5,
        color: Color(0xFF9AA3B2),
        letterSpacing: 1.0,
      ),
    );
  }
}

// ── Timeline Item ─────────────────────────────────────────────────────────────
class _TimelineItem extends StatelessWidget {
  final TrackingEvent event;
  final bool isLast;
  final bool isFirst;
  final int index;
  final AnimationController entryController;

  const _TimelineItem({
    required this.event,
    required this.isLast,
    required this.isFirst,
    required this.index,
    required this.entryController,
  });

  Color get _iconColor {
    switch (event.icon) {
      case Icons.check_circle_outline:
      case Icons.check_circle_rounded:
        return const Color(0xFF05C46B);
      case Icons.cancel_outlined:
      case Icons.cancel_rounded:
        return const Color(0xFFFF3B30);
      case Icons.publish:
      case Icons.send_rounded:
        return const Color(0xFF8B5CF6);
      case Icons.rate_review_outlined:
        return const Color(0xFF2B5CE6);
      default:
        return const Color(0xFF002366);
    }
  }

  @override
  Widget build(BuildContext context) {
    // Staggered entry animation
    final start = (index * 0.12).clamp(0.0, 0.7);
    final end = (start + 0.4).clamp(0.0, 1.0);
    final anim = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: entryController,
        curve: Interval(start, end, curve: Curves.easeOutCubic),
      ),
    );

    return AnimatedBuilder(
      animation: anim,
      builder: (_, child) => Opacity(
        opacity: anim.value,
        child: Transform.translate(
          offset: Offset(0, (1 - anim.value) * 18),
          child: child,
        ),
      ),
      child: IntrinsicHeight(
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── Left: icon + connector line ──────────────────────────────
            SizedBox(
              width: 56,
              child: Column(
                children: [
                  // Icon circle
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      gradient: isFirst
                          ? LinearGradient(
                              begin: Alignment.topLeft,
                              end: Alignment.bottomRight,
                              colors: [
                                _iconColor.withOpacity(0.85),
                                _iconColor,
                              ],
                            )
                          : null,
                      color: isFirst ? null : Colors.white,
                      shape: BoxShape.circle,
                      border: isFirst
                          ? null
                          : Border.all(
                              color: _iconColor.withOpacity(0.3),
                              width: 1.5,
                            ),
                      boxShadow: isFirst
                          ? [
                              BoxShadow(
                                color: _iconColor.withOpacity(0.35),
                                blurRadius: 12,
                                offset: const Offset(0, 4),
                              ),
                            ]
                          : [
                              const BoxShadow(
                                color: Color(0x08001540),
                                blurRadius: 6,
                                offset: Offset(0, 2),
                              ),
                            ],
                    ),
                    child: Icon(
                      event.icon,
                      size: 18,
                      color: isFirst ? Colors.white : _iconColor,
                    ),
                  ),
                  // Connector line
                  if (!isLast)
                    Expanded(
                      child: Container(
                        width: 2,
                        margin: const EdgeInsets.symmetric(vertical: 4),
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.topCenter,
                            end: Alignment.bottomCenter,
                            colors: [
                              _iconColor.withOpacity(0.3),
                              const Color(0xFFE9EDF6),
                            ],
                          ),
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                    ),
                ],
              ),
            ),

            const SizedBox(width: 12),

            // ── Right: card ───────────────────────────────────────────────
            Expanded(
              child: Padding(
                padding: EdgeInsets.only(bottom: isLast ? 0 : 14),
                child: Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(
                      color: isFirst
                          ? _iconColor.withOpacity(0.2)
                          : const Color(0x0E000000),
                      width: isFirst ? 1.5 : 1,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(
                          0xFF001540,
                        ).withOpacity(isFirst ? 0.07 : 0.04),
                        blurRadius: isFirst ? 14 : 8,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: IntrinsicHeight(
                    child: Row(
                      children: [
                        // Left accent bar on first/important events
                        if (isFirst)
                          Container(
                            width: 4,
                            height: 64,
                            decoration: BoxDecoration(
                              color: _iconColor,
                              borderRadius: const BorderRadius.only(
                                topRight: Radius.circular(4),
                                bottomRight: Radius.circular(4),
                              ),
                            ),
                          ),
                        Expanded(
                          child: Padding(
                            padding: EdgeInsets.fromLTRB(
                              isFirst ? 14 : 16,
                              14,
                              16,
                              14,
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Expanded(
                                      child: Text(
                                        event.title,
                                        style: TextStyle(
                                          fontFamily: 'DM Sans',
                                          fontWeight: isFirst
                                              ? FontWeight.w800
                                              : FontWeight.w700,
                                          fontSize: 14.5,
                                          color: const Color(0xFF080F1E),
                                          letterSpacing: -0.2,
                                        ),
                                      ),
                                    ),
                                    if (isFirst)
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 8,
                                          vertical: 3,
                                        ),
                                        decoration: BoxDecoration(
                                          color: _iconColor.withOpacity(0.1),
                                          borderRadius: BorderRadius.circular(
                                            99,
                                          ),
                                        ),
                                        child: Text(
                                          'Latest',
                                          style: TextStyle(
                                            fontFamily: 'DM Sans',
                                            fontWeight: FontWeight.w800,
                                            fontSize: 9.5,
                                            color: _iconColor,
                                            letterSpacing: 0.2,
                                          ),
                                        ),
                                      ),
                                  ],
                                ),
                                const SizedBox(height: 5),
                                Text(
                                  event.subtitle,
                                  style: const TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontWeight: FontWeight.w400,
                                    fontSize: 12.5,
                                    color: Color(0xFF3D4A63),
                                    height: 1.5,
                                  ),
                                ),
                                if (event.timestamp.isNotEmpty) ...[
                                  const SizedBox(height: 8),
                                  Row(
                                    children: [
                                      const Icon(
                                        Icons.access_time_rounded,
                                        size: 12,
                                        color: Color(0xFF9AA3B2),
                                      ),
                                      const SizedBox(width: 4),
                                      Text(
                                        event.timestamp,
                                        style: const TextStyle(
                                          fontFamily: 'DM Sans',
                                          fontWeight: FontWeight.w500,
                                          fontSize: 11,
                                          color: Color(0xFF9AA3B2),
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
