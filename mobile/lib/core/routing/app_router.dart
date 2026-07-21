import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import 'package:flutter_bloc/flutter_bloc.dart';

import '../../features/auth/presentation/manager/otp_cubit/otp_cubit.dart';
import '../../features/auth/presentation/manager/phone_cubit/phone_cubit.dart';
import '../../features/auth/presentation/views/otp_view.dart';
import '../../features/auth/presentation/views/phone_view.dart';
import '../../features/jobs/presentation/manager/browse_cubit/browse_cubit.dart';
import '../../features/jobs/presentation/manager/job_detail_cubit/job_detail_cubit.dart';
import '../../features/jobs/presentation/views/browse_view.dart';
import '../../features/jobs/presentation/views/job_detail_view.dart';
import '../../features/profile/presentation/manager/profile_cubit/profile_cubit.dart';
import '../../features/profile/presentation/views/profile_view.dart';
import '../../features/profile/presentation/views/register_candidate_view.dart';
import '../../features/onboarding/presentation/views/onboarding_view.dart';
import '../../features/onboarding/presentation/views/role_picker_view.dart';
import '../../features/onboarding/presentation/views/splash_view.dart';
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
      GoRoute(path: RoutesKeys.splash, builder: (_, _) => const SplashView()),
      GoRoute(
        path: RoutesKeys.onboarding,
        builder: (_, _) => const OnboardingView(),
      ),
      GoRoute(
        path: RoutesKeys.rolePicker,
        builder: (_, _) => const RolePickerView(),
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
        builder: (_, _) => BlocProvider(
          create: (_) => ProfileCubit(getIt()),
          child: const RegisterCandidateView(),
        ),
      ),
      GoRoute(
        path: RoutesKeys.registerEmployer,
        builder: (_, _) => const PlaceholderView(title: 'بيانات صاحب العمل'),
      ),

      StatefulShellRoute.indexedStack(
        builder: (_, _, shell) => CandidateShell(shell: shell),
        branches: [
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: RoutesKeys.browse,
                builder: (_, _) => BlocProvider(
                  create: (_) => BrowseCubit(getIt())..load(),
                  child: const BrowseView(),
                ),
              ),
            ],
          ),
          _branch(RoutesKeys.nearby, 'الوظائف القريبة مني'),
          _branch(RoutesKeys.applications, 'طلباتي'),
          _branch(RoutesKeys.messages, 'الرسائل'),
          StatefulShellBranch(
            routes: [
              GoRoute(
                path: RoutesKeys.profile,
                builder: (_, _) => BlocProvider(
                  create: (_) => ProfileCubit(getIt())..load(),
                  child: const ProfileView(),
                ),
              ),
            ],
          ),
        ],
      ),

      GoRoute(
        path: RoutesKeys.jobDetail,
        parentNavigatorKey: parentKey,
        builder: (_, state) => BlocProvider(
          create: (_) =>
              JobDetailCubit(getIt())..load(state.pathParameters['id'] ?? ''),
          child: const JobDetailView(),
        ),
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
