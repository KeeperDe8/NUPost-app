import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import '../app_bottom_nav.dart';
import '../services/api_service.dart';
import '../services/session_store.dart';
import '../widgets/floating_message_button.dart';

class CreateRequestScreen extends StatefulWidget {
  const CreateRequestScreen({super.key});

  @override
  State<CreateRequestScreen> createState() => _CreateRequestScreenState();
}

class _CreateRequestScreenState extends State<CreateRequestScreen>
    with TickerProviderStateMixin {
  // ── Controllers ────────────────────────────────────────────────────────────
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
  final int _currentNavIndex = 2;
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
  }

  @override
  void dispose() {
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
        if (data['my_requests'] is List) allPosts.addAll(data['my_requests']);
        if (data['public_calendar'] is List) {
          allPosts.addAll(data['public_calendar']);
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
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate ?? DateTime.now().add(const Duration(days: 1)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (context, child) => Theme(
        data: Theme.of(context).copyWith(
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
      _showSnack('Enter title and description before generating caption.');
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
      _showSnack('AI unavailable. Used smart fallback caption.');
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
      _showSnack('Only first 4 files were selected.');
    }
  }

  Future<void> _onSubmit() async {
    if (_isSubmitting) return;
    final userId = SessionStore.userId;
    if (userId == null) {
      _showSnack('Please login first.');
      return;
    }
    final platforms = _platforms.entries
        .where((e) => e.value)
        .map((e) => e.key)
        .toList();

    if (_titleController.text.trim().isEmpty ||
        _descriptionController.text.trim().isEmpty ||
        _selectedCategory == null ||
        _selectedPriority == null ||
        _selectedDate == null) {
      _showSnack('Please fill all required fields.');
      return;
    }

    setState(() => _isSubmitting = true);
    try {
      final preferredDate =
          '${_selectedDate!.year.toString().padLeft(4, '0')}-'
          '${_selectedDate!.month.toString().padLeft(2, '0')}-'
          '${_selectedDate!.day.toString().padLeft(2, '0')}';

      await ApiService.createRequest(
        userId: userId,
        title: _titleController.text.trim(),
        description: _descriptionController.text.trim(),
        category: _selectedCategory!,
        priority: _selectedPriority!,
        platforms: platforms,
        preferredDate: preferredDate,
        caption: _captionController.text.trim(),
        mediaFiles: _mediaFiles,
      );
      if (!mounted) return;

      // Show success overlay
      setState(() {
        _submitted = true;
      });
      _successController.forward();

      await Future.delayed(const Duration(milliseconds: 1800));
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
      _showSnack('Request submitted successfully.');
    } catch (e) {
      if (!mounted) return;
      _showSnack(
        'Submit failed: ${e.toString().replaceFirst('Exception: ', '')}',
      );
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  void _showSnack(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          msg,
          style: const TextStyle(
            fontFamily: 'DM Sans',
            fontWeight: FontWeight.w600,
          ),
        ),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        margin: const EdgeInsets.fromLTRB(16, 0, 16, 16),
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
    return Scaffold(
      backgroundColor: const Color(0xFFE9EDF6),
      body: Stack(
        children: [
          Column(
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
                          const SizedBox(height: 16),
                          _fieldLabel('Preferred Posting Date *'),
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
                              style: const TextStyle(
                                fontFamily: 'DM Sans',
                                fontSize: 10.5,
                                color: Color(0xFF9AA3B2),
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
                          color: const Color(0xFFF5F3FF),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: const Color(0xFF7C3AED).withOpacity(0.18),
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
                                  color: const Color(0xFF7C3AED).withOpacity(0.7),
                                ),
                                const SizedBox(width: 6),
                                Text(
                                  'AUTO-ADDED WHEN YOU SUBMIT',
                                  style: TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontWeight: FontWeight.w800,
                                    fontSize: 9.5,
                                    color: const Color(0xFF7C3AED).withOpacity(0.6),
                                    letterSpacing: 0.8,
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 10),
                            const Text(
                              'Apply now and secure your place for the upcoming academic year: https://onlineapp.nu-lipa.edu.ph/quest/register.php',
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontSize: 12.5,
                                color: Color(0xFF3D4A63),
                                height: 1.5,
                              ),
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'Experience Education That Works.',
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontSize: 12.5,
                                fontStyle: FontStyle.italic,
                                color: Color(0xFF3D4A63),
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

          // ── Bottom nav bar ───────────────────────────────────────────────
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: AppBottomNav(currentIndex: _currentNavIndex),
          ),

          // ── Success overlay ──────────────────────────────────────────────
          if (_submitted)
            Positioned.fill(
              child: Container(
                color: Colors.black.withOpacity(0.35),
                child: Center(
                  child: FadeTransition(
                    opacity: _successFade,
                    child: ScaleTransition(
                      scale: _successScale,
                      child: Container(
                        width: 160,
                        height: 160,
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(32),
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
                              decoration: BoxDecoration(
                                gradient: const LinearGradient(
                                  colors: [
                                    Color(0xFF34D399),
                                    Color(0xFF05C46B),
                                  ],
                                ),
                                shape: BoxShape.circle,
                                boxShadow: const [
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
                            const Text(
                              'Submitted!',
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontWeight: FontWeight.w900,
                                fontSize: 16,
                                color: Color(0xFF080F1E),
                              ),
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'Request sent',
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontSize: 12,
                                color: Color(0xFF9AA3B2),
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

          const FloatingMessageButton(),
        ],
      ),
    );
  }

  // ── Progress bar ──────────────────────────────────────────────────────────
  Widget _buildProgress() {
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
                      : const Color(0xFF9AA3B2),
                ),
              ),
              Text(
                '${(done / total * 100).toInt()}%',
                style: const TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w800,
                  fontSize: 11.5,
                  color: Color(0xFF002366),
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
                  Container(height: 5, color: Colors.white),
                  FractionallySizedBox(
                    widthFactor: val,
                    child: Container(
                      height: 5,
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
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
              children: const [
                Text(
                  'Create Request',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: FontWeight.w900,
                    fontSize: 24,
                    color: Color(0xFF002366),
                    letterSpacing: -0.5,
                  ),
                ),
                SizedBox(height: 3),
                Text(
                  'Submit a new social media post',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 13,
                    color: Color(0xFF9AA3B2),
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
                      ? const Color(0xFF002366)
                      : const Color(0xFFE9EDF6),
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
  Widget _fieldLabel(String text) => Padding(
    padding: const EdgeInsets.only(bottom: 8),
    child: Text(
      text,
      style: const TextStyle(
        fontFamily: 'DM Sans',
        fontWeight: FontWeight.w800,
        fontSize: 11,
        color: Color(0xFF9AA3B2),
        letterSpacing: 0.8,
      ),
    ),
  );

  // ── Text fields ───────────────────────────────────────────────────────────
  Widget _textField(TextEditingController ctrl, {String? hint}) => TextField(
    controller: ctrl,
    style: const TextStyle(
      fontFamily: 'DM Sans',
      fontSize: 14,
      color: Color(0xFF080F1E),
    ),
    decoration: _inputDeco(hint: hint),
  );

  Widget _textAreaField(
    TextEditingController ctrl, {
    int rows = 4,
    ValueChanged<String>? onChanged,
  }) => SizedBox(
    height: rows * 22.0 + 28,
    child: TextField(
      controller: ctrl,
      maxLines: null,
      expands: true,
      textAlignVertical: TextAlignVertical.top,
      onChanged: onChanged,
      style: const TextStyle(
        fontFamily: 'DM Sans',
        fontSize: 14,
        color: Color(0xFF080F1E),
      ),
      decoration: _inputDeco(),
    ),
  );

  InputDecoration _inputDeco({String? hint}) => InputDecoration(
    hintText: hint,
    hintStyle: const TextStyle(
      fontFamily: 'DM Sans',
      fontSize: 13.5,
      color: Color(0xFF9AA3B2),
    ),
    filled: true,
    fillColor: const Color(0xFFF1F4FB),
    border: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: BorderSide.none,
    ),
    enabledBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: BorderSide.none,
    ),
    focusedBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: const BorderSide(color: Color(0xFF2B5CE6), width: 1.5),
    ),
    contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
  );

  // ── Platform list ─────────────────────────────────────────────────────────
  Widget _buildPlatformList() {
    final entries = _platforms.entries.toList();
    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFE),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0x0E000000)),
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
                              ? const Color(0xFF002366)
                              : Colors.transparent,
                          border: Border.all(
                            color: isChecked
                                ? const Color(0xFF002366)
                                : const Color(0xFFCDD1DB),
                            width: 1.5,
                          ),
                          borderRadius: BorderRadius.circular(7),
                        ),
                        child: isChecked
                            ? const Icon(
                                Icons.check_rounded,
                                size: 13,
                                color: Colors.white,
                              )
                            : null,
                      ),
                      const SizedBox(width: 12),
                      Container(
                        width: 32,
                        height: 32,
                        decoration: BoxDecoration(
                          color: isChecked
                              ? const Color(0xFF002366).withOpacity(0.1)
                              : const Color(0xFF9AA3B2).withOpacity(0.08),
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Icon(
                          _platformIcons[platform] ?? Icons.public_rounded,
                          size: 16,
                          color: isChecked
                              ? const Color(0xFF002366)
                              : const Color(0xFF9AA3B2),
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
                              ? const Color(0xFF080F1E)
                              : const Color(0xFF3D4A63),
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
                            color: const Color(0xFF2B5CE6).withOpacity(0.1),
                            borderRadius: BorderRadius.circular(99),
                          ),
                          child: const Text(
                            'Selected',
                            style: TextStyle(
                              fontFamily: 'DM Sans',
                              fontWeight: FontWeight.w700,
                              fontSize: 10,
                              color: Color(0xFF2B5CE6),
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
                  color: const Color(0x08000000),
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
  }) => Container(
    height: 46,
    decoration: BoxDecoration(
      color: const Color(0xFFF1F4FB),
      borderRadius: BorderRadius.circular(14),
    ),
    padding: const EdgeInsets.symmetric(horizontal: 14),
    child: DropdownButtonHideUnderline(
      child: DropdownButton<String>(
        value: value,
        hint: Text(
          hint,
          style: const TextStyle(
            fontFamily: 'DM Sans',
            fontWeight: FontWeight.w500,
            fontSize: 13.5,
            color: Color(0xFF9AA3B2),
          ),
        ),
        icon: const Icon(
          Icons.keyboard_arrow_down_rounded,
          size: 18,
          color: Color(0xFF9AA3B2),
        ),
        isExpanded: true,
        style: const TextStyle(
          fontFamily: 'DM Sans',
          fontWeight: FontWeight.w600,
          fontSize: 13.5,
          color: Color(0xFF080F1E),
        ),
        items: items
            .map((e) => DropdownMenuItem(value: e, child: Text(e)))
            .toList(),
        onChanged: onChanged,
      ),
    ),
  );

  // ── Date picker ───────────────────────────────────────────────────────────
  Widget _buildDatePicker() => GestureDetector(
    onTap: _pickDate,
    child: Container(
      height: 48,
      decoration: BoxDecoration(
        color: const Color(0xFFF1F4FB),
        borderRadius: BorderRadius.circular(14),
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
                    ? const Color(0xFF9AA3B2)
                    : const Color(0xFF080F1E),
                fontWeight: _selectedDate == null
                    ? FontWeight.w400
                    : FontWeight.w600,
              ),
            ),
          ),
          const Icon(
            Icons.calendar_month_rounded,
            size: 20,
            color: Color(0xFF9AA3B2),
          ),
        ],
      ),
    ),
  );

  // ── Date volume panel ─────────────────────────────────────────────────────
  Widget _buildDateVolumePanel() {
    if (_selectedDate == null) return const SizedBox();
    return AnimatedSize(
      duration: const Duration(milliseconds: 280),
      curve: Curves.easeOutCubic,
      child: Container(
        margin: const EdgeInsets.only(top: 10),
        decoration: BoxDecoration(
          color: Colors.white,
          border: Border.all(color: const Color(0xFFE4E8F0), width: 1.5),
          borderRadius: BorderRadius.circular(16),
          boxShadow: const [
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
              decoration: const BoxDecoration(
                color: Color(0xFFF8FAFE),
                border: Border(bottom: BorderSide(color: Color(0xFFF0F2F8))),
                borderRadius: BorderRadius.vertical(top: Radius.circular(14.5)),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.calendar_today_rounded,
                    size: 14,
                    color: Color(0xFF9AA3B2),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    '${_selectedDate!.month}/${_selectedDate!.day}/${_selectedDate!.year}',
                    style: const TextStyle(
                      fontFamily: 'DM Sans',
                      fontSize: 12.5,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF080F1E),
                    ),
                  ),
                  const Spacer(),
                  if (_isLoadingDateData)
                    const SizedBox(
                      width: 14,
                      height: 14,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Color(0xFF2B5CE6),
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
                              style: const TextStyle(
                                fontFamily: 'DM Sans',
                                fontSize: 11,
                                color: Color(0xFF9AA3B2),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),

                        // Capacity bar with LayoutBuilder (fixes MediaQuery hack)
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
                                    color: const Color(0xFFF1F4FB),
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
                                color: const Color(0xFFF8FAFE),
                                border: Border.all(
                                  color: const Color(0xFFF0F2F8),
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
                                      style: const TextStyle(
                                        fontFamily: 'DM Sans',
                                        fontWeight: FontWeight.w600,
                                        fontSize: 12.5,
                                        color: Color(0xFF3D4A63),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    status,
                                    style: const TextStyle(
                                      fontFamily: 'DM Sans',
                                      fontSize: 10.5,
                                      color: Color(0xFF9AA3B2),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          }),
                        ] else ...[
                          const SizedBox(height: 14),
                          const Center(
                            child: Column(
                              children: [
                                Icon(
                                  Icons.event_available_rounded,
                                  size: 28,
                                  color: Color(0xFF05C46B),
                                ),
                                SizedBox(height: 6),
                                Text(
                                  'No scheduled posts on this date',
                                  style: TextStyle(
                                    fontFamily: 'DM Sans',
                                    fontSize: 12.5,
                                    color: Color(0xFF9AA3B2),
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
  Widget _buildMediaUpload() => GestureDetector(
    onTap: _pickMedia,
    child: Container(
      width: double.infinity,
      constraints: const BoxConstraints(minHeight: 130),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFE),
        border: Border.all(
          color: const Color(0xFF2B5CE6).withOpacity(0.2),
          width: 2,
          style: BorderStyle.solid,
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
                      color: const Color(0xFF2B5CE6).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(15),
                    ),
                    child: const Icon(
                      Icons.upload_rounded,
                      size: 22,
                      color: Color(0xFF2B5CE6),
                    ),
                  ),
                  const SizedBox(height: 10),
                  const Text(
                    'Tap to upload images or videos',
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      fontWeight: FontWeight.w600,
                      fontSize: 13.5,
                      color: Color(0xFF3D4A63),
                    ),
                  ),
                  const SizedBox(height: 3),
                  const Text(
                    'PNG, JPG, MP4 · max 10MB',
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      fontSize: 11.5,
                      color: Color(0xFF9AA3B2),
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
                          color: const Color(0xFF05C46B).withOpacity(0.1),
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
                        style: const TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w700,
                          fontSize: 13,
                          color: Color(0xFF080F1E),
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
                          const Icon(
                            Icons.attachment_rounded,
                            size: 13,
                            color: Color(0xFF9AA3B2),
                          ),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              f.name,
                              style: const TextStyle(
                                fontFamily: 'DM Sans',
                                fontSize: 11.5,
                                color: Color(0xFF3D4A63),
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    'Tap to change files',
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      fontSize: 11,
                      color: Color(0xFF9AA3B2),
                    ),
                  ),
                ],
              ),
            ),
    ),
  );

  // ── AI caption card ───────────────────────────────────────────────────────
  Widget _buildAICaption() => Container(
    decoration: BoxDecoration(
      gradient: const LinearGradient(
        begin: Alignment.topLeft,
        end: Alignment.bottomRight,
        colors: [Color(0xFFF8F4FF), Color(0xFFEFF4FF)],
      ),
      border: Border.all(color: const Color(0xFF8B5CF6).withOpacity(0.2)),
      borderRadius: BorderRadius.circular(16),
    ),
    padding: const EdgeInsets.all(16),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Let AI craft an engaging caption based on your post details.',
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontSize: 13,
            color: Color(0xFF3D4A63),
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

  // ── Sticky submit ─────────────────────────────────────────────────────────
  Widget _buildStickySubmit() {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 10),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.97),
        border: const Border(top: BorderSide(color: Color(0x0E000000))),
        boxShadow: const [
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
              gradient: const LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [Color(0xFF001540), Color(0xFF003080)],
              ),
              borderRadius: BorderRadius.circular(16),
              boxShadow: const [
                BoxShadow(
                  color: Color(0x40001540),
                  blurRadius: 18,
                  offset: Offset(0, 6),
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
                        const Text(
                          'Submit Request',
                          style: TextStyle(
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
    final color = accent ?? const Color(0xFF002366);
    return Container(
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
          // Card header
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 12),
            child: Row(
              children: [
                Container(
                  width: 32,
                  height: 32,
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.1),
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
          Container(height: 1, color: const Color(0x08000000)),
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
