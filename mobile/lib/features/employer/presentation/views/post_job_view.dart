import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/components/app_field.dart';
import '../../../../core/components/app_select_field.dart';
import '../../../../core/components/app_toast.dart';
import '../../../../core/motion/motion.dart';
import '../../../../core/params/job_params.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_shadows.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../../../core/styles/category_visuals.dart';
import '../../../profile/data/models/city_model.dart';
import '../../data/models/job_form_options.dart';
import '../manager/publish_job_cubit/publish_job_cubit.dart';

class PostJobView extends StatelessWidget {
  const PostJobView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: BlocConsumer<PublishJobCubit, PublishJobState>(
        listener: (context, state) {
          if (state is PublishJobDone) {
            AppToast.success(context, 'تم نشر الإعلان.');
            context.pop();
          }
          if (state case PublishJobFailed(:final failure)) {
            AppToast.error(context, failure.message);
          }
        },
        builder: (context, state) => Column(
          children: [
            const _DarkHeader(),
            Expanded(
              child: switch (state) {
                PublishJobLoading() => const Center(
                  child: CircularProgressIndicator(color: AppColors.amber),
                ),
                PublishJobLoadFailed(:final failure) => Center(
                  child: Text(failure.message, style: AppTextStyles.bodyMd),
                ),
                _ => _PostJobForm(
                  options: context.read<PublishJobCubit>().options!,
                  cities: context.read<PublishJobCubit>().cities,
                  submitting: state is PublishJobSubmitting,
                ),
              },
            ),
          ],
        ),
      ),
    );
  }
}

class _DarkHeader extends StatelessWidget {
  const _DarkHeader();

  @override
  Widget build(BuildContext context) {
    final topInset = MediaQuery.paddingOf(context).top;

    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(18, topInset + 12, 18, 16),
      decoration: const BoxDecoration(
        color: AppColors.charcoalSoft,
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(24)),
      ),
      child: Row(
        children: [
          Pressable(
            onTap: () => context.pop(),
            child: Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: .1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(
                Icons.chevron_right_rounded,
                size: 23,
                color: Colors.white,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Text(
            'نشر إعلان وظيفة',
            style: AppTextStyles.titleMd.copyWith(color: Colors.white),
          ),
        ],
      ),
    );
  }
}

class _PostJobForm extends StatefulWidget {
  const _PostJobForm({
    required this.options,
    required this.cities,
    required this.submitting,
  });

  final JobFormOptions options;
  final List<CityModel> cities;
  final bool submitting;

  @override
  State<_PostJobForm> createState() => _PostJobFormState();
}

class _PostJobFormState extends State<_PostJobForm> {
  final _formKey = GlobalKey<FormState>();
  final _title = TextEditingController();
  final _salary = TextEditingController();
  final _vacancies = TextEditingController(text: '1');
  final _description = TextEditingController();
  final List<_FieldDraft> _fields = [];

  int? _categoryId;
  int? _workTypeId;
  int? _salaryUnitId;
  int? _genderId;
  int? _nationalityId;
  int? _cityId;
  String _contact = 'app';

  @override
  void dispose() {
    for (final c in [_title, _salary, _vacancies, _description]) {
      c.dispose();
    }
    for (final f in _fields) {
      f.dispose();
    }
    super.dispose();
  }

  /// The employer-authored form fields, ready for the API — blank labels
  /// dropped, options kept only for pick lists.
  List<Map<String, dynamic>> _buildApplicationFields() {
    final result = <Map<String, dynamic>>[];

    for (final field in _fields) {
      final label = field.label.text.trim();
      if (label.isEmpty) continue;

      final options = field.type == 'select'
          ? field.options.text
                .split(RegExp('[،,]'))
                .map((e) => e.trim())
                .where((e) => e.isNotEmpty)
                .toList()
          : const <String>[];

      result.add({
        'label': label,
        'type': field.type,
        'required': field.required,
        if (field.type == 'select') 'options': options,
      });
    }

    return result;
  }

