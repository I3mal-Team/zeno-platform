import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/motion/motion.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../manager/nearby_cubit/nearby_cubit.dart';
import 'widgets/job_card.dart';

class NearbyView extends StatelessWidget {
  const NearbyView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: Column(
        children: [
          const _Header(),
          Expanded(
            child: BlocBuilder<NearbyCubit, NearbyState>(
              builder: (context, state) => switch (state) {
                NearbyLoading() => const Center(
                  child: CircularProgressIndicator(color: AppColors.amber),
                ),
                NearbyFailed(:final failure) => Center(
                  child: Text(failure.message, style: AppTextStyles.bodyMd),
                ),
                NearbyLoaded(:final jobs, :final byGps) =>
                  jobs.isEmpty
                      ? _Empty(byGps: byGps)
                      : Column(
                          children: [
                            if (!byGps) const _CityBanner(),
                            Expanded(
                              child: RefreshIndicator(
                                color: AppColors.amber,
                                onRefresh: () =>
                                    context.read<NearbyCubit>().load(),
                                child: ListView.separated(
                                  padding: const EdgeInsets.fromLTRB(
                                    16,
                                    16,
                                    16,
                                    24,
                                  ),
                                  itemCount: jobs.length,
                                  separatorBuilder: (_, _) =>
                                      const SizedBox(height: 12),
                                  itemBuilder: (_, i) => JobCard(
                                    job: jobs[i],
                                    onTap: () => context.push(
                                      RoutesKeys.job(jobs[i].slug),
                                    ),
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
              },
            ),
          ),
        ],
      ),
    );
  }
}

/// Shown when the results come from the saved city rather than a live fix.
class _CityBanner extends StatelessWidget {
  const _CityBanner();

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.fromLTRB(16, 14, 16, 0),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
      decoration: BoxDecoration(
        color: AppColors.warningBg,
        borderRadius: BorderRadius.circular(13),
      ),
      child: Row(
        children: [
          const Icon(
            Icons.info_outline_rounded,
            size: 19,
            color: AppColors.warningFg,
          ),
          const SizedBox(width: 9),
          Expanded(
            child: Text(
              'النتائج مبنية على مدينتك. فعّل الموقع لنتائج أدق.',
              style: AppTextStyles.caption.copyWith(
                fontWeight: FontWeight.w700,
                color: AppColors.warningFg,
              ),
            ),
          ),
          Pressable(
            onTap: () => context.read<NearbyCubit>().load(),
            child: Text(
              'تفعيل',
              style: AppTextStyles.caption.copyWith(
                fontWeight: FontWeight.w900,
                color: AppColors.warningFg,
              ),
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
            'الوظائف القريبة مني',
            style: AppTextStyles.titleMd.copyWith(color: Colors.white),
          ),
        ],
      ),
    );
  }
}

class _Empty extends StatelessWidget {
  const _Empty({required this.byGps});

  final bool byGps;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(
            Icons.location_off_rounded,
            size: 52,
            color: Color(0xFFC9C4B9),
          ),
          const SizedBox(height: 14),
          Text('لا توجد وظائف قريبة', style: AppTextStyles.titleSm),
          const SizedBox(height: 6),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 40),
            child: Text(
              byGps
                  ? 'وسّع نطاق بحثك أو جرّب لاحقًا.'
                  : 'فعّل الموقع أو حدّث مدينتك في ملفك الشخصي.',
              textAlign: TextAlign.center,
              style: AppTextStyles.bodySm.copyWith(color: AppColors.textMuted),
            ),
          ),
          const SizedBox(height: 16),
          Pressable(
            onTap: () => context.read<NearbyCubit>().load(),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 11),
              decoration: BoxDecoration(
                color: AppColors.amber,
                borderRadius: BorderRadius.circular(13),
              ),
              child: Text(
                'إعادة المحاولة',
                style: AppTextStyles.bodySm.copyWith(
                  fontWeight: FontWeight.w800,
                  color: AppColors.textStrong,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
