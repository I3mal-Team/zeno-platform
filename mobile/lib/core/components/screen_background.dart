import 'package:flutter/material.dart';

import '../styles/app_colors.dart';

/// The warm radial wash with the dotted overlay used across the entry screens.
class ScreenBackground extends StatelessWidget {
  const ScreenBackground({
    required this.child,
    this.showGlow = true,
    super.key,
  });

  final Widget child;
  final bool showGlow;

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: const BoxDecoration(
        gradient: RadialGradient(
          center: Alignment(0, -1.1),
          radius: 1.25,
          colors: [Color(0xFFFFFFFF), Color(0xFFF4F2EC), Color(0xFFEDE9DF)],
          stops: [0, .55, 1],
        ),
      ),
      child: Stack(
        children: [
          const Positioned.fill(child: _DotGrid()),
          if (showGlow) ...[
            const Positioned(
              top: -50,
              right: -40,
              child: _Glow(size: 260, color: AppColors.amber, opacity: .32),
            ),
            const Positioned(
              bottom: 150,
              left: -70,
              child: _Glow(
                size: 240,
                color: AppColors.charcoalSoft,
                opacity: .10,
              ),
            ),
          ],
          child,
        ],
      ),
    );
  }
}

class _Glow extends StatelessWidget {
  const _Glow({required this.size, required this.color, required this.opacity});

  final double size;
  final Color color;
  final double opacity;

  @override
  Widget build(BuildContext context) {
    return IgnorePointer(
      child: Container(
        width: size,
        height: size,
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          gradient: RadialGradient(
            colors: [
              color.withValues(alpha: opacity),
              color.withValues(alpha: 0),
            ],
            stops: const [0, .68],
          ),
        ),
      ),
    );
  }
}

class _DotGrid extends StatelessWidget {
  const _DotGrid();

  @override
  Widget build(BuildContext context) {
    return IgnorePointer(child: CustomPaint(painter: _DotPainter()));
  }
}

class _DotPainter extends CustomPainter {
  static const _spacing = 22.0;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = const Color(0xFF2B2724).withValues(alpha: .045);

    for (var y = 0.0; y < size.height; y += _spacing) {
      for (var x = 0.0; x < size.width; x += _spacing) {
        canvas.drawCircle(Offset(x, y), 1.2, paint);
      }
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
