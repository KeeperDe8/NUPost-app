import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'screens/splash_screen.dart';
import 'main_shell.dart';
import 'theme/app_theme.dart';
import 'services/session_store.dart';
import 'services/in_app_notification_service.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  // Lock to portrait mode
  SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);
  await SessionStore.loadSession();
  runApp(const NUPostApp());
}

class NUPostApp extends StatelessWidget {
  const NUPostApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<bool>(
      valueListenable: SessionStore.isDarkModeNotifier,
      builder: (context, isDark, _) {
        return MaterialApp(
          navigatorKey: rootNavigatorKey,
          title: 'NUPost',
          debugShowCheckedModeBanner: false,
          theme: AppTheme.light,
          darkTheme: AppTheme.dark,
          themeMode: isDark ? ThemeMode.dark : ThemeMode.light,
          home: const SplashScreen(),
          routes: {
            '/home': (_) => const MainShell(initialIndex: 0),
            '/requests': (_) => const MainShell(initialIndex: 1),
            '/create': (_) => const MainShell(initialIndex: 2),
            '/notifications': (_) => const MainShell(initialIndex: 3),
            '/profile': (_) => const MainShell(initialIndex: 4),
          },
        );
      },
    );
  }
}
