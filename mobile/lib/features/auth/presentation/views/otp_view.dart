import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/components/app_button.dart';
import '../../../../core/components/app_toast.dart';
import '../../../../core/managers/user_cubit/user_cubit.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_dimensions.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../manager/otp_cubit/otp_cubit.dart';

class OtpView extends StatelessWidget {
  const OtpView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(),
      body: BlocConsumer<OtpCubit, OtpState>(
        listener: (context, state) {
          switch (state) {
            case OtpVerified(:final session):
              context.read<UserCubit>().onSignedIn(session.user);
              final isEmployer = context.read<OtpCubit>().role == 'employer';
              context.go(switch ((session.isNewUser, isEmployer)) {
                (true, true) => RoutesKeys.registerEmployer,
                (true, false) => RoutesKeys.registerCandidate,
                (false, true) => RoutesKeys.employerJobs,
                (false, false) => RoutesKeys.browse,
              });
            case OtpResent():
              AppToast.success(context, 'تم إرسال رمز جديد.');
            case OtpError(:final failure):
              AppToast.error(context, failure.message);
            default:
              break;
          }
        },
        builder: (context, state) {
          final cubit = context.read<OtpCubit>();

          return Padding(
            padding: const EdgeInsets.all(AppDimensions.screenPadding),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('أدخل رمز التحقق', style: AppTextStyles.displayLg),
                const SizedBox(height: AppDimensions.space8),
                Text(
                  'الرمز المكوّن من ${OtpCubit.codeLength} أرقام أُرسل إلى ${cubit.phone}',
                  style: AppTextStyles.bodyLg,
                ),
                const SizedBox(height: AppDimensions.space32),
                _CodeField(onChanged: cubit.onCodeChanged),
                const SizedBox(height: AppDimensions.space20),
                _ResendRow(onResend: cubit.resend),
                const Spacer(),
                AppButton(
                  label: 'تأكيد ومتابعة',
                  isLoading: state is OtpVerifying,
                  onPressed: cubit.verify,
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

class _CodeField extends StatelessWidget {
  const _CodeField({required this.onChanged});

  final ValueChanged<String> onChanged;

  @override
  Widget build(BuildContext context) {
    return TextField(
      onChanged: onChanged,
      keyboardType: TextInputType.number,
      textAlign: TextAlign.center,
      textDirection: TextDirection.ltr,
      maxLength: OtpCubit.codeLength,
      autofocus: true,
      style: AppTextStyles.otpDigit.copyWith(letterSpacing: 18),
      inputFormatters: [FilteringTextInputFormatter.digitsOnly],
      decoration: InputDecoration(
        counterText: '',
        filled: true,
        fillColor: AppColors.surface,
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.radiusControl),
          borderSide: const BorderSide(color: AppColors.borderStrong),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(AppDimensions.radiusControl),
          borderSide: const BorderSide(color: AppColors.charcoal, width: 1.5),
        ),
        contentPadding: const EdgeInsets.symmetric(
          vertical: AppDimensions.space16,
        ),
      ),
    );
  }
}

/// The countdown starts on the cooldown the server reports, so the client never
/// guesses how long the wait is.
class _ResendRow extends StatefulWidget {
  const _ResendRow({required this.onResend});

  final Future<void> Function() onResend;

  @override
  State<_ResendRow> createState() => _ResendRowState();
}

class _ResendRowState extends State<_ResendRow> {
  static const _cooldown = 60;

  Timer? _timer;
  int _remaining = _cooldown;

  @override
  void initState() {
    super.initState();
    _startCountdown();
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  void _startCountdown() {
    setState(() => _remaining = _cooldown);

    _timer?.cancel();
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (_remaining <= 1) {
        timer.cancel();
      }
      setState(() => _remaining--);
    });
  }

  @override
  Widget build(BuildContext context) {
    final canResend = _remaining <= 0;

    return Row(
      children: [
        Text('لم يصلك الرمز؟', style: AppTextStyles.bodyMd),
        const SizedBox(width: AppDimensions.space6),
        TextButton(
          onPressed: canResend
              ? () async {
                  await widget.onResend();
                  if (mounted) _startCountdown();
                }
              : null,
          child: Text(
            canResend ? 'إعادة الإرسال' : 'إعادة الإرسال بعد $_remaining ث',
            style: AppTextStyles.titleSm.copyWith(
              color: canResend ? AppColors.textStrong : AppColors.textMuted,
            ),
          ),
        ),
      ],
    );
  }
}
