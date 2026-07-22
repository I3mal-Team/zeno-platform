import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../core/components/app_toast.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../data/models/applicant_profile_model.dart';
import '../manager/applicant_profile_cubit/applicant_profile_cubit.dart';

class ApplicantProfileView extends StatelessWidget {
  const ApplicantProfileView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      appBar: AppBar(
        backgroundColor: AppColors.paper,
        title: Text('ملف المتقدم', style: AppTextStyles.titleLg),
      ),
      body: BlocConsumer<ApplicantProfileCubit, ApplicantProfileState>(
        listener: (context, state) {
          if (state case ApplicantProfileFailed(:final failure)) {
            AppToast.error(context, failure.message);
          }
        },
        builder: (context, state) => switch (state) {
          ApplicantProfileLoaded(:final profile) => _Body(profile: profile),
          ApplicantProfileFailed() => const SizedBox.shrink(),
          _ => const Center(
            child: CircularProgressIndicator(color: AppColors.amber),
          ),
        },
      ),
    );
  }
}

class _Body extends StatelessWidget {
  const _Body({required this.profile});

  final ApplicantProfileModel profile;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Expanded(
          child: ListView(
            padding: const EdgeInsets.fromLTRB(20, 8, 20, 24),
            children: [
              _Header(profile: profile),
              const SizedBox(height: 16),
              _Facts(profile: profile),
              if (profile.skills.isNotEmpty) ...[
                const SizedBox(height: 20),
                _Section(
                  title: 'المهارات',
                  child: Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      for (final skill in profile.skills)
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 13,
                            vertical: 7,
                          ),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF6F5F1),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Text(
                            skill,
                            style: AppTextStyles.caption.copyWith(
                              fontWeight: FontWeight.w700,
                              color: const Color(0xFF56524A),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ],
              if (profile.bio != null && profile.bio!.isNotEmpty) ...[
                const SizedBox(height: 20),
                _Section(
                  title: 'نبذة',
                  child: Text(
                    profile.bio!,
                    style: AppTextStyles.bodyMd.copyWith(
                      height: 1.85,
                      color: AppColors.textBody,
                    ),
                  ),
                ),
              ],
              if (profile.isAccepted) ...[
                const SizedBox(height: 20),
                _ContactCard(profile: profile),
              ],
            ],
          ),
        ),
        if (profile.isDecidable) const _DecisionBar(),
      ],
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({required this.profile});

  final ApplicantProfileModel profile;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.border),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        children: [
          Container(
            width: 60,
            height: 60,
            alignment: Alignment.center,
            decoration: const BoxDecoration(
              shape: BoxShape.circle,
              gradient: RadialGradient(
                center: Alignment(-.3, -.4),
                colors: [Color(0xFFFDEFC2), Color(0xFFF6D783)],
              ),
            ),
            child: Text(
              profile.name?.characters.first ?? 'م',
              style: AppTextStyles.titleLg.copyWith(
                color: const Color(0xFF7A5E0E),
              ),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(profile.name ?? 'مرشح', style: AppTextStyles.titleLg),
                const SizedBox(height: 3),
                Text(
                  profile.jobTitle ?? '—',
                  style: AppTextStyles.caption.copyWith(
                    fontWeight: FontWeight.w600,
                    color: AppColors.textMuted,
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

class _Facts extends StatelessWidget {
  const _Facts({required this.profile});

  final ApplicantProfileModel profile;

  @override
  Widget build(BuildContext context) {
    final facts = <(String, String)>[
      ('العمر', profile.age?.toString() ?? '—'),
      ('سنوات الخبرة', profile.yearsOfExperience?.toString() ?? '—'),
      ('المدينة', profile.city ?? '—'),
    ];

    return Row(
      children: [
        for (final (index, (label, value)) in facts.indexed) ...[
          if (index > 0) const SizedBox(width: 11),
          Expanded(
            child: Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: AppColors.surface,
                border: Border.all(color: AppColors.border),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    label,
                    style: AppTextStyles.caption.copyWith(
                      color: AppColors.textMuted,
                    ),
                  ),
                  const SizedBox(height: 5),
                  Text(value, style: AppTextStyles.titleSm),
                ],
              ),
            ),
          ),
        ],
      ],
    );
  }
}

class _Section extends StatelessWidget {
  const _Section({required this.title, required this.child});

  final String title;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: AppTextStyles.titleMd),
        const SizedBox(height: 10),
        child,
      ],
    );
  }
}

class _ContactCard extends StatelessWidget {
  const _ContactCard({required this.profile});

  final ApplicantProfileModel profile;

  @override
  Widget build(BuildContext context) {
    final channel = switch (profile.contactChannel) {
      'whatsapp' => 'واتساب',
      'both' => 'داخل التطبيق أو واتساب',
      _ => 'داخل التطبيق',
    };

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.successBg,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(
        children: [
          const Icon(Icons.check_circle_rounded, color: AppColors.successFg),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'تم القبول · التواصل عبر $channel',
                  style: AppTextStyles.caption.copyWith(
                    fontWeight: FontWeight.w800,
                    color: const Color(0xFF1F5C36),
                  ),
                ),
                if (profile.candidatePhone != null) ...[
                  const SizedBox(height: 3),
                  Text(
                    profile.candidatePhone!,
                    textDirection: TextDirection.ltr,
                    style: AppTextStyles.titleSm.copyWith(
                      color: const Color(0xFF1F5C36),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _DecisionBar extends StatelessWidget {
  const _DecisionBar();

  @override
  Widget build(BuildContext context) {
    final cubit = context.read<ApplicantProfileCubit>();

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
            Expanded(
              child: SizedBox(
                height: 50,
                child: FilledButton(
                  onPressed: cubit.accept,
                  style: FilledButton.styleFrom(
                    backgroundColor: AppColors.successFg,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(15),
                    ),
                  ),
                  child: Text('قبول', style: AppTextStyles.button),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: SizedBox(
                height: 50,
                child: OutlinedButton(
                  onPressed: cubit.reject,
                  style: OutlinedButton.styleFrom(
                    foregroundColor: AppColors.errorFg,
                    side: const BorderSide(color: Color(0xFFF0D5D5)),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(15),
                    ),
                  ),
                  child: Text('رفض', style: AppTextStyles.button),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
