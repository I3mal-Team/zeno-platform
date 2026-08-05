import 'dart:io';

import 'package:file_picker/file_picker.dart';
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
import '../../data/models/organization_model.dart';
import '../manager/employer_profile_cubit/employer_profile_cubit.dart';

class RegisterEmployerView extends StatefulWidget {
  const RegisterEmployerView({this.organization, super.key});

  /// When set, the form opens in edit mode, pre-filled with the current data.
  final OrganizationModel? organization;

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
  File? _logo;

  bool get _isCompany => _type == 'company';
  bool get _isEditing => widget.organization != null;

  Future<void> _pickLogo() async {
    final result = await FilePicker.platform.pickFiles(type: FileType.image);
    final path = result?.files.single.path;
    if (path != null) setState(() => _logo = File(path));
  }

  @override
  void initState() {
    super.initState();
    context.read<EmployerProfileCubit>().loadCities();

    // Seed the form from the existing organization so an edit doesn't start
    // blank and reset the account type. The CR is never returned by the API, so
    // it stays empty and is only re-sent if the user types a new one.
    final org = widget.organization;
    if (org != null) {
      _type = org.type;
      _name.text = org.name;
      _responsible.text = org.responsiblePersonName ?? '';
      _about.text = org.about ?? '';
      _cityId = org.cityId;
    }
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

    final responsible = _responsible.text.trim();
    final registration = _registration.text.trim();
    final about = _about.text.trim();

    context.read<EmployerProfileCubit>().saveWithLogo(
      SaveOrganizationParam(
        type: _type,
        name: _name.text.trim(),
        responsiblePersonName: responsible.isEmpty ? null : responsible,
        commercialRegistration: _isCompany && registration.isNotEmpty
            ? registration
            : null,
        cityId: _cityId,
        about: about.isEmpty ? null : about,
      ),
      _logo,
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
            colors: [Color(0xFFFFFFFF), Color(0xFFEDF1EC)],
            stops: [0, .7],
          ),
        ),
        child: SafeArea(
          child: BlocConsumer<EmployerProfileCubit, EmployerProfileState>(
            listener: (context, state) {
              if (state is EmployerProfileSaved) {
                if (_isEditing) {
                  AppToast.success(context, 'تم حفظ التعديلات.');
                  context.pop();
                } else {
                  context.go(RoutesKeys.employerJobs);
                }
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
                      _isEditing ? 'تعديل بيانات جهتك' : 'بيانات صاحب العمل',
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
                    Center(
                      child: _LogoPicker(
                        file: _logo,
                        existingUrl: widget.organization?.logoUrl,
                        onTap: _pickLogo,
                      ),
                    ),
                    const SizedBox(height: AppDimensions.space20),
                    Text(
                      'نوع الحساب',
                      style: AppTextStyles.caption.copyWith(
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        color: const Color(0xFF869089),
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
                    // The name is collected for both types (like the website); an
                    // individual enters their own name as the account name.
                    AppField(
                      controller: _name,
                      hint: _isCompany ? 'اسم المنشأة' : 'الاسم الكامل',
                      validator: (value) => (value ?? '').trim().length < 2
                          ? (_isCompany ? 'أدخل اسم المنشأة' : 'أدخل الاسم')
                          : null,
                    ),
                    const SizedBox(height: 13),
                    if (_isCompany) ...[
                      AppField(
                        controller: _registration,
                        hint: _isEditing
                            ? 'رقم السجل التجاري (اتركه فارغاً لعدم التغيير)'
                            : 'رقم السجل التجاري',
                        keyboardType: TextInputType.number,
                        textDirection: TextDirection.rtl,
                        inputFormatters: [
                          FilteringTextInputFormatter.digitsOnly,
                          LengthLimitingTextInputFormatter(10),
                        ],
                        validator: (value) {
                          final cr = (value ?? '').trim();
                          // Required on create; on edit an empty value means
                          // "keep the current CR".
                          if (!_isEditing && cr.length != 10) {
                            return 'السجل التجاري 10 أرقام';
                          }
                          if (cr.isNotEmpty && cr.length != 10) {
                            return 'السجل التجاري 10 أرقام';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 13),
                    ],
                    AppField(
                      controller: _responsible,
                      hint: 'اسم الشخص المسؤول (اختياري)',
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
                    const SizedBox(height: 13),
                    AppField(
                      controller: _about,
                      hint: 'نبذة عن جهتك (اختياري)',
                      maxLines: 4,
                    ),
                    const SizedBox(height: AppDimensions.space22),
                    AppButton(
                      label: _isEditing ? 'حفظ التعديلات' : 'إنشاء الحساب',
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

/// Optional organization logo, picked during sign-up or edit. A failed upload
/// never blocks saving. Shows a freshly picked file, else the existing logo.
class _LogoPicker extends StatelessWidget {
  const _LogoPicker({
    required this.file,
    required this.existingUrl,
    required this.onTap,
  });

  final File? file;
  final String? existingUrl;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final DecorationImage? image = file != null
        ? DecorationImage(image: FileImage(file!), fit: BoxFit.cover)
        : (existingUrl != null
              ? DecorationImage(
                  image: NetworkImage(existingUrl!),
                  fit: BoxFit.cover,
                )
              : null);

    return Pressable(
      onTap: onTap,
      child: Column(
        children: [
          Stack(
            children: [
              Container(
                width: 96,
                height: 96,
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(color: AppColors.amber, width: 2),
                  image: image,
                ),
                child: image == null
                    ? const Icon(
                        Icons.storefront_rounded,
                        size: 42,
                        color: Color(0xFFC9C4B9),
                      )
                    : null,
              ),
              Positioned(
                right: -2,
                bottom: -2,
                child: Container(
                  width: 30,
                  height: 30,
                  decoration: const BoxDecoration(
                    color: AppColors.amber,
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.camera_alt_rounded,
                    size: 16,
                    color: AppColors.textStrong,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            image == null ? 'إضافة شعار (اختياري)' : 'تغيير الشعار',
            style: AppTextStyles.caption.copyWith(
              fontWeight: FontWeight.w700,
              color: AppColors.textMuted,
            ),
          ),
        ],
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
              color: selected ? AppColors.amber : const Color(0xFFE5EAE6),
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
