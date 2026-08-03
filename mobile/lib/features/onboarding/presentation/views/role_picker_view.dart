import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/components/screen_background.dart';
import '../../../../core/motion/motion.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_dimensions.dart';
import '../../../../core/styles/app_shadows.dart';
import '../../../../core/styles/app_text_styles.dart';

/// The admin entry the prototype put here is deliberately absent: the admin
/// console is web-only, behind its own guard.
class RolePickerView extends StatelessWidget {
  const RolePickerView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: ScreenBackground(
        child: SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(24, 28, 24, 30),
            child: Column(
              children: [
                Expanded(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Image.asset('assets/images/zeno-logo.png', width: 224),
                      const SizedBox(height: AppDimensions.space16),
                      SizedBox(
                        width: 250,
                        child: Text(
                          'منصة الوظائف التشغيلية والخدمية والموسمية — الفرصة الأقرب إليك.',
                          textAlign: TextAlign.center,
                          style: AppTextStyles.bodyMd.copyWith(
                            fontSize: 14.5,
                            color: AppColors.textMuted,
                            height: 1.65,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                Align(
                  alignment: AlignmentDirectional.centerStart,
                  child: Padding(
                    padding: const EdgeInsetsDirectional.only(
                      start: AppDimensions.space6,
                      bottom: AppDimensions.space12,
                    ),
                    child: Text(
                      'اختر طريقة الدخول',
                      style: AppTextStyles.caption.copyWith(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w800,
                        color: const Color(0xFF869089),
                      ),
                    ),
                  ),
                ),
                Entrance(
                  index: 0,
                  child: _RoleCard(
                    title: 'أبحث عن عمل',
                    subtitle: 'تصفّح الوظائف القريبة وقدّم بسهولة',
                    icon: Icons.person_search_rounded,
                    iconColor: const Color(0xFFFFFFFF),
                    discGradient: const [Color(0xFF3E6B54), Color(0xFF284C3D)],
                    glowColor: AppColors.amber,
                    borderColor: const Color(0xFFEDF1EC),
                    onTap: () =>
                        context.push('${RoutesKeys.phone}?role=candidate'),
                  ),
                ),
                const SizedBox(height: AppDimensions.space12),
                Entrance(
                  index: 1,
                  child: _RoleCard(
                    title: 'أبحث عن موظفين',
                    subtitle: 'انشر وظيفة وتواصل مع المرشحين',
                    icon: Icons.work_rounded,
                    iconColor: AppColors.amber,
                    discGradient: const [Color(0xFF2E4A3C), Color(0xFF1E3A2E)],
                    glowColor: AppColors.charcoalSoft,
                    borderColor: const Color(0xFFE5EAE6),
                    onTap: () =>
                        context.push('${RoutesKeys.phone}?role=employer'),
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

class _RoleCard extends StatelessWidget {
  const _RoleCard({
    required this.title,
    required this.subtitle,
    required this.icon,
    required this.iconColor,
    required this.discGradient,
    required this.glowColor,
    required this.borderColor,
    required this.onTap,
  });

  final String title;
  final String subtitle;
  final IconData icon;
  final Color iconColor;
  final List<Color> discGradient;
  final Color glowColor;
  final Color borderColor;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(17),
        decoration: BoxDecoration(
          color: AppColors.surface,
          border: Border.all(color: borderColor),
          borderRadius: BorderRadius.circular(24),
          boxShadow: AppShadows.card,
        ),
        child: Row(
          children: [
            _RoleDisc(
              icon: icon,
              iconColor: iconColor,
              gradient: discGradient,
              ringColor: glowColor.withValues(alpha: .3),
            ),
            const SizedBox(width: AppDimensions.space16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: AppTextStyles.titleMd),
                  const SizedBox(height: AppDimensions.space2),
                  Text(
                    subtitle,
                    style: AppTextStyles.caption.copyWith(
                      fontWeight: FontWeight.w500,
                      height: 1.5,
                    ),
                  ),
                ],
              ),
            ),
            const Icon(
              Icons.chevron_left_rounded,
              size: 26,
              color: Color(0xFFBAC4BD),
            ),
          ],
        ),
      ),
    );
  }
}

class _RoleDisc extends StatelessWidget {
  const _RoleDisc({
    required this.icon,
    required this.iconColor,
    required this.gradient,
    required this.ringColor,
  });

  final IconData icon;
  final Color iconColor;
  final List<Color> gradient;
  final Color ringColor;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 58,
      height: 58,
      child: Stack(
        clipBehavior: Clip.none,
        alignment: Alignment.center,
        children: [
          Positioned(
            width: 78,
            height: 78,
            child: DecoratedBox(
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(color: ringColor, width: 1.5),
              ),
            ),
          ),
          DecoratedBox(
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: RadialGradient(
                center: const Alignment(-.36, -.44),
                colors: gradient,
              ),
            ),
            child: const SizedBox(width: 58, height: 58),
          ),
          Positioned(
            top: 1,
            left: 7,
            child: Container(
              width: 8,
              height: 8,
              decoration: const BoxDecoration(
                shape: BoxShape.circle,
                color: AppColors.amber,
              ),
            ),
          ),
          Icon(icon, size: 27, color: iconColor),
        ],
      ),
    );
  }
}
