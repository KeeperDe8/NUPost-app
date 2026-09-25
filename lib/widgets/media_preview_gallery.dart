import 'package:flutter/material.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';

class MediaPreviewGallery extends StatelessWidget {
  final List<String> mediaUrls;
  final List<String> mediaFiles;

  const MediaPreviewGallery({
    super.key,
    required this.mediaUrls,
    this.mediaFiles = const [],
  });

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final cardBg = isDark ? const Color(0xFF131D31) : AppColors.surface;
    final borderColor = isDark ? const Color(0xFF1E2B45) : AppColors.border;
    final textColor = isDark ? const Color(0xFFF1F5F9) : AppColors.ink;
    final subtextColor = isDark ? const Color(0xFF94A3B8) : AppColors.inkMute;
    final brokenBg = isDark ? const Color(0xFF0D1527) : const Color(0xFFF1F4F9);

    final List<String> rawList = mediaUrls.isNotEmpty
        ? mediaUrls
        : mediaFiles.map((f) => 'uploads/$f').toList();

    final urls = rawList
        .map((item) => ApiService.resolveMediaUrl(item))
        .where((u) => u.isNotEmpty)
        .toList();

    if (urls.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: cardBg,
          borderRadius: BorderRadius.circular(AppRadius.md),
          border: Border.all(color: borderColor),
        ),
        child: Row(
          children: [
            Icon(Icons.attachment, color: subtextColor, size: 20),
            const SizedBox(width: 8),
            Text(
              'No attachments uploaded',
              style: TextStyle(
                color: subtextColor,
                fontSize: 14,
                fontStyle: FontStyle.italic,
              ),
            ),
          ],
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            const Icon(Icons.attach_file, color: AppColors.accent, size: 18),
            const SizedBox(width: 6),
            Text(
              'Requested Files & Media (${urls.length})',
              style: TextStyle(
                color: textColor,
                fontWeight: FontWeight.w700,
                fontSize: 14,
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),
        SizedBox(
          height: 110,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: urls.length,
            separatorBuilder: (_, __) => const SizedBox(width: 10),
            itemBuilder: (context, index) {
              final url = urls[index];
              final cleanUrl = url.split('?').first.toLowerCase();
              final isVideo = cleanUrl.endsWith('.mp4') ||
                  cleanUrl.endsWith('.mov') ||
                  cleanUrl.endsWith('.avi') ||
                  cleanUrl.endsWith('.mkv');

              return GestureDetector(
                onTap: () => _showMediaDialog(context, url, isVideo),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(AppRadius.md),
                  child: Container(
                    width: 120,
                    height: 110,
                    decoration: BoxDecoration(
                      color: cardBg,
                      border: Border.all(color: borderColor),
                    ),
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        if (isVideo)
                          Container(
                            color: AppColors.primary.withValues(alpha: 0.85),
                            child: const Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.play_circle_fill,
                                    color: Colors.white, size: 36),
                                SizedBox(height: 4),
                                Text(
                                  'Video',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 12,
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                              ],
                            ),
                          )
                        else
                          Image.network(
                            url,
                            fit: BoxFit.cover,
                            loadingBuilder: (ctx, child, progress) {
                              if (progress == null) return child;
                              return Container(
                                color: brokenBg,
                                child: const Center(
                                  child: SizedBox(
                                    width: 20,
                                    height: 20,
                                    child: CircularProgressIndicator(
                                      strokeWidth: 2,
                                      color: Color(0xFF3B82F6),
                                    ),
                                  ),
                                ),
                              );
                            },
                            errorBuilder: (_, __, ___) => Container(
                              color: brokenBg,
                              padding: const EdgeInsets.all(8),
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(Icons.broken_image_rounded,
                                      color: subtextColor, size: 28),
                                  const SizedBox(height: 4),
                                  Text(
                                    url.split('/').last.split('?').first,
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    textAlign: TextAlign.center,
                                    style: TextStyle(
                                      color: subtextColor,
                                      fontSize: 10,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        Positioned(
                          bottom: 4,
                          right: 4,
                          child: Container(
                            padding: const EdgeInsets.all(4),
                            decoration: const BoxDecoration(
                              color: Colors.black54,
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(
                              Icons.zoom_in,
                              color: Colors.white,
                              size: 14,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  void _showMediaDialog(BuildContext context, String url, bool isVideo) {
    showDialog(
      context: context,
      builder: (dialogCtx) => Dialog(
        backgroundColor: Colors.black.withOpacity(0.92),
        insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        child: ClipRRect(
          borderRadius: BorderRadius.circular(20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                color: const Color(0xFF131D31),
                child: Row(
                  children: [
                    const Icon(Icons.image_outlined, color: Color(0xFF60A5FA), size: 18),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        url.split('/').last.split('?').first,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded, color: Colors.white70, size: 20),
                      padding: EdgeInsets.zero,
                      constraints: const BoxConstraints(),
                      onPressed: () => Navigator.pop(dialogCtx),
                    ),
                  ],
                ),
              ),
              Flexible(
                child: Container(
                  constraints: BoxConstraints(
                    maxHeight: MediaQuery.of(context).size.height * 0.65,
                    minHeight: 200,
                  ),
                  color: Colors.black,
                  child: isVideo
                      ? Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.video_library,
                                color: AppColors.accent, size: 64),
                            const SizedBox(height: 12),
                            SelectableText(
                              url,
                              textAlign: TextAlign.center,
                              style: const TextStyle(color: Colors.white70),
                            ),
                            const SizedBox(height: 16),
                            const Text(
                              'Video preview available on web portal.',
                              style: TextStyle(color: AppColors.inkMute, fontSize: 12),
                            ),
                          ],
                        )
                      : InteractiveViewer(
                          panEnabled: true,
                          minScale: 0.8,
                          maxScale: 4.0,
                          child: Center(
                            child: Image.network(
                              url,
                              fit: BoxFit.contain,
                              loadingBuilder: (ctx, child, progress) {
                                if (progress == null) return child;
                                return const Center(
                                  child: CircularProgressIndicator(
                                    strokeWidth: 2.5,
                                    color: Color(0xFF3B82F6),
                                  ),
                                );
                              },
                              errorBuilder: (_, __, ___) => Padding(
                                padding: const EdgeInsets.all(24),
                                child: Column(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(Icons.broken_image_rounded,
                                        color: Colors.white54, size: 48),
                                    const SizedBox(height: 12),
                                    Text(
                                      'File: ${url.split('/').last.split('?').first}',
                                      textAlign: TextAlign.center,
                                      style: const TextStyle(
                                          color: Colors.white70, fontSize: 13),
                                    ),
                                    const SizedBox(height: 8),
                                    const Text(
                                      'Media file not found on server (404).',
                                      textAlign: TextAlign.center,
                                      style: TextStyle(
                                          color: Colors.white38, fontSize: 11),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                        ),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                color: const Color(0xFF131D31),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: const [
                    Icon(Icons.pinch_outlined, color: Colors.white54, size: 16),
                    SizedBox(width: 6),
                    Text(
                      'Pinch or drag to zoom and pan',
                      style: TextStyle(color: Colors.white54, fontSize: 11),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
