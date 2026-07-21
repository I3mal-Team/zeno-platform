import 'package:flutter/material.dart';

/// Maps a category code to a Material icon standing in for the design's
/// iconsax glyph. Colours come from [AppColors.categoryTint].
abstract final class CategoryVisuals {
  static const _icons = <String, IconData>{
    'restaurants': Icons.local_cafe_rounded,
    'cleaning': Icons.cleaning_services_rounded,
    'logistics': Icons.local_shipping_rounded,
    'events': Icons.celebration_rounded,
    'retail': Icons.storefront_rounded,
    'security': Icons.shield_rounded,
    'operations': Icons.build_rounded,
    'crafts': Icons.handyman_rounded,
    'seasonal_work': Icons.ac_unit_rounded,
    'other': Icons.more_horiz_rounded,
  };

  static IconData icon(String code) => _icons[code] ?? Icons.work_rounded;
}
