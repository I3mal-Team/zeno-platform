import '../../errors/error_codes.dart';

/// Mirrors the server's response envelope. Parsed once here so no feature ever
/// reaches into the raw shape.
final class ApiEnvelope {
  const ApiEnvelope({
    required this.success,
    this.message,
    this.data,
    this.meta,
    this.errorCode,
    this.traceId,
    this.details,
  });

  factory ApiEnvelope.fromJson(Map<String, dynamic> json) {
    final error = json['error'] as Map<String, dynamic>?;

    return ApiEnvelope(
      success: json['success'] as bool? ?? false,
      message: json['message'] as String?,
      data: json['data'],
      meta: json['meta'] as Map<String, dynamic>?,
      errorCode: error?['code'] as String? ?? ErrorCodes.unknown,
      traceId: error?['trace_id'] as String?,
      details: _asDetails(error?['details']),
    );
  }

  final bool success;
  final String? message;
  final dynamic data;
  final Map<String, dynamic>? meta;
  final String? errorCode;
  final String? traceId;
  final Map<String, dynamic>? details;

  /// The server sends `{}` for an empty details object, which decodes to a map,
  /// but a list would arrive if it were ever empty-array encoded.
  static Map<String, dynamic>? _asDetails(dynamic raw) =>
      raw is Map<String, dynamic> && raw.isNotEmpty ? raw : null;
}
