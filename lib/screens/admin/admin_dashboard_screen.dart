import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../services/app_memory_cache.dart';
import '../../services/session_store.dart';
import '../../theme/app_theme.dart';
import '../../widgets/skeleton_loader.dart';
import '../request_tracking_screen.dart';

class AdminDashboardScreen extends StatefulWidget {
  const AdminDashboardScreen({super.key});

  @override
  State<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends State<AdminDashboardScreen>
    with TickerProviderStateMixin {
  late final AnimationController _entryCtrl;
  late final Animation<double> _entryFade;
  late final Animation<Offset> _entrySlide;

  bool _isLoading = true;
  String? _errorMessage;

  Map<String, dynamic> _stats = {
    'total': 0,
    'pending': 0,
    'review': 0,
    'approved': 0,
    'posted': 0,
    'rejected': 0,
    'users': 0,
    'monthly': <Map<String, dynamic>>[],
  };

  List<Map<String, dynamic>> _requests = [];
  String _selectedStatusFilter = 'all';
  String _searchQuery = '';

  final TextEditingController _searchCtrl = TextEditingController();

  AnimationController? _staggerCtrl;

  @override
  void initState() {
    super.initState();
    _entryCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 700),
    );
    _entryFade = CurvedAnimation(
      parent: _entryCtrl,
      curve: const Interval(0.0, 0.7, curve: Curves.easeOut),
    );
    _entrySlide = Tween<Offset>(
      begin: const Offset(0, 0.04),
      end: Offset.zero,
    ).animate(CurvedAnimation(
      parent: _entryCtrl,
      curve: const Interval(0.0, 0.8, curve: Curves.easeOutCubic),
    ));

