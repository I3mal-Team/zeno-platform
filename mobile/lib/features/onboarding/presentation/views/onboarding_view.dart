import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/components/app_button.dart';
import '../../../../core/components/screen_background.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_dimensions.dart';
import '../../../../core/styles/app_text_styles.dart';

typedef _Slide = ({
  IconData icon,
  List<IconData> orbiting,
  String title,
  String body,
});

class OnboardingView extends StatefulWidget {
  const OnboardingView({super.key});

  @override
  State<OnboardingView> createState() => _OnboardingViewState();
}

class _OnboardingViewState extends State<OnboardingView> {
  final _controller = PageController();
  int _index = 0;

  static const _slides = <_Slide>[
    (
      icon: Icons.location_on_rounded,
      orbiting: [Icons.local_cafe_rounded, Icons.local_shipping_rounded],
      title: 'فرصتك القادمة قريبة منك',
      body:
          'اكتشف وظائف تشغيلية وخدمية وموسمية بالقرب من موقعك، وقدّم في ثوانٍ معدودة.',
    ),
    (
      icon: Icons.grid_view_rounded,
      orbiting: [Icons.storefront_rounded, Icons.cleaning_services_rounded],
      title: 'كل الخدمات في مكان واحد',
      body:
          'مطاعم، نظافة، نقل، فعاليات، مبيعات، أمن وصيانة — تصنيفات تناسب كل مهارة.',
    ),
    (
      icon: Icons.forum_rounded,
      orbiting: [Icons.phone_iphone_rounded, Icons.check_circle_rounded],
      title: 'تواصل وقدّم بأبسط الخطوات',
      body:
          'سجّل برقم جوالك، قدّم على الوظيفة، وتواصل داخل التطبيق أو عبر واتساب.',
    ),
  ];

  bool get _isLast => _index == _slides.length - 1;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _next() {
    if (_isLast) return context.go(RoutesKeys.rolePicker);

    _controller.nextPage(
      duration: AppDimensions.durationBase,
      curve: Curves.easeOut,
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
                    child: TextButton(
                      onPressed: _isLast
                          ? null
                          : () => context.go(RoutesKeys.rolePicker),
                      style: TextButton.styleFrom(
                        backgroundColor: AppColors.charcoalSoft.withValues(
                          alpha: .05,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(
                            AppDimensions.space12,
                          ),
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
              Expanded(
                child: PageView.builder(
                  controller: _controller,
                  itemCount: _slides.length,
                  onPageChanged: (index) => setState(() => _index = index),
                  itemBuilder: (context, index) =>
                      _SlideView(slide: _slides[index]),
                ),
              ),
              Padding(
                padding: const EdgeInsets.fromLTRB(28, 0, 28, 30),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        for (var i = 0; i < _slides.length; i++)
                          GestureDetector(
                            onTap: () => _controller.animateToPage(
                              i,
                              duration: AppDimensions.durationBase,
                              curve: Curves.easeOut,
                            ),
                            child: AnimatedContainer(
                              duration: AppDimensions.durationFast,
                              margin: const EdgeInsets.symmetric(horizontal: 3),
                              width: i == _index ? 22 : 8,
                              height: 8,
                              decoration: BoxDecoration(
                                color: i == _index
                                    ? AppColors.amber
                                    : AppColors.border,
                                borderRadius: BorderRadius.circular(
                                  AppDimensions.radiusPill,
                                ),
                              ),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: AppDimensions.space26),
                    AppButton(
                      label: _isLast ? 'ابدأ الآن' : 'التالي',
                      onPressed: _next,
                    ),
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

class _SlideView extends StatelessWidget {
  const _SlideView({required this.slide});

  final _Slide slide;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 28),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          _SlideArt(slide: slide),
          const SizedBox(height: AppDimensions.space26),
          Text(
            slide.title,
            textAlign: TextAlign.center,
            style: AppTextStyles.displayLg.copyWith(fontSize: 24),
          ),
          const SizedBox(height: AppDimensions.space10),
          Text(
            slide.body,
            textAlign: TextAlign.center,
            style: AppTextStyles.bodyLg.copyWith(height: 1.7),
          ),
        ],
      ),
    );
  }
}

class _SlideArt extends StatelessWidget {
  const _SlideArt({required this.slide});

  final _Slide slide;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: 280,
      height: 264,
      child: Stack(
        alignment: Alignment.center,
        children: [
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
          Container(
            width: 144,
            height: 144,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: const RadialGradient(
                center: Alignment(-.36, -.48),
                colors: [Color(0xFFFBD46B), Color(0xFFF2A50E)],
              ),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFFF2A50E).withValues(alpha: .5),
                  blurRadius: 50,
                  offset: const Offset(0, 26),
                  spreadRadius: -18,
                ),
              ],
            ),
            child: Icon(slide.icon, size: 64, color: Colors.white),
          ),
          Positioned(
            top: 4,
            right: 6,
            child: _FloatingTile(icon: slide.orbiting.first),
          ),
          Positioned(
            bottom: 14,
            left: 2,
            child: _FloatingTile(icon: slide.orbiting.last),
          ),
        ],
      ),
    );
  }
}

class _FloatingTile extends StatelessWidget {
  const _FloatingTile({required this.icon});

  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 58,
      height: 58,
      decoration: BoxDecoration(
        color: AppColors.surface,
        border: Border.all(color: const Color(0xFFF0EDE6)),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF282319).withValues(alpha: .2),
            blurRadius: 28,
            offset: const Offset(0, 16),
            spreadRadius: -14,
          ),
        ],
      ),
      child: Icon(icon, size: 28, color: const Color(0xFF8A6D2E)),
    );
  }
}
