import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/components/app_toast.dart';
import '../../../../core/motion/motion.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../../../core/services/service_locator.dart';
import '../../data/repos/jobs_repo.dart';
import '../manager/search_cubit/search_cubit.dart';
import 'widgets/filters_sheet.dart';
import 'widgets/job_card.dart';

class SearchView extends StatelessWidget {
  const SearchView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: Column(
        children: [
          const _SearchHeader(),
          Expanded(
            child: BlocBuilder<SearchCubit, SearchState>(
              builder: (context, state) => switch (state) {
                SearchIdle() => const _Placeholder(
                  icon: Icons.search_rounded,
                  title: 'ابحث عن وظيفة',
                  body: 'اكتب مسمّى الوظيفة، أو استخدم التصفية لتضييق النتائج.',
                ),
                SearchLoading() => const Center(
                  child: CircularProgressIndicator(color: AppColors.amber),
                ),
                SearchEmpty() => const _Placeholder(
                  icon: Icons.search_off_rounded,
                  title: 'لا نتائج',
                  body: 'جرّب كلمات أخرى أو وسّع التصفية.',
                ),
                SearchFailed(:final failure) => _Placeholder(
                  icon: Icons.wifi_off_rounded,
                  title: 'تعذّر البحث',
                  body: failure.message,
                  onRetry: () => context.read<SearchCubit>().retry(),
                ),
                SearchLoaded(:final jobs) => ListView.builder(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 104),
                  itemCount: jobs.length + 1,
                  itemBuilder: (_, index) {
                    if (index == 0) {
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 10, right: 4),
                        child: Text(
                          '${jobs.length} نتيجة',
                          style: AppTextStyles.caption,
                        ),
                      );
                    }

                    final job = jobs[index - 1];

                    return Entrance(
                      index: index,
                      child: JobCard(
                        job: job,
                        onTap: () => context.push(RoutesKeys.job(job.slug)),
                      ),
                    );
                  },
                ),
              },
            ),
          ),
        ],
      ),
    );
  }
}

/// Dark sticky header holding the query box and the filters button, as the
/// design draws it.
class _SearchHeader extends StatefulWidget {
  const _SearchHeader();

  @override
  State<_SearchHeader> createState() => _SearchHeaderState();
}

class _SearchHeaderState extends State<_SearchHeader> {
  bool _loadingOptions = false;

  @override
  Widget build(BuildContext context) {
    final topInset = MediaQuery.paddingOf(context).top;
    final activeCount = context.watch<SearchCubit>().filters.activeCount;

    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(16, topInset + 12, 16, 16),
      decoration: const BoxDecoration(
        color: AppColors.charcoalSoft,
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(24)),
      ),
      child: Row(
        children: [
          Expanded(
            child: Container(
              height: 48,
              padding: const EdgeInsets.symmetric(horizontal: 14),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: .1),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.search_rounded,
                    size: 20,
                    color: Color(0xFFA39D90),
                  ),
                  const SizedBox(width: 9),
                  Expanded(
                    child: TextField(
                      autofocus: true,
                      textInputAction: TextInputAction.search,
                      onChanged: (value) =>
                          context.read<SearchCubit>().query(value),
                      style: AppTextStyles.input.copyWith(color: Colors.white),
                      decoration: InputDecoration(
                        isDense: true,
                        border: InputBorder.none,
                        hintText: 'ابحث عن وظيفة…',
                        hintStyle: AppTextStyles.input.copyWith(
                          color: const Color(0xFFA39D90),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(width: 10),
          Pressable(
            onTap: _loadingOptions ? null : _openFilters,
            child: Badge(
              isLabelVisible: activeCount > 0,
              label: Text('$activeCount'),
              backgroundColor: AppColors.amber,
              textColor: AppColors.charcoalSoft,
              child: Container(
                width: 48,
                height: 48,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: .1),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: _loadingOptions
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: AppColors.amber,
                        ),
                      )
                    : const Icon(
                        Icons.tune_rounded,
                        size: 22,
                        color: Colors.white,
                      ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _openFilters() async {
    setState(() => _loadingOptions = true);

    final cubit = context.read<SearchCubit>();
    final result = await getIt<JobsRepo>().fetchFilterOptions();

    if (!mounted) return;
    setState(() => _loadingOptions = false);

    await result.fold(
      (failure) async => AppToast.error(context, failure.message),
      (options) async {
        final applied = await showFiltersSheet(
          context,
          filters: cubit.filters,
          options: options,
        );

        if (applied != null) await cubit.applyFilters(applied);
      },
    );
  }
}

class _Placeholder extends StatelessWidget {
  const _Placeholder({
    required this.icon,
    required this.title,
    required this.body,
    this.onRetry,
  });

  final IconData icon;
  final String title;
  final String body;
  final VoidCallback? onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 40),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 72,
              height: 72,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: const Color(0xFFF0EEE7),
                borderRadius: BorderRadius.circular(22),
              ),
              child: Icon(icon, size: 34, color: const Color(0xFFA39D90)),
            ),
            const SizedBox(height: 16),
            Text(title, style: AppTextStyles.titleLg),
            const SizedBox(height: 6),
            Text(
              body,
              textAlign: TextAlign.center,
              style: AppTextStyles.bodyMd,
            ),
            if (onRetry != null) ...[
              const SizedBox(height: 16),
              TextButton(
                onPressed: onRetry,
                child: const Text('إعادة المحاولة'),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
