/// The gateway repositories depend on. Nothing outside the implementation may
/// reference the HTTP client directly.
abstract interface class ApiConsumer {
  Future<dynamic> get(String path, {Map<String, dynamic>? queryParameters});

  Future<dynamic> post(
    String path, {
    Object? body,
    Map<String, dynamic>? queryParameters,
  });

  Future<dynamic> patch(String path, {Object? body});

  Future<dynamic> delete(String path, {Object? body});
}
