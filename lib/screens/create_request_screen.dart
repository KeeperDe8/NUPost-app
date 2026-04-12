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

class _CreateRequestScreenState extends State<CreateRequestScreen> {
  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _captionController = TextEditingController();

  // Platform checkboxes
  final Map<String, bool> _platforms = {
    'Facebook': false,
    'LinkedIn': false,
    'Youtube': false,
    'Tiktok': false,
  };

  String? _selectedCategory;
  String? _selectedPriority;
  DateTime? _selectedDate;
  int _captionLength = 0;
  final int _currentNavIndex = 2; // Create is active
  bool _isSubmitting = false;
  bool _isGeneratingCaption = false;
  List<PlatformFile> _mediaFiles = [];

  final List<String> _categories = [
    'Event',
    'Announcement',
    'News',
    'Achievement',
    'Promotion',
  ];

  final List<String> _priorities = ['Low', 'Medium', 'High', 'Urgent'];

  @override
  void dispose() {
    _titleController.dispose();
    _descriptionController.dispose();
    _captionController.dispose();
    super.dispose();
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now().add(const Duration(days: 1)),
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(primary: Color(0xFF003366)),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() => _selectedDate = picked);
    }
  }

  Future<void> _onGenerateCaption() async {
    if (_isGeneratingCaption) return;

    final title = _titleController.text.trim();
    final description = _descriptionController.text.trim();
    final category = _selectedCategory ?? 'General';
    final selectedPlatforms = _platforms.entries
        .where((e) => e.value)
        .map((e) => e.key)
        .toList();

    if (title.isEmpty || description.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Enter title and description before generating caption.',
          ),
        ),
      );
      return;
    }

    setState(() => _isGeneratingCaption = true);
    try {
      final caption = await ApiService.generateCaption(
        title: title,
        description: description,
        category: category,
        platforms: selectedPlatforms,
      );
      if (!mounted) return;
      setState(() {
        _captionController.text = caption;
        _captionLength = caption.length;
      });
    } catch (e) {
      if (!mounted) return;
      final msg = e.toString().replaceFirst('Exception: ', '');
      final fallbackCaption = _buildFallbackCaption(
        title: title,
        category: category,
        platforms: selectedPlatforms,
      );
      setState(() {
        _captionController.text = fallbackCaption;
        _captionLength = fallbackCaption.length;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('AI unavailable ($msg). Used smart fallback caption.'),
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _isGeneratingCaption = false);
      }
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
        .where((f) => (f.path != null) || (f.bytes != null))
        .toList();
    if (picked.isEmpty) return;

    setState(() {
      _mediaFiles = picked.take(4).toList();
    });

    if (picked.length > 4 && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Only first 4 files were selected.')),
      );
    }
  }

  Future<void> _onSubmit() async {
    if (_isSubmitting) return;

    final userId = SessionStore.userId;
    if (userId == null) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(const SnackBar(content: Text('Please login first.')));
      return;
    }

    final selectedPlatforms = _platforms.entries
        .where((e) => e.value)
        .map((e) => e.key)
        .toList();

    if (_titleController.text.trim().isEmpty ||
        _descriptionController.text.trim().isEmpty ||
        _selectedCategory == null ||
        _selectedPriority == null ||
        _selectedDate == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please fill all required fields.')),
      );
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
        platforms: selectedPlatforms,
        preferredDate: preferredDate,
        caption: _captionController.text.trim(),
        mediaFiles: _mediaFiles,
      );

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Request submitted successfully.')),
      );

      setState(() {
        _titleController.clear();
        _descriptionController.clear();
        _captionController.clear();
        _selectedCategory = null;
        _selectedPriority = null;
        _selectedDate = null;
        _captionLength = 0;
        _mediaFiles = [];
        for (final key in _platforms.keys) {
          _platforms[key] = false;
        }
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Submit failed: ${e.toString().replaceFirst('Exception: ', '')}',
          ),
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _isSubmitting = false);
      }
    }
  }

  // ── Reusable field label ───────────────────────────────
  Widget _label(String text) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Text(
        text,
        style: const TextStyle(
          fontFamily: 'Inter',
          fontWeight: FontWeight.w500,
          fontSize: 14.8,
          color: Color(0xFF0A0A0A),
        ),
      ),
    );
  }

  // ── Reusable input decoration ──────────────────────────
  InputDecoration _inputDeco({String? hint}) {
    return InputDecoration(
      hintText: hint,
      hintStyle: const TextStyle(
        fontFamily: 'Inter',
        fontSize: 13,
        color: Color(0xFF99A1AF),
      ),
      filled: true,
      fillColor: const Color(0xFFF3F3F5),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: BorderSide.none,
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: BorderSide.none,
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: const BorderSide(color: Color(0xFF003366), width: 1.5),
      ),
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
    );
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

              // ── Scrollable form ──────────────────────────
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(16, 20, 16, 100),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Post/Event Title *
                      _label('Post/Event Title *'),
                      SizedBox(
                        height: 48,
                        child: TextField(
                          controller: _titleController,
                          style: const TextStyle(
                            fontFamily: 'Inter',
                            fontSize: 13,
                          ),
                          decoration: _inputDeco(),
                        ),
                      ),

                      const SizedBox(height: 20),

                      // Description *
                      _label('Description *'),
                      SizedBox(
                        height: 161,
                        child: TextField(
                          controller: _descriptionController,
                          maxLines: null,
                          expands: true,
                          textAlignVertical: TextAlignVertical.top,
                          style: const TextStyle(
                            fontFamily: 'Inter',
                            fontSize: 13,
                          ),
                          decoration: _inputDeco(),
                        ),
                      ),

                      const SizedBox(height: 20),

                      // Target Platform(s) *
                      _label('Target Platform(s) *'),
                      Container(
                        decoration: BoxDecoration(
                          color: Colors.white,
                          border: Border.all(
                            color: Colors.black.withOpacity(0.1),
                          ),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        padding: const EdgeInsets.symmetric(
                          vertical: 8,
                          horizontal: 4,
                        ),
                        child: Column(
                          children: _platforms.keys.map((platform) {
                            return _PlatformCheckbox(
                              label: platform,
                              value: _platforms[platform]!,
                              onChanged: (val) => setState(
                                () => _platforms[platform] = val ?? false,
                              ),
                            );
                          }).toList(),
                        ),
                      ),

                      const SizedBox(height: 20),

                      // Category * and Priority * (side by side)
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Category
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                _label('Category *'),
                                _buildDropdown(
                                  value: _selectedCategory,
                                  hint: 'Select',
                                  items: _categories,
                                  onChanged: (val) =>
                                      setState(() => _selectedCategory = val),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 12),
                          // Priority
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                _label('Priority *'),
                                _buildDropdown(
                                  value: _selectedPriority,
                                  hint: 'Select',
                                  items: _priorities,
                                  onChanged: (val) =>
                                      setState(() => _selectedPriority = val),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 20),

                      // Preferred Posting Date *
                      _label('Preferred Posting Date *'),
                      GestureDetector(
                        onTap: _pickDate,
                        child: Container(
                          height: 48,
                          decoration: BoxDecoration(
                            color: const Color(0xFFF3F3F5),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          padding: const EdgeInsets.symmetric(horizontal: 14),
                          child: Row(
                            children: [
                              Expanded(
                                child: Text(
                                  _selectedDate == null
                                      ? ''
                                      : '${_selectedDate!.month}/${_selectedDate!.day}/${_selectedDate!.year}',
                                  style: const TextStyle(
                                    fontFamily: 'Inter',
                                    fontSize: 13,
                                    color: Color(0xFF0A0A0A),
                                  ),
                                ),
                              ),
                              const Icon(
                                Icons.calendar_today_outlined,
                                size: 20,
                                color: Color(0xFF99A1AF),
                              ),
                            ],
                          ),
                        ),
                      ),

                      const SizedBox(height: 20),

                      // Media Upload
                      _label('Media Upload'),
                      GestureDetector(
                        onTap: _pickMedia,
                        child: Container(
                          width: double.infinity,
                          height: 156,
                          decoration: BoxDecoration(
                            color: const Color(0xFFF9FAFB),
                            border: Border.all(
                              color: const Color(0xFFD1D5DC),
                              width: 2,
                              style: BorderStyle.solid,
                            ),
                            borderRadius: BorderRadius.circular(14),
                          ),
                          child: _mediaFiles.isEmpty
                              ? Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    SizedBox(
                                      width: 40,
                                      height: 40,
                                      child: CustomPaint(
                                        painter: _UploadIconPainter(),
                                      ),
                                    ),
                                    const SizedBox(height: 10),
                                    const Text(
                                      'Tap to upload images or videos',
                                      style: TextStyle(
                                        fontFamily: 'Inter',
                                        fontWeight: FontWeight.w400,
                                        fontSize: 13,
                                        color: Color(0xFF4A5565),
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    const Text(
                                      'PNG, JPG, MP4 (max 10MB)',
                                      style: TextStyle(
                                        fontFamily: 'Inter',
                                        fontWeight: FontWeight.w400,
                                        fontSize: 10.9,
                                        color: Color(0xFF99A1AF),
                                      ),
                                    ),
                                  ],
                                )
                              : Padding(
                                  padding: const EdgeInsets.all(12),
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        '${_mediaFiles.length} file(s) selected',
                                        style: const TextStyle(
                                          fontFamily: 'Inter',
                                          fontWeight: FontWeight.w600,
                                          fontSize: 12,
                                          color: Color(0xFF364153),
                                        ),
                                      ),
                                      const SizedBox(height: 8),
                                      Expanded(
                                        child: ListView.builder(
                                          itemCount: _mediaFiles.length,
                                          itemBuilder: (context, index) {
                                            final fileName =
                                                _mediaFiles[index].name;
                                            return Padding(
                                              padding: const EdgeInsets.only(
                                                bottom: 4,
                                              ),
                                              child: Text(
                                                '- $fileName',
                                                style: const TextStyle(
                                                  fontFamily: 'Inter',
                                                  fontSize: 11,
                                                  color: Color(0xFF4A5565),
                                                ),
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                            );
                                          },
                                        ),
                                      ),
                                      const Text(
                                        'Tap to change files',
                                        style: TextStyle(
                                          fontFamily: 'Inter',
                                          fontSize: 11,
                                          color: Color(0xFF99A1AF),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                        ),
                      ),

                      const SizedBox(height: 20),

                      // AI-Assisted Caption Generator
                      _label('AI-Assisted Caption Generator'),
                      Container(
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                            colors: [Color(0xFFFAF5FF), Color(0xFFEFF6FF)],
                          ),
                          border: Border.all(color: const Color(0xFFE9D4FF)),
                          borderRadius: BorderRadius.circular(14),
                        ),
                        padding: const EdgeInsets.all(17),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Let AI help you craft an engaging caption based on your post details.',
                              style: TextStyle(
                                fontFamily: 'Inter',
                                fontWeight: FontWeight.w400,
                                fontSize: 13.1,
                                color: Color(0xFF364153),
                                height: 1.53,
                              ),
                            ),
                            const SizedBox(height: 16),
                            // Generate Caption button
                            SizedBox(
                              width: double.infinity,
                              height: 44,
                              child: DecoratedBox(
                                decoration: BoxDecoration(
                                  gradient: const LinearGradient(
                                    colors: [
                                      Color(0xFF9810FA),
                                      Color(0xFF155DFC),
                                    ],
                                  ),
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: ElevatedButton(
                                  onPressed: _isGeneratingCaption
                                      ? null
                                      : () => _onGenerateCaption(),
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: Colors.transparent,
                                    shadowColor: Colors.transparent,
                                    disabledBackgroundColor: Colors.transparent,
                                    disabledForegroundColor: Colors.white,
                                    shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                  ),
                                  child: _isGeneratingCaption
                                      ? const SizedBox(
                                          width: 18,
                                          height: 18,
                                          child: CircularProgressIndicator(
                                            strokeWidth: 2,
                                            valueColor:
                                                AlwaysStoppedAnimation<Color>(
                                                  Colors.white,
                                                ),
                                          ),
                                        )
                                      : Row(
                                          mainAxisAlignment:
                                              MainAxisAlignment.center,
                                          children: const [
                                            Icon(
                                              Icons.auto_awesome,
                                              size: 16,
                                              color: Colors.white,
                                            ),
                                            SizedBox(width: 8),
                                            Text(
                                              'Generate Caption',
                                              style: TextStyle(
                                                fontFamily: 'Inter',
                                                fontWeight: FontWeight.w500,
                                                fontSize: 13.2,
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
                      ),

                      const SizedBox(height: 20),

                      // Caption
                      _label('Caption'),
                      SizedBox(
                        height: 120,
                        child: TextField(
                          controller: _captionController,
                          maxLines: null,
                          expands: true,
                          textAlignVertical: TextAlignVertical.top,
                          style: const TextStyle(
                            fontFamily: 'Inter',
                            fontSize: 13,
                          ),
                          onChanged: (val) =>
                              setState(() => _captionLength = val.length),
                          decoration: _inputDeco(),
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        '$_captionLength characters',
                        style: const TextStyle(
                          fontFamily: 'Inter',
                          fontWeight: FontWeight.w400,
                          fontSize: 10.7,
                          color: Color(0xFF6A7282),
                        ),
                      ),

                      const SizedBox(height: 20),

                      // Submit Request button
                      SizedBox(
                        width: double.infinity,
                        height: 48,
                        child: ElevatedButton(
                          onPressed: _isSubmitting ? null : _onSubmit,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF003366),
                            foregroundColor: Colors.white,
                            elevation: 0,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                          ),
                          child: _isSubmitting
                              ? const SizedBox(
                                  width: 20,
                                  height: 20,
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2,
                                    valueColor: AlwaysStoppedAnimation<Color>(
                                      Colors.white,
                                    ),
                                  ),
                                )
                              : const Text(
                                  'Submit Request',
                                  style: TextStyle(
                                    fontFamily: 'Inter',
                                    fontWeight: FontWeight.w500,
                                    fontSize: 15.1,
                                    color: Colors.white,
                                  ),
                                ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),

          // ── Bottom Nav ─────────────────────────────────
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
            'Create Request',
            style: TextStyle(
              fontFamily: 'Inter',
              fontWeight: FontWeight.w700,
              fontSize: 24,
              color: Color(0xFF003366),
            ),
          ),
          SizedBox(height: 4),
          Text(
            'Submit a new social media post',
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

  // ── Dropdown ──────────────────────────────────────────
  Widget _buildDropdown({
    required String? value,
    required String hint,
    required List<String> items,
    required ValueChanged<String?> onChanged,
  }) {
    return Container(
      height: 36,
      decoration: BoxDecoration(
        color: const Color(0xFFF3F3F5),
        borderRadius: BorderRadius.circular(8),
      ),
      padding: const EdgeInsets.symmetric(horizontal: 13),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String>(
          value: value,
          hint: const Text(
            'Select',
            style: TextStyle(
              fontFamily: 'Inter',
              fontWeight: FontWeight.w500,
              fontSize: 12.6,
              color: Color(0xFF717182),
            ),
          ),
          icon: const Icon(
            Icons.keyboard_arrow_down,
            size: 16,
            color: Color(0xFF717182),
          ),
          isExpanded: true,
          style: const TextStyle(
            fontFamily: 'Inter',
            fontWeight: FontWeight.w500,
            fontSize: 12.6,
            color: Color(0xFF0A0A0A),
          ),
          items: items
              .map((e) => DropdownMenuItem(value: e, child: Text(e)))
              .toList(),
          onChanged: onChanged,
        ),
      ),
    );
  }
}

// ── Platform Checkbox Row ─────────────────────────────────────────────────────
class _PlatformCheckbox extends StatelessWidget {
  final String label;
  final bool value;
  final ValueChanged<bool?> onChanged;

  const _PlatformCheckbox({
    required this.label,
    required this.value,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () => onChanged(!value),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 6),
        child: Row(
          children: [
            Container(
              width: 16,
              height: 16,
              decoration: BoxDecoration(
                color: value
                    ? const Color(0xFF003366)
                    : const Color(0xFFF3F3F5),
                border: Border.all(
                  color: value
                      ? const Color(0xFF003366)
                      : Colors.black.withOpacity(0.1),
                ),
                borderRadius: BorderRadius.circular(4),
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x0D000000),
                    blurRadius: 2,
                    offset: Offset(0, 1),
                  ),
                ],
              ),
              child: value
                  ? const Icon(Icons.check, size: 11, color: Colors.white)
                  : null,
            ),
            const SizedBox(width: 13),
            Text(
              label,
              style: const TextStyle(
                fontFamily: 'Inter',
                fontWeight: FontWeight.w500,
                fontSize: 14.6,
                color: Color(0xFF0A0A0A),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ── Upload Icon Painter ───────────────────────────────────────────────────────
class _UploadIconPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFF99A1AF)
      ..style = PaintingStyle.stroke
      ..strokeWidth = 3.33
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;

    // Tray base
    canvas.drawLine(
      Offset(size.width * 0.125, size.height * 0.75),
      Offset(size.width * 0.125, size.height * 0.875),
      paint,
    );
    canvas.drawLine(
      Offset(size.width * 0.125, size.height * 0.875),
      Offset(size.width * 0.875, size.height * 0.875),
      paint,
    );
    canvas.drawLine(
      Offset(size.width * 0.875, size.height * 0.875),
      Offset(size.width * 0.875, size.height * 0.75),
      paint,
    );

    // Up arrow stem
    canvas.drawLine(
      Offset(size.width * 0.5, size.height * 0.625),
      Offset(size.width * 0.5, size.height * 0.125),
      paint,
    );

    // Arrow head
    final arrowPath = Path()
      ..moveTo(size.width * 0.292, size.height * 0.333)
      ..lineTo(size.width * 0.5, size.height * 0.125)
      ..lineTo(size.width * 0.708, size.height * 0.333);
    canvas.drawPath(arrowPath, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}
