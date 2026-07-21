import 'package:dio/dio.dart';

class LocaleInterceptor extends Interceptor {
  const LocaleInterceptor(this._localeCode);

  final String Function() _localeCode;

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    options.headers['Accept-Language'] = _localeCode();
    options.headers['Accept'] = 'application/json';
    handler.next(options);
  }
}
