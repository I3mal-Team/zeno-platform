import 'package:flutter/material.dart';

import '../styles/app_colors.dart';
import '../styles/app_dimensions.dart';
import '../styles/app_text_styles.dart';

/// One selectable option in an [AppSelectField].
typedef SelectOption<T> = ({T value, String label});

/// A placeholder-style select that looks like [AppField] but opens a bottom
/// sheet of options — used where the app offers an admin-managed list (cities,
/// categories) in place of the design's free-text input.
class AppSelectField<T> extends StatelessWidget {
  const AppSelectField({
    required this.hint,
    required this.value,
    required this.options,
    required this.onChanged,
    super.key,
  });

  final String hint;
  final T? value;
  final List<SelectOption<T>> options;
  final ValueChanged<T> onChanged;

  @override
  Widget build(BuildContext context) {
    final selected = options.where((o) => o.value == value).firstOrNull;

    return GestureDetector(
      onTap: () => _open(context),
      child: Container(
        height: 50,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        decoration: BoxDecoration(
          color: AppColors.surface,
          border: Border.all(color: const Color(0xFFE5EAE6)),
          borderRadius: BorderRadius.circular(14),
        ),
        child: Row(
          children: [
            Expanded(
              child: Text(
                selected?.label ?? hint,
                style: AppTextStyles.input.copyWith(
                  fontSize: 15,
                  fontWeight: selected != null
                      ? FontWeight.w600
                      : FontWeight.w500,
                  color: selected != null
                      ? AppColors.textStrong
                      : AppColors.textMuted,
                ),
              ),
            ),
            const Icon(
              Icons.keyboard_arrow_down_rounded,
              color: AppColors.textMuted,
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _open(BuildContext context) async {
    final picked = await showModalBottomSheet<T>(
      context: context,
      backgroundColor: AppColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (_) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const SizedBox(height: AppDimensions.space12),
            Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: AppColors.border,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(AppDimensions.space16),
              child: Text(hint, style: AppTextStyles.titleSm),
            ),
            Flexible(
              child: ListView(
                shrinkWrap: true,
                children: [
                  for (final option in options)
                    ListTile(
                      onTap: () => Navigator.of(context).pop(option.value),
                      title: Text(option.label, style: AppTextStyles.bodyMd),
                      trailing: option.value == value
                          ? const Icon(
                              Icons.check_rounded,
                              color: AppColors.textStrong,
                            )
                          : null,
                    ),
                ],
              ),
            ),
            const SizedBox(height: AppDimensions.space8),
          ],
        ),
      ),
    );

    if (picked != null) onChanged(picked);
  }
}
