import 'dart:io';

import 'package:file_picker/file_picker.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:latlong2/latlong.dart';

import '../../../../core/components/app_button.dart';
import '../../../../core/components/app_field.dart';
import '../../../../core/components/app_select_field.dart';
import '../../../../core/components/app_toast.dart';
import '../../../../core/components/map_picker_screen.dart';
import '../../../../core/components/app_top_bar.dart';
import '../../../../core/motion/motion.dart';
import '../../../../core/params/profile_params.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_dimensions.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../data/models/candidate_profile_model.dart';
import '../manager/profile_cubit/profile_cubit.dart';

class RegisterCandidateView extends StatefulWidget {
  const RegisterCandidateView({super.key, this.profile});

  /// When set, this is an edit of an existing profile: the form opens seeded
  /// with its values instead of blank.
  final CandidateProfileModel? profile;

  @override
  State<RegisterCandidateView> createState() => _RegisterCandidateViewState();
}

class _RegisterCandidateViewState extends State<RegisterCandidateView> {
  final _formKey = GlobalKey<FormState>();
  final _fullName = TextEditingController();
  final _nationalId = TextEditingController();
  final _age = TextEditingController();
  final _jobTitle = TextEditingController();
  final _years = TextEditingController();
  final _bio = TextEditingController();

  int? _cityId;
  String? _nationalityCode;
  String? _gender;
  File? _avatar;
  double? _latitude;
  double? _longitude;

  Future<void> _pickAvatar() async {
    final result = await FilePicker.platform.pickFiles(type: FileType.image);
    final path = result?.files.single.path;
    if (path != null) setState(() => _avatar = File(path));
  }

  Future<void> _pickLocationOnMap() async {
    final initial = (_latitude != null && _longitude != null)
        ? LatLng(_latitude!, _longitude!)
        : null;
    final picked = await MapPickerScreen.show(context, initial: initial);
    if (picked != null) {
      setState(() {
        _latitude = picked.latitude;
        _longitude = picked.longitude;
      });
    }
  }

  @override
  void initState() {
    super.initState();
    _seedFromProfile();
    _loadCatalogs();
  }

  /// Open an edit with the current values in place. The national id is never
  /// returned by the API, so it stays blank and is only re-sent if retyped.
  void _seedFromProfile() {
    final p = widget.profile;
    if (p == null) return;

    _fullName.text = p.fullName;
    if (p.age != null) _age.text = p.age.toString();
    _jobTitle.text = p.jobTitle ?? '';
    if (p.yearsOfExperience != null) {
      _years.text = p.yearsOfExperience.toString();
    }
    _bio.text = p.bio ?? '';
    _gender = p.gender;
    _nationalityCode = p.nationalityCode;
    _cityId = p.city?.id;
  }

  /// Cities and nationalities feed two dropdowns; both are set on the cubit
  /// without emitting, so a setState here makes the first build see them.
  Future<void> _loadCatalogs() async {
    final cubit = context.read<ProfileCubit>();
    await Future.wait([cubit.loadCities(), cubit.loadCountries()]);
    if (mounted) setState(() {});
  }

  @override
  void dispose() {
    for (final controller in [
      _fullName,
      _nationalId,
      _age,
      _jobTitle,
      _years,
      _bio,
    ]) {
      controller.dispose();
    }
    super.dispose();
  }

  void _submit() {
    final formValid = _formKey.currentState?.validate() ?? false;

    // City and nationality are dropdowns, not form fields, so they are checked
    // here rather than through the form validator.
    final missing = <String>[
      if (_cityId == null) 'المدينة',
      if (_nationalityCode == null) 'الجنسية',
    ];

    if (missing.isNotEmpty) {
      AppToast.error(context, 'من فضلك اختر ${missing.join(' و')}');
    }

    if (!formValid || missing.isNotEmpty) return;

    final nationalId = _nationalId.text.trim();

    context.read<ProfileCubit>().saveWithAvatar(
      SaveCandidateProfileParam(
        fullName: _fullName.text.trim(),
        nationalId: nationalId.isEmpty ? null : nationalId,
        nationalIdType: _nationalIdType(nationalId),
        birthDate: _birthDateFromAge(),
        gender: _gender,
        nationalityCode: _nationalityCode,
        cityId: _cityId,
        jobTitle: _jobTitle.text.trim().isEmpty ? null : _jobTitle.text.trim(),
        yearsOfExperience: int.tryParse(_years.text),
        bio: _bio.text.trim().isEmpty ? null : _bio.text.trim(),
        latitude: _latitude,
        longitude: _longitude,
      ),
      _avatar,
    );
  }

