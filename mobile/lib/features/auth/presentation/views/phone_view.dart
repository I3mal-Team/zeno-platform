import 'package:flutter/gestures.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../../core/components/app_button.dart';
import '../../../../core/components/app_toast.dart';
import '../../../../core/components/screen_background.dart';
import '../../../../core/databases/api/end_points.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_dimensions.dart';
import '../../../../core/styles/app_shadows.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../data/models/country_model.dart';
import '../manager/phone_cubit/phone_cubit.dart';
import '../widgets/auth_header.dart';
import '../widgets/auth_hero.dart';
import '../widgets/country_picker_sheet.dart';

class PhoneView extends StatelessWidget {
  const PhoneView({required this.role, super.key});

  final String role;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: ScreenBackground(
        child: SafeArea(
          child: BlocConsumer<PhoneCubit, PhoneState>(
            listener: (context, state) {
              if (state case PhoneCodeSent(:final phone, :final country)) {
                context.push(
                  '${RoutesKeys.otp}?phone=$phone&country=$country&role=$role',
                );
              }

              if (state case PhoneError(:final failure)) {
                AppToast.error(context, failure.message);
              }
            },
            builder: (context, state) {
              final cubit = context.read<PhoneCubit>();

              return Padding(
                padding: const EdgeInsets.fromLTRB(24, 10, 24, 28),
                child: Column(
                  children: [
                    const AuthHeader(),
                    const SizedBox(height: AppDimensions.space20),
                    const Center(
                      child: AuthHero(icon: Icons.smartphone_rounded),
                    ),
                    const SizedBox(height: AppDimensions.space22),
                    Text(
                      'أدخل رقم جوالك',
                      style: AppTextStyles.displayLg.copyWith(fontSize: 24),
                    ),
                    const SizedBox(height: AppDimensions.space8),
                    SizedBox(
                      width: 280,
                      child: Text(
                        'سنرسل لك رمز تحقق عبر رسالة نصية لتأكيد رقمك — لا حاجة لكلمة مرور.',
                        textAlign: TextAlign.center,
                        style: AppTextStyles.bodyLg.copyWith(
                          color: AppColors.textMuted,
                          height: 1.65,
                        ),
                      ),
                    ),
                    const SizedBox(height: AppDimensions.space26),
                    _PhoneField(
                      country: cubit.selectedCountry,
                      onPhoneChanged: cubit.onPhoneChanged,
                      onPickCountry: () => _pickCountry(context, cubit),
                    ),
                    const Spacer(),
                    AppButton(
                      label: 'متابعة',
                      isLoading: state is PhoneSubmitting,
                      onPressed: cubit.requestCode,
                    ),
                    const SizedBox(height: AppDimensions.space16),
                    const _LegalNotice(),
                  ],
                ),
              );
            },
          ),
        ),
      ),
    );
  }

  Future<void> _pickCountry(BuildContext context, PhoneCubit cubit) async {
    if (cubit.countries.isEmpty) return;

    final picked = await showCountryPickerSheet(
      context,
      countries: cubit.countries,
      selected: cubit.selectedCountry,
    );

    if (picked != null) cubit.onCountryChanged(picked);
  }
}

/// The consent line under the continue button. Both stores expect the legal
/// pages to be reachable from the app, so the two phrases are real links rather
/// than the plain sentence this used to be.
class _LegalNotice extends StatefulWidget {
  const _LegalNotice();

  @override
  State<_LegalNotice> createState() => _LegalNoticeState();
}

class _LegalNoticeState extends State<_LegalNotice> {
  // Recognizers hold a subscription each and must outlive a rebuild, so they
  // are owned by the state and disposed with it.
  final _terms = TapGestureRecognizer();
  final _privacy = TapGestureRecognizer();

  @override
  void initState() {
    super.initState();
    _terms.onTap = () => _open(EndPoints.terms);
    _privacy.onTap = () => _open(EndPoints.privacy);
  }

