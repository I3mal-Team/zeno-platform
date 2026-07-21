import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../styles/app_colors.dart';
import '../styles/app_dimensions.dart';
import '../styles/app_text_styles.dart';

class CandidateShell extends StatelessWidget {
  const CandidateShell({required this.shell, super.key});

  final StatefulNavigationShell shell;

  static const _items = [
    (icon: Icons.home_rounded, label: 'الرئيسية'),
    (icon: Icons.my_location_rounded, label: 'القريبة'),
    (icon: Icons.description_rounded, label: 'طلباتي'),
    (icon: Icons.forum_rounded, label: 'الرسائل'),
    (icon: Icons.person_rounded, label: 'حسابي'),
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: shell,
      bottomNavigationBar: Container(
        decoration: const BoxDecoration(
          color: AppColors.charcoalSoft,
          borderRadius: BorderRadius.vertical(
            top: Radius.circular(AppDimensions.radiusSheet),
          ),
        ),
        child: SafeArea(
          top: false,
          child: SizedBox(
            height: 68,
            child: Row(
              children: [
                for (final (index, item) in _items.indexed)
                  Expanded(
                    child: _NavItem(
                      icon: item.icon,
                      label: item.label,
                      isActive: shell.currentIndex == index,
                      onTap: () => shell.goBranch(
                        index,
                        initialLocation: index == shell.currentIndex,
                      ),
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

class _NavItem extends StatelessWidget {
  const _NavItem({
    required this.icon,
    required this.label,
    required this.isActive,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final bool isActive;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final color = isActive ? AppColors.amber : AppColors.textOnDarkMuted;

    return InkWell(
      onTap: onTap,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, size: AppDimensions.iconLg, color: color),
          const SizedBox(height: AppDimensions.space4),
          Text(label, style: AppTextStyles.navLabel.copyWith(color: color)),
        ],
      ),
    );
  }
}
