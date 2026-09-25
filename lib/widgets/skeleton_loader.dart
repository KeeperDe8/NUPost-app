import 'package:flutter/material.dart';
import 'package:shimmer/shimmer.dart';

/// Base shimmer container widget with full dark mode support
class SkeletonLoader extends StatelessWidget {
  final double width;
  final double height;
  final double borderRadius;
  final Color? baseColor;
  final Color? highlightColor;

  const SkeletonLoader({
    super.key,
    this.width = double.infinity,
    required this.height,
    this.borderRadius = 8.0,
    this.baseColor,
    this.highlightColor,
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final defBase = isDark ? const Color(0xFF1E2B45) : const Color(0xFFE2E8F0);
    final defHighlight = isDark ? const Color(0xFF2B3D63) : const Color(0xFFF8FAFC);
    final defFill = isDark ? const Color(0xFF1E2B45) : Colors.white;

    return Shimmer.fromColors(
      baseColor: baseColor ?? defBase,
      highlightColor: highlightColor ?? defHighlight,
      child: Container(
        width: width,
        height: height,
        decoration: BoxDecoration(
          color: defFill,
          borderRadius: BorderRadius.circular(borderRadius),
        ),
      ),
    );
  }
}

/// Request Card Skeleton (used in RequestsScreen)
class RequestCardSkeleton extends StatelessWidget {
  const RequestCardSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF131D31) : Colors.white;
    final cardBorder = isDark ? const Color(0xFF1E2B45) : const Color(0xFFE4E8F0);

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 7),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: cardBorder),
        boxShadow: [
          BoxShadow(
            color: isDark ? Colors.black26 : const Color(0x04000000),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: const [
              SkeletonLoader(width: 110, height: 18, borderRadius: 6),
              SkeletonLoader(width: 75, height: 24, borderRadius: 12),
            ],
          ),
          const SizedBox(height: 12),
          const SkeletonLoader(width: double.infinity, height: 16, borderRadius: 6),
          const SizedBox(height: 8),
          const SkeletonLoader(width: 220, height: 14, borderRadius: 6),
          const SizedBox(height: 16),
          Row(
            children: const [
              SkeletonLoader(width: 80, height: 24, borderRadius: 8),
              SizedBox(width: 8),
              SkeletonLoader(width: 90, height: 24, borderRadius: 8),
              Spacer(),
              SkeletonLoader(width: 65, height: 14, borderRadius: 4),
            ],
          ),
        ],
      ),
    );
  }
}

/// Notification Item Skeleton (used in NotificationsScreen)
class NotificationSkeleton extends StatelessWidget {
  const NotificationSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF131D31) : Colors.white;
    final cardBorder = isDark ? const Color(0xFF1E2B45) : const Color(0xFFE4E8F0);

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: cardBorder),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const SkeletonLoader(width: 42, height: 42, borderRadius: 14),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: const [
                SkeletonLoader(width: 160, height: 15, borderRadius: 6),
                SizedBox(height: 8),
                SkeletonLoader(width: double.infinity, height: 13, borderRadius: 6),
                SizedBox(height: 5),
                SkeletonLoader(width: 120, height: 13, borderRadius: 6),
                SizedBox(height: 8),
                SkeletonLoader(width: 70, height: 11, borderRadius: 4),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// Message Thread Item Skeleton (used in MessagesScreen)
class MessageThreadSkeleton extends StatelessWidget {
  const MessageThreadSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF131D31) : Colors.white;
    final cardBorder = isDark ? const Color(0xFF1E2B45) : const Color(0xFFE4E8F0);

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: cardBg,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: cardBorder),
      ),
      child: Row(
        children: [
          const SkeletonLoader(width: 46, height: 46, borderRadius: 15),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: const [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    SkeletonLoader(width: 130, height: 15, borderRadius: 6),
                    SkeletonLoader(width: 45, height: 12, borderRadius: 4),
                  ],
                ),
                SizedBox(height: 7),
                SkeletonLoader(width: double.infinity, height: 13, borderRadius: 6),
                SizedBox(height: 5),
                SkeletonLoader(width: 180, height: 12, borderRadius: 5),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

/// Home Content Skeleton (used in HomeScreen)
class HomeContentSkeleton extends StatelessWidget {
  const HomeContentSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Hero card shimmer
          const SkeletonLoader(height: 160, borderRadius: 24),
          const SizedBox(height: 20),

          // Metrics grid shimmer
          Row(
            children: const [
              Expanded(child: SkeletonLoader(height: 95, borderRadius: 18)),
              SizedBox(width: 12),
              Expanded(child: SkeletonLoader(height: 95, borderRadius: 18)),
              SizedBox(width: 12),
              Expanded(child: SkeletonLoader(height: 95, borderRadius: 18)),
            ],
          ),
          const SizedBox(height: 24),

          // Section header shimmer
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: const [
              SkeletonLoader(width: 140, height: 18, borderRadius: 6),
              SkeletonLoader(width: 60, height: 14, borderRadius: 4),
            ],
          ),
          const SizedBox(height: 14),

          // List cards
          const RequestCardSkeleton(),
          const RequestCardSkeleton(),
          const RequestCardSkeleton(),
        ],
      ),
    );
  }
}

