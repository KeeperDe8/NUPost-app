import 'dart:async';
import 'package:flutter/material.dart';
import 'request_tracking_screen.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';
import '../services/app_memory_cache.dart';
import '../widgets/skeleton_loader.dart';

class RequestsScreen extends StatefulWidget {
  const RequestsScreen({super.key});

  @override
  State<RequestsScreen> createState() => _RequestsScreenState();
}

class _RequestsScreenState extends State<RequestsScreen>
    with TickerProviderStateMixin {
  late TabController _tabController;
  late AnimationController _staggerController;
  late AnimationController _entryController;
  late Animation<double> _entryFade;
  late Animation<Offset> _entrySlide;

  bool _isLoading = false;
  String? _error;
  final List<String> _tabs = ['All', 'Pending', 'Approved', 'Posted'];
  List<_RequestPreview> _requests = const [];
  final TextEditingController _searchController = TextEditingController();
  String _searchQuery = '';
  Timer? _liveSyncTimer;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: _tabs.length, vsync: this);
    _staggerController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    );
    _entryController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 600),
    )..forward();
    _entryFade = CurvedAnimation(
      parent: _entryController,
      curve: const Interval(0.0, 0.7, curve: Curves.easeOut),
    );
    _entrySlide = Tween<Offset>(begin: const Offset(0, 0.04), end: Offset.zero)
        .animate(
          CurvedAnimation(
            parent: _entryController,
            curve: const Interval(0.0, 0.8, curve: Curves.easeOutCubic),
          ),
        );

    // Warm-start from session cache: 0ms instant display, no skeleton if loaded in this session
    if (AppMemoryCache.hasRequests) {
      _requests = _mapRowsToPreviews(AppMemoryCache.requests!);
      _isLoading = false;
    }

    _tabController.addListener(() {
      setState(() {});
      if (!_tabController.indexIsChanging) _loadRequests();
    });
    _loadRequests();
    AppMemoryCache.requestsRevision.addListener(_onRequestsChanged);

    // Real-time background sync (every 3 seconds) for live status updates
    _liveSyncTimer = Timer.periodic(const Duration(seconds: 3), (_) {
      if (mounted && !_isLoading) _loadRequests(silent: true);
    });
  }

  @override
  void dispose() {
    _liveSyncTimer?.cancel();
    AppMemoryCache.requestsRevision.removeListener(_onRequestsChanged);
    _searchController.dispose();
    _tabController.dispose();
    _staggerController.dispose();
    _entryController.dispose();
    super.dispose();
  }

  void _onRequestsChanged() {
    if (!mounted) return;
    _loadRequests(silent: true);
  }

  void _replayStagger() {
    _staggerController.reset();
    _staggerController.forward();
  }

  List<_RequestPreview> _mapRowsToPreviews(List<Map<String, dynamic>> rows) {
    return rows.map((row) {
      final id = (row['id'] as num?)?.toInt() ?? 0;
      final reqNo = (row['request_id'] ?? '').toString();
      final createdAt = (row['created_at'] ?? '').toString();
      final displayNo = AppMemoryCache.getDisplayRequestNumber(
        id: id,
        rawNumber: reqNo,
        isAdmin: SessionStore.isAdmin,
      );
      return _RequestPreview(
        id: id,
        number: displayNo,
        title: (row['title'] ?? '').toString(),
        status: (row['status'] ?? 'Pending').toString(),
        submittedAt: createdAt.isEmpty
            ? 'Submitted —'
            : 'Submitted $createdAt',
        priority: (row['priority'] ?? '').toString(),
      );
    }).toList();
  }

  Future<void> _loadRequests({bool silent = false}) async {
    final userId = SessionStore.userId;
    if (userId == null && !SessionStore.isAdmin) {
      if (!silent) {
        setState(() {
          _error = 'Login first.';
          _requests = const [];
        });
      }
      return;
    }
    if (!silent) {
      setState(() {
        _isLoading = _requests.isEmpty;
        _error = null;
      });
    }
    try {
      final status = _statusForTab(_tabs[_tabController.index]);
      final List<Map<String, dynamic>> rows;
      if (SessionStore.isAdmin) {
        rows = await ApiService.fetchAdminRequests(status: status ?? 'all');
      } else {
        rows = await ApiService.fetchRequests(
          userId: userId!,
          status: status,
        );
        AppMemoryCache.updateUserRequestSequence(rows);
      }
      final mapped = _mapRowsToPreviews(rows);
      if (_tabController.index == 0) {
        AppMemoryCache.requests = rows;
      }

      bool hasChanges = false;
      if (mapped.length != _requests.length) {
        hasChanges = true;
      } else {
        for (int i = 0; i < mapped.length; i++) {
          if (mapped[i].id != _requests[i].id || mapped[i].status != _requests[i].status) {
            hasChanges = true;
            break;
          }
        }
      }

      if (hasChanges || !silent) {
        setState(() {
          _requests = mapped;
          _error = null;
        });
        if (hasChanges && !silent) {
          _replayStagger();
        }
      }
    } catch (e) {
      if (!silent) {
        setState(() {
          _error = e.toString().replaceFirst('Exception: ', '');
          if (_requests.isEmpty) _requests = const [];
        });
      }
    } finally {
      if (mounted && !silent) setState(() => _isLoading = false);
    }
  }

  String? _statusForTab(String tab) {
    switch (tab) {
      case 'Pending':
        return 'Pending';
      case 'Approved':
        return 'Approved';
      case 'Posted':
        return 'Posted';
      default:
        return null;
    }
  }

  Future<void> _openRequestDetails(
    BuildContext context,
    _RequestPreview req,
  ) async {
    showDialog(
      context: context,
      useRootNavigator: false,
      barrierDismissible: false,
      builder: (_) => const Center(
        child: CircularProgressIndicator(color: Color(0xFF002366)),
      ),
    );
    try {
      final response = await ApiService.fetchRequestDetails(requestId: req.id);
      if (!context.mounted) return;
      Navigator.of(context).pop();
      if (response['success'] == true) {
        final data = response['data'] ?? {};
        final request = data['request'] ?? {};
        final activities = (data['activities'] as List?) ?? [];
        final events = <TrackingEvent>[];
        events.add(
          TrackingEvent(
            icon: Icons.send_outlined,
            title: 'Request Submitted',
            subtitle: 'Your request was sent successfully.',
            timestamp: _fmt(request['created_at'] ?? ''),
          ),
        );
        for (final activity in activities) {
          final action = (activity['action'] ?? '').toString();
          IconData icon = Icons.info_outline;
          String title = 'Update';
          String sub = action;
          if (action.contains('Under Review')) {
            icon = Icons.rate_review_outlined;
            title = 'Under Review';
            sub = 'Marketing team is evaluating your request.';
          } else if (action.contains('Approved')) {
            icon = Icons.check_circle_outline;
            title = 'Approved';
            sub = 'Your request has been approved.';
          } else if (action.contains('Rejected')) {
            icon = Icons.cancel_outlined;
            title = 'Rejected';
            sub = 'Your request was not approved.';
          } else if (action.contains('Posted')) {
            icon = Icons.publish;
            title = 'Posted';
            sub = 'Your content has been published.';
          } else if (action.contains('Pending')) {
            icon = Icons.hourglass_empty;
            title = 'Pending Review';
            sub = 'Your request is waiting for review.';
          } else if (action.contains('Internal note')) {
            icon = Icons.comment_outlined;
            title = 'Note Added';
            sub = action.replaceFirst('Internal note: ', '');
          }
          events.add(
            TrackingEvent(
              icon: icon,
              title: title,
              subtitle: sub,
              timestamp: _fmt(activity['created_at'] ?? ''),
            ),
          );
        }
        final cs = request['status'] ?? 'Pending';
        String sm = 'Your request is being processed.';
        if (cs == 'Pending') {
          sm = 'Your request is currently queued for review.';
        }
        if (cs == 'Approved') {
          sm = 'Your request has been approved by the Marketing Office.';
        }
        if (cs == 'Posted') {
          sm = 'Your content has been successfully published.';
        }
        if (cs == 'Rejected') {
          sm = 'Your request was not approved. Please check feedback.';
        }
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => RequestTrackingScreen(
              requestId: req.id,
              heroTag: 'request-${req.id}',
              requestNumber: req.number,
              requestTitle: req.title,
              currentStatus: cs,
              currentStatusMessage: sm,
              events: events,
            ),
          ),
        );
      }
    } catch (e) {
      if (context.mounted) {
        Navigator.of(context).pop();
        ScaffoldMessenger.of(
          context,
        ).showSnackBar(SnackBar(content: Text('Error: $e')));
      }
    }
  }

  String _fmt(String dt) {
    if (dt.isEmpty) return '';
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
      final h = d.hour > 12 ? d.hour - 12 : (d.hour == 0 ? 12 : d.hour);
      return '${m[d.month - 1]} ${d.day}, ${d.year} · $h:${d.minute.toString().padLeft(2, '0')} ${d.hour >= 12 ? 'PM' : 'AM'}';
    } catch (_) {
      return dt;
    }
  }

  @override
  Widget build(BuildContext context) {
    final topPad = MediaQuery.of(context).padding.top;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Scaffold(
      resizeToAvoidBottomInset: false,
      backgroundColor: isDark ? const Color(0xFF0A0F1D) : const Color(0xFFE9EDF6),
      body: Stack(
        children: [
          FadeTransition(
            opacity: _entryFade,
            child: SlideTransition(
              position: _entrySlide,
              child: Column(
                children: [
                  // ── HERO ─────────────────────────────────────────────────
                  Container(
                    decoration: const BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                        colors: [
                          Color(0xFF001540),
                          Color(0xFF002878),
                          Color(0xFF1243B0),
                        ],
                        stops: [0.0, 0.5, 1.0],
                      ),
                      borderRadius: BorderRadius.only(
                        bottomLeft: Radius.circular(32),
                        bottomRight: Radius.circular(32),
                      ),
                    ),
                    child: Stack(
                      children: [
                        Positioned(
                          top: -40,
                          right: -40,
                          child: Container(
                            width: 200,
                            height: 200,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: Colors.white.withOpacity(0.05),
                            ),
                          ),
                        ),
                        Padding(
                          padding: EdgeInsets.fromLTRB(22, topPad + 18, 22, 24),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                SessionStore.isAdmin
                                    ? 'Manage Requests'
                                    : 'My Requests',
                                style: const TextStyle(
                                  fontFamily: 'DM Sans',
                                  fontWeight: FontWeight.w900,
                                  fontSize: 28,
                                  color: Colors.white,
                                  letterSpacing: -0.6,
                                ),
                              ),
                              const SizedBox(height: 5),
                              Text(
                                SessionStore.isAdmin
                                    ? 'Review and manage submission statuses'
                                    : 'Track your submission status',
                                style: TextStyle(
                                  fontFamily: 'DM Sans',
                                  fontSize: 13,
                                  color: Colors.white.withOpacity(0.45),
                                ),
                              ),
                              const SizedBox(height: 18),
                              Row(
                                children: [
                                  _MiniStat(
                                    label: 'Total',
                                    value: '${_requests.length}',
                                    color: Colors.white,
                                  ),
                                  const SizedBox(width: 8),
                                  _MiniStat(
                                    label: 'Pending',
                                    value:
                                        '${_requests.where((r) => r.status.toLowerCase().contains('pending') || r.status.toLowerCase().contains('review')).length}',
                                    color: const Color(0xFFFBBF24),
                                  ),
                                  const SizedBox(width: 8),
                                  _MiniStat(
                                    label: 'Approved',
                                    value:
                                        '${_requests.where((r) => r.status == 'Approved').length}',
                                    color: const Color(0xFF34D399),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  // ── TAB BAR ───────────────────────────────────────────────
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 14, 16, 8),
                    child: Container(
                      height: 44,
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0xFF131D31) : Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(
                          color: isDark ? const Color(0xFF1E2B45) : Colors.transparent,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: isDark ? const Color(0x30000000) : const Color(0x0A001540),
                            blurRadius: 10,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: TabBar(
                        controller: _tabController,
                        indicator: BoxDecoration(
                          gradient: LinearGradient(
                            colors: isDark
                                ? [const Color(0xFF1E3A8A), const Color(0xFF2563EB)]
                                : [const Color(0xFF001540), const Color(0xFF0032A0)],
                          ),
                          borderRadius: BorderRadius.circular(12),
                          boxShadow: const [
                            BoxShadow(
                              color: Color(0x35001540),
                              blurRadius: 8,
                              offset: Offset(0, 3),
                            ),
                          ],
                        ),
                        indicatorSize: TabBarIndicatorSize.tab,
                        indicatorPadding: const EdgeInsets.all(4),
                        dividerColor: Colors.transparent,
                        labelColor: Colors.white,
                        unselectedLabelColor: isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2),
                        labelStyle: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w800,
                          fontSize: 12.5,
                        ),
                        unselectedLabelStyle: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w500,
                          fontSize: 12.5,
                        ),
                        tabs: _tabs.map((t) => Tab(text: t)).toList(),
                      ),
                    ),
                  ),

                  // ── SEARCH BAR ─────────────────────────────────────────────
                  Padding(
                    padding: const EdgeInsets.fromLTRB(16, 4, 16, 10),
                    child: Container(
                      height: 44,
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0xFF131D31) : Colors.white,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(
                          color: isDark ? const Color(0xFF1E2B45) : const Color(0x18002366),
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: isDark ? const Color(0x30000000) : const Color(0x08001540),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: TextField(
                        controller: _searchController,
                        onChanged: (val) {
                          setState(() => _searchQuery = val.trim());
                        },
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontSize: 13.5,
                          color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF080F1E),
                        ),
                        decoration: InputDecoration(
                          hintText: 'Search by title or tracking ID...',
                          hintStyle: TextStyle(
                            fontFamily: 'DM Sans',
                            fontSize: 13,
                            color: isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2),
                          ),
                          prefixIcon: Icon(
                            Icons.search_rounded,
                            size: 18,
                            color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF002366),
                          ),
                          suffixIcon: _searchQuery.isNotEmpty
                              ? IconButton(
                                  icon: const Icon(Icons.clear_rounded, size: 16),
                                  color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF6B7280),
                                  onPressed: () {
                                    _searchController.clear();
                                    setState(() => _searchQuery = '');
                                  },
                                )
                              : null,
                          border: InputBorder.none,
                          contentPadding: const EdgeInsets.symmetric(vertical: 12),
                        ),
                      ),
                    ),
                  ),

                  // ── LIST ──────────────────────────────────────────────────
                  Expanded(
                    child: TabBarView(
                      controller: _tabController,
                      children: _tabs.map((tab) {
                        final items = tab == 'All'
                            ? _requests
                            : _requests.where((e) {
                                if (tab == 'Pending') {
                                  return e.status == 'Pending' ||
                                      e.status == 'Pending Review' ||
                                      e.status == 'Under Review';
                                }
                                return e.status == tab;
                              }).toList();
                        final q = _searchQuery.toLowerCase();
                        final filteredItems = q.isEmpty
                            ? items
                            : items.where((e) {
                                return e.title.toLowerCase().contains(q) ||
                                    e.number.toLowerCase().contains(q) ||
                                    e.priority.toLowerCase().contains(q);
                              }).toList();

                        if (_isLoading && _requests.isEmpty) {
                          return ListView(
                            padding: const EdgeInsets.symmetric(vertical: 8),
                            children: const [
                              RequestCardSkeleton(),
                              RequestCardSkeleton(),
                              RequestCardSkeleton(),
                              RequestCardSkeleton(),
                            ],
                          );
                        }
                        if (_error != null) {
                          return Center(
                            child: Padding(
                              padding: const EdgeInsets.all(28),
                              child: Column(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(
                                    Icons.error_outline_rounded,
                                    size: 40,
                                    color: Color(0xFFFF3B30),
                                  ),
                                  const SizedBox(height: 12),
                                  Text(
                                    _error!,
                                    textAlign: TextAlign.center,
                                    style: TextStyle(
                                      fontFamily: 'DM Sans',
                                      color: isDark ? const Color(0xFFE2E8F0) : const Color(0xFF3D4A63),
                                    ),
                                  ),
                                  const SizedBox(height: 16),
                                  GestureDetector(
                                    onTap: _loadRequests,
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 24,
                                        vertical: 12,
                                      ),
                                      decoration: BoxDecoration(
                                        gradient: const LinearGradient(
                                          colors: [
                                            Color(0xFF001540),
                                            Color(0xFF0032A0),
                                          ],
                                        ),
                                        borderRadius: BorderRadius.circular(14),
                                      ),
                                      child: const Text(
                                        'Retry',
                                        style: TextStyle(
                                          fontFamily: 'DM Sans',
                                          fontWeight: FontWeight.w800,
                                          fontSize: 13,
                                          color: Colors.white,
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          );
                        }
                        if (filteredItems.isEmpty) {
                          final isSearching = _searchQuery.isNotEmpty;
                          return Center(
                            child: Padding(
                              padding: const EdgeInsets.only(bottom: 80),
                              child: Column(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Container(
                                    width: 72,
                                    height: 72,
                                    decoration: BoxDecoration(
                                      color: isDark
                                          ? const Color(0xFF1E2B45)
                                          : const Color(0xFF9AA3B2).withOpacity(0.08),
                                      borderRadius: BorderRadius.circular(24),
                                    ),
                                    child: Icon(
                                      isSearching ? Icons.search_off_rounded : Icons.inbox_rounded,
                                      size: 36,
                                      color: isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2),
                                    ),
                                  ),
                                  const SizedBox(height: 14),
                                  Text(
                                    isSearching ? 'No matching requests' : 'No requests yet',
                                    style: TextStyle(
                                      fontFamily: 'DM Sans',
                                      fontWeight: FontWeight.w700,
                                      fontSize: 15,
                                      color: isDark ? const Color(0xFFE2E8F0) : const Color(0xFF3D4A63),
                                    ),
                                  ),
                                  if (isSearching) ...[
                                    const SizedBox(height: 6),
                                    Text(
                                      'Check your spelling or clear the filter',
                                      style: TextStyle(
                                        fontFamily: 'DM Sans',
                                        fontSize: 12.5,
                                        color: isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2),
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          );
                        }
                        return ListView.separated(
                          padding: const EdgeInsets.fromLTRB(16, 4, 16, 110),
                          itemCount: filteredItems.length,
                          separatorBuilder: (_, __) =>
                              const SizedBox(height: 10),
                          itemBuilder: (ctx, i) => _StaggerItem(
                            controller: _staggerController,
                            index: i,
                            total: filteredItems.length,
                            child: _RequestCard(
                              req: filteredItems[i],
                              onTap: () => _openRequestDetails(ctx, filteredItems[i]),
                            ),
                          ),
                        );
                      }).toList(),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ── Widgets ───────────────────────────────────────────────────────────────────
class _MiniStat extends StatelessWidget {
  final String label, value;
  final Color color;
  const _MiniStat({
    required this.label,
    required this.value,
    required this.color,
  });
  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
    decoration: BoxDecoration(
      color: Colors.white.withOpacity(0.1),
      border: Border.all(color: Colors.white.withOpacity(0.13)),
      borderRadius: BorderRadius.circular(12),
    ),
    child: Column(
      children: [
        Text(
          value,
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontWeight: FontWeight.w900,
            fontSize: 18,
            color: color,
            letterSpacing: -0.5,
          ),
        ),
        Text(
          label.toUpperCase(),
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontSize: 9,
            fontWeight: FontWeight.w700,
            color: Colors.white.withOpacity(0.5),
            letterSpacing: 0.5,
          ),
        ),
      ],
    ),
  );
}

class _RequestCard extends StatelessWidget {
  final _RequestPreview req;
  final VoidCallback onTap;
  const _RequestCard({required this.req, required this.onTap});

  Color get _statusColor {
    switch (req.status) {
      case 'Approved':
        return const Color(0xFF05C46B);
      case 'Posted':
        return const Color(0xFF8B5CF6);
      case 'Rejected':
        return const Color(0xFFFF3B30);
      case 'Pending Review':
      case 'Under Review':
      case 'Pending':
      default:
        return const Color(0xFFF59E0B);
    }
  }

  @override
  Widget build(BuildContext context) => Hero(
    tag: 'request-${req.id}',
    flightShuttleBuilder: (_, __, ___, ____, _____) =>
        Material(color: Colors.transparent, child: _body(context)),
    child: Material(
      color: Colors.transparent,
      child: InkWell(
        borderRadius: BorderRadius.circular(20),
        onTap: onTap,
        child: _body(context),
      ),
    ),
  );

  Widget _body(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Container(
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF131D31) : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: isDark ? const Color(0xFF1E2B45) : const Color(0x0E000000)),
        boxShadow: [
          BoxShadow(
            color: isDark ? const Color(0x30000000) : const Color(0x07001540),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Container(
            width: 4,
            height: 64,
            decoration: BoxDecoration(
              color: _statusColor,
              borderRadius: const BorderRadius.only(
                topRight: Radius.circular(4),
                bottomRight: Radius.circular(4),
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
                          color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFE9EDF6),
                          borderRadius: BorderRadius.circular(7),
                        ),
                        child: Text(
                          req.number,
                          style: TextStyle(
                            fontFamily: 'DM Sans',
                            fontWeight: FontWeight.w700,
                            fontSize: 10,
                            color: isDark ? const Color(0xFF93C5FD) : const Color(0xFF3D4A63),
                            letterSpacing: 0.3,
                          ),
                        ),
                      ),
                      const Spacer(),
                      _Chip(status: req.status),
                    ],
                  ),
                  const SizedBox(height: 9),
                  Text(
                    req.title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      fontWeight: FontWeight.w800,
                      fontSize: 15,
                      color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF080F1E),
                      letterSpacing: -0.2,
                      height: 1.3,
                    ),
                  ),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      const Icon(
                        Icons.access_time_rounded,
                        size: 13,
                        color: Color(0xFF9AA3B2),
                      ),
                      const SizedBox(width: 4),
                      Expanded(
                        child: Text(
                          req.submittedAt,
                          style: const TextStyle(
                            fontFamily: 'DM Sans',
                            fontSize: 11.5,
                            color: Color(0xFF9AA3B2),
                          ),
                        ),
                      ),
                      Container(
                        width: 30,
                        height: 30,
                        decoration: BoxDecoration(
                          color: isDark ? const Color(0xFF1E2B45) : const Color(0xFF001540).withOpacity(0.06),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Icon(
                          Icons.arrow_forward_ios_rounded,
                          size: 12,
                          color: isDark ? const Color(0xFF93C5FD) : const Color(0xFF002366),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _Chip extends StatelessWidget {
  final String status;
  const _Chip({required this.status});
  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    Color bg, fg;
    switch (status) {
      case 'Approved':
        bg = isDark ? const Color(0xFF065F46).withOpacity(0.3) : const Color(0xFFD1FAE5);
        fg = isDark ? const Color(0xFF6EE7B7) : const Color(0xFF065F46);
        break;
      case 'Posted':
        bg = isDark ? const Color(0xFF5B21B6).withOpacity(0.3) : const Color(0xFFEDE9FE);
        fg = isDark ? const Color(0xFFC4B5FD) : const Color(0xFF5B21B6);
        break;
      case 'Rejected':
        bg = isDark ? const Color(0xFF991B1B).withOpacity(0.3) : const Color(0xFFFEE2E2);
        fg = isDark ? const Color(0xFFFCA5A5) : const Color(0xFF991B1B);
        break;
      case 'Pending Review':
      case 'Under Review':
      case 'Pending':
      default:
        bg = isDark ? const Color(0xFF92400E).withOpacity(0.3) : const Color(0xFFFEF3C7);
        fg = isDark ? const Color(0xFFFCD34D) : const Color(0xFF92400E);
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

class _RequestPreview {
  final int id;
  final String number, title, status, submittedAt, priority;
  const _RequestPreview({
    required this.id,
    required this.number,
    required this.title,
    required this.status,
    required this.submittedAt,
    this.priority = '',
  });
}

class _StaggerItem extends StatelessWidget {
  final AnimationController controller;
  final int index, total;
  final Widget child;
  const _StaggerItem({
    required this.controller,
    required this.index,
    required this.total,
    required this.child,
  });
  @override
  Widget build(BuildContext context) {
    final count = total.clamp(1, 20);
    final slot = index.clamp(0, count - 1);
    final start = (slot / (count + 4)).clamp(0.0, 0.85);
    final end = (start + 0.55).clamp(0.0, 1.0);
    final curve = CurvedAnimation(
      parent: controller,
      curve: Interval(start, end, curve: Curves.easeOutCubic),
    );
    return AnimatedBuilder(
      animation: curve,
      builder: (_, inner) => Opacity(
        opacity: curve.value,
        child: Transform.translate(
          offset: Offset(0, (1 - curve.value) * 16),
          child: inner,
        ),
      ),
      child: child,
    );
  }
}
