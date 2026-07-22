import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../../../core/styles/category_visuals.dart';
import '../../data/models/application_model.dart';
import '../manager/applications_cubit/applications_cubit.dart';

class MyApplicationsView extends StatelessWidget {
  const MyApplicationsView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 12, 20, 12),
              child: Align(
                alignment: AlignmentDirectional.centerStart,
                child: Text('طلباتي', style: AppTextStyles.displayLg),
              ),
            ),
            Expanded(
              child: BlocBuilder<ApplicationsCubit, ApplicationsState>(
                builder: (context, state) => switch (state) {
                  ApplicationsLoading() => const Center(
                    child: CircularProgressIndicator(color: AppColors.amber),
                  ),
                  ApplicationsEmpty() => const _Message(
                    icon: Icons.description_outlined,
                    text: 'لم تقدّم على أي وظيفة بعد.',
                  ),
                  ApplicationsFailed(:final failure) => _Message(
                    icon: Icons.wifi_off_rounded,
                    text: failure.message,
                  ),
                  ApplicationsLoaded(:final applications) => RefreshIndicator(
                    color: AppColors.amber,
                    onRefresh: () => context.read<ApplicationsCubit>().load(),
                    child: ListView.separated(
                      padding: const EdgeInsets.fromLTRB(20, 4, 20, 24),
                      itemCount: applications.length,
                      separatorBuilder: (_, _) => const SizedBox(height: 12),
                      itemBuilder: (_, index) =>
                          _ApplicationCard(application: applications[index]),
                    ),
                  ),
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ApplicationCard extends StatelessWidget {
  const _ApplicationCard({required this.application});

  final ApplicationModel application;

  @override
  Widget build(BuildContext context) {
    final (tint, _) = AppColors.categoryTint(application.categoryCode);

    return Container(
      padding: const EdgeInsets.all(15),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.border),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  color: tint,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(
                  CategoryVisuals.icon(application.categoryCode),
                  color: Colors.white,
                  size: 23,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      application.jobTitle ?? 'وظيفة',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: AppTextStyles.titleMd,
                    ),
                    const SizedBox(height: 2),
                    Text(
                      application.organizationName ?? '',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: AppTextStyles.caption.copyWith(
                        fontWeight: FontWeight.w600,
                        color: AppColors.textMuted,
                      ),
                    ),
                  ],
                ),
              ),
              _StatusBadge(
                status: application.status,
                label: application.statusLabel,
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Text(
                'رقم الطلب ',
                style: AppTextStyles.caption.copyWith(
                  color: AppColors.textMuted,
                ),
              ),
              Text(
                application.reference,
                style: AppTextStyles.caption.copyWith(
                  fontWeight: FontWeight.w800,
                  color: AppColors.textStrong,
                ),
              ),
              const Spacer(),
              if (application.isDecidable)
                TextButton(
                  onPressed: () => _confirmWithdraw(context),
                  child: Text(
                    'سحب الطلب',
                    style: AppTextStyles.caption.copyWith(
                      fontWeight: FontWeight.w800,
                      color: AppColors.errorFg,
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }

  void _confirmWithdraw(BuildContext context) {
    final cubit = context.read<ApplicationsCubit>();

    showDialog<void>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text('سحب الطلب', style: AppTextStyles.titleMd),
        content: Text(
          'هل تريد سحب طلبك على هذه الوظيفة؟',
          style: AppTextStyles.bodyMd,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(dialogContext).pop(),
            child: const Text('إلغاء'),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(dialogContext).pop();
              cubit.withdraw(application.reference);
            },
            child: Text('سحب', style: TextStyle(color: AppColors.errorFg)),
          ),
        ],
      ),
    );
  }
}

class _StatusBadge extends StatelessWidget {
  const _StatusBadge({required this.status, required this.label});

  final String status;
  final String label;

  @override
  Widget build(BuildContext context) {
    final (fg, bg) = switch (status) {
      'accepted' => (AppColors.successFg, AppColors.successBg),
      'rejected' => (AppColors.errorFg, AppColors.errorBg),
      'withdrawn' => (AppColors.neutralFg, AppColors.neutralBg),
      _ => (AppColors.warningFg, AppColors.warningBg),
    };

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(9),
      ),
      child: Text(
        label,
        style: AppTextStyles.caption.copyWith(
          fontSize: 11.5,
          fontWeight: FontWeight.w800,
          color: fg,
        ),
      ),
    );
  }
}

class _Message extends StatelessWidget {
  const _Message({required this.icon, required this.text});

  final IconData icon;
  final String text;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 46, color: AppColors.textMuted),
          const SizedBox(height: 14),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 40),
            child: Text(
              text,
              textAlign: TextAlign.center,
              style: AppTextStyles.bodyMd.copyWith(color: AppColors.textBody),
            ),
          ),
        ],
      ),
    );
  }
}
