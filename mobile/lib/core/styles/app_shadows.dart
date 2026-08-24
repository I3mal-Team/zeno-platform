import 'package:flutter/widgets.dart';

import 'app_colors.dart';

/// The design's elevation language. Every shadow in the app is one of these,
/// so surfaces lift consistently. Values mirror the box-shadows in the design.
abstract final class AppShadows {
  /// Soft lift under cards — `0 18px 38px -24px rgba(40,35,25,.42)`.
  static const card = [
    BoxShadow(
      color: Color(0x0F282319),
      blurRadius: 30,
      spreadRadius: -18,
      offset: Offset(0, 16),
    ),
  ];

  /// Tight lift under chips and small controls — `0 10px 22px -18px …`.
  static const chip = [
    BoxShadow(
      color: Color(0x0A282319),
      blurRadius: 20,
      spreadRadius: -14,
      offset: Offset(0, 10),
    ),
  ];

  /// The amber glow beneath the primary call-to-action.
  static final amberGlow = [
    BoxShadow(
      color: AppColors.amber.withValues(alpha: 0.5),
      blurRadius: 26,
      spreadRadius: -12,
      offset: const Offset(0, 16),
    ),
  ];

  /// The charcoal glow beneath dark buttons and the active nav pill.
  static final charcoalGlow = [
    BoxShadow(
      color: AppColors.charcoalSoft.withValues(alpha: 0.5),
      blurRadius: 26,
      spreadRadius: -12,
      offset: const Offset(0, 16),
    ),
  ];
}
