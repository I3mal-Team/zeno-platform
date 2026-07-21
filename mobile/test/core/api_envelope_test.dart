import 'package:flutter_test/flutter_test.dart';
import 'package:zeno/core/databases/api/api_envelope.dart';
import 'package:zeno/core/errors/error_codes.dart';

void main() {
  test('parses a success envelope', () {
    final envelope = ApiEnvelope.fromJson({
      'success': true,
      'message': 'تم',
      'data': [
        {'code': 'restaurants'},
      ],
    });

    expect(envelope.success, isTrue);
    expect(envelope.data, hasLength(1));
  });

  test('parses an error envelope and keeps the code', () {
    final envelope = ApiEnvelope.fromJson({
      'success': false,
      'message': 'لقد سبق لك التقديم على هذه الوظيفة',
      'error': {
        'code': ErrorCodes.applicationAlreadyExists,
        'trace_id': '01JG8Z',
        'details': {},
      },
    });

    expect(envelope.success, isFalse);
    expect(envelope.errorCode, ErrorCodes.applicationAlreadyExists);
    expect(envelope.traceId, '01JG8Z');
    expect(envelope.details, isNull);
  });

  test('falls back to the unknown code when the server omits one', () {
    final envelope = ApiEnvelope.fromJson({'success': false});

    expect(envelope.errorCode, ErrorCodes.unknown);
  });

  test('keeps validation details when present', () {
    final envelope = ApiEnvelope.fromJson({
      'success': false,
      'error': {
        'code': ErrorCodes.validationFailed,
        'details': {
          'phone': ['رقم غير صالح'],
        },
      },
    });

    expect(envelope.details?['phone'], isNotNull);
  });
}
