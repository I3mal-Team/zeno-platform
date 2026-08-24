import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/motion/motion.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../manager/saved_jobs_cubit/saved_jobs_cubit.dart';
import 'widgets/job_card.dart';

class SavedJobsView extends StatelessWidget {
  const SavedJobsView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: Column(
        children: [
          const _Header(),
          Expanded(
            child: BlocBuilder<SavedJobsCubit, SavedJobsState>(
              builder: (context, state) => switch (state) {
                SavedJobsLoading() => const Center(
                  child: CircularProgressIndicator(color: AppColors.amber),
                ),
                SavedJobsFailed(:final failure) => Center(
                  child: Text(failure.message, style: AppTextStyles.bodyMd),
                ),
                SavedJobsLoaded(:final jobs) =>
                  jobs.isEmpty
                      ? const _Empty()
                      : RefreshIndicator(
                          color: AppColors.amber,
                          onRefresh: () =>
                              context.read<SavedJobsCubit>().load(),
                          child: ListView.separated(
                            padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
                            itemCount: jobs.length,
                            separatorBuilder: (_, _) =>
                                const SizedBox(height: 12),
                            itemBuilder: (_, i) => JobCard(
                              job: jobs[i],
                              onTap: () =>
                                  context.push(RoutesKeys.job(jobs[i].slug)),
                            ),
                          ),
                        ),
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header();

  @override
  Widget build(BuildContext context) {
    final topInset = MediaQuery.paddingOf(context).top;

    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(18, topInset + 12, 18, 16),
      decoration: const BoxDecoration(
        color: AppColors.charcoalSoft,
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(24)),
      ),
      child: Row(
        children: [
          Pressable(
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
          ),
          const SizedBox(width: 12),
          Text(
            'الوظائف المحفوظة',
            style: AppTextStyles.titleMd.copyWith(color: Colors.white),
          ),
        ],
      ),
    );
  }
}

class _Empty extends StatelessWidget {
  const _Empty();

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(
            Icons.favorite_border_rounded,
            size: 52,
            color: Color(0xFFC9C4B9),
          ),
          const SizedBox(height: 14),
          Text('لا توجد وظائف محفوظة بعد', style: AppTextStyles.titleSm),
          const SizedBox(height: 6),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 40),
            child: Text(
              'احفظ أي وظيفة من التصفّح لتظهر هنا.',
              textAlign: TextAlign.center,
              style: AppTextStyles.bodySm.copyWith(color: AppColors.textMuted),
            ),
          ),
        ],
      ),
    );
  }
}
