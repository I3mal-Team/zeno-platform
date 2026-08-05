import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/motion/motion.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../../../core/styles/category_visuals.dart';
import '../../data/models/application_model.dart';
import '../manager/applications_cubit/applications_cubit.dart';

class MyApplicationsView extends StatelessWidget {
  const MyApplicationsView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: BlocBuilder<ApplicationsCubit, ApplicationsState>(
        builder: (context, state) {
          final count = state is ApplicationsLoaded
              ? state.applications.length
              : null;

          return Column(
            children: [
              _Header(count: count),
              Expanded(
                child: switch (state) {
                  ApplicationsLoading() => const Center(
                    child: CircularProgressIndicator(color: AppColors.amber),
                  ),
                  ApplicationsEmpty() => const _EmptyState(),
                  ApplicationsFailed(:final failure) => _Message(
                    icon: Icons.wifi_off_rounded,
                    text: failure.message,
                  ),
                  ApplicationsLoaded(:final applications) => RefreshIndicator(
                    color: AppColors.amber,
                    onRefresh: () => context.read<ApplicationsCubit>().load(),
                    child: ListView.separated(
                      padding: const EdgeInsets.fromLTRB(20, 18, 20, 104),
                      itemCount: applications.length,
                      separatorBuilder: (_, _) => const SizedBox(height: 13),
                      itemBuilder: (_, index) => Entrance(
                        index: index,
                        child: _ApplicationCard(
                          application: applications[index],
                        ),
                      ),
                    ),
                  ),
                },
              ),
            ],
          );
        },
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({required this.count});

  final int? count;

  @override
  Widget build(BuildContext context) {
    final topInset = MediaQuery.paddingOf(context).top;

    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(22, topInset + 16, 22, 24),
      decoration: const BoxDecoration(
        color: AppColors.charcoalSoft,
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(28)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'طلباتي',
            style: AppTextStyles.titleLg.copyWith(color: Colors.white),
          ),
          const SizedBox(height: 5),
          Text(
            count != null
                ? '$count طلبات — تابع حالة كل طلب لحظة بلحظة'
                : 'تابع حالة كل طلب لحظة بلحظة',
            style: AppTextStyles.bodySm.copyWith(
              color: const Color(0xFFC7C2B8),
            ),
          ),
        ],
      ),
    );
  }
}

class _ApplicationCard extends StatelessWidget {
  const _ApplicationCard({required this.application});

  final ApplicationModel application;

  @override
  Widget build(BuildContext context) {
    final (tint, _) = AppColors.categoryTint(application.categoryCode);

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: AppColors.border),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: tint,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(
                  CategoryVisuals.icon(application.categoryCode),
                  color: Colors.white,
                  size: 24,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      application.jobTitle ?? 'وظيفة',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: AppTextStyles.titleMd,
                    ),
                    const SizedBox(height: 2),
                    Text(
                      application.organizationName ?? '',
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
                status: application.status,
                label: application.statusLabel,
              ),
            ],
          ),
          const SizedBox(height: 16),
          _ProgressTrack(status: application.status),
          const SizedBox(height: 15),
          const Divider(height: 1, color: Color(0xFFF2F0EA)),
          const SizedBox(height: 12),
          Row(
            children: [
              Text(
                'طلب ',
                style: AppTextStyles.caption.copyWith(
                  color: AppColors.textMuted,
                ),
              ),
              Text(
                application.reference,
                textDirection: TextDirection.ltr,
                style: AppTextStyles.caption.copyWith(
                  fontWeight: FontWeight.w800,
                  color: AppColors.textMuted,
                ),
              ),
              const Spacer(),
              if (application.status == 'accepted')
                _ContactButton(
                  onTap: () {
                    final uuid = application.conversationUuid;
                    if (uuid != null) {
                      final title = Uri.encodeComponent(
                        application.jobTitle ?? 'المحادثة',
                      );
                      context.push('${RoutesKeys.chat(uuid)}?title=$title');
                    } else {
                      context.go(RoutesKeys.messages);
                    }
                  },
                )
              else if (application.isDecidable)
                GestureDetector(
                  onTap: () => _confirmWithdraw(context),
                  child: Text(
                    'سحب الطلب',
                    style: AppTextStyles.caption.copyWith(
                      fontWeight: FontWeight.w800,
                      color: AppColors.errorFg,
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }

  void _confirmWithdraw(BuildContext context) {
    final cubit = context.read<ApplicationsCubit>();

    showDialog<void>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text('سحب الطلب', style: AppTextStyles.titleMd),
        content: Text(
          'هل تريد سحب طلبك على هذه الوظيفة؟',
          style: AppTextStyles.bodyMd,
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(dialogContext).pop(),
            child: const Text('إلغاء'),
          ),
          TextButton(
            onPressed: () {
              Navigator.of(dialogContext).pop();
              cubit.withdraw(application.reference);
            },
            child: Text('سحب', style: TextStyle(color: AppColors.errorFg)),
          ),
        ],
      ),
    );
  }
}

