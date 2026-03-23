import 'package:flutter/material.dart';
import '../app_bottom_nav.dart';
import 'request_tracking_screen.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';

class RequestsScreen extends StatefulWidget {
  const RequestsScreen({super.key});

  @override
  State<RequestsScreen> createState() => _RequestsScreenState();
}

class _RequestsScreenState extends State<RequestsScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final int _currentNavIndex = 1; // Requests is active
  bool _isLoading = false;
  String? _error;

  final List<String> _tabs = ['All', 'Pending', 'Approved', 'Posted'];
  List<_RequestPreview> _requests = const [];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: _tabs.length, vsync: this);
    _tabController.addListener(() {
      setState(() {});
      if (!_tabController.indexIsChanging) {
        _loadRequests();
      }
    });
    _loadRequests();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF9FAFB),
      body: Stack(
        children: [
          Column(
            children: [
              // ── Header ──────────────────────────────────
              _buildHeader(),

              // ── Tab bar ─────────────────────────────────
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
                child: _buildTabBar(),
              ),

              const SizedBox(height: 16),

              // ── Tab content ──────────────────────────────
              Expanded(
                child: TabBarView(
                  controller: _tabController,
                  children: _tabs.map((tab) => _buildRequestList(tab)).toList(),
                ),
              ),

              // Space for nav bar
              const SizedBox(height: 90),
            ],
          ),

          // ── Bottom Nav ───────────────────────────────────
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: AppBottomNav(currentIndex: _currentNavIndex),
          ),
        ],
      ),
    );
  }

  // ── Header ────────────────────────────────────────────
  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      padding: EdgeInsets.only(
        left: 20,
        right: 20,
        top: MediaQuery.of(context).padding.top + 18,
        bottom: 18,
      ),
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(bottom: BorderSide(color: Color(0xFFE5E7EB), width: 1)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: const [
          Text(
            'My Requests',
            style: TextStyle(
              fontFamily: 'Inter',
              fontWeight: FontWeight.w700,
              fontSize: 24,
              color: Color(0xFF003366),
            ),
          ),
          SizedBox(height: 4),
          Text(
            'Track your submission status',
            style: TextStyle(
              fontFamily: 'Inter',
              fontWeight: FontWeight.w400,
              fontSize: 13.5,
              color: Color(0xFF6B7280),
            ),
          ),
        ],
      ),
    );
  }

  // ── Tab bar ───────────────────────────────────────────
  Widget _buildTabBar() {
    return Container(
      height: 36,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
      ),
      child: TabBar(
        controller: _tabController,
        indicator: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          boxShadow: const [
            BoxShadow(
              color: Color(0x1A000000),
              blurRadius: 4,
              offset: Offset(0, 2),
            ),
          ],
        ),
        indicatorSize: TabBarIndicatorSize.tab,
        dividerColor: Colors.transparent,
        labelColor: const Color(0xFF0A0A0A),
        unselectedLabelColor: const Color(0xFF0A0A0A),
        labelStyle: const TextStyle(
          fontFamily: 'Inter',
          fontWeight: FontWeight.w500,
          fontSize: 13.5,
        ),
        unselectedLabelStyle: const TextStyle(
          fontFamily: 'Inter',
          fontWeight: FontWeight.w500,
          fontSize: 13.5,
        ),
        tabs: _tabs.map((t) => Tab(text: t)).toList(),
        padding: const EdgeInsets.symmetric(vertical: 3.5, horizontal: 3),
      ),
    );
  }

  // ── Empty state ───────────────────────────────────────
  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.only(bottom: 80),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: const [
            Icon(Icons.inbox_outlined, size: 48, color: Color(0xFF99A1AF)),
            SizedBox(height: 12),
            Text(
              'No requests yet',
              style: TextStyle(
                fontFamily: 'Inter',
                fontWeight: FontWeight.w400,
                fontSize: 14,
                color: Color(0xFF4A5565),
              ),
            ),
            SizedBox(height: 6),
            Text(
              'Tap + Create to submit a new request',
              style: TextStyle(
                fontFamily: 'Inter',
                fontWeight: FontWeight.w400,
                fontSize: 12,
                color: Color(0xFF99A1AF),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _loadRequests() async {
    final userId = SessionStore.userId;
    if (userId == null) {
      setState(() {
        _error = 'Login first to load requests.';
        _requests = const [];
      });
      return;
    }

    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final status = _statusForTab(_tabs[_tabController.index]);
      final rows = await ApiService.fetchRequests(
        userId: userId,
        status: status,
      );
      final mapped = rows.map((row) {
        final id = (row['id'] as num?)?.toInt() ?? 0;
        final reqNo = (row['request_id'] ?? '').toString();
        final createdAt = (row['created_at'] ?? '').toString();
        return _RequestPreview(
          id: id,
          number: reqNo.isEmpty ? 'REQ-$id' : reqNo,
          title: (row['title'] ?? '').toString(),
          status: (row['status'] ?? 'Pending').toString(),
          submittedAt: createdAt.isEmpty
              ? 'Submitted -'
              : 'Submitted $createdAt',
        );
      }).toList();

      setState(() {
        _requests = mapped;
      });
    } catch (e) {
      setState(() {
        _error = e.toString().replaceFirst('Exception: ', '');
        _requests = const [];
      });
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
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

  Widget _buildRequestList(String activeTab) {
    final items = activeTab == 'All'
        ? _requests
        : _requests.where((e) => e.status == activeTab).toList();

    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_error != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                _error!,
                textAlign: TextAlign.center,
                style: const TextStyle(color: Color(0xFF6B7280)),
              ),
              const SizedBox(height: 12),
              OutlinedButton(
                onPressed: _loadRequests,
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      );
    }

    if (items.isEmpty) {
      return _buildEmptyState();
    }

    return ListView.separated(
      padding: const EdgeInsets.fromLTRB(16, 4, 16, 96),
      itemCount: items.length,
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final req = items[index];
        return InkWell(
          borderRadius: BorderRadius.circular(12),
          onTap: () {
            Navigator.of(context).push(
              MaterialPageRoute(
                builder: (_) => RequestTrackingScreen(
                  requestNumber: req.number,
                  requestTitle: req.title,
                  currentStatus: req.status,
                  currentStatusMessage: req.status == 'Pending'
                      ? 'Your request is currently queued for review.'
                      : 'Your request has been approved by the Marketing Office.',
                  events: [
                    TrackingEvent(
                      icon: Icons.send_outlined,
                      title: 'Request Submitted',
                      subtitle: 'Your request was sent successfully.',
                      timestamp: req.submittedAt,
                    ),
                    const TrackingEvent(
                      icon: Icons.rate_review_outlined,
                      title: 'Under Review',
                      subtitle: 'Marketing team is evaluating your request.',
                      timestamp: 'In progress',
                    ),
                    TrackingEvent(
                      icon: req.status == 'Approved'
                          ? Icons.check_circle_outline
                          : Icons.hourglass_bottom_outlined,
                      title: req.status == 'Approved'
                          ? 'Approved'
                          : 'Awaiting Approval',
                      subtitle: req.status == 'Approved'
                          ? 'Ready for scheduling and posting.'
                          : 'Waiting for final decision.',
                      timestamp: req.status == 'Approved'
                          ? 'Mar 13, 2026 • 2:16 PM'
                          : 'In progress',
                    ),
                  ],
                ),
              ),
            );
          },
          child: Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFE5E7EB)),
            ),
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        req.number,
                        style: const TextStyle(
                          fontFamily: 'Inter',
                          fontWeight: FontWeight.w500,
                          fontSize: 12,
                          color: Color(0xFF6B7280),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        req.title,
                        style: const TextStyle(
                          fontFamily: 'Inter',
                          fontWeight: FontWeight.w600,
                          fontSize: 14,
                          color: Color(0xFF101828),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        req.submittedAt,
                        style: const TextStyle(
                          fontFamily: 'Inter',
                          fontWeight: FontWeight.w400,
                          fontSize: 12,
                          color: Color(0xFF6B7280),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 12),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    _StatusChip(status: req.status),
                    const SizedBox(height: 8),
                    const Icon(
                      Icons.chevron_right,
                      size: 20,
                      color: Color(0xFF99A1AF),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  // ── Bottom Nav ────────────────────────────────────────
}

class _RequestPreview {
  final int id;
  final String number;
  final String title;
  final String status;
  final String submittedAt;

  const _RequestPreview({
    required this.id,
    required this.number,
    required this.title,
    required this.status,
    required this.submittedAt,
  });
}

class _StatusChip extends StatelessWidget {
  final String status;
  const _StatusChip({required this.status});

  @override
  Widget build(BuildContext context) {
    final isApproved = status == 'Approved';
    final bg = isApproved ? const Color(0xFFECFDF3) : const Color(0xFFFFF7ED);
    final fg = isApproved ? const Color(0xFF027A48) : const Color(0xFFB54708);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        status,
        style: TextStyle(
          fontFamily: 'Inter',
          fontWeight: FontWeight.w600,
          fontSize: 11,
          color: fg,
        ),
      ),
    );
  }
}
