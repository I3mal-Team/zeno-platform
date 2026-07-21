import 'package:dartz/dartz.dart';

import '../../../../core/errors/failure.dart';
import '../../../../core/params/auth_params.dart';
import '../models/auth_session_model.dart';
import '../models/user_model.dart';

abstract interface class AuthRepo {
  /// Returns how long the code stays valid, so the UI can count down.
  Future<Either<Failure, int>> requestOtp(RequestOtpParam param);

  Future<Either<Failure, AuthSessionModel>> verifyOtp(VerifyOtpParam param);

  Future<Either<Failure, UserModel>> currentUser();

  Future<Either<Failure, Unit>> logout();

  Future<Either<Failure, Unit>> logoutEverywhere();
}
