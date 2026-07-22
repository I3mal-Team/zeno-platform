import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/components/app_button.dart';
import '../../../../core/components/app_text_field.dart';
import '../../../../core/components/app_toast.dart';
import '../../../../core/params/profile_params.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_dimensions.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../../profile/data/models/city_model.dart';
import '../manager/employer_profile_cubit/employer_profile_cubit.dart';

class RegisterEmployerView extends StatefulWidget {
  const RegisterEmployerView({super.key});

  @override
  State<RegisterEmployerView> createState() => _RegisterEmployerViewState();
}

class _RegisterEmployerViewState extends State<RegisterEmployerView> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _responsible = TextEditingController();
  final _registration = TextEditingController();
  final _about = TextEditingController();

  String _type = 'company';
  int? _cityId;

  bool get _isCompany => _type == 'company';

  @override
  void initState() {
    super.initState();
    context.read<EmployerProfileCubit>().loadCities();
  }

  @override
  void dispose() {
    for (final c in [_name, _responsible, _registration, _about]) {
      c.dispose();
    }
    super.dispose();
  }

  void _submit() {
    if (!(_formKey.currentState?.validate() ?? false)) return;

    context.read<EmployerProfileCubit>().save(
      SaveOrganizationParam(
        type: _type,
        name: _name.text.trim(),
        responsiblePersonName: _responsible.text.trim().isEmpty
            ? null
            : _responsible.text.trim(),
        commercialRegistration:
            _isCompany && _registration.text.trim().isNotEmpty
            ? _registration.text.trim()
            : null,
        cityId: _cityId,
        about: _about.text.trim().isEmpty ? null : _about.text.trim(),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('بيانات المنشأة', style: AppTextStyles.titleMd),
      ),
      body: BlocConsumer<EmployerProfileCubit, EmployerProfileState>(
        listener: (context, state) {
          if (state is EmployerProfileSaved) {
            context.go(RoutesKeys.employerJobs);
          }
          if (state case EmployerProfileFailed(:final failure)) {
            AppToast.error(context, failure.message);
          }
        },
        builder: (context, state) {
          final cubit = context.read<EmployerProfileCubit>();

          return Form(
            key: _formKey,
            child: ListView(
              padding: const EdgeInsets.all(AppDimensions.screenPadding),
              children: [
                Text(
                  'عرّفنا بمنشأتك لتبدأ في نشر وظائفك والوصول إلى المرشحين.',
                  style: AppTextStyles.bodyLg,
                ),
                const SizedBox(height: AppDimensions.space26),

                _TypeToggle(
                  value: _type,
                  onChanged: (value) => setState(() => _type = value),
                ),
                const SizedBox(height: AppDimensions.space16),

                AppTextField(
                  controller: _name,
                  label: _isCompany ? 'اسم المنشأة' : 'الاسم التجاري',
                  validator: (value) =>
                      (value ?? '').trim().length < 2 ? 'أدخل الاسم' : null,
                ),
                const SizedBox(height: AppDimensions.space16),

                AppTextField(
                  controller: _responsible,
                  label: 'اسم الشخص المسؤول',
                ),
                const SizedBox(height: AppDimensions.space16),

                if (_isCompany) ...[
                  AppTextField(
                    controller: _registration,
                    label: 'رقم السجل التجاري',
                    keyboardType: TextInputType.number,
                    inputFormatters: [
                      FilteringTextInputFormatter.digitsOnly,
                      LengthLimitingTextInputFormatter(10),
                    ],
                    validator: (value) => (value ?? '').trim().length != 10
                        ? 'السجل التجاري 10 أرقام'
                        : null,
                  ),
                  const SizedBox(height: AppDimensions.space16),
                ],

                _CityPicker(
                  cities: cubit.cities,
                  value: _cityId,
                  onChanged: (id) => setState(() => _cityId = id),
                ),
                const SizedBox(height: AppDimensions.space16),

                AppTextField(
                  controller: _about,
                  label: 'نبذة عن المنشأة',
                  maxLines: 4,
                ),
                const SizedBox(height: AppDimensions.space32),

                AppButton(
                  label: 'حفظ ومتابعة',
                  isLoading: state is EmployerProfileSaving,
                  onPressed: _submit,
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _TypeToggle extends StatelessWidget {
  const _TypeToggle({required this.value, required this.onChanged});

  final String value;
  final ValueChanged<String> onChanged;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text('نوع الحساب', style: AppTextStyles.titleSm),
        const SizedBox(height: AppDimensions.space8),
        Row(
          children: [
            _TypeOption(
              label: 'منشأة',
              selected: value == 'company',
              onTap: () => onChanged('company'),
            ),
            const SizedBox(width: AppDimensions.space12),
            _TypeOption(
              label: 'فرد',
              selected: value == 'individual',
              onTap: () => onChanged('individual'),
            ),
          ],
        ),
      ],
    );
  }
}

class _TypeOption extends StatelessWidget {
  const _TypeOption({
    required this.label,
    required this.selected,
    required this.onTap,
  });

  final String label;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(AppDimensions.radiusControl),
        child: Container(
          height: AppDimensions.inputHeight,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: selected ? AppColors.warningBg : AppColors.surface,
            border: Border.all(
              color: selected ? AppColors.amber : AppColors.borderStrong,
              width: selected ? 1.5 : 1,
            ),
            borderRadius: BorderRadius.circular(AppDimensions.radiusControl),
          ),
          child: Text(
            label,
            style: AppTextStyles.titleSm.copyWith(
              color: selected ? AppColors.warningFg : AppColors.textBody,
            ),
          ),
        ),
      ),
    );
  }
}

class _CityPicker extends StatelessWidget {
  const _CityPicker({
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
