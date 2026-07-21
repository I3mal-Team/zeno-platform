import 'package:flutter/material.dart';

import 'core/styles/app_colors.dart';
import 'core/styles/app_text_styles.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const ZenoApp());
}

class ZenoApp extends StatelessWidget {
  const ZenoApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'zeno',
      debugShowCheckedModeBanner: false,
      locale: const Locale('ar'),
      supportedLocales: const [Locale('ar')],
      theme: ThemeData(
        fontFamily: AppTextStyles.fontFamily,
        scaffoldBackgroundColor: AppColors.paper,
        colorScheme: ColorScheme.fromSeed(
          seedColor: AppColors.amber,
          primary: AppColors.amber,
          surface: AppColors.surface,
        ),
        useMaterial3: true,
      ),
      builder: (context, child) => Directionality(
        textDirection: TextDirection.rtl,
        child: child!,
      ),
      home: const _Placeholder(),
    );
  }
}

class _Placeholder extends StatelessWidget {
  const _Placeholder();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(child: Text('zeno', style: AppTextStyles.displayLg)),
    );
  }
}
