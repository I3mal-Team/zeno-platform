import 'package:dio/dio.dart';

import '../../errors/error_codes.dart';
import '../../errors/exceptions.dart';
import 'api_consumer.dart';
import 'api_envelope.dart';
import 'end_points.dart';

class DioConsumer implements ApiConsumer {
  DioConsumer(this._dio) {
    _dio.options
      ..baseUrl = EndPoints.baseUrl
      ..connectTimeout = const Duration(seconds: 20)
      ..receiveTimeout = const Duration(seconds: 20)
      ..responseType = ResponseType.json
      // The envelope carries the outcome, so let every status through and
      // decide from `success` rather than from the HTTP code alone.
      ..validateStatus = (_) => true;
  }

  final Dio _dio;

  @override
  Future<dynamic> get(String path, {Map<String, dynamic>? queryParameters}) =>
      _send(() => _dio.get(path, queryParameters: queryParameters));

  @override
  Future<dynamic> post(
    String path, {
    Object? body,
    Map<String, dynamic>? queryParameters,
  }) => _send(
    () => _dio.post(path, data: body, queryParameters: queryParameters),
  );

  @override
  Future<dynamic> put(String path, {Object? body}) =>
      _send(() => _dio.put(path, data: body));

  @override
  Future<dynamic> patch(String path, {Object? body}) =>
      _send(() => _dio.patch(path, data: body));

  @override
  Future<dynamic> delete(String path, {Object? body}) =>
      _send(() => _dio.delete(path, data: body));

  Future<dynamic> _send(Future<Response<dynamic>> Function() request) async {
    late final Response<dynamic> response;

    try {
      response = await request();
    } on DioException catch (e) {
      throw NetworkException(
        message: _networkMessage(e.type),
        code: ErrorCodes.networkUnavailable,
      );
    }

    final body = response.data;

    if (body is! Map<String, dynamic>) {
      throw ServerException(
        message: _networkMessage(DioExceptionType.unknown),
        code: ErrorCodes.unknown,
        statusCode: response.statusCode,
      );
    }

    final envelope = ApiEnvelope.fromJson(body);

    if (envelope.success) return envelope.data;

    throw ServerException(
      message: envelope.message ?? '',
      code: envelope.errorCode ?? ErrorCodes.unknown,
      traceId: envelope.traceId,
      details: envelope.details,
      statusCode: response.statusCode,
    );
  }

  String _networkMessage(DioExceptionType type) => switch (type) {
    DioExceptionType.connectionTimeout ||
    DioExceptionType.sendTimeout ||
    DioExceptionType.receiveTimeout => 'انتهت مهلة الاتصال. حاول مرة أخرى.',
    DioExceptionType.connectionError =>
      'تعذّر الاتصال بالإنترنت. تحقّق من اتصالك.',
    _ => 'تعذّر إتمام الطلب. حاول مرة أخرى.',
  };
}
