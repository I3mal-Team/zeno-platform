import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/components/app_button.dart';
import '../../../../core/motion/motion.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';

/// The full-screen confirmation shown after an application is sent, with the
/// design's popping tick, expanding ping ring, and the request number card.
class SubmittedView extends StatefulWidget {
  const SubmittedView({required this.reference, super.key});

  final String reference;

  @override
  State<SubmittedView> createState() => _SubmittedViewState();
}

class _SubmittedViewState extends State<SubmittedView>
    with TickerProviderStateMixin {
  late final AnimationController _pop = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 500),
  )..forward();

  late final AnimationController _ping = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 2000),
  )..repeat();

  @override
  void dispose() {
    _pop.dispose();
    _ping.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.fromLTRB(26, 32, 26, 32),
          child: Column(
            children: [
              Expanded(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    _SuccessMark(pop: _pop, ping: _ping),
                    const SizedBox(height: 18),
                    Text(
                      'تم إرسال طلبك بنجاح',
                      style: AppTextStyles.displayLg.copyWith(fontSize: 21),
                    ),
                    const SizedBox(height: 8),
                    SizedBox(
                      width: 280,
                      child: Text(
                        'تم إرسال ملفك الشخصي إلى صاحب العمل. ستظهر حالة الطلب في قسم «طلباتي».',
                        textAlign: TextAlign.center,
                        style: AppTextStyles.bodyLg.copyWith(
                          color: AppColors.textMuted,
                          height: 1.7,
                        ),
                      ),
                    ),
                    const SizedBox(height: 26),
                    _ReferenceCard(reference: widget.reference),
                  ],
                ),
              ),
              AppButton(
                label: 'متابعة الطلب',
                onPressed: () => context.go(RoutesKeys.applications),
              ),
              const SizedBox(height: 11),
              _SecondaryButton(
                label: 'العودة للرئيسية',
                onTap: () => context.go(RoutesKeys.browse),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _SuccessMark extends StatelessWidget {
  const _SuccessMark({required this.pop, required this.ping});

  final Animation<double> pop;
  final Animation<double> ping;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 110,
      height: 110,
      child: Stack(
        alignment: Alignment.center,
        children: [
          // Expanding ping ring.
          AnimatedBuilder(
            animation: ping,
            builder: (context, child) {
              final t = ping.value;
              return Transform.scale(
                scale: 0.6 + 1.8 * t,
                child: Opacity(
                  opacity: (0.7 * (1 - t)).clamp(0.0, 1.0),
                  child: Container(
                    width: 96,
                    height: 96,
                    decoration: BoxDecoration(
                      color: AppColors.successFg.withValues(alpha: 0.18),
                      shape: BoxShape.circle,
                    ),
                  ),
                ),
              );
            },
          ),
          // Popping tick disc.
          ScaleTransition(
            scale: Tween<double>(begin: 0.4, end: 1).animate(
              CurvedAnimation(parent: pop, curve: AppMotion.overshoot),
            ),
            child: Container(
              width: 96,
              height: 96,
              decoration: const BoxDecoration(
                color: AppColors.successFg,
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.check_circle_rounded,
                size: 56,
                color: Colors.white,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ReferenceCard extends StatelessWidget {
  const _ReferenceCard({required this.reference});

  final String reference;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      constraints: const BoxConstraints(maxWidth: 300),
      padding: const EdgeInsets.symmetric(horizontal: 30, vertical: 22),
      decoration: BoxDecoration(
        color: AppColors.charcoalSoft,
        borderRadius: BorderRadius.circular(22),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'رقم الطلب',
            style: AppTextStyles.bodySm.copyWith(
              fontWeight: FontWeight.w700,
              color: const Color(0xFFC7C2B8),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            reference,
            textDirection: TextDirection.ltr,
            style: AppTextStyles.displayLg.copyWith(
              fontSize: 34,
              color: AppColors.amber,
              letterSpacing: 1,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            'استخدم هذا الرقم لمتابعة طلبك',
            style: AppTextStyles.caption.copyWith(
              fontWeight: FontWeight.w600,
              color: const Color(0xFF9A958A),
            ),
          ),
        ],
      ),
    );
  }
}

class _SecondaryButton extends StatelessWidget {
  const _SecondaryButton({required this.label, required this.onTap});

  final String label;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Container(
        height: 50,
        width: double.infinity,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: AppColors.surface,
          border: Border.all(color: const Color(0xFFE5EAE6), width: 1.5),
          borderRadius: BorderRadius.circular(17),
        ),
        child: Text(
          label,
          style: AppTextStyles.button.copyWith(color: AppColors.textStrong),
        ),
      ),
    );
  }
}
