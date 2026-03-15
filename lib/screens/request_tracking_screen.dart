import 'package:flutter/material.dart';
import '../app_bottom_nav.dart';

class RequestTrackingScreen extends StatelessWidget {
  // Pass real request data when navigating to this screen
  final String requestNumber;
  final String requestTitle;
  final List<TrackingEvent> events;
  final String currentStatus;
  final String currentStatusMessage;

  const RequestTrackingScreen({
    super.key,
    this.requestNumber = '',
    this.requestTitle = '',
    this.events = const [],
    this.currentStatus = '',
    this.currentStatusMessage = '',
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF9FAFB),
      body: Stack(
        children: [
          Column(
            children: [
              // ── Header ──────────────────────────────────
              _buildHeader(context),

              // ── Timeline or empty state ──────────────────
              Expanded(
                child: events.isEmpty ? _buildEmptyState() : _buildTimeline(),
              ),

              const SizedBox(height: 65),
            ],
          ),

          // ── Bottom Nav ───────────────────────────────────
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: _buildBottomNav(context),
          ),
        ],
      ),
    );
  }

  // ── Header ────────────────────────────────────────────
  Widget _buildHeader(BuildContext context) {
    return Material(
      color: Colors.white,
      elevation: 1,
      child: Padding(
        padding: EdgeInsets.only(
          top: MediaQuery.of(context).padding.top + 12,
          bottom: 16,
          left: 16,
          right: 24,
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Back button + title row
            Row(
              children: [
                GestureDetector(
                  onTap: () => Navigator.of(context).pop(),
                  child: const Icon(
                    Icons.arrow_back,
                    size: 24,
                    color: Color(0xFF003366),
                  ),
                ),
                const SizedBox(width: 16),
                const Text(
                  'Request Tracking',
                  style: TextStyle(
                    fontFamily: 'Inter',
                    fontWeight: FontWeight.w500,
                    fontSize: 18.4,
                    color: Color(0xFF003366),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 8),

            // Request number and title (shown if available)
            if (requestNumber.isNotEmpty || requestTitle.isNotEmpty)
              Padding(
                padding: const EdgeInsets.only(left: 10),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (requestNumber.isNotEmpty)
                      Text(
                        requestNumber,
                        style: const TextStyle(
                          fontFamily: 'Inter',
                          fontWeight: FontWeight.w400,
                          fontSize: 13.2,
                          color: Color(0xFF4A5565),
                        ),
                      ),
                    if (requestTitle.isNotEmpty)
                      Text(
                        requestTitle,
                        style: const TextStyle(
                          fontFamily: 'Inter',
                          fontWeight: FontWeight.w400,
                          fontSize: 13,
                          color: Color(0xFF101828),
                        ),
                      ),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }

  // ── Empty state ───────────────────────────────────────
  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.only(bottom: 80),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: const [
            Icon(Icons.timeline_outlined, size: 48, color: Color(0xFF99A1AF)),
            SizedBox(height: 12),
            Text(
              'No tracking history yet',
              style: TextStyle(
                fontFamily: 'Inter',
                fontWeight: FontWeight.w400,
                fontSize: 14,
                color: Color(0xFF4A5565),
              ),
            ),
            SizedBox(height: 6),
            Text(
              'Updates will appear here once your request\nis reviewed by the Marketing Office',
              textAlign: TextAlign.center,
              style: TextStyle(
                fontFamily: 'Inter',
                fontWeight: FontWeight.w400,
                fontSize: 12,
                color: Color(0xFF99A1AF),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ── Timeline ──────────────────────────────────────────
  Widget _buildTimeline() {
    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(16, 24, 16, 24),
      child: Column(
        children: [
          // Timeline items
          _TimelineList(events: events),

          const SizedBox(height: 20),

          // Current status card
          if (currentStatus.isNotEmpty) _buildCurrentStatusCard(),
        ],
      ),
    );
  }

  // ── Current Status card ───────────────────────────────
  Widget _buildCurrentStatusCard() {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [Color(0xFF003366), Color(0xFF004D99)],
        ),
        borderRadius: BorderRadius.circular(10),
        boxShadow: const [
          BoxShadow(
            color: Color(0x1A000000),
            blurRadius: 15,
            offset: Offset(0, 10),
          ),
        ],
      ),
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Current Status',
            style: TextStyle(
              fontFamily: 'Inter',
              fontWeight: FontWeight.w500,
              fontSize: 16.6,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            currentStatus,
            style: const TextStyle(
              fontFamily: 'Inter',
              fontWeight: FontWeight.w400,
              fontSize: 27.3,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            currentStatusMessage,
            style: const TextStyle(
              fontFamily: 'Inter',
              fontWeight: FontWeight.w400,
              fontSize: 13,
              color: Colors.white,
              height: 1.54,
            ),
          ),
        ],
      ),
    );
  }

  // ── Bottom Nav ────────────────────────────────────────
  Widget _buildBottomNav(BuildContext context) {
    return const AppBottomNav(currentIndex: 1);
  }
}

// ── Timeline List ─────────────────────────────────────────────────────────────
class _TimelineList extends StatelessWidget {
  final List<TrackingEvent> events;
  const _TimelineList({required this.events});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: events.asMap().entries.map((entry) {
        final i = entry.key;
        final event = entry.value;
        final isLast = i == events.length - 1;
        return _TimelineItem(event: event, isLast: isLast);
      }).toList(),
    );
  }
}

// ── Single Timeline Item ──────────────────────────────────────────────────────
class _TimelineItem extends StatelessWidget {
  final TrackingEvent event;
  final bool isLast;

  const _TimelineItem({required this.event, required this.isLast});

  @override
  Widget build(BuildContext context) {
    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Left: icon + vertical line
          SizedBox(
            width: 60,
            child: Column(
              children: [
                // Circle icon
                Container(
                  width: 36,
                  height: 36,
                  decoration: const BoxDecoration(
                    color: Color(0xFF003366),
                    shape: BoxShape.circle,
                  ),
                  child: Icon(event.icon, size: 18, color: Colors.white),
                ),
                // Vertical connector line
                if (!isLast)
                  Expanded(
                    child: Container(
                      width: 2,
                      color: const Color(0xFFE5E7EB),
                      margin: const EdgeInsets.symmetric(vertical: 4),
                    ),
                  ),
              ],
            ),
          ),

          const SizedBox(width: 16),

          // Right: card
          Expanded(
            child: Padding(
              padding: EdgeInsets.only(bottom: isLast ? 0 : 20),
              child: Container(
                padding: const EdgeInsets.all(17),
                decoration: BoxDecoration(
                  color: Colors.white,
                  border: Border.all(color: const Color(0xFFF3F4F6)),
                  borderRadius: BorderRadius.circular(10),
                  boxShadow: const [
                    BoxShadow(
                      color: Color(0x1A000000),
                      blurRadius: 3,
                      offset: Offset(0, 1),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      event.title,
                      style: const TextStyle(
                        fontFamily: 'Inter',
                        fontWeight: FontWeight.w500,
                        fontSize: 15.1,
                        color: Color(0xFF101828),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      event.subtitle,
                      style: const TextStyle(
                        fontFamily: 'Inter',
                        fontWeight: FontWeight.w400,
                        fontSize: 12.7,
                        color: Color(0xFF4A5565),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      event.timestamp,
                      style: const TextStyle(
                        fontFamily: 'Inter',
                        fontWeight: FontWeight.w400,
                        fontSize: 10.7,
                        color: Color(0xFF6A7282),
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
}

// ── Data model ────────────────────────────────────────────────────────────────
class TrackingEvent {
  final IconData icon;
  final String title;
  final String subtitle;
  final String timestamp;

  const TrackingEvent({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.timestamp,
  });
}
