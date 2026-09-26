import 'dart:async';
import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';
import '../theme/app_theme.dart';
import '../widgets/media_preview_gallery.dart';
import 'create_request_screen.dart';
import 'message_thread_screen.dart';

// ── Public data model (used by other screens) ─────────────────────────────────
class TrackingEvent {
  final IconData icon;
  final String title;
  final String subtitle;
  final String timestamp;
  final String? role; // 'admin', 'requestor', 'system'

  const TrackingEvent({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.timestamp,
    this.role,
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

  // ignore: unused_field
  bool _isLoading = false;
  // ignore: unused_field
  String? _errorMessage;

  String _dynamicTitle = '';
  String _dynamicNumber = '';
  String _dynamicStatus = '';
  String _dynamicDescription = '';
  String _dynamicCategory = '';
  String _dynamicPriority = '';
  String _dynamicPreferredDate = '';
  String _dynamicCaption = '';
  String _dynamicPlatform = '';
  String _adminRejectionNote = '';

  List<TrackingEvent> _dynamicEvents = [];
  List<String> _mediaUrls = [];
  List<String> _mediaFiles = [];
  Timer? _pollTimer;

  final TextEditingController _quickReplyCtrl = TextEditingController();
  bool _isSendingReply = false;

  bool get _isEditable {
    if (SessionStore.isAdmin) return false;
    final s = _dynamicStatus.isNotEmpty ? _dynamicStatus : widget.currentStatus;
    final lower = s.toLowerCase().trim();
    return lower == 'pending' || lower == 'pending review' || lower == 'rejected';
  }

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
      // Near-realtime background sync for messages and timeline updates
      _pollTimer = Timer.periodic(const Duration(seconds: 3), (_) {
        if (mounted && !_isSendingReply && !_isLoading) {
          _fetchDetails(silent: true);
        }
      });
    }
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _entryCtrl.dispose();
    _quickReplyCtrl.dispose();
    super.dispose();
  }

  Future<void> _sendQuickReply() async {
    final text = _quickReplyCtrl.text.trim();
    if (text.isEmpty || _isSendingReply || widget.requestId == null || widget.requestId! <= 0) return;

    final userId = SessionStore.userId;
    if (userId == null || userId == 0) return;

    setState(() => _isSendingReply = true);
    try {
      await ApiService.sendMessage(
        userId: userId,
        requestId: widget.requestId!,
        message: text,
      );
      _quickReplyCtrl.clear();
      await _fetchDetails();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Message sent successfully!'),
            behavior: SnackBarBehavior.floating,
            duration: Duration(seconds: 2),
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to send message: ${e.toString().replaceFirst("Exception: ", "")}'),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isSendingReply = false);
    }
  }

