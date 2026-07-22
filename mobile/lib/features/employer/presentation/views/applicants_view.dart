import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

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
      appBar: AppBar(
        backgroundColor: AppColors.paper,
        title: Text('المتقدّمون', style: AppTextStyles.titleLg),
      ),
      body: BlocBuilder<ApplicantsCubit, ApplicantsState>(
        builder: (context, state) => switch (state) {
          ApplicantsLoading() => const Center(
            child: CircularProgressIndicator(color: AppColors.amber),
          ),
          ApplicantsEmpty() => const _Message(
            icon: Icons.people_outline_rounded,
            text: 'لا يوجد متقدمون على هذه الوظيفة بعد.',
          ),
          ApplicantsFailed(:final failure) => _Message(
            icon: Icons.wifi_off_rounded,
            text: failure.message,
          ),
          ApplicantsLoaded(:final applicants) => RefreshIndicator(
            color: AppColors.amber,
            onRefresh: () => context.read<ApplicantsCubit>().load(),
            child: ListView.separated(
              padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
              itemCount: applicants.length,
              separatorBuilder: (_, _) => const SizedBox(height: 12),
              itemBuilder: (_, index) => _ApplicantTile(
                applicant: applicants[index],
                onTap: () async {
                  await context.push(
                    RoutesKeys.employerApplicant(applicants[index].id),
                  );
                  if (context.mounted) context.read<ApplicantsCubit>().load();
                },
              ),
            ),
          ),
        },
      ),
    );
  }
}

class _ApplicantTile extends StatelessWidget {
  const _ApplicantTile({required this.applicant, required this.onTap});

  final ApplicantModel applicant;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: AppColors.surface,
      borderRadius: BorderRadius.circular(18),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(18),
        child: Container(
          padding: const EdgeInsets.all(15),
          decoration: BoxDecoration(
            border: Border.all(color: AppColors.border),
            borderRadius: BorderRadius.circular(18),
          ),
          child: Row(
            children: [
              Container(
                width: 46,
                height: 46,
                alignment: Alignment.center,
                decoration: const BoxDecoration(
                  shape: BoxShape.circle,
                  gradient: RadialGradient(
                    center: Alignment(-.3, -.4),
                    colors: [Color(0xFFFDEFC2), Color(0xFFF6D783)],
                  ),
                ),
                child: Text(
                  applicant.name?.characters.first ?? 'م',
                  style: AppTextStyles.titleMd.copyWith(
                    color: const Color(0xFF7A5E0E),
                  ),
                ),
              ),
              const SizedBox(width: 12),
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
                        if (applicant.isNew) ...[
                          const SizedBox(width: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 7,
                              vertical: 2,
                            ),
                            decoration: BoxDecoration(
                              color: AppColors.amber,
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              'جديد',
                              style: AppTextStyles.caption.copyWith(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w800,
                                color: AppColors.charcoalSoft,
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                    const SizedBox(height: 2),
                    Text(
                      applicant.jobTitle ?? '—',
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
                status: applicant.status,
                label: applicant.statusLabel,
              ),
              const SizedBox(width: 6),
              const Icon(
                Icons.chevron_left_rounded,
                size: 22,
                color: Color(0xFFC9C4B9),
              ),
            ],
          ),
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
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(9),
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
