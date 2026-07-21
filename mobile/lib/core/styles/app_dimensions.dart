/// المصدر الوحيد للمسافات ونصف الأقطار والأحجام.
///
/// القيم من `design/HANDOVER.md §4.3–§4.4`.
/// ممنوع padding أو radius برقم حر في الميزات (`CLAUDE.md §3`).
abstract final class AppDimensions {
  // ── سلّم المسافات ────────────────────────────────
  /// الإيقاع الأساسي 4px. استخدم السلّم لا أرقاماً حرة.
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

  /// هامش جانبي لشاشات الموبايل (HANDOVER: 18–22px).
  static const screenPadding = 20.0;

  // ── نصف الأقطار ──────────────────────────────────
  /// رقائق وشارات صغيرة.
  static const radiusChip = 10.0;

  /// أزرار وحقول إدخال.
  static const radiusControl = 14.0;

  /// بطاقات.
  static const radiusCard = 20.0;

  /// أوراق سفلية ونوافذ.
  static const radiusSheet = 26.0;

  /// مربّعات الأيقونات.
  static const radiusIconTile = 14.0;

  /// حبوب مستديرة بالكامل.
  static const radiusPill = 100.0;

  // ── أحجام العناصر ────────────────────────────────
  static const buttonHeight = 52.0;
  static const inputHeight = 52.0;
  static const iconButtonSize = 42.0;
  static const iconTileSize = 52.0;
  static const avatarSize = 44.0;
  static const otpBoxSize = 60.0;

  /// أصغر مساحة لمس مقبولة — لا تُخالَف (`CLAUDE.md §13`).
  static const minTouchTarget = 48.0;

  // ── الأيقونات ────────────────────────────────────
  static const iconSm = 16.0;
  static const iconMd = 20.0;
  static const iconLg = 24.0;
  static const iconXl = 30.0;

  // ── سمك الحدود ───────────────────────────────────
  static const borderWidth = 1.0;
  static const borderWidthStrong = 1.5;

  // ── الحركة ───────────────────────────────────────
  // HANDOVER §4.7 — دخول قائم على التحويل فقط، لا من opacity:0.
  static const durationFast = Duration(milliseconds: 200);
  static const durationBase = Duration(milliseconds: 280);
  static const durationSlow = Duration(milliseconds: 300);

  /// مدة بقاء التوست قبل الإخفاء التلقائي.
  static const toastDuration = Duration(milliseconds: 2200);

  /// إزاحة دخول الشاشة — `translateY(8–10px) → 0`.
  static const entranceOffset = 10.0;

  // ── نقاط التوقّف ─────────────────────────────────
  /// التطبيق هاتف-أولاً؛ تُستخدم للأجهزة اللوحية فقط.
  static const breakpointTablet = 600.0;
}
