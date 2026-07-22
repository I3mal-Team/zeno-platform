import 'package:flutter/widgets.dart';

import 'app_motion.dart';

/// Mirrors the design's `pulseDot`: a small accent dot that breathes, used to
/// mark live or unread state (nearby ping, unread notifications).
class PulseDot extends StatefulWidget {
  const PulseDot({required this.color, this.size = 8, super.key});

  final Color color;
  final double size;

  @override
  State<PulseDot> createState() => _PulseDotState();
}

class _PulseDotState extends State<PulseDot>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller = AnimationController(
    vsync: this,
    duration: AppMotion.pulse,
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
      builder: (context, child) {
        final t = animation.value;
        return Transform.scale(
          scale: 1 + 0.35 * t,
          child: Opacity(opacity: 1 - 0.45 * t, child: child),
        );
      },
      child: Container(
        width: widget.size,
        height: widget.size,
        decoration: BoxDecoration(color: widget.color, shape: BoxShape.circle),
      ),
    );
  }
}
