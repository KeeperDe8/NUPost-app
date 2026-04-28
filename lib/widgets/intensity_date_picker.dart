import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';

class IntensityDatePicker extends StatefulWidget {
  final DateTime initialDate;
  final DateTime firstDate;
  final DateTime lastDate;

  const IntensityDatePicker({
    super.key,
    required this.initialDate,
    required this.firstDate,
    required this.lastDate,
  });

  @override
  State<IntensityDatePicker> createState() => _IntensityDatePickerState();
}

class _IntensityDatePickerState extends State<IntensityDatePicker> {
  late DateTime _focusedMonth;
  DateTime? _selectedDate;
  bool _isLoading = false;

  // Mapping from DateTime (just year/month/day) to list of post titles
  Map<DateTime, List<String>> _monthPosts = {};

  @override
  void initState() {
    super.initState();
    _selectedDate = widget.initialDate;
    _focusedMonth = DateTime(
      widget.initialDate.year,
      widget.initialDate.month,
      1,
    );
    _fetchMonthData();
  }

  Future<void> _fetchMonthData() async {
    setState(() => _isLoading = true);
    try {
      final userId = SessionStore.userId ?? 0;
      // Fetch public view to see overall usage of dates
      final res = await ApiService.fetchCalendar(
        userId: userId,
        month: _focusedMonth.month,
        year: _focusedMonth.year,
        publicView: true,
      );

      if (res['success'] == true) {
        final data = res['data'] ?? {};
        final Map<DateTime, List<String>> newMap = {};

        // Merge my_requests and public_calendar if applicable
        final List<dynamic> allPosts = [];
        if (data['my_requests'] is List) {
          allPosts.addAll(data['my_requests'] as List);
        }
        if (data['public_calendar'] is List) {
          allPosts.addAll(data['public_calendar'] as List);
        }

        for (final item in allPosts) {
          final sDate = item['scheduled_date']?.toString();
          final rDate = item['request_date']?.toString();
          final title =
              item['title']?.toString() ??
              item['platform']?.toString() ??
              'Post';
          final dStr = sDate ?? rDate;

          if (dStr != null && dStr.isNotEmpty) {
            try {
              final d = DateTime.parse(dStr);
              final normalized = DateTime(d.year, d.month, d.day);
              newMap.putIfAbsent(normalized, () => []).add(title);
            } catch (_) {}
          }
        }

        if (mounted) {
          setState(() {
            _monthPosts = newMap;
          });
        }
      }
    } catch (_) {
      // Ignored for UI
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _changeMonth(int offset) {
    final nextMonth = DateTime(
      _focusedMonth.year,
      _focusedMonth.month + offset,
      1,
    );

    // basic boundary checks
    final minMF = DateTime(widget.firstDate.year, widget.firstDate.month, 1);
    final maxMF = DateTime(widget.lastDate.year, widget.lastDate.month, 1);

    if (nextMonth.isBefore(minMF) && offset < 0) return;
    if (nextMonth.isAfter(maxMF) && offset > 0) return;

    setState(() {
      _focusedMonth = nextMonth;
      _monthPosts.clear();
    });
    _fetchMonthData();
  }

  Widget _buildHeader() {
    final monthNames = [
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
    final mName = monthNames[_focusedMonth.month - 1];

    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        IconButton(
          icon: const Icon(Icons.chevron_left, color: Color(0xFF080F1E)),
          onPressed: () => _changeMonth(-1),
        ),
        Text(
          '$mName ${_focusedMonth.year}',
          style: const TextStyle(
            fontFamily: 'DM Sans',
            fontSize: 16,
            fontWeight: FontWeight.w700,
            color: Color(0xFF080F1E),
          ),
        ),
        IconButton(
          icon: const Icon(Icons.chevron_right, color: Color(0xFF080F1E)),
          onPressed: () => _changeMonth(1),
        ),
      ],
    );
  }

  Widget _buildCalendarGrid() {
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    final firstDayInMonth = DateTime(
      _focusedMonth.year,
      _focusedMonth.month,
      1,
    );
    final daysInMonth = DateTime(
      _focusedMonth.year,
      _focusedMonth.month + 1,
      0,
    ).day;
    final startOffset = firstDayInMonth.weekday % 7;

    final List<Widget> dayHeaders = dayNames
        .map(
          (d) => Expanded(
            child: Center(
              child: Padding(
                padding: const EdgeInsets.only(bottom: 8.0),
                child: Text(
                  d,
                  style: const TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF9AA3B2),
                  ),
                ),
              ),
            ),
          ),
        )
        .toList();