/// The three-step tracker: applied → in review → decision, coloured by status.
class _ProgressTrack extends StatelessWidget {
  const _ProgressTrack({required this.status});

  final String status;

  @override
  Widget build(BuildContext context) {
    const pending = Color(0xFFDCE3DD);
    final reached =
        status == 'review' || status == 'accepted' || status == 'rejected';
    final decided = status == 'accepted' || status == 'rejected';
    final decisionColor = switch (status) {
      'accepted' => AppColors.successFg,
      'rejected' => AppColors.errorFg,
      _ => pending,
    };
    final withdrawn = status == 'withdrawn';

    final dot0 = withdrawn ? AppColors.neutralFg : AppColors.amber;
    final dot1 = withdrawn
        ? AppColors.neutralFg
        : (reached ? AppColors.amber : pending);
    final line1 = reached ? AppColors.amber : pending;
    final line2 = decided ? decisionColor : pending;

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 4),
          child: Row(
            children: [
              _Dot(color: dot0),
              Expanded(child: _Line(color: line1)),
              _Dot(color: dot1),
              Expanded(child: _Line(color: line2)),
              _Dot(color: decided ? decisionColor : pending),
            ],
          ),
        ),
        const SizedBox(height: 7),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            _stepLabel('تم التقديم'),
            _stepLabel('قيد المراجعة'),
            _stepLabel('القرار'),
          ],
        ),
      ],
    );
  }

  Widget _stepLabel(String text) => Text(
    text,
    style: AppTextStyles.caption.copyWith(
      fontSize: 10.5,
      fontWeight: FontWeight.w700,
      color: AppColors.textMuted,
    ),
  );
}

class _Dot extends StatelessWidget {
  const _Dot({required this.color});

  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 11,
      height: 11,
      decoration: BoxDecoration(color: color, shape: BoxShape.circle),
    );
  }
}

class _Line extends StatelessWidget {
  const _Line({required this.color});

  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(height: 3, color: color);
  }
}

class _ContactButton extends StatelessWidget {
  const _ContactButton({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 15, vertical: 9),
        decoration: BoxDecoration(
          color: AppColors.charcoalSoft,
          borderRadius: BorderRadius.circular(11),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.forum_rounded, size: 17, color: Colors.white),
            const SizedBox(width: 6),
            Text(
              'تواصل الآن',
              style: AppTextStyles.caption.copyWith(
                fontWeight: FontWeight.w800,
                color: Colors.white,
              ),
            ),
          ],
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
      padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 6),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(9),
      ),
      child: Text(
        label,
        style: AppTextStyles.caption.copyWith(
          fontSize: 12.5,
          fontWeight: FontWeight.w800,
          color: fg,
        ),
      ),
    );
  }
}

class _EmptyState extends StatelessWidget {
  const _EmptyState();

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        const SizedBox(height: 46),
        Container(
          width: 84,
          height: 84,
          decoration: BoxDecoration(
            color: AppColors.surface,
            border: Border.all(color: AppColors.border),
            borderRadius: BorderRadius.circular(26),
          ),
          child: const Icon(
            Icons.description_outlined,
            size: 40,
            color: Color(0xFFC9C4B9),
          ),
        ),
        const SizedBox(height: 16),
        Text('لا توجد طلبات بعد', style: AppTextStyles.titleMd),
        const SizedBox(height: 6),
        Text(
          'تصفّح الوظائف وقدّم على أول فرصة تناسبك',
          style: AppTextStyles.bodySm.copyWith(color: AppColors.textMuted),
        ),
      ],
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
