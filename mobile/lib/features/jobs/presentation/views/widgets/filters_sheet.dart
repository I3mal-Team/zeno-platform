import 'package:flutter/material.dart';

import '../../../../../core/components/app_toast.dart';
import '../../../../../core/params/job_params.dart';
import '../../../../../core/services/service_locator.dart';
import '../../../../../core/styles/app_colors.dart';
import '../../../../../core/styles/app_text_styles.dart';
import '../../../../employer/data/models/job_form_options.dart';
import '../../../data/repos/jobs_repo.dart';

/// The advanced-filter sheet: work type, gender and nationality. Edits are held
/// locally and only handed back on apply, so backing out leaves the results
/// exactly as they were.
Future<JobFilters?> showFiltersSheet(
  BuildContext context, {
  required JobFilters filters,
  required JobFormOptions options,
}) {
  return showModalBottomSheet<JobFilters>(
    context: context,
    backgroundColor: Colors.transparent,
    isScrollControlled: true,
    builder: (_) => _FiltersSheet(filters: filters, options: options),
  );
}

class _FiltersSheet extends StatefulWidget {
  const _FiltersSheet({required this.filters, required this.options});

  final JobFilters filters;
  final JobFormOptions options;

  @override
  State<_FiltersSheet> createState() => _FiltersSheetState();
}

class _FiltersSheetState extends State<_FiltersSheet> {
  late JobFilters _draft = widget.filters;

  int? _idFor(List<RefOption> options, String? code) {
    if (code == null) return null;
    for (final option in options) {
      if (option.code == code) return option.id;
    }
    return null;
  }

  Future<void> _saveAlert(BuildContext context) async {
    final categoryId = _idFor(widget.options.categories, _draft.categoryCode);
    final workTypeId = _idFor(widget.options.workTypes, _draft.workTypeCode);
    final keyword = _draft.query?.trim();

    final params = <String, dynamic>{
      if (keyword != null && keyword.isNotEmpty) 'keyword': keyword,
      'city_id': ?_draft.cityId,
      'category_id': ?categoryId,
      'work_type_id': ?workTypeId,
    };

    final result = await getIt<JobsRepo>().createAlert(params);
    if (!context.mounted) return;

    result.fold((failure) => AppToast.error(context, failure.message), (_) {
      AppToast.success(context, 'تم إنشاء التنبيه — سنُشعرك بأي وظيفة مطابقة.');
      Navigator.of(context).pop();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      constraints: BoxConstraints(
        maxHeight: MediaQuery.sizeOf(context).height * 0.85,
      ),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(30)),
      ),
      padding: const EdgeInsets.fromLTRB(22, 14, 22, 28),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Center(
            child: Container(
              width: 42,
              height: 5,
              margin: const EdgeInsets.only(bottom: 16),
              decoration: BoxDecoration(
                color: const Color(0xFFE2DED4),
                borderRadius: BorderRadius.circular(3),
              ),
            ),
          ),
          Row(
            children: [
              Expanded(
                child: Text('تصفية النتائج', style: AppTextStyles.titleLg),
              ),
              TextButton(
                onPressed: () => setState(() => _draft = _draft.cleared()),
                child: Text(
                  'مسح الكل',
                  style: AppTextStyles.titleSm.copyWith(
                    color: AppColors.textMuted,
                  ),
                ),
              ),
            ],
          ),
          Flexible(
            child: SingleChildScrollView(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  _Group(
                    title: 'نوع الدوام',
                    options: widget.options.workTypes,
                    selected: _draft.workTypeCode,
                    onSelected: (code) => setState(
                      () => _draft = code == null
                          ? _draft.copyWith(clearWorkType: true)
                          : _draft.copyWith(workTypeCode: code),
                    ),
                  ),
                  _Group(
                    title: 'الجنس',
                    options: widget.options.genderRequirements,
                    selected: _draft.genderCode,
                    onSelected: (code) => setState(
                      () => _draft = code == null
                          ? _draft.copyWith(clearGender: true)
                          : _draft.copyWith(genderCode: code),
                    ),
                  ),
                  _Group(
                    title: 'الجنسية',
                    options: widget.options.nationalityRequirements,
                    selected: _draft.nationalityCode,
                    onSelected: (code) => setState(
                      () => _draft = code == null
                          ? _draft.copyWith(clearNationality: true)
                          : _draft.copyWith(nationalityCode: code),
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 50,
            child: OutlinedButton.icon(
              onPressed: () => _saveAlert(context),
              icon: const Icon(Icons.notifications_active_rounded, size: 19),
              label: Text(
                'احفظ هذا البحث كتنبيه',
                style: AppTextStyles.bodyMd.copyWith(
                  fontWeight: FontWeight.w800,
                ),
              ),
              style: OutlinedButton.styleFrom(
                foregroundColor: AppColors.charcoalSoft,
                side: const BorderSide(color: AppColors.border, width: 1.5),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(17),
                ),
              ),
            ),
          ),
          const SizedBox(height: 10),
          SizedBox(
            height: 54,
            child: ElevatedButton(
              onPressed: () => Navigator.of(context).pop(_draft),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.charcoalSoft,
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(17),
                ),
              ),
              child: Text('تطبيق', style: AppTextStyles.button),
            ),
          ),
        ],
      ),
    );
  }
}

/// One filter dimension. Tapping the chosen chip again clears it, so every
/// dimension can return to "any" without a separate control.
class _Group extends StatelessWidget {
  const _Group({
    required this.title,
    required this.options,
    required this.selected,
    required this.onSelected,
  });

  final String title;
  final List<RefOption> options;
  final String? selected;
  final ValueChanged<String?> onSelected;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: 18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: AppTextStyles.titleSm),
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: options.map((option) {
              final on = option.code == selected;

              return GestureDetector(
                onTap: () => onSelected(on ? null : option.code),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 140),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 10,
                  ),
                  decoration: BoxDecoration(
                    color: on
                        ? AppColors.charcoalSoft
                        : const Color(0xFFF7F9F7),
                    border: Border.all(
                      color: on
                          ? AppColors.charcoalSoft
                          : const Color(0xFFDCE3DD),
                    ),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    option.name,
                    style: AppTextStyles.titleSm.copyWith(
                      color: on ? Colors.white : AppColors.textBody,
                    ),
                  ),
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }
}
