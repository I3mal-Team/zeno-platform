import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/components/app_button.dart';
import '../../../../core/components/app_toast.dart';
import '../../../../core/components/screen_background.dart';
import '../../../../core/databases/api/end_points.dart';
import '../../../../core/managers/user_cubit/user_cubit.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_dimensions.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../manager/otp_cubit/otp_cubit.dart';
import '../widgets/auth_header.dart';
import '../widgets/auth_hero.dart';
import '../widgets/otp_boxes.dart';

class OtpView extends StatelessWidget {
  const OtpView({super.key});

  @override
  Widget build(BuildContext context) {
    final fillController = OtpBoxesController();

    return Scaffold(
      body: ScreenBackground(
        child: SafeArea(
          child: BlocConsumer<OtpCubit, OtpState>(
            listener: (context, state) {
              switch (state) {
                case OtpVerified(:final session):
                  context.read<UserCubit>().onSignedIn(session.user);
                  final isEmployer =
                      context.read<OtpCubit>().role == 'employer';
                  // Employers branch on whether the account is new; job seekers
                  // branch on whether they've saved their profile yet, so a
                  // returning-but-incomplete candidate still lands on the form.
                  context.go(switch ((
                    isEmployer,
                    session.isNewUser,
                    session.profileCompleted,
                  )) {
                    (true, true, _) => RoutesKeys.registerEmployer,
                    (true, false, _) => RoutesKeys.employerJobs,
                    (false, _, false) => RoutesKeys.registerCandidate,
                    (false, _, true) => RoutesKeys.browse,
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
                padding: const EdgeInsets.fromLTRB(26, 10, 26, 28),
                child: Column(
                  children: [
                    const AuthHeader(),
                    const SizedBox(height: AppDimensions.space16),
                    const Center(
                      child: AuthHero(
                        icon: Icons.verified_user_rounded,
                        discSize: 90,
                      ),
                    ),
                    const SizedBox(height: AppDimensions.space20),
                    Text(
                      'أدخل رمز التحقق',
                      style: AppTextStyles.displayLg.copyWith(fontSize: 24),
                    ),
                    const SizedBox(height: AppDimensions.space8),
                    _SentToLine(phone: cubit.phone),
                    const SizedBox(height: AppDimensions.space26),
                    OtpBoxes(
                      length: OtpCubit.codeLength,
                      controller: fillController,
                      onChanged: cubit.onCodeChanged,
                    ),
                    const SizedBox(height: AppDimensions.space20),
                    _ResendRow(onResend: cubit.resend),
                    if (AppEnvironment.current == AppEnvironment.dev) ...[
                      const SizedBox(height: AppDimensions.space12),
                      _AutoFillButton(onTap: () => fillController.fill('4829')),
                    ],
                    const Spacer(),
                    AppButton(
                      label: 'تأكيد ومتابعة',
                      icon: Icons.check_circle_rounded,
                      dark: true,
                      isLoading: state is OtpVerifying,
                      onPressed: cubit.verify,
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

class _SentToLine extends StatelessWidget {
  const _SentToLine({required this.phone});

  final String phone;

  @override
  Widget build(BuildContext context) {
    return Text.rich(
      TextSpan(
        text: 'الرمز المكوّن من ${OtpCubit.codeLength} أرقام أُرسل إلى ',
        style: AppTextStyles.bodyLg.copyWith(color: AppColors.textMuted),
        children: [
          TextSpan(
            text: phone,
            style: AppTextStyles.bodyLg.copyWith(
              color: AppColors.textStrong,
              fontWeight: FontWeight.w800,
            ),
          ),
        ],
      ),
      textAlign: TextAlign.center,
    );
  }
}

class _AutoFillButton extends StatelessWidget {
  const _AutoFillButton({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 9),
        decoration: BoxDecoration(
          color: AppColors.warningBg,
          borderRadius: BorderRadius.circular(11),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.bolt_rounded,
              size: 17,
              color: AppColors.warningFg,
            ),
            const SizedBox(width: AppDimensions.space6),
            Text(
              'تعبئة تلقائية (تجريبي)',
              style: AppTextStyles.caption.copyWith(
                fontSize: 13,
                color: AppColors.warningFg,
              ),
            ),
          ],
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
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Text(
          'لم يصلك الرمز؟',
          style: AppTextStyles.bodyMd.copyWith(
            color: AppColors.textMuted,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(width: AppDimensions.space6),
        GestureDetector(
          onTap: canResend
              ? () async {
                  await widget.onResend();
                  if (mounted) _startCountdown();
                }
              : null,
          child: Text(
            canResend ? 'إعادة الإرسال' : 'إعادة الإرسال بعد $_remaining ث',
            style: AppTextStyles.titleSm.copyWith(
              color: canResend ? AppColors.warningFg : AppColors.textMuted,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
      ],
    );
  }
}
