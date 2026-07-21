import 'package:flutter/material.dart';

/// المصدر الوحيد لكل لون في التطبيق.
///
/// القيم منقولة حرفياً من `design/HANDOVER.md §4.1` — وهي **مؤكدة** ومشتقة من
/// شعار المنتج. لا تُضاف قيمة هنا بلا مرجع في التسليم.
///
/// ممنوع `Color(0xFF...)` في أي مكان خارج هذا الملف (`CLAUDE.md §3`).
abstract final class AppColors {
  // ── العلامة ──────────────────────────────────────
  /// اللون الأساسي — أزرار الإجراء والحالات النشطة والإبرازات.
  static const amber = Color(0xFFF7BE17);

  /// شريك التدرّج مع [amber].
  static const amberDeep = Color(0xFFF2A50E);

  /// تدرّج العلامة — `linear-gradient(120deg, #F7BE17, #F2A50E)`.
  static const amberGradient = LinearGradient(
    begin: Alignment.topRight,
    end: Alignment.bottomLeft,
    colors: [amber, amberDeep],
  );

  // ── الفحمي ───────────────────────────────────────
  /// الرؤوس والتنقّل السفلي والأسطح الداكنة والنص القوي.
  static const charcoal = Color(0xFF211F20);
  static const charcoalSoft = Color(0xFF2B2724);

  /// تدرّج الرؤوس الداكنة — `#34302B → #26221F`.
  static const charcoalGradient = LinearGradient(
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
    colors: [Color(0xFF34302B), Color(0xFF26221F)],
  );

  // ── الأسطح ───────────────────────────────────────
  /// خلفية شاشات التطبيق.
  static const paper = Color(0xFFF6F5F1);

  /// أسطح البطاقات والحقول.
  static const surface = Color(0xFFFFFFFF);

  // ── الحدود ───────────────────────────────────────
  static const border = Color(0xFFEFEDE6);
  static const borderStrong = Color(0xFFE7E3DA);

  // ── النص ─────────────────────────────────────────
  /// العناوين والمحتوى الأساسي.
  static const textStrong = Color(0xFF211F20);

  /// نص الفقرات.
  static const textBody = Color(0xFF5A554C);

  /// البيانات الوصفية والمحتوى الثانوي.
  static const textMuted = Color(0xFF8A857A);

  /// نص على سطح داكن.
  static const textOnDark = Color(0xFFFFFFFF);
  static const textOnDarkMuted = Color(0xFFC7C2B8);

  // ── الحالات الدلالية ─────────────────────────────
  // كل حالة زوج: مقدمة + خلفية. راجع HANDOVER §4.1.
  static const successFg = Color(0xFF1F7A3D);
  static const successBg = Color(0xFFE3F3E8);

  static const warningFg = Color(0xFF8A6D12);
  static const warningBg = Color(0xFFFDF3D6);

  static const errorFg = Color(0xFFB23232);
  static const errorBg = Color(0xFFFBE6E6);

  static const infoFg = Color(0xFF2E6E8A);
  static const infoBg = Color(0xFFE2EEF4);

  /// حالة محايدة — تُستخدم لـ `submitted` في متتبّع الطلب.
  static const neutralFg = Color(0xFF5B6470);
  static const neutralBg = Color(0xFFEEF1F4);

  // ── قنوات وشارات ─────────────────────────────────
  static const whatsapp = Color(0xFF25D366);
  static const verified = Color(0xFF2E86C1);

  // ── ألوان التصنيفات ──────────────────────────────
  /// أزواج (مقدمة، خلفية) لأيقونات التصنيفات.
  /// المفتاح هو `code` في جدول `categories` — راجع
  /// `docs/10-domain-model-and-database-schema.md §3.1`.
  static const categoryTints = <String, (Color, Color)>{
    'restaurants': (Color(0xFF8A6D12), Color(0xFFFDF1CC)),
    'cleaning': (Color(0xFF4F7A2E), Color(0xFFE6F0E1)),
    'logistics': (Color(0xFFB2453A), Color(0xFFF8E3E1)),
    'events': (Color(0xFF6A4E8A), Color(0xFFECE6F4)),
    'retail': (Color(0xFF2E6E8A), Color(0xFFE2EEF4)),
    'security': (Color(0xFF2E7A6B), Color(0xFFE0EFEC)),
    'operations': (Color(0xFF2E5A8A), Color(0xFFE2EAF4)),
    'crafts': (Color(0xFF8A4E2E), Color(0xFFF4E8E2)),
    'seasonal_work': (Color(0xFF2E7A8A), Color(0xFFE0EDF0)),
    'other': (Color(0xFF5A554C), Color(0xFFEDEAE3)),
  };

  /// زوج ألوان التصنيف، مع تراجع آمن لتصنيف غير معروف.
  static (Color, Color) categoryTint(String code) =>
      categoryTints[code] ?? categoryTints['other']!;
}
