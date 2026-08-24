import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// The only place credentials are read or written. Tokens never touch
/// SharedPreferences or Hive.
class SecureStorageManager {
  const SecureStorageManager(this._storage);

  final FlutterSecureStorage _storage;

  static const _accessToken = 'access_token';
  static const _refreshToken = 'refresh_token';

  Future<String?> readAccessToken() => _storage.read(key: _accessToken);

  Future<String?> readRefreshToken() => _storage.read(key: _refreshToken);

  Future<void> saveTokens({
    required String accessToken,
    String? refreshToken,
  }) async {
    await _storage.write(key: _accessToken, value: accessToken);
    if (refreshToken != null) {
      await _storage.write(key: _refreshToken, value: refreshToken);
    }
  }

  Future<void> clear() => _storage.deleteAll();
}
