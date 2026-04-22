import 'package:flutter/material.dart';
import '../app_bottom_nav.dart';
import '../theme/app_theme.dart';
import 'request_tracking_screen.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';
import '../widgets/floating_message_button.dart';

class RequestsScreen extends StatefulWidget {
  const RequestsScreen({super.key});

  @override
  State<RequestsScreen> createState() => _RequestsScreenState();
}

class _RequestsScreenState extends State<RequestsScreen>
    with TickerProviderStateMixin {
  late TabController _tabController;
  late AnimationController _staggerController;
  final int _currentNavIndex = 1;
  bool _isLoading = false;
  String? _error;

  final List<String> _tabs = ['All', 'Pending', 'Approved', 'Posted'];
  List<_RequestPreview> _requests = const [];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: _tabs.length, vsync: this);
    _staggerController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    );
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
    _staggerController.dispose();
    super.dispose();
  }

  void _replayStagger() {
    _staggerController.reset();
    _staggerController.forward();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.pageBg,
      body: Stack(
        children: [
          NestedScrollView(
            headerSliverBuilder: (context, innerBoxIsScrolled) => [
              _buildSliverAppBar(context),
              SliverPersistentHeader(
                pinned: true,
                delegate: _TabBarDelegate(
                  child: Container(
                    color: AppColors.pageBg,
                    padding: const EdgeInsets.fromLTRB(16, 12, 16, 12),
                    child: _buildTabBar(),
                  ),
                ),
              ),
            ],
            body: TabBarView(
              controller: _tabController,
              children: _tabs.map((tab) => _buildRequestList(tab)).toList(),
            ),
          ),
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: AppBottomNav(currentIndex: _currentNavIndex),
          ),
          const FloatingMessageButton(),
        ],
      ),
    );
  }

  Widget _buildSliverAppBar(BuildContext context) {
    return SliverAppBar(
      pinned: true,
      expandedHeight: 180,
      backgroundColor: AppColors.primaryDark,
      foregroundColor: Colors.white,
      elevation: 0,
      automaticallyImplyLeading: false,
      flexibleSpace: FlexibleSpaceBar(
        titlePadding: const EdgeInsets.fromLTRB(20, 0, 20, 16),
        title: const Text(
          'My Requests',
          style: TextStyle(
            fontWeight: FontWeight.w700,
            fontSize: 20,
            color: Colors.white,
          ),
        ),
        background: Container(
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
          ),
          child: Stack(
            children: [
              Positioned(
                right: -30,
                top: -30,
                child: Container(
                  width: 180,
                  height: 180,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.white.withValues(alpha: 0.06),
                  ),
                ),
              ),
              Positioned(
                left: 20,
                bottom: 54,
                right: 20,
                child: Text(
                  'Track your submission status',
                  style: TextStyle(
                    fontWeight: FontWeight.w400,
                    fontSize: 13.5,
                    color: Colors.white.withValues(alpha: 0.85),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTabBar() {
    return Container(
      height: 40,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(AppRadius.md),
        boxShadow: const [
          BoxShadow(
            color: Color(0x0F000000),
            blurRadius: 8,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: TabBar(
        controller: _tabController,
        indicator: BoxDecoration(
          color: AppColors.primary,
          borderRadius: BorderRadius.circular(AppRadius.md - 2),
        ),
        indicatorSize: TabBarIndicatorSize.tab,
        indicatorPadding: const EdgeInsets.all(3),
        dividerColor: Colors.transparent,
        labelColor: Colors.white,
        unselectedLabelColor: AppColors.inkMid,
        labelStyle: const TextStyle(
          fontWeight: FontWeight.w600,
          fontSize: 13,
        ),
        unselectedLabelStyle: const TextStyle(
          fontWeight: FontWeight.w500,
          fontSize: 13,
        ),
        tabs: _tabs.map((t) => Tab(text: t)).toList(),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.only(bottom: 80),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: const [
            Icon(Icons.inbox_outlined, size: 48, color: AppColors.inkMute),
            SizedBox(height: 12),
            Text(
              'No requests yet',
              style: TextStyle(
                fontWeight: FontWeight.w500,
                fontSize: 14,
                color: AppColors.inkMid,
              ),
            ),
            SizedBox(height: 6),
            Text(
              'Tap + Create to submit a new request',
              style: TextStyle(
                fontWeight: FontWeight.w400,
                fontSize: 12,
                color: AppColors.inkMute,
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
      _replayStagger();
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

  Future<void> _openRequestDetails(
    BuildContext context,
    _RequestPreview req,
  ) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(
        child: CircularProgressIndicator(color: AppColors.primary),
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

        final createdAt = request['created_at'] ?? '';
        events.add(
          TrackingEvent(
            icon: Icons.send_outlined,
            title: 'Request Submitted',
            subtitle: 'Your request was sent successfully.',
            timestamp: _formatTimestamp(createdAt),
          ),
        );

        for (final activity in activities) {
          final action = (activity['action'] ?? '').toString();
          final activityTime = activity['created_at'] ?? '';

          IconData icon = Icons.info_outline;
          String title = 'Update';
          String subtitle = action;

          if (action.contains('Under Review')) {
            icon = Icons.rate_review_outlined;
            title = 'Under Review';
            subtitle = 'Marketing team is evaluating your request.';
          } else if (action.contains('Approved')) {
            icon = Icons.check_circle_outline;
            title = 'Approved';
            subtitle = 'Your request has been approved.';
          } else if (action.contains('Rejected')) {
            icon = Icons.cancel_outlined;
            title = 'Rejected';
            subtitle = 'Your request was not approved.';
          } else if (action.contains('Posted')) {
            icon = Icons.publish;
            title = 'Posted';
            subtitle = 'Your content has been published.';
          } else if (action.contains('Pending')) {
            icon = Icons.hourglass_empty;
            title = 'Pending Review';
            subtitle = 'Your request is waiting for review.';
          } else if (action.contains('Internal note')) {
            icon = Icons.comment_outlined;
            title = 'Note Added';
            subtitle = action.replaceFirst('Internal note: ', '');
          }

          events.add(
            TrackingEvent(
              icon: icon,
              title: title,
              subtitle: subtitle,
              timestamp: _formatTimestamp(activityTime),
            ),
          );
        }

        final currentStatus = request['status'] ?? 'Pending';
        String statusMessage = 'Your request is being processed.';
        if (currentStatus == 'Pending') {
          statusMessage = 'Your request is currently queued for review.';
        } else if (currentStatus == 'Approved') {
          statusMessage =
              'Your request has been approved by the Marketing Office.';
        } else if (currentStatus == 'Posted') {
          statusMessage = 'Your content has been successfully published.';
        } else if (currentStatus == 'Rejected') {
          statusMessage =
              'Your request was not approved. Please check feedback.';
        }

        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => RequestTrackingScreen(
              heroTag: 'request-${req.id}',
              requestNumber: req.number,
              requestTitle: req.title,
              currentStatus: currentStatus,
              currentStatusMessage: statusMessage,
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
        ).showSnackBar(SnackBar(content: Text('Error: ${e.toString()}')));
      }
    }
  }

  String _formatTimestamp(String datetime) {
    if (datetime.isEmpty) return '';
    try {
      final dt = DateTime.parse(datetime);
      final months = [
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
      final hour = dt.hour > 12 ? dt.hour - 12 : (dt.hour == 0 ? 12 : dt.hour);
      final ampm = dt.hour >= 12 ? 'PM' : 'AM';
      final min = dt.minute.toString().padLeft(2, '0');
      return '${months[dt.month - 1]} ${dt.day}, ${dt.year} • $hour:$min $ampm';
    } catch (_) {
      return datetime;
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
                style: const TextStyle(color: AppColors.inkMid),
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
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 110),
      itemCount: items.length,
      separatorBuilder: (_, __) => const SizedBox(height: 12),
      itemBuilder: (context, index) {
        final req = items[index];
        return _StaggeredItem(
          controller: _staggerController,
          index: index,
          total: items.length,
          child: _RequestCard(
            req: req,
            onTap: () => _openRequestDetails(context, req),
          ),
        );
      },
    );
  }
}

class _TabBarDelegate extends SliverPersistentHeaderDelegate {
  final Widget child;
  _TabBarDelegate({required this.child});

  @override
  double get minExtent => 64;
  @override
  double get maxExtent => 64;

  @override
  Widget build(
    BuildContext context,
    double shrinkOffset,
    bool overlapsContent,
  ) => child;

  @override
  bool shouldRebuild(covariant _TabBarDelegate oldDelegate) => false;
}

class _StaggeredItem extends StatelessWidget {
  final AnimationController controller;
  final int index;
  final int total;
  final Widget child;

  const _StaggeredItem({
    required this.controller,
    required this.index,
    required this.total,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    final slotCount = total.clamp(1, 20);
    final slot = index.clamp(0, slotCount - 1);
    final start = (slot / (slotCount + 4)).clamp(0.0, 0.85);
    final end = (start + 0.55).clamp(0.0, 1.0);
    final curve = CurvedAnimation(
      parent: controller,
      curve: Interval(start, end, curve: Curves.easeOutCubic),
    );
    return AnimatedBuilder(
      animation: curve,
      builder: (context, inner) {
        return Opacity(
          opacity: curve.value,
          child: Transform.translate(
            offset: Offset(0, (1 - curve.value) * 18),
            child: inner,
          ),
        );
      },
      child: child,
    );
  }
}

class _RequestCard extends StatelessWidget {
  final _RequestPreview req;
  final VoidCallback onTap;

  const _RequestCard({required this.req, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Hero(
      tag: 'request-${req.id}',
      flightShuttleBuilder: (_, __, ___, ____, _____) => Material(
        color: Colors.transparent,
        child: _RequestCardBody(req: req),
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(AppRadius.lg),
          onTap: onTap,
          child: _RequestCardBody(req: req),
        ),
      ),
    );
  }
}

class _RequestCardBody extends StatelessWidget {
  final _RequestPreview req;
  const _RequestCardBody({required this.req});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(AppRadius.lg),
        border: Border.all(color: const Color(0x0F000000)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x0A000000),
            blurRadius: 10,
            offset: Offset(0, 4),
          ),
        ],
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
                    fontWeight: FontWeight.w500,
                    fontSize: 12,
                    color: AppColors.inkMute,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  req.title,
                  style: const TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 14.5,
                    color: AppColors.ink,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 6),
                Text(
                  req.submittedAt,
                  style: const TextStyle(
                    fontWeight: FontWeight.w400,
                    fontSize: 12,
                    color: AppColors.inkMute,
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
                color: AppColors.inkMute,
              ),
            ],
          ),
        ],
      ),
    );
  }
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
    Color bg;
    Color fg;
    switch (status) {
      case 'Approved':
        bg = const Color(0xFFD1FAE5);
        fg = const Color(0xFF047857);
        break;
      case 'Posted':
        bg = const Color(0xFFEDE9FE);
        fg = const Color(0xFF6D28D9);
        break;
      case 'Rejected':
        bg = const Color(0xFFFEE2E2);
        fg = const Color(0xFFB91C1C);
        break;
      case 'Pending':
      default:
        bg = AppColors.goldBg;
        fg = AppColors.goldDark;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        status,
        style: TextStyle(
          fontWeight: FontWeight.w600,
          fontSize: 11,
          color: fg,
        ),
      ),
    );
  }
}
