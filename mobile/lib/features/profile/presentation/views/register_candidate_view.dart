import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/components/app_button.dart';
import '../../../../core/components/app_text_field.dart';
import '../../../../core/components/app_toast.dart';
import '../../../../core/params/profile_params.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_dimensions.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../data/models/city_model.dart';
import '../manager/profile_cubit/profile_cubit.dart';

class RegisterCandidateView extends StatefulWidget {
  const RegisterCandidateView({super.key});

  @override
  State<RegisterCandidateView> createState() => _RegisterCandidateViewState();
}

class _RegisterCandidateViewState extends State<RegisterCandidateView> {
  final _formKey = GlobalKey<FormState>();
  final _fullName = TextEditingController();
  final _jobTitle = TextEditingController();
  final _years = TextEditingController();
  final _bio = TextEditingController();
  final _skills = TextEditingController();

  int? _cityId;

  @override
  void initState() {
    super.initState();
    context.read<ProfileCubit>().loadCities();
  }

  @override
  void dispose() {
    for (final controller in [_fullName, _jobTitle, _years, _bio, _skills]) {
      controller.dispose();
    }
    super.dispose();
  }

  void _submit() {
    if (!(_formKey.currentState?.validate() ?? false)) return;

    context.read<ProfileCubit>().save(
      SaveCandidateProfileParam(
        fullName: _fullName.text.trim(),
        cityId: _cityId,
        jobTitle: _jobTitle.text.trim().isEmpty ? null : _jobTitle.text.trim(),
        yearsOfExperience: int.tryParse(_years.text),
        skills: _skills.text
            .split('،')
            .map((skill) => skill.trim())
            .where((skill) => skill.isNotEmpty)
            .toList(),
        bio: _bio.text.trim().isEmpty ? null : _bio.text.trim(),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('أكمل بياناتك', style: AppTextStyles.titleMd)),
      body: BlocConsumer<ProfileCubit, ProfileState>(
        listener: (context, state) {
          if (state is ProfileSaved) {
            context.go(RoutesKeys.browse);
          }

          if (state case ProfileFailed(:final failure)) {
            AppToast.error(context, failure.message);
          }
        },
        builder: (context, state) {
          final cubit = context.read<ProfileCubit>();

          return Form(
            key: _formKey,
            child: ListView(
              padding: const EdgeInsets.all(AppDimensions.screenPadding),
              children: [
                Text(
                  'عرّفنا بنفسك ليصل ملفك لأصحاب العمل بسهولة.',
                  style: AppTextStyles.bodyLg,
                ),
                const SizedBox(height: AppDimensions.space26),

                AppTextField(
                  controller: _fullName,
                  label: 'الاسم الكامل',
                  validator: (value) => (value ?? '').trim().length < 3
                      ? 'أدخل الاسم الكامل'
                      : null,
                ),
                const SizedBox(height: AppDimensions.space16),

                _CityPicker(
                  cities: cubit.cities,
                  value: _cityId,
                  onChanged: (id) => setState(() => _cityId = id),
                ),
                const SizedBox(height: AppDimensions.space16),

                AppTextField(
                  controller: _jobTitle,
                  label: 'المهنة الحالية أو المستهدفة',
                ),
                const SizedBox(height: AppDimensions.space16),

                AppTextField(
                  controller: _years,
                  label: 'سنوات الخبرة',
                  keyboardType: TextInputType.number,
                  inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                ),
                const SizedBox(height: AppDimensions.space16),

                AppTextField(
                  controller: _skills,
                  label: 'المهارات',
                  hint: 'افصل بين المهارات بفاصلة،',
                ),
                const SizedBox(height: AppDimensions.space16),

                AppTextField(
                  controller: _bio,
                  label: 'نبذة مختصرة عنك',
                  maxLines: 4,
                ),
                const SizedBox(height: AppDimensions.space32),

                AppButton(
                  label: 'إنشاء الحساب',
                  isLoading: state is ProfileSaving,
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
