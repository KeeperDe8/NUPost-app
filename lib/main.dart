import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'screens/splash_screen.dart';
import 'main_shell.dart';
import 'theme/app_theme.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  // Lock to portrait mode
  SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);
  runApp(const NUPostApp());
}

class NUPostApp extends StatelessWidget {
  const NUPostApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'NUPost',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.light,
      home: const SplashScreen(),
      routes: {
        '/home': (_) => const MainShell(initialIndex: 0),
        '/requests': (_) => const MainShell(initialIndex: 1),
        '/create': (_) => const MainShell(initialIndex: 2),
        '/notifications': (_) => const MainShell(initialIndex: 3),
        '/profile': (_) => const MainShell(initialIndex: 4),
      },
    );
  }
}
