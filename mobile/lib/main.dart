import 'package:flutter/material.dart';

import 'generated/l10n/app_localizations.dart';

import 'core/routing/app_router.dart';
import 'core/services/service_locator.dart';
import 'core/styles/app_colors.dart';
import 'core/styles/app_text_styles.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await setupServiceLocator();
  runApp(const ZenoApp());
}

class ZenoApp extends StatelessWidget {
  const ZenoApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      title: 'zeno',
      debugShowCheckedModeBanner: false,
      routerConfig: AppRouter.router,
      locale: const Locale('ar'),
      supportedLocales: AppLocalizations.supportedLocales,
      localizationsDelegates: AppLocalizations.localizationsDelegates,
      theme: _theme,
      builder: (context, child) => Directionality(
        textDirection: TextDirection.rtl,
        child: child!,
      ),
    );
  }

  ThemeData get _theme => ThemeData(
        fontFamily: AppTextStyles.fontFamily,
        scaffoldBackgroundColor: AppColors.paper,
        colorScheme: ColorScheme.fromSeed(
          seedColor: AppColors.amber,
          primary: AppColors.amber,
          surface: AppColors.surface,
        ),
        appBarTheme: const AppBarTheme(
          backgroundColor: AppColors.surface,
          surfaceTintColor: Colors.transparent,
          elevation: 0,
        ),
        useMaterial3: true,
      );
}
