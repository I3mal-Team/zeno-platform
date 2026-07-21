/// Thrown by the API layer and converted to a [Failure] before it reaches a
/// repository caller. These never cross the repository boundary.
sealed class ApiException implements Exception {
  const ApiException({
    required this.message,
    required this.code,
    this.traceId,
    this.details,
    this.statusCode,
  });

  final String message;
  final String code;
  final String? traceId;
  final Map<String, dynamic>? details;
  final int? statusCode;
}

final class ServerException extends ApiException {
  const ServerException({
    required super.message,
    required super.code,
    super.traceId,
    super.details,
    super.statusCode,
  });
}

final class NetworkException extends ApiException {
  const NetworkException({required super.message, required super.code});
}
