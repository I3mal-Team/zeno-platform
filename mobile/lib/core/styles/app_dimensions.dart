/// Spacing, radii and sizes. Features must not use raw numbers for layout.
abstract final class AppDimensions {
  static const space2 = 2.0;
  static const space4 = 4.0;
  static const space6 = 6.0;
  static const space8 = 8.0;
  static const space10 = 10.0;
  static const space12 = 12.0;
  static const space16 = 16.0;
  static const space20 = 20.0;
  static const space22 = 22.0;
  static const space26 = 26.0;
  static const space32 = 32.0;
  static const space40 = 40.0;

  static const screenPadding = 20.0;

  static const radiusChip = 10.0;
  static const radiusControl = 14.0;
  static const radiusCard = 20.0;
  static const radiusSheet = 26.0;
  static const radiusIconTile = 14.0;
  static const radiusPill = 100.0;

  static const buttonHeight = 54.0;
  static const inputHeight = 54.0;
  static const radiusButton = 18.0;
  static const iconButtonSize = 42.0;
  static const iconTileSize = 52.0;
  static const avatarSize = 44.0;
  static const otpBoxSize = 60.0;

  static const minTouchTarget = 48.0;

  static const iconSm = 16.0;
  static const iconMd = 20.0;
  static const iconLg = 24.0;
  static const iconXl = 30.0;

  static const borderWidth = 1.0;
  static const borderWidthStrong = 1.5;

  static const durationFast = Duration(milliseconds: 200);
  static const durationBase = Duration(milliseconds: 280);
  static const durationSlow = Duration(milliseconds: 300);
  static const toastDuration = Duration(milliseconds: 2200);

  /// Screen entrances translate only; they never animate from `opacity: 0`.
  static const entranceOffset = 10.0;

  static const breakpointTablet = 600.0;
}
