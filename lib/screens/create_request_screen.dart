import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import '../main_shell.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';
import '../services/app_memory_cache.dart';
import '../widgets/app_snackbar.dart';
import '../widgets/sla_guidelines_sheet.dart';

class CreateRequestScreen extends StatefulWidget {
  final Map<String, dynamic>? initialData;
  final bool isEditing;

  const CreateRequestScreen({
    super.key,
    this.initialData,
    this.isEditing = false,
  });

  @override
  State<CreateRequestScreen> createState() => _CreateRequestScreenState();
}

class _CreateRequestScreenState extends State<CreateRequestScreen>
    with TickerProviderStateMixin {
  // ── Controllers ────────────────────────────────────────────────────────────
  late final AnimationController _entryCtrl;
  late final Animation<double> _entryFade;
  late final Animation<Offset> _entrySlide;
  late final AnimationController _submitScaleController;
  late final AnimationController _successController;
  late final Animation<double> _successScale;
  late final Animation<double> _successFade;

  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _captionController = TextEditingController();

  // ── Form state ─────────────────────────────────────────────────────────────
  final Map<String, bool> _platforms = {'Facebook': false, 'LinkedIn': false};

  String? _selectedCategory;
  String? _selectedPriority;
  DateTime? _selectedDate;

  bool _isLoadingDateData = false;
  int _datePostCount = 0;
  List<Map<String, dynamic>> _dateUpcomingPosts = [];

  int _captionLength = 0;
  bool _isSubmitting = false;
  bool _isGeneratingCaption = false;
  bool _submitted = false;
  List<PlatformFile> _mediaFiles = [];

  final List<String> _categories = [
    'Event',
    'Announcement',
    'News',
    'Achievement',
    'Promotion',
  ];

  static Map<String, dynamic> getSlaInfo(String? category) {
    switch (category) {
      case 'Event':
        return {
          'turnaround': '2–4 working days',
          'minDays': 3,
          'note': 'Standard PubMat (2–4 days). Event coverage requires 30 days notice.',
        };
      case 'Promotion':
        return {
          'turnaround': '2–4 working days',
          'minDays': 3,
          'note': 'Campaign & promotional pubmat (min. 3 days lead time)',
        };
      case 'Announcement':
        return {
          'turnaround': 'Up to 48 hours',
          'minDays': 2,
          'note': 'Template-based announcement (min. 48h lead time)',
        };
      case 'News':
        return {
          'turnaround': '24–48 hours',
          'minDays': 1,
          'note': 'News, articles, & updates (min. 24h lead time)',
        };
      case 'Achievement':
        return {
          'turnaround': 'Up to 48 hours',
          'minDays': 2,
          'requiresMedia': true,
          'note': 'Recognition pubmat (requires photo/certificate, min. 48h lead time)',
        };
      // Fallback mappings for legacy/SLA classifications
      case 'Checking of Materials':
      case 'Ready-Made PubMat':
        return {
          'turnaround': 'Up to 24 hours',
          'minDays': 1,
          'note': 'Review of ready materials (min. 24h lead time)',
        };
      case 'Template-Based PubMat':
        return {
          'turnaround': 'Up to 48 hours',
          'minDays': 2,
          'note': 'Announcements, news, articles (min. 48h lead time)',
        };
      case 'Standard PubMat':
        return {
          'turnaround': '2–4 working days',
          'minDays': 3,
          'note': 'Custom event promotional graphic (min. 3 days lead time)',
        };
      case 'Tarpaulin / Collaterals':
      case 'Multiple Collateral Materials / Tarpaulins':
        return {
          'turnaround': '5–10 working days',
          'minDays': 5,
          'note': 'Tarpaulins & print collaterals (min. 5 working days)',
        };
      case 'New Campaign Concept':
      case 'New Campaign / Creative Concept':
        return {
          'turnaround': '10–20 working days',
          'minDays': 10,
          'note': 'Full campaign art direction & branding (min. 10 days)',
        };
      case 'Event Documentation':
        return {
          'turnaround': '1 month prior to event',
          'minDays': 30,
          'note': 'Booking media team coverage (min. 30 days advance notice)',
        };
      default:
        return {
          'turnaround': '2–4 working days',
          'minDays': 1,
          'note': 'Marketing review requires at least 24h lead time',
        };
    }
  }

  final List<String> _priorities = ['Low', 'Medium', 'High', 'Urgent'];

  final Map<String, IconData> _platformIcons = {
    'Facebook': Icons.facebook_rounded,
    'LinkedIn': Icons.business_rounded,
  };

  // ── Step progress ──────────────────────────────────────────────────────────
  int get _completedSteps {
    int s = 0;
    if (_titleController.text.trim().isNotEmpty &&
        _descriptionController.text.trim().isNotEmpty) {
      s++;
    }
    if (_platforms.values.any((v) => v)) s++;
    if (_selectedCategory != null && _selectedPriority != null) s++;
    if (_selectedDate != null) s++;
    return s;
  }

  @override
  void initState() {
    super.initState();
    _entryCtrl = AnimationController(vsync: this, duration: const Duration(milliseconds: 650))..forward();
    _entryFade = CurvedAnimation(parent: _entryCtrl, curve: const Interval(0.0, 0.7, curve: Curves.easeOut));
    _entrySlide = Tween<Offset>(begin: const Offset(0, 0.03), end: Offset.zero)
        .animate(CurvedAnimation(parent: _entryCtrl, curve: const Interval(0.0, 0.8, curve: Curves.easeOutCubic)));

    _submitScaleController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 150),
      lowerBound: 0.0,
      upperBound: 1.0,
      value: 0.0,
    );

    _successController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 600),
    );
    _successScale = Tween<double>(begin: 0.5, end: 1.0).animate(
      CurvedAnimation(parent: _successController, curve: Curves.elasticOut),
    );
    _successFade = CurvedAnimation(
      parent: _successController,
      curve: Curves.easeOut,
    );

    _titleController.addListener(() => setState(() {}));
    _descriptionController.addListener(() => setState(() {}));

    if (widget.isEditing && widget.initialData != null) {
      final init = widget.initialData!;
      _titleController.text = (init['title'] ?? '').toString();
      _descriptionController.text = (init['description'] ?? '').toString();

      String cap = (init['caption'] ?? '').toString();
      final footerIdx = cap.indexOf('Apply now and secure your place');
      if (footerIdx != -1) {
        cap = cap.substring(0, footerIdx).trim();
      }
      _captionController.text = cap;
      _captionLength = cap.length;

      final cat = (init['category'] ?? '').toString();
      if (_categories.contains(cat)) {
        _selectedCategory = cat;
      } else if (cat.isNotEmpty) {
        _categories.add(cat);
        _selectedCategory = cat;
      }

      final prio = (init['priority'] ?? '').toString();
      if (_priorities.contains(prio)) {
        _selectedPriority = prio;
      } else if (prio.isNotEmpty) {
        _selectedPriority = _priorities.first;
      }

      final pDateStr = (init['preferred_date'] ?? '').toString();
      if (pDateStr.isNotEmpty) {
        _selectedDate = DateTime.tryParse(pDateStr);
        if (_selectedDate != null) {
          _fetchDateInfo(_selectedDate!);
        }
      }

      final pStr = (init['platform'] ?? '').toString();
      for (final key in _platforms.keys) {
        if (pStr.toLowerCase().contains(key.toLowerCase())) {
          _platforms[key] = true;
        }
      }
    }
  }

  @override
  void dispose() {
    _entryCtrl.dispose();
    _submitScaleController.dispose();
    _successController.dispose();
    _titleController.dispose();
    _descriptionController.dispose();
    _captionController.dispose();
    super.dispose();
  }

  // ── All original logic preserved ─────────────────────────────────────────
  Future<void> _fetchDateInfo(DateTime date) async {
    setState(() => _isLoadingDateData = true);
    try {
      final res = await ApiService.fetchCalendar(
        userId: SessionStore.userId ?? 0,
        month: date.month,
        year: date.year,
        publicView: true,
      );
      if (res['success'] == true) {
        final data = res['data'] ?? {};
        final List<dynamic> allPosts = [];
        if (data['posts'] is List) {
          allPosts.addAll(data['posts']);
        }

        int count = 0;
        List<Map<String, dynamic>> matchingPosts = [];
        for (final item in allPosts) {
          final sDate = item['scheduled_date']?.toString();
          final rDate = item['request_date']?.toString();
          final dStr = sDate ?? rDate;
          if (dStr != null && dStr.isNotEmpty) {
            try {
              final d = DateTime.parse(dStr);
              if (d.year == date.year &&
                  d.month == date.month &&
                  d.day == date.day) {
                count++;
                matchingPosts.add(item as Map<String, dynamic>);
              }
            } catch (_) {}
          }
        }
        if (mounted) {
          setState(() {
            _datePostCount = count;
            _dateUpcomingPosts = matchingPosts;
          });
        }
      }
    } catch (_) {
    } finally {
      if (mounted) setState(() => _isLoadingDateData = false);
    }
  }

  Future<void> _pickDate() async {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate ?? DateTime.now().add(const Duration(days: 1)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (context, child) => Theme(
        data: isDark
            ? ThemeData.dark().copyWith(
                scaffoldBackgroundColor: const Color(0xFF131D31),
                dialogBackgroundColor: const Color(0xFF131D31),
                colorScheme: const ColorScheme.dark(
                  primary: Color(0xFFFFD200),
                  onPrimary: Color(0xFF0A0F1D),
                  surface: Color(0xFF131D31),
                  onSurface: Colors.white,
                ),
              )
            : Theme.of(context).copyWith(
                colorScheme: const ColorScheme.light(primary: Color(0xFF002366)),
              ),
        child: child!,
      ),
    );
    if (picked != null) {
      if (_selectedDate == null ||
          _selectedDate!.year != picked.year ||
          _selectedDate!.month != picked.month ||
          _selectedDate!.day != picked.day) {
        setState(() => _selectedDate = picked);
        _fetchDateInfo(picked);
      }
    }
  }

  Future<void> _onGenerateCaption() async {
    if (_isGeneratingCaption) return;
    final title = _titleController.text.trim();
    final description = _descriptionController.text.trim();
    final category = _selectedCategory ?? 'General';
    final platforms = _platforms.entries
        .where((e) => e.value)
        .map((e) => e.key)
        .toList();

    if (title.isEmpty || description.isEmpty) {
      AppSnackbar.show(context, 'Enter title and description before generating caption.', isError: true);
      return;
    }
    setState(() => _isGeneratingCaption = true);
    try {
      final caption = await ApiService.generateCaption(
        title: title,
        description: description,
        category: category,
        platforms: platforms,
      );
      if (!mounted) return;
      setState(() {
        _captionController.text = caption;
        _captionLength = caption.length;
      });
    } catch (e) {
      if (!mounted) return;
      final fallback = _buildFallbackCaption(
        title: title,
        category: category,
        platforms: platforms,
      );
      setState(() {
        _captionController.text = fallback;
        _captionLength = fallback.length;
      });
      AppSnackbar.show(context, 'AI unavailable. Used smart fallback caption.');
    } finally {
      if (mounted) setState(() => _isGeneratingCaption = false);
    }
  }

  Future<void> _pickMedia() async {
    final result = await FilePicker.platform.pickFiles(
      allowMultiple: true,
      withData: true,
      type: FileType.custom,
      allowedExtensions: const [
        'png',
        'jpg',
        'jpeg',
        'gif',
        'webp',
        'mp4',
        'mov',
      ],
    );
    if (result == null || result.files.isEmpty) return;
    final picked = result.files
        .where((f) => f.path != null || f.bytes != null)
        .toList();
    if (picked.isEmpty) return;
    setState(() {
      _mediaFiles = picked.take(4).toList();
    });
    if (picked.length > 4 && mounted) {
      AppSnackbar.show(context, 'Only first 4 files were selected.');
    }
  }

  Future<void> _onSubmit() async {
    if (_isSubmitting) return;
    final userId = SessionStore.userId;
    if (userId == null) {
      AppSnackbar.show(context, 'Please login first.', isError: true);
      return;
    }
    final platforms = _platforms.entries
        .where((e) => e.value)
        .map((e) => e.key)
        .toList();

    // ── Guardrail Validation List ───────────────────────────────────────────
    final List<String> errors = [];

    // 1. Title validation
    final title = _titleController.text.trim();
    if (title.isEmpty) {
      errors.add('Request Title is required.');
    } else if (title.length < 5) {
      errors.add('Request Title must be at least 5 characters long.');
    }

    // 2. Description validation
    final desc = _descriptionController.text.trim();
    if (desc.isEmpty) {
      errors.add('Description is required.');
    } else if (desc.length < 15) {
      errors.add('Description must be at least 15 characters long to provide sufficient detail for marketing review.');
    }

    // 3. Platform validation
    if (platforms.isEmpty) {
      errors.add('Please select at least one target platform (Facebook or LinkedIn).');
    }

    // 4. Category & Priority
    if (_selectedCategory == null || _selectedCategory!.isEmpty) {
      errors.add('Please select a Category.');
    }
    if (_selectedPriority == null || _selectedPriority!.isEmpty) {
      errors.add('Please select a Priority level.');
    }

    // 5. Date & SLA Lead Time Validation
    if (_selectedDate == null) {
      errors.add('Please select a preferred posting date.');
    } else {
      final now = DateTime.now();
      final today = DateTime(now.year, now.month, now.day);
      final selected = DateTime(_selectedDate!.year, _selectedDate!.month, _selectedDate!.day);
      final diffDays = selected.difference(today).inDays;

      if (diffDays < 1) {
        errors.add('Preferred date cannot be today or in the past. All requests require at least 24 hours advance notice.');
      } else if (_selectedCategory != null) {
        final sla = getSlaInfo(_selectedCategory);
        final minDays = (sla['minDays'] as int?) ?? 1;
        if (diffDays < minDays) {
          final earliest = today.add(Duration(days: minDays));
          final earliestStr = '${earliest.month}/${earliest.day}/${earliest.year}';
          errors.add('$_selectedCategory requests require at least $minDays day${minDays == 1 ? '' : 's'} advance notice under SLA guidelines (Earliest date: $earliestStr).');
        }
      }
    }

    // 6. Category-specific conditions
    if (_selectedCategory == 'Achievement') {
      final hasMedia = _mediaFiles.isNotEmpty ||
          (widget.isEditing &&
              widget.initialData != null &&
              widget.initialData!['media_file'] != null &&
              widget.initialData!['media_file'].toString().trim().isNotEmpty);
      if (!hasMedia) {
        errors.add('Achievement requests require at least one attached photo, award, or certificate of the achiever.');
      }
    }

    if (_selectedCategory == 'Event' && desc.length < 25) {
      errors.add('Event requests require detailed information in Description (e.g. event date/time, venue, target participants, and program flow).');
    }

    // Fallback condition for ready-made pubmat if legacy category is used
    if (_selectedCategory == 'Ready-Made PubMat') {
      final hasMedia = _mediaFiles.isNotEmpty ||
          (widget.isEditing &&
              widget.initialData != null &&
              widget.initialData!['media_file'] != null &&
              widget.initialData!['media_file'].toString().trim().isNotEmpty);
      if (!hasMedia) {
        errors.add('Ready-Made PubMat requests require at least one attached pubmat image or document.');
      }
      if (_captionController.text.trim().isEmpty) {
        errors.add('Ready-Made PubMat requests require a complete caption to be provided.');
      }
    }

    if (errors.isNotEmpty) {
      _showValidationErrorsDialog(errors);
      return;
    }

    if (_datePostCount >= 3) {
      final isDark = Theme.of(context).brightness == Brightness.dark;
      final confirmed = await showDialog<bool>(
        context: context,
        useRootNavigator: false,
        builder: (_) => AlertDialog(
          backgroundColor: isDark ? const Color(0xFF131D31) : Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(20),
            side: isDark ? const BorderSide(color: Color(0xFF1E2B45)) : BorderSide.none,
          ),
          title: Text(
            'Busy date',
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontWeight: FontWeight.w800,
              fontSize: 18,
              color: isDark ? Colors.white : const Color(0xFF080F1E),
            ),
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'This date already has $_datePostCount scheduled post${_datePostCount == 1 ? '' : 's'}. '
                  'Posting here may overlap with others. Continue anyway?',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 14,
                    color: isDark ? const Color(0xFFCBD5E1) : const Color(0xFF3D4A63),
                  ),
                ),
                if (_dateUpcomingPosts.isNotEmpty) ...[
                  const SizedBox(height: 14),
                  ..._dateUpcomingPosts.take(5).map((post) {
                    final status = post['status']?.toString() ?? 'Pending';
                    final title = post['title']?.toString() ??
                        post['platform']?.toString() ??
                        'Untitled';
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 6),
                      child: Row(
                        children: [
                          Container(
                            width: 7,
                            height: 7,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: _statusColor(status),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              title,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontWeight: FontWeight.w600,
                                fontSize: 12.5,
                                color: isDark ? Colors.white : const Color(0xFF3D4A63),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Text(
                            status,
                            style: TextStyle(
                              fontFamily: 'DM Sans',
                              fontSize: 11,
                              fontWeight: FontWeight.w600,
                              color: _statusColor(status),
                            ),
                          ),
                        ],
                      ),
                    );
                  }),
                ],
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: Text(
                'Pick another date',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w600,
                  color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF9AA3B2),
                ),
              ),
            ),
            TextButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text(
                'Continue',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w800,
                  color: Color(0xFFFF3B30),
                ),
              ),
            ),
          ],
        ),
      );
      if (confirmed != true) return;
    }

    setState(() => _isSubmitting = true);
    try {
      final preferredDate =
          '${_selectedDate!.year.toString().padLeft(4, '0')}-'
          '${_selectedDate!.month.toString().padLeft(2, '0')}-'
          '${_selectedDate!.day.toString().padLeft(2, '0')}';

      final originalCaption = _captionController.text.trim();
      final footer = "\n\nApply now and secure your place for the upcoming academic year: https://onlineapp.nu-lipa.edu.ph/quest/register.php\nExperience 𝘌𝘥𝘶𝘤𝘢𝘵𝘪𝘰𝘯 𝘛𝘩𝘢𝘵 𝘞𝘰𝘳𝘬𝘴. #NULipa #EducationThatWorks";
      final finalCaption = originalCaption.isNotEmpty 
          ? "$originalCaption$footer" 
          : footer.trim();

      if (widget.isEditing && widget.initialData != null) {
        final reqId = (widget.initialData!['id'] is int)
            ? widget.initialData!['id'] as int
            : int.parse(widget.initialData!['id'].toString());

        await ApiService.updateRequest(
          requestId: reqId,
          userId: userId,
          title: _titleController.text.trim(),
          description: _descriptionController.text.trim(),
          category: _selectedCategory!,
          priority: _selectedPriority!,
          platforms: platforms,
          preferredDate: preferredDate,
          caption: finalCaption,
          mediaFiles: _mediaFiles,
          keepExistingMedia: _mediaFiles.isEmpty,
        );

        AppMemoryCache.invalidateRequests();

        if (!mounted) return;

        setState(() {
          _submitted = true;
        });
        _successController.forward();

        await Future.delayed(const Duration(milliseconds: 600));
        if (!mounted) return;

        AppSnackbar.show(context, 'Request updated & resubmitted successfully.', isSuccess: true);
        Navigator.pop(context, true);
        return;
      }

      await ApiService.createRequest(
        userId: userId,
        title: _titleController.text.trim(),
        description: _descriptionController.text.trim(),
        category: _selectedCategory!,
        priority: _selectedPriority!,
        platforms: platforms,
        preferredDate: preferredDate,
        caption: finalCaption,
        mediaFiles: _mediaFiles,
      );

      AppMemoryCache.invalidateRequests();

      if (!mounted) return;

      // Show success overlay
      setState(() {
        _submitted = true;
      });
      _successController.forward();

      await Future.delayed(const Duration(milliseconds: 400));
      if (!mounted) return;

      // Reset form
      setState(() {
        _submitted = false;
        _titleController.clear();
        _descriptionController.clear();
        _captionController.clear();
        _selectedCategory = null;
        _selectedPriority = null;
        _selectedDate = null;
        _captionLength = 0;
        _mediaFiles = [];
        _datePostCount = 0;
        _dateUpcomingPosts = [];
        for (final key in _platforms.keys) {
          _platforms[key] = false;
        }
      });
      _successController.reset();
      AppSnackbar.show(context, 'Request submitted successfully.', isSuccess: true);
      
      // Auto-navigate back to Home dashboard
      MainShell.switchTo(context, 0);
    } catch (e) {
      if (!mounted) return;
      AppSnackbar.show(
        context,
        e.toString().replaceFirst('Exception: ', ''),
        isError: true,
      );
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  void _showValidationErrorsDialog(List<String> errors) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    showDialog(
      context: context,
      useRootNavigator: false,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(24),
          side: isDark ? const BorderSide(color: Color(0xFF1E2B45)) : BorderSide.none,
        ),
        backgroundColor: isDark ? const Color(0xFF131D31) : Colors.white,
        titlePadding: const EdgeInsets.fromLTRB(22, 22, 22, 10),
        contentPadding: const EdgeInsets.fromLTRB(22, 0, 22, 16),
        actionsPadding: const EdgeInsets.fromLTRB(22, 0, 22, 20),
        title: Row(
          children: [
            Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF2E1218) : const Color(0xFFFFF1F2),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(
                Icons.error_outline_rounded,
                color: Color(0xFFE11D48),
                size: 22,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                'Action Required',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w900,
                  fontSize: 18,
                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                ),
              ),
            ),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Please resolve the following requirements before submitting your request:',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontSize: 13,
                  color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF64748B),
                  height: 1.4,
                ),
              ),
              const SizedBox(height: 14),
              ...errors.map((err) => Container(
                margin: const EdgeInsets.only(bottom: 9),
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF0D1527) : const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFE2E8F0),
                  ),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Padding(
                      padding: EdgeInsets.only(top: 2),
                      child: Icon(
                        Icons.cancel_rounded,
                        size: 15,
                        color: Color(0xFFE11D48),
                      ),
                    ),
                    const SizedBox(width: 9),
                    Expanded(
                      child: Text(
                        err,
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontSize: 12.5,
                          fontWeight: FontWeight.w600,
                          color: isDark ? Colors.white : const Color(0xFF1E293B),
                          height: 1.35,
                        ),
                      ),
                    ),
                  ],
                ),
              )),
            ],
          ),
        ),
        actions: [
          SizedBox(
            width: double.infinity,
            height: 46,
            child: ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF002366),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14),
                  side: isDark
                      ? BorderSide(color: const Color(0xFFFFD200).withOpacity(0.4))
                      : BorderSide.none,
                ),
                elevation: 0,
              ),
              onPressed: () => Navigator.pop(ctx),
              child: const Text(
                'Fix Issues & Edit',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w800,
                  fontSize: 14,
                  color: Colors.white,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSlaHint() {
    if (_selectedCategory == null) return const SizedBox.shrink();
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final sla = getSlaInfo(_selectedCategory);
    final turnaround = (sla['turnaround'] ?? '2–4 days') as String;
    final note = (sla['note'] ?? '') as String;
    final minDays = (sla['minDays'] ?? 1) as int;

    bool isTooSoon = false;
    if (_selectedDate != null) {
      final now = DateTime.now();
      final today = DateTime(now.year, now.month, now.day);
      final sel = DateTime(_selectedDate!.year, _selectedDate!.month, _selectedDate!.day);
      final diff = sel.difference(today).inDays;
      if (diff < minDays) {
        isTooSoon = true;
      }
    }

    final bgColor = isTooSoon
        ? (isDark ? const Color(0xFF2A1215) : const Color(0xFFFFF1F2))
        : (isDark ? const Color(0xFF0D2518) : const Color(0xFFF0FDF4));
    final borderColor = isTooSoon
        ? (isDark ? const Color(0xFF5A1D24) : const Color(0xFFFECDD3))
        : (isDark ? const Color(0xFF144D2F) : const Color(0xFFBBF7D0));
    final iconColor = isTooSoon
        ? (isDark ? const Color(0xFFFF6B6B) : const Color(0xFFE11D48))
        : (isDark ? const Color(0xFF4ADE80) : const Color(0xFF16A34A));
    final textColor = isTooSoon
        ? (isDark ? const Color(0xFFFF6B6B) : const Color(0xFFBE123C))
        : (isDark ? const Color(0xFF4ADE80) : const Color(0xFF15803D));

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8.5),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: borderColor),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            isTooSoon ? Icons.warning_amber_rounded : Icons.verified_outlined,
            size: 16,
            color: iconColor,
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              isTooSoon
                  ? 'SLA Notice: $_selectedCategory requires at least $minDays day${minDays == 1 ? '' : 's'} advance notice. Selected date is too soon.'
                  : 'SLA Turnaround: $turnaround • $note',
              style: TextStyle(
                fontFamily: 'DM Sans',
                fontSize: 11.5,
                fontWeight: FontWeight.w600,
                color: textColor,
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _buildFallbackCaption({
    required String title,
    required String category,
    required List<String> platforms,
  }) {
    final target = platforms.isEmpty
        ? 'our social channels'
        : platforms.join(', ');
    return '$title\n\nStay tuned for this $category update from NU Lipa Marketing Office. '
        'Catch this post on $target. #NULipa #NUPost';
  }

  // ── Capacity helpers ──────────────────────────────────────────────────────
  Color _capacityColor(int count) {
    if (count == 0) return const Color(0xFF05C46B);
    if (count <= 2) return const Color(0xFFF59E0B);
    return const Color(0xFFFF3B30);
  }

  LinearGradient _capacityGradient(int count) {
    if (count == 0) {
      return const LinearGradient(
        colors: [Color(0xFF34D399), Color(0xFF05C46B)],
      );
    }
    if (count <= 2) {
      return const LinearGradient(
        colors: [Color(0xFFFBBF24), Color(0xFFF59E0B)],
      );
    }
    return const LinearGradient(colors: [Color(0xFFF87171), Color(0xFFFF3B30)]);
  }

  String _capacityLabel(int count) {
    if (count == 0) return 'Open';
    if (count == 1) return 'Light';
    if (count <= 2) return 'Moderate';
    return 'Busy';
  }

  Color _statusColor(String status) {
    final s = status.toLowerCase();
    if (s.contains('approved')) return const Color(0xFF05C46B);
    if (s.contains('review')) return const Color(0xFFF59E0B);
    return const Color(0xFF9AA3B2);
  }

  // ── BUILD ─────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0A0F1D) : const Color(0xFFE9EDF6),
      body: Stack(
        children: [
          FadeTransition(
            opacity: _entryFade,
            child: SlideTransition(
              position: _entrySlide,
              child: Column(
            children: [
              _buildHeader(),
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 160),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Progress indicator
                      _buildProgress(),
                      const SizedBox(height: 20),

                      // Section 1: Content
                      _SectionCard(
                        icon: Icons.edit_note_rounded,
                        title: 'Post Details',
                        children: [
                          _fieldLabel('Post / Event Title *'),
                          _textField(
                            _titleController,
                            hint: 'e.g. Welcome Back NU Peeps!',
                          ),
                          const SizedBox(height: 16),
                          _fieldLabel('Description *'),
                          _textAreaField(_descriptionController, rows: 5),
                        ],
                      ),
                      const SizedBox(height: 14),

                      // Section 2: Platforms
                      _SectionCard(
                        icon: Icons.share_rounded,
                        title: 'Platforms',
                        children: [
                          _fieldLabel('Target Platform(s) *'),
                          _buildPlatformList(),
                        ],
                      ),
                      const SizedBox(height: 14),

                      // Section 3: Category, Priority, Date
                      _SectionCard(
                        icon: Icons.tune_rounded,
                        title: 'Details',
                        children: [
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    _fieldLabel('Category *'),
                                    _buildDropdown(
                                      value: _selectedCategory,
                                      hint: 'Select',
                                      items: _categories,
                                      onChanged: (val) => setState(
                                        () => _selectedCategory = val,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    _fieldLabel('Priority *'),
                                    _buildDropdown(
                                      value: _selectedPriority,
                                      hint: 'Select',
                                      items: _priorities,
                                      onChanged: (val) => setState(
                                        () => _selectedPriority = val,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          if (_selectedCategory != null) ...[
                            const SizedBox(height: 10),
                            _buildSlaHint(),
                          ],
                          const SizedBox(height: 16),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              _fieldLabel('Preferred Posting Date *'),
                              GestureDetector(
                                onTap: () => SlaGuidelinesSheet.show(context, isReferenceMode: true),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    Icon(
                                      Icons.info_outline_rounded,
                                      size: 14,
                                      color: isDark ? const Color(0xFFFFD200) : const Color(0xFF2B5CE6),
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      'SLA Guide',
                                      style: TextStyle(
                                        fontFamily: 'DM Sans',
                                        fontSize: 12,
                                        fontWeight: FontWeight.w700,
                                        color: isDark ? const Color(0xFFFFD200) : const Color(0xFF2B5CE6),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          _buildDatePicker(),
                          _buildDateVolumePanel(),
                        ],
                      ),
                      const SizedBox(height: 14),

                      // Section 4: Media
                      _SectionCard(
                        icon: Icons.perm_media_rounded,
                        title: 'Media Upload',
                        children: [_buildMediaUpload()],
                      ),
                      const SizedBox(height: 14),

                      // Section 5: Caption
                      _SectionCard(
                        icon: Icons.auto_awesome_rounded,
                        title: 'Caption',
                        accent: const Color(0xFF7C3AED),
                        children: [
                          _buildAICaption(),
                          const SizedBox(height: 14),
                          _fieldLabel('Caption'),
                          _textAreaField(
                            _captionController,
                            rows: 4,
                            onChanged: (val) =>
                                setState(() => _captionLength = val.length),
                          ),
                          const SizedBox(height: 5),
                          Align(
                            alignment: Alignment.centerRight,
                            child: Text(
                              '$_captionLength characters',
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontSize: 10.5,
                                color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF9AA3B2),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),

                      // ── Auto-appended text info ─────────────────────────
                      Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: isDark ? const Color(0xFF181528) : const Color(0xFFF5F3FF),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: const Color(0xFF7C3AED).withOpacity(isDark ? 0.35 : 0.18),
                          ),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Icon(
                                  Icons.auto_fix_high_rounded,
                                  size: 14,
                                  color: const Color(0xFF7C3AED).withOpacity(isDark ? 0.9 : 0.7),
                                ),
                                const SizedBox(width: 6),
                                Text(
                                  'AUTO-ADDED WHEN YOU SUBMIT',
                                  style: TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontWeight: FontWeight.w800,
                                    fontSize: 9.5,
                                    color: const Color(0xFF7C3AED).withOpacity(isDark ? 0.9 : 0.6),
                                    letterSpacing: 0.8,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 10),
                            Text(
                              'Apply now and secure your place for the upcoming academic year: https://onlineapp.nu-lipa.edu.ph/quest/register.php',
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontSize: 12.5,
                                color: isDark ? const Color(0xFFCBD5E1) : const Color(0xFF3D4A63),
                                height: 1.5,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Experience 𝘌𝘥𝘶𝘤𝘢𝘵𝘪𝘰𝘯 𝘛𝘩𝘢𝘵 𝘞𝘰𝘳𝘬𝘴.',
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontSize: 12.5,
                                fontStyle: FontStyle.italic,
                                color: isDark ? const Color(0xFFCBD5E1) : const Color(0xFF3D4A63),
                                height: 1.5,
                              ),
                            ),
                            const Text(
                              '#NULipa #EducationThatWorks',
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontSize: 12.5,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFF7C3AED),
                                height: 1.5,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 20),

                      // ── Submit button (scrolls with content) ────────────
                      _buildStickySubmit(),
                      const SizedBox(height: 100),
                    ],
                  ),
                ),
              ),
            ],
          ),
            ),
          ),

          // ── Success overlay ──────────────────────────────────────────────
          if (_submitted)
            Positioned.fill(
              child: Container(
                color: Colors.black.withOpacity(0.55),
                child: Center(
                  child: FadeTransition(
                    opacity: _successFade,
                    child: ScaleTransition(
                      scale: _successScale,
                      child: Container(
                        width: 160,
                        height: 160,
                        decoration: BoxDecoration(
                          color: isDark ? const Color(0xFF131D31) : Colors.white,
                          borderRadius: BorderRadius.circular(32),
                          border: isDark
                              ? Border.all(color: const Color(0xFF1E2B45))
                              : null,
                          boxShadow: const [
                            BoxShadow(
                              color: Color(0x40000000),
                              blurRadius: 40,
                              offset: Offset(0, 16),
                            ),
                          ],
                        ),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Container(
                              width: 60,
                              height: 60,
                              decoration: const BoxDecoration(
                                gradient: LinearGradient(
                                  colors: [
                                    Color(0xFF34D399),
                                    Color(0xFF05C46B),
                                  ],
                                ),
                                shape: BoxShape.circle,
                                boxShadow: [
                                  BoxShadow(
                                    color: Color(0x4005C46B),
                                    blurRadius: 16,
                                    offset: Offset(0, 6),
                                  ),
                                ],
                              ),
                              child: const Icon(
                                Icons.check_rounded,
                                color: Colors.white,
                                size: 30,
                              ),
                            ),
                            const SizedBox(height: 14),
                            Text(
                              'Submitted!',
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontWeight: FontWeight.w900,
                                fontSize: 16,
                                color: isDark ? Colors.white : const Color(0xFF080F1E),
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              'Request sent',
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontSize: 12,
                                color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF9AA3B2),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ),

        ],
      ),
    );
  }

  // ── Progress bar ──────────────────────────────────────────────────────────
  Widget _buildProgress() {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    const total = 4;
    final done = _completedSteps;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 4),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                done == total
                    ? 'All fields complete ✓'
                    : '$done of $total sections complete',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w700,
                  fontSize: 11.5,
                  color: done == total
                      ? const Color(0xFF05C46B)
                      : (isDark ? const Color(0xFF8E9BAE) : const Color(0xFF9AA3B2)),
                ),
              ),
              Text(
                '${(done / total * 100).toInt()}%',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w800,
                  fontSize: 11.5,
                  color: isDark ? const Color(0xFFFFD200) : const Color(0xFF002366),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          ClipRRect(
            borderRadius: BorderRadius.circular(99),
            child: TweenAnimationBuilder<double>(
              tween: Tween(begin: 0, end: done / total),
              duration: const Duration(milliseconds: 500),
              curve: Curves.easeOutCubic,
              builder: (_, val, __) => Stack(
                children: [
                  Container(
                    height: 5,
                    color: isDark ? const Color(0xFF1E2B45) : Colors.white,
                  ),
                  FractionallySizedBox(
                    widthFactor: val,
                    child: Container(
                      height: 5,
                      decoration: BoxDecoration(
                        gradient: isDark
                            ? const LinearGradient(
                                colors: [Color(0xFFE5A000), Color(0xFFFFD200)],
                              )
                            : const LinearGradient(
                                colors: [Color(0xFF001540), Color(0xFF2B5CE6)],
                              ),
                        borderRadius: BorderRadius.circular(99),
                      ),
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

  // ── Header ────────────────────────────────────────────────────────────────
  Widget _buildHeader() {
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
        boxShadow: isDark
            ? []
            : const [
                BoxShadow(
                  color: Color(0x07001540),
                  blurRadius: 12,
                  offset: Offset(0, 1),
                ),
              ],
      ),
      padding: EdgeInsets.fromLTRB(
        22,
        MediaQuery.of(context).padding.top + 18,
        22,
        16,
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  widget.isEditing ? 'Edit Request' : 'Create Request',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w900,
                    fontSize: 24,
                    color: isDark ? Colors.white : const Color(0xFF002366),
                    letterSpacing: -0.5,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  widget.isEditing
                      ? 'Update your social media request'
                      : 'Submit a new social media post',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 13,
                    color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF9AA3B2),
                  ),
                ),
              ],
            ),
          ),
          // Step dots
          Row(
            children: List.generate(
              4,
              (i) => AnimatedContainer(
                duration: const Duration(milliseconds: 300),
                margin: const EdgeInsets.only(left: 5),
                width: i < _completedSteps ? 18 : 6,
                height: 6,
                decoration: BoxDecoration(
                  color: i < _completedSteps
                      ? (isDark ? const Color(0xFFFFD200) : const Color(0xFF002366))
                      : (isDark ? const Color(0xFF1E2B45) : const Color(0xFFE9EDF6)),
                  borderRadius: BorderRadius.circular(99),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Field label ───────────────────────────────────────────────────────────
  Widget _fieldLabel(String text) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Text(
        text,
        style: TextStyle(
          fontFamily: 'DM Sans',
          fontWeight: FontWeight.w800,
          fontSize: 11,
          color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF9AA3B2),
          letterSpacing: 0.8,
        ),
      ),
    );
  }

  // ── Text fields ───────────────────────────────────────────────────────────
  Widget _textField(TextEditingController ctrl, {String? hint}) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return TextField(
      controller: ctrl,
      style: TextStyle(
        fontFamily: 'DM Sans',
        fontSize: 14,
        color: isDark ? Colors.white : const Color(0xFF080F1E),
      ),
      decoration: _inputDeco(hint: hint),
    );
  }

  Widget _textAreaField(
    TextEditingController ctrl, {
    int rows = 4,
    ValueChanged<String>? onChanged,
  }) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return SizedBox(
      height: rows * 22.0 + 28,
      child: TextField(
        controller: ctrl,
        maxLines: null,
        expands: true,
        textAlignVertical: TextAlignVertical.top,
        onChanged: onChanged,
        style: TextStyle(
          fontFamily: 'DM Sans',
          fontSize: 14,
          color: isDark ? Colors.white : const Color(0xFF080F1E),
        ),
        decoration: _inputDeco(),
      ),
    );
  }

  InputDecoration _inputDeco({String? hint}) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return InputDecoration(
      hintText: hint,
      hintStyle: TextStyle(
        fontFamily: 'DM Sans',
        fontSize: 13.5,
        color: isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2),
      ),
      filled: true,
      fillColor: isDark ? const Color(0xFF0D1527) : const Color(0xFFF1F4FB),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: isDark ? const BorderSide(color: Color(0xFF1E2B45)) : BorderSide.none,
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: isDark ? const BorderSide(color: Color(0xFF1E2B45)) : BorderSide.none,
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: BorderSide(
          color: isDark ? const Color(0xFFFFD200) : const Color(0xFF2B5CE6),
          width: 1.5,
        ),
      ),
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
    );
  }

  // ── Platform list ─────────────────────────────────────────────────────────
  Widget _buildPlatformList() {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final entries = _platforms.entries.toList();
    return Container(
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF0D1527) : const Color(0xFFF8FAFE),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isDark ? const Color(0xFF1E2B45) : const Color(0x0E000000),
        ),
      ),
      child: Column(
        children: entries.asMap().entries.map((e) {
          final i = e.key;
          final platform = e.value.key;
          final isChecked = e.value.value;
          return Column(
            children: [
              InkWell(
                borderRadius: i == 0
                    ? const BorderRadius.vertical(top: Radius.circular(16))
                    : i == entries.length - 1
                    ? const BorderRadius.vertical(bottom: Radius.circular(16))
                    : BorderRadius.zero,
                onTap: () => setState(() => _platforms[platform] = !isChecked),
                child: Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 12,
                  ),
                  child: Row(
                    children: [
                      AnimatedContainer(
                        duration: const Duration(milliseconds: 180),
                        width: 20,
                        height: 20,
                        decoration: BoxDecoration(
                          color: isChecked
                              ? (isDark ? const Color(0xFFFFD200) : const Color(0xFF002366))
                              : Colors.transparent,
                          border: Border.all(
                            color: isChecked
                                ? (isDark ? const Color(0xFFFFD200) : const Color(0xFF002366))
                                : (isDark ? const Color(0xFF334155) : const Color(0xFFCDD1DB)),
                            width: 1.5,
                          ),
                          borderRadius: BorderRadius.circular(7),
                        ),
                        child: isChecked
                            ? Icon(
                                Icons.check_rounded,
                                size: 13,
                                color: isDark ? const Color(0xFF0A0F1D) : Colors.white,
                              )
                            : null,
                      ),
                      const SizedBox(width: 12),
                      Container(
                        width: 32,
                        height: 32,
                        decoration: BoxDecoration(
                          color: isChecked
                              ? (isDark
                                  ? const Color(0xFFFFD200).withOpacity(0.18)
                                  : const Color(0xFF002366).withOpacity(0.1))
                              : (isDark
                                  ? const Color(0xFF1E2B45)
                                  : const Color(0xFF9AA3B2).withOpacity(0.08)),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Icon(
                          _platformIcons[platform] ?? Icons.public_rounded,
                          size: 16,
                          color: isChecked
                              ? (isDark ? const Color(0xFFFFD200) : const Color(0xFF002366))
                              : (isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2)),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Text(
                        platform,
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: isChecked
                              ? FontWeight.w700
                              : FontWeight.w500,
                          fontSize: 14.5,
                          color: isChecked
                              ? (isDark ? Colors.white : const Color(0xFF080F1E))
                              : (isDark ? const Color(0xFF8E9BAE) : const Color(0xFF3D4A63)),
                        ),
                      ),
                      const Spacer(),
                      if (isChecked)
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 3,
                          ),
                          decoration: BoxDecoration(
                            color: isDark
                                ? const Color(0xFFFFD200).withOpacity(0.15)
                                : const Color(0xFF2B5CE6).withOpacity(0.1),
                            borderRadius: BorderRadius.circular(99),
                          ),
                          child: Text(
                            'Selected',
                            style: TextStyle(
                              fontFamily: 'DM Sans',
                              fontWeight: FontWeight.w700,
                              fontSize: 10,
                              color: isDark ? const Color(0xFFFFD200) : const Color(0xFF2B5CE6),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ),
              if (i < entries.length - 1)
                Container(
                  height: 1,
                  margin: const EdgeInsets.symmetric(horizontal: 14),
                  color: isDark ? const Color(0xFF1E2B45) : const Color(0x08000000),
                ),
            ],
          );
        }).toList(),
      ),
    );
  }

  // ── Dropdown ──────────────────────────────────────────────────────────────
  Widget _buildDropdown({
    required String? value,
    required String hint,
    required List<String> items,
    required ValueChanged<String?> onChanged,
  }) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Container(
      height: 46,
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF0D1527) : const Color(0xFFF1F4FB),
        borderRadius: BorderRadius.circular(14),
        border: isDark ? Border.all(color: const Color(0xFF1E2B45)) : null,
      ),
      padding: const EdgeInsets.symmetric(horizontal: 14),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String>(
          value: value,
          dropdownColor: isDark ? const Color(0xFF131D31) : Colors.white,
          hint: Text(
            hint,
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontWeight: FontWeight.w500,
              fontSize: 13.5,
              color: isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2),
            ),
          ),
          icon: Icon(
            Icons.keyboard_arrow_down_rounded,
            size: 18,
            color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF9AA3B2),
          ),
          isExpanded: true,
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontWeight: FontWeight.w600,
            fontSize: 13.5,
            color: isDark ? Colors.white : const Color(0xFF080F1E),
          ),
          items: items
              .map((e) => DropdownMenuItem(
                    value: e,
                    child: Text(
                      e,
                      style: TextStyle(
                        color: isDark ? Colors.white : const Color(0xFF080F1E),
                      ),
                    ),
                  ))
              .toList(),
          onChanged: onChanged,
        ),
      ),
    );
  }

  // ── Date picker ───────────────────────────────────────────────────────────
  Widget _buildDatePicker() {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return GestureDetector(
      onTap: _pickDate,
      child: Container(
        height: 48,
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF0D1527) : const Color(0xFFF1F4FB),
          borderRadius: BorderRadius.circular(14),
          border: isDark ? Border.all(color: const Color(0xFF1E2B45)) : null,
        ),
        padding: const EdgeInsets.symmetric(horizontal: 16),
        child: Row(
          children: [
            Expanded(
              child: Text(
                _selectedDate == null
                    ? 'Select a date…'
                    : '${_selectedDate!.month}/${_selectedDate!.day}/${_selectedDate!.year}',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontSize: 14,
                  color: _selectedDate == null
                      ? (isDark ? const Color(0xFF64748B) : const Color(0xFF9AA3B2))
                      : (isDark ? Colors.white : const Color(0xFF080F1E)),
                  fontWeight: _selectedDate == null
                      ? FontWeight.w400
                      : FontWeight.w600,
                ),
              ),
            ),
            Icon(
              Icons.calendar_month_rounded,
              size: 20,
              color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF9AA3B2),
            ),
          ],
        ),
      ),
    );
  }

  // ── Date volume panel ─────────────────────────────────────────────────────
  Widget _buildDateVolumePanel() {
    if (_selectedDate == null) return const SizedBox();
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return AnimatedSize(
      duration: const Duration(milliseconds: 280),
      curve: Curves.easeOutCubic,
      child: Container(
        margin: const EdgeInsets.only(top: 10),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF131D31) : Colors.white,
          border: Border.all(
            color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFE4E8F0),
            width: 1.5,
          ),
          borderRadius: BorderRadius.circular(16),
          boxShadow: isDark
              ? []
              : const [
                  BoxShadow(
                    color: Color(0x10000000),
                    blurRadius: 14,
                    offset: Offset(0, 4),
                  ),
                ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Header
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 11),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF0D1527) : const Color(0xFFF8FAFE),
                border: Border(
                  bottom: BorderSide(
                    color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFF0F2F8),
                  ),
                ),
                borderRadius: const BorderRadius.vertical(top: Radius.circular(14.5)),
              ),
              child: Row(
                children: [
                  Icon(
                    Icons.calendar_today_rounded,
                    size: 14,
                    color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF9AA3B2),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    '${_selectedDate!.month}/${_selectedDate!.day}/${_selectedDate!.year}',
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      fontSize: 12.5,
                      fontWeight: FontWeight.w700,
                      color: isDark ? Colors.white : const Color(0xFF080F1E),
                    ),
                  ),
                  const Spacer(),
                  if (_isLoadingDateData)
                    SizedBox(
                      width: 14,
                      height: 14,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: isDark ? const Color(0xFFFFD200) : const Color(0xFF2B5CE6),
                      ),
                    )
                  else
                    Text(
                      _datePostCount == 0
                          ? 'Free day'
                          : '$_datePostCount request${_datePostCount == 1 ? "" : "s"}',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontSize: 11,
                        color: _capacityColor(_datePostCount),
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                ],
              ),
            ),

            // Body
            Padding(
              padding: const EdgeInsets.all(14),
              child: _isLoadingDateData
                  ? const SizedBox(height: 48)
                  : Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Capacity row
                        Row(
                          children: [
                            // Capacity label pill
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 10,
                                vertical: 4,
                              ),
                              decoration: BoxDecoration(
                                color: _capacityColor(
                                  _datePostCount,
                                ).withOpacity(0.1),
                                border: Border.all(
                                  color: _capacityColor(
                                    _datePostCount,
                                  ).withOpacity(0.25),
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
                                      color: _capacityColor(_datePostCount),
                                    ),
                                  ),
                                  const SizedBox(width: 5),
                                  Text(
                                    _capacityLabel(_datePostCount),
                                    style: TextStyle(
                                      fontFamily: 'DM Sans',
                                      fontWeight: FontWeight.w800,
                                      fontSize: 11,
                                      color: _capacityColor(_datePostCount),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const Spacer(),
                            Text(
                              'Schedule load',
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontSize: 11,
                                color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF9AA3B2),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),

                        // Capacity bar with LayoutBuilder
                        LayoutBuilder(
                          builder: (_, constraints) {
                            final pct =
                                (_datePostCount * 20).clamp(0, 100) / 100.0;
                            return ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: Stack(
                                children: [
                                  Container(
                                    height: 8,
                                    color: isDark ? const Color(0xFF0D1527) : const Color(0xFFF1F4FB),
                                  ),
                                  TweenAnimationBuilder<double>(
                                    tween: Tween(begin: 0, end: pct),
                                    duration: const Duration(milliseconds: 600),
                                    curve: Curves.easeOutCubic,
                                    builder: (_, val, __) => Container(
                                      height: 8,
                                      width: constraints.maxWidth * val,
                                      decoration: BoxDecoration(
                                        gradient: _capacityGradient(
                                          _datePostCount,
                                        ),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          },
                        ),

                        // Existing posts list
                        if (_dateUpcomingPosts.isNotEmpty) ...[
                          const SizedBox(height: 12),
                          ..._dateUpcomingPosts.take(5).map((post) {
                            final status =
                                post['status']?.toString() ?? 'Pending';
                            final title =
                                post['title']?.toString() ??
                                post['platform']?.toString() ??
                                'Untitled';
                            return Container(
                              margin: const EdgeInsets.only(bottom: 7),
                              padding: const EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 9,
                              ),
                              decoration: BoxDecoration(
                                color: isDark ? const Color(0xFF0D1527) : const Color(0xFFF8FAFE),
                                border: Border.all(
                                  color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFF0F2F8),
                                ),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: Row(
                                children: [
                                  Container(
                                    width: 7,
                                    height: 7,
                                    decoration: BoxDecoration(
                                      shape: BoxShape.circle,
                                      color: _statusColor(status),
                                    ),
                                  ),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Text(
                                      title,
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: TextStyle(
                                        fontFamily: 'DM Sans',
                                        fontWeight: FontWeight.w600,
                                        fontSize: 12.5,
                                        color: isDark ? Colors.white : const Color(0xFF3D4A63),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    status,
                                    style: TextStyle(
                                      fontFamily: 'DM Sans',
                                      fontSize: 10.5,
                                      color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF9AA3B2),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          }),
                        ] else ...[
                          const SizedBox(height: 14),
                          Center(
                            child: Column(
                              children: [
                                const Icon(
                                  Icons.event_available_rounded,
                                  size: 28,
                                  color: Color(0xFF05C46B),
                                ),
                                const SizedBox(height: 6),
                                Text(
                                  'No scheduled posts on this date',
                                  style: TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontSize: 12.5,
                                    color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF9AA3B2),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ],
                    ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Media upload ──────────────────────────────────────────────────────────
  Widget _buildMediaUpload() {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return GestureDetector(
      onTap: _pickMedia,
      child: Container(
        width: double.infinity,
        constraints: const BoxConstraints(minHeight: 130),
        decoration: BoxDecoration(
          color: isDark ? const Color(0xFF0D1527) : const Color(0xFFF8FAFE),
          border: Border.all(
            color: isDark
                ? const Color(0xFFFFD200).withOpacity(0.35)
                : const Color(0xFF2B5CE6).withOpacity(0.2),
            width: 1.5,
          ),
          borderRadius: BorderRadius.circular(16),
        ),
        child: _mediaFiles.isEmpty
            ? Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      width: 46,
                      height: 46,
                      decoration: BoxDecoration(
                        color: isDark
                            ? const Color(0xFFFFD200).withOpacity(0.12)
                            : const Color(0xFF2B5CE6).withOpacity(0.1),
                        borderRadius: BorderRadius.circular(15),
                      ),
                      child: Icon(
                        Icons.upload_rounded,
                        size: 22,
                        color: isDark ? const Color(0xFFFFD200) : const Color(0xFF2B5CE6),
                      ),
                    ),
                    const SizedBox(height: 10),
                    Text(
                      'Tap to upload images or videos',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w600,
                        fontSize: 13.5,
                        color: isDark ? Colors.white : const Color(0xFF3D4A63),
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      'PNG, JPG, MP4 · max 10MB',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontSize: 11.5,
                        color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF9AA3B2),
                      ),
                    ),
                  ],
                ),
              )
            : Padding(
                padding: const EdgeInsets.all(14),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          width: 30,
                          height: 30,
                          decoration: BoxDecoration(
                            color: const Color(0xFF05C46B).withOpacity(0.15),
                            borderRadius: BorderRadius.circular(9),
                          ),
                          child: const Icon(
                            Icons.check_rounded,
                            size: 15,
                            color: Color(0xFF05C46B),
                          ),
                        ),
                        const SizedBox(width: 9),
                        Text(
                          '${_mediaFiles.length} file(s) selected',
                          style: TextStyle(
                            fontFamily: 'DM Sans',
                            fontWeight: FontWeight.w700,
                            fontSize: 13,
                            color: isDark ? Colors.white : const Color(0xFF080F1E),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    ..._mediaFiles.map(
                      (f) => Padding(
                        padding: const EdgeInsets.only(bottom: 4),
                        child: Row(
                          children: [
                            Icon(
                              Icons.attachment_rounded,
                              size: 13,
                              color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF9AA3B2),
                            ),
                            const SizedBox(width: 6),
                            Expanded(
                              child: Text(
                                f.name,
                                style: TextStyle(
                                  fontFamily: 'DM Sans',
                                  fontSize: 11.5,
                                  color: isDark ? const Color(0xFFCBD5E1) : const Color(0xFF3D4A63),
                                ),
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Tap to change files',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontSize: 11,
                        color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF9AA3B2),
                      ),
                    ),
                  ],
                ),
              ),
      ),
    );
  }

  // ── AI caption card ───────────────────────────────────────────────────────
  Widget _buildAICaption() {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Container(
      decoration: BoxDecoration(
        gradient: isDark
            ? const LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [Color(0xFF1E1735), Color(0xFF131D31)],
              )
            : const LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [Color(0xFFF8F4FF), Color(0xFFEFF4FF)],
              ),
        border: Border.all(
          color: const Color(0xFF8B5CF6).withOpacity(isDark ? 0.35 : 0.2),
        ),
        borderRadius: BorderRadius.circular(16),
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Let AI craft an engaging caption based on your post details.',
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontSize: 13,
              color: isDark ? const Color(0xFFCBD5E1) : const Color(0xFF3D4A63),
              height: 1.55,
            ),
          ),
          const SizedBox(height: 14),
          SizedBox(
            width: double.infinity,
            height: 44,
            child: DecoratedBox(
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF7C3AED), Color(0xFF2563EB)],
                ),
                borderRadius: BorderRadius.circular(13),
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x407C3AED),
                    blurRadius: 12,
                    offset: Offset(0, 4),
                  ),
                ],
              ),
              child: ElevatedButton(
                onPressed: _isGeneratingCaption ? null : _onGenerateCaption,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.transparent,
                  shadowColor: Colors.transparent,
                  disabledBackgroundColor: Colors.transparent,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(13),
                  ),
                ),
                child: _isGeneratingCaption
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                        ),
                      )
                    : const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.auto_awesome_rounded,
                            size: 16,
                            color: Colors.white,
                          ),
                          SizedBox(width: 8),
                          Text(
                            'Generate Caption',
                            style: TextStyle(
                              fontFamily: 'DM Sans',
                              fontWeight: FontWeight.w800,
                              fontSize: 14,
                              color: Colors.white,
                            ),
                          ),
                        ],
                      ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ── Sticky submit ─────────────────────────────────────────────────────────
  Widget _buildStickySubmit() {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 10),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF131D31) : Colors.white.withOpacity(0.97),
        border: Border(
          top: BorderSide(
            color: isDark ? const Color(0xFF1E2B45) : const Color(0x0E000000),
          ),
        ),
        boxShadow: isDark
            ? []
            : const [
                BoxShadow(
                  color: Color(0x12001540),
                  blurRadius: 20,
                  offset: Offset(0, -4),
                ),
              ],
      ),
      child: GestureDetector(
        onTapDown: (_) {
          if (!_isSubmitting) _submitScaleController.forward();
        },
        onTapUp: (_) {
          _submitScaleController.reverse();
          _onSubmit();
        },
        onTapCancel: () => _submitScaleController.reverse(),
        child: ScaleTransition(
          scale: Tween<double>(begin: 1.0, end: 0.97).animate(
            CurvedAnimation(
              parent: _submitScaleController,
              curve: Curves.easeOut,
            ),
          ),
          child: Container(
            width: double.infinity,
            height: 52,
            decoration: BoxDecoration(
              gradient: isDark
                  ? const LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [Color(0xFF002366), Color(0xFF1243B0)],
                    )
                  : const LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [Color(0xFF001540), Color(0xFF003080)],
                    ),
              borderRadius: BorderRadius.circular(16),
              border: isDark
                  ? Border.all(color: const Color(0xFFFFD200).withOpacity(0.35))
                  : null,
              boxShadow: [
                BoxShadow(
                  color: isDark
                      ? const Color(0xFF002366).withOpacity(0.4)
                      : const Color(0x40001540),
                  blurRadius: 18,
                  offset: const Offset(0, 6),
                ),
              ],
            ),
            child: ElevatedButton(
              onPressed: _isSubmitting ? null : _onSubmit,
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.transparent,
                shadowColor: Colors.transparent,
                disabledBackgroundColor: Colors.transparent,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
              ),
              child: _isSubmitting
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(
                        strokeWidth: 2.5,
                        valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                      ),
                    )
                  : Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(
                          Icons.send_rounded,
                          color: Colors.white,
                          size: 16,
                        ),
                        const SizedBox(width: 9),
                        Text(
                          widget.isEditing ? 'Update & Resubmit' : 'Submit Request',
                          style: const TextStyle(
                            fontFamily: 'DM Sans',
                            fontWeight: FontWeight.w900,
                            fontSize: 15.5,
                            color: Colors.white,
                            letterSpacing: 0.1,
                          ),
                        ),
                        const SizedBox(width: 9),
                        // Progress dots in button
                        Row(
                          children: List.generate(
                            4,
                            (i) => AnimatedContainer(
                              duration: const Duration(milliseconds: 300),
                              margin: const EdgeInsets.only(left: 3),
                              width: 5,
                              height: 5,
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                color: i < _completedSteps
                                    ? Colors.white
                                    : Colors.white.withOpacity(0.25),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
            ),
          ),
        ),
      ),
    );
  }
}

