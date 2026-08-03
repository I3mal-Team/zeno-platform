import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/managers/user_cubit/user_cubit.dart';
import '../../../../core/motion/motion.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../data/models/organization_model.dart';
import '../manager/employer_profile_cubit/employer_profile_cubit.dart';

class EmployerAccountView extends StatelessWidget {
  const EmployerAccountView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: BlocBuilder<EmployerProfileCubit, EmployerProfileState>(
        builder: (context, state) => switch (state) {
          EmployerProfileLoaded(:final organization) => _Body(
            org: organization,
          ),
          EmployerProfileSaved(:final organization) => _Body(org: organization),
          EmployerProfileFailed(:final failure) => Center(
            child: Text(failure.message, style: AppTextStyles.bodyMd),
          ),
          _ => const Center(
            child: CircularProgressIndicator(color: AppColors.amber),
          ),
        },
      ),
    );
  }
}

class _Body extends StatelessWidget {
  const _Body({required this.org});

  final OrganizationModel org;

  @override
  Widget build(BuildContext context) {
    final isCompany = org.type == 'company';
    final rows = <(String, String)>[
      ('نوع الحساب', isCompany ? 'منشأة تجارية' : 'فرد'),
      if (org.responsiblePersonName != null)
        ('المسؤول', org.responsiblePersonName!),
      if (org.cityName != null) ('المدينة', org.cityName!),
    ];

    return ListView(
      padding: const EdgeInsets.only(bottom: 104),
      children: [
        _Header(org: org, isCompany: isCompany),
        Container(
          margin: const EdgeInsets.fromLTRB(20, 16, 20, 0),
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
        if (org.about != null && org.about!.isNotEmpty) ...[
          const Padding(
            padding: EdgeInsets.fromLTRB(22, 18, 22, 8),
            child: _SectionTitle('نبذة عن المنشأة'),
          ),
          Container(
            margin: const EdgeInsets.symmetric(horizontal: 20),
            padding: const EdgeInsets.all(15),
            decoration: BoxDecoration(
              color: AppColors.surface,
              border: Border.all(color: AppColors.border),
              borderRadius: BorderRadius.circular(18),
            ),
            child: Text(
              org.about!,
              style: AppTextStyles.bodyMd.copyWith(height: 1.8),
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
  const _Header({required this.org, required this.isCompany});

  final OrganizationModel org;
  final bool isCompany;

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
                'حساب صاحب العمل',
                style: AppTextStyles.bodySm.copyWith(
                  fontWeight: FontWeight.w700,
                  color: const Color(0xFFC7C2B8),
                ),
              ),
              _EditButton(
                onTap: () =>
                    context.push(RoutesKeys.registerEmployer, extra: org),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Container(
                width: 72,
                height: 56,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: AppColors.amber,
                  borderRadius: BorderRadius.circular(22),
                ),
                child: Text(
                  org.name.characters.firstOrNull ?? 'ك',
                  style: AppTextStyles.displayLg.copyWith(
                    fontSize: 24,
                    color: AppColors.textStrong,
                  ),
                ),
              ),
              const SizedBox(width: 15),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      org.name,
                      style: AppTextStyles.titleLg.copyWith(
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        Icon(
                          isCompany
                              ? Icons.storefront_rounded
                              : Icons.person_rounded,
                          size: 16,
                          color: AppColors.amber,
                        ),
                        const SizedBox(width: 5),
                        Text(
                          isCompany ? 'منشأة تجارية' : 'فرد',
                          style: AppTextStyles.bodySm.copyWith(
                            fontWeight: FontWeight.w800,
                            color: AppColors.amber,
                          ),
                        ),
                      ],
                    ),
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
    return Text(
      title,
      style: AppTextStyles.titleSm.copyWith(fontWeight: FontWeight.w800),
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
