import 'package:equatable/equatable.dart';

import 'error_codes.dart';

/// كل مسار فشل ينتهي إلى [Failure] مكتوب النوع.
///
/// **قاعدة ملزمة:** التفرّع في الواجهة يكون على [code] لا على [message].
/// الرسالة عربية قابلة للتغيير من الخادم؛ الكود ثابت وهو العقد الفعلي.
/// راجع `docs/18-architecture-and-stack.md §3.1`.
sealed class Failure extends Equatable {
  const Failure({
    required this.message,
    this.code = ErrorCodes.unknown,
    this.traceId,
    this.details,
  });

  /// رسالة جاهزة للعرض — من الخادم أو من الترجمة المحلية.
  /// **ممنوع** تمرير نص استثناء خام هنا (`CLAUDE.md §3`).
  final String message;

  /// كود ثابت بالإنجليزية. راجع [ErrorCodes].
  final String code;

  /// معرّف التتبّع — يطابق سجلات الخادم، يُعرض في شاشات الخطأ للدعم.
  final String? traceId;

  /// حقول إضافية، غالباً أخطاء التحقّق: `{"phone": ["..."]}`.
  final Map<String, dynamic>? details;

  @override
  List<Object?> get props => [message, code, traceId, details];
}

/// فشل من الخادم — استجابة وصلت بحالة غير ناجحة.
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

/// انقطاع الشبكة أو انتهاء المهلة — لم تصل استجابة.
final class NetworkFailure extends Failure {
  const NetworkFailure({required super.message})
      : super(code: ErrorCodes.networkUnavailable);
}

/// فشل تحقّق — [Failure.details] يحمل الأخطاء لكل حقل.
final class ValidationFailure extends Failure {
  const ValidationFailure({
    required super.message,
    super.details,
    super.traceId,
  }) : super(code: ErrorCodes.validationFailed);

  /// أول رسالة خطأ لحقل بعينه، أو `null` إن لم يكن به خطأ.
  String? forField(String field) {
    final errors = details?[field];
    return errors is List && errors.isNotEmpty ? errors.first as String : null;
  }
}

/// فشل في التخزين المحلي.
final class CacheFailure extends Failure {
  const CacheFailure({required super.message})
      : super(code: ErrorCodes.cacheError);
}