  /// Saudi national IDs start with 1, iqamas with 2.
  String? _nationalIdType(String id) {
    if (id.isEmpty) return null;
    return id.startsWith('2') ? 'iqama' : 'national';
  }

  /// The form collects age; the API stores a birth date, so derive one.
  String? _birthDateFromAge() {
    final age = int.tryParse(_age.text.trim());
    if (age == null) return null;

    final now = DateTime.now();
    final date = DateTime(now.year - age, now.month, now.day);
    final month = date.month.toString().padLeft(2, '0');
    final day = date.day.toString().padLeft(2, '0');
    return '${date.year}-$month-$day';
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
          child: BlocConsumer<ProfileCubit, ProfileState>(
            listener: (context, state) {
              if (state is ProfileSaved) {
                if (widget.profile != null) {
                  AppToast.success(context, 'تم حفظ التعديلات.');
                  context.pop();
                } else {
                  context.go(RoutesKeys.browse);
                }
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
                      widget.profile != null ? 'تعديل ملفك' : 'أكمل بياناتك',
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
                    Center(
                      child: _AvatarPicker(
                        file: _avatar,
                        existingUrl: widget.profile?.avatarUrl,
                        onTap: _pickAvatar,
                      ),
                    ),
                    const SizedBox(height: AppDimensions.space16),
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
                      child: AppField(
                        controller: _nationalId,
                        hint: 'رقم الهوية أو الإقامة',
                        keyboardType: TextInputType.number,
                        textDirection: TextDirection.ltr,
                        inputFormatters: [
                          FilteringTextInputFormatter.digitsOnly,
                          LengthLimitingTextInputFormatter(10),
                        ],
                        validator: (value) {
                          final id = (value ?? '').trim();
                          if (id.isEmpty) return 'أدخل رقم الهوية أو الإقامة';
                          if (id.length != 10) {
                            return 'رقم الهوية يتكوّن من 10 أرقام';
                          }
                          return null;
                        },
                      ),
                    ),
                    const SizedBox(height: 13),
                    Entrance(
                      index: 2,
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(
                            flex: 17,
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
                          const SizedBox(width: 12),
                          Expanded(
                            flex: 10,
                            child: AppField(
                              controller: _age,
                              hint: 'العمر',
                              keyboardType: TextInputType.number,
                              textDirection: TextDirection.ltr,
                              inputFormatters: [
                                FilteringTextInputFormatter.digitsOnly,
                                LengthLimitingTextInputFormatter(2),
                              ],
                              validator: (value) {
                                final text = (value ?? '').trim();
                                if (text.isEmpty) return null;
                                final age = int.tryParse(text);
                                if (age == null || age < 16 || age > 80) {
                                  return 'عمر غير صحيح';
                                }
                                return null;
                              },
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 13),
                    Entrance(
                      index: 3,
                      child: AppSelectField<String>(
                        hint: 'الجنسية',
                        value: _nationalityCode,
                        options: [
                          for (final country in cubit.countries)
                            (value: country.iso2, label: country.name),
                        ],
                        onChanged: (code) =>
                            setState(() => _nationalityCode = code),
                      ),
                    ),
                    const SizedBox(height: 13),
                    Entrance(
                      index: 4,
                      child: _GenderSelector(
                        value: _gender,
                        onChanged: (g) => setState(() => _gender = g),
                      ),
                    ),
                    const SizedBox(height: 13),
                    Entrance(
                      index: 5,
                      child: AppField(
                        controller: _jobTitle,
                        hint: 'المهنة الحالية أو المستهدفة',
                      ),
                    ),
                    const SizedBox(height: 13),
                    Entrance(
                      index: 5,
                      child: AppField(
                        controller: _years,
                        hint: 'سنوات الخبرة',
                        keyboardType: TextInputType.number,
                        textDirection: TextDirection.ltr,
                        inputFormatters: [
                          FilteringTextInputFormatter.digitsOnly,
                          LengthLimitingTextInputFormatter(2),
                        ],
                      ),
                    ),
                    const SizedBox(height: 13),
                    Entrance(
                      index: 6,
                      child: AppField(
                        controller: _bio,
                        hint: 'نبذة مختصرة عنك (اختياري)',
                        maxLines: 4,
                      ),
                    ),
                    const SizedBox(height: 13),
                    Entrance(
                      index: 7,
                      child: _MapLocationButton(
                        located: _latitude != null,
                        onTap: _pickLocationOnMap,
                      ),
                    ),
                    const SizedBox(height: AppDimensions.space22),
                    AppButton(
                      label: widget.profile != null
                          ? 'حفظ التعديلات'
                          : 'إنشاء الحساب',
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

/// Optional profile photo, picked during sign-up. A failed upload never blocks
/// registration — the account is created either way.
class _AvatarPicker extends StatelessWidget {
  const _AvatarPicker({
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
                  shape: BoxShape.circle,
                  border: Border.all(color: AppColors.amber, width: 2),
                  image: image,
                ),
                child: image == null
                    ? const Icon(
                        Icons.person_rounded,
                        size: 44,
                        color: Color(0xFFC9C4B9),
                      )
                    : null,
              ),
              Positioned(
                right: 0,
                bottom: 0,
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
            image == null ? 'إضافة صورة (اختياري)' : 'تغيير الصورة',
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

/// Opens the map picker to pin the candidate's location, so "nearby jobs" work
/// from where they actually are rather than the city centre. Optional.
class _MapLocationButton extends StatelessWidget {
  const _MapLocationButton({required this.located, required this.onTap});

  final bool located;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final color = located ? AppColors.successFg : AppColors.charcoalSoft;

    return Pressable(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 15),
        decoration: BoxDecoration(
          color: located ? AppColors.successBg : AppColors.surface,
          border: Border.all(
            color: located ? AppColors.successFg : const Color(0xFFE5EAE6),
            width: 1.5,
          ),
          borderRadius: BorderRadius.circular(14),
        ),
        child: Row(
          children: [
            Icon(
              located ? Icons.check_circle_rounded : Icons.map_rounded,
              size: 20,
              color: color,
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                located
                    ? 'تم تحديد مكانك على الخريطة'
                    : 'تحديد مكاني على الخريطة (اختياري)',
                style: AppTextStyles.bodyMd.copyWith(
                  fontWeight: FontWeight.w800,
                  color: color,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// Two-segment gender picker (optional). Matches the app's chip styling.
class _GenderSelector extends StatelessWidget {
  const _GenderSelector({required this.value, required this.onChanged});

  final String? value;
  final ValueChanged<String> onChanged;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: _GenderOption(
            label: 'ذكر',
            icon: Icons.male_rounded,
            active: value == 'male',
            onTap: () => onChanged('male'),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: _GenderOption(
            label: 'أنثى',
            icon: Icons.female_rounded,
            active: value == 'female',
            onTap: () => onChanged('female'),
          ),
        ),
      ],
    );
  }
}

class _GenderOption extends StatelessWidget {
  const _GenderOption({
    required this.label,
    required this.icon,
    required this.active,
    required this.onTap,
  });

  final String label;
  final IconData icon;
  final bool active;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Container(
        height: 52,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: active ? AppColors.warningBg : AppColors.surface,
          border: Border.all(
            color: active ? AppColors.amber : const Color(0xFFE5EAE6),
            width: 1.5,
          ),
          borderRadius: BorderRadius.circular(14),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 19,
              color: active ? AppColors.warningFg : AppColors.textMuted,
            ),
            const SizedBox(width: 7),
            Text(
              label,
              style: AppTextStyles.bodyMd.copyWith(
                fontWeight: FontWeight.w800,
                color: active ? AppColors.warningFg : AppColors.textBody,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
