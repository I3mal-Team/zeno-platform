import 'package:flutter/widgets.dart';

import 'app_motion.dart';

/// Mirrors the design's `obDrift`: a slow, endless vertical float used on the
/// hero icons of the splash, phone and OTP screens.
class Floating extends StatefulWidget {
  const Floating({
    required this.child,
    this.amplitude = 13,
    this.duration = AppMotion.drift,
    super.key,
  });

  final Widget child;

  /// Peak vertical travel, in logical pixels.
  final double amplitude;
  final Duration duration;

  @override
  State<Floating> createState() => _FloatingState();
}

class _FloatingState extends State<Floating>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller = AnimationController(
    vsync: this,
    duration: widget.duration,
  )..repeat(reverse: true);

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final animation = CurvedAnimation(
      parent: _controller,
      curve: Curves.easeInOut,
    );

    return AnimatedBuilder(
      animation: animation,
      builder: (context, child) => Transform.translate(
        offset: Offset(0, -widget.amplitude * animation.value),
        child: child,
      ),
      child: widget.child,
    );
  }
}
