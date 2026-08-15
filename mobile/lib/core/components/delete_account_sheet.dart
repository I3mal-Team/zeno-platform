import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../managers/user_cubit/user_cubit.dart';
import '../motion/motion.dart';
import '../routing/routes_keys.dart';
import '../styles/app_colors.dart';
import '../styles/app_dimensions.dart';
import '../styles/app_text_styles.dart';
import 'app_toast.dart';

/// Account closure, offered from both the candidate and the employer account
/// screens. Apple (guideline 5.1.1(v)) and Play both require the path to live
/// inside the app and not behind a support request, and the consequences to be
/// spelled out before it runs.
Future<void> showDeleteAccountSheet(BuildContext context) {
  return showModalBottomSheet<void>(
    context: context,
    backgroundColor: Colors.transparent,
    isScrollControlled: true,
    // Deleting an account by tapping the backdrop by accident would be a bad
    // day for everyone.
    isDismissible: false,
    enableDrag: false,
    builder: (_) => BlocProvider.value(
      value: context.read<UserCubit>(),
      child: const _DeleteAccountSheet(),
    ),
  );
}

/// The entry point both account screens render, directly under sign-out. Kept
/// deliberately plain so it never competes with the primary actions, but it
/// stays on the account screen itself — reviewers reject a deletion path that
/// takes more than a couple of taps to find.
class DeleteAccountButton extends StatelessWidget {
  const DeleteAccountButton({super.key});

  @override
  Widget build(BuildContext context) {
    return TextButton.icon(
      onPressed: () => showDeleteAccountSheet(context),
      icon: const Icon(
        Icons.delete_outline_rounded,
        size: 18,
        color: AppColors.textMuted,
      ),
      label: Text(
        'حذف الحساب',
        style: AppTextStyles.bodySm.copyWith(
          fontWeight: FontWeight.w700,
          color: AppColors.textMuted,
          decoration: TextDecoration.underline,
          decorationColor: AppColors.textMuted,
        ),
      ),
    );
  }
}

class _DeleteAccountSheet extends StatefulWidget {
  const _DeleteAccountSheet();

  @override
  State<_DeleteAccountSheet> createState() => _DeleteAccountSheetState();
}

class _DeleteAccountSheetState extends State<_DeleteAccountSheet> {
  bool _isDeleting = false;

  static const _consequences = [
    'ملفك الشخصي وبياناتك ومرفقاتك',
    'طلبات التوظيف التي قدّمتها',
    'محادثاتك وإشعاراتك',
  ];

  Future<void> _confirm() async {
    setState(() => _isDeleting = true);

    // Resolved before the await: popping the sheet tears this element down, so
    // the context is no longer safe to look either up by the time we navigate.
    final router = GoRouter.of(context);

    final failure = await context.read<UserCubit>().deleteAccount();

    if (!mounted) return;

    if (failure != null) {
      setState(() => _isDeleting = false);
      AppToast.error(context, failure.message);

      return;
    }

    Navigator.of(context).pop();
    router.go(RoutesKeys.rolePicker);
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.vertical(
          top: Radius.circular(AppDimensions.radiusSheet),
        ),
      ),
      padding: const EdgeInsets.fromLTRB(22, 22, 22, 34),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  color: AppColors.errorBg,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(
                  Icons.delete_forever_rounded,
                  color: AppColors.errorFg,
                  size: 26,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text('حذف الحساب', style: AppTextStyles.titleMd),
              ),
            ],
          ),
          const SizedBox(height: AppDimensions.space16),
          Text(
            'سيتم حذف حسابك نهائيًا، ولا يمكن التراجع عن ذلك. سيُحذف:',
            style: AppTextStyles.bodyMd.copyWith(height: 1.7),
          ),
          const SizedBox(height: AppDimensions.space12),
          for (final line in _consequences)
            Padding(
              padding: const EdgeInsets.only(bottom: 8),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Padding(
                    padding: EdgeInsets.only(top: 5),
                    child: Icon(
                      Icons.close_rounded,
                      size: 15,
                      color: AppColors.errorFg,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(line, style: AppTextStyles.bodySm),
                  ),
                ],
              ),
            ),
          const SizedBox(height: AppDimensions.space8),
          Text(
            'يمكنك التسجيل من جديد بنفس رقم الجوال في أي وقت.',
            style: AppTextStyles.caption,
          ),
          const SizedBox(height: AppDimensions.space20),
          _DangerButton(
            label: 'حذف الحساب نهائيًا',
            isLoading: _isDeleting,
            onTap: _isDeleting ? null : _confirm,
          ),
          const SizedBox(height: AppDimensions.space8),
          TextButton(
            onPressed: _isDeleting ? null : () => Navigator.of(context).pop(),
            child: Text(
              'إلغاء',
              style: AppTextStyles.button.copyWith(
                fontSize: 15,
                color: AppColors.textMuted,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _DangerButton extends StatelessWidget {
  const _DangerButton({
    required this.label,
    required this.isLoading,
    required this.onTap,
  });

  final String label;
  final bool isLoading;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(16),
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: AppColors.errorFg,
          borderRadius: BorderRadius.circular(AppDimensions.radiusControl),
        ),
        child: isLoading
            ? const SizedBox(
                width: 21,
                height: 21,
                child: CircularProgressIndicator(
                  strokeWidth: 2.4,
                  color: Colors.white,
                ),
              )
            : Text(
                label,
                style: AppTextStyles.button.copyWith(
                  fontSize: 15,
                  color: Colors.white,
                ),
              ),
      ),
    );
  }
}
