import 'package:flutter/material.dart';

import 'app_colors.dart';

/// المصدر الوحيد لأنماط النص.
///
/// الخط **Tajawal** بأوزان 400/500/700/800/900 — مقاسات الموبايل من
/// `design/HANDOVER.md §4.2`. ممنوع `TextStyle(...)` سطرياً في الميزات.
///
/// كل الأنماط تُبنى من [_base] لضمان توحيد الخط وارتفاع السطر العربي.
abstract final class AppTextStyles {
  static const fontFamily = 'Tajawal';

  /// ارتفاع سطر مناسب للعربية — أعلى من الافتراضي اللاتيني.
  static const _arabicHeight = 1.55;

  static const TextStyle _base = TextStyle(
    fontFamily: fontFamily,
    height: _arabicHeight,
    color: AppColors.textStrong,
  );

  // ── العناوين ─────────────────────────────────────
  /// عنوان شاشة كبير — 22/900.
  static final displayLg = _base.copyWith(
    fontSize: 22,
    fontWeight: FontWeight.w900,
    letterSpacing: -0.2,
    height: 1.35,
  );

  /// عنوان شاشة — 20/800.
  static final titleLg = _base.copyWith(
    fontSize: 20,
    fontWeight: FontWeight.w800,
    height: 1.4,
  );

  /// عنوان بطاقة — 17/800.
  static final titleMd = _base.copyWith(
    fontSize: 17,
    fontWeight: FontWeight.w800,
    height: 1.4,
  );

  /// عنوان فرعي — 15/700.
  static final titleSm = _base.copyWith(
    fontSize: 15,
    fontWeight: FontWeight.w700,
  );

  // ── المتن ────────────────────────────────────────
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

  // ── البيانات الوصفية ─────────────────────────────
  /// أصغر مقاس مسموح — لا تنزل تحته (HANDOVER §4.2).
  static final caption = _base.copyWith(
    fontSize: 12,
    fontWeight: FontWeight.w700,
    color: AppColors.textMuted,
    height: 1.4,
  );

  /// نص الشارات والرقائق.
  static final badge = _base.copyWith(
    fontSize: 12,
    fontWeight: FontWeight.w700,
    height: 1.2,
  );

  // ── عناصر التحكّم ────────────────────────────────
  static final button = _base.copyWith(
    fontSize: 16,
    fontWeight: FontWeight.w800,
    height: 1.2,
  );

  static final input = _base.copyWith(
    fontSize: 15,
    fontWeight: FontWeight.w600,
  );

  /// خانة رمز التحقّق — أرقام لاتينية، اتجاه محايد.
  static final otpDigit = _base.copyWith(
    fontSize: 24,
    fontWeight: FontWeight.w800,
    height: 1.2,
  );

  /// تسمية التنقّل السفلي.
  static final navLabel = _base.copyWith(
    fontSize: 11,
    fontWeight: FontWeight.w700,
    height: 1.2,
  );
}
