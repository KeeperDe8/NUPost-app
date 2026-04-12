import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'screens/splash_screen.dart';
import 'screens/home_screen.dart';
import 'screens/requests_screen.dart';
import 'screens/create_request_screen.dart';
import 'screens/notifications_screen.dart';
import 'screens/profile_screen.dart';
import 'screens/post_calendar_screen.dart';
import 'screens/messages_screen.dart';

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
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF29286A)),
        useMaterial3: true,
      ),
      home: const SplashScreen(),
      routes: {
        '/home': (_) => const HomeScreen(),
        '/requests': (_) => const RequestsScreen(),
        '/create': (_) => const CreateRequestScreen(),
        '/notifications': (_) => const NotificationsScreen(),
        '/profile': (_) => const ProfileScreen(),
        '/calendar': (_) => const PostCalendarScreen(),
        '/messages': (_) => const MessagesScreen(),
      },
    );
  }
}
