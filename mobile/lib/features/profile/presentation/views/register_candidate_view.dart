import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/components/app_button.dart';
import '../../../../core/components/app_field.dart';
import '../../../../core/components/app_select_field.dart';
import '../../../../core/components/app_toast.dart';
import '../../../../core/components/app_top_bar.dart';
import '../../../../core/motion/motion.dart';
import '../../../../core/params/profile_params.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_dimensions.dart';
import '../../../../core/styles/app_text_styles.dart';
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
      body: DecoratedBox(
        decoration: const BoxDecoration(
          gradient: RadialGradient(
            center: Alignment(0, -1.1),
            radius: 1.1,
            colors: [Color(0xFFFFFFFF), Color(0xFFF4F2EC)],
            stops: [0, .7],
          ),
        ),
        child: SafeArea(
          child: BlocConsumer<ProfileCubit, ProfileState>(
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
                  padding: const EdgeInsets.fromLTRB(24, 12, 24, 34),
                  children: [
                    const AppTopBar(showLogo: true),
                    const SizedBox(height: AppDimensions.space22),
                    Text(
                      'أكمل بياناتك',
                      style: AppTextStyles.displayLg.copyWith(fontSize: 24),
                    ),
                    const SizedBox(height: AppDimensions.space6),
                    Text(
                      'عرّفنا بنفسك ليصل ملفك لأصحاب العمل بسهولة.',
                      style: AppTextStyles.bodyLg.copyWith(
                        color: AppColors.textMuted,
                      ),
                    ),
                    const SizedBox(height: AppDimensions.space22),
                    Entrance(
                      index: 0,
                      child: AppField(
                        controller: _fullName,
                        hint: 'الاسم الكامل',
                        validator: (value) => (value ?? '').trim().length < 3
                            ? 'أدخل الاسم الكامل'
                            : null,
                      ),
                    ),
                    const SizedBox(height: 13),
                    Entrance(
                      index: 1,
                      child: AppSelectField<int>(
                        hint: 'المدينة',
                        value: _cityId,
                        options: [
                          for (final city in cubit.cities)
                            (value: city.id, label: city.name),
                        ],
                        onChanged: (id) => setState(() => _cityId = id),
                      ),
                    ),
                    const SizedBox(height: 13),
                    Entrance(
                      index: 2,
                      child: AppField(
                        controller: _jobTitle,
                        hint: 'المهنة الحالية أو المستهدفة',
                      ),
                    ),
                    const SizedBox(height: 13),
                    Entrance(
                      index: 3,
                      child: AppField(
                        controller: _years,
                        hint: 'سنوات الخبرة',
                        keyboardType: TextInputType.number,
                        inputFormatters: [
                          FilteringTextInputFormatter.digitsOnly,
                        ],
                      ),
                    ),
                    const SizedBox(height: 13),
                    Entrance(
                      index: 4,
                      child: AppField(
                        controller: _skills,
                        hint: 'المهارات (افصل بينها بفاصلة،)',
                      ),
                    ),
                    const SizedBox(height: 13),
                    Entrance(
                      index: 5,
                      child: AppField(
                        controller: _bio,
                        hint: 'نبذة مختصرة عنك (اختياري)',
                        maxLines: 4,
                      ),
                    ),
                    const SizedBox(height: AppDimensions.space22),
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
        ),
      ),
    );
  }
}