/// Profile Content Skeleton (used in ProfileScreen)
class ProfileSkeleton extends StatelessWidget {
  const ProfileSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF131D31) : Colors.white;
    final cardBorder = isDark ? const Color(0xFF1E2B45) : const Color(0xFFE4E8F0);

    return SingleChildScrollView(
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 20, 16, 100),
      child: Column(
        children: [
          // User Avatar & Name
          Center(
            child: Column(
              children: const [
                SkeletonLoader(width: 86, height: 86, borderRadius: 43),
                SizedBox(height: 14),
                SkeletonLoader(width: 160, height: 20, borderRadius: 6),
                SizedBox(height: 6),
                SkeletonLoader(width: 200, height: 14, borderRadius: 5),
              ],
            ),
          ),
          const SizedBox(height: 24),

          // Stats row
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: cardBg,
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: cardBorder),
            ),
            child: Row(
              children: const [
                Expanded(child: SkeletonLoader(height: 48, borderRadius: 10)),
                SizedBox(width: 12),
                Expanded(child: SkeletonLoader(height: 48, borderRadius: 10)),
                SizedBox(width: 12),
                Expanded(child: SkeletonLoader(height: 48, borderRadius: 10)),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // Contact info card
          const SkeletonLoader(height: 110, borderRadius: 20),
          const SizedBox(height: 20),

          // Menu section card
          const SkeletonLoader(height: 140, borderRadius: 20),
          const SizedBox(height: 20),
          const SkeletonLoader(height: 100, borderRadius: 20),
        ],
      ),
    );
  }
}

/// Calendar Skeleton (used in PostCalendarScreen)
class CalendarSkeleton extends StatelessWidget {
  const CalendarSkeleton({super.key});

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF131D31) : Colors.white;
    final cardBorder = isDark ? const Color(0xFF1E2B45) : const Color(0xFFE4E8F0);

    return SingleChildScrollView(
      physics: const NeverScrollableScrollPhysics(),
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
      child: Column(
        children: [
          // Month header & switcher
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: const [
              SkeletonLoader(width: 130, height: 22, borderRadius: 6),
              SkeletonLoader(width: 90, height: 32, borderRadius: 10),
            ],
          ),
          const SizedBox(height: 16),

          // Calendar month grid
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: cardBg,
              borderRadius: BorderRadius.circular(22),
              border: Border.all(color: cardBorder),
            ),
            child: Column(
              children: [
                // Day of week headers
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: List.generate(
                    7,
                    (_) => const SkeletonLoader(width: 28, height: 14, borderRadius: 4),
                  ),
                ),
                const SizedBox(height: 14),
                // Days matrix
                ...List.generate(
                  5,
                  (_) => Padding(
                    padding: const EdgeInsets.symmetric(vertical: 6),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                      children: List.generate(
                        7,
                        (_) => const SkeletonLoader(width: 32, height: 32, borderRadius: 10),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),

          // Scheduled events header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: const [
              SkeletonLoader(width: 150, height: 18, borderRadius: 6),
              SkeletonLoader(width: 50, height: 14, borderRadius: 4),
            ],
          ),
          const SizedBox(height: 12),

          // Event item cards
          const RequestCardSkeleton(),
          const RequestCardSkeleton(),
        ],
      ),
    );
  }
}
