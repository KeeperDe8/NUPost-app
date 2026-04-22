import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppColors {
  static const primary = Color(0xFF002366);
  static const primaryDark = Color(0xFF001A4D);
  static const primaryLight = Color(0xFF003A8C);
  static const accent = Color(0xFF3B6EF5);

  static const gold = Color(0xFFF59E0B);
  static const goldLight = Color(0xFFFBBF24);
  static const goldDark = Color(0xFFD97706);
  static const goldBg = Color(0xFFFEF3C7);

  static const surface = Color(0xFFFFFFFF);
  static const pageBg = Color(0xFFE8ECF4);
  static const wrapperBg = Color(0xFFD8DCE8);

  static const ink = Color(0xFF1A1A1A);
  static const inkMid = Color(0xFF3D3D3D);
  static const inkMute = Color(0xFF8A8A8A);
  static const navBg = Color(0xFF111827);

  static const approved = Color(0xFF10B981);
  static const pending = Color(0xFFF59E0B);
  static const posted = Color(0xFF8B5CF6);
  static const rejected = Color(0xFFEF4444);
  static const neutral = Color(0xFFCBD5E1);

  static const border = Color(0xFFE4E8F0);
}

class AppRadius {
  static const sm = 8.0;
  static const md = 12.0;
  static const lg = 16.0;
  static const xl = 22.0;
}

// ─── Chat dark-mode palette ───────────────────────────────────────────────────
class ChatColors {
  static const bg = Color(0xFF121212);
  static const surface1 = Color(0xFF1E1E2E);
  static const surface2 = Color(0xFF282E30);
  static const glassWhite = Color(0x14FFFFFF);
  static const glassBorder = Color(0x1FFFFFFF);
  static const outBubble1 = Color(0xFF002366);
  static const outBubble2 = Color(0xFF3B6EF5);
  static const inBubble = Color(0xFF1E1E2E);
  static const inkOnDark = Color(0xFFECEFF4);
  static const inkMuteDark = Color(0xFF8B95A1);
  static const inputFill = Color(0x22FFFFFF);
}

// ─── ThemeExtension so widgets can pull tokens via Theme.of(ctx) ──────────────
@immutable
class GlassColors extends ThemeExtension<GlassColors> {
  const GlassColors({
    required this.bg,
    required this.surface1,
    required this.surface2,
    required this.glassWhite,
    required this.glassBorder,
    required this.outBubble1,
    required this.outBubble2,
    required this.inBubble,
    required this.inkOnDark,
    required this.inkMuteDark,
    required this.inputFill,
  });

  final Color bg;
  final Color surface1;
  final Color surface2;
  final Color glassWhite;
  final Color glassBorder;
  final Color outBubble1;
  final Color outBubble2;
  final Color inBubble;
  final Color inkOnDark;
  final Color inkMuteDark;
  final Color inputFill;

  static const defaults = GlassColors(
    bg: ChatColors.bg,
    surface1: ChatColors.surface1,
    surface2: ChatColors.surface2,
    glassWhite: ChatColors.glassWhite,
    glassBorder: ChatColors.glassBorder,
    outBubble1: ChatColors.outBubble1,
    outBubble2: ChatColors.outBubble2,
    inBubble: ChatColors.inBubble,
    inkOnDark: ChatColors.inkOnDark,
    inkMuteDark: ChatColors.inkMuteDark,
    inputFill: ChatColors.inputFill,
  );

  static const light = GlassColors(
    bg: AppColors.pageBg,
    surface1: AppColors.surface,
    surface2: AppColors.wrapperBg,
    glassWhite: Color(0xF2FFFFFF),
    glassBorder: AppColors.border,
    outBubble1: AppColors.primary,
    outBubble2: AppColors.accent,
    inBubble: AppColors.surface,
    inkOnDark: AppColors.ink,
    inkMuteDark: AppColors.inkMute,
    inputFill: Color(0xF7FFFFFF),
  );

  @override
  GlassColors copyWith({
    Color? bg,
    Color? surface1,
    Color? surface2,
    Color? glassWhite,
    Color? glassBorder,
    Color? outBubble1,
    Color? outBubble2,
    Color? inBubble,
    Color? inkOnDark,
    Color? inkMuteDark,
    Color? inputFill,
  }) => GlassColors(
    bg: bg ?? this.bg,
    surface1: surface1 ?? this.surface1,
    surface2: surface2 ?? this.surface2,
    glassWhite: glassWhite ?? this.glassWhite,
    glassBorder: glassBorder ?? this.glassBorder,
    outBubble1: outBubble1 ?? this.outBubble1,
    outBubble2: outBubble2 ?? this.outBubble2,
    inBubble: inBubble ?? this.inBubble,
    inkOnDark: inkOnDark ?? this.inkOnDark,
    inkMuteDark: inkMuteDark ?? this.inkMuteDark,
    inputFill: inputFill ?? this.inputFill,
  );

  @override
  GlassColors lerp(GlassColors? other, double t) {
    if (other == null) return this;
    return GlassColors(
      bg: Color.lerp(bg, other.bg, t)!,
      surface1: Color.lerp(surface1, other.surface1, t)!,
      surface2: Color.lerp(surface2, other.surface2, t)!,
      glassWhite: Color.lerp(glassWhite, other.glassWhite, t)!,
      glassBorder: Color.lerp(glassBorder, other.glassBorder, t)!,
      outBubble1: Color.lerp(outBubble1, other.outBubble1, t)!,
      outBubble2: Color.lerp(outBubble2, other.outBubble2, t)!,
      inBubble: Color.lerp(inBubble, other.inBubble, t)!,
      inkOnDark: Color.lerp(inkOnDark, other.inkOnDark, t)!,
      inkMuteDark: Color.lerp(inkMuteDark, other.inkMuteDark, t)!,
      inputFill: Color.lerp(inputFill, other.inputFill, t)!,
    );
  }
}

class AppTheme {
  static ThemeData get light {
    final scheme = ColorScheme.fromSeed(
      seedColor: AppColors.primary,
      surface: AppColors.pageBg,
    );
    final base = ThemeData(
      colorScheme: scheme,
      scaffoldBackgroundColor: AppColors.pageBg,
      textTheme: GoogleFonts.dmSansTextTheme(),
      useMaterial3: true,
    );

    return base.copyWith(
      cardTheme: CardThemeData(
        color: AppColors.surface,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(AppRadius.lg),
          side: const BorderSide(color: Color(0x0F000000)),
        ),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.primary,
          foregroundColor: Colors.white,
          elevation: 0,
          textStyle: GoogleFonts.dmSans(
            fontWeight: FontWeight.w600,
            fontSize: 15,
          ),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppRadius.md),
          ),
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 20),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppRadius.md),
          borderSide: const BorderSide(color: AppColors.border),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppRadius.md),
          borderSide: const BorderSide(color: AppColors.border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppRadius.md),
          borderSide: const BorderSide(color: AppColors.accent, width: 1.5),
        ),
      ),
    );
  }

  static ThemeData get chatDark => ThemeData.dark(useMaterial3: true).copyWith(
    scaffoldBackgroundColor: ChatColors.bg,
    textTheme: GoogleFonts.dmSansTextTheme(ThemeData.dark().textTheme),
    extensions: const [GlassColors.defaults],
  );

  static ThemeData get chatLight => ThemeData(
    useMaterial3: true,
    scaffoldBackgroundColor: AppColors.pageBg,
    textTheme: GoogleFonts.dmSansTextTheme(),
    colorScheme: ColorScheme.fromSeed(seedColor: AppColors.primary),
  ).copyWith(extensions: const [GlassColors.light]);
}
