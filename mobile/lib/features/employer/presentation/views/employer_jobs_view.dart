import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/components/app_toast.dart';
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

  Future<void> _view(BuildContext context, JobModel job) async {
    await context.push(RoutesKeys.employerJobDetail(job.id));
    if (context.mounted) context.read<EmployerJobsCubit>().load();
  }

  Future<void> _edit(BuildContext context, JobModel job) async {
    await context.push(RoutesKeys.employerEditJob(job.id));
    if (context.mounted) context.read<EmployerJobsCubit>().load();
  }

  /// Bottom sheet with the status actions valid for this listing.
  void _manage(BuildContext context, JobModel job) {
    final cubit = context.read<EmployerJobsCubit>();

    showModalBottomSheet<void>(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (_) => _ManageSheet(
        job: job,
        onEdit: () {
          Navigator.of(context).pop();
          _edit(context, job);
        },
        onPause: () => _run(context, cubit.pause(job.id)),
        onResume: () => _run(context, cubit.resume(job.id)),
        onClose: () async {
          final confirmed = await _confirmClose(context);
          if (confirmed && context.mounted) {
            await _run(context, cubit.closeListing(job.id));
          }
        },
      ),
    );
  }

  /// Runs a status mutation, closes the sheet, and toasts on failure.
  Future<void> _run(BuildContext context, Future<Object?> action) async {
    Navigator.of(context).pop();
    final failure = await action;
    if (failure != null && context.mounted) {
      AppToast.error(context, 'تعذّر تنفيذ العملية. حاول مجددًا.');
    }
  }

  Future<bool> _confirmClose(BuildContext context) async {
    final result = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        backgroundColor: AppColors.surface,
        title: Text('إغلاق الإعلان', style: AppTextStyles.titleMd),
        content: Text(
          'سيتوقف استقبال الطلبات ولا يمكن التراجع. متأكد؟',
          style: AppTextStyles.bodyMd,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(dialogContext).pop(false),
            child: Text('تراجع', style: AppTextStyles.bodyMd),
          ),
          TextButton(
            onPressed: () => Navigator.of(dialogContext).pop(true),
            child: Text(
              'إغلاق',
              style: AppTextStyles.bodyMd.copyWith(
                color: AppColors.errorFg,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
        ],
      ),
    );
    return result ?? false;
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
                                onView: () => _view(context, job),
                                onApplicants: () => context.push(
                                  RoutesKeys.employerJobApplicants(job.id),
                                ),
                                onManage: () => _manage(context, job),
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
  const _EmployerJobTile({
    required this.job,
    required this.onView,
    required this.onApplicants,
    required this.onManage,
  });

  final JobModel job;
  final VoidCallback onView;
  final VoidCallback onApplicants;
  final VoidCallback onManage;

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
          Pressable(
            onTap: onView,
            child: Row(
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
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              _MiniStat(
                icon: Icons.people_alt_rounded,
                value: '${job.applicationsCount ?? 0}',
                label: 'متقدم',
              ),
              const SizedBox(width: 10),
              _MiniStat(
                icon: Icons.visibility_rounded,
                value: '${job.viewsCount ?? 0}',
                label: 'مشاهدة',
              ),
            ],
          ),
          const SizedBox(height: 14),
          const Divider(height: 1, color: Color(0xFFF2F0EA)),
          const SizedBox(height: 12),
          Row(
            children: [
              _ManageButton(onTap: onManage),
              const Spacer(),
              _ApplicantsButton(onTap: onApplicants),
            ],
          ),
        ],
      ),
    );
  }
}

/// A compact metric pill (applicants, views) shown on a listing tile.
class _MiniStat extends StatelessWidget {
  const _MiniStat({
    required this.icon,
    required this.value,
    required this.label,
  });

  final IconData icon;
  final String value;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: AppColors.paper,
        borderRadius: BorderRadius.circular(11),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: AppColors.textMuted),
          const SizedBox(width: 6),
          Text(
            value,
            style: AppTextStyles.titleSm.copyWith(fontWeight: FontWeight.w800),
          ),
          const SizedBox(width: 4),
          Text(
            label,
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

class _ManageButton extends StatelessWidget {
  const _ManageButton({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
        decoration: BoxDecoration(
          color: AppColors.paper,
          border: Border.all(color: AppColors.border),
          borderRadius: BorderRadius.circular(11),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.tune_rounded, size: 17, color: AppColors.textBody),
            const SizedBox(width: 6),
            Text(
              'إدارة',
              style: AppTextStyles.caption.copyWith(
                fontWeight: FontWeight.w800,
                color: AppColors.textBody,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// The status-management actions for one listing, shown as a bottom sheet.
/// Only the transitions valid for the current status are offered; the backend
/// is the final authority and rejects anything stale.
class _ManageSheet extends StatelessWidget {
  const _ManageSheet({
    required this.job,
    required this.onEdit,
    required this.onPause,
    required this.onResume,
    required this.onClose,
  });

  final JobModel job;
  final VoidCallback onEdit;
  final VoidCallback onPause;
  final VoidCallback onResume;
  final VoidCallback onClose;

  @override
  Widget build(BuildContext context) {
    const editable = {'active', 'paused', 'pending_review'};
    const closable = {'active', 'paused', 'pending_review'};

    return SafeArea(
      top: false,
      child: Container(
        margin: const EdgeInsets.all(12),
        padding: const EdgeInsets.symmetric(vertical: 8),
        decoration: BoxDecoration(
          color: AppColors.surface,
          borderRadius: BorderRadius.circular(24),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const SizedBox(height: 8),
            Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: AppColors.border,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const SizedBox(height: 10),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 4, 20, 10),
              child: Text(
                job.title,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: AppTextStyles.titleSm,
              ),
            ),
            if (editable.contains(job.status))
              _ManageAction(
                icon: Icons.edit_rounded,
                label: 'تعديل الإعلان',
                onTap: onEdit,
              ),
            if (job.status == 'active')
              _ManageAction(
                icon: Icons.pause_circle_rounded,
                label: 'إيقاف مؤقت',
                onTap: onPause,
              ),
            if (job.status == 'paused')
              _ManageAction(
                icon: Icons.play_circle_rounded,
                label: 'استئناف',
                onTap: onResume,
              ),
            if (closable.contains(job.status))
              _ManageAction(
                icon: Icons.stop_circle_rounded,
                label: 'إغلاق الإعلان',
                destructive: true,
                onTap: onClose,
              ),
            const SizedBox(height: 6),
          ],
        ),
      ),
    );
  }
}

class _ManageAction extends StatelessWidget {
  const _ManageAction({
    required this.icon,
    required this.label,
    required this.onTap,
    this.destructive = false,
  });

  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final bool destructive;

  @override
  Widget build(BuildContext context) {
    final color = destructive ? AppColors.errorFg : AppColors.textStrong;

    return Pressable(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
        child: Row(
          children: [
            Icon(icon, size: 22, color: color),
            const SizedBox(width: 14),
            Text(
              label,
              style: AppTextStyles.bodyMd.copyWith(
                fontWeight: FontWeight.w700,
                color: color,
              ),
            ),
          ],
        ),
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
