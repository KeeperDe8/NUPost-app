import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

class TermsGuidelinesScreen extends StatefulWidget {
  const TermsGuidelinesScreen({super.key});

  @override
  State<TermsGuidelinesScreen> createState() => _TermsGuidelinesScreenState();
}

class _TermsGuidelinesScreenState extends State<TermsGuidelinesScreen>
    with SingleTickerProviderStateMixin {
  late final AnimationController _animCtrl;
  late final Animation<double> _fadeAnim;
  late final Animation<Offset> _slideAnim;

  int _expandedIndex = 0; // Default first section open

  final List<Map<String, dynamic>> _sections = [
    {
      'icon': Icons.verified_user_outlined,
      'title': '1. Eligibility & Governance',
      'summary': 'Who can submit social media posting requests.',
      'content': [
        'Posting requests may only be submitted by officially recognized student organizations, faculty members, academic departments, and administrative offices of National University Lipa.',
        'Each requesting entity must designate an authorized representative responsible for verifying the accuracy of the submission before submitting to the Marketing Office.',
        'Personal or commercial advertisements unrelated to university activities or academic interests are strictly prohibited.',
      ],
    },
    {
      'icon': Icons.schedule_rounded,
      'title': '2. Lead Time & Scheduling Policy',
      'summary': 'Submission windows and timing constraints.',
      'content': [
        'Regular posting requests must be submitted at least 3 to 5 business days prior to the preferred posting date to allow adequate review and scheduling.',
        'Urgent announcements (e.g., severe weather advisories, sudden room changes, emergency notices) may be processed expeditiously with administrative approval.',
        'The Marketing Office reserves the right to reschedule posts to optimize public engagement, avoid platform clutter, or maintain content pacing.',
      ],
    },
    {
      'icon': Icons.palette_outlined,
      'title': '3. Branding & Media Standards',
      'summary': 'Visual quality, official logos, and layout standards.',
      'content': [
        'All visual materials must adhere to the official NU Lipa Brand Identity Manual (official NU Blue #002366, Gold #FFD200, and sanctioned typography).',
        'Images and graphics must be high resolution (minimum 1080x1080px for square, 1080x1350px for portraits) with no watermarks or blurred text.',
        'Captions must be concise, grammatically correct, and uphold the core values and institutional dignity of National University.',
      ],
    },
    {
      'icon': Icons.rate_review_outlined,
      'title': '4. Review & Approval Workflow',
      'summary': 'How requests are evaluated, revised, or rejected.',
      'content': [
        'Every request undergoes a structured review workflow: Pending Review → Under Review → Approved / Rejected → Posted.',
        'If a submission requires modification, an Admin Rejection Note will be issued specifying the required edits. Requestors may update details/media and re-submit back to Pending.',
        'Submissions containing offensive language, political partisanship, misleading information, or unverified claims will be immediately rejected.',
      ],
    },
    {
      'icon': Icons.security_rounded,
      'title': '5. Data Privacy & Consent',
      'summary': 'Compliance with Republic Act 10173 (Data Privacy Act).',
      'content': [
        'Submissions featuring identifiable photographs of students, staff, or guests must have obtained appropriate consent prior to submission.',
        'Private sensitive personal information (such as student ID numbers, home addresses, personal phone numbers, or academic records) must never be published.',
        'Requestors assume full responsibility for ensuring consent compliance for all media assets uploaded to the system.',
      ],
    },
    {
      'icon': Icons.copyright_rounded,
      'title': '6. Intellectual Property & Rights',
      'summary': 'Original work, copyright, and licensing rules.',
      'content': [
        'Requestors must own or possess explicit licensing rights to all photos, videos, vector illustrations, and music tracks included in requests.',
        'Unauthorized use of copyrighted graphics, logos of external commercial brands, or non-royalty-free music will result in rejection.',
        'By submitting content, the requestor grants NU Lipa permission to publish and archive the media on its official digital platforms.',
      ],
    },
  ];

  @override
  void initState() {
    super.initState();
    _animCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 600),
    )..forward();
    _fadeAnim = CurvedAnimation(parent: _animCtrl, curve: Curves.easeOut);
    _slideAnim = Tween<Offset>(
      begin: const Offset(0, 0.04),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _animCtrl, curve: Curves.easeOutCubic));
  }

  @override
  void dispose() {
    _animCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0A0F1D) : const Color(0xFFF4F6FC),
      appBar: AppBar(
        backgroundColor: isDark ? const Color(0xFF0A0F1D) : const Color(0xFF001540),
        elevation: 0,
        systemOverlayStyle: SystemUiOverlayStyle.light,
        leading: IconButton(
          icon: Icon(
            Icons.arrow_back_ios_new_rounded,
            color: isDark ? const Color(0xFFFFD200) : Colors.white,
            size: 18,
          ),
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: const Text(
          'Terms & Guidelines',
          style: TextStyle(
            fontFamily: 'DM Sans',
            fontWeight: FontWeight.w800,
            fontSize: 17,
            color: Colors.white,
          ),
        ),
        centerTitle: true,
      ),
      body: FadeTransition(
        opacity: _fadeAnim,
        child: SlideTransition(
          position: _slideAnim,
          child: Column(
            children: [
              // Hero Banner
              Container(
                width: double.infinity,
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 22),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: isDark
                        ? [const Color(0xFF070B14), const Color(0xFF001540), const Color(0xFF002366)]
                        : [const Color(0xFF001540), const Color(0xFF002366)],
                  ),
                  borderRadius: const BorderRadius.only(
                    bottomLeft: Radius.circular(24),
                    bottomRight: Radius.circular(24),
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.12),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Icon(
                            Icons.gavel_rounded,
                            color: Color(0xFFFFD200),
                            size: 22,
                          ),
                        ),
                        const SizedBox(width: 12),
                        const Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                'NU Lipa Marketing Office',
                                style: TextStyle(
                                  fontFamily: 'DM Sans',
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600,
                                  color: Color(0xFF90B0FF),
                                ),
                              ),
                              Text(
                                'Social Media Governance Policy',
                                style: TextStyle(
                                  fontFamily: 'DM Sans',
                                  fontSize: 16,
                                  fontWeight: FontWeight.w800,
                                  color: Colors.white,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'Please review these posting standards and operational guidelines to ensure your submissions comply with university policies and branding standards.',
                      style: TextStyle(
                        fontFamily: 'DM Sans',
                        fontSize: 12.5,
                        color: Colors.white.withOpacity(0.85),
                        height: 1.45,
                      ),
                    ),
                  ],
                ),
              ),

              // Policy Sections List
              Expanded(
                child: ListView.builder(
                  padding: const EdgeInsets.fromLTRB(16, 16, 16, 30),
                  itemCount: _sections.length,
                  itemBuilder: (context, i) {
                    final item = _sections[i];
                    final isExpanded = _expandedIndex == i;

                    return AnimatedContainer(
                      duration: const Duration(milliseconds: 250),
                      curve: Curves.easeInOut,
                      margin: const EdgeInsets.only(bottom: 12),
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0xFF131D31) : Colors.white,
                        borderRadius: BorderRadius.circular(18),
                        border: Border.all(
                          color: isExpanded
                              ? (isDark
                                  ? const Color(0xFFFFD200).withOpacity(0.5)
                                  : const Color(0xFF002366).withOpacity(0.35))
                              : (isDark ? const Color(0xFF1E2B45) : const Color(0xFFE4E8F0)),
                          width: isExpanded ? 1.5 : 1,
                        ),
                        boxShadow: [
                          BoxShadow(
                            color: isExpanded
                                ? (isDark
                                    ? const Color(0xFFFFD200).withOpacity(0.08)
                                    : const Color(0xFF002366).withOpacity(0.08))
                                : Colors.black.withOpacity(isDark ? 0.2 : 0.03),
                            blurRadius: isExpanded ? 12 : 6,
                            offset: const Offset(0, 3),
                          ),
                        ],
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(18),
                        child: Column(
                          children: [
                            InkWell(
                              onTap: () {
                                setState(() {
                                  _expandedIndex = isExpanded ? -1 : i;
                                });
                              },
                              child: Padding(
                                padding: const EdgeInsets.all(16),
                                child: Row(
                                  children: [
                                    Container(
                                      width: 40,
                                      height: 40,
                                      decoration: BoxDecoration(
                                        color: isExpanded
                                            ? (isDark
                                                ? const Color(0xFF1E2B45)
                                                : const Color(0xFF002366))
                                            : (isDark
                                                ? const Color(0xFF0D1527)
                                                : const Color(0xFFF0F4FC)),
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: Icon(
                                        item['icon'] as IconData,
                                        size: 20,
                                        color: isExpanded
                                            ? (isDark ? const Color(0xFFFFD200) : Colors.white)
                                            : (isDark ? const Color(0xFFFFD200) : const Color(0xFF002366)),
                                      ),
                                    ),
                                    const SizedBox(width: 14),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            item['title'] as String,
                                            style: TextStyle(
                                              fontFamily: 'DM Sans',
                                              fontSize: 14.5,
                                              fontWeight: FontWeight.w800,
                                              color: isDark ? Colors.white : const Color(0xFF080F1E),
                                            ),
                                          ),
                                          const SizedBox(height: 3),
                                          Text(
                                            item['summary'] as String,
                                            style: TextStyle(
                                              fontFamily: 'DM Sans',
                                              fontSize: 12,
                                              color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF6B7280),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                    Icon(
                                      isExpanded
                                          ? Icons.keyboard_arrow_up_rounded
                                          : Icons.keyboard_arrow_down_rounded,
                                      color: isDark ? const Color(0xFF8E9BAE) : const Color(0xFF9AA3B2),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                            if (isExpanded) ...[
                              Container(
                                height: 1,
                                color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFF0F4FC),
                              ),
                              Container(
                                width: double.infinity,
                                padding: const EdgeInsets.fromLTRB(18, 14, 18, 18),
                                color: isDark ? const Color(0xFF0D1527) : const Color(0xFFFAFBFE),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: (item['content'] as List<String>)
                                      .map((point) => Padding(
                                            padding: const EdgeInsets.only(bottom: 10),
                                            child: Row(
                                              crossAxisAlignment: CrossAxisAlignment.start,
                                              children: [
                                                Padding(
                                                  padding: const EdgeInsets.only(top: 5),
                                                  child: Icon(
                                                    Icons.check_circle_rounded,
                                                    size: 14,
                                                    color: isDark ? const Color(0xFFFFD200) : const Color(0xFF002366),
                                                  ),
                                                ),
                                                const SizedBox(width: 10),
                                                Expanded(
                                                  child: Text(
                                                    point,
                                                    style: TextStyle(
                                                      fontFamily: 'DM Sans',
                                                      fontSize: 13,
                                                      color: isDark ? const Color(0xFFCBD5E1) : const Color(0xFF374151),
                                                      height: 1.45,
                                                    ),
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ))
                                      .toList(),
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                    );
                  },
                ),
              ),

              // Bottom Confirmation bar
              Container(
                padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF0D1527) : Colors.white,
                  border: Border(
                    top: BorderSide(
                      color: isDark ? const Color(0xFF1E2B45) : const Color(0xFFE4E8F0),
                    ),
                  ),
                ),
                child: SafeArea(
                  top: false,
                  child: SizedBox(
                    width: double.infinity,
                    height: 50,
                    child: ElevatedButton(
                      onPressed: () => Navigator.of(context).pop(true),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF002366),
                        elevation: 0,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                          side: isDark
                              ? const BorderSide(color: Color(0xFF1E2B45))
                              : BorderSide.none,
                        ),
                      ),
                      child: const Text(
                        'I Understand & Agree',
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w800,
                          fontSize: 15,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
