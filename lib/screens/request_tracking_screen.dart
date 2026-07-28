import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';
import '../theme/app_theme.dart';
import '../widgets/media_preview_gallery.dart';

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
  final int? requestId;
  final String requestNumber;
  final String requestTitle;
  final List<TrackingEvent> events;
  final String currentStatus;
  final String currentStatusMessage;
  final String? heroTag;

  const RequestTrackingScreen({
    super.key,
    this.requestId,
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

  bool _isLoading = false;
  String? _errorMessage;

  String _dynamicTitle = '';
  String _dynamicNumber = '';
  String _dynamicStatus = '';
  String _dynamicDescription = '';
  List<TrackingEvent> _dynamicEvents = [];
  List<String> _mediaUrls = [];
  List<String> _mediaFiles = [];

  @override
  void initState() {
    super.initState();
    _dynamicTitle = widget.requestTitle;
    _dynamicNumber = widget.requestNumber;
    _dynamicStatus = widget.currentStatus;
    _dynamicEvents = List.from(widget.events);

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

    if (widget.requestId != null && widget.requestId! > 0) {
      _fetchDetails();
    }
  }

  Future<void> _fetchDetails() async {
    if (widget.requestId == null) return;
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final res = await ApiService.fetchRequestDetails(requestId: widget.requestId!);
      final data = (res['data'] as Map<String, dynamic>?) ?? {};
      final req = (data['request'] as Map<String, dynamic>?) ?? {};
      final rawActs = (data['activities'] as List?) ?? [];

      final urls = (req['media_urls'] as List?)?.cast<String>() ?? [];
      final files = (req['media_files'] as List?)?.cast<String>() ?? [];

      final eventsList = <TrackingEvent>[];
      for (final a in rawActs) {
        final actMap = a as Map<String, dynamic>;
        eventsList.add(TrackingEvent(
          icon: _iconForAction((actMap['action'] ?? '').toString()),
          title: (actMap['actor'] ?? 'System').toString(),
          subtitle: (actMap['action'] ?? '').toString(),
          timestamp: (actMap['created_at'] ?? '').toString(),
        ));
      }

      if (mounted) {
        setState(() {
          _dynamicTitle = (req['title'] ?? _dynamicTitle).toString();
          _dynamicNumber = (req['request_id'] ?? _dynamicNumber).toString();
          _dynamicStatus = (req['status'] ?? _dynamicStatus).toString();
          _dynamicDescription = (req['description'] ?? '').toString();
          _mediaUrls = urls;
          _mediaFiles = files;
          if (eventsList.isNotEmpty) {
            _dynamicEvents = eventsList.reversed.toList();
          }
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = e.toString().replaceAll('Exception: ', '');
          _isLoading = false;
        });
      }
    }
  }

  IconData _iconForAction(String action) {
    final lower = action.toLowerCase();
    if (lower.contains('approved')) return Icons.check_circle_outline;
    if (lower.contains('posted')) return Icons.rocket_launch;
    if (lower.contains('rejected')) return Icons.cancel_outlined;
    if (lower.contains('note') || lower.contains('message')) return Icons.comment_outlined;
    return Icons.rate_review_outlined;
  }

  @override
  void dispose() {
    _entryCtrl.dispose();
    super.dispose();
  }

  Color get _statusColor {
    final s = _dynamicStatus.isNotEmpty ? _dynamicStatus : widget.currentStatus;
    switch (s.toLowerCase()) {
      case 'approved':
        return const Color(0xFF05C46B);
      case 'posted':
        return const Color(0xFF8B5CF6);
      case 'rejected':
        return const Color(0xFFFF3B30);
      case 'pending':
      case 'pending review':
        return const Color(0xFFF59E0B);
      default:
        return const Color(0xFF2B5CE6);
    }
  }

  IconData get _statusIcon {
    final s = _dynamicStatus.isNotEmpty ? _dynamicStatus : widget.currentStatus;
    switch (s.toLowerCase()) {
      case 'approved':
        return Icons.check_circle_rounded;
      case 'posted':
        return Icons.send_rounded;
      case 'rejected':
        return Icons.cancel_rounded;
      case 'pending':
      case 'pending review':
        return Icons.hourglass_empty_rounded;
      default:
        return Icons.info_rounded;
    }
  }

  int get _progressStep {
    final s = _dynamicStatus.isNotEmpty ? _dynamicStatus : widget.currentStatus;
    switch (s.toLowerCase()) {
      case 'approved':
        return 2;
      case 'posted':
        return 3;
      case 'rejected':
        return -1;
      default:
        return 1;
    }
  }

  @override
  Widget build(BuildContext context) {
    final activeStatus = _dynamicStatus.isNotEmpty ? _dynamicStatus : widget.currentStatus;
    final activeTitle = _dynamicTitle.isNotEmpty ? _dynamicTitle : widget.requestTitle;
    final activeNumber = _dynamicNumber.isNotEmpty ? _dynamicNumber : widget.requestNumber;
    final activeEvents = _dynamicEvents.isNotEmpty ? _dynamicEvents : widget.events;

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
                  _buildHeader(activeNumber, activeTitle, activeStatus),
                  Expanded(
                    child: SingleChildScrollView(
                      padding: const EdgeInsets.fromLTRB(16, 20, 16, 110),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          _buildStatusCard(activeStatus),
                          const SizedBox(height: 20),

                          _buildAdminActionBar(),

                          if (_mediaUrls.isNotEmpty || _mediaFiles.isNotEmpty) ...[
                            MediaPreviewGallery(
                              mediaUrls: _mediaUrls,
                              mediaFiles: _mediaFiles,
                            ),
                            const SizedBox(height: 20),
                          ],

                          if (activeStatus.toLowerCase() != 'rejected') ...[
                            _buildProgressStepper(activeStatus),
                            const SizedBox(height: 20),
                          ],

                          if (activeEvents.isNotEmpty) ...[
                            const _SectionLabel(text: 'Activity Timeline'),
                            const SizedBox(height: 12),
                            _buildTimeline(activeEvents),
                          ] else
                            _buildEmptyState(),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAdminActionBar() {
    if (widget.requestId == null || widget.requestId! <= 0) {
      return const SizedBox.shrink();
    }

    return Container(
      padding: const EdgeInsets.all(16),
      margin: const EdgeInsets.only(bottom: 20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFF002366).withOpacity(0.2), width: 1.5),
        boxShadow: const [
          BoxShadow(
            color: Color(0x0A001540),
            blurRadius: 10,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: const [
              Icon(Icons.admin_panel_settings, color: Color(0xFF002366), size: 20),
              SizedBox(width: 8),
              Text(
                'Admin Status Actions',
                style: TextStyle(
                  color: Color(0xFF002366),
                  fontWeight: FontWeight.bold,
                  fontSize: 14,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              _buildAdminStatusBtn('Under Review', Colors.orange, Icons.rate_review_outlined),
              _buildAdminStatusBtn('Approved', Colors.green, Icons.check_circle_outline),
              _buildAdminStatusBtn('Posted', Colors.purple, Icons.rocket_launch),
              _buildAdminStatusBtn('Rejected', Colors.redAccent, Icons.cancel_outlined),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildAdminStatusBtn(String targetStatus, Color color, IconData icon) {
    final isCurrent = _dynamicStatus.toLowerCase() == targetStatus.toLowerCase();

    return ElevatedButton.icon(
      style: ElevatedButton.styleFrom(
        backgroundColor: isCurrent ? color : color.withOpacity(0.12),
        foregroundColor: isCurrent ? Colors.white : color,
        elevation: isCurrent ? 2 : 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(10),
          side: BorderSide(color: color.withOpacity(0.3)),
        ),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      ),
      icon: Icon(icon, size: 16),
      label: Text(
        targetStatus,
        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
      ),
      onPressed: isCurrent ? null : () => _promptStatusChange(targetStatus),
    );
  }

  Future<void> _promptStatusChange(String newStatus) async {
    final noteCtrl = TextEditingController();
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: Text('Change Status to "$newStatus"?'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'This will update the status and notify the requester.',
              style: TextStyle(color: AppColors.inkMute, fontSize: 13),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: noteCtrl,
              decoration: const InputDecoration(
                labelText: 'Internal Admin Note (Optional)',
                hintText: 'e.g., Graphics approved, ready for queue',
                border: OutlineInputBorder(),
              ),
              maxLines: 2,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Update Status'),
          ),
        ],
      ),
    );

    if (confirm == true) {
      try {
        await ApiService.updateRequestStatus(
          requestId: widget.requestId!,
          status: newStatus,
          note: noteCtrl.text.trim(),
          adminName: (SessionStore.name ?? '').isNotEmpty ? SessionStore.name! : 'Admin',
        );

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Status updated to $newStatus')),
          );
          _fetchDetails();
        }
      } catch (e) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text('Failed: $e'), backgroundColor: Colors.redAccent),
          );
        }
      }
    }
  }

  // ── Header ────────────────────────────────────────────────────────────────
  Widget _buildHeader(String number, String title, String status) {
    final topPad = MediaQuery.of(context).padding.top;
    final displayStatus = status.isNotEmpty ? status : widget.currentStatus;
    final displayNumber = number.isNotEmpty ? number : widget.requestNumber;
    final displayTitle = title.isNotEmpty ? title : widget.requestTitle;

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
              if (displayStatus.isNotEmpty)
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
                        displayStatus,
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
          if (displayNumber.isNotEmpty || displayTitle.isNotEmpty)
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 6, 0, 0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (displayNumber.isNotEmpty)
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
                        displayNumber,
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w700,
                          fontSize: 10,
                          color: Color(0xFF3D4A63),
                          letterSpacing: 0.4,
                        ),
                      ),
                    ),
                  if (displayTitle.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text(
                      displayTitle,
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
  Widget _buildStatusCard(String status) {
    final displayStatus = status.isNotEmpty ? status : widget.currentStatus;
    final isRejected = displayStatus.toLowerCase() == 'rejected';

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
                  displayStatus.isEmpty
                      ? 'Processing'
                      : displayStatus,
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
                  _dynamicDescription.isNotEmpty
                      ? _dynamicDescription
                      : (widget.currentStatusMessage.isEmpty
                          ? 'Your request is being processed.'
                          : widget.currentStatusMessage),
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
  Widget _buildProgressStepper(String status) {
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
  Widget _buildTimeline(List<TrackingEvent> events) {
    final list = events.isNotEmpty ? events : widget.events;
    return Column(
      children: list.asMap().entries.map((entry) {
        final i = entry.key;
        final event = entry.value;
        final isLast = i == list.length - 1;
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
