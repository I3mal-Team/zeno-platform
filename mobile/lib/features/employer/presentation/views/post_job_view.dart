import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/components/app_button.dart';
import '../../../../core/components/app_text_field.dart';
import '../../../../core/components/app_toast.dart';
import '../../../../core/params/job_params.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_dimensions.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../data/models/job_form_options.dart';
import '../../../profile/data/models/city_model.dart';
import '../manager/publish_job_cubit/publish_job_cubit.dart';

class PostJobView extends StatelessWidget {
  const PostJobView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      appBar: AppBar(
        backgroundColor: AppColors.paper,
        title: Text('نشر وظيفة', style: AppTextStyles.titleLg),
      ),
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
        builder: (context, state) => switch (state) {
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
    super.dispose();
  }

  bool get _dropdownsReady =>
      _categoryId != null &&
      _workTypeId != null &&
      _salaryUnitId != null &&
      _genderId != null &&
      _nationalityId != null &&
      _cityId != null;

  void _submit() {
    if (!(_formKey.currentState?.validate() ?? false) || !_dropdownsReady) {
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
        description: _description.text.trim().isEmpty
            ? null
            : _description.text.trim(),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final o = widget.options;

    return Form(
      key: _formKey,
      child: ListView(
        padding: const EdgeInsets.all(AppDimensions.screenPadding),
        children: [
          AppTextField(
            controller: _title,
            label: 'المسمى الوظيفي',
            validator: (v) =>
                (v ?? '').trim().length < 3 ? 'أدخل المسمى الوظيفي' : null,
          ),
          const SizedBox(height: AppDimensions.space16),
          _Dropdown(
            label: 'التصنيف',
            value: _categoryId,
            options: o.categories,
            onChanged: (v) => setState(() => _categoryId = v),
          ),
          const SizedBox(height: AppDimensions.space16),
          _Dropdown(
            label: 'نوع الدوام',
            value: _workTypeId,
            options: o.workTypes,
            onChanged: (v) => setState(() => _workTypeId = v),
          ),
          const SizedBox(height: AppDimensions.space16),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: AppTextField(
                  controller: _salary,
                  label: 'الراتب / الأجر',
                  keyboardType: TextInputType.number,
                  inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                  validator: (v) =>
                      (v ?? '').trim().isEmpty ? 'أدخل الراتب' : null,
                ),
              ),
              const SizedBox(width: AppDimensions.space12),
              Expanded(
                child: _Dropdown(
                  label: 'الوحدة',
                  value: _salaryUnitId,
                  options: o.salaryUnits,
                  onChanged: (v) => setState(() => _salaryUnitId = v),
                ),
              ),
            ],
          ),
          const SizedBox(height: AppDimensions.space16),
          AppTextField(
            controller: _vacancies,
            label: 'عدد الشواغر',
            keyboardType: TextInputType.number,
            inputFormatters: [FilteringTextInputFormatter.digitsOnly],
            validator: (v) =>
                (int.tryParse(v ?? '') ?? 0) < 1 ? 'أدخل عدداً صحيحاً' : null,
          ),
          const SizedBox(height: AppDimensions.space16),
          _Dropdown(
            label: 'الجنس المطلوب',
            value: _genderId,
            options: o.genderRequirements,
            onChanged: (v) => setState(() => _genderId = v),
          ),
          const SizedBox(height: AppDimensions.space16),
          _Dropdown(
            label: 'الجنسية',
            value: _nationalityId,
            options: o.nationalityRequirements,
            onChanged: (v) => setState(() => _nationalityId = v),
          ),
          const SizedBox(height: AppDimensions.space16),
          _CityDropdown(
            cities: widget.cities,
            value: _cityId,
            onChanged: (v) => setState(() => _cityId = v),
          ),
          const SizedBox(height: AppDimensions.space16),
          _ContactToggle(
            value: _contact,
            onChanged: (v) => setState(() => _contact = v),
          ),
          const SizedBox(height: AppDimensions.space16),
          AppTextField(
            controller: _description,
            label: 'وصف الوظيفة',
            maxLines: 5,
          ),
          const SizedBox(height: AppDimensions.space32),
          AppButton(
            label: 'نشر الإعلان',
            isLoading: widget.submitting,
            onPressed: _submit,
          ),
        ],
      ),
    );
  }
}

class _Dropdown extends StatelessWidget {
  const _Dropdown({
    required this.label,
    required this.value,
    required this.options,
    required this.onChanged,
  });

  final String label;
  final int? value;
  final List<RefOption> options;
  final ValueChanged<int?> onChanged;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: AppTextStyles.titleSm),
        const SizedBox(height: AppDimensions.space8),
        DropdownButtonFormField<int>(
          value: value,
          isExpanded: true,
          items: [
            for (final option in options)
              DropdownMenuItem(value: option.id, child: Text(option.name)),
          ],
          onChanged: onChanged,
          decoration: const InputDecoration(border: OutlineInputBorder()),
        ),
      ],
    );
  }
}

class _CityDropdown extends StatelessWidget {
  const _CityDropdown({
    required this.cities,
    required this.value,
    required this.onChanged,
  });

  final List<CityModel> cities;
  final int? value;
  final ValueChanged<int?> onChanged;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('المدينة', style: AppTextStyles.titleSm),
        const SizedBox(height: AppDimensions.space8),
        DropdownButtonFormField<int>(
          value: value,
          isExpanded: true,
          items: [
            for (final city in cities)
              DropdownMenuItem(value: city.id, child: Text(city.name)),
          ],
          onChanged: onChanged,
          decoration: const InputDecoration(border: OutlineInputBorder()),
        ),
      ],
    );
  }
}

class _ContactToggle extends StatelessWidget {
  const _ContactToggle({required this.value, required this.onChanged});

  final String value;
  final ValueChanged<String> onChanged;

  static const _options = [
    ('app', 'داخل التطبيق'),
    ('whatsapp', 'واتساب'),
    ('both', 'كلاهما'),
  ];

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('طريقة التواصل', style: AppTextStyles.titleSm),
        const SizedBox(height: AppDimensions.space8),
        Row(
          children: [
            for (final (index, (code, label)) in _options.indexed) ...[
              if (index > 0) const SizedBox(width: AppDimensions.space8),
              Expanded(
                child: InkWell(
                  onTap: () => onChanged(code),
                  borderRadius: BorderRadius.circular(
                    AppDimensions.radiusControl,
                  ),
                  child: Container(
                    height: 46,
                    alignment: Alignment.center,
                    decoration: BoxDecoration(
                      color: value == code
                          ? AppColors.warningBg
                          : AppColors.surface,
                      border: Border.all(
                        color: value == code
                            ? AppColors.amber
                            : AppColors.borderStrong,
                        width: value == code ? 1.5 : 1,
                      ),
                      borderRadius: BorderRadius.circular(
                        AppDimensions.radiusControl,
                      ),
                    ),
                    child: Text(
                      label,
                      style: AppTextStyles.caption.copyWith(
                        fontWeight: FontWeight.w800,
                        color: value == code
                            ? AppColors.warningFg
                            : AppColors.textBody,
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ],
        ),
      ],
    );
  }
}
