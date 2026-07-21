import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import 'package:flutter_bloc/flutter_bloc.dart';

import '../../features/auth/presentation/manager/otp_cubit/otp_cubit.dart';
import '../../features/auth/presentation/manager/phone_cubit/phone_cubit.dart';
import '../../features/auth/presentation/views/otp_view.dart';
import '../../features/auth/presentation/views/phone_view.dart';
import '../navigation_bar/candidate_shell.dart';
import '../services/service_locator.dart';
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
        builder: (_, state) {
          final role = state.uri.queryParameters['role'] ?? 'candidate';

          return BlocProvider(
            create: (_) => PhoneCubit(getIt()),
            child: PhoneView(role: role),
          );
        },
      ),
      GoRoute(
        path: RoutesKeys.otp,
        builder: (_, state) => BlocProvider(
          create: (_) => OtpCubit(
            getIt(),
            phone: state.uri.queryParameters['phone'] ?? '',
            role: state.uri.queryParameters['role'] ?? 'candidate',
          ),
          child: const OtpView(),
        ),
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
