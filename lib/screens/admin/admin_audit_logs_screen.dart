import 'dart:async';
import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../services/session_store.dart';

class AdminAuditLogsScreen extends StatefulWidget {
  const AdminAuditLogsScreen({super.key});

  @override
  State<AdminAuditLogsScreen> createState() => _AdminAuditLogsScreenState();
}

class _AdminAuditLogsScreenState extends State<AdminAuditLogsScreen> {
  final TextEditingController _searchCtrl = TextEditingController();
  final ScrollController _scrollCtrl = ScrollController();

  List<Map<String, dynamic>> _logs = [];
  bool _isLoading = true;
  bool _isLoadingMore = false;
  String? _errorMessage;

  int _currentPage = 1;
  int _lastPage = 1;
  String _selectedCategory = 'all';
  String _searchQuery = '';
  Timer? _debounceTimer;

  final List<Map<String, String>> _categories = const [
    {'key': 'all', 'label': 'All Activities'},
    {'key': 'requests', 'label': 'Requests'},
    {'key': 'auth', 'label': 'Sign-ins'},
  ];

  @override
  void initState() {
    super.initState();
    _loadLogs(reset: true);

    _scrollCtrl.addListener(() {
      if (_scrollCtrl.position.pixels >= _scrollCtrl.position.maxScrollExtent - 200 &&
          !_isLoadingMore &&
          _currentPage < _lastPage) {
        _loadMoreLogs();
      }
    });
  }

  @override
  void dispose() {
    _debounceTimer?.cancel();
    _searchCtrl.dispose();
    _scrollCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadLogs({bool reset = false}) async {
    if (reset) {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
        _currentPage = 1;
      });
    }

