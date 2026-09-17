import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'app_bottom_nav.dart';
import 'screens/admin/admin_dashboard_screen.dart';
import 'screens/home_screen.dart';
import 'screens/requests_screen.dart';
import 'screens/create_request_screen.dart';
import 'screens/notifications_screen.dart';
import 'screens/profile_screen.dart';
import 'screens/messages_screen.dart';
import 'screens/post_calendar_screen.dart';
import 'services/session_store.dart';
import 'widgets/floating_message_button.dart';

class MainShell extends StatefulWidget {
  final int initialIndex;
  const MainShell({super.key, this.initialIndex = 0});

  @override
  State<MainShell> createState() => _MainShellState();

  /// Switch the active tab from any descendant.
  static void switchTo(BuildContext context, int index) {
    final state = context.findAncestorStateOfType<_MainShellState>();
    state?.setIndex(index);
  }
}

class _MainShellState extends State<MainShell> {
  late int _currentIndex = widget.initialIndex;
  final GlobalKey<NavigatorState> _shellNavKey = GlobalKey<NavigatorState>();
  late final ValueNotifier<int> _indexNotifier;
  final Set<int> _activatedTabs = {};

  @override
  void initState() {
    super.initState();
    _activatedTabs.add(widget.initialIndex);
    _indexNotifier = ValueNotifier<int>(widget.initialIndex);
  }

  @override
  void dispose() {
    _indexNotifier.dispose();
    super.dispose();
  }

  void setIndex(int i) {
    if (_shellNavKey.currentState?.canPop() == true) {
      _shellNavKey.currentState!.popUntil((route) => route.isFirst);
    }
    _activatedTabs.add(i);
    if (i == _currentIndex) return;
    setState(() => _currentIndex = i);
    _indexNotifier.value = i;
  }

  Widget _buildTab(int i) {
    if (!_activatedTabs.contains(i)) {
      return const SizedBox.shrink();
    }
    switch (i) {
      case 0:
        return SessionStore.isAdmin ? const AdminDashboardScreen() : const HomeScreen();
      case 1:
        return const RequestsScreen();
      case 2:
        return const CreateRequestScreen();
      case 3:
        return const NotificationsScreen();
      case 4:
        return const ProfileScreen();
      default:
        return const SizedBox.shrink();
    }
  }

  Widget _buildTabContainer() {
    return Stack(
      children: [
        ValueListenableBuilder<int>(
          valueListenable: _indexNotifier,
          builder: (context2, index, child2) => IndexedStack(
            index: index,
            children: [
              _buildTab(0),
              _buildTab(1),
              _buildTab(2),
              _buildTab(3),
              _buildTab(4),
            ],
          ),
        ),
        const FloatingMessageButton(bottom: 18),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFE8ECF4),
      bottomNavigationBar: AppBottomNav(
        currentIndex: _currentIndex,
        onTap: setIndex,
      ),
      body: PopScope(
        canPop: false,
        onPopInvokedWithResult: (didPop, _) {
          if (didPop) return;
          if (_shellNavKey.currentState?.canPop() == true) {
            _shellNavKey.currentState!.pop();
          } else if (_currentIndex != 0) {
            setIndex(0);
          } else {
            SystemNavigator.pop();
          }
        },
        child: Navigator(
          key: _shellNavKey,
          onGenerateRoute: (settings) {
            if (settings.name == '/messages') {
              return MaterialPageRoute(
                builder: (_) => const MessagesScreen(),
              );
            }
            if (settings.name == '/calendar') {
              return MaterialPageRoute(
                builder: (_) => const PostCalendarScreen(),
              );
            }
            return MaterialPageRoute(
              builder: (_) => _buildTabContainer(),
            );
          },
        ),
      ),
    );
  }
}
