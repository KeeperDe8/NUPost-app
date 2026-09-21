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
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(AppRadius.md),
          border: Border.all(color: AppColors.border),
        ),
        child: const Row(
          children: [
            Icon(Icons.attachment, color: AppColors.inkMute, size: 20),
            SizedBox(width: 8),
            Text(
              'No attachments uploaded',
              style: TextStyle(
                color: AppColors.inkMute,
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
              style: const TextStyle(
                color: AppColors.ink,
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
                      color: AppColors.surface,
                      border: Border.all(color: AppColors.border),
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
                            errorBuilder: (_, __, ___) => Container(
                              color: const Color(0xFFF1F4F9),
                              padding: const EdgeInsets.all(8),
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  const Icon(Icons.broken_image_rounded,
                                      color: Color(0xFF94A3B8), size: 28),
                                  const SizedBox(height: 4),
                                  Text(
                                    url.split('/').last,
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                    textAlign: TextAlign.center,
                                    style: const TextStyle(
                                      color: AppColors.inkMute,
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
        backgroundColor: Colors.black87,
        insetPadding: const EdgeInsets.all(16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Align(
              alignment: Alignment.topRight,
              child: IconButton(
                icon: const Icon(Icons.close, color: Colors.white),
                onPressed: () => Navigator.pop(dialogCtx),
              ),
            ),
            Flexible(
              child: Padding(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                child: isVideo
                    ? Column(
                        mainAxisSize: MainAxisSize.min,
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
                    : Image.network(
                        url,
                        fit: BoxFit.contain,
                        errorBuilder: (_, __, ___) => Padding(
                          padding: const EdgeInsets.all(24),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(Icons.broken_image_rounded,
                                  color: Colors.white54, size: 48),
                              const SizedBox(height: 12),
                              Text(
                                'File: ${url.split('/').last}',
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
            const SizedBox(height: 12),
          ],
        ),
      ),
    );
  }
}