  void _openFullConversation() {
    if (widget.requestId == null || widget.requestId! <= 0) return;
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => MessageThreadScreen(
          requestId: widget.requestId!,
          requestCode: _dynamicNumber.isNotEmpty ? _dynamicNumber : widget.requestNumber,
          requestTitle: _dynamicTitle.isNotEmpty ? _dynamicTitle : widget.requestTitle,
          requestStatus: _dynamicStatus.isNotEmpty ? _dynamicStatus : widget.currentStatus,
        ),
      ),
    ).then((_) => _fetchDetails());
  }

  Future<void> _fetchDetails({bool silent = false}) async {
    if (widget.requestId == null) return;
    if (!silent) {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
      });
    }
    try {
      final res = await ApiService.fetchRequestDetails(requestId: widget.requestId!);
      final data = (res['data'] as Map<String, dynamic>?) ?? {};
      final req = (data['request'] as Map<String, dynamic>?) ?? {};
      final rawActs = (data['activities'] as List?) ?? [];

      final urls = (req['media_urls'] as List?)?.cast<String>() ?? [];
      final files = (req['media_files'] as List?)?.cast<String>() ?? [];

      final eventsList = <TrackingEvent>[];
      String foundNote = '';
      for (final a in rawActs) {
        final actMap = a as Map<String, dynamic>;
        var actStr = (actMap['action'] ?? '').toString();
        var actor = (actMap['actor'] ?? '').toString().trim();
        var role = (actMap['role'] ?? '').toString().toLowerCase().trim();

        if (actor.isEmpty) {
          actor = (role == 'admin') ? 'System Admin' : 'Requestor';
        }

        if (role.isEmpty) {
          final lowerActor = actor.toLowerCase();
          final lowerAct = actStr.toLowerCase();
          if (lowerActor.contains('admin') || lowerAct.contains('admin note:')) {
            role = 'admin';
          } else if (lowerActor.contains('system')) {
            role = 'system';
          } else {
            role = 'requestor';
          }
        }

        // Clean up message prefixes for cleaner display
        if (actStr.startsWith('Internal note:')) {
          foundNote = actStr.substring('Internal note:'.length).trim();
          actStr = foundNote;
        } else if (actStr.startsWith('Admin note:')) {
          actStr = actStr.substring('Admin note:'.length).trim();
        } else if (actStr.startsWith('Requestor message:')) {
          actStr = actStr.substring('Requestor message:'.length).trim();
        } else if (foundNote.isEmpty && actStr.toLowerCase().contains('rejected')) {
          final parts = actStr.split(':');
          if (parts.length > 1) {
            foundNote = parts.sublist(1).join(':').trim();
          }
        }

        eventsList.add(TrackingEvent(
          icon: _iconForAction(actStr),
          title: actor,
          subtitle: actStr,
          timestamp: (actMap['created_at'] ?? '').toString(),
          role: role,
        ));
      }

      if (mounted) {
        final currentStat = (req['status'] ?? _dynamicStatus).toString();
        setState(() {
          _dynamicTitle = (req['title'] ?? _dynamicTitle).toString();
          _dynamicNumber = (req['request_id'] ?? _dynamicNumber).toString();
          _dynamicStatus = currentStat;
          _dynamicDescription = (req['description'] ?? '').toString();
          _dynamicCategory = (req['category'] ?? '').toString();
          _dynamicPriority = (req['priority'] ?? '').toString();
          _dynamicPreferredDate = (req['preferred_date'] ?? '').toString();
          _dynamicCaption = (req['caption'] ?? '').toString();
          _dynamicPlatform = (req['platform'] ?? '').toString();
          _adminRejectionNote = currentStat.toLowerCase() == 'rejected' ? foundNote : '';
          _mediaUrls = urls;
          _mediaFiles = files;
          if (eventsList.isNotEmpty) {
            _dynamicEvents = eventsList.reversed.toList();
          }
          if (!silent) _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          if (!silent) {
            _errorMessage = e.toString().replaceAll('Exception: ', '');
            _isLoading = false;
          }
        });
      }
    }
  }

  void _openEditRequest() async {
    if (widget.requestId == null || widget.requestId! <= 0) return;
    final reqData = {
      'id': widget.requestId,
      'title': _dynamicTitle.isNotEmpty ? _dynamicTitle : widget.requestTitle,
      'description': _dynamicDescription,
      'status': _dynamicStatus,
      'category': _dynamicCategory,
      'priority': _dynamicPriority,
      'preferred_date': _dynamicPreferredDate,
      'caption': _dynamicCaption,
      'platform': _dynamicPlatform,
      'media_urls': _mediaUrls,
      'media_files': _mediaFiles,
    };

    final result = await Navigator.push<bool>(
      context,
      MaterialPageRoute(
        builder: (_) => Scaffold(
          body: CreateRequestScreen(
            initialData: reqData,
            isEditing: true,
          ),
        ),
      ),
    );

    if (result == true) {
      if (mounted) {
        setState(() {
          _dynamicStatus = 'Pending Review';
          _adminRejectionNote = '';
        });
      }
      _fetchDetails();
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

    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF090D16) : const Color(0xFFE9EDF6),
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

                          // Admin Rejection Message (ONLY when status is Rejected)
                          if (activeStatus.toLowerCase() == 'rejected' && _adminRejectionNote.isNotEmpty) ...[
                            const SizedBox(height: 14),
                            Container(
                              width: double.infinity,
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFEF2F2),
                                borderRadius: BorderRadius.circular(18),
                                border: Border.all(color: const Color(0xFFFCA5A5), width: 1.5),
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
                                      Icon(Icons.report_problem_rounded, color: Color(0xFFDC2626), size: 20),
                                      SizedBox(width: 8),
                                      Text(
                                        'Admin Rejection Reason',
                                        style: TextStyle(
                                          fontFamily: 'DM Sans',
                                          fontWeight: FontWeight.w800,
                                          fontSize: 14,
                                          color: Color(0xFF991B1B),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  Text(
                                    _adminRejectionNote.isNotEmpty
                                        ? _adminRejectionNote
                                        : 'This request was rejected by an admin. Please make necessary changes and re-submit for review.',
                                    style: const TextStyle(
                                      fontFamily: 'DM Sans',
                                      fontSize: 13,
                                      fontWeight: FontWeight.w600,
                                      color: Color(0xFF7F1D1D),
                                      height: 1.4,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],

                          // Re-submit Request button card (Requestor only)
                          if (_isEditable) ...[
                            const SizedBox(height: 14),
                            Container(
                              width: double.infinity,
                              decoration: BoxDecoration(
                                gradient: const LinearGradient(
                                  begin: Alignment.topLeft,
                                  end: Alignment.bottomRight,
                                  colors: [Color(0xFF001540), Color(0xFF003080)],
                                ),
                                borderRadius: BorderRadius.circular(18),
                                boxShadow: const [
                                  BoxShadow(
                                    color: Color(0x30001540),
                                    blurRadius: 14,
                                    offset: Offset(0, 5),
                                  ),
                                ],
                              ),
                              child: Material(
                                color: Colors.transparent,
                                child: InkWell(
                                  onTap: _openEditRequest,
                                  borderRadius: BorderRadius.circular(18),
                                  child: Padding(
                                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                                    child: Row(
                                      children: [
                                        Container(
                                          width: 40,
                                          height: 40,
                                          decoration: BoxDecoration(
                                            color: Colors.white.withOpacity(0.15),
                                            borderRadius: BorderRadius.circular(12),
                                          ),
                                          child: const Icon(
                                            Icons.replay_rounded,
                                            color: Colors.white,
                                            size: 22,
                                          ),
                                        ),
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                (_dynamicStatus.isNotEmpty ? _dynamicStatus : widget.currentStatus).toLowerCase().contains('reject')
                                                    ? 'Re-submit Request'
                                                    : 'Edit Request',
                                                style: const TextStyle(
                                                  fontFamily: 'DM Sans',
                                                  fontWeight: FontWeight.w900,
                                                  fontSize: 15,
                                                  color: Colors.white,
                                                ),
                                              ),
                                              const SizedBox(height: 2),
                                              Text(
                                                (_dynamicStatus.isNotEmpty ? _dynamicStatus : widget.currentStatus).toLowerCase().contains('reject')
                                                    ? 'Tap to edit details or pictures and send back to admin'
                                                    : 'Tap to edit details or media before review begins',
                                                style: const TextStyle(
                                                  fontFamily: 'DM Sans',
                                                  fontSize: 12,
                                                  color: Color(0xFFD0D9F0),
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                        const Icon(
                                          Icons.arrow_forward_ios_rounded,
                                          color: Colors.white,
                                          size: 16,
                                        ),
                                      ],
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ],
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

                          _buildQuickReplySection(isDark),
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
    if (!SessionStore.isAdmin || widget.requestId == null || widget.requestId! <= 0) {
      return const SizedBox.shrink();
    }

    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      padding: const EdgeInsets.all(16),
      margin: const EdgeInsets.only(bottom: 20),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF131D31) : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isDark ? const Color(0xFF1E2B45) : const Color(0xFF002366).withOpacity(0.2),
          width: 1.5,
        ),
        boxShadow: [
          BoxShadow(
            color: isDark ? const Color(0x30000000) : const Color(0x0A001540),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.admin_panel_settings, color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF002366), size: 20),
              const SizedBox(width: 8),
              Text(
                'Admin Status Actions',
                style: TextStyle(
                  color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF002366),
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
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF131D31) : Colors.white,
        border: Border(
          bottom: BorderSide(
            color: isDark ? const Color(0xFF1E2B45) : const Color(0x0F000000),
            width: 1,
          ),
        ),
        boxShadow: [
          BoxShadow(
            color: isDark ? const Color(0x40000000) : const Color(0x07001540),
            blurRadius: 12,
            offset: const Offset(0, 1),
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
                icon: Icon(
                  Icons.arrow_back_ios_new_rounded,
                  color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF002366),
                  size: 18,
                ),
              ),
              Expanded(
                child: Text(
                  'Request Tracking',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w900,
                    fontSize: 20,
                    color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF080F1E),
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
              if (_isEditable) ...[
                const SizedBox(width: 8),
                InkWell(
                  onTap: _openEditRequest,
                  borderRadius: BorderRadius.circular(10),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: const Color(0xFF002366).withOpacity(0.08),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFF002366).withOpacity(0.2)),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.edit_note_rounded, size: 16, color: Color(0xFF002366)),
                        SizedBox(width: 4),
                        Text(
                          'Edit',
                          style: TextStyle(
                            fontFamily: 'DM Sans',
                            fontWeight: FontWeight.w800,
                            fontSize: 12,
                            color: Color(0xFF002366),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
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
                        color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFE9EDF6),
                        borderRadius: BorderRadius.circular(7),
                      ),
                      child: Text(
                        displayNumber,
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w700,
                          fontSize: 10,
                          color: isDark ? const Color(0xFF93C5FD) : const Color(0xFF3D4A63),
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
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w600,
                        fontSize: 13.5,
                        color: isDark ? const Color(0xFFCBD5E1) : const Color(0xFF080F1E),
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
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 18),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF131D31) : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: isDark ? const Color(0xFF1E2B45) : const Color(0x0E000000)),
        boxShadow: [
          BoxShadow(
            color: isDark ? const Color(0x30000000) : const Color(0x07001540),
            blurRadius: 10,
            offset: const Offset(0, 4),
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
          LayoutBuilder(
            builder: (context, constraints) {
              final stepWidth = constraints.maxWidth / steps.length;
              return Stack(
                alignment: Alignment.topCenter,
                children: [
                  // Connector track behind the circles
                  Positioned(
                    top: 15,
                    left: stepWidth / 2,
                    right: stepWidth / 2,
                    child: Row(
                      children: List.generate(steps.length - 1, (i) {
                        final isPassed = i < current;
                        return Expanded(
                          child: Container(
                            height: 2.5,
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(2),
                              gradient: isPassed
                                  ? const LinearGradient(
                                      colors: [
                                        Color(0xFF001540),
                                        Color(0xFF1A4FCC),
                                      ],
                                    )
                                  : null,
                              color: isPassed ? null : const Color(0xFFE9EDF6),
                            ),
                          ),
                        );
                      }),
                    ),
                  ),
                  // The 4 step nodes
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: steps.asMap().entries.map((entry) {
                      final i = entry.key;
                      final label = entry.value;
                      final isDone = i <= current;
                      final isActive = i == current;

                      return Expanded(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            // Step circle
                            SizedBox(
                              width: 34,
                              height: 34,
                              child: Center(
                                child: AnimatedContainer(
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
                                              color: const Color(0xFF001540).withValues(alpha: 0.3),
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
                              ),
                            ),
                            const SizedBox(height: 7),
                            Text(
                              label,
                              textAlign: TextAlign.center,
                              maxLines: 1,
                              overflow: TextOverflow.visible,
                              softWrap: false,
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontWeight: isDone
                                    ? FontWeight.w700
                                    : FontWeight.w500,
                                fontSize: 10,
                                color: isDone
                                    ? const Color(0xFF080F1E)
                                    : const Color(0xFF9AA3B2),
                                letterSpacing: 0.1,
                              ),
                            ),
                          ],
                        ),
                      );
                    }).toList(),
                  ),
                ],
              );
            },
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
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(vertical: 48, horizontal: 24),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF131D31) : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: isDark ? const Color(0xFF1E2B45) : const Color(0x0E000000)),
        boxShadow: [
          BoxShadow(
            color: isDark ? const Color(0x30000000) : const Color(0x07001540),
            blurRadius: 10,
            offset: const Offset(0, 4),
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
              color: isDark
                  ? const Color(0xFF1E2B45).withOpacity(0.5)
                  : const Color(0xFF9AA3B2).withOpacity(0.08),
              borderRadius: BorderRadius.circular(22),
            ),
            child: Icon(
              Icons.timeline_rounded,
              size: 30,
              color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF9AA3B2),
            ),
          ),
          const SizedBox(height: 16),
          Text(
            'No tracking history yet',
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontWeight: FontWeight.w700,
              fontSize: 15,
              color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF3D4A63),
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'Updates will appear here once your\nrequest is reviewed by the Marketing Office',
            textAlign: TextAlign.center,
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontSize: 12.5,
              color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF9AA3B2),
              height: 1.55,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQuickReplySection(bool isDark) {
    if (widget.requestId == null || widget.requestId! <= 0) {
      return const SizedBox.shrink();
    }

    final cardBg = isDark ? const Color(0xFF131D31) : Colors.white;
    final borderColor = isDark ? const Color(0xFF1E2B45) : const Color(0x0E000000);
    final fieldBg = isDark ? const Color(0xFF0D1527) : const Color(0xFFF1F4FB);
    final fieldBorder = isDark ? const Color(0xFF1E2B45) : const Color(0x0A000000);
    final textColor = isDark ? const Color(0xFFF1F5F9) : const Color(0xFF080F1E);
    final hintColor = isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2);
    final isAdmin = SessionStore.isAdmin;

    return Container(
      margin: const EdgeInsets.only(top: 20),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: borderColor),
        boxShadow: [
          BoxShadow(
            color: isDark ? const Color(0x30000000) : const Color(0x07001540),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.forum_outlined, color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF002366), size: 18),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  isAdmin ? 'Reply to Requester' : 'Comments & Discussion',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.bold,
                    fontSize: 14,
                    color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF002366),
                  ),
                ),
              ),
              InkWell(
                onTap: _openFullConversation,
                borderRadius: BorderRadius.circular(8),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  child: Row(
                    children: [
                      Text(
                        'Full Chat',
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w700,
                          fontSize: 12,
                          color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF2B5CE6),
                        ),
                      ),
                      const SizedBox(width: 4),
                      Icon(
                        Icons.open_in_new_rounded,
                        size: 13,
                        color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF2B5CE6),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: Container(
                  decoration: BoxDecoration(
                    color: fieldBg,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: fieldBorder),
                  ),
                  child: TextField(
                    controller: _quickReplyCtrl,
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      fontSize: 13,
                      color: textColor,
                    ),
                    decoration: InputDecoration(
                      hintText: isAdmin ? 'Type reply to requester...' : 'Ask a question or leave a note...',
                      hintStyle: TextStyle(
                        fontFamily: 'DM Sans',
                        color: hintColor,
                        fontSize: 13,
                      ),
                      filled: false,
                      border: InputBorder.none,
                      enabledBorder: InputBorder.none,
                      focusedBorder: InputBorder.none,
                      contentPadding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 10,
                      ),
                      isDense: true,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              GestureDetector(
                onTap: _isSendingReply ? null : _sendQuickReply,
                child: Container(
                  width: 42,
                  height: 42,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [Color(0xFF001540), Color(0xFF1A4FCC)],
                    ),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: Center(
                    child: _isSendingReply
                        ? const SizedBox(
                            width: 16,
                            height: 16,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              valueColor: AlwaysStoppedAnimation(Colors.white),
                            ),
                          )
                        : const Icon(
                            Icons.send_rounded,
                            color: Colors.white,
                            size: 18,
                          ),
                  ),
                ),
              ),
            ],
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
    final isDark = Theme.of(context).brightness == Brightness.dark;

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
                      color: isFirst ? null : (isDark ? const Color(0xFF131D31) : Colors.white),
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
                              BoxShadow(
                                color: isDark ? const Color(0x30000000) : const Color(0x08001540),
                                blurRadius: 6,
                                offset: const Offset(0, 2),
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
                              isDark ? const Color(0xFF1E2B45) : const Color(0xFFE9EDF6),
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
                    color: isDark ? const Color(0xFF131D31) : Colors.white,
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(
                      color: isDark
                          ? (isFirst ? _iconColor.withOpacity(0.4) : const Color(0xFF1E2B45))
                          : (isFirst ? _iconColor.withOpacity(0.2) : const Color(0x0E000000)),
                      width: isFirst ? 1.5 : 1,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: isDark
                            ? const Color(0x30000000)
                            : const Color(0xFF001540).withOpacity(isFirst ? 0.07 : 0.04),
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
                                      child: Row(
                                        children: [
                                          Flexible(
                                            child: Text(
                                              event.title,
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                              style: TextStyle(
                                                fontFamily: 'DM Sans',
                                                fontWeight: isFirst
                                                    ? FontWeight.w800
                                                    : FontWeight.w700,
                                                fontSize: 14.5,
                                                color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF080F1E),
                                                letterSpacing: -0.2,
                                              ),
                                            ),
                                          ),
                                          if ((event.role ?? '').toLowerCase() == 'admin' ||
                                              (event.role ?? '').toLowerCase() == 'requestor') ...[
                                            const SizedBox(width: 8),
                                            Container(
                                              padding: const EdgeInsets.symmetric(
                                                horizontal: 7,
                                                vertical: 2,
                                              ),
                                              decoration: BoxDecoration(
                                                color: (event.role?.toLowerCase() == 'admin')
                                                    ? const Color(0xFF002366)
                                                    : (isDark ? const Color(0xFF1E293B) : const Color(0xFFEFF6FF)),
                                                borderRadius: BorderRadius.circular(6),
                                              ),
                                              child: Text(
                                                (event.role?.toLowerCase() == 'admin')
                                                    ? 'ADMIN'
                                                    : 'REQUESTOR',
                                                style: TextStyle(
                                                  fontFamily: 'DM Sans',
                                                  fontWeight: FontWeight.w800,
                                                  fontSize: 9,
                                                  color: (event.role?.toLowerCase() == 'admin')
                                                      ? const Color(0xFFFFD400)
                                                      : (isDark ? const Color(0xFF60A5FA) : const Color(0xFF2563EB)),
                                                  letterSpacing: 0.5,
                                                ),
                                              ),
                                            ),
                                          ],
                                        ],
                                      ),
                                    ),
                                    if (isFirst)
                                      Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 8,
                                          vertical: 3,
                                        ),
                                        decoration: BoxDecoration(
                                          color: _iconColor.withValues(alpha: 0.1),
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
                                  style: TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontWeight: FontWeight.w400,
                                    fontSize: 12.5,
                                    color: isDark ? const Color(0xFFCBD5E1) : const Color(0xFF3D4A63),
                                    height: 1.5,
                                  ),
                                ),
                                if (event.timestamp.isNotEmpty) ...[
                                  const SizedBox(height: 8),
                                  Row(
                                    children: [
                                      Icon(
                                        Icons.access_time_rounded,
                                        size: 12,
                                        color: isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2),
                                      ),
                                      const SizedBox(width: 4),
                                      Text(
                                        event.timestamp,
                                        style: TextStyle(
                                          fontFamily: 'DM Sans',
                                          fontWeight: FontWeight.w500,
                                          fontSize: 11,
                                          color: isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2),
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

