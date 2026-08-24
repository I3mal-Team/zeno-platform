import 'package:flutter/material.dart';

import '../styles/app_colors.dart';
import '../styles/app_dimensions.dart';
import '../styles/app_text_styles.dart';

abstract final class AppToast {
  static void error(BuildContext context, String message) =>
      _show(context, message, AppColors.errorFg);

  static void success(BuildContext context, String message) =>
      _show(context, message, AppColors.successFg);

  static void info(BuildContext context, String message) =>
      _show(context, message, AppColors.amber);

  static void _show(BuildContext context, String message, Color accent) {
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(
          content: Text(
            message,
            style: AppTextStyles.bodyMd.copyWith(color: AppColors.textOnDark),
          ),
          backgroundColor: AppColors.charcoalSoft,
          behavior: SnackBarBehavior.floating,
          duration: AppDimensions.toastDuration,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(AppDimensions.radiusControl),
          ),
        ),
      );
  }
}
