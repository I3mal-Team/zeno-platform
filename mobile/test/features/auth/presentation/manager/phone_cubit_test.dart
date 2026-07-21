import 'package:bloc_test/bloc_test.dart';
import 'package:dartz/dartz.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:zeno/core/errors/error_codes.dart';
import 'package:zeno/core/errors/failure.dart';
import 'package:zeno/core/params/auth_params.dart';
import 'package:zeno/features/auth/data/repos/auth_repo.dart';
import 'package:zeno/features/auth/presentation/manager/phone_cubit/phone_cubit.dart';

class MockAuthRepo extends Mock implements AuthRepo {}

void main() {
  late MockAuthRepo repo;

  setUpAll(() => registerFallbackValue(const RequestOtpParam(phone: '')));

  setUp(() => repo = MockAuthRepo());

  blocTest<PhoneCubit, PhoneState>(
    'emits submitting then code-sent on success',
    build: () {
      when(
        () => repo.requestOtp(any()),
      ).thenAnswer((_) async => const Right(300));
      return PhoneCubit(repo);
    },
    act: (cubit) {
      cubit.onPhoneChanged('0512345678');
      return cubit.requestCode();
    },
    expect: () => [
      const PhoneSubmitting(),
      const PhoneCodeSent(phone: '0512345678', expiresInSeconds: 300),
    ],
  );

  blocTest<PhoneCubit, PhoneState>(
    'emits the failure so the screen can show it',
    build: () {
      when(() => repo.requestOtp(any())).thenAnswer(
        (_) async => const Left(
          ServerFailure(
            message: 'انتظر قليلاً قبل طلب رمز جديد.',
            code: ErrorCodes.otpResendCooldown,
          ),
        ),
      );
      return PhoneCubit(repo);
    },
    act: (cubit) {
      cubit.onPhoneChanged('0512345678');
      return cubit.requestCode();
    },
    expect: () => [const PhoneSubmitting(), isA<PhoneError>()],
  );

  blocTest<PhoneCubit, PhoneState>(
    'does not call the api for a number that is too short',
    build: () => PhoneCubit(repo),
    act: (cubit) {
      cubit.onPhoneChanged('0512');
      return cubit.requestCode();
    },
    expect: () => <PhoneState>[],
    verify: (_) => verifyNever(() => repo.requestOtp(any())),
  );
}
