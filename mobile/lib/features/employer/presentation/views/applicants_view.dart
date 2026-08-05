import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/motion/motion.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../data/models/applicant_model.dart';
import '../manager/applicants_cubit/applicants_cubit.dart';

class ApplicantsView extends StatelessWidget {
  const ApplicantsView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: BlocBuilder<ApplicantsCubit, ApplicantsState>(
        builder: (context, state) {
          final applicants = state is ApplicantsLoaded
              ? state.applicants
              : <ApplicantModel>[];
          final pending = applicants
              .where((a) => a.status == 'submitted' || a.status == 'review')
              .length;

          return Column(
            children: [
              _Header(total: applicants.length, pending: pending),
              Expanded(
                child: switch (state) {
                  ApplicantsLoading() => const Center(
                    child: CircularProgressIndicator(color: AppColors.amber),
                  ),
                  ApplicantsEmpty() => const _EmptyState(),
                  ApplicantsFailed(:final failure) => _Message(
                    icon: Icons.wifi_off_rounded,
                    text: failure.message,
                  ),
                  ApplicantsLoaded(:final applicants) => RefreshIndicator(
                    color: AppColors.amber,
                    onRefresh: () => context.read<ApplicantsCubit>().load(),
                    child: ListView.separated(
                      padding: const EdgeInsets.fromLTRB(20, 18, 20, 104),
                      itemCount: applicants.length,
                      separatorBuilder: (_, _) => const SizedBox(height: 13),
                      itemBuilder: (_, index) => Entrance(
                        index: index,
                        child: _ApplicantCard(
                          applicant: applicants[index],
                          onOpen: () async {
                            await context.push(
                              RoutesKeys.employerApplicant(
                                applicants[index].id,
                              ),
                            );
                            if (context.mounted) {
                              context.read<ApplicantsCubit>().load();
                            }
                          },
                          onAccept: () => context
                              .read<ApplicantsCubit>()
                              .accept(applicants[index].id),
                          onReject: () => context
                              .read<ApplicantsCubit>()
                              .reject(applicants[index].id),
                        ),
                      ),
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
  const _Header({required this.total, required this.pending});

  final int total;
  final int pending;

  @override
  Widget build(BuildContext context) {
    final topInset = MediaQuery.paddingOf(context).top;

    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(22, topInset + 14, 22, 24),
      decoration: const BoxDecoration(
        color: AppColors.charcoalSoft,
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(28)),
      ),
      child: Row(
        children: [
          if (Navigator.of(context).canPop()) ...[
            const _DarkBackBox(),
            const SizedBox(width: 13),
          ],
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'المتقدمون',
                  style: AppTextStyles.titleLg.copyWith(color: Colors.white),
                ),
                const SizedBox(height: 4),
                Text(
                  '$total متقدمين · $pending بانتظار المراجعة',
                  style: AppTextStyles.bodySm.copyWith(
                    color: const Color(0xFFC7C2B8),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _DarkBackBox extends StatelessWidget {
  const _DarkBackBox();

  @override
  Widget build(BuildContext context) {
    return Pressable(
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
    );
  }
}

class _ApplicantCard extends StatelessWidget {
  const _ApplicantCard({
    required this.applicant,
    required this.onOpen,
    required this.onAccept,
    required this.onReject,
  });

  final ApplicantModel applicant;
  final VoidCallback onOpen;
  final VoidCallback onAccept;
  final VoidCallback onReject;

  bool get _pending =>
      applicant.status == 'submitted' || applicant.status == 'review';

  @override
  Widget build(BuildContext context) {
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
            onTap: onOpen,
            child: Row(
              children: [
                Container(
                  width: 50,
                  height: 50,
                  alignment: Alignment.center,
                  decoration: const BoxDecoration(
                    shape: BoxShape.circle,
                    gradient: RadialGradient(
                      center: Alignment(-.3, -.4),
                      colors: [Color(0xFF3E6B54), Color(0xFF284C3D)],
                    ),
                  ),
                  child: Text(
                    applicant.name?.characters.firstOrNull ?? 'م',
                    style: AppTextStyles.titleLg.copyWith(
                      color: const Color(0xFFFFFFFF),
                    ),
                  ),
                ),
                const SizedBox(width: 13),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Flexible(
                            child: Text(
                              applicant.name ?? 'مرشح',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: AppTextStyles.titleMd,
                            ),
                          ),
                          const SizedBox(width: 8),
                          _StatusBadge(
                            status: applicant.status,
                            label: applicant.statusLabel,
                          ),
                          if (applicant.shortlisted) ...[
                            const SizedBox(width: 6),
                            const Icon(
                              Icons.star_rounded,
                              size: 18,
                              color: AppColors.amber,
                            ),
                          ],
                        ],
                      ),
                      const SizedBox(height: 3),
                      Text(
                        'تقدّم على: ${applicant.jobTitle ?? '—'}',
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
                const Icon(
                  Icons.chevron_right_rounded,
                  size: 21,
                  color: Color(0xFFBAC4BD),
                ),
              ],
            ),
          ),
          if (applicant.yearsOfExperience != null) ...[
            const SizedBox(height: 13),
            _MetaChip(label: '${applicant.yearsOfExperience} سنوات خبرة'),
          ],
          const SizedBox(height: 15),
          const Divider(height: 1, color: Color(0xFFF2F0EA)),
          const SizedBox(height: 12),
          Row(
            children: [
              Text(
                'طلب ${applicant.reference}',
                textDirection: TextDirection.ltr,
                style: AppTextStyles.caption.copyWith(
                  fontWeight: FontWeight.w700,
                  color: AppColors.textMuted,
                ),
              ),
              const Spacer(),
              if (_pending) ...[
                _DecisionButton(
                  label: 'رفض',
                  icon: Icons.close_rounded,
                  onTap: onReject,
                ),
                const SizedBox(width: 8),
                _DecisionButton(
                  label: 'قبول',
                  icon: Icons.check_circle_rounded,
                  filled: true,
                  onTap: onAccept,
                ),
              ] else if (applicant.status == 'accepted')
                _DecisionButton(
                  label: 'تواصل',
                  icon: Icons.forum_rounded,
                  dark: true,
                  onTap: onOpen,
                ),
            ],
          ),
        ],
      ),
    );
  }
}

class _MetaChip extends StatelessWidget {
  const _MetaChip({required this.label});

  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: AppColors.paper,
        borderRadius: BorderRadius.circular(9),
      ),
      child: Text(
        label,
        style: AppTextStyles.caption.copyWith(
          fontSize: 12,
          fontWeight: FontWeight.w700,
          color: const Color(0xFF56524A),
        ),
      ),
    );
  }
}

class _DecisionButton extends StatelessWidget {
  const _DecisionButton({
    required this.label,
    required this.icon,
    required this.onTap,
    this.filled = false,
    this.dark = false,
  });

  final String label;
  final IconData icon;
  final VoidCallback onTap;
  final bool filled;
  final bool dark;

  @override
  Widget build(BuildContext context) {
    final (bg, fg, border) = dark
        ? (AppColors.charcoalSoft, Colors.white, null)
        : filled
        ? (AppColors.successFg, Colors.white, null)
        : (AppColors.surface, AppColors.errorFg, const Color(0xFFF6DADA));

    return Pressable(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 8),
        decoration: BoxDecoration(
          color: bg,
          border: border != null ? Border.all(color: border, width: 1.5) : null,
          borderRadius: BorderRadius.circular(11),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 16, color: fg),
            const SizedBox(width: 5),
            Text(
              label,
              style: AppTextStyles.caption.copyWith(
                fontWeight: FontWeight.w800,
                color: fg,
              ),
            ),
          ],
        ),
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
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Text(
        label,
        style: AppTextStyles.caption.copyWith(
          fontSize: 11,
          fontWeight: FontWeight.w800,
          color: fg,
        ),
      ),
    );
  }
}

class _EmptyState extends StatelessWidget {
  const _EmptyState();

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        const SizedBox(height: 46),
        Container(
          width: 84,
          height: 84,
          decoration: BoxDecoration(
            color: AppColors.surface,
            border: Border.all(color: AppColors.border),
            borderRadius: BorderRadius.circular(26),
          ),
          child: const Icon(
            Icons.person_search_rounded,
            size: 40,
            color: Color(0xFFBAC4BD),
          ),
        ),
        const SizedBox(height: 16),
        Text('لا يوجد متقدمون بعد', style: AppTextStyles.titleMd),
        const SizedBox(height: 6),
        Text(
          'سيظهر المتقدمون هنا بمجرد تقديمهم على إعلاناتك',
          style: AppTextStyles.bodySm.copyWith(color: AppColors.textMuted),
        ),
      ],
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
