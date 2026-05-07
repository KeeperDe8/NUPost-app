import 'package:flutter/material.dart';

class AppSnackbar {
  static void show(
    BuildContext context,
    String message, {
    bool isError = false,
    bool isSuccess = false,
  }) {
    final messenger = ScaffoldMessenger.maybeOf(context);
    if (messenger == null) return;
    messenger.hideCurrentSnackBar();

    Color? bg;
    IconData? icon;
    Color fg = Colors.white;
    if (isError) {
      bg = const Color(0xFFFF3B30);
      icon = Icons.error_outline_rounded;
    } else if (isSuccess) {
      bg = const Color(0xFF05C46B);
      icon = Icons.check_circle_rounded;
    }

    messenger.showSnackBar(
      SnackBar(
        backgroundColor: bg,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
        ),
        margin: const EdgeInsets.fromLTRB(16, 0, 16, 16),
        duration: const Duration(seconds: 3),
        content: Row(
          children: [
            if (icon != null) ...[
              Icon(icon, color: fg, size: 20),
              const SizedBox(width: 10),
            ],
            Expanded(
              child: Text(
                message,
                style: TextStyle(
                  fontFamily: 'DM Sans',
                  fontWeight: FontWeight.w600,
                  fontSize: 13,
                  color: bg == null ? null : fg,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
