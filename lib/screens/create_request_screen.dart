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
    with SingleTickerProviderStateMixin {
  late final AnimationController _submitScaleController;
  final _titleController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _captionController = TextEditingController();

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
  List<PlatformFile> _mediaFiles = [];

  final List<String> _categories = [
    'Event',
    'Announcement',
    'News',
    'Achievement',
    'Promotion',
  ];
  final List<String> _priorities = ['Low', 'Medium', 'High', 'Urgent'];

  // Platform icons mapping
  final Map<String, IconData> _platformIcons = {
    'Facebook': Icons.facebook_rounded,
    'LinkedIn': Icons.link_rounded,
  };

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
  }

  @override
  void dispose() {
    _submitScaleController.dispose();
    _titleController.dispose();
    _descriptionController.dispose();
    _captionController.dispose();
    super.dispose();
  }

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
        if (data['public_calendar'] is List) allPosts.addAll(data['public_calendar']);
        
        int count = 0;
        List<Map<String, dynamic>> matchingPosts = [];
        
        for (final item in allPosts) {
          final sDate = item['scheduled_date']?.toString();
          final rDate = item['request_date']?.toString();
          final dStr = sDate ?? rDate;
          if (dStr != null && dStr.isNotEmpty) {
            try {
              final d = DateTime.parse(dStr);
              if (d.year == date.year && d.month == date.month && d.day == date.day) {
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
      final msg = e.toString().replaceFirst('Exception: ', '');
      final fallback = _buildFallbackCaption(
        title: title,
        category: category,
        platforms: platforms,
      );
      setState(() {
        _captionController.text = fallback;
        _captionLength = fallback.length;
      });
      _showSnack('AI unavailable ($msg). Used smart fallback caption.');
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
      _showSnack('Request submitted successfully.');
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
      _showSnack(
        'Submit failed: ${e.toString().replaceFirst('Exception: ', '')}',
      );
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  void _showSnack(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
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
                  padding: const EdgeInsets.fromLTRB(16, 20, 16, 100),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _fieldLabel('Post / Event Title *'),
                      _textField(
                        _titleController,
                        hint: 'e.g. Welcome Back NU Peeps!',
                      ),
                      const SizedBox(height: 20),

                      _fieldLabel('Description *'),
                      _textAreaField(_descriptionController, rows: 5),
                      const SizedBox(height: 20),

                      _fieldLabel('Target Platform(s) *'),
                      _buildPlatformList(),
                      const SizedBox(height: 20),

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
                                  onChanged: (val) =>
                                      setState(() => _selectedCategory = val),
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
                                  onChanged: (val) =>
                                      setState(() => _selectedPriority = val),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 20),

                      _fieldLabel('Preferred Posting Date *'),
                      _buildDatePicker(),
                      _buildDateVolumePanel(),
                      const SizedBox(height: 20),

                      _fieldLabel('Media Upload'),
                      _buildMediaUpload(),
                      const SizedBox(height: 20),

                      _fieldLabel('AI Caption Generator'),
                      _buildAICaption(),
                      const SizedBox(height: 20),

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
                      const SizedBox(height: 24),

                      _buildSubmitButton(),
                    ],
                  ),
                ),
              ),
            ],
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
      padding: EdgeInsets.only(
        left: 22,
        right: 22,
        top: MediaQuery.of(context).padding.top + 18,
        bottom: 18,
      ),
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
              fontWeight: FontWeight.w400,
              fontSize: 13,
              color: Color(0xFF9AA3B2),
            ),
          ),
        ],
      ),
    );
  }

  // ── Field Label ───────────────────────────────────────────────────────────
  Widget _fieldLabel(String text) {
    return Padding(
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
  }

  // ── Text Field ────────────────────────────────────────────────────────────
  Widget _textField(TextEditingController ctrl, {String? hint}) {
    return TextField(
      controller: ctrl,
      style: const TextStyle(
        fontFamily: 'DM Sans',
        fontSize: 14,
        color: Color(0xFF080F1E),
      ),
      decoration: _inputDeco(hint: hint),
    );
  }

  Widget _textAreaField(
    TextEditingController ctrl, {
    int rows = 4,
    ValueChanged<String>? onChanged,
  }) {
    return SizedBox(
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
  }

  InputDecoration _inputDeco({String? hint}) {
    return InputDecoration(
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
  }

  // ── Platform List ─────────────────────────────────────────────────────────
  Widget _buildPlatformList() {
    final entries = _platforms.entries.toList();
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0x0E000000)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x06001540),
            blurRadius: 10,
            offset: Offset(0, 3),
          ),
        ],
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
                    ? const BorderRadius.vertical(top: Radius.circular(18))
                    : i == entries.length - 1
                    ? const BorderRadius.vertical(bottom: Radius.circular(18))
                    : BorderRadius.zero,
                onTap: () => setState(() => _platforms[platform] = !isChecked),
                child: Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 13,
                  ),
                  child: Row(
                    children: [
                      // Custom checkbox
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
                      const SizedBox(width: 13),
                      // Platform icon
                      Container(
                        width: 32,
                        height: 32,
                        decoration: BoxDecoration(
                          color: const Color(0xFF002366).withOpacity(0.07),
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
                  margin: const EdgeInsets.symmetric(horizontal: 16),
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
  }) {
    return Container(
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
  }

  // ── Date Picker ───────────────────────────────────────────────────────────
  Widget _buildDatePicker() {
    return GestureDetector(
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
  }

  // ── Date Volume Panel ─────────────────────────────────────────────────────
  Widget _buildDateVolumePanel() {
    if (_selectedDate == null) return const SizedBox();

    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      margin: const EdgeInsets.only(top: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: const Color(0xFFE4E8F0), width: 1.5),
        borderRadius: BorderRadius.circular(14),
        boxShadow: const [
          BoxShadow(
            color: Color(0x14000000), // 8% opacity roughly
            blurRadius: 16,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Panel Header
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            decoration: const BoxDecoration(
              color: Color(0xFFFAFBFD),
              border: Border(bottom: BorderSide(color: Color(0xFFF0F2F8))),
              borderRadius: BorderRadius.vertical(top: Radius.circular(12.5)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  '${_selectedDate!.month}/${_selectedDate!.day}/${_selectedDate!.year}',
                  style: const TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 12.5,
                    fontWeight: FontWeight.w700,
                    color: Color(0xFF111827),
                  ),
                ),
                Text(
                  _isLoadingDateData 
                    ? 'Checking schedule…' 
                    : (_datePostCount == 0 ? 'Free day' : '$_datePostCount request${_datePostCount == 1 ? "" : "s"} scheduled'),
                  style: const TextStyle(
                    fontFamily: 'DM Sans',
                    fontSize: 11,
                    color: Color(0xFF9AA3B2),
                  ),
                ),
              ],
            ),
          ),
          
          // Body
          Container(
            padding: const EdgeInsets.all(16),
            child: _isLoadingDateData 
              ? const Center(
                  child: Padding(
                    padding: EdgeInsets.all(8.0),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        SizedBox(
                          width: 16, height: 16, 
                          child: CircularProgressIndicator(strokeWidth: 2, color: Color(0xFF3B6EF5))
                        ),
                        SizedBox(width: 8),
                        Text('Loading schedule…', style: TextStyle(color: Color(0xFF9CA3AF), fontSize: 12.5, fontFamily: 'DM Sans')),
                      ],
                    ),
                  ),
                )
              : Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Capacity Label
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Schedule load',
                          style: TextStyle(fontFamily: 'DM Sans', fontSize: 11.5, color: Color(0xFF6B7280), fontWeight: FontWeight.w500),
                        ),
                        Text(
                          _getCapacityLabel(_datePostCount),
                          style: TextStyle(fontFamily: 'DM Sans', fontSize: 13, color: _getCapacityColor(_datePostCount), fontWeight: FontWeight.w800),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    
                    // Bar Track
                    Container(
                      height: 8,
                      decoration: BoxDecoration(
                        color: const Color(0xFFF3F4F6),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      alignment: Alignment.centerLeft,
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 500),
                        width: _getCapacityWidth(_datePostCount),
                        decoration: BoxDecoration(
                          gradient: _getCapacityGradient(_datePostCount),
                          borderRadius: BorderRadius.circular(8),
                        ),
                      ),
                    ),

                    // Existing Requests List
                    if (_dateUpcomingPosts.isNotEmpty) ...[
                      const SizedBox(height: 12),
                      ..._dateUpcomingPosts.take(5).map((post) {
                        final status = post['status']?.toString() ?? 'Pending';
                        final title = post['title']?.toString() ?? post['platform']?.toString() ?? 'Untitled';
                        
                        return Container(
                          margin: const EdgeInsets.only(bottom: 7),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF8F9FB),
                            border: Border.all(color: const Color(0xFFF0F2F8)),
                            borderRadius: BorderRadius.circular(9),
                          ),
                          child: Row(
                            children: [
                              Container(
                                width: 8, height: 8,
                                decoration: BoxDecoration(
                                  shape: BoxShape.circle,
                                  color: _getStatusColor(status)
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Text(
                                  title,
                                  maxLines: 1, 
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(fontFamily: 'DM Sans', fontWeight: FontWeight.w600, color: Color(0xFF374151), fontSize: 12),
                                ),
                              ),
                              Text(
                                status,
                                style: const TextStyle(fontFamily: 'DM Sans', fontSize: 10.5, color: Color(0xFF9CA3AF)),
                              )
                            ],
                          ),
                        );
                      }).toList(),
                    ] else ...[
                      const SizedBox(height: 18),
                      const Center(
                        child: Column(
                          children: [
                            Text('Free Date', style: TextStyle(fontFamily: 'DM Sans', fontSize: 12.5, color: Color(0xFF9AA3B2))),
                            SizedBox(height: 4),
                            Text('No scheduled posts', style: TextStyle(fontFamily: 'DM Sans', fontSize: 13, color: Color(0xFF16A34A), fontWeight: FontWeight.w700)),
                          ],
                        ),
                      ),
                    ]
                  ],
                ),
          ),
        ],
      )
    );
  }

  Color _getCapacityColor(int count) {
    if (count == 0) return const Color(0xFF16A34A); // Low (Green)
    if (count <= 2) return const Color(0xFFD97706); // Medium (Orange)
    return const Color(0xFFDC2626); // High (Red)
  }

  LinearGradient _getCapacityGradient(int count) {
    if (count == 0) return const LinearGradient(colors: [Color(0xFF4ADE80), Color(0xFF16A34A)]);
    if (count <= 2) return const LinearGradient(colors: [Color(0xFFFBBF24), Color(0xFFD97706)]);
    return const LinearGradient(colors: [Color(0xFFF87171), Color(0xFFDC2626)]);
  }

  double _getCapacityWidth(int count) {
    // Calculate width relative to 100% (or fixed value mapping)
    // Up to 5 posts to fill the bar completely
    final pct = (count * 20).clamp(0, 100);
    // Since width is constrained by parent, we use percentage via LayoutBuilder OR a fixed large dimension 
    // Wait, simpler: we need a percentage Width! 
    return (MediaQuery.of(context).size.width - 64) * (pct / 100); 
  }

  String _getCapacityLabel(int count) {
    if (count == 0) return 'Open — no requests yet';
    if (count == 1) return 'Light — 1 request';
    if (count <= 2) return 'Moderate — $count requests';
    return 'Busy — $count requests';
  }

  Color _getStatusColor(String status) {
    final s = status.toLowerCase();
    if (s.contains('approved')) return const Color(0xFF16A34A);
    if (s.contains('review')) return const Color(0xFFD97706);
    return const Color(0xFF9CA3AF);
  }

  // ── Media Upload ──────────────────────────────────────────────────────────
  Widget _buildMediaUpload() {
    return GestureDetector(
      onTap: _pickMedia,
      child: Container(
        width: double.infinity,
        constraints: const BoxConstraints(minHeight: 140),
        decoration: BoxDecoration(
          color: const Color(0xFFF8FAFE),
          border: Border.all(
            color: const Color(0xFF2B5CE6).withOpacity(0.2),
            width: 2,
            style: BorderStyle.solid,
          ),
          borderRadius: BorderRadius.circular(18),
        ),
        child: _mediaFiles.isEmpty
            ? Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      width: 48,
                      height: 48,
                      decoration: BoxDecoration(
                        color: const Color(0xFF2B5CE6).withOpacity(0.1),
                        borderRadius: BorderRadius.circular(16),
                      ),
                      child: const Icon(
                        Icons.upload_rounded,
                        size: 24,
                        color: Color(0xFF2B5CE6),
                      ),
                    ),
                    const SizedBox(height: 12),
                    const Text(
                      'Tap to upload images or videos',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w600,
                        fontSize: 13.5,
                        color: Color(0xFF3D4A63),
                      ),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'PNG, JPG, MP4 · max 10MB',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontWeight: FontWeight.w400,
                        fontSize: 11.5,
                        color: Color(0xFF9AA3B2),
                      ),
                    ),
                  ],
                ),
              )
            : Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          width: 32,
                          height: 32,
                          decoration: BoxDecoration(
                            color: const Color(0xFF05C46B).withOpacity(0.1),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(
                            Icons.check_rounded,
                            size: 16,
                            color: Color(0xFF05C46B),
                          ),
                        ),
                        const SizedBox(width: 10),
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
                    const SizedBox(height: 6),
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
  }

  // ── AI Caption ────────────────────────────────────────────────────────────
  Widget _buildAICaption() {
    return Container(
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFFF8F4FF), Color(0xFFEFF4FF)],
        ),
        border: Border.all(color: const Color(0xFF8B5CF6).withOpacity(0.2)),
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF8B5CF6).withOpacity(0.07),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF7C3AED), Color(0xFF2563EB)],
                  ),
                  borderRadius: BorderRadius.circular(12),
                  boxShadow: const [
                    BoxShadow(
                      color: Color(0x407C3AED),
                      blurRadius: 8,
                      offset: Offset(0, 3),
                    ),
                  ],
                ),
                child: const Icon(
                  Icons.auto_awesome_rounded,
                  size: 18,
                  color: Colors.white,
                ),
              ),
              const SizedBox(width: 12),
              const Text(
                'AI Caption Generator',
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w800,
                  fontSize: 14,
                  color: Color(0xFF080F1E),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Text(
            'Let AI craft an engaging caption based on your post details.',
            style: TextStyle(
              fontFamily: 'DM Sans',
              fontWeight: FontWeight.w400,
              fontSize: 13,
              color: Color(0xFF3D4A63),
              height: 1.55,
            ),
          ),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            height: 46,
            child: DecoratedBox(
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFF7C3AED), Color(0xFF2563EB)],
                ),
                borderRadius: BorderRadius.circular(14),
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x407C3AED),
                    blurRadius: 14,
                    offset: Offset(0, 5),
                  ),
                ],
              ),
              child: ElevatedButton(
                onPressed: _isGeneratingCaption ? null : _onGenerateCaption,
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.transparent,
                  shadowColor: Colors.transparent,
                  disabledBackgroundColor: Colors.transparent,
                  disabledForegroundColor: Colors.white70,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                ),
                child: _isGeneratingCaption
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(
                            Colors.white,
                          ),
                        ),
                      )
                    : const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.auto_awesome_rounded,
                            size: 17,
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

  // ── Submit Button ─────────────────────────────────────────────────────────
  Widget _buildSubmitButton() {
    return GestureDetector(
      onTapDown: (_) {
        if (!_isSubmitting) _submitScaleController.forward();
      },
      onTapUp: (_) => _submitScaleController.reverse(),
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
          height: 54,
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Color(0xFF001540), Color(0xFF003080)],
            ),
            borderRadius: BorderRadius.circular(18),
            boxShadow: const [
              BoxShadow(
                color: Color(0x45001540),
                blurRadius: 20,
                offset: Offset(0, 7),
              ),
              BoxShadow(
                color: Color(0x18001540),
                blurRadius: 6,
                offset: Offset(0, 2),
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
                borderRadius: BorderRadius.circular(18),
              ),
            ),
            child: _isSubmitting
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                    ),
                  )
                : const Text(
                    'Submit Request',
                    style: TextStyle(
                      fontFamily: 'DM Sans',
                      fontWeight: FontWeight.w900,
                      fontSize: 15.5,
                      color: Colors.white,
                      letterSpacing: 0.1,
                    ),
                  ),
          ),
        ),
      ),
    );
  }
}