  bool get _selectorsReady =>
      _categoryId != null &&
      _workTypeId != null &&
      _salaryUnitId != null &&
      _genderId != null &&
      _nationalityId != null &&
      _cityId != null;

  void _submit() {
    if (!(_formKey.currentState?.validate() ?? false) || !_selectorsReady) {
      AppToast.error(context, 'أكمل جميع الحقول المطلوبة.');
      return;
    }

    context.read<PublishJobCubit>().submit(
      PublishJobParam(
        title: _title.text.trim(),
        categoryId: _categoryId!,
        workTypeId: _workTypeId!,
        salaryUnitId: _salaryUnitId!,
        genderRequirementId: _genderId!,
        nationalityRequirementId: _nationalityId!,
        salaryAmount: num.tryParse(_salary.text.trim()) ?? 0,
        vacanciesCount: int.tryParse(_vacancies.text.trim()) ?? 1,
        cityId: _cityId!,
        contactChannel: _contact,
        applicationFields: _buildApplicationFields(),
        description: _description.text.trim().isEmpty
            ? null
            : _description.text.trim(),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final o = widget.options;

    return Column(
      children: [
        Expanded(
          child: Form(
            key: _formKey,
            child: ListView(
              padding: const EdgeInsets.fromLTRB(20, 18, 20, 20),
              children: [
                const _Label('التصنيف'),
                _ChipSelect(
                  options: o.categories,
                  value: _categoryId,
                  withIcons: true,
                  onSelect: (id) => setState(() => _categoryId = id),
                ),
                const _Label('المسمى الوظيفي'),
                AppField(
                  controller: _title,
                  hint: 'مثال: باريستا، عامل نظافة، سائق...',
                  validator: (v) =>
                      (v ?? '').trim().length < 3 ? 'أدخل المسمى الوظيفي' : null,
                ),
                const _Label('المدينة'),
                AppSelectField<int>(
                  hint: 'اختر المدينة',
                  value: _cityId,
                  options: [
                    for (final c in widget.cities) (value: c.id, label: c.name),
                  ],
                  onChanged: (id) => setState(() => _cityId = id),
                ),
                const _Label('نوع العمل'),
                _ChipSelect(
                  options: o.workTypes,
                  value: _workTypeId,
                  onSelect: (id) => setState(() => _workTypeId = id),
                ),
                const _Label('الراتب / الأجر'),
                AppField(
                  controller: _salary,
                  hint: 'مثال: 4500',
                  keyboardType: TextInputType.number,
                  textDirection: TextDirection.rtl,
                  inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                  validator: (v) =>
                      (v ?? '').trim().isEmpty ? 'أدخل الراتب' : null,
                ),
                const SizedBox(height: 10),
                _ChipSelect(
                  options: o.salaryUnits,
                  value: _salaryUnitId,
                  onSelect: (id) => setState(() => _salaryUnitId = id),
                ),
                const _Label('الجنس المطلوب'),
                _SegmentSelect(
                  options: o.genderRequirements,
                  value: _genderId,
                  onSelect: (id) => setState(() => _genderId = id),
                ),
                const _Label('الجنسية المطلوبة'),
                _SegmentSelect(
                  options: o.nationalityRequirements,
                  value: _nationalityId,
                  onSelect: (id) => setState(() => _nationalityId = id),
                ),
                const _Label('العدد المطلوب'),
                AppField(
                  controller: _vacancies,
                  hint: 'العدد المطلوب',
                  keyboardType: TextInputType.number,
                  textDirection: TextDirection.rtl,
                  inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                  validator: (v) =>
                      (int.tryParse(v ?? '') ?? 0) < 1 ? 'أدخل عدداً صحيحاً' : null,
                ),
                const _Label('وصف الوظيفة'),
                AppField(
                  controller: _description,
                  hint: 'اكتب تفاصيل المهام والمتطلبات...',
                  maxLines: 4,
                ),
                const _Label('طريقة التواصل'),
                _ContactSelect(
                  value: _contact,
                  onSelect: (v) => setState(() => _contact = v),
                ),
                const _Label('نموذج التقديم (اختياري)'),
                Text(
                  'أضف حقولاً يملؤها المتقدّم — سيرة ذاتية، صورة، سؤال، أو قائمة اختيار.',
                  style: AppTextStyles.caption.copyWith(
                    color: AppColors.textMuted,
                    height: 1.6,
                  ),
                ),
                const SizedBox(height: 10),
                for (final field in _fields)
                  _FieldEditor(
                    key: ObjectKey(field),
                    field: field,
                    onChanged: () => setState(() {}),
                    onRemove: () => setState(() {
                      field.dispose();
                      _fields.remove(field);
                    }),
                  ),
                _AddFieldButton(
                  onTap: () => setState(() => _fields.add(_FieldDraft())),
                ),
              ],
            ),
          ),
        ),
        _SubmitBar(submitting: widget.submitting, onSubmit: _submit),
      ],
    );
  }
}

class _Label extends StatelessWidget {
  const _Label(this.text);

  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(0, 22, 0, 8),
      child: Text(
        text,
        style: AppTextStyles.titleSm.copyWith(
          fontSize: 13.5,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }
}

/// A wrap of single-select chips, optionally with a category icon.
class _ChipSelect extends StatelessWidget {
  const _ChipSelect({
    required this.options,
    required this.value,
    required this.onSelect,
    this.withIcons = false,
  });

  final List<RefOption> options;
  final int? value;
  final ValueChanged<int> onSelect;
  final bool withIcons;

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: [
        for (final option in options)
          _Chip(
            label: option.name,
            icon: withIcons ? CategoryVisuals.icon(option.code) : null,
            active: value == option.id,
            onTap: () => onSelect(option.id),
          ),
      ],
    );
  }
}

class _Chip extends StatelessWidget {
  const _Chip({
    required this.label,
    required this.active,
    required this.onTap,
    this.icon,
  });

