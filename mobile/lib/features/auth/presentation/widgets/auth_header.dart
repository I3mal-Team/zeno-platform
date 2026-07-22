import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_dimensions.dart';

/// The auth-flow header: a rounded back box on the start side and the logo on
/// the other, matching the phone and OTP screens in the design.
class AuthHeader extends StatelessWidget {
  const AuthHeader({super.key});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        _BackBox(onTap: () => context.pop()),
        Image.asset('assets/images/zeno-logo.png', height: 30),
      ],
    );
  }
}

class _BackBox extends StatelessWidget {
  const _BackBox({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(AppDimensions.radiusControl),
      child: Container(
        width: 42,
        height: 42,
        decoration: BoxDecoration(
          color: AppColors.surface,
          border: Border.all(color: AppColors.border),
          borderRadius: BorderRadius.circular(AppDimensions.radiusControl),
        ),
        // chevron_right points toward the start edge in this RTL flow — the
        // "back" direction the design uses.
        child: const Icon(
          Icons.chevron_right_rounded,
          size: 24,
          color: AppColors.textStrong,
        ),
      ),
    );
  }
}
