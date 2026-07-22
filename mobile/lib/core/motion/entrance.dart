import 'package:flutter/widgets.dart';

import 'app_motion.dart';

/// Mirrors the design's `.enter` cardIn: a child rises and scales into place,
/// staggered by [index] so a list settles one item after another. No fade —
/// transform only, matching the design's keyframes.
class Entrance extends StatefulWidget {
  const Entrance({
    required this.child,
    this.index = 0,
    this.enabled = true,
    super.key,
  });

  final Widget child;

  /// Position among siblings; drives the stagger delay.
  final int index;

  /// When false the child is placed at rest immediately (e.g. for tests).
  final bool enabled;

  @override
  State<Entrance> createState() => _EntranceState();
}

class _EntranceState extends State<Entrance>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller = AnimationController(
    vsync: this,
    duration: AppMotion.cardEnter,
  );

  @override
  void initState() {
    super.initState();

    if (!widget.enabled) {
      _controller.value = 1;
      return;
    }

    Future<void>.delayed(
      AppMotion.staggerBase + AppMotion.stagger * widget.index,
      () {
        if (mounted) _controller.forward();
      },
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final animation = CurvedAnimation(
      parent: _controller,
      curve: AppMotion.easeOut,
    );

    return AnimatedBuilder(
      animation: animation,
      builder: (context, child) {
        final settled = animation.value;
        return Transform.translate(
          offset: Offset(0, AppMotion.cardOffset * (1 - settled)),
          child: Transform.scale(
            scale: 0.97 + 0.03 * settled,
            child: child,
          ),
        );
      },
      child: widget.child,
    );
  }
}
