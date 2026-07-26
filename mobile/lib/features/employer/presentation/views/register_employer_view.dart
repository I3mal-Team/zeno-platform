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
    for (final c in [_name, _responsible, _registration]) {
      c.dispose();
    }
    super.dispose();
  }

  void _submit() {
    if (!(_formKey.currentState?.validate() ?? false)) return;

    final responsible = _responsible.text.trim();

    context.read<EmployerProfileCubit>().save(
      SaveOrganizationParam(
        type: _type,
        // An individual has no separate trade name — the person is the entity.
        name: _isCompany ? _name.text.trim() : responsible,
        responsiblePersonName: responsible.isEmpty ? null : responsible,
        commercialRegistration:
            _isCompany && _registration.text.trim().isNotEmpty
            ? _registration.text.trim()
            : null,
        cityId: _cityId,
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
          child: BlocConsumer<EmployerProfileCubit, EmployerProfileState>(
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
                  padding: const EdgeInsets.fromLTRB(24, 12, 24, 34),
                  children: [
                    const AppTopBar(showLogo: true),
                    const SizedBox(height: AppDimensions.space22),
                    Text(
                      'بيانات صاحب العمل',
                      style: AppTextStyles.displayLg.copyWith(fontSize: 24),
                    ),
                    const SizedBox(height: AppDimensions.space6),
                    Text(
                      'اختر نوع الحساب وأكمل بيانات جهتك.',
                      style: AppTextStyles.bodyLg.copyWith(
                        color: AppColors.textMuted,
                      ),
                    ),
                    const SizedBox(height: AppDimensions.space20),
                    Text(
                      'نوع الحساب',
                      style: AppTextStyles.caption.copyWith(
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        color: const Color(0xFF9A958A),
                      ),
                    ),
                    const SizedBox(height: 9),
                    Row(
                      children: [
                        _TypeOption(
                          label: 'فرد',
                          icon: Icons.person_rounded,
                          selected: !_isCompany,
                          onTap: () => setState(() => _type = 'individual'),
                        ),
                        const SizedBox(width: AppDimensions.space10),
                        _TypeOption(
                          label: 'منشأة تجارية',
                          icon: Icons.storefront_rounded,
                          selected: _isCompany,
                          onTap: () => setState(() => _type = 'company'),
                        ),
                      ],
                    ),
                    const SizedBox(height: 18),
                    if (_isCompany) ...[
                      AppField(
                        controller: _name,
                        hint: 'اسم المنشأة',
                        validator: (value) => (value ?? '').trim().length < 2
                            ? 'أدخل اسم المنشأة'
                            : null,
                      ),
                      const SizedBox(height: 13),
                      AppField(
                        controller: _registration,
                        hint: 'رقم السجل التجاري',
                        keyboardType: TextInputType.number,
                        textDirection: TextDirection.rtl,
                        inputFormatters: [
                          FilteringTextInputFormatter.digitsOnly,
                          LengthLimitingTextInputFormatter(10),
                        ],
                        validator: (value) => (value ?? '').trim().length != 10
                            ? 'السجل التجاري 10 أرقام'
                            : null,
                      ),
                      const SizedBox(height: 13),
                    ],
                    AppField(
                      controller: _responsible,
                      hint: 'اسم المسؤول',
                      validator: (value) => (value ?? '').trim().length < 2
                          ? 'أدخل اسم المسؤول'
                          : null,
                    ),
                    const SizedBox(height: 13),
                    AppSelectField<int>(
                      hint: 'المدينة',
                      value: _cityId,
                      options: [
                        for (final city in cubit.cities)
                          (value: city.id, label: city.name),
                      ],
                      onChanged: (id) => setState(() => _cityId = id),
                    ),
                    const SizedBox(height: AppDimensions.space22),
                    AppButton(
                      label: 'إنشاء الحساب',
                      isLoading: state is EmployerProfileSaving,
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

class _TypeOption extends StatelessWidget {
  const _TypeOption({
    required this.label,
    required this.icon,
    required this.selected,
    required this.onTap,
  });

  final String label;
  final IconData icon;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Pressable(
        onTap: onTap,
        child: AnimatedContainer(
          duration: AppMotion.press,
          height: 50,
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: selected ? AppColors.warningBg : AppColors.surface,
            border: Border.all(
              color: selected ? AppColors.amber : const Color(0xFFECEAE3),
              width: selected ? 1.5 : 1,
            ),
            borderRadius: BorderRadius.circular(14),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                icon,
                size: 19,
                color: selected ? AppColors.warningFg : AppColors.textMuted,
              ),
              const SizedBox(width: 7),
              Text(
                label,
                style: AppTextStyles.titleSm.copyWith(
                  fontWeight: FontWeight.w800,
                  color: selected ? AppColors.warningFg : AppColors.textBody,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
