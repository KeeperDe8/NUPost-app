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
import 'widgets/sla_guidelines_sheet.dart';
import 'services/in_app_notification_service.dart';

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
  final Map<int, Widget> _cachedTabs = {};

  @override
  void initState() {
    super.initState();
    _activatedTabs.add(widget.initialIndex);
    _indexNotifier = ValueNotifier<int>(widget.initialIndex);
    if (SessionStore.isLoggedIn) {
      InAppNotificationService.startListening();
    }
  }

  @override
  void dispose() {
    InAppNotificationService.stopListening();
    _indexNotifier.dispose();
    super.dispose();
  }

  void setIndex(int i) async {
    if (i == 2 && !SessionStore.isAdmin) {
      final shouldShow = await SessionStore.shouldShowSlaNotice();
      if (shouldShow && mounted) {
        final proceed = await SlaGuidelinesSheet.show(context);
        if (proceed != true) return;
      }
    }

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
    if (_cachedTabs.containsKey(i)) {
      return _cachedTabs[i]!;
    }

    Widget tabWidget;
    switch (i) {
      case 0:
        tabWidget = SessionStore.isAdmin ? const AdminDashboardScreen() : const HomeScreen();
        break;
      case 1:
        tabWidget = const RequestsScreen();
        break;
      case 2:
        tabWidget = const CreateRequestScreen();
        break;
      case 3:
        tabWidget = const NotificationsScreen();
        break;
      case 4:
        tabWidget = const ProfileScreen();
        break;
      default:
        tabWidget = const SizedBox.shrink();
    }

    _cachedTabs[i] = tabWidget;
    return tabWidget;
  }

  Widget _buildTabContainer() {
    return Stack(
      children: [
        ValueListenableBuilder<int>(
          valueListenable: _indexNotifier,
          builder: (context2, index, child2) {
            return AnimatedSwitcher(
              duration: const Duration(milliseconds: 240),
              switchInCurve: Curves.easeOutCubic,
              switchOutCurve: Curves.easeInCubic,
              transitionBuilder: (child, anim) {
                return FadeTransition(
                  opacity: anim,
                  child: child,
                );
              },
              child: KeyedSubtree(
                key: ValueKey<int>(index),
                child: _buildTab(index),
              ),
            );
          },
        ),
        const FloatingMessageButton(bottom: 18),
      ],
    );
  }

  PageRouteBuilder _buildPageRoute(Widget page) {
    return PageRouteBuilder(
      pageBuilder: (_, __, ___) => page,
      transitionsBuilder: (_, anim, secAnim, child) {
        const begin = Offset(0.06, 0);
        const end = Offset.zero;
        final curve = CurvedAnimation(parent: anim, curve: Curves.easeOutCubic);
        return SlideTransition(
          position: Tween(begin: begin, end: end).animate(curve),
          child: FadeTransition(opacity: curve, child: child),
        );
      },
      transitionDuration: const Duration(milliseconds: 260),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final scaffoldBg = isDark ? const Color(0xFF0A0F1D) : const Color(0xFFE8ECF4);

    return Scaffold(
      backgroundColor: scaffoldBg,
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
              return _buildPageRoute(const MessagesScreen());
            }
            if (settings.name == '/calendar') {
              return _buildPageRoute(const PostCalendarScreen());
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
