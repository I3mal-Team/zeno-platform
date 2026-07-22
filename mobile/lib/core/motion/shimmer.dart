import 'package:flutter/widgets.dart';

import '../styles/app_colors.dart';

/// Mirrors the design's `shine`: a highlight sweeps across a surface, used on
/// loading skeletons so waiting feels alive rather than frozen.
class Shimmer extends StatefulWidget {
  const Shimmer({required this.child, super.key});

  final Widget child;

  @override
  State<Shimmer> createState() => _ShimmerState();
}

class _ShimmerState extends State<Shimmer> with SingleTickerProviderStateMixin {
  late final AnimationController _controller = AnimationController(
    vsync: this,
    duration: const Duration(milliseconds: 1400),
  )..repeat();

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: _controller,
      builder: (context, child) {
        return ShaderMask(
          blendMode: BlendMode.srcATop,
          shaderCallback: (bounds) {
            // Sweep the highlight from before the start edge to past the end.
            final travel = -1.2 + 3.4 * _controller.value;
            return LinearGradient(
              begin: Alignment(travel - 0.6, 0),
              end: Alignment(travel + 0.6, 0),
              colors: const [
                AppColors.borderStrong,
                AppColors.surface,
                AppColors.borderStrong,
              ],
              stops: const [0.35, 0.5, 0.65],
            ).createShader(bounds);
          },
          child: child,
        );
      },
      child: widget.child,
    );
  }
}
