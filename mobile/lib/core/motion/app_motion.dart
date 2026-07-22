import 'package:flutter/animation.dart';

/// The app's motion language, mirroring the design's easing and timing so every
/// screen animates with one consistent, professional feel.
///
/// The design uses transform-only keyframes (no opacity fades), so the widgets
/// built on these tokens translate and scale but never fade a screen in.
abstract final class AppMotion {
  /// cubic-bezier(.2,.75,.25,1) — the design's primary ease-out for entrances.
  static const easeOut = Cubic(0.2, 0.75, 0.25, 1);

  /// cubic-bezier(.2,.85,.3,1.3) — a gentle overshoot for pops and badges.
  static const overshoot = Cubic(0.2, 0.85, 0.3, 1.3);

  /// `.scr { animation: zfade .32s }` — the whole-screen slide-in.
  static const screenEnter = Duration(milliseconds: 320);

  /// `.enter { animation: cardIn .5s }` — a card rising into place.
  static const cardEnter = Duration(milliseconds: 500);

  /// `button:active { transform: scale(.96) }` — the press response.
  static const press = Duration(milliseconds: 140);

  /// The first staggered sibling's delay (design: `.enter:nth-child(1) .03s`).
  static const staggerBase = Duration(milliseconds: 30);

  /// The gap between staggered siblings (design steps by ~.06s per child).
  static const stagger = Duration(milliseconds: 60);

  /// `obDrift` — the slow float of hero icons.
  static const drift = Duration(milliseconds: 4000);

  /// `pulseDot` — the breathing accent dot.
  static const pulse = Duration(milliseconds: 1600);

  /// The screen slides in from this many logical pixels on the start edge.
  static const screenOffset = 24.0;

  /// A card rises this many pixels as it settles.
  static const cardOffset = 18.0;
}
