import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/motion/motion.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../../../core/styles/category_visuals.dart';
import '../manager/browse_cubit/browse_cubit.dart';
import 'widgets/job_card.dart';

class BrowseView extends StatelessWidget {
  const BrowseView({super.key});

  @override
  Widget build(BuildContext context) {
    final cubit = context.watch<BrowseCubit>();

    return Scaffold(
      backgroundColor: AppColors.paper,
      body: BlocBuilder<BrowseCubit, BrowseState>(
        builder: (context, state) {
          return RefreshIndicator(
            color: AppColors.amber,
            onRefresh: () => context.read<BrowseCubit>().load(),
            child: ListView(
              padding: const EdgeInsets.only(bottom: 104),
              children: [
                _Hero(
                  onSearch: (q) => context.read<BrowseCubit>().search(q),
                ),
                const SizedBox(height: 18),
                _CategoryChips(
                  selected: cubit.filters.categoryCode,
                  onSelect: (code) =>
                      context.read<BrowseCubit>().selectCategory(code),
                ),
                const SizedBox(height: 8),
                _NearbyCard(onTap: () => context.go(RoutesKeys.nearby)),
                _SectionRow(
                  count: state is BrowseLoaded ? state.jobs.length : null,
                ),
                ..._content(context, state),
              ],
            ),
          );
        },
      ),
    );
  }

  List<Widget> _content(BuildContext context, BrowseState state) {
    return switch (state) {
      BrowseLoading() => [
        for (var i = 0; i < 3; i++)
          const Padding(
            padding: EdgeInsets.fromLTRB(20, 0, 20, 12),
            child: _JobSkeleton(),
          ),
      ],
      BrowseEmpty() => const [
        _Message(icon: Icons.work_off_rounded, text: 'لا توجد وظائف مطابقة حالياً.'),
      ],
      BrowseFailed(:final failure) => [
        _Message(icon: Icons.wifi_off_rounded, text: failure.message),
        Center(
          child: TextButton(
            onPressed: () => context.read<BrowseCubit>().load(),
            child: const Text('إعادة المحاولة'),
          ),
        ),
      ],
      BrowseLoaded(:final jobs) => [
        for (final (index, job) in jobs.indexed)
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 0, 20, 12),
            child: Entrance(
              index: index,
              child: JobCard(
                job: job,
                onTap: () => context.push(RoutesKeys.job(job.slug)),
              ),
            ),
          ),
      ],
    };
  }
}

/// The charcoal banner with the top bar, greeting and search box.
class _Hero extends StatelessWidget {
  const _Hero({required this.onSearch});

  final ValueChanged<String> onSearch;

  @override
  Widget build(BuildContext context) {
    final topInset = MediaQuery.paddingOf(context).top;

    return Container(
      padding: EdgeInsets.fromLTRB(22, topInset + 14, 22, 20),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topRight,
          end: Alignment.bottomLeft,
          colors: [Color(0xFF34302B), Color(0xFF2B2724), Color(0xFF26221F)],
        ),
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(28)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              _LocationPill(onTap: () => context.go(RoutesKeys.nearby)),
              const Spacer(),
              _BellButton(
                onTap: () => context.push(RoutesKeys.notifications),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            'أهلاً بك 👋',
            style: AppTextStyles.bodySm.copyWith(color: const Color(0xFFB7B1A6)),
          ),
          const SizedBox(height: 3),
          Text(
            'وظيفتك القادمة على بُعد خطوة',
            style: AppTextStyles.titleLg.copyWith(color: Colors.white),
          ),
          const SizedBox(height: 14),
          _SearchBox(onSearch: onSearch),
        ],
      ),
    );
  }
}

class _LocationPill extends StatelessWidget {
  const _LocationPill({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 9),
        decoration: BoxDecoration(
          color: AppColors.charcoalSoft,
          border: Border.all(color: const Color(0xFF3A332E)),
          borderRadius: BorderRadius.circular(15),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.location_on_rounded, size: 17, color: AppColors.amber),
            const SizedBox(width: 7),
            Text(
              'قريب مني',
              style: AppTextStyles.titleSm.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w800,
              ),
            ),
            const SizedBox(width: 4),
            const Icon(
              Icons.keyboard_arrow_down_rounded,
              size: 16,
              color: Color(0xFF9A958A),
            ),
          ],
        ),
      ),
    );
  }
}

class _BellButton extends StatelessWidget {
  const _BellButton({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Container(
        width: 42,
        height: 42,
        decoration: BoxDecoration(
          color: AppColors.charcoalSoft,
          border: Border.all(color: const Color(0xFF3A332E)),
          borderRadius: BorderRadius.circular(14),
        ),
        child: Stack(
          alignment: Alignment.center,
          children: [
            const Icon(
              Icons.notifications_rounded,
              size: 21,
              color: Color(0xFFEDE9E0),
            ),
            Positioned(
              top: 9,
              right: 10,
              child: PulseDot(color: AppColors.amber, size: 7),
            ),
          ],
        ),
      ),
    );
  }
}

class _SearchBox extends StatefulWidget {
  const _SearchBox({required this.onSearch});

  final ValueChanged<String> onSearch;

  @override
  State<_SearchBox> createState() => _SearchBoxState();
}

