import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/components/app_toast.dart';
import '../../../../core/motion/motion.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../../../core/styles/category_visuals.dart';
import '../../../applications/presentation/manager/apply_cubit/apply_cubit.dart';
import '../../../applications/presentation/views/widgets/apply_form_sheet.dart';
import '../../data/models/job_detail_model.dart';
import '../manager/job_detail_cubit/job_detail_cubit.dart';

class JobDetailView extends StatelessWidget {
  const JobDetailView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: BlocBuilder<JobDetailCubit, JobDetailState>(
        builder: (context, state) => switch (state) {
          JobDetailLoading() => const Center(
            child: CircularProgressIndicator(color: AppColors.amber),
          ),
          JobDetailFailed(:final failure) => _DetailError(
            message: failure.message,
          ),
          JobDetailLoaded(:final job) => _JobDetailBody(job: job),
        },
      ),
    );
  }
}

class _JobDetailBody extends StatelessWidget {
  const _JobDetailBody({required this.job});

  final JobDetailModel job;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Expanded(
          child: ListView(
            padding: EdgeInsets.zero,
            children: [
              _DetailHeader(job: job),
              if (!job.isOpenForApplications) const _PausedBanner(),
              _InfoGrid(job: job),
              if (job.description != null && job.description!.isNotEmpty)
                _Section(
                  title: 'وصف الوظيفة',
                  child: Text(
                    job.description!,
                    style: AppTextStyles.bodyMd.copyWith(
                      height: 1.85,
                      color: AppColors.textBody,
                    ),
                  ),
                ),
              _ContactCard(channel: job.contactChannel),
              const SizedBox(height: 20),
            ],
          ),
        ),
        _ApplyBar(job: job),
      ],
    );
  }
}

class _DetailHeader extends StatelessWidget {
  const _DetailHeader({required this.job});

  final JobDetailModel job;

