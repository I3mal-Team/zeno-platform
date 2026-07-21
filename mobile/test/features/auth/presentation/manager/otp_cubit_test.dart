import 'package:bloc_test/bloc_test.dart';
import 'package:dartz/dartz.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:zeno/core/errors/error_codes.dart';
import 'package:zeno/core/errors/failure.dart';
import 'package:zeno/core/params/auth_params.dart';
import 'package:zeno/features/auth/data/models/auth_session_model.dart';
import 'package:zeno/features/auth/data/models/user_model.dart';
import 'package:zeno/features/auth/data/repos/auth_repo.dart';
import 'package:zeno/features/auth/presentation/manager/otp_cubit/otp_cubit.dart';

class MockAuthRepo extends Mock implements AuthRepo {}

const _user = UserModel(
  id: 'uuid',
  phone: '0512345678',
  role: 'candidate',
  status: 'active',
  phoneVerified: true,
);

void main() {
  late MockAuthRepo repo;

  setUpAll(() {
    registerFallbackValue(
      const VerifyOtpParam(phone: '', code: '', role: 'candidate'),
    );
    registerFallbackValue(const RequestOtpParam(phone: ''));
  });

  setUp(() => repo = MockAuthRepo());

  OtpCubit build() => OtpCubit(repo, phone: '0512345678', role: 'candidate');

  blocTest<OtpCubit, OtpState>(
    'emits verified when the code is accepted',
    build: () {
      when(() => repo.verifyOtp(any())).thenAnswer(
        (_) async => const Right(
          AuthSessionModel(token: 't', user: _user, isNewUser: true),
        ),
      );
      return build();
    },
    act: (cubit) {
      cubit.onCodeChanged('4829');
      return cubit.verify();
    },
    expect: () => [const OtpVerifying(), isA<OtpVerified>()],
  );

  blocTest<OtpCubit, OtpState>(
    'emits an error when the code is wrong',
    build: () {
      when(() => repo.verifyOtp(any())).thenAnswer(
        (_) async => const Left(
          ServerFailure(
            message: 'رمز التحقق غير صحيح.',
            code: ErrorCodes.otpInvalid,
          ),
        ),
      );
      return build();
    },
    act: (cubit) {
      cubit.onCodeChanged('1111');
      return cubit.verify();
    },
    expect: () => [const OtpVerifying(), isA<OtpError>()],
  );

  blocTest<OtpCubit, OtpState>(
    'will not verify an incomplete code',
    build: build,
    act: (cubit) {
      cubit.onCodeChanged('48');
      return cubit.verify();
    },
    expect: () => <OtpState>[],
    verify: (_) => verifyNever(() => repo.verifyOtp(any())),
  );

  test('surfaces the cooldown the server reported', () {
    const state = OtpError(
      ServerFailure(
        message: 'انتظر قليلاً.',
        code: ErrorCodes.otpResendCooldown,
        details: {'retry_after_seconds': 42},
      ),
    );

    expect(state.retryAfterSeconds, 42);
  });
}
