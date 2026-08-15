import 'dart:async';
import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/managers/user_cubit/user_cubit.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_dimensions.dart';
import '../../../../core/styles/app_text_styles.dart';

class SplashView extends StatefulWidget {
  const SplashView({super.key});

  @override
  State<SplashView> createState() => _SplashViewState();
}

class _SplashViewState extends State<SplashView> with TickerProviderStateMixin {
  late final AnimationController _breathe = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1500),
  )..repeat(reverse: true);

  late final AnimationController _spin = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1000),
  )..repeat();

  @override
  void initState() {
    super.initState();
    _decideRoute();
  }

  /// Hold the splash for its animation, then route on the restored session:
  /// a signed-in user goes straight to their home instead of being bounced to
  /// the role picker (which read as being logged out on every cold start).
  Future<void> _decideRoute() async {
    // `restore()` runs at app start; wait a little more if it hasn't resolved.
    final cubit = context.read<UserCubit>();

    await Future<void>.delayed(const Duration(milliseconds: 1600));

    var waited = 0;
    while (mounted && cubit.state is UserUnknown && waited < 4000) {
      await Future<void>.delayed(const Duration(milliseconds: 150));
      waited += 150;
    }
    if (!mounted) return;

    context.go(switch (cubit.state) {
      UserSignedIn(:final user) =>
        user.isEmployer ? RoutesKeys.employerJobs : RoutesKeys.browse,
      _ => RoutesKeys.onboarding,
    });
  }

  @override
  void dispose() {
    _breathe.dispose();
    _spin.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: DecoratedBox(
        decoration: const BoxDecoration(
          gradient: RadialGradient(
            center: Alignment(0, -0.36),
            radius: 1.3,
            colors: [Color(0xFFFFFFFF), Color(0xFFEDF1EC)],
          ),
        ),
        child: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              SizedBox(
                width: 148,
                height: 148,
                child: Stack(
                  alignment: Alignment.center,
                  children: [
                    // Static track ring.
                    Container(
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: const Color(0xFFE5EAE6),
                          width: 3,
                        ),
                      ),
                    ),
                    // Rotating amber arc sweeping the top-right of the ring.
                    RotationTransition(
                      turns: _spin,
                      child: CustomPaint(
                        size: const Size.square(148),
                        painter: _ArcPainter(),
                      ),
                    ),
                    ScaleTransition(
                      scale: Tween(begin: 1.0, end: 1.09).animate(
                        CurvedAnimation(
                          parent: _breathe,
                          curve: Curves.easeInOut,
                        ),
                      ),
                      child: Image.asset(
                        'assets/images/zeno-mark.png',
                        width: 76,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: AppDimensions.space22),
              Text(
                'Zeno',
                textDirection: TextDirection.ltr,
                style: AppTextStyles.displayLg.copyWith(
                  fontSize: 32,
                  fontWeight: FontWeight.w800,
                  letterSpacing: -0.6,
                ),
              ),
              const SizedBox(height: AppDimensions.space6),
              Text(
                'جارٍ التحميل...',
                style: AppTextStyles.caption.copyWith(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF869089),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// The amber quarter-plus arc that rides on the splash ring.
class _ArcPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = AppColors.amber
      ..strokeWidth = 3
      ..strokeCap = StrokeCap.round
      ..style = PaintingStyle.stroke;

    // Start at the top and sweep 130° through the right side.
    canvas.drawArc(
      Rect.fromLTWH(1.5, 1.5, size.width - 3, size.height - 3),
      -math.pi / 2,
      math.pi * 0.72,
      false,
      paint,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
