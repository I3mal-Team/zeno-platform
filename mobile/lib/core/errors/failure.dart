import 'package:equatable/equatable.dart';

import 'error_codes.dart';

/// Every failure path resolves to one of these. The UI branches on [code],
/// never on [message]: the message is server-owned copy and can change freely.
sealed class Failure extends Equatable {
  const Failure({
    required this.message,
    this.code = ErrorCodes.unknown,
    this.traceId,
    this.details,
  });

  /// Display-ready copy. Never a raw exception string.
  final String message;

  final String code;

  /// Matches the server log entry; surfaced in error screens for support.
  final String? traceId;

  final Map<String, dynamic>? details;

  @override
  List<Object?> get props => [message, code, traceId, details];
}

final class ServerFailure extends Failure {
  const ServerFailure({
    required super.message,
    super.code,
    super.traceId,
    super.details,
    this.statusCode,
  });

  final int? statusCode;

  @override
  List<Object?> get props => [...super.props, statusCode];
}

/// No response arrived: connection dropped or timed out.
final class NetworkFailure extends Failure {
  const NetworkFailure({required super.message})
    : super(code: ErrorCodes.networkUnavailable);
}

final class ValidationFailure extends Failure {
  const ValidationFailure({
    required super.message,
    super.details,
    super.traceId,
  }) : super(code: ErrorCodes.validationFailed);

  String? forField(String field) {
    final errors = details?[field];
    return errors is List && errors.isNotEmpty ? errors.first as String : null;
  }
}

final class CacheFailure extends Failure {
  const CacheFailure({required super.message})
    : super(code: ErrorCodes.cacheError);
}
