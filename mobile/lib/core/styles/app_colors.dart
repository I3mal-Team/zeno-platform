import 'package:flutter/material.dart';

/// Every colour in the app. Features must not declare `Color(0xFF...)`.
///
/// Brand palette: a deep green primary (`charcoal*` — the dark surfaces and
/// primary buttons) with a refined gold accent (`amber*`). The token names are
/// kept for continuity even though the values are now green/gold.
abstract final class AppColors {
  static const amber = Color(0xFFC9A24B);

  static const amberDeep = Color(0xFFB88A2E);

  static const amberGradient = LinearGradient(
    begin: Alignment.topRight,
    end: Alignment.bottomLeft,
    colors: [amber, amberDeep],
  );

  static const charcoal = Color(0xFF22302A);
  static const charcoalSoft = Color(0xFF284C3D);

  static const charcoalGradient = LinearGradient(
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
    colors: [Color(0xFF315C4A), Color(0xFF1E3A2E)],
  );

  static const paper = Color(0xFFF4F6F3);

  static const surface = Color(0xFFFFFFFF);

  static const border = Color(0xFFE5EAE6);
  static const borderStrong = Color(0xFFDCE3DD);

  static const textStrong = Color(0xFF22302A);

  static const textBody = Color(0xFF566159);

  static const textMuted = Color(0xFF7E8B84);

  static const textOnDark = Color(0xFFFFFFFF);
  static const textOnDarkMuted = Color(0xFFB8C4BC);

  static const successFg = Color(0xFF1F7A3D);
  static const successBg = Color(0xFFE3F3E8);

  static const warningFg = Color(0xFF8A6D12);
  static const warningBg = Color(0xFFF3ECD6);

  static const errorFg = Color(0xFFB23232);
  static const errorBg = Color(0xFFFBE6E6);

  static const infoFg = Color(0xFF2E6E8A);
  static const infoBg = Color(0xFFE2EEF4);

  static const neutralFg = Color(0xFF5B6470);
  static const neutralBg = Color(0xFFEEF1F4);

  static const whatsapp = Color(0xFF25D366);
  static const verified = Color(0xFF2E86C1);

  /// Keyed by the category code returned from the API.
  static const categoryTints = <String, (Color, Color)>{
    'restaurants': (Color(0xFF8A6D12), Color(0xFFFDF1CC)),
    'cleaning': (Color(0xFF4F7A2E), Color(0xFFE6F0E1)),
    'logistics': (Color(0xFFB2453A), Color(0xFFF8E3E1)),
    'events': (Color(0xFF6A4E8A), Color(0xFFECE6F4)),
    'retail': (Color(0xFF2E6E8A), Color(0xFFE2EEF4)),
    'security': (Color(0xFF2E7A6B), Color(0xFFE0EFEC)),
    'operations': (Color(0xFF2E5A8A), Color(0xFFE2EAF4)),
    'crafts': (Color(0xFF8A4E2E), Color(0xFFF4E8E2)),
    'seasonal_work': (Color(0xFF2E7A8A), Color(0xFFE0EDF0)),
    'other': (Color(0xFF5A554C), Color(0xFFEDEAE3)),
  };

  static (Color, Color) categoryTint(String code) =>
      categoryTints[code] ?? categoryTints['other']!;
}
