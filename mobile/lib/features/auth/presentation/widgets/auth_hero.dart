import 'package:flutter/material.dart';

import '../../../../core/motion/motion.dart';
import '../../../../core/styles/app_colors.dart';

/// The concentric-ring amber disc that heads the phone and OTP screens, with
/// the two accent dots and a slow float — matching the design's hero circle.
class AuthHero extends StatelessWidget {
  const AuthHero({required this.icon, this.discSize = 94, super.key});

  final IconData icon;
  final double discSize;

  @override
  Widget build(BuildContext context) {
    final ringOuter = discSize + 34;
    final ringInner = discSize + 14;

    return SizedBox(
      width: 172,
      height: 172,
      child: Stack(
        alignment: Alignment.center,
        children: [
          // Ambient glow.
          Container(
            width: 172,
            height: 172,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: RadialGradient(
                colors: [
                  AppColors.amber.withValues(alpha: .16),
                  AppColors.amber.withValues(alpha: 0),
                ],
                stops: const [0, .64],
              ),
            ),
          ),
          _Ring(size: ringOuter, opacity: .22),
          _Ring(size: ringInner, opacity: .32),
          Floating(
            amplitude: 8,
            child: Container(
              width: discSize,
              height: discSize,
              decoration: const BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  center: Alignment(-.36, -.48),
                  colors: [Color(0xFFFDEFC2), Color(0xFFF6D783)],
                ),
                boxShadow: [
                  BoxShadow(
                    color: Color(0x8CF7BE17),
                    blurRadius: 32,
                    offset: Offset(0, 18),
                    spreadRadius: -14,
                  ),
                ],
              ),
              child: Icon(icon, size: discSize * 0.45, color: const Color(0xFF7A5E0E)),
            ),
          ),
          // Accent dots pinned to the disc's corners.
          Positioned(
            top: (172 - discSize) / 2,
            left: (172 - discSize) / 2 + 14,
            child: _Dot(size: 9, color: AppColors.amber),
          ),
          Positioned(
            bottom: (172 - discSize) / 2 + 6,
            right: (172 - discSize) / 2 + 6,
            child: _Dot(size: 7, color: AppColors.charcoalSoft),
          ),
        ],
      ),
    );
  }
}

class _Ring extends StatelessWidget {
  const _Ring({required this.size, required this.opacity});

  final double size;
  final double opacity;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        border: Border.all(
          color: AppColors.amber.withValues(alpha: opacity),
          width: 1.5,
        ),
      ),
    );
  }
}

class _Dot extends StatelessWidget {
  const _Dot({required this.size, required this.color});

  final double size;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(color: color, shape: BoxShape.circle),
    );
  }
}
