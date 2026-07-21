import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../manager/browse_cubit/browse_cubit.dart';
import 'widgets/job_card.dart';

class BrowseView extends StatelessWidget {
  const BrowseView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            const _BrowseHeader(),
            Expanded(
              child: BlocBuilder<BrowseCubit, BrowseState>(
                builder: (context, state) => switch (state) {
                  BrowseLoading() => const Center(
                    child: CircularProgressIndicator(color: AppColors.amber),
                  ),
                  BrowseEmpty() => const _BrowseMessage(
                    icon: Icons.work_off_rounded,
                    text: 'لا توجد وظائف مطابقة حالياً.',
                  ),
                  BrowseFailed(:final failure) => _BrowseMessage(
                    icon: Icons.wifi_off_rounded,
                    text: failure.message,
                    onRetry: () => context.read<BrowseCubit>().load(),
                  ),
                  BrowseLoaded(:final jobs) => RefreshIndicator(
                    color: AppColors.amber,
                    onRefresh: () => context.read<BrowseCubit>().load(),
                    child: ListView.separated(
                      padding: const EdgeInsets.fromLTRB(20, 4, 20, 24),
                      itemCount: jobs.length,
                      separatorBuilder: (_, _) => const SizedBox(height: 12),
                      itemBuilder: (_, index) {
                        final job = jobs[index];

                        return JobCard(
                          job: job,
                          onTap: () => context.push(RoutesKeys.job(job.slug)),
                        );
                      },
                    ),
                  ),
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _BrowseHeader extends StatelessWidget {
  const _BrowseHeader();

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 8, 20, 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text('أحدث الوظائف', style: AppTextStyles.displayLg),
              const Spacer(),
              BlocBuilder<BrowseCubit, BrowseState>(
                builder: (_, state) => Text(
                  state is BrowseLoaded ? '${state.jobs.length} نتيجة' : '',
                  style: AppTextStyles.caption.copyWith(
                    fontWeight: FontWeight.w700,
                    color: AppColors.textMuted,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          _SearchField(
            onSubmitted: (value) => context.read<BrowseCubit>().search(value),
          ),
        ],
      ),
    );
  }
}

class _SearchField extends StatelessWidget {
  const _SearchField({required this.onSubmitted});

  final ValueChanged<String> onSubmitted;

  @override
  Widget build(BuildContext context) {
    return TextField(
      onSubmitted: onSubmitted,
      textInputAction: TextInputAction.search,
      style: AppTextStyles.bodyMd,
      decoration: InputDecoration(
        hintText: 'ابحث عن وظيفة أو منشأة',
        hintStyle: AppTextStyles.bodyMd.copyWith(color: AppColors.textMuted),
        prefixIcon: const Icon(
          Icons.search_rounded,
          color: AppColors.textMuted,
        ),
        filled: true,
        fillColor: AppColors.surface,
        contentPadding: const EdgeInsets.symmetric(vertical: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: AppColors.border),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: AppColors.border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: AppColors.amber),
        ),
      ),
    );
  }
}

class _BrowseMessage extends StatelessWidget {
  const _BrowseMessage({required this.icon, required this.text, this.onRetry});

  final IconData icon;
  final String text;
  final VoidCallback? onRetry;

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
          if (onRetry != null) ...[
            const SizedBox(height: 12),
            TextButton(onPressed: onRetry, child: const Text('إعادة المحاولة')),
          ],
        ],
      ),
    );
  }
}
