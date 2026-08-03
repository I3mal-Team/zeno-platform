import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/motion/motion.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../../../core/styles/category_visuals.dart';
import '../../../jobs/data/models/job_model.dart';
import '../manager/employer_jobs_cubit/employer_jobs_cubit.dart';

class EmployerJobsView extends StatelessWidget {
  const EmployerJobsView({super.key});

  Future<void> _post(BuildContext context) async {
    await context.push(RoutesKeys.employerPostJob);
    if (context.mounted) context.read<EmployerJobsCubit>().load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: BlocBuilder<EmployerJobsCubit, EmployerJobsState>(
        builder: (context, state) {
          final jobs = state is EmployerJobsLoaded ? state.jobs : <JobModel>[];
          final activeCount = jobs.where((j) => j.status == 'active').length;
          final totalApps = jobs.fold<int>(
            0,
            (sum, j) => sum + (j.applicationsCount ?? 0),
          );

          return Column(
            children: [
              _Header(
                activeCount: activeCount,
                jobsCount: jobs.length,
                totalApps: totalApps,
                onBell: () => context.push(RoutesKeys.notifications),
              ),
              Expanded(
                child: switch (state) {
                  EmployerJobsLoading() => const Center(
                    child: CircularProgressIndicator(color: AppColors.amber),
                  ),
                  EmployerJobsEmpty() => _EmptyState(
                    onPost: () => _post(context),
                  ),
                  EmployerJobsFailed(:final failure) => Center(
                    child: Text(failure.message, style: AppTextStyles.bodyMd),
                  ),
                  EmployerJobsLoaded(:final jobs) => RefreshIndicator(
                    color: AppColors.amber,
                    onRefresh: () => context.read<EmployerJobsCubit>().load(),
                    child: ListView(
                      padding: const EdgeInsets.fromLTRB(20, 0, 20, 104),
                      children: [
                        _SectionRow(onPost: () => _post(context)),
                        for (final (index, job) in jobs.indexed)
                          Padding(
                            padding: const EdgeInsets.only(bottom: 12),
                            child: Entrance(
                              index: index,
                              child: _EmployerJobTile(
                                job: job,
                                onApplicants: () => context.push(
                                  RoutesKeys.employerJobApplicants(job.id),
                                ),
                              ),
                            ),
                          ),
                      ],
                    ),
                  ),
                },
              ),
            ],
          );
        },
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({
    required this.activeCount,
    required this.jobsCount,
    required this.totalApps,
    required this.onBell,
  });

  final int activeCount;
  final int jobsCount;
  final int totalApps;
  final VoidCallback onBell;

  @override
  Widget build(BuildContext context) {
    final topInset = MediaQuery.paddingOf(context).top;

    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(22, topInset + 14, 22, 24),
      decoration: const BoxDecoration(color: AppColors.charcoalSoft),
      child: Column(
        children: [
          Row(
            children: [
              Container(
                width: 52,
                height: 48,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: AppColors.amber,
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Icon(
                  Icons.work_rounded,
                  color: AppColors.textStrong,
                ),
              ),
              const SizedBox(width: 13),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'لوحة صاحب العمل',
                      style: AppTextStyles.bodySm.copyWith(
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFFC7C2B8),
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'إدارة إعلاناتك',
                      style: AppTextStyles.titleLg.copyWith(
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),
              ),
              Pressable(
                onTap: onBell,
                child: Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: .08),
                    borderRadius: BorderRadius.circular(13),
                  ),
                  child: const Icon(
                    Icons.notifications_rounded,
                    size: 22,
                    color: Colors.white,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          Row(
            children: [
              _StatTile(value: activeCount, label: 'إعلان نشط'),
              const SizedBox(width: 10),
              _StatTile(value: jobsCount, label: 'إجمالي الإعلانات'),
              const SizedBox(width: 10),
              _StatTile(value: totalApps, label: 'إجمالي الطلبات'),
            ],
          ),
        ],
      ),
    );
  }
}

class _StatTile extends StatelessWidget {
  const _StatTile({required this.value, required this.label});

  final int value;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(13),
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: .07),
          borderRadius: BorderRadius.circular(15),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              '$value',
              style: AppTextStyles.titleLg.copyWith(color: AppColors.amber),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: AppTextStyles.caption.copyWith(
                fontSize: 11.5,
                fontWeight: FontWeight.w700,
                color: const Color(0xFFC7C2B8),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SectionRow extends StatelessWidget {
  const _SectionRow({required this.onPost});

  final VoidCallback onPost;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(2, 20, 2, 10),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text('إعلاناتي', style: AppTextStyles.titleLg),
          Pressable(
            onTap: onPost,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: AppColors.warningBg,
                borderRadius: BorderRadius.circular(11),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(
                    Icons.add_rounded,
                    size: 17,
                    color: AppColors.warningFg,
                  ),
                  const SizedBox(width: 5),
                  Text(
                    'إعلان جديد',
                    style: AppTextStyles.caption.copyWith(
                      fontWeight: FontWeight.w800,
                      color: AppColors.warningFg,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _EmployerJobTile extends StatelessWidget {
  const _EmployerJobTile({required this.job, required this.onApplicants});

  final JobModel job;
  final VoidCallback onApplicants;

  @override
  Widget build(BuildContext context) {
    final (tint, _) = AppColors.categoryTint(job.categoryCode);
    final meta = [
      job.district,
      job.workType,
    ].where((e) => e != null && e.isNotEmpty).join(' · ');

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.border),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: tint,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(
                  CategoryVisuals.icon(job.categoryCode),
                  color: Colors.white,
                  size: 24,
                ),
              ),
              const SizedBox(width: 13),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      job.title,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: AppTextStyles.titleMd,
                    ),
                    if (meta.isNotEmpty) ...[
                      const SizedBox(height: 2),
                      Text(
                        meta,
                        style: AppTextStyles.caption.copyWith(
                          fontWeight: FontWeight.w600,
                          color: AppColors.textMuted,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              _StatusBadge(status: job.status),
            ],
          ),
          const SizedBox(height: 15),
          const Divider(height: 1, color: Color(0xFFF2F0EA)),
          const SizedBox(height: 12),
          Row(
            children: [
              Text(
                '${job.applicationsCount ?? 0} متقدمين',
                style: AppTextStyles.titleSm.copyWith(
                  fontWeight: FontWeight.w800,
                ),
              ),
              const Spacer(),
              _ApplicantsButton(onTap: onApplicants),
            ],
          ),
        ],
      ),
    );
  }
}

class _ApplicantsButton extends StatelessWidget {
  const _ApplicantsButton({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
        decoration: BoxDecoration(
          color: AppColors.charcoalSoft,
          borderRadius: BorderRadius.circular(11),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              'المتقدمون',
              style: AppTextStyles.caption.copyWith(
                fontWeight: FontWeight.w800,
                color: Colors.white,
              ),
            ),
            const SizedBox(width: 5),
            const Icon(
              Icons.chevron_left_rounded,
              size: 17,
              color: Colors.white,
            ),
          ],
        ),
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
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            width: 7,
            height: 7,
            decoration: BoxDecoration(color: fg, shape: BoxShape.circle),
          ),
          const SizedBox(width: 5),
          Text(
            label,
            style: AppTextStyles.caption.copyWith(
              fontSize: 11.5,
              fontWeight: FontWeight.w800,
              color: fg,
            ),
          ),
        ],
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
          Container(
            width: 84,
            height: 84,
            decoration: BoxDecoration(
              color: AppColors.surface,
              border: Border.all(color: AppColors.border),
              borderRadius: BorderRadius.circular(26),
            ),
            child: const Icon(
              Icons.work_outline_rounded,
              size: 40,
              color: Color(0xFFC9C4B9),
            ),
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
          Pressable(
            onTap: onPost,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 14),
              decoration: BoxDecoration(
                color: AppColors.amber,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Text('نشر وظيفة', style: AppTextStyles.button),
            ),
          ),
        ],
      ),
    );
  }
}
