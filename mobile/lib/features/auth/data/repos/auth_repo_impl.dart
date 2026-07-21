import 'package:dartz/dartz.dart';

import '../../../../core/databases/api/api_consumer.dart';
import '../../../../core/databases/api/end_points.dart';
import '../../../../core/databases/api/handle_request.dart';
import '../../../../core/errors/failure.dart';
import '../../../../core/params/auth_params.dart';
import '../../../../core/utils/secure_storage_manager.dart';
import '../models/auth_session_model.dart';
import '../models/user_model.dart';
import 'auth_repo.dart';

class AuthRepoImpl implements AuthRepo {
  const AuthRepoImpl(this._api, this._handle, this._storage);

  final ApiConsumer _api;
  final RequestHandler _handle;
  final SecureStorageManager _storage;

  @override
  Future<Either<Failure, int>> requestOtp(RequestOtpParam param) {
    return _handle(
      () => _api.post(EndPoints.requestOtp, body: param.toJson()),
      (data) => (data as Map<String, dynamic>)['expires_in_seconds'] as int,
    );
  }

  @override
  Future<Either<Failure, AuthSessionModel>> verifyOtp(
    VerifyOtpParam param,
  ) async {
    final result = await _handle(
      () => _api.post(EndPoints.verifyOtp, body: param.toJson()),
      (data) => AuthSessionModel.fromJson(data as Map<String, dynamic>),
    );

    // Persisted before returning so the very next request already carries it.
    if (result case Right(value: final session)) {
      await _storage.saveTokens(accessToken: session.token);
    }

    return result;
  }

  @override
  Future<Either<Failure, UserModel>> currentUser() {
    return _handle(
      () => _api.get(EndPoints.currentUser),
      (data) => UserModel.fromJson(data as Map<String, dynamic>),
    );
  }

  @override
  Future<Either<Failure, Unit>> logout() async {
    final result = await _handle(
      () => _api.post(EndPoints.logout),
      (_) => unit,
    );

    await _storage.clear();

    return result;
  }

  @override
  Future<Either<Failure, Unit>> logoutEverywhere() async {
    final result = await _handle(
      () => _api.post(EndPoints.logoutAll),
      (_) => unit,
    );

    await _storage.clear();

    return result;
  }
}
