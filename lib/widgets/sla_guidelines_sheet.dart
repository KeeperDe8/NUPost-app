import 'package:flutter/material.dart';
import '../services/session_store.dart';

class SlaGuidelinesSheet extends StatefulWidget {
  final bool isReferenceMode;

  const SlaGuidelinesSheet({
    super.key,
    this.isReferenceMode = false,
  });

  /// Displays the SLA guidelines sheet as a modal bottom sheet.
  /// Returns `true` if the user proceeds or confirms.
  static Future<bool?> show(
    BuildContext context, {
    bool isReferenceMode = false,
  }) {
    return showModalBottomSheet<bool>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => SlaGuidelinesSheet(isReferenceMode: isReferenceMode),
    );
  }

  @override
  State<SlaGuidelinesSheet> createState() => _SlaGuidelinesSheetState();
}

class _SlaGuidelinesSheetState extends State<SlaGuidelinesSheet> {
  bool _dontShowAgain = false;

  final List<Map<String, dynamic>> _slaItems = const [
    {
      'title': 'Checking of Materials',
      'turnaround': 'Up to 24 hours',
      'badgeColor': Color(0xFF05C46B),
      'icon': Icons.fact_check_outlined,
    },
    {
      'title': 'Posting with Ready-Made PubMat',
      'turnaround': 'Up to 24 hours',
      'badgeColor': Color(0xFF2B5CE6),
      'icon': Icons.image_outlined,
      'note': 'Caption must also be provided upon request submission.',
      'isAlertNote': true,
    },
    {
      'title': 'Template-Based PubMat',
      'turnaround': 'Up to 48 hours',
      'badgeColor': Color(0xFF00A8FF),
      'icon': Icons.dashboard_customize_outlined,
      'note': 'e.g. announcements, congratulatory, news articles, partnerships.',
      'isAlertNote': true,
    },
    {
      'title': 'Standard PubMat',
      'turnaround': '2–4 working days',
      'badgeColor': Color(0xFFF59E0B),
      'icon': Icons.brush_outlined,
      'note': 'Basic, non-templated promotional material created for a specific event.',
      'isAlertNote': false,
    },
    {
      'title': 'Multiple Collaterals / Tarpaulins',
      'turnaround': '5–10 working days',
      'badgeColor': Color(0xFFFF6B6B),
      'icon': Icons.layers_outlined,
    },
    {
      'title': 'New Campaign / Creative Concept',
      'turnaround': '10–20 working days',
      'badgeColor': Color(0xFF8854D0),
      'icon': Icons.lightbulb_outline_rounded,
    },
    {
      'title': 'Event Documentation',
      'turnaround': '1 month prior to event',
      'badgeColor': Color(0xFFFF3838),
      'icon': Icons.camera_alt_outlined,
    },
  ];

  void _onProceed() async {
    if (_dontShowAgain) {
      await SessionStore.setPermanentSlaNotice(false);
    } else {
      SessionStore.dismissSlaForSession();
    }
    if (mounted) Navigator.pop(context, true);
  }

