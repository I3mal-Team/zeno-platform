import 'package:flutter/widgets.dart';

import 'app_motion.dart';

/// Mirrors the design's `.scr` entrance: the screen body slides in from the
/// start edge once, with no opacity fade. Wrap a view's content in it so every
/// screen arrives with the same motion.
class ScreenEnter extends StatelessWidget {
  const ScreenEnter({required this.child, super.key});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    final sign = Directionality.of(context) == TextDirection.rtl ? 1.0 : -1.0;

    return TweenAnimationBuilder<double>(
      tween: Tween<double>(begin: 1, end: 0),
      duration: AppMotion.screenEnter,
      curve: AppMotion.easeOut,
      builder: (context, t, child) => Transform.translate(
        offset: Offset(AppMotion.screenOffset * t * sign, 0),
        child: child,
      ),
      child: child,
    );
  }
}
