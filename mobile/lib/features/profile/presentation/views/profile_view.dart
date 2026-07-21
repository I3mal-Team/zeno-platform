import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../core/components/app_button.dart';
import '../../../../core/managers/user_cubit/user_cubit.dart';
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
      appBar: AppBar(title: Text('حسابي', style: AppTextStyles.titleMd)),
      body: BlocBuilder<ProfileCubit, ProfileState>(
        builder: (context, state) => switch (state) {
          ProfileLoading() => const Center(child: CircularProgressIndicator()),
          ProfileEmpty() => const _EmptyProfile(),
          ProfileLoaded(:final profile) => _ProfileBody(profile: profile),
          ProfileSaved(:final profile) => _ProfileBody(profile: profile),
          ProfileFailed(:final failure) => _LoadFailed(
            message: failure.message,
          ),
          _ => const Center(child: CircularProgressIndicator()),
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
    return ListView(
      padding: const EdgeInsets.all(AppDimensions.screenPadding),
      children: [
        _Header(profile: profile),
        const SizedBox(height: AppDimensions.space20),
        if (!profile.isComplete) _CompletionBar(profile: profile),
        const SizedBox(height: AppDimensions.space20),
        if (profile.bio != null)
          _Section(
            title: 'نبذة مختصرة',
            child: Text(profile.bio!, style: AppTextStyles.bodyMd),
          ),
        if (profile.skills.isNotEmpty)
          _Section(
            title: 'المهارات',
            child: Wrap(
              spacing: AppDimensions.space8,
              runSpacing: AppDimensions.space8,
              children: [for (final skill in profile.skills) _SkillChip(skill)],
            ),
          ),
        const SizedBox(height: AppDimensions.space32),
        AppButton(
          label: 'تسجيل الخروج',
          onPressed: () => context.read<UserCubit>().signOut(),
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
    return Row(
      children: [
        CircleAvatar(
          radius: 34,
          backgroundColor: AppColors.warningBg,
          foregroundImage: profile.avatarUrl != null
              ? NetworkImage(profile.avatarUrl!)
              : null,
          child: Text(
            profile.fullName.characters.first,
            style: AppTextStyles.displayLg,
          ),
        ),
        const SizedBox(width: AppDimensions.space16),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(profile.fullName, style: AppTextStyles.titleLg),
              if (profile.jobTitle != null)
                Text(profile.jobTitle!, style: AppTextStyles.bodyMd),
              if (profile.city != null)
                Text(profile.city!.name, style: AppTextStyles.caption),
            ],
          ),
        ),
      ],
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

class _Section extends StatelessWidget {
  const _Section({required this.title, required this.child});

  final String title;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: AppDimensions.space20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(title, style: AppTextStyles.titleSm),
          const SizedBox(height: AppDimensions.space8),
          child,
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
      padding: const EdgeInsets.symmetric(
        horizontal: AppDimensions.space12,
        vertical: AppDimensions.space6,
      ),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.border),
        borderRadius: BorderRadius.circular(AppDimensions.radiusChip),
      ),
      child: Text(label, style: AppTextStyles.badge),
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
              onPressed: () => context.read<ProfileCubit>().load(),
            ),
          ],
        ),
      ),
    );
  }
}
