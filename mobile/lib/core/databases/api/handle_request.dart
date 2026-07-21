import 'package:dartz/dartz.dart';

import '../../errors/error_codes.dart';
import '../../errors/exceptions.dart';
import '../../errors/failure.dart';

/// Reactions that must happen once, centrally, regardless of which screen made
/// the call: session expiry, forced update, maintenance, suspension.
typedef GlobalFailureHandler = void Function(Failure failure);

/// The standard way a repository calls the API. Bypassing it silently opts out
/// of every global reaction below.
class RequestHandler {
  const RequestHandler({GlobalFailureHandler? onGlobalFailure})
    : _onGlobalFailure = onGlobalFailure;

  final GlobalFailureHandler? _onGlobalFailure;

  Future<Either<Failure, T>> call<T>(
    Future<dynamic> Function() request,
    T Function(dynamic data) parse,
  ) async {
    try {
      return Right(parse(await request()));
    } on ApiException catch (e) {
      final failure = _toFailure(e);

      if (ErrorCodes.globalHandled.contains(failure.code)) {
        _onGlobalFailure?.call(failure);
      }

      return Left(failure);
    } catch (_) {
      return const Left(ServerFailure(message: 'حدث خطأ غير متوقع.'));
    }
  }

  Failure _toFailure(ApiException e) => switch (e) {
    NetworkException() => NetworkFailure(message: e.message),
    ServerException() when e.code == ErrorCodes.validationFailed =>
      ValidationFailure(
        message: e.message,
        details: e.details,
        traceId: e.traceId,
      ),
    ServerException() => ServerFailure(
      message: e.message,
      code: e.code,
      traceId: e.traceId,
      details: e.details,
      statusCode: e.statusCode,
    ),
  };
}