  final String label;
  final bool active;
  final VoidCallback onTap;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: AnimatedContainer(
        duration: AppMotion.press,
        padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 9),
        decoration: BoxDecoration(
          color: active ? AppColors.warningBg : AppColors.surface,
          border: Border.all(
            color: active ? AppColors.amber : const Color(0xFFE5EAE6),
            width: 1.5,
          ),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (icon != null) ...[
              Icon(
                icon,
                size: 17,
                color: active ? AppColors.warningFg : AppColors.textBody,
              ),
              const SizedBox(width: 6),
            ],
            Text(
              label,
              style: AppTextStyles.bodySm.copyWith(
                fontWeight: FontWeight.w700,
                color: active ? AppColors.warningFg : AppColors.textBody,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// A row of equal-width segments (gender, nationality).
class _SegmentSelect extends StatelessWidget {
  const _SegmentSelect({
    required this.options,
    required this.value,
    required this.onSelect,
  });

  final List<RefOption> options;
  final int? value;
  final ValueChanged<int> onSelect;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        for (final (index, option) in options.indexed) ...[
          if (index > 0) const SizedBox(width: 8),
          Expanded(
            child: _Segment(
              label: option.name,
              active: value == option.id,
              onTap: () => onSelect(option.id),
            ),
          ),
        ],
      ],
    );
  }
}

class _ContactSelect extends StatelessWidget {
  const _ContactSelect({required this.value, required this.onSelect});

  final String value;
  final ValueChanged<String> onSelect;

  static const _options = [
    ('app', 'داخل التطبيق'),
    ('whatsapp', 'واتساب'),
    ('both', 'كلاهما'),
  ];

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        for (final (index, (code, label)) in _options.indexed) ...[
          if (index > 0) const SizedBox(width: 8),
          Expanded(
            child: _Segment(
              label: label,
              active: value == code,
              onTap: () => onSelect(code),
            ),
          ),
        ],
      ],
    );
  }
}

class _Segment extends StatelessWidget {
  const _Segment({
    required this.label,
    required this.active,
    required this.onTap,
  });

