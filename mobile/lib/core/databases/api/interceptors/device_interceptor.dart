import 'package:dio/dio.dart';

/// Device metadata travels as headers on every request so the server can gate
/// on app version without each feature passing it explicitly.
class DeviceInterceptor extends Interceptor {
  const DeviceInterceptor({
    required this.platform,
    required this.appVersion,
    required this.buildNumber,
  });

  final String platform;
  final String appVersion;
  final String buildNumber;

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    options.headers.addAll({
      'X-Platform': platform,
      'X-App-Version': appVersion,
      'X-Build-Number': buildNumber,
    });
    handler.next(options);
  }
}
