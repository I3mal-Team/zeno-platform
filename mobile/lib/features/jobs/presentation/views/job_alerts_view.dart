import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../core/motion/motion.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../data/models/job_alert_model.dart';
import '../manager/job_alerts_cubit/job_alerts_cubit.dart';

class JobAlertsView extends StatelessWidget {
  const JobAlertsView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: Column(
        children: [
          const _Header(),
          Expanded(
            child: BlocBuilder<JobAlertsCubit, JobAlertsState>(
              builder: (context, state) => switch (state) {
                JobAlertsLoading() => const Center(
                  child: CircularProgressIndicator(color: AppColors.amber),
                ),
                JobAlertsFailed(:final failure) => Center(
                  child: Text(failure.message, style: AppTextStyles.bodyMd),
                ),
                JobAlertsLoaded(:final alerts) =>
                  alerts.isEmpty
                      ? const _Empty()
                      : ListView.separated(
                          padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
                          itemCount: alerts.length,
                          separatorBuilder: (_, _) =>
                              const SizedBox(height: 12),
                          itemBuilder: (_, i) => _AlertCard(alert: alerts[i]),
                        ),
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _AlertCard extends StatelessWidget {
  const _AlertCard({required this.alert});

  final JobAlertModel alert;

  @override
  Widget build(BuildContext context) {
    final chips = alert.chips;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.border),
        borderRadius: BorderRadius.circular(18),
      ),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: AppColors.warningBg,
              borderRadius: BorderRadius.circular(13),
            ),
            child: const Icon(
              Icons.notifications_active_rounded,
              color: AppColors.warningFg,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: chips.isEmpty
                ? Text(
                    'كل الوظائف الجديدة',
                    style: AppTextStyles.bodyMd.copyWith(
                      fontWeight: FontWeight.w700,
                    ),
                  )
                : Wrap(
                    spacing: 6,
                    runSpacing: 6,
                    children: [for (final chip in chips) _Chip(chip)],
                  ),
          ),
          IconButton(
            onPressed: () => context.read<JobAlertsCubit>().delete(alert.id),
            icon: const Icon(
              Icons.delete_outline_rounded,
              color: AppColors.errorFg,
            ),
          ),
        ],
      ),
    );
  }
}

class _Chip extends StatelessWidget {
  const _Chip(this.label);

  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 6),
      decoration: BoxDecoration(
        color: const Color(0xFFF4F6F3),
        borderRadius: BorderRadius.circular(9),
      ),
      child: Text(
        label,
        style: AppTextStyles.caption.copyWith(
          fontSize: 12.5,
          fontWeight: FontWeight.w800,
          color: AppColors.textStrong,
        ),
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header();

  @override
  Widget build(BuildContext context) {
    final topInset = MediaQuery.paddingOf(context).top;

    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(18, topInset + 12, 18, 16),
      decoration: const BoxDecoration(
        color: AppColors.charcoalSoft,
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(24)),
      ),
      child: Row(
        children: [
          Pressable(
            onTap: () => Navigator.of(context).maybePop(),
            child: Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: .1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(
                Icons.chevron_left_rounded,
                size: 23,
                color: Colors.white,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Text(
            'تنبيهات الوظائف',
            style: AppTextStyles.titleMd.copyWith(color: Colors.white),
          ),
        ],
      ),
    );
  }
}

class _Empty extends StatelessWidget {
  const _Empty();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(
            Icons.notifications_off_rounded,
            size: 52,
            color: Color(0xFFC9C4B9),
          ),
          const SizedBox(height: 14),
          Text('لا توجد تنبيهات بعد', style: AppTextStyles.titleSm),
          const SizedBox(height: 6),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 40),
            child: Text(
              'من فلاتر البحث اضغط «احفظ كتنبيه» لنُشعرك بالوظائف المطابقة.',
              textAlign: TextAlign.center,
              style: AppTextStyles.bodySm.copyWith(color: AppColors.textMuted),
            ),
          ),
        ],
      ),
    );
  }
}