  final String label;
  final bool active;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: AnimatedContainer(
        duration: AppMotion.press,
        height: 46,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: active ? AppColors.warningBg : AppColors.surface,
          border: Border.all(
            color: active ? AppColors.amber : const Color(0xFFE5EAE6),
            width: 1.5,
          ),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Text(
          label,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: AppTextStyles.bodySm.copyWith(
            fontWeight: FontWeight.w700,
            color: active ? AppColors.warningFg : AppColors.textBody,
          ),
        ),
      ),
    );
  }
}

class _SubmitBar extends StatelessWidget {
  const _SubmitBar({required this.submitting, required this.onSubmit});

  final bool submitting;
  final VoidCallback onSubmit;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 12),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(top: BorderSide(color: Color(0xFFE5EAE6))),
      ),
      child: SafeArea(
        top: false,
        child: Pressable(
          onTap: submitting ? null : onSubmit,
          child: Container(
            height: 50,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: AppColors.amber,
              borderRadius: BorderRadius.circular(17),
              boxShadow: AppShadows.amberGlow,
            ),
            child: submitting
                ? const SizedBox(
                    width: 22,
                    height: 22,
                    child: CircularProgressIndicator(
                      strokeWidth: 2.4,
                      color: AppColors.textStrong,
                    ),
                  )
                : Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text('نشر الإعلان', style: AppTextStyles.button),
                      const SizedBox(width: 8),
                      const Icon(
                        Icons.send_rounded,
                        size: 20,
                        color: AppColors.textStrong,
                      ),
                    ],
                  ),
          ),
        ),
      ),
    );
  }
}

/// One in-progress application-form field the employer is authoring. Holds its
/// own controllers so text survives rebuilds; disposed with the form.
class _FieldDraft {
  _FieldDraft()
    : label = TextEditingController(),
      options = TextEditingController();

  final TextEditingController label;
  final TextEditingController options;
  String type = 'text';
  bool required = false;

  void dispose() {
    label.dispose();
    options.dispose();
  }
}

const _fieldTypeOptions = <(String, String)>[
  ('text', 'نص'),
  ('number', 'أرقام'),
  ('select', 'قائمة'),
  ('file', 'ملف'),
  ('image', 'صورة'),
];

/// The editor card for a single custom field: label, type, required, options.
class _FieldEditor extends StatelessWidget {
  const _FieldEditor({
    required this.field,
    required this.onChanged,
    required this.onRemove,
    super.key,
  });

  final _FieldDraft field;
  final VoidCallback onChanged;
  final VoidCallback onRemove;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.border),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: AppField(controller: field.label, hint: 'عنوان الحقل'),
              ),
              const SizedBox(width: 8),
              Pressable(
                onTap: onRemove,
                child: Container(
                  width: 44,
                  height: 44,
                  alignment: Alignment.center,
                  decoration: BoxDecoration(
                    color: AppColors.errorBg,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(
                    Icons.delete_outline_rounded,
                    color: AppColors.errorFg,
                    size: 20,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              for (final (code, label) in _fieldTypeOptions)
                _Chip(
                  label: label,
                  active: field.type == code,
                  onTap: () {
                    field.type = code;
                    onChanged();
                  },
                ),
            ],
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              _Chip(
                label: 'مطلوب',
                icon: field.required
                    ? Icons.check_box_rounded
                    : Icons.check_box_outline_blank_rounded,
                active: field.required,
                onTap: () {
                  field.required = !field.required;
                  onChanged();
                },
              ),
            ],
          ),
          if (field.type == 'select') ...[
            const SizedBox(height: 10),
            AppField(
              controller: field.options,
              hint: 'الخيارات مفصولة بفاصلة — مثال: دوام كامل، جزئي',
            ),
          ],
        ],
      ),
    );
  }
}

class _AddFieldButton extends StatelessWidget {
  const _AddFieldButton({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(top: 2),
        padding: const EdgeInsets.symmetric(vertical: 13),
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: AppColors.warningBg,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: AppColors.amber, width: 1.4),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.add_rounded, size: 20, color: AppColors.warningFg),
            const SizedBox(width: 7),
            Text(
              'إضافة حقل',
              style: AppTextStyles.bodySm.copyWith(
                fontWeight: FontWeight.w800,
                color: AppColors.warningFg,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
