import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../features/employer/presentation/manager/applicants_cubit/applicants_cubit.dart';
import '../../features/employer/presentation/manager/employer_jobs_cubit/employer_jobs_cubit.dart';
import '../../features/employer/presentation/manager/employer_profile_cubit/employer_profile_cubit.dart';
import '../../features/chat/presentation/manager/conversations_cubit/conversations_cubit.dart';
import '../motion/motion.dart';
import '../routing/routes_keys.dart';
import '../styles/app_colors.dart';
import '../styles/app_shadows.dart';
import '../styles/app_text_styles.dart';

class EmployerShell extends StatelessWidget {
  const EmployerShell({required this.shell, super.key});

  final StatefulNavigationShell shell;

  /// The three branch tabs in the main pill; a publish action and the account
  /// pill sit beside them, matching the design's employer nav.
  static const _main = [
    (icon: Icons.work_rounded, label: 'وظائفي'),
    (icon: Icons.people_alt_rounded, label: 'المتقدمون'),
    (icon: Icons.forum_rounded, label: 'الرسائل'),
  ];

  static const _accountIndex = 3;

  /// Switch tab and refresh that tab's data, so returning to a list always
  /// shows the latest (a new application, a sent message, an edited profile).
  void _go(BuildContext context, int index) {
    shell.goBranch(index, initialLocation: index == shell.currentIndex);
    _reload(context, index);
  }

  void _reload(BuildContext context, int index) {
    switch (index) {
      case 0:
        context.read<EmployerJobsCubit>().load();
      case 1:
        context.read<ApplicantsCubit>().load();
      case 2:
        context.read<ConversationsCubit>().load();
      case _accountIndex:
        context.read<EmployerProfileCubit>().load();
    }
  }

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
                  active: shell.currentIndex == _accountIndex,
                  onTap: () => _go(context, _accountIndex),
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
                            onTap: () => _go(context, index),
                          ),
                        ),
                      Expanded(
                        child: _PublishItem(
                          onTap: () async {
                            await context.push(RoutesKeys.employerPostJob);
                            if (!context.mounted) return;
                            // Land on "my jobs" and refresh so the new listing
                            // shows immediately.
                            shell.goBranch(0);
                            context.read<EmployerJobsCubit>().load();
                          },
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

/// The amber "نشر" action — publishes a new listing rather than switching tabs.
class _PublishItem extends StatelessWidget {
  const _PublishItem({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 2),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 38,
              height: 26,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: AppColors.amber,
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(
                Icons.add_rounded,
                size: 19,
                color: AppColors.textStrong,
              ),
            ),
            const SizedBox(height: 3),
            Text(
              'نشر',
              style: AppTextStyles.navLabel.copyWith(
                fontSize: 9.5,
                fontWeight: FontWeight.w800,
                color: AppColors.amber,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
