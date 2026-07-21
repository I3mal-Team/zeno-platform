import 'package:flutter/material.dart';

import 'app_colors.dart';

/// Every text style. Features must not build `TextStyle` inline.
abstract final class AppTextStyles {
  static const fontFamily = 'Tajawal';

  /// Arabic needs more leading than the Latin default.
  static const _arabicHeight = 1.55;

  static const TextStyle _base = TextStyle(
    fontFamily: fontFamily,
    height: _arabicHeight,
    color: AppColors.textStrong,
  );

  static final displayLg = _base.copyWith(
    fontSize: 22,
    fontWeight: FontWeight.w900,
    letterSpacing: -0.2,
    height: 1.35,
  );

  static final titleLg = _base.copyWith(
    fontSize: 20,
    fontWeight: FontWeight.w800,
    height: 1.4,
  );

  static final titleMd = _base.copyWith(
    fontSize: 17,
    fontWeight: FontWeight.w800,
    height: 1.4,
  );

  static final titleSm = _base.copyWith(
    fontSize: 15,
    fontWeight: FontWeight.w700,
  );

  static final bodyLg = _base.copyWith(
    fontSize: 15,
    fontWeight: FontWeight.w500,
    color: AppColors.textBody,
  );

  static final bodyMd = _base.copyWith(
    fontSize: 14,
    fontWeight: FontWeight.w500,
    color: AppColors.textBody,
  );

  static final bodySm = _base.copyWith(
    fontSize: 13,
    fontWeight: FontWeight.w500,
    color: AppColors.textBody,
  );

  /// The smallest size the design allows.
  static final caption = _base.copyWith(
    fontSize: 12,
    fontWeight: FontWeight.w700,
    color: AppColors.textMuted,
    height: 1.4,
  );

  static final badge = _base.copyWith(
    fontSize: 12,
    fontWeight: FontWeight.w700,
    height: 1.2,
  );

  static final button = _base.copyWith(
    fontSize: 16,
    fontWeight: FontWeight.w800,
    height: 1.2,
  );

  static final input = _base.copyWith(
    fontSize: 15,
    fontWeight: FontWeight.w600,
  );

  static final otpDigit = _base.copyWith(
    fontSize: 24,
    fontWeight: FontWeight.w800,
    height: 1.2,
  );

  static final navLabel = _base.copyWith(
    fontSize: 11,
    fontWeight: FontWeight.w700,
    height: 1.2,
  );
}
