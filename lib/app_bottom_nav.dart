import 'package:flutter/material.dart';
import 'services/session_store.dart';

/// Shared bottom navigation bar used by all screens.
/// [currentIndex]: 0=Home, 1=Requests, 2=Create, 3=Notifications, 4=Profile
class AppBottomNav extends StatelessWidget {
  final int currentIndex;
  final ValueChanged<int>? onTap;

  const AppBottomNav({
    super.key,
    required this.currentIndex,
    this.onTap,
  });

  void _navigate(BuildContext context, int index) {
    if (onTap != null) {
      onTap!(index);
      return;
    }
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
    final isAdmin = SessionStore.isAdmin;

    return Material(
      color: Colors.white,
      elevation: 12,
      child: SafeArea(
        top: false,
        child: Container(
          height: 62,
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: const BorderRadius.only(
              topLeft: Radius.circular(24),
              topRight: Radius.circular(24),
            ),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF001540).withOpacity(0.08),
                blurRadius: 16,
                offset: const Offset(0, -4),
              ),
            ],
          ),
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
              if (!isAdmin)
                _CreateButton(
                  isActive: currentIndex == 2,
                  onTap: () => _navigate(context, 2),
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
    final color = widget.isActive ? _activeColor : _inactiveColor;

    return Expanded(
      child: InkWell(
        onTap: widget.onTap,
        splashColor: Colors.transparent,
        highlightColor: Colors.transparent,
        child: ScaleTransition(
          scale: _scaleAnim,
          child: Align(
            alignment: Alignment.center,
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              mainAxisSize: MainAxisSize.min,
              children: [
                AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  width: widget.isActive ? 42 : 28,
                  height: 24,
                  decoration: BoxDecoration(
                    color: widget.isActive
                        ? const Color(0xFF002366).withOpacity(0.09)
                        : Colors.transparent,
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Center(
                    child: Icon(
                      widget.isActive ? widget.icon : widget.iconOff,
                      size: 19,
                      color: color,
                    ),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  widget.label,
                  style: TextStyle(
                    fontFamily: 'DM Sans',
                    fontWeight:
                        widget.isActive ? FontWeight.w800 : FontWeight.w500,
                    fontSize: 9.0,
                    color: color,
                  ),
                ),
                const SizedBox(height: 2),
                AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  width: widget.isActive ? 4 : 0,
                  height: widget.isActive ? 4 : 0,
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
    return Expanded(
      child: InkWell(
        onTap: widget.onTap,
        splashColor: Colors.transparent,
        highlightColor: Colors.transparent,
        child: ScaleTransition(
          scale: _scaleAnim,
          child: Align(
            alignment: Alignment.center,
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                      colors: [Color(0xFF001540), Color(0xFF0032A0)],
                    ),
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF001540).withOpacity(0.35),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.add_rounded,
                    color: Colors.white,
                    size: 24,
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