  @override
  void dispose() {
    _terms.dispose();
    _privacy.dispose();
    super.dispose();
  }

  Future<void> _open(String url) async {
    final opened = await launchUrl(
      Uri.parse(url),
      mode: LaunchMode.externalApplication,
    );

    if (!mounted) return;

    if (!opened) {
      AppToast.error(context, 'تعذّر فتح الصفحة.');
    }
  }

  @override
  Widget build(BuildContext context) {
    final base = AppTextStyles.caption.copyWith(
      fontSize: 12,
      fontWeight: FontWeight.w500,
      color: const Color(0xFFA8A296),
      height: 1.6,
    );

    final link = base.copyWith(
      fontWeight: FontWeight.w800,
      color: AppColors.charcoalSoft,
      decoration: TextDecoration.underline,
      decorationColor: AppColors.charcoalSoft,
    );

    return Text.rich(
      TextSpan(
        style: base,
        children: [
          const TextSpan(text: 'بالمتابعة فإنك توافق على '),
          TextSpan(text: 'الشروط والأحكام', style: link, recognizer: _terms),
          const TextSpan(text: ' و'),
          TextSpan(text: 'سياسة الخصوصية', style: link, recognizer: _privacy),
        ],
      ),
      textAlign: TextAlign.center,
    );
  }
}

class _PhoneField extends StatelessWidget {
  const _PhoneField({
    required this.country,
    required this.onPhoneChanged,
    required this.onPickCountry,
  });

  final CountryModel? country;
  final ValueChanged<String> onPhoneChanged;
  final VoidCallback onPickCountry;

  @override
  Widget build(BuildContext context) {
    // Forced LTR so the dial-code chip sits on the left and the number on the
    // right, mirroring the website's sign-in field regardless of app direction.
    return Directionality(
      textDirection: TextDirection.ltr,
      child: Row(
        children: [
          _CountryChip(country: country, onTap: onPickCountry),
          const SizedBox(width: AppDimensions.space10),
          Expanded(
            child: Container(
              height: AppDimensions.inputHeight,
              decoration: BoxDecoration(
                color: AppColors.surface,
                border: Border.all(color: const Color(0xFFE5EAE6)),
                borderRadius: BorderRadius.circular(16),
                boxShadow: AppShadows.chip,
              ),
              child: TextField(
                onChanged: onPhoneChanged,
                keyboardType: TextInputType.phone,
                textDirection: TextDirection.ltr,
                maxLength: 15,
                style: AppTextStyles.input.copyWith(
                  fontSize: 19,
                  fontWeight: FontWeight.w700,
                  letterSpacing: 1,
                ),
                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                decoration: InputDecoration(
                  counterText: '',
                  border: InputBorder.none,
                  hintText: country?.placeholder ?? '5X XXX XXXX',
                  hintStyle: AppTextStyles.input.copyWith(
                    fontSize: 19,
                    fontWeight: FontWeight.w700,
                    color: AppColors.textMuted,
                  ),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 18),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _CountryChip extends StatelessWidget {
  const _CountryChip({required this.country, required this.onTap});

  final CountryModel? country;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        height: AppDimensions.inputHeight,
        padding: const EdgeInsets.symmetric(horizontal: 15),
        decoration: BoxDecoration(
          color: AppColors.surface,
          border: Border.all(color: const Color(0xFFE5EAE6)),
          borderRadius: BorderRadius.circular(16),
          boxShadow: AppShadows.chip,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(country?.flag ?? '🌐', style: const TextStyle(fontSize: 18)),
            const SizedBox(width: AppDimensions.space6),
            Text(
              country?.dialCode ?? '+966',
              style: AppTextStyles.input.copyWith(fontWeight: FontWeight.w800),
            ),
            const Icon(
              Icons.keyboard_arrow_down_rounded,
              size: 18,
              color: AppColors.textMuted,
            ),
          ],
        ),
      ),
    );
  }
}