  @override
  Widget build(BuildContext context) {
    final mediaQuery = MediaQuery.of(context);
    final maxHeight = mediaQuery.size.height * 0.88;

    return Container(
      constraints: BoxConstraints(maxHeight: maxHeight),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(28),
          topRight: Radius.circular(28),
        ),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // ── Handle indicator ───────────────────────────────────────────────
          const SizedBox(height: 12),
          Center(
            child: Container(
              width: 44,
              height: 4.5,
              decoration: BoxDecoration(
                color: const Color(0xFFE2E8F0),
                borderRadius: BorderRadius.circular(99),
              ),
            ),
          ),
          const SizedBox(height: 14),

          // ── Header ─────────────────────────────────────────────────────────
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 22),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 46,
                  height: 46,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF001A6E), Color(0xFF002366)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(14),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF002366).withValues(alpha: 0.25),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.schedule_rounded,
                    color: Color(0xFFFFD200),
                    size: 24,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          const Text(
                            'Creative SLA',
                            style: TextStyle(
                              fontFamily: 'DM Sans',
                              fontWeight: FontWeight.w900,
                              fontSize: 18,
                              color: Color(0xFF080F1E),
                              letterSpacing: -0.3,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2.5),
                            decoration: BoxDecoration(
                              color: const Color(0xFFFFD200).withValues(alpha: 0.2),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: const Text(
                              'POLICY',
                              style: TextStyle(
                                fontFamily: 'DM Sans',
                                fontWeight: FontWeight.w800,
                                fontSize: 9.5,
                                color: Color(0xFFB45309),
                                letterSpacing: 0.6,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 3),
                      const Text(
                        'Standard turnaround periods observed by Marketing Office.',
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontSize: 12.5,
                          color: Color(0xFF64748B),
                          height: 1.3,
                        ),
                      ),
                    ],
                  ),
                ),
                if (widget.isReferenceMode)
                  IconButton(
                    icon: const Icon(Icons.close_rounded, color: Color(0xFF64748B)),
                    onPressed: () => Navigator.pop(context),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          const Divider(height: 1, color: Color(0xFFF1F5F9)),

          // ── Scrollable List of Classifications ──────────────────────────────
          Flexible(
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 16),
              physics: const BouncingScrollPhysics(),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  ...List.generate(_slaItems.length, (index) {
                    final item = _slaItems[index];
                    final title = item['title'] as String;
                    final turnaround = item['turnaround'] as String;
                    final badgeColor = item['badgeColor'] as Color;
                    final icon = item['icon'] as IconData;
                    final note = item['note'] as String?;
                    final isAlertNote = (item['isAlertNote'] as bool?) ?? false;

                    return Container(
                      margin: const EdgeInsets.only(bottom: 9),
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(15),
                        border: Border.all(
                          color: const Color(0xFFE2E8F0).withValues(alpha: 0.8),
                          width: 1.1,
                        ),
                      ),
                      child: Row(
                        crossAxisAlignment: note != null
                            ? CrossAxisAlignment.start
                            : CrossAxisAlignment.center,
                        children: [
                          Container(
                            width: 36,
                            height: 36,
                            decoration: BoxDecoration(
                              color: badgeColor.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(11),
                            ),
                            child: Icon(icon, color: badgeColor, size: 19),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Expanded(
                                      child: Text(
                                        title,
                                        style: const TextStyle(
                                          fontFamily: 'DM Sans',
                                          fontWeight: FontWeight.w800,
                                          fontSize: 13.5,
                                          color: Color(0xFF0F172A),
                                        ),
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
                                      decoration: BoxDecoration(
                                        color: badgeColor.withValues(alpha: 0.14),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Text(
                                        turnaround,
                                        style: TextStyle(
                                          fontFamily: 'DM Sans',
                                          fontWeight: FontWeight.w800,
                                          fontSize: 10.5,
                                          color: badgeColor,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                if (note != null) ...[
                                  const SizedBox(height: 3.5),
                                  Text(
                                    note,
                                    style: TextStyle(
                                      fontFamily: 'DM Sans',
                                      fontSize: 11,
                                      fontWeight: isAlertNote ? FontWeight.w600 : FontWeight.w500,
                                      color: isAlertNote ? const Color(0xFFE11D48) : const Color(0xFF64748B),
                                      height: 1.25,
                                    ),
                                  ),
                                ],
                              ],
                            ),
                          ),
                        ],
                      ),
                    );
                  }),
                ],
              ),
            ),
          ),

          // ── Bottom Action Controls ──────────────────────────────────────────
          Container(
            padding: EdgeInsets.fromLTRB(
              20,
              12,
              20,
              mediaQuery.padding.bottom > 0 ? mediaQuery.padding.bottom + 8 : 16,
            ),
            decoration: BoxDecoration(
              color: Colors.white,
              border: const Border(top: BorderSide(color: Color(0xFFF1F5F9))),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.03),
                  blurRadius: 8,
                  offset: const Offset(0, -3),
                ),
              ],
            ),
            child: widget.isReferenceMode
                ? SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF002366),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        elevation: 0,
                      ),
                      onPressed: () => Navigator.pop(context),
                      child: const Text(
                        'Close',
                        style: TextStyle(
                          fontFamily: 'DM Sans',
                          fontWeight: FontWeight.w800,
                          fontSize: 14,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  )
                : Column(
                    children: [
                      // "Don't show again" checkbox
                      InkWell(
                        onTap: () => setState(() => _dontShowAgain = !_dontShowAgain),
                        borderRadius: BorderRadius.circular(8),
                        child: Padding(
                          padding: const EdgeInsets.symmetric(vertical: 4),
                          child: Row(
                            children: [
                              SizedBox(
                                width: 22,
                                height: 22,
                                child: Checkbox(
                                  value: _dontShowAgain,
                                  onChanged: (val) => setState(() => _dontShowAgain = val ?? false),
                                  activeColor: const Color(0xFF002366),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(5)),
                                ),
                              ),
                              const SizedBox(width: 8),
                              const Text(
                                "Don't show this notice automatically again",
                                style: TextStyle(
                                  fontFamily: 'DM Sans',
                                  fontSize: 12.5,
                                  fontWeight: FontWeight.w600,
                                  color: Color(0xFF475569),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          Expanded(
                            flex: 1,
                            child: OutlinedButton(
                              style: OutlinedButton.styleFrom(
                                side: const BorderSide(color: Color(0xFFCBD5E1)),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                padding: const EdgeInsets.symmetric(vertical: 13),
                              ),
                              onPressed: () => Navigator.pop(context, false),
                              child: const Text(
                                'Cancel',
                                style: TextStyle(
                                  fontFamily: 'DM Sans',
                                  fontWeight: FontWeight.w700,
                                  fontSize: 13.5,
                                  color: Color(0xFF64748B),
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            flex: 2,
                            child: ElevatedButton(
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF002366),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                                padding: const EdgeInsets.symmetric(vertical: 13),
                                elevation: 0,
                              ),
                              onPressed: _onProceed,
                              child: const Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Text(
                                    'I Understand & Proceed',
                                    style: TextStyle(
                                      fontFamily: 'DM Sans',
                                      fontWeight: FontWeight.w800,
                                      fontSize: 13.5,
                                      color: Colors.white,
                                    ),
                                  ),
                                  SizedBox(width: 6),
                                  Icon(Icons.arrow_forward_rounded, size: 16, color: Color(0xFFFFD200)),
                                ],
                              ),
                            ),
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
}
