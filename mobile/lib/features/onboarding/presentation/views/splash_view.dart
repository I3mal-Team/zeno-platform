import 'dart:async';

import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_dimensions.dart';
import '../../../../core/styles/app_text_styles.dart';

class SplashView extends StatefulWidget {
  const SplashView({super.key});

  @override
  State<SplashView> createState() => _SplashViewState();
}

class _SplashViewState extends State<SplashView>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1500),
  )..repeat(reverse: true);

  Timer? _timer;

  @override
  void initState() {
    super.initState();
    _timer = Timer(
      const Duration(milliseconds: 2100),
      () => mounted ? context.go(RoutesKeys.onboarding) : null,
    );
  }

  @override
  void dispose() {
    _timer?.cancel();
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: DecoratedBox(
        decoration: const BoxDecoration(
          gradient: RadialGradient(
            center: Alignment(0, -.36),
            radius: 1.3,
            colors: [Color(0xFFFFFFFF), Color(0xFFF4F2EC)],
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
                    Container(
                      decoration: BoxDecoration(
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: const Color(0xFFF0EDE3),
                          width: 3,
                        ),
                      ),
                    ),
                    const SizedBox(
                      width: 148,
                      height: 148,
                      child: CircularProgressIndicator(
                        strokeWidth: 3,
                        color: AppColors.amber,
                      ),
                    ),
                    ScaleTransition(
                      scale: Tween(begin: 1.0, end: 1.08).animate(
                        CurvedAnimation(
                          parent: _controller,
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
                'zeno',
                textDirection: TextDirection.ltr,
                style: AppTextStyles.displayLg.copyWith(
                  fontSize: 32,
                  fontWeight: FontWeight.w800,
                  letterSpacing: -.6,
                ),
              ),
              const SizedBox(height: AppDimensions.space6),
              Text('جارٍ التحميل...', style: AppTextStyles.caption),
            ],
          ),
        ),
      ),
    );
  }
}
