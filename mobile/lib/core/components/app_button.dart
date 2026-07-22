import 'package:flutter/material.dart';

import '../motion/motion.dart';
import '../styles/app_colors.dart';
import '../styles/app_dimensions.dart';
import '../styles/app_shadows.dart';
import '../styles/app_text_styles.dart';

/// The primary call-to-action, matching the design: a tall amber pill with an
/// amber glow, a directional forward arrow, and a press-scale response. Pass
/// [dark] for the charcoal-on-amber-text variant.
class AppButton extends StatelessWidget {
  const AppButton({
    required this.label,
    required this.onPressed,
    this.isLoading = false,
    this.icon = Icons.arrow_forward_rounded,
    this.dark = false,
    super.key,
  });

  final String label;
  final VoidCallback? onPressed;
  final bool isLoading;

  /// The trailing glyph; flips with text direction. Null hides it.
  final IconData? icon;

  /// The charcoal-background, amber-text variant.
  final bool dark;

  @override
  Widget build(BuildContext context) {
    final isEnabled = onPressed != null && !isLoading;
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    final background = dark ? AppColors.charcoalSoft : AppColors.amber;
    final foreground = dark ? AppColors.amber : AppColors.textStrong;
    final glow = dark ? AppShadows.charcoalGlow : AppShadows.amberGlow;

    return Pressable(
      onTap: isEnabled ? onPressed : null,
      child: Container(
        height: AppDimensions.buttonHeight,
        width: double.infinity,
        decoration: BoxDecoration(
          color: isEnabled ? background : AppColors.border,
          borderRadius: BorderRadius.circular(AppDimensions.radiusButton),
          boxShadow: isEnabled ? glow : null,
        ),
        alignment: Alignment.center,
        child: isLoading
            ? SizedBox(
                width: AppDimensions.iconMd,
                height: AppDimensions.iconMd,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  color: foreground,
                ),
              )
            : Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    label,
                    style: AppTextStyles.button.copyWith(
                      color: isEnabled ? foreground : AppColors.textMuted,
                    ),
                  ),
                  if (icon != null) ...[
                    const SizedBox(width: AppDimensions.space8),
                    Transform.flip(
                      flipX: isRtl,
                      child: Icon(
                        icon,
                        size: AppDimensions.iconMd,
                        color: isEnabled ? foreground : AppColors.textMuted,
                      ),
                    ),
                  ],
                ],
              ),
      ),
    );
  }
}
