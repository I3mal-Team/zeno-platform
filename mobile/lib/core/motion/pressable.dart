import 'package:flutter/widgets.dart';

import 'app_motion.dart';

/// Mirrors the design's `button:active { transform: scale(.96) }`: the child
/// dips while pressed and springs back on release. Use it for any tappable
/// surface — cards, chips, icon buttons — so touch feedback is consistent.
class Pressable extends StatefulWidget {
  const Pressable({
    required this.child,
    required this.onTap,
    this.onLongPress,
    this.scale = 0.96,
    super.key,
  });

  final Widget child;
  final VoidCallback? onTap;
  final VoidCallback? onLongPress;

  /// How far the surface dips while held.
  final double scale;

  @override
  State<Pressable> createState() => _PressableState();
}

class _PressableState extends State<Pressable> {
  bool _held = false;

  bool get _enabled => widget.onTap != null || widget.onLongPress != null;

  void _setHeld(bool value) {
    if (_enabled && _held != value) setState(() => _held = value);
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTapDown: (_) => _setHeld(true),
      onTapUp: (_) => _setHeld(false),
      onTapCancel: () => _setHeld(false),
      onTap: widget.onTap,
      onLongPress: widget.onLongPress,
      behavior: HitTestBehavior.opaque,
      child: AnimatedScale(
        scale: _held ? widget.scale : 1,
        duration: AppMotion.press,
        curve: Curves.easeOut,
        child: widget.child,
      ),
    );
  }
}
