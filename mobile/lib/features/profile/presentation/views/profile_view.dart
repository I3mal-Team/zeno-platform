import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/components/app_button.dart';
import '../../../../core/managers/user_cubit/user_cubit.dart';
import '../../../../core/motion/motion.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_dimensions.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../data/models/candidate_profile_model.dart';
import '../manager/profile_cubit/profile_cubit.dart';

class ProfileView extends StatelessWidget {
  const ProfileView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: BlocBuilder<ProfileCubit, ProfileState>(
        builder: (context, state) => switch (state) {
          ProfileLoading() => const Center(
            child: CircularProgressIndicator(color: AppColors.amber),
          ),
          ProfileEmpty() => const _EmptyProfile(),
          ProfileLoaded(:final profile) => _ProfileBody(profile: profile),
          ProfileSaved(:final profile) => _ProfileBody(profile: profile),
          ProfileFailed(:final failure) => _LoadFailed(
            message: failure.message,
          ),
          _ => const Center(
            child: CircularProgressIndicator(color: AppColors.amber),
          ),
        },
      ),
    );
  }
}

class _ProfileBody extends StatelessWidget {
  const _ProfileBody({required this.profile});

  final CandidateProfileModel profile;

  @override
  Widget build(BuildContext context) {
    final rows = <(String, String)>[
      if (profile.age != null) ('العمر', '${profile.age} سنة'),
      if (profile.nationalityCode != null)
        ('الجنسية', profile.nationalityCode!),
      if (profile.city != null) ('المدينة', profile.city!.name),
      if (profile.yearsOfExperience != null)
        ('سنوات الخبرة', '${profile.yearsOfExperience} سنوات'),
    ];

    return ListView(
      padding: const EdgeInsets.only(bottom: 104),
      children: [
        _Header(profile: profile),
        const SizedBox(height: 16),
        if (!profile.isComplete)
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 0, 20, 4),
            child: _CompletionBar(profile: profile),
          ),
        if (rows.isNotEmpty)
          Container(
            margin: const EdgeInsets.fromLTRB(20, 12, 20, 0),
            padding: const EdgeInsets.symmetric(horizontal: 18),
            decoration: BoxDecoration(
              color: AppColors.surface,
              border: Border.all(color: AppColors.border),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Column(
              children: [
                for (final (index, (label, value)) in rows.indexed)
                  _InfoRow(
                    label: label,
                    value: value,
                    divider: index != rows.length - 1,
                  ),
              ],
            ),
          ),
        if (profile.bio != null && profile.bio!.isNotEmpty) ...[
          const _SectionTitle('نبذة مختصرة'),
          Container(
            margin: const EdgeInsets.symmetric(horizontal: 20),
            padding: const EdgeInsets.all(15),
            decoration: BoxDecoration(
              color: AppColors.surface,
              border: Border.all(color: AppColors.border),
              borderRadius: BorderRadius.circular(18),
            ),
            child: Text(
              profile.bio!,
              style: AppTextStyles.bodyMd.copyWith(height: 1.8),
            ),
          ),
        ],
        if (profile.skills.isNotEmpty) ...[
          const _SectionTitle('المهارات'),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [for (final s in profile.skills) _SkillChip(s)],
            ),
          ),
        ],
        const SizedBox(height: 18),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20),
          child: _LogoutButton(
            onTap: () => context.read<UserCubit>().signOut(),
          ),
        ),
      ],
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({required this.profile});

  final CandidateProfileModel profile;

  @override
  Widget build(BuildContext context) {
    final topInset = MediaQuery.paddingOf(context).top;

    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(22, topInset + 14, 22, 22),
      decoration: const BoxDecoration(color: AppColors.charcoalSoft),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'حسابي',
                style: AppTextStyles.bodySm.copyWith(
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFFC7C2B8),
                ),
              ),
              _EditButton(
                onTap: () => context.push(RoutesKeys.registerCandidate),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(22),
                child: profile.avatarUrl != null
                    ? Image.network(
                        profile.avatarUrl!,
                        width: 72,
                        height: 56,
                        fit: BoxFit.cover,
                        errorBuilder: (_, _, _) =>
                            _AvatarLetter(profile: profile),
                      )
                    : _AvatarLetter(profile: profile),
              ),
              const SizedBox(width: 15),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      profile.fullName,
                      style: AppTextStyles.titleLg.copyWith(
                        color: Colors.white,
                      ),
                    ),
                    if (profile.jobTitle != null) ...[
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(
                            Icons.work_rounded,
                            size: 17,
                            color: AppColors.amber,
                          ),
                          const SizedBox(width: 5),
                          Flexible(
                            child: Text(
                              profile.jobTitle!,
                              style: AppTextStyles.bodySm.copyWith(
                                fontWeight: FontWeight.w800,
                                color: AppColors.amber,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _EditButton extends StatelessWidget {
  const _EditButton({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 8),
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: .08),
          border: Border.all(color: Colors.white.withValues(alpha: .18)),
          borderRadius: BorderRadius.circular(11),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.edit_rounded, size: 16, color: Colors.white),
            const SizedBox(width: 6),
            Text(
              'تعديل',
              style: AppTextStyles.caption.copyWith(
                fontWeight: FontWeight.w700,
                color: Colors.white,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({
    required this.label,
    required this.value,
    required this.divider,
  });

  final String label;
  final String value;
  final bool divider;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 13),
      decoration: BoxDecoration(
        border: divider
            ? const Border(bottom: BorderSide(color: Color(0xFFF2F0EA)))
            : null,
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(
            label,
            style: AppTextStyles.bodySm.copyWith(
              fontWeight: FontWeight.w700,
              color: AppColors.textMuted,
            ),
          ),
          Text(
            value,
            style: AppTextStyles.titleSm.copyWith(fontWeight: FontWeight.w800),
          ),
        ],
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle(this.title);

  final String title;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(22, 18, 22, 8),
      child: Text(
        title,
        style: AppTextStyles.titleSm.copyWith(fontWeight: FontWeight.w800),
      ),
    );
  }
}

class _CompletionBar extends StatelessWidget {
  const _CompletionBar({required this.profile});

  final CandidateProfileModel profile;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(AppDimensions.space16),
      decoration: BoxDecoration(
        color: AppColors.warningBg,
        borderRadius: BorderRadius.circular(AppDimensions.radiusCard),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'اكتمال الملف ${profile.completionPercentage}%',
            style: AppTextStyles.titleSm,
          ),
          const SizedBox(height: AppDimensions.space8),
          ClipRRect(
            borderRadius: BorderRadius.circular(AppDimensions.radiusPill),
            child: LinearProgressIndicator(
              value: profile.completionPercentage / 100,
              minHeight: 6,
              backgroundColor: AppColors.surface,
              color: AppColors.amber,
            ),
          ),
          const SizedBox(height: AppDimensions.space8),
          Text(
            'الملف المكتمل يصل لأصحاب العمل أسرع.',
            style: AppTextStyles.caption,
          ),
        ],
      ),
    );
  }
}