class _SearchBoxState extends State<_SearchBox> {
  final _controller = TextEditingController();

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _submit() => widget.onSearch(_controller.text);

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(15),
      ),
      padding: const EdgeInsets.fromLTRB(14, 4, 6, 4),
      child: Row(
        children: [
          const Icon(Icons.search_rounded, size: 20, color: Color(0xFFA39D90)),
          const SizedBox(width: 10),
          Expanded(
            child: TextField(
              controller: _controller,
              textInputAction: TextInputAction.search,
              onSubmitted: (_) => _submit(),
              style: AppTextStyles.bodyMd.copyWith(color: AppColors.textStrong),
              decoration: InputDecoration(
                isDense: true,
                border: InputBorder.none,
                hintText: 'ابحث عن وظيفة، مهنة، أو منشأة…',
                hintStyle: AppTextStyles.bodyMd.copyWith(
                  color: AppColors.textMuted,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ),
          Pressable(
            onTap: _submit,
            child: Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: AppColors.amber,
                borderRadius: BorderRadius.circular(10),
              ),
              child: const Icon(
                Icons.tune_rounded,
                size: 17,
                color: AppColors.textStrong,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _CategoryChips extends StatelessWidget {
  const _CategoryChips({required this.selected, required this.onSelect});

  final String? selected;
  final ValueChanged<String?> onSelect;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: 40,
      child: ListView(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 20),
        children: [
          _Chip(
            label: 'الكل',
            icon: Icons.apps_rounded,
            active: selected == null,
            onTap: () => onSelect(null),
          ),
          for (final code in CategoryVisuals.codes)
            Padding(
              padding: const EdgeInsetsDirectional.only(start: 9),
              child: _Chip(
                label: CategoryVisuals.label(code),
                icon: CategoryVisuals.icon(code),
                active: selected == code,
                onTap: () => onSelect(code),
              ),
            ),
        ],
      ),
    );
  }
}

class _Chip extends StatelessWidget {
  const _Chip({
    required this.label,
    required this.icon,
    required this.active,
    required this.onTap,
  });

  final String label;
  final IconData icon;
  final bool active;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: AnimatedContainer(
        duration: AppMotion.press,
        padding: const EdgeInsets.symmetric(horizontal: 15),
        decoration: BoxDecoration(
          color: active ? AppColors.warningBg : AppColors.surface,
          border: Border.all(
            color: active ? AppColors.amber : const Color(0xFFEDE9E0),
            width: 1.5,
          ),
          borderRadius: BorderRadius.circular(13),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              icon,
              size: 18,
              color: active ? AppColors.warningFg : AppColors.textBody,
            ),
            const SizedBox(width: 7),
            Text(
              label,
              style: AppTextStyles.bodyMd.copyWith(
                fontWeight: FontWeight.w700,
                color: active ? AppColors.warningFg : AppColors.textBody,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _NearbyCard extends StatelessWidget {
  const _NearbyCard({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(20, 8, 20, 4),
      child: Pressable(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              begin: Alignment.centerRight,
              end: Alignment.centerLeft,
              colors: [Color(0xFF2B2724), Color(0xFF34302B)],
            ),
            borderRadius: BorderRadius.circular(20),
          ),
          child: Row(
            children: [
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  color: AppColors.amber,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(
                  Icons.my_location_rounded,
                  size: 26,
                  color: AppColors.textStrong,
                ),
              ),
              const SizedBox(width: 13),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'الوظائف القريبة مني',
                      style: AppTextStyles.titleMd.copyWith(color: Colors.white),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      'اعرض الفرص حسب موقعك الحالي',
                      style: AppTextStyles.bodySm.copyWith(
                        color: const Color(0xFFC7C2B8),
                      ),
                    ),
                  ],
                ),
              ),
              const Icon(
                Icons.chevron_left_rounded,
                size: 22,
                color: AppColors.amber,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _SectionRow extends StatelessWidget {
  const _SectionRow({required this.count});

  final int? count;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(22, 18, 22, 10),
      child: Row(
        children: [
          Text('أحدث الوظائف', style: AppTextStyles.titleLg),
          const Spacer(),
          if (count != null)
            Text(
              '$count نتيجة',
              style: AppTextStyles.bodySm.copyWith(
                fontWeight: FontWeight.w700,
                color: AppColors.textMuted,
              ),
            ),
        ],
      ),
    );
  }
}

class _JobSkeleton extends StatelessWidget {
  const _JobSkeleton();

  @override
  Widget build(BuildContext context) {
    return Shimmer(
      child: Container(
        height: 150,
        decoration: BoxDecoration(
          color: AppColors.surface,
          border: Border.all(color: const Color(0xFFF0EDE6)),
          borderRadius: BorderRadius.circular(22),
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
    return Padding(
      padding: const EdgeInsets.fromLTRB(40, 60, 40, 20),
      child: Column(
        children: [
          Icon(icon, size: 46, color: AppColors.textMuted),
          const SizedBox(height: 14),
          Text(
            text,
            textAlign: TextAlign.center,
            style: AppTextStyles.bodyMd.copyWith(color: AppColors.textBody),
          ),
        ],
      ),
    );
  }
}
