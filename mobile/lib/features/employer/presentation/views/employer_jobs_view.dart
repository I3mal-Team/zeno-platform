import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../../../core/styles/category_visuals.dart';
import '../../../jobs/data/models/job_model.dart';
import '../manager/employer_jobs_cubit/employer_jobs_cubit.dart';

class EmployerJobsView extends StatelessWidget {
  const EmployerJobsView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      appBar: AppBar(
        backgroundColor: AppColors.paper,
        title: Text('إعلاناتي', style: AppTextStyles.titleLg),
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: AppColors.amber,
        foregroundColor: AppColors.charcoalSoft,
        onPressed: () async {
          await context.push(RoutesKeys.employerPostJob);
          if (context.mounted) context.read<EmployerJobsCubit>().load();
        },
        icon: const Icon(Icons.add_rounded),
        label: Text('نشر وظيفة', style: AppTextStyles.button),
      ),
      body: BlocBuilder<EmployerJobsCubit, EmployerJobsState>(
        builder: (context, state) => switch (state) {
          EmployerJobsLoading() => const Center(
            child: CircularProgressIndicator(color: AppColors.amber),
          ),
          EmployerJobsEmpty() => _EmptyState(
            onPost: () async {
              await context.push(RoutesKeys.employerPostJob);
              if (context.mounted) context.read<EmployerJobsCubit>().load();
            },
          ),
          EmployerJobsFailed(:final failure) => Center(
            child: Text(failure.message, style: AppTextStyles.bodyMd),
          ),
          EmployerJobsLoaded(:final jobs) => RefreshIndicator(
            color: AppColors.amber,
            onRefresh: () => context.read<EmployerJobsCubit>().load(),
            child: ListView.separated(
              padding: const EdgeInsets.fromLTRB(20, 8, 20, 96),
              itemCount: jobs.length,
              separatorBuilder: (_, _) => const SizedBox(height: 12),
              itemBuilder: (_, index) => _EmployerJobTile(job: jobs[index]),
            ),
          ),
        },
      ),
    );
  }
}

class _EmployerJobTile extends StatelessWidget {
  const _EmployerJobTile({required this.job});

  final JobModel job;

  @override
  Widget build(BuildContext context) {
    final (tint, _) = AppColors.categoryTint(job.categoryCode);

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
                  CategoryVisuals.icon(job.categoryCode),
                  color: Colors.white,
                  size: 23,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  job.title,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: AppTextStyles.titleMd,
                ),
              ),
              _StatusBadge(status: job.status),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Text(
                '${job.salary.formatted} ريال',
                style: AppTextStyles.titleSm,
              ),
              const Spacer(),
              const Icon(
                Icons.people_alt_rounded,
                size: 17,
                color: AppColors.textMuted,
              ),
              const SizedBox(width: 5),
              Text(
                '${job.applicationsCount ?? 0} متقدم',
                style: AppTextStyles.caption.copyWith(
                  fontWeight: FontWeight.w700,
                  color: AppColors.textBody,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _StatusBadge extends StatelessWidget {
  const _StatusBadge({required this.status});

  final String status;

  @override
  Widget build(BuildContext context) {
    final (label, fg, bg) = switch (status) {
      'active' => ('نشط', AppColors.successFg, AppColors.successBg),
      'pending_review' => (
        'قيد المراجعة',
        AppColors.warningFg,
        AppColors.warningBg,
      ),
      'paused' => ('متوقف', AppColors.warningFg, AppColors.warningBg),
      'filled' => ('اكتمل', AppColors.neutralFg, AppColors.neutralBg),
      'rejected' => ('مرفوض', AppColors.errorFg, AppColors.errorBg),
      'expired' => ('منتهي', AppColors.neutralFg, AppColors.neutralBg),
      'closed' => ('مغلق', AppColors.neutralFg, AppColors.neutralBg),
      _ => (status, AppColors.neutralFg, AppColors.neutralBg),
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

class _EmptyState extends StatelessWidget {
  const _EmptyState({required this.onPost});

  final VoidCallback onPost;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(
            Icons.work_outline_rounded,
            size: 54,
            color: AppColors.textMuted,
          ),
          const SizedBox(height: 16),
          Text('لم تنشر أي وظيفة بعد', style: AppTextStyles.titleMd),
          const SizedBox(height: 8),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 44),
            child: Text(
              'انشر أول وظيفة لتصل إلى المرشحين القريبين منك.',
              textAlign: TextAlign.center,
              style: AppTextStyles.bodyMd.copyWith(color: AppColors.textBody),
            ),
          ),
          const SizedBox(height: 18),
          FilledButton(
            onPressed: onPost,
            style: FilledButton.styleFrom(
              backgroundColor: AppColors.amber,
              foregroundColor: AppColors.charcoalSoft,
            ),
            child: Text('نشر وظيفة', style: AppTextStyles.button),
          ),
        ],
      ),
    );
  }
}
