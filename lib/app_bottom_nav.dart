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
    final bottomPad = MediaQuery.of(context).viewPadding.bottom;

    return Hero(
      tag: 'app_bottom_nav',
      child: Material(
        type: MaterialType.transparency,
        child: Container(
          height: 68 + bottomPad,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: const BorderRadius.only(
              topLeft: Radius.circular(24),
              topRight: Radius.circular(24),
            ),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF001540).withOpacity(0.10),
                blurRadius: 24,
                spreadRadius: 0,
                offset: const Offset(0, -6),
              ),
              BoxShadow(
                color: const Color(0xFF001540).withOpacity(0.05),
                blurRadius: 8,
                offset: const Offset(0, -2),
              ),
            ],
          ),
          padding: EdgeInsets.only(bottom: bottomPad),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          _NavItem(
            label: 'Home',
            icon: Icons.home_rounded,
            iconOff: Icons.home_outlined,
            isActive: currentIndex == 0,
            onTap: () => _navigate(context, 0),
          ),
          _NavItem(
            label: 'Requests',
            icon: Icons.description_rounded,
            iconOff: Icons.description_outlined,
            isActive: currentIndex == 1,
            onTap: () => _navigate(context, 1),
          ),

          // ── Floating Create Button ──────────────────────────────────────
          Expanded(
            child: _CreateButton(
              isActive: currentIndex == 2,
              onTap: () => _navigate(context, 2),
            ),
          ),

          _NavItem(
            label: 'Alerts',
            icon: Icons.notifications_rounded,
            iconOff: Icons.notifications_outlined,
            isActive: currentIndex == 3,
            onTap: () => _navigate(context, 3),
          ),
          _NavItem(
            label: 'Profile',
            icon: Icons.person_rounded,
            iconOff: Icons.person_outline_rounded,
            isActive: currentIndex == 4,
            onTap: () => _navigate(context, 4),
          ),
        ],
      ),
    ),
  ),
);
}
}

// ── Nav Item ──────────────────────────────────────────────────────────────────
// Must be StatefulWidget so press scale animation and
// AnimatedContainer actually re-render when isActive changes.
class _NavItem extends StatefulWidget {
  final String label;
  final IconData icon;
  final IconData iconOff;
  final bool isActive;
  final VoidCallback onTap;

  const _NavItem({
    required this.label,
    required this.icon,
    required this.iconOff,
    required this.isActive,
    required this.onTap,
  });

  @override
  State<_NavItem> createState() => _NavItemState();
}

class _NavItemState extends State<_NavItem>
    with SingleTickerProviderStateMixin {
  late final AnimationController _pressCtrl;
  late final Animation<double> _scaleAnim;

  static const _activeColor = Color(0xFF002366);
  static const _inactiveColor = Color(0xFF9AA3B2);

  @override
  void initState() {
    super.initState();
    _pressCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 110),
      lowerBound: 0.0,
      upperBound: 1.0,
    );
    _scaleAnim = Tween<double>(
      begin: 1.0,
      end: 0.86,
    ).animate(CurvedAnimation(parent: _pressCtrl, curve: Curves.easeOut));
  }

  @override
  void dispose() {
    _pressCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final color = widget.isActive ? _activeColor : _inactiveColor;

    return Expanded(
      child: GestureDetector(
        behavior: HitTestBehavior.opaque,
        onTapDown: (_) => _pressCtrl.forward(),
        onTapUp: (_) {
          _pressCtrl.reverse();
          widget.onTap();
        },
        onTapCancel: () => _pressCtrl.reverse(),
        child: ScaleTransition(
          scale: _scaleAnim,
          child: SizedBox(
            height: double.infinity,
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // Pill highlight container behind the icon
                AnimatedContainer(
                  duration: const Duration(milliseconds: 250),
                  curve: Curves.easeOutCubic,
                  width: widget.isActive ? 46 : 32,
                  height: 30,
                  decoration: BoxDecoration(
                    color: widget.isActive
                        ? const Color(0xFF002366).withOpacity(0.09)
                        : Colors.transparent,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Center(
                    child: Icon(
                      widget.isActive ? widget.icon : widget.iconOff,
                      size: 22,
                      color: color,
                    ),
                  ),
                ),
                const SizedBox(height: 3),
                AnimatedDefaultTextStyle(
                  duration: const Duration(milliseconds: 200),
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: widget.isActive
                        ? FontWeight.w800
                        : FontWeight.w500,
                    fontSize: 9.5,
                    color: color,
                    letterSpacing: 0.2,
                  ),
                  child: Text(widget.label),
                ),
                const SizedBox(height: 3),
                // Active indicator dot
                AnimatedContainer(
                  duration: const Duration(milliseconds: 280),
                  curve: Curves.easeOutBack,
                  width: widget.isActive ? 5 : 0,
                  height: widget.isActive ? 5 : 0,
                  decoration: const BoxDecoration(
                    color: _activeColor,
                    shape: BoxShape.circle,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

// ── Create Button ─────────────────────────────────────────────────────────────
class _CreateButton extends StatefulWidget {
  final bool isActive;
  final VoidCallback onTap;

  const _CreateButton({required this.isActive, required this.onTap});

  @override
  State<_CreateButton> createState() => _CreateButtonState();
}

class _CreateButtonState extends State<_CreateButton>
    with SingleTickerProviderStateMixin {
  late final AnimationController _pressCtrl;
  late final Animation<double> _scaleAnim;

  @override
  void initState() {
    super.initState();
    _pressCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 130),
      lowerBound: 0.0,
      upperBound: 1.0,
    );
    _scaleAnim = Tween<double>(
      begin: 1.0,
      end: 0.88,
    ).animate(CurvedAnimation(parent: _pressCtrl, curve: Curves.easeOut));
  }

  @override
  void dispose() {
    _pressCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      behavior: HitTestBehavior.opaque,
      onTapDown: (_) => _pressCtrl.forward(),
      onTapUp: (_) {
        _pressCtrl.reverse();
        widget.onTap();
      },
      onTapCancel: () => _pressCtrl.reverse(),
      child: ScaleTransition(
        scale: _scaleAnim,
        child: SizedBox(
          height: double.infinity,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Lifts above the nav bar surface
              Transform.translate(
                offset: const Offset(0, -10),
                child: Container(
                  width: 54,
                  height: 54,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [Color(0xFF001540), Color(0xFF0032A0)],
                    ),
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF001540).withOpacity(0.45),
                        blurRadius: 20,
                        spreadRadius: 0,
                        offset: const Offset(0, 8),
                      ),
                      BoxShadow(
                        color: const Color(0xFF001540).withOpacity(0.18),
                        blurRadius: 6,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.add_rounded,
                    color: Colors.white,
                    size: 28,
                  ),
                ),
              ),
              // Label aligns just below the button
              Transform.translate(
                offset: const Offset(0, -8),
                child: Text(
                  'Create',
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight: widget.isActive
                        ? FontWeight.w800
                        : FontWeight.w700,
                    fontSize: 9.5,
                    color: const Color(0xFF002366),
                    letterSpacing: 0.2,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
