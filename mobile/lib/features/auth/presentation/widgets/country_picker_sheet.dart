import 'package:flutter/material.dart';

import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_dimensions.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../data/models/country_model.dart';

/// Presents the admin-managed dial codes and returns the chosen one, or null
/// if the sheet is dismissed without a pick.
Future<CountryModel?> showCountryPickerSheet(
  BuildContext context, {
  required List<CountryModel> countries,
  required CountryModel? selected,
}) {
  return showModalBottomSheet<CountryModel>(
    context: context,
    backgroundColor: AppColors.surface,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
    ),
    builder: (_) =>
        _CountryPickerSheet(countries: countries, selected: selected),
  );
}

class _CountryPickerSheet extends StatelessWidget {
  const _CountryPickerSheet({required this.countries, required this.selected});

  final List<CountryModel> countries;
  final CountryModel? selected;

  @override
  Widget build(BuildContext context) {
    return SafeArea(
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
            child: Text('اختر الدولة', style: AppTextStyles.titleSm),
          ),
          ListView.builder(
            shrinkWrap: true,
            itemCount: countries.length,
            itemBuilder: (context, index) {
              final country = countries[index];
              final isSelected = country.iso2 == selected?.iso2;

              return ListTile(
                onTap: () => Navigator.of(context).pop(country),
                leading: Text(
                  country.flag,
                  style: const TextStyle(fontSize: 24),
                ),
                title: Text(country.name, style: AppTextStyles.bodyMd),
                trailing: Text(
                  country.dialCode,
                  style: AppTextStyles.input.copyWith(
                    color: isSelected
                        ? AppColors.textStrong
                        : AppColors.textMuted,
                  ),
                ),
                selected: isSelected,
              );
            },
          ),
          const SizedBox(height: AppDimensions.space8),
        ],
      ),
    );
  }
}