// ── Section card wrapper ──────────────────────────────────────────────────────
class _SectionCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final List<Widget> children;
  final Color? accent;

  const _SectionCard({
    required this.icon,
    required this.title,
    required this.children,
    this.accent,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final color = accent ?? (isDark ? const Color(0xFFFFD200) : const Color(0xFF002366));
    return Container(
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF131D31) : Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isDark ? const Color(0xFF1E2B45) : const Color(0x0E000000),
        ),
        boxShadow: isDark
            ? const [
                BoxShadow(
                  color: Color(0x20000000),
                  blurRadius: 10,
                  offset: Offset(0, 4),
                ),
              ]
            : const [
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
          // Card header
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 12),
            child: Row(
              children: [
                Container(
                  width: 32,
                  height: 32,
                  decoration: BoxDecoration(
                    color: color.withOpacity(isDark ? 0.18 : 0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(icon, size: 17, color: color),
                ),
                const SizedBox(width: 10),
                Text(
                  title,
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w800,
                    fontSize: 14,
                    color: color,
                    letterSpacing: -0.1,
                  ),
                ),
              ],
            ),
          ),
          // Thin divider
          Container(
            height: 1,
            color: isDark ? const Color(0xFF1E2B45) : const Color(0x08000000),
          ),
          // Content
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: children,
            ),
          ),
        ],
      ),
    );
  }
}
