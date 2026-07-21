import 'package:device_info_plus/device_info_plus.dart';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:get_it/get_it.dart';
import 'package:package_info_plus/package_info_plus.dart';

import '../databases/api/api_consumer.dart';
import '../databases/api/dio_consumer.dart';
import '../databases/api/handle_request.dart';
import '../errors/error_codes.dart';
import '../databases/api/interceptors/auth_interceptor.dart';
import '../databases/api/interceptors/device_interceptor.dart';
import '../databases/api/interceptors/locale_interceptor.dart';
import '../managers/user_cubit/user_cubit.dart';
import '../utils/secure_storage_manager.dart';
import '../../features/auth/data/repos/auth_repo.dart';
import '../../features/auth/data/repos/auth_repo_impl.dart';

final getIt = GetIt.instance;

/// Awaited before `runApp`. Nothing may resolve from the container before this
/// completes.
Future<void> setupServiceLocator() async {
  await _registerPlatform();
  _registerNetworking();
  _registerFeatures();
}

Future<void> _registerPlatform() async {
  getIt.registerLazySingleton(() => const FlutterSecureStorage());
  getIt.registerLazySingleton(() => SecureStorageManager(getIt()));

  final packageInfo = await PackageInfo.fromPlatform();
  getIt.registerSingleton(packageInfo);
  getIt.registerLazySingleton(DeviceInfoPlugin.new);
}

void _registerNetworking() {
  final packageInfo = getIt<PackageInfo>();

  final dio = Dio();

  // Order is load-bearing: locale and device headers are set before auth so a
  // token refresh triggered downstream still carries them.
  dio.interceptors.addAll([
    LocaleInterceptor(() => 'ar'),
    DeviceInterceptor(
      platform: defaultTargetPlatform.name,
      appVersion: packageInfo.version,
      buildNumber: packageInfo.buildNumber,
    ),
    AuthInterceptor(getIt<SecureStorageManager>()),
  ]);

  getIt.registerSingleton(dio);
  getIt.registerLazySingleton<ApiConsumer>(() => DioConsumer(getIt()));

  // A lost session is handled once here rather than in every screen.
  getIt.registerLazySingleton(
    () => RequestHandler(
      onGlobalFailure: (failure) {
        if (failure.code == ErrorCodes.sessionExpired ||
            failure.code == ErrorCodes.accountSuspended) {
          getIt<UserCubit>().onSessionLost();
        }
      },
    ),
  );
}

void _registerFeatures() {
  getIt.registerLazySingleton<AuthRepo>(
    () => AuthRepoImpl(getIt(), getIt(), getIt()),
  );

  getIt.registerLazySingleton(() => UserCubit(getIt(), getIt()));
}
