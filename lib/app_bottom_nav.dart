import 'package:flutter/material.dart';

/// Shared bottom navigation bar used by all screens.
/// [currentIndex]: 0=Home, 1=Requests, 2=Create, 3=Notifications, 4=Profile
class AppBottomNav extends StatelessWidget {
  final int currentIndex;
  const AppBottomNav({super.key, required this.currentIndex});

  void _navigate(BuildContext context, int index) {
    if (currentIndex >= 0 && index == currentIndex) return;
    switch (index) {
      case 0:
        Navigator.pushNamedAndRemoveUntil(context, '/home', (_) => false);
        break;
      case 1:
        Navigator.pushReplacementNamed(context, '/requests');
        break;
      case 2:
        Navigator.pushReplacementNamed(context, '/create');
        break;
      case 3:
        Navigator.pushReplacementNamed(context, '/notifications');
        break;
      case 4:
        Navigator.pushReplacementNamed(context, '/profile');
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    final bottomPad = MediaQuery.of(context).padding.bottom;
    return Container(
      height: 75 + bottomPad,
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(top: BorderSide(color: Color(0xFFE5E7EB))),
        boxShadow: [
          BoxShadow(
            color: Color(0x1A000000),
            blurRadius: 15,
            offset: Offset(0, -3),
          ),
        ],
      ),
      padding: EdgeInsets.only(bottom: bottomPad),
      child: Row(
        children: [
          _NavItem(
            label: 'Home',
            icon: Icons.home_outlined,
            isActive: currentIndex == 0,
            onTap: () => _navigate(context, 0),
          ),
          _NavItem(
            label: 'Requests',
            icon: Icons.description_outlined,
            isActive: currentIndex == 1,
            onTap: () => _navigate(context, 1),
          ),
          // Center Create FAB
          Expanded(
            child: GestureDetector(
              onTap: () => _navigate(context, 2),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: const BoxDecoration(
                      color: Color(0xFF003366),
                      shape: BoxShape.circle,
                      boxShadow: [
                        BoxShadow(
                          color: Color(0x1A000000),
                          blurRadius: 15,
                          offset: Offset(0, 4),
                        ),
                      ],
                    ),
                    child: const Icon(Icons.add, color: Colors.white, size: 28),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    'Create',
                    style: TextStyle(
                      fontFamily: 'Inter',
                      fontWeight: currentIndex == 2
                          ? FontWeight.w600
                          : FontWeight.w500,
                      fontSize: 10.9,
                      color: const Color(0xFF003366),
                    ),
                  ),
                ],
              ),
            ),
          ),
          _NavItem(
            label: 'Notifications',
            icon: Icons.notifications_outlined,
            isActive: currentIndex == 3,
            onTap: () => _navigate(context, 3),
          ),
          _NavItem(
            label: 'Profile',
            icon: Icons.person_outline,
            isActive: currentIndex == 4,
            onTap: () => _navigate(context, 4),
          ),
        ],
      ),
    );
  }
}

class _NavItem extends StatelessWidget {
  final String label;
  final IconData icon;
  final bool isActive;
  final VoidCallback onTap;

  const _NavItem({
    required this.label,
    required this.icon,
    required this.isActive,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final color = isActive ? const Color(0xFF003366) : const Color(0xFF99A1AF);
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        behavior: HitTestBehavior.opaque,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 22, color: color),
            const SizedBox(height: 3),
            Text(
              label,
              style: TextStyle(
                fontFamily: 'Inter',
                fontWeight: isActive ? FontWeight.w600 : FontWeight.w400,
                fontSize: 10.9,
                color: color,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