    _staggerCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 700),
    );

    // Warm-start from session cache: 0ms instant display, zero skeleton on revisit
    if (AppMemoryCache.hasAdminData) {
      if (AppMemoryCache.adminStats != null) {
        _stats = AppMemoryCache.adminStats!;
      }
      if (AppMemoryCache.adminRequests != null) {
        _requests = AppMemoryCache.adminRequests!;
      }
      _isLoading = false;
    }

    _loadData(showLoading: !AppMemoryCache.hasAdminData);
    _entryCtrl.forward();
    AppMemoryCache.requestsRevision.addListener(_onRequestsChanged);
  }

  @override
  void dispose() {
    AppMemoryCache.requestsRevision.removeListener(_onRequestsChanged);
    _entryCtrl.dispose();
    _staggerCtrl?.dispose();
    _searchCtrl.dispose();
    super.dispose();
  }

  void _onRequestsChanged() {
    if (!mounted) return;
    _loadData(showLoading: false);
  }

  Future<void> _loadData({bool showLoading = false}) async {
    if (showLoading && mounted) {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
      });
    }

    try {
      final statsRes = await ApiService.fetchAdminStats();
      final reqsRes = await ApiService.fetchAdminRequests(
        status: _selectedStatusFilter,
        search: _searchQuery,
      );

      if (mounted) {
        final newStats = (statsRes['data'] as Map<String, dynamic>?) ?? _stats;
        if (_selectedStatusFilter == 'all' && _searchQuery.isEmpty) {
          AppMemoryCache.adminStats = newStats;
          AppMemoryCache.adminRequests = reqsRes;
        }
        setState(() {
          _stats = newStats;
          _requests = reqsRes;
          _isLoading = false;
        });
        _staggerCtrl?.reset();
        _staggerCtrl?.forward();
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

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      resizeToAvoidBottomInset: false,
      backgroundColor: isDark ? const Color(0xFF090D16) : AppColors.pageBg,
      body: ClipRect(
        child: Stack(
        children: [
          FadeTransition(
            opacity: _entryFade,
            child: SlideTransition(
              position: _entrySlide,
              child: RefreshIndicator(
                onRefresh: _loadData,
                child: SingleChildScrollView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildHeroHeader(),
                      const SizedBox(height: 18),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16.0),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _buildMonthlyGraphCard(isDark),
                            const SizedBox(height: 20),
                            _buildQueueSectionHeader(isDark),
                            const SizedBox(height: 12),
                            _buildFilterChips(isDark),
                            const SizedBox(height: 12),
                            _buildSearchBar(isDark),
                            const SizedBox(height: 16),
                            _buildRequestList(isDark),
                            const SizedBox(height: 100),
                          ],
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

  // ── Hero Header ───────────────────────────────────────────────────────────
  Widget _buildHeroHeader() {
    final topPad = MediaQuery.of(context).padding.top;
    final adminName = (SessionStore.name ?? 'Admin').split(' ').first;

    final total = _stats['total'] ?? 0;
    final pending = (_stats['pending'] ?? 0) + (_stats['review'] ?? 0);
    final approved = _stats['approved'] ?? 0;
    final posted = _stats['posted'] ?? 0;
    final rejected = _stats['rejected'] ?? 0;

    return Container(
      width: double.infinity,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF001540), Color(0xFF002878), Color(0xFF1243B0)],
          stops: [0.0, 0.5, 1.0],
        ),
        borderRadius: BorderRadius.only(
          bottomLeft: Radius.circular(36),
          bottomRight: Radius.circular(36),
        ),
      ),
      child: Stack(
        children: [
          // Background decorative circles
          Positioned(
            top: -50,
            right: -50,
            child: Container(
              width: 220,
              height: 220,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withOpacity(0.05),
              ),
            ),
          ),
          Positioned(
            bottom: -30,
            left: -20,
            child: Container(
              width: 140,
              height: 140,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: const Color(0xFFF59E0B).withOpacity(0.07),
              ),
            ),
          ),
          Padding(
            padding: EdgeInsets.fromLTRB(22, topPad + 18, 22, 26),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Top Title & Action Buttons Row
                Row(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 3,
                                ),
                                decoration: BoxDecoration(
                                  color: AppColors.gold.withOpacity(0.2),
                                  borderRadius: BorderRadius.circular(6),
                                  border: Border.all(
                                    color: AppColors.gold.withOpacity(0.4),
                                    width: 1,
                                  ),
                                ),
                                child: const Text(
                                  'ADMIN PORTAL',
                                  style: TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontWeight: FontWeight.w800,
                                    fontSize: 10,
                                    color: AppColors.goldLight,
                                    letterSpacing: 0.8,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Hello, $adminName 👋',
                            style: const TextStyle(
                              fontFamily: 'DM Sans',
                              fontWeight: FontWeight.w900,
                              fontSize: 22,
                              color: Colors.white,
                              letterSpacing: -0.5,
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Top Action Icons
                    Row(
                      children: [
                        // Admin Calendar Button
                        GestureDetector(
                          onTap: () => Navigator.pushNamed(context, '/calendar'),
                          child: Container(
                            width: 42,
                            height: 42,
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.12),
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(
                                color: Colors.white.withOpacity(0.2),
                              ),
                            ),
                            child: const Icon(
                              Icons.calendar_month_rounded,
                              color: Colors.white,
                              size: 20,
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        // Refresh Button
                        GestureDetector(
                          onTap: _loadData,
                          child: Container(
                            width: 42,
                            height: 42,
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.12),
                              borderRadius: BorderRadius.circular(14),
                              border: Border.all(
                                color: Colors.white.withOpacity(0.2),
                              ),
                            ),
                            child: const Icon(
                              Icons.refresh_rounded,
                              color: Colors.white,
                              size: 20,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),

                const SizedBox(height: 22),

                // Metrics Grid Cards embedded inside Hero
                if (_isLoading)
                  const SkeletonLoader(height: 110)
                else
                  Column(
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: _buildHeroMetricCard(
                              label: 'Total Requests',
                              value: '$total',
                              icon: Icons.article_outlined,
                              color: const Color(0xFF60A5FA),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: _buildHeroMetricCard(
                              label: 'Action Needed',
                              value: '$pending',
                              icon: Icons.pending_actions_rounded,
                              color: const Color(0xFFFBBF24),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Expanded(
                            child: _buildHeroMetricCard(
                              label: 'Approved',
                              value: '$approved',
                              icon: Icons.check_circle_outline,
                              color: const Color(0xFF34D399),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: _buildHeroMetricCard(
                              label: 'Posted',
                              value: '$posted',
                              icon: Icons.rocket_launch_outlined,
                              color: const Color(0xFFA78BFA),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: _buildHeroMetricCard(
                              label: 'Rejected',
                              value: '$rejected',
                              icon: Icons.cancel_outlined,
                              color: const Color(0xFFF87171),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeroMetricCard({
    required String label,
    required String value,
    required IconData icon,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.12),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: Colors.white.withOpacity(0.15),
          width: 1,
        ),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(5),
            decoration: BoxDecoration(
              color: color.withOpacity(0.2),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color, size: 15),
          ),
          const SizedBox(width: 6),
          Expanded(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                FittedBox(
                  fit: BoxFit.scaleDown,
                  alignment: Alignment.centerLeft,
                  child: Text(
                    value,
                    style: const TextStyle(
                      fontFamily: 'DM Sans',
                      color: Colors.white,
                      fontSize: 15,
                      fontWeight: FontWeight.w900,
                      height: 1.1,
                    ),
                  ),
                ),
                Text(
                  label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    color: Colors.white.withOpacity(0.7),
                    fontSize: 9.5,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ── Monthly Volume Analytics Card ─────────────────────────────────────────
  Widget _buildMonthlyGraphCard(bool isDark) {
    if (_isLoading) {
      return const SkeletonLoader(height: 150);
    }

    final rawMonthly = (_stats['monthly'] as List?) ?? [];
    final monthly = rawMonthly.cast<Map<String, dynamic>>();

    if (monthly.isEmpty) return const SizedBox.shrink();

    int maxCount = 1;
    for (final m in monthly) {
      final c = (m['count'] as num?)?.toInt() ?? 0;
      if (c > maxCount) maxCount = c;
    }

    return Container(
      padding: const EdgeInsets.all(16),
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
          Row(
            children: [
              const Icon(Icons.bar_chart_rounded, color: AppColors.accent, size: 18),
              const SizedBox(width: 8),
              Text(
                'Request Volume Analytics',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  color: isDark ? const Color(0xFFF1F5F9) : AppColors.ink,
                  fontWeight: FontWeight.bold,
                  fontSize: 14,
                ),
              ),
              const Spacer(),
              Text(
                'Last 6 Months',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  color: isDark ? const Color(0xFF94A3B8) : AppColors.inkMute,
                  fontSize: 11,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          SizedBox(
            height: 100,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              crossAxisAlignment: CrossAxisAlignment.end,
              children: monthly.map((m) {
                final label = (m['label'] ?? '').toString();
                final count = (m['count'] as num?)?.toInt() ?? 0;
                final factor = maxCount > 0 ? (count / maxCount) : 0.0;

                return Column(
                  mainAxisAlignment: MainAxisAlignment.end,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      '$count',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                        color: count > 0 ? AppColors.accent : (isDark ? const Color(0xFF64748B) : AppColors.inkMute),
                      ),
                    ),
                    const SizedBox(height: 3),
                    Container(
                      width: 20,
                      height: (factor * 44).clamp(4.0, 44.0),
                      decoration: BoxDecoration(
                        color: count > 0 ? AppColors.accent : (isDark ? const Color(0xFF1E2B45) : Colors.grey.shade300),
                        borderRadius: BorderRadius.circular(6),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      label,
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontSize: 10,
                        color: isDark ? const Color(0xFF94A3B8) : AppColors.inkMute,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildQueueSectionHeader(bool isDark) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          'Requests Queue (${_requests.length})',
          style: TextStyle(
            fontFamily: 'DM Sans',
            color: isDark ? const Color(0xFFF1F5F9) : AppColors.ink,
            fontSize: 16,
            fontWeight: FontWeight.bold,
          ),
        ),
      ],
    );
  }

  Widget _buildFilterChips(bool isDark) {
    final filters = [
      {'key': 'all', 'label': 'All'},
      {'key': 'Pending Review', 'label': 'Pending'},
      {'key': 'Under Review', 'label': 'Review'},
      {'key': 'Approved', 'label': 'Approved'},
      {'key': 'Posted', 'label': 'Posted'},
      {'key': 'Rejected', 'label': 'Rejected'},
    ];

    return Container(
      height: 44,
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF131D31) : Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: isDark ? Border.all(color: const Color(0xFF1E2B45)) : null,
        boxShadow: [
          BoxShadow(
            color: isDark ? const Color(0x30000000) : const Color(0x0A001540),
            blurRadius: 10,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: SingleChildScrollView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.all(4),
        child: Row(
          children: filters.map((f) {
            final isSelected = _selectedStatusFilter == f['key'];
            return GestureDetector(
              onTap: () {
                if (!isSelected) {
                  setState(() {
                    _selectedStatusFilter = f['key']!;
                  });
                  _loadData();
                }
              },
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                decoration: BoxDecoration(
                  gradient: isSelected
                      ? const LinearGradient(
                          colors: [Color(0xFF001540), Color(0xFF0032A0)],
                        )
                      : null,
                  color: isSelected ? null : Colors.transparent,
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: isSelected
                      ? const [
                          BoxShadow(
                            color: Color(0x35001540),
                            blurRadius: 8,
                            offset: Offset(0, 3),
                          ),
                        ]
                      : null,
                ),
                child: Text(
                  f['label']!,
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                    fontSize: 12.5,
                    color: isSelected ? Colors.white : const Color(0xFF9AA3B2),
                  ),
                ),
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  Widget _buildSearchBar(bool isDark) {
    return Container(
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF131D31) : Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: isDark ? const Color(0xFF1E2B45) : AppColors.border),
      ),
      child: TextField(
        controller: _searchCtrl,
        style: TextStyle(
          fontFamily: 'DM Sans',
          color: isDark ? const Color(0xFFF1F5F9) : const Color(0xFF080F1E),
          fontSize: 13,
        ),
        decoration: InputDecoration(
          hintText: 'Search title, requester, or category...',
          hintStyle: TextStyle(
            fontFamily: 'DM Sans',
            color: isDark ? const Color(0xFF64748B) : AppColors.inkMute,
            fontSize: 13,
          ),
          prefixIcon: Icon(Icons.search, color: isDark ? const Color(0xFF94A3B8) : AppColors.inkMute, size: 20),
          suffixIcon: _searchCtrl.text.isNotEmpty
              ? IconButton(
                  icon: Icon(Icons.clear, size: 18, color: isDark ? const Color(0xFF94A3B8) : null),
                  onPressed: () {
                    _searchCtrl.clear();
                    setState(() {
                      _searchQuery = '';
                    });
                    _loadData();
                  },
                )
              : null,
          border: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(vertical: 12),
        ),
        onSubmitted: (val) {
          setState(() {
            _searchQuery = val.trim();
          });
          _loadData();
        },
      ),
    );
  }

  Widget _buildRequestList(bool isDark) {
    if (_isLoading) {
      return Column(
        children: List.generate(4, (_) => const Padding(
          padding: EdgeInsets.only(bottom: 10),
          child: SkeletonLoader(height: 80),
        )),
      );
    }

    if (_errorMessage != null) {
      return Container(
        padding: const EdgeInsets.all(20),
        alignment: Alignment.center,
        child: Column(
          children: [
            const Icon(Icons.error_outline, color: Colors.redAccent, size: 40),
            const SizedBox(height: 8),
            Text(
              _errorMessage!,
              textAlign: TextAlign.center,
              style: const TextStyle(fontFamily: 'DM Sans', color: AppColors.inkMute),
            ),
            const SizedBox(height: 12),
            ElevatedButton(
              onPressed: _loadData,
              child: const Text('Try Again'),
            ),
          ],
        ),
      );
    }

    if (_requests.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(32),
        alignment: Alignment.center,
        child: const Column(
          children: [
            Icon(Icons.inbox_outlined, color: AppColors.inkMute, size: 48),
            SizedBox(height: 12),
            Text(
              'No requests found matching criteria.',
              style: TextStyle(fontFamily: 'DM Sans', color: AppColors.inkMute, fontSize: 14),
            ),
          ],
        ),
      );
    }

    return ListView.separated(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: _requests.length,
      separatorBuilder: (_, __) => const SizedBox(height: 10),
      itemBuilder: (context, index) {
        final req = _requests[index];
        final reqId = req['id'] as int;
        final rawCode = (req['request_id'] ?? '').toString().trim();
        final reqCode =
            rawCode.isEmpty ? 'REQ-${reqId.toString().padLeft(5, '0')}' : rawCode;
        final title = (req['title'] ?? 'Untitled Request').toString();
        final requester = (req['requester'] ?? 'Unknown User').toString();
        final status = (req['status'] ?? 'Pending Review').toString();
        final createdAt = (req['created_at'] ?? '').toString();

        Color statusColor;
        switch (status) {
          case 'Approved':
            statusColor = const Color(0xFF05C46B);
            break;
          case 'Posted':
            statusColor = const Color(0xFF8B5CF6);
            break;
          case 'Rejected':
            statusColor = const Color(0xFFFF3B30);
            break;
          case 'Under Review':
          case 'Pending Review':
          case 'Pending':
          default:
            statusColor = const Color(0xFFF59E0B);
            break;
        }

        return _StaggerItem(
          controller: _staggerCtrl,
          index: index,
          total: _requests.length,
          child: GestureDetector(
            onTap: () async {
              await Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (_) => RequestTrackingScreen(requestId: reqId),
                ),
              );
              _loadData();
            },
            child: Container(
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
                      color: statusColor,
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
                                  reqCode,
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
                              _buildStatusBadge(status),
                            ],
                          ),
                          const SizedBox(height: 9),
                          Text(
                            title,
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
                              Icon(
                                Icons.access_time_rounded,
                                size: 13,
                                color: isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2),
                              ),
                              const SizedBox(width: 4),
                              Expanded(
                                child: Text(
                                  createdAt.isNotEmpty
                                      ? 'Submitted $createdAt'
                                      : 'By $requester',
                                  style: TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontSize: 11.5,
                                    color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF9AA3B2),
                                  ),
                                ),
                              ),
                              Container(
                                width: 30,
                                height: 30,
                                decoration: BoxDecoration(
                                  color: isDark ? const Color(0xFF1E2B45) : const Color(0xFF002366).withOpacity(0.06),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Icon(
                                  Icons.arrow_forward_ios_rounded,
                                  size: 12,
                                  color: isDark ? const Color(0xFF60A5FA) : const Color(0xFF002366),
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
            ),
          ),
        );
      },
    );
  }

  Widget _buildStatusBadge(String status) {
    Color bg;
    Color fg;

    switch (status) {
      case 'Approved':
        bg = const Color(0xFFD1FAE5);
        fg = const Color(0xFF065F46);
        break;
      case 'Posted':
        bg = const Color(0xFFEDE9FE);
        fg = const Color(0xFF5B21B6);
        break;
      case 'Rejected':
        bg = const Color(0xFFFEE2E2);
        fg = const Color(0xFF991B1B);
        break;
      case 'Under Review':
      case 'Pending Review':
      case 'Pending':
      default:
        bg = const Color(0xFFFEF3C7);
        fg = const Color(0xFF92400E);
        break;
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
          color: fg,
          fontSize: 10.5,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }
}

class _StaggerItem extends StatelessWidget {
  final AnimationController? controller;
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
    final ctrl = controller;
    if (ctrl == null) return child;

    final count = total.clamp(1, 20);
    final slot = index.clamp(0, count - 1);
    final start = (slot / (count + 4)).clamp(0.0, 0.85);
    final end = (start + 0.55).clamp(0.0, 1.0);
    final curve = CurvedAnimation(
      parent: ctrl,
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
