import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/components/app_button.dart';
import '../../../../core/components/app_toast.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_dimensions.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../manager/phone_cubit/phone_cubit.dart';

class PhoneView extends StatelessWidget {
  const PhoneView({required this.role, super.key});

  final String role;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(),
      body: BlocConsumer<PhoneCubit, PhoneState>(
        listener: (context, state) {
          if (state case PhoneCodeSent(:final phone)) {
            context.push('${RoutesKeys.otp}?phone=$phone&role=$role');
          }

          if (state case PhoneError(:final failure)) {
            AppToast.error(context, failure.message);
          }
        },
        builder: (context, state) {
          final cubit = context.read<PhoneCubit>();

          return Padding(
            padding: const EdgeInsets.all(AppDimensions.screenPadding),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('أدخل رقم جوالك', style: AppTextStyles.displayLg),
                const SizedBox(height: AppDimensions.space8),
                Text(
                  'سنرسل لك رمز تحقق عبر رسالة نصية لتأكيد رقمك — لا حاجة لكلمة مرور.',
                  style: AppTextStyles.bodyLg,
                ),
                const SizedBox(height: AppDimensions.space32),
                _PhoneField(onChanged: cubit.onPhoneChanged),
                const Spacer(),
                AppButton(
                  label: 'متابعة',
                  isLoading: state is PhoneSubmitting,
                  onPressed: cubit.requestCode,
                ),
                const SizedBox(height: AppDimensions.space16),
              ],
            ),
          );
        },
      ),
    );
  }
}

class _PhoneField extends StatelessWidget {
  const _PhoneField({required this.onChanged});

  final ValueChanged<String> onChanged;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: AppDimensions.inputHeight,
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.borderStrong),
        borderRadius: BorderRadius.circular(AppDimensions.radiusControl),
      ),
      child: Row(
        children: [
          Padding(
            padding: const EdgeInsetsDirectional.only(
              start: AppDimensions.space16,
              end: AppDimensions.space12,
            ),
            child: Text('+966', style: AppTextStyles.input),
          ),
          Container(width: 1, height: 24, color: AppColors.border),
          Expanded(
            child: TextField(
              onChanged: onChanged,
              keyboardType: TextInputType.phone,
              textDirection: TextDirection.ltr,
              maxLength: 10,
              style: AppTextStyles.input,
              inputFormatters: [FilteringTextInputFormatter.digitsOnly],
              decoration: InputDecoration(
                counterText: '',
                border: InputBorder.none,
                hintText: '5X XXX XXXX',
                hintStyle: AppTextStyles.input.copyWith(
                  color: AppColors.textMuted,
                ),
                contentPadding: const EdgeInsetsDirectional.symmetric(
                  horizontal: AppDimensions.space12,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
