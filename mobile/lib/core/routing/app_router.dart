import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import '../navigation_bar/candidate_shell.dart';
import '../views/placeholder_view.dart';
import 'routes_keys.dart';

/// Exposed so navigation helpers and screens that must sit above the bottom
/// nav can reference the right navigator.
final parentKey = GlobalKey<NavigatorState>();
final shellKey = GlobalKey<NavigatorState>();

abstract final class AppRouter {
  static final router = GoRouter(
    navigatorKey: parentKey,
    initialLocation: RoutesKeys.splash,
    routes: [
      GoRoute(
        path: RoutesKeys.splash,
        builder: (_, _) => const PlaceholderView(title: 'zeno'),
      ),
      GoRoute(
        path: RoutesKeys.onboarding,
        builder: (_, _) => const PlaceholderView(title: 'التعريف بالتطبيق'),
      ),
      GoRoute(
        path: RoutesKeys.rolePicker,
        builder: (_, _) => const PlaceholderView(title: 'اختر طريقة الدخول'),
      ),
      GoRoute(
        path: RoutesKeys.phone,
        builder: (_, _) => const PlaceholderView(title: 'أدخل رقم جوالك'),
      ),
      GoRoute(
        path: RoutesKeys.otp,
        builder: (_, _) => const PlaceholderView(title: 'أدخل رمز التحقق'),
      ),
      GoRoute(
        path: RoutesKeys.registerCandidate,
        builder: (_, _) => const PlaceholderView(title: 'أكمل بياناتك'),
      ),
      GoRoute(
        path: RoutesKeys.registerEmployer,
        builder: (_, _) => const PlaceholderView(title: 'بيانات صاحب العمل'),
      ),

      StatefulShellRoute.indexedStack(
        builder: (_, _, shell) => CandidateShell(shell: shell),
        branches: [
          _branch(RoutesKeys.browse, 'أحدث الوظائف'),
          _branch(RoutesKeys.nearby, 'الوظائف القريبة مني'),
          _branch(RoutesKeys.applications, 'طلباتي'),
          _branch(RoutesKeys.messages, 'الرسائل'),
          _branch(RoutesKeys.profile, 'حسابي'),
        ],
      ),

      GoRoute(
        path: RoutesKeys.jobDetail,
        parentNavigatorKey: parentKey,
        builder: (_, state) =>
            PlaceholderView(title: 'وظيفة ${state.pathParameters['id']}'),
      ),
      GoRoute(
        path: RoutesKeys.notifications,
        parentNavigatorKey: parentKey,
        builder: (_, _) => const PlaceholderView(title: 'الإشعارات'),
      ),
    ],
  );

  static StatefulShellBranch _branch(String path, String title) {
    return StatefulShellBranch(
      routes: [
        GoRoute(
          path: path,
          builder: (_, _) => PlaceholderView(title: title),
        ),
      ],
    );
  }
}