  @override
  Widget build(BuildContext context) {
    final (tint, _) = AppColors.categoryTint(job.categoryCode);
    final place = [
      job.organizationName,
      job.district,
      job.city,
    ].where((e) => e != null && e.isNotEmpty).join(' · ');

    return Container(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
      decoration: const BoxDecoration(
        color: AppColors.charcoalSoft,
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(28)),
      ),
      child: SafeArea(
        bottom: false,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Align(
              alignment: AlignmentDirectional.centerStart,
              child: _RoundIconButton(
                icon: Icons.chevron_right_rounded,
                onTap: () => Navigator.of(context).maybePop(),
              ),
            ),
            const SizedBox(height: 18),
            Row(
              children: [
                Container(
                  width: 64,
                  height: 64,
                  decoration: BoxDecoration(
                    color: tint,
                    borderRadius: BorderRadius.circular(19),
                  ),
                  child: Icon(
                    CategoryVisuals.icon(job.categoryCode),
                    color: Colors.white,
                    size: 32,
                  ),
                ),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Flexible(
                            child: Text(
                              job.title,
                              style: AppTextStyles.titleLg.copyWith(
                                color: Colors.white,
                              ),
                            ),
                          ),
                          if (job.organizationVerified) ...[
                            const SizedBox(width: 6),
                            const Icon(
                              Icons.verified_rounded,
                              size: 18,
                              color: AppColors.amber,
                            ),
                          ],
                        ],
                      ),
                      const SizedBox(height: 4),
                      Text(
                        place,
                        style: AppTextStyles.bodySm.copyWith(
                          fontWeight: FontWeight.w700,
                          color: AppColors.textOnDarkMuted,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            if (job.category != null) ...[
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 7,
                ),
                decoration: BoxDecoration(
                  color: AppColors.amber.withValues(alpha: .16),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      CategoryVisuals.icon(job.categoryCode),
                      size: 16,
                      color: AppColors.amber,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      job.category!.name,
                      style: AppTextStyles.caption.copyWith(
                        fontWeight: FontWeight.w800,
                        color: AppColors.amber,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _PausedBanner extends StatelessWidget {
  const _PausedBanner();

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.fromLTRB(20, 16, 20, 0),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: AppColors.warningBg,
        borderRadius: BorderRadius.circular(14),
      ),
      child: Row(
        children: [
          const Icon(Icons.pause_circle_rounded, color: AppColors.warningFg),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              'هذا الإعلان موقوف حالياً ولا يستقبل طلبات جديدة.',
              style: AppTextStyles.caption.copyWith(
                fontWeight: FontWeight.w700,
                color: AppColors.warningFg,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _InfoGrid extends StatelessWidget {
  const _InfoGrid({required this.job});

  final JobDetailModel job;

  @override
  Widget build(BuildContext context) {
    final unit = job.salary.unit != null ? ' ${job.salary.unit}' : '';
    final tiles = <(IconData, String, String)>[
      (
        Icons.payments_rounded,
        'الراتب / الأجر',
        'ريال$unit · ${job.salary.formatted}',
      ),
      if (job.workType != null)
        (Icons.schedule_rounded, 'نوع العمل', job.workType!),
      (Icons.groups_rounded, 'العدد المطلوب', '${job.vacanciesCount}'),
      if (job.genderRequirement != null)
        (Icons.person_rounded, 'الجنس المطلوب', job.genderRequirement!),
      if (job.nationalityRequirement != null)
        (Icons.public_rounded, 'الجنسية', job.nationalityRequirement!),
      if (job.hoursPerWeek != null)
        (Icons.timelapse_rounded, 'ساعات أسبوعياً', '${job.hoursPerWeek}'),
    ];

    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 18, 20, 4),
      child: GridView.count(
        crossAxisCount: 2,
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        crossAxisSpacing: 11,
        mainAxisSpacing: 11,
        childAspectRatio: 2.4,
        children: [
          for (final (index, (icon, label, value)) in tiles.indexed)
            Entrance(
              index: index,
              child: _InfoTile(icon: icon, label: label, value: value),
            ),
        ],
      ),
    );
  }
}

class _InfoTile extends StatelessWidget {
  const _InfoTile({
    required this.icon,
    required this.label,
    required this.value,
  });

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.border),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Row(
            children: [
              Icon(icon, size: 18, color: AppColors.textMuted),
              const SizedBox(width: 7),
              Flexible(
                child: Text(
                  label,
                  style: AppTextStyles.caption.copyWith(
                    fontWeight: FontWeight.w700,
                    color: AppColors.textMuted,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            value,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: AppTextStyles.titleSm,
          ),
        ],
      ),
    );
  }
}

class _Section extends StatelessWidget {
  const _Section({required this.title, required this.child});

  final String title;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(22, 18, 22, 6),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: AppTextStyles.titleMd),
          const SizedBox(height: 8),
          child,
        ],
      ),
    );
  }
}

class _ContactCard extends StatelessWidget {
  const _ContactCard({required this.channel});

  final String channel;

  @override
  Widget build(BuildContext context) {
    final label = switch (channel) {
      'whatsapp' => 'واتساب',
      'both' => 'داخل التطبيق أو واتساب',
      _ => 'داخل التطبيق',
    };

    return Container(
      margin: const EdgeInsets.fromLTRB(20, 14, 20, 0),
      padding: const EdgeInsets.all(15),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.border),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          Container(
            width: 42,
            height: 42,
            decoration: BoxDecoration(
              color: AppColors.successBg,
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(Icons.forum_rounded, color: AppColors.successFg),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'طريقة التواصل بعد القبول',
                  style: AppTextStyles.caption.copyWith(
                    fontWeight: FontWeight.w700,
                    color: AppColors.textMuted,
                  ),
                ),
                const SizedBox(height: 2),
                Text(label, style: AppTextStyles.titleSm),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _ApplyBar extends StatelessWidget {
  const _ApplyBar({required this.job});

  final JobDetailModel job;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 12, 20, 12),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(top: BorderSide(color: AppColors.border)),
      ),
      child: SafeArea(
        top: false,
        child: Row(
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'الراتب',
                  style: AppTextStyles.caption.copyWith(
                    fontWeight: FontWeight.w700,
                    color: AppColors.textMuted,
                  ),
                ),
                Text(job.salary.formatted, style: AppTextStyles.titleMd),
              ],
            ),
            const SizedBox(width: 16),
            Expanded(
              child: SizedBox(
                height: 50,
                child: BlocConsumer<ApplyCubit, ApplyState>(
                  listener: (context, state) {
                    if (state case ApplyDone(:final application)) {
                      context.push(
                        '${RoutesKeys.applicationSubmitted}?ref=${application.reference}',
                      );
                    }
                    if (state case ApplyFailed(:final failure)) {
                      AppToast.error(context, failure.message);
                    }
                  },
                  builder: (context, state) {
                    final applied = state is ApplyDone;
                    final canApply = job.isOpenForApplications && !applied;

                    return FilledButton(
                      onPressed: canApply ? () => _onApply(context) : null,
                      style: FilledButton.styleFrom(
                        backgroundColor: AppColors.amber,
                        foregroundColor: AppColors.charcoalSoft,
                        disabledBackgroundColor: applied
                            ? AppColors.successBg
                            : AppColors.border,
                        disabledForegroundColor: applied
                            ? AppColors.successFg
                            : AppColors.textMuted,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(17),
                        ),
                      ),
                      child: state is ApplySubmitting
                          ? const SizedBox(
                              width: 22,
                              height: 22,
                              child: CircularProgressIndicator(
                                strokeWidth: 2.4,
                                color: AppColors.charcoalSoft,
                              ),
                            )
                          : Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Text(
                                  _label(job.isOpenForApplications, applied),
                                  style: AppTextStyles.button,
                                ),
                                if (canApply) ...[
                                  const SizedBox(width: 8),
                                  const Icon(Icons.send_rounded, size: 20),
                                ],
                              ],
                            ),
                    );
                  },
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  /// A plain job applies in one tap; one with a custom form opens the sheet
  /// first and only submits once the candidate fills it in.
  Future<void> _onApply(BuildContext context) async {
    final cubit = context.read<ApplyCubit>();

    if (job.applicationFields.isEmpty) {
      cubit.apply(job.slug);
      return;
    }

    final result = await ApplyFormSheet.show(
      context,
      fields: job.applicationFields,
    );

    if (result == null) return;

    cubit.apply(job.slug, answers: result.answers, files: result.files);
  }

  String _label(bool open, bool applied) {
    if (applied) return 'تم التقديم';

    return open ? 'تقديم على الوظيفة' : 'غير متاح للتقديم';
  }
}

class _RoundIconButton extends StatelessWidget {
  const _RoundIconButton({required this.icon, required this.onTap});

  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Container(
        width: 42,
        height: 42,
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: .08),
          borderRadius: BorderRadius.circular(13),
          border: Border.all(color: Colors.white.withValues(alpha: .12)),
        ),
        child: Icon(icon, color: Colors.white),
      ),
    );
  }
}

class _DetailError extends StatelessWidget {
  const _DetailError({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    return SafeArea(
      child: Column(
        children: [
          Align(
            alignment: AlignmentDirectional.centerStart,
            child: Padding(
              padding: const EdgeInsets.all(12),
              child: IconButton(
                onPressed: () => Navigator.of(context).maybePop(),
                icon: const Icon(Icons.chevron_right_rounded),
              ),
            ),
          ),
          Expanded(
            child: Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(
                    Icons.error_outline_rounded,
                    size: 46,
                    color: AppColors.textMuted,
                  ),
                  const SizedBox(height: 12),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 40),
                    child: Text(
                      message,
                      textAlign: TextAlign.center,
                      style: AppTextStyles.bodyMd.copyWith(
                        color: AppColors.textBody,
                      ),
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
