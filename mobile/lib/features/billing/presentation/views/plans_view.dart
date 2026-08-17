import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../core/motion/motion.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../data/models/plan_model.dart';
import '../manager/plans_cubit/plans_cubit.dart';

class PlansView extends StatelessWidget {
  const PlansView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: Column(
        children: [
          const _Header(),
          Expanded(
            child: BlocBuilder<PlansCubit, PlansState>(
              builder: (context, state) => switch (state) {
                PlansLoading() => const Center(
                  child: CircularProgressIndicator(color: AppColors.amber),
                ),
                PlansFailed(:final failure) => _ErrorBody(
                  message: failure.message,
                  onRetry: () => context.read<PlansCubit>().load(),
                ),
                PlansLoaded(:final plans) => _Body(plans: plans),
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
              alignment: Alignment.center,
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
            'الباقات',
            style: AppTextStyles.titleMd.copyWith(color: Colors.white),
          ),
        ],
      ),
    );
  }
}

class _Body extends StatelessWidget {
  const _Body({required this.plans});

  final List<PlanModel> plans;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 32),
      children: [
        Text(
          'اختر الباقة المناسبة لك',
          style: AppTextStyles.titleLg,
        ),
        const SizedBox(height: 6),
        Text(
          'رقِّ باقتك لتحصل على مزايا أكثر. الدفع سيتاح قريباً.',
          style: AppTextStyles.bodySm,
        ),
        const SizedBox(height: 16),
        for (final plan in plans) ...[
          _PlanCard(plan: plan),
          const SizedBox(height: 14),
        ],
      ],
    );
  }
}

class _PlanCard extends StatelessWidget {
  const _PlanCard({required this.plan});

  final PlanModel plan;

  @override
  Widget build(BuildContext context) {
    final highlighted = plan.isCurrent;

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(
          color: highlighted ? AppColors.amber : AppColors.border,
          width: highlighted ? 1.6 : 1,
        ),
        borderRadius: BorderRadius.circular(22),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Text(plan.name, style: AppTextStyles.titleMd),
              ),
              if (plan.isCurrent) const _CurrentChip(),
            ],
          ),
          const SizedBox(height: 10),
          _Price(plan: plan),
          if (plan.grantedFeatures.isNotEmpty) ...[
            const SizedBox(height: 16),
            for (final feature in plan.grantedFeatures) ...[
              _FeatureRow(text: feature.display),
              const SizedBox(height: 8),
            ],
          ],
          const SizedBox(height: 8),
          _PlanCta(plan: plan),
        ],
      ),
    );
  }
}

class _Price extends StatelessWidget {
  const _Price({required this.plan});

  final PlanModel plan;

  @override
  Widget build(BuildContext context) {
    if (plan.isFree) {
      return Text(
        'مجانية',
        style: AppTextStyles.titleLg.copyWith(color: AppColors.successFg),
      );
    }

    return Row(
      crossAxisAlignment: CrossAxisAlignment.baseline,
      textBaseline: TextBaseline.alphabetic,
      children: [
        Text(
          plan.price.toStringAsFixed(0),
          style: AppTextStyles.displayLg.copyWith(color: AppColors.textStrong),
        ),
        const SizedBox(width: 4),
        Text(
          'ر.س / ${plan.durationDays} يوم',
          style: AppTextStyles.bodySm,
        ),
      ],
    );
  }
}

class _FeatureRow extends StatelessWidget {
  const _FeatureRow({required this.text});

  final String text;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Icon(
          Icons.check_circle_rounded,
          size: 18,
          color: AppColors.successFg,
        ),
        const SizedBox(width: 8),
        Expanded(
          child: Text(text, style: AppTextStyles.bodyMd),
        ),
      ],
    );
  }
}

class _CurrentChip extends StatelessWidget {
  const _CurrentChip();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
      decoration: BoxDecoration(
        color: AppColors.successBg,
        borderRadius: BorderRadius.circular(9),
      ),
      child: Text(
        'باقتك الحالية',
        style: AppTextStyles.caption.copyWith(color: AppColors.successFg),
      ),
    );
  }
}

/// Purchase is intentionally not wired yet (payment is a later phase), so the
/// CTA states are: the current plan (inert), or an upgrade marked "قريباً".
class _PlanCta extends StatelessWidget {
  const _PlanCta({required this.plan});

  final PlanModel plan;

  @override
  Widget build(BuildContext context) {
    if (plan.isCurrent) {
      return _Button(
        label: 'باقتك الحالية',
        filled: false,
        onTap: null,
      );
    }

    if (plan.isFree) {
      return const SizedBox.shrink();
    }

    return _Button(
      label: 'الاشتراك (قريباً)',
      filled: true,
      onTap: null,
    );
  }
}

class _Button extends StatelessWidget {
  const _Button({required this.label, required this.filled, this.onTap});

  final String label;
  final bool filled;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final disabled = onTap == null;

    return Opacity(
      opacity: disabled ? .55 : 1,
      child: Pressable(
        onTap: onTap ?? () {},
        child: Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(vertical: 14),
          alignment: Alignment.center,
          decoration: BoxDecoration(
            color: filled ? AppColors.charcoalSoft : AppColors.surface,
            border: Border.all(
              color: filled ? AppColors.charcoalSoft : AppColors.borderStrong,
            ),
            borderRadius: BorderRadius.circular(14),
          ),
          child: Text(
            label,
            style: AppTextStyles.button.copyWith(
              fontSize: 15,
              color: filled ? Colors.white : AppColors.textBody,
            ),
          ),
        ),
      ),
    );
  }
}

class _ErrorBody extends StatelessWidget {
  const _ErrorBody({required this.message, required this.onRetry});

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Text(message, style: AppTextStyles.bodyMd),
          const SizedBox(height: 12),
          Pressable(
            onTap: onRetry,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
              decoration: BoxDecoration(
                color: AppColors.charcoalSoft,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                'إعادة المحاولة',
                style: AppTextStyles.button.copyWith(
                  fontSize: 14,
                  color: Colors.white,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
