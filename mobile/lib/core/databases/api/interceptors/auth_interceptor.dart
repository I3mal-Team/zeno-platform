import 'package:dio/dio.dart';

import '../../../utils/secure_storage_manager.dart';

/// Resolves the token per request rather than setting it once on the client, so
/// a refresh or a logout takes effect immediately.
class AuthInterceptor extends Interceptor {
  const AuthInterceptor(this._storage);

  final SecureStorageManager _storage;

  @override
  Future<void> onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    final token = await _storage.readAccessToken();

    if (token != null) {
      options.headers['Authorization'] = 'Bearer $token';
    }

    handler.next(options);
  }
}