    try {
      final userId = SessionStore.userId;
      if (userId == null || userId <= 0) {
        setState(() {
          _isLoading = false;
          _errorMessage = 'Admin session not found';
        });
        return;
      }

      final response = await ApiService.fetchAdminAuditLogs(
        userId: userId,
        search: _searchQuery,
        category: _selectedCategory,
        page: _currentPage,
      );

      if (response['success'] == true && mounted) {
        final list = (response['data'] as List?)?.cast<Map<String, dynamic>>() ?? [];
        final meta = response['meta'] as Map<String, dynamic>? ?? {};

        setState(() {
          _logs = reset ? list : [..._logs, ...list];
          _lastPage = (meta['last_page'] as num?)?.toInt() ?? 1;
          _isLoading = false;
        });
      } else if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage = response['message']?.toString() ?? 'Failed to load audit logs';
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoading = false;
          _errorMessage = e.toString().replaceFirst('Exception: ', '');
        });
      }
    }
  }

  Future<void> _loadMoreLogs() async {
    if (_isLoadingMore || _currentPage >= _lastPage) return;

    setState(() => _isLoadingMore = true);
    _currentPage++;

    try {
      final userId = SessionStore.userId;
      if (userId == null) return;

      final response = await ApiService.fetchAdminAuditLogs(
        userId: userId,
        search: _searchQuery,
        category: _selectedCategory,
        page: _currentPage,
      );

      if (response['success'] == true && mounted) {
        final list = (response['data'] as List?)?.cast<Map<String, dynamic>>() ?? [];
        setState(() {
          _logs.addAll(list);
          _isLoadingMore = false;
        });
      } else if (mounted) {
        setState(() => _isLoadingMore = false);
      }
    } catch (_) {
      if (mounted) setState(() => _isLoadingMore = false);
    }
  }

  void _onSearchChanged(String query) {
    _debounceTimer?.cancel();
    _debounceTimer = Timer(const Duration(milliseconds: 350), () {
      setState(() {
        _searchQuery = query.trim();
      });
      _loadLogs(reset: true);
    });
  }

  Color _actionBadgeColor(String action) {
    if (action.contains('approved')) return const Color(0xFF10B981);
    if (action.contains('rejected')) return const Color(0xFFEF4444);
    if (action.contains('login') || action.contains('logout')) return const Color(0xFF3B82F6);
    if (action.contains('created') || action.contains('edited')) return const Color(0xFFF59E0B);
    if (action.contains('api_key') || action.contains('settings')) return const Color(0xFF8B5CF6);
    return const Color(0xFF6B7280);
  }

  IconData _actionIcon(String action) {
    if (action.contains('approved')) return Icons.check_circle_outline_rounded;
    if (action.contains('rejected')) return Icons.cancel_outlined;
    if (action.contains('login') || action.contains('logout')) return Icons.login_rounded;
    if (action.contains('created')) return Icons.post_add_rounded;
    if (action.contains('edited')) return Icons.edit_note_rounded;
    if (action.contains('api_key') || action.contains('settings')) return Icons.key_rounded;
    return Icons.history_rounded;
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF091122) : const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: isDark ? const Color(0xFF0D1526) : Colors.white,
        elevation: 0,
        title: Text(
          'Activity History',
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontWeight: FontWeight.w800,
            fontSize: 18,
            color: isDark ? Colors.white : const Color(0xFF0F172A),
          ),
        ),
        centerTitle: false,
        iconTheme: IconThemeData(
          color: isDark ? Colors.white : const Color(0xFF0F172A),
        ),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(1),
          child: Container(
            color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFE2E8F0),
            height: 1,
          ),
        ),
      ),
      body: Column(
        children: [
          // Search & Filter Header
          Container(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 12),
            color: isDark ? const Color(0xFF0D1526) : Colors.white,
            child: Column(
              children: [
                // Search Input
                Container(
                  height: 44,
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF131D31) : const Color(0xFFF1F5F9),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFE2E8F0),
                    ),
                  ),
                  child: TextField(
                    controller: _searchCtrl,
                    onChanged: _onSearchChanged,
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      fontSize: 13.5,
                      color: isDark ? Colors.white : const Color(0xFF0F172A),
                    ),
                    decoration: InputDecoration(
                      hintText: 'Search user, action, or date...',
                      hintStyle: TextStyle(
                        fontFamily: 'DM Sans',
                        fontSize: 13,
                        color: isDark ? const Color(0xFF64748B) : const Color(0xFF94A3B8),
                      ),
                      prefixIcon: const Icon(Icons.search_rounded, size: 20, color: Color(0xFF94A3B8)),
                      suffixIcon: _searchCtrl.text.isNotEmpty
                          ? IconButton(
                              icon: const Icon(Icons.clear_rounded, size: 18),
                              onPressed: () {
                                _searchCtrl.clear();
                                _onSearchChanged('');
                              },
                            )
                          : null,
                      border: InputBorder.none,
                      contentPadding: const EdgeInsets.symmetric(vertical: 12),
                    ),
                  ),
                ),
                const SizedBox(height: 10),

                // Category Filter Pills
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  child: Row(
                    children: _categories.map((c) {
                      final isSelected = _selectedCategory == c['key'];
                      return Padding(
                        padding: const EdgeInsets.only(right: 8),
                        child: InkWell(
                          onTap: () {
                            if (_selectedCategory == c['key']) return;
                            setState(() => _selectedCategory = c['key']!);
                            _loadLogs(reset: true);
                          },
                          borderRadius: BorderRadius.circular(20),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                            decoration: BoxDecoration(
                              color: isSelected
                                  ? const Color(0xFF002366)
                                  : (isDark ? const Color(0xFF131D31) : const Color(0xFFF1F5F9)),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(
                                color: isSelected
                                    ? const Color(0xFF002366)
                                    : (isDark ? const Color(0xFF1E2B45) : const Color(0xFFCBD5E1)),
                              ),
                            ),
                            child: Text(
                              c['label']!,
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontSize: 12,
                                fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                                color: isSelected
                                    ? Colors.white
                                    : (isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B)),
                              ),
                            ),
                          ),
                        ),
                      );
                    }).toList(),
                  ),
                ),
              ],
            ),
          ),
          Divider(
            height: 1,
            color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFE2E8F0),
          ),

          // Log list
          Expanded(
            child: _isLoading
                ? const Center(
                    child: CircularProgressIndicator(
                      strokeWidth: 2.5,
                      color: Color(0xFF002366),
                    ),
                  )
                : _errorMessage != null
                    ? Center(
                        child: Padding(
                          padding: const EdgeInsets.all(24),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.error_outline_rounded, size: 42, color: Colors.red.shade400),
                              const SizedBox(height: 12),
                              Text(
                                _errorMessage!,
                                textAlign: TextAlign.center,
                                style: TextStyle(
                                  fontFamily: 'DM Sans',
                                  fontSize: 14,
                                  color: isDark ? const Color(0xFFCBD5E1) : const Color(0xFF64748B),
                                ),
                              ),
                              const SizedBox(height: 16),
                              ElevatedButton(
                                onPressed: () => _loadLogs(reset: true),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: const Color(0xFF002366),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                ),
                                child: const Text('Try Again', style: TextStyle(fontFamily: 'DM Sans')),
                              ),
                            ],
                          ),
                        ),
                      )
                    : _logs.isEmpty
                        ? Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.history_rounded, size: 48, color: Colors.grey.shade400),
                                const SizedBox(height: 12),
                                Text(
                                  'No activity records found',
                                  style: TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontWeight: FontWeight.w700,
                                    fontSize: 16,
                                    color: isDark ? Colors.white : const Color(0xFF0F172A),
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  'Actions will appear here as they happen.',
                                  style: TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontSize: 12.5,
                                    color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                                  ),
                                ),
                              ],
                            ),
                          )
                        : RefreshIndicator(
                            color: const Color(0xFF002366),
                            onRefresh: () => _loadLogs(reset: true),
                            child: ListView.separated(
                              controller: _scrollCtrl,
                              padding: const EdgeInsets.all(16),
                              itemCount: _logs.length + (_isLoadingMore ? 1 : 0),
                              separatorBuilder: (_, __) => const SizedBox(height: 10),
                              itemBuilder: (context, index) {
                                if (index >= _logs.length) {
                                  return const Padding(
                                    padding: EdgeInsets.symmetric(vertical: 16),
                                    child: Center(
                                      child: CircularProgressIndicator(strokeWidth: 2),
                                    ),
                                  );
                                }

                                final item = _logs[index];
                                final actionType = item['action_type']?.toString() ?? '';
                                final actionLabel = item['action_label']?.toString() ?? actionType;
                                final userName = item['user_name']?.toString() ?? 'User';
                                final userEmail = item['user_email']?.toString() ?? '';
                                final userRole = item['user_role']?.toString() ?? 'User';
                                final createdAt = item['created_at']?.toString() ?? '';
                                final timeAgo = item['time_ago']?.toString() ?? '';

                                final badgeColor = _actionBadgeColor(actionType);
                                final initials = userName.length >= 2 ? userName.substring(0, 2).toUpperCase() : 'U';

                                return Container(
                                  padding: const EdgeInsets.all(16),
                                  decoration: BoxDecoration(
                                    color: isDark ? const Color(0xFF0D1526) : Colors.white,
                                    borderRadius: BorderRadius.circular(16),
                                    border: Border.all(
                                      color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFE2E8F0),
                                    ),
                                    boxShadow: [
                                      BoxShadow(
                                        color: Colors.black.withOpacity(0.02),
                                        blurRadius: 6,
                                        offset: const Offset(0, 2),
                                      ),
                                    ],
                                  ),
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      // Top row: Action badge & Timestamp
                                      Row(
                                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                            decoration: BoxDecoration(
                                              color: badgeColor.withOpacity(0.14),
                                              borderRadius: BorderRadius.circular(20),
                                              border: Border.all(color: badgeColor.withOpacity(0.3)),
                                            ),
                                            child: Row(
                                              mainAxisSize: MainAxisSize.min,
                                              children: [
                                                Icon(_actionIcon(actionType), size: 14, color: badgeColor),
                                                const SizedBox(width: 6),
                                                Text(
                                                  actionLabel,
                                                  style: TextStyle(
                                                    fontFamily: 'DM Sans',
                                                    fontWeight: FontWeight.w700,
                                                    fontSize: 11.5,
                                                    color: badgeColor,
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                          Text(
                                            timeAgo,
                                            style: TextStyle(
                                              fontFamily: 'DM Sans',
                                              fontSize: 11,
                                              fontWeight: FontWeight.w500,
                                              color: isDark ? const Color(0xFF64748B) : const Color(0xFF94A3B8),
                                            ),
                                          ),
                                        ],
                                      ),
                                      const SizedBox(height: 12),

                                      // Middle row: Actor details
                                      Row(
                                        children: [
                                          CircleAvatar(
                                            radius: 16,
                                            backgroundColor: const Color(0xFF002366),
                                            child: Text(
                                              initials,
                                              style: const TextStyle(
                                                color: Colors.white,
                                                fontSize: 11,
                                                fontWeight: FontWeight.w800,
                                              ),
                                            ),
                                          ),
                                          const SizedBox(width: 10),
                                          Expanded(
                                            child: Column(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Row(
                                                  children: [
                                                    Text(
                                                      userName,
                                                      style: TextStyle(
                                                        fontFamily: 'DM Sans',
                                                        fontWeight: FontWeight.w700,
                                                        fontSize: 13.5,
                                                        color: isDark ? Colors.white : const Color(0xFF0F172A),
                                                      ),
                                                    ),
                                                    const SizedBox(width: 8),
                                                    Container(
                                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                                                      decoration: BoxDecoration(
                                                        color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFE2E8F0),
                                                        borderRadius: BorderRadius.circular(6),
                                                      ),
                                                      child: Text(
                                                        userRole,
                                                        style: TextStyle(
                                                          fontFamily: 'DM Sans',
                                                          fontSize: 9.5,
                                                          fontWeight: FontWeight.w700,
                                                          color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                                                        ),
                                                      ),
                                                    ),
                                                  ],
                                                ),
                                                if (userEmail.isNotEmpty) ...[
                                                  const SizedBox(height: 1),
                                                  Text(
                                                    userEmail,
                                                    style: TextStyle(
                                                      fontFamily: 'DM Sans',
                                                      fontSize: 11.5,
                                                      color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                                                    ),
                                                  ),
                                                ],
                                              ],
                                            ),
                                          ),
                                        ],
                                      ),

                                      // Bottom: Exact timestamp
                                      const SizedBox(height: 10),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                        decoration: BoxDecoration(
                                          color: isDark ? const Color(0xFF131D31) : const Color(0xFFF8FAFC),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Row(
                                          children: [
                                            Icon(
                                              Icons.access_time_rounded,
                                              size: 13,
                                              color: isDark ? const Color(0xFF64748B) : const Color(0xFF94A3B8),
                                            ),
                                            const SizedBox(width: 6),
                                            Text(
                                              createdAt,
                                              style: TextStyle(
                                                fontFamily: 'DM Sans',
                                                fontSize: 11.5,
                                                color: isDark ? const Color(0xFF94A3B8) : const Color(0xFF64748B),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ],
                                  ),
                                );
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }
}
