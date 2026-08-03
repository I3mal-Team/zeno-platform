import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/components/screen_background.dart';
import '../../../../core/motion/motion.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_dimensions.dart';
import '../../../../core/styles/app_shadows.dart';
import '../../../../core/styles/app_text_styles.dart';
import 'widgets/dashed_shapes.dart';

class OnboardingView extends StatefulWidget {
  const OnboardingView({super.key});

  @override
  State<OnboardingView> createState() => _OnboardingViewState();
}

class _OnboardingViewState extends State<OnboardingView> {
  final _controller = PageController();
  int _index = 0;

  static const _titles = [
    'فرصتك القادمة قريبة منك',
    'كل الخدمات في مكان واحد',
    'تواصل وقدّم بأبسط الخطوات',
  ];

  static const _bodies = [
    'اكتشف وظائف تشغيلية وخدمية وموسمية بالقرب من موقعك، وقدّم في ثوانٍ معدودة.',
    'مطاعم، نظافة، نقل، فعاليات، مبيعات، أمن وصيانة — تصنيفات تناسب كل مهارة.',
    'سجّل برقم جوالك، قدّم على الوظيفة، وتواصل داخل التطبيق أو عبر واتساب.',
  ];

  bool get _isLast => _index == _titles.length - 1;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _next() {
    if (_isLast) return context.go(RoutesKeys.rolePicker);

    _controller.nextPage(
      duration: AppDimensions.durationBase,
      curve: AppMotion.easeOut,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: ScreenBackground(
        child: SafeArea(
          child: Column(
            children: [
              Align(
                alignment: AlignmentDirectional.centerStart,
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(24, 12, 24, 0),
                  child: AnimatedOpacity(
                    opacity: _isLast ? 0 : 1,
                    duration: AppDimensions.durationFast,
                    child: Pressable(
                      onTap: _isLast
                          ? null
                          : () => context.go(RoutesKeys.rolePicker),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: AppDimensions.space16,
                          vertical: AppDimensions.space8,
                        ),
                        decoration: BoxDecoration(
                          color: AppColors.charcoalSoft.withValues(alpha: .05),
                          borderRadius: BorderRadius.circular(
                            AppDimensions.space12,
                          ),
                        ),
                        child: Text(
                          'تخطّي',
                          style: AppTextStyles.caption.copyWith(
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                    ),
                  ),
                ),
              ),
              Expanded(
                child: PageView.builder(
                  controller: _controller,
                  itemCount: _titles.length,
                  onPageChanged: (index) => setState(() => _index = index),
                  itemBuilder: (context, index) => _SlideView(
                    index: index,
                    title: _titles[index],
                    body: _bodies[index],
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(30, 0, 30, 30),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        for (var i = 0; i < _titles.length; i++)
                          GestureDetector(
                            onTap: () => _controller.animateToPage(
                              i,
                              duration: AppDimensions.durationBase,
                              curve: AppMotion.easeOut,
                            ),
                            child: AnimatedContainer(
                              duration: AppDimensions.durationFast,
                              margin: const EdgeInsets.symmetric(horizontal: 3),
                              width: i == _index ? 22 : 7,
                              height: 7,
                              decoration: BoxDecoration(
                                color: i == _index
                                    ? AppColors.amber
                                    : AppColors.borderStrong,
                                borderRadius: BorderRadius.circular(4),
                              ),
                            ),
                          ),
                      ],
                    ),
                    _isLast
                        ? _StartButton(onTap: _next)
                        : _NextButton(onTap: _next),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// The amber pill shown on the last slide.
class _StartButton extends StatelessWidget {
  const _StartButton({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    return Pressable(
      onTap: onTap,
      child: Container(
        height: 56,
        padding: const EdgeInsets.symmetric(horizontal: AppDimensions.space26),
        decoration: BoxDecoration(
          color: AppColors.amber,
          borderRadius: BorderRadius.circular(AppDimensions.radiusButton),
          boxShadow: AppShadows.amberGlow,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('ابدأ الآن', style: AppTextStyles.button),
            const SizedBox(width: AppDimensions.space8),
            Transform.flip(
              flipX: isRtl,
              child: const Icon(
                Icons.arrow_forward_rounded,
                size: 22,
                color: AppColors.textStrong,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

/// The dark square arrow shown on the earlier slides.
class _NextButton extends StatelessWidget {
  const _NextButton({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final isRtl = Directionality.of(context) == TextDirection.rtl;

    return Pressable(
      onTap: onTap,
      child: Container(
        width: 56,
        height: 56,
        decoration: BoxDecoration(
          color: AppColors.charcoalSoft,
          borderRadius: BorderRadius.circular(AppDimensions.radiusButton),
          boxShadow: AppShadows.charcoalGlow,
        ),
        child: Transform.flip(
          flipX: isRtl,
          child: const Icon(
            Icons.arrow_forward_rounded,
            size: 26,
            color: AppColors.amber,
          ),
        ),
      ),
    );
  }
}

class _SlideView extends StatelessWidget {
  const _SlideView({required this.index, required this.title, required this.body});

  final int index;
  final String title;
  final String body;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 28),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          switch (index) {
            0 => const _LocationArt(),
            1 => const _GridArt(),
            _ => const _ConnectArt(),
          },
          const SizedBox(height: AppDimensions.space26),
          Text(
            title,
            textAlign: TextAlign.center,
            style: AppTextStyles.displayLg.copyWith(fontSize: 24),
          ),
          const SizedBox(height: AppDimensions.space10),
          Text(
            body,
            textAlign: TextAlign.center,
            style: AppTextStyles.bodyLg.copyWith(height: 1.7),
          ),
        ],
      ),
    );
  }
}

/// A soft-tinted rounded tile holding a category icon, floating in place.
class _FloatTile extends StatelessWidget {
  const _FloatTile({
    required this.size,
    required this.radius,
    required this.background,
    required this.icon,
    required this.iconColor,
    required this.iconSize,
    this.amplitude = 11,
    this.border,
    this.shadow,
  });

  final double size;
  final double radius;
  final Color background;
  final IconData icon;
  final Color iconColor;
  final double iconSize;
  final double amplitude;
  final Color? border;
  final List<BoxShadow>? shadow;

  @override
  Widget build(BuildContext context) {
    return Floating(
      amplitude: amplitude,
      child: Container(
        width: size,
        height: size,
        decoration: BoxDecoration(
          color: background,
          borderRadius: BorderRadius.circular(radius),
          border: border != null ? Border.all(color: border!) : null,
          boxShadow: shadow,
        ),
        child: Icon(icon, size: iconSize, color: iconColor),
      ),
    );
  }
}

/// Slide 1 — a location hero encircled by a slow dashed ring and category tiles.
class _LocationArt extends StatelessWidget {
  const _LocationArt();

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 280,
      height: 264,
      child: Stack(
        clipBehavior: Clip.none,
        alignment: Alignment.center,
        children: [
          const DashedRing(size: 248),
          Container(
            width: 190,
            height: 190,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: RadialGradient(
                colors: [
                  AppColors.amber.withValues(alpha: .16),
                  AppColors.amber.withValues(alpha: 0),
                ],
                stops: const [0, .64],
              ),
            ),
          ),
          Floating(
            child: Container(
              width: 144,
              height: 144,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: const RadialGradient(
                  center: Alignment(-.36, -.48),
                  colors: [Color(0xFF3E6B54), Color(0xFF284C3D)],
                ),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF284C3D).withValues(alpha: .5),
                    blurRadius: 50,
                    offset: const Offset(0, 26),
                    spreadRadius: -18,
                  ),
                ],
              ),
              child: const Icon(
                Icons.location_on_rounded,
                size: 68,
                color: Colors.white,
              ),
            ),
          ),
          Positioned(
            top: 4,
            right: 6,
            child: _FloatTile(
              size: 58,
              radius: 18,
              background: AppColors.surface,
              border: const Color(0xFFE5EAE6),
              shadow: AppShadows.card,
              icon: Icons.local_cafe_rounded,
              iconColor: const Color(0xFF2E4A3C),
              iconSize: 28,
              amplitude: 12,
            ),
          ),
          Positioned(
            bottom: 14,
            left: 2,
            child: _FloatTile(
              size: 54,
              radius: 17,
              background: AppColors.surface,
              border: const Color(0xFFE5EAE6),
              shadow: AppShadows.card,
              icon: Icons.local_shipping_rounded,
              iconColor: const Color(0xFF2E6E8A),
              iconSize: 27,
              amplitude: 9,
            ),
          ),
          Positioned(
            top: 46,
            left: 24,
            child: _FloatTile(
              size: 48,
              radius: 15,
              background: AppColors.charcoalSoft,
              icon: Icons.cleaning_services_rounded,
              iconColor: AppColors.amber,
              iconSize: 24,
              amplitude: 13,
            ),
          ),
        ],
      ),
    );
  }
}

/// Slide 2 — the six category tiles.
class _GridArt extends StatelessWidget {
  const _GridArt();

  static const _tiles = <(Color, IconData, Color)>[
    (Color(0xFFE4EDE8), Icons.local_cafe_rounded, Color(0xFF8A6D12)),
    (Color(0xFFE2EEF4), Icons.cleaning_services_rounded, Color(0xFF2E6E8A)),
    (Color(0xFFE6F0E1), Icons.local_shipping_rounded, Color(0xFF4F7A2E)),
    (Color(0xFFECE6F4), Icons.auto_awesome_rounded, Color(0xFF6A4E8A)),
    (Color(0xFFF8E3E1), Icons.shield_rounded, Color(0xFFB2453A)),
    (Color(0xFFE0EFEC), Icons.storefront_rounded, Color(0xFF2E7A6B)),
  ];

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 268,
      height: 264,
      child: Center(
        child: GridView.count(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisCount: 3,
          mainAxisSpacing: 13,
          crossAxisSpacing: 13,
          children: [
            for (final (index, tile) in _tiles.indexed)
              _FloatTile(
                size: 80,
                radius: 21,
                background: tile.$1,
                icon: tile.$2,
                iconColor: tile.$3,
                iconSize: 34,
                amplitude: index.isEven ? 11 : 9,
              ),
          ],
        ),
      ),
    );
  }
}

/// Slide 3 — the candidate and employer discs linked by a dashed connector.
class _ConnectArt extends StatelessWidget {
  const _ConnectArt();

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 290,
      height: 248,
      child: Stack(
        clipBehavior: Clip.none,
        alignment: Alignment.center,
        children: [
          const Positioned(
            top: 128,
            left: 34,
            right: 34,
            child: DashedLine(),
          ),
          Positioned(
            left: 8,
            top: 92,
            child: _BigDisc(
              gradient: const [Color(0xFF3E6B54), Color(0xFF284C3D)],
              glow: const Color(0xFF284C3D),
              icon: Icons.person_rounded,
              iconColor: Colors.white,
            ),
          ),
          Positioned(
            right: 8,
            top: 92,
            child: _BigDisc(
              gradient: const [Color(0xFF2E4A3C), Color(0xFF1E3A2E)],
              glow: AppColors.charcoalSoft,
              icon: Icons.work_rounded,
              iconColor: AppColors.amber,
              amplitude: 10,
            ),
          ),
          Positioned(
            top: 8,
            child: _FloatTile(
              size: 84,
              radius: 26,
              background: AppColors.surface,
              border: const Color(0xFFE5EAE6),
              shadow: AppShadows.card,
              icon: Icons.forum_rounded,
              iconColor: const Color(0xFF8A6D12),
              iconSize: 40,
            ),
          ),
          Positioned(
            bottom: 10,
            left: 60,
            child: _FloatTile(
              size: 44,
              radius: 14,
              background: const Color(0xFFE7F4EC),
              icon: Icons.check_circle_rounded,
              iconColor: const Color(0xFF1F8A4D),
              iconSize: 24,
              amplitude: 8,
            ),
          ),
          Positioned(
            bottom: 22,
            right: 58,
            child: _FloatTile(
              size: 44,
              radius: 14,
              background: const Color(0xFFF3ECD6),
              icon: Icons.call_rounded,
              iconColor: const Color(0xFF8A6D12),
              iconSize: 22,
              amplitude: 10,
            ),
          ),
        ],
      ),
    );
  }
}

class _BigDisc extends StatelessWidget {
  const _BigDisc({
    required this.gradient,
    required this.glow,
    required this.icon,
    required this.iconColor,
    this.amplitude = 13,
  });

  final List<Color> gradient;
  final Color glow;
  final IconData icon;
  final Color iconColor;
  final double amplitude;

  @override
  Widget build(BuildContext context) {
    return Floating(
      amplitude: amplitude,
      child: Container(
        width: 88,
        height: 88,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(27),
          gradient: RadialGradient(
            center: const Alignment(-.4, -.48),
            colors: gradient,
          ),
          boxShadow: [
            BoxShadow(
              color: glow.withValues(alpha: .6),
              blurRadius: 40,
              offset: const Offset(0, 22),
              spreadRadius: -16,
            ),
          ],
        ),
        child: Icon(icon, size: 42, color: iconColor),
      ),
    );
  }
}
