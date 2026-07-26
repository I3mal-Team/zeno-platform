import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../motion/motion.dart';
import '../styles/app_colors.dart';
import '../styles/app_shadows.dart';
import '../styles/app_text_styles.dart';

class CandidateShell extends StatelessWidget {
  const CandidateShell({required this.shell, super.key});

  final StatefulNavigationShell shell;

  /// The four tabs in the main pill; the profile tab sits in its own pill.
  static const _main = [
    (icon: Icons.home_rounded, label: 'الرئيسية'),
    (icon: Icons.my_location_rounded, label: 'القريبة'),
    (icon: Icons.description_rounded, label: 'طلباتي'),
    (icon: Icons.forum_rounded, label: 'الرسائل'),
  ];

  static const _profileIndex = 4;

  void _go(int index) =>
      shell.goBranch(index, initialLocation: index == shell.currentIndex);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      extendBody: true,
      body: shell,
      bottomNavigationBar: Padding(
        padding: const EdgeInsets.fromLTRB(14, 0, 14, 16),
        child: SafeArea(
          top: false,
          child: Row(
            children: [
              _Pill(
                width: 58,
                child: _NavItem(
                  icon: Icons.person_rounded,
                  label: 'حسابي',
                  active: shell.currentIndex == _profileIndex,
                  onTap: () => _go(_profileIndex),
                ),
              ),
              const SizedBox(width: 9),
              Expanded(
                child: _Pill(
                  child: Row(
                    children: [
                      for (final (index, item) in _main.indexed)
                        Expanded(
                          child: _NavItem(
                            icon: item.icon,
                            label: item.label,
                            active: shell.currentIndex == index,
                            onTap: () => _go(index),
                          ),
                        ),
                    ],
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

class _Pill extends StatelessWidget {
  const _Pill({required this.child, this.width});

  final Widget child;
  final double? width;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: width,
      padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
      decoration: BoxDecoration(
        color: AppColors.charcoalSoft,
        border: Border.all(color: const Color(0xFF3A332E)),
        borderRadius: BorderRadius.circular(19),
        boxShadow: AppShadows.charcoalGlow,
      ),
      child: child,
    );
  }
}

class _NavItem extends StatelessWidget {
  const _NavItem({
    required this.icon,
    required this.label,
    required this.active,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final bool active;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final color = active ? AppColors.amber : AppColors.textOnDarkMuted;

    return Pressable(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 2),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            AnimatedScale(
              scale: active ? 1.1 : 1,
              duration: AppMotion.press,
              child: Icon(icon, size: 20, color: color),
            ),
            const SizedBox(height: 3),
            Text(
              label,
              style: AppTextStyles.navLabel.copyWith(
                fontSize: 9.5,
                fontWeight: FontWeight.w800,
                color: color,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
