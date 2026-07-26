import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../motion/motion.dart';
import '../styles/app_colors.dart';
import '../styles/app_dimensions.dart';
import '../styles/app_text_styles.dart';

/// The header used across the app: a rounded back box on the start edge, with
/// either the logo, a centered title, or custom trailing actions — matching the
/// design's screen headers. Falls back to `context.pop()` when [onBack] is null.
class AppTopBar extends StatelessWidget {
  const AppTopBar({
    this.title,
    this.trailing,
    this.showLogo = false,
    this.showBack = true,
    this.onBack,
    super.key,
  });

  /// Shown centered between the back box and the trailing slot.
  final String? title;

  /// Custom trailing widget (e.g. an action button). Ignored when [showLogo].
  final Widget? trailing;

  /// Shows the zeno logo on the end edge.
  final bool showLogo;
  final bool showBack;
  final VoidCallback? onBack;

  @override
  Widget build(BuildContext context) {
    final trailingWidget = showLogo
        ? Image.asset('assets/images/zeno-logo.png', height: 30)
        : trailing;

    return Row(
      children: [
        if (showBack)
          _BackBox(onTap: onBack ?? () => context.pop())
        else
          const SizedBox(width: AppDimensions.iconButtonSize),
        if (title != null)
          Expanded(
            child: Text(
              title!,
              textAlign: TextAlign.center,
              style: AppTextStyles.titleMd,
            ),
          )
        else
          const Spacer(),
        trailingWidget ??
            const SizedBox(width: AppDimensions.iconButtonSize),
      ],
    );
  }
}

class _BackBox extends StatelessWidget {
  const _BackBox({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Container(
        width: AppDimensions.iconButtonSize,
        height: AppDimensions.iconButtonSize,
        decoration: BoxDecoration(
          color: AppColors.surface,
          border: Border.all(color: const Color(0xFFECEAE3)),
          borderRadius: BorderRadius.circular(13),
        ),
        // chevron_right is the "back" direction in this RTL flow.
        child: const Icon(
          Icons.chevron_right_rounded,
          size: 24,
          color: AppColors.textStrong,
        ),
      ),
    );
  }
}