    return Column(
      children: [
        Row(children: dayHeaders),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 7,
            mainAxisSpacing: 8,
            crossAxisSpacing: 8,
            childAspectRatio: 1.0,
          ),
          // 42 cells guarantees 6 full rows
          itemCount: 42,
          itemBuilder: (context, index) {
            final day = index - startOffset + 1;

            if (day < 1 || day > daysInMonth) {
              return const SizedBox();
            }

            final currentDate = DateTime(
              _focusedMonth.year,
              _focusedMonth.month,
              day,
            );
            final selDateObj = _selectedDate != null
                ? DateTime(
                    _selectedDate!.year,
                    _selectedDate!.month,
                    _selectedDate!.day,
                  )
                : null;

            final isBeforeFirst = currentDate.isBefore(
              DateTime(
                widget.firstDate.year,
                widget.firstDate.month,
                widget.firstDate.day,
              ),
            );
            final isAfterLast = currentDate.isAfter(
              DateTime(
                widget.lastDate.year,
                widget.lastDate.month,
                widget.lastDate.day,
              ),
            );

            final isDisabled = isBeforeFirst || isAfterLast;
            final isSelected = selDateObj == currentDate;

            final posts = _monthPosts[currentDate] ?? [];
            final intensityCount = posts.length;

            Color bgColor = Colors.transparent;
            Color textColor = isDisabled
                ? const Color(0xFFD4D8E0)
                : const Color(0xFF080F1E);
            Border? border;

            if (isSelected && !isDisabled) {
              border = Border.all(color: const Color(0xFF002366), width: 2);
            }

            if (!isDisabled) {
              if (intensityCount > 0 && intensityCount < 3) {
                bgColor = Colors.red.withOpacity(0.15); // Light red
              } else if (intensityCount >= 3) {
                bgColor = Colors.red.withOpacity(0.7); // Dark red
                textColor = Colors.white;
              }
            }

            return GestureDetector(
              onTap: isDisabled
                  ? null
                  : () => setState(() => _selectedDate = currentDate),
              child: Container(
                decoration: BoxDecoration(
                  color: bgColor,
                  borderRadius: BorderRadius.circular(8),
                  border: border,
                ),
                alignment: Alignment.center,
                child: Text(
                  '$day',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 14,
                    fontWeight: isSelected ? FontWeight.w700 : FontWeight.w500,
                    color: textColor,
                  ),
                ),
              ),
            );
          },
        ),
      ],
    );
  }

  Widget _buildSelectedDayPostsList() {
    if (_selectedDate == null) return const SizedBox();

    final selNormalized = DateTime(
      _selectedDate!.year,
      _selectedDate!.month,
      _selectedDate!.day,
    );
    final posts = _monthPosts[selNormalized] ?? [];

    if (posts.isEmpty) {
      return Padding(
        padding: const EdgeInsets.only(top: 12.0),
        child: Text(
          'No scheduled posts for this date.',
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontSize: 13,
            color: Colors.green.shade700,
            fontWeight: FontWeight.w600,
          ),
        ),
      );
    }

    return Container(
      margin: const EdgeInsets.only(top: 12.0),
      constraints: const BoxConstraints(maxHeight: 100),
      child: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              '${posts.length} upcoming post${posts.length == 1 ? "" : "s"} on ${_selectedDate!.month}/${_selectedDate!.day}:',
              style: const TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 13,
                color: Colors.red,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 6),
            ...posts.map(
              (title) => Padding(
                padding: const EdgeInsets.only(bottom: 5.0),
                child: Row(
                  children: [
                    const Icon(Icons.circle, size: 6, color: Colors.redAccent),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        title,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontSize: 13,
                          color: Color(0xFF3D4A63),
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      backgroundColor: Colors.white,
      insetPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
      child: Padding(
        padding: const EdgeInsets.all(20.0),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text(
              'Select Date',
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 18,
                fontWeight: FontWeight.w700,
                color: Color(0xFF080F1E),
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 12),
            _buildHeader(),
            const SizedBox(height: 8),

            if (_isLoading)
              const SizedBox(
                height: 250,
                child: Center(
                  child: CircularProgressIndicator(color: Color(0xFF002366)),
                ),
              )
            else
              _buildCalendarGrid(),

            AnimatedSize(
              duration: const Duration(milliseconds: 200),
              child: _buildSelectedDayPostsList(),
            ),

            const SizedBox(height: 20),
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                TextButton(
                  onPressed: () => Navigator.of(context).pop(null),
                  style: TextButton.styleFrom(
                    foregroundColor: const Color(0xFF9AA3B2),
                  ),
                  child: const Text(
                    'Cancel',
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                ElevatedButton(
                  onPressed: _selectedDate == null
                      ? null
                      : () => Navigator.of(context).pop(_selectedDate),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF002366),
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 16,
                      vertical: 10,
                    ),
                  ),
                  child: const Text(
                    'Confirm',
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      color: Colors.white,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