class _SkillChip extends StatelessWidget {
  const _SkillChip(this.label);

  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 8),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.border),
        borderRadius: BorderRadius.circular(11),
      ),
      child: Text(
        label,
        style: AppTextStyles.badge.copyWith(fontWeight: FontWeight.w700),
      ),
    );
  }
}

class _LogoutButton extends StatelessWidget {
  const _LogoutButton({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(15),
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: AppColors.surface,
          border: Border.all(color: const Color(0xFFF6DADA), width: 1.5),
          borderRadius: BorderRadius.circular(16),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.logout_rounded,
              size: 21,
              color: AppColors.errorFg,
            ),
            const SizedBox(width: 8),
            Text(
              'تسجيل الخروج',
              style: AppTextStyles.button.copyWith(
                fontSize: 15,
                color: AppColors.errorFg,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _EmptyProfile extends StatelessWidget {
  const _EmptyProfile();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppDimensions.screenPadding),
        child: Text(
          'لم تكمل بياناتك بعد.',
          style: AppTextStyles.bodyLg,
          textAlign: TextAlign.center,
        ),
      ),
    );
  }
}

class _LoadFailed extends StatelessWidget {
  const _LoadFailed({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppDimensions.screenPadding),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              message,
              style: AppTextStyles.bodyLg,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppDimensions.space20),
            AppButton(
              label: 'إعادة المحاولة',
              icon: null,
              onPressed: () => context.read<ProfileCubit>().load(),
            ),
          ],
        ),
      ),
    );
  }
}

class _AvatarLetter extends StatelessWidget {
  const _AvatarLetter({required this.profile});

  final CandidateProfileModel profile;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 72,
      height: 56,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        color: AppColors.amber,
        borderRadius: BorderRadius.circular(22),
      ),
      child: Text(
        profile.fullName.characters.firstOrNull ?? '؟',
        style: AppTextStyles.displayLg.copyWith(
          fontSize: 24,
          color: AppColors.textStrong,
        ),
      ),
    );
  }
}
