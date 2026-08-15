import 'package:bloc_test/bloc_test.dart';
import 'package:dartz/dartz.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:zeno/core/errors/failure.dart';
import 'package:zeno/core/managers/user_cubit/user_cubit.dart';
import 'package:zeno/core/utils/secure_storage_manager.dart';
import 'package:zeno/features/auth/data/models/user_model.dart';
import 'package:zeno/features/auth/data/repos/auth_repo.dart';

class MockAuthRepo extends Mock implements AuthRepo {}

class MockSecureStorage extends Mock implements SecureStorageManager {}

const _user = UserModel(
  id: 'u-1',
  phone: '0512345678',
  role: 'candidate',
  status: 'active',
  phoneVerified: true,
);

void main() {
  late MockAuthRepo repo;
  late MockSecureStorage storage;

  setUp(() {
    repo = MockAuthRepo();
    storage = MockSecureStorage();
  });

  blocTest<UserCubit, UserState>(
    'signs the user out once the account is deleted',
    build: () {
      when(
        () => repo.deleteAccount(),
      ).thenAnswer((_) async => const Right(unit));
      return UserCubit(repo, storage);
    },
    seed: () => const UserSignedIn(_user),
    act: (cubit) => cubit.deleteAccount(),
    expect: () => [const UserSignedOut()],
  );

  blocTest<UserCubit, UserState>(
    'keeps the user signed in when the deletion fails',
    // Dropping the session on a failed delete would strand someone on the
    // sign-in screen with an account that still exists.
    build: () {
      when(() => repo.deleteAccount()).thenAnswer(
        (_) async => const Left(NetworkFailure(message: 'لا يوجد اتصال.')),
      );
      return UserCubit(repo, storage);
    },
    seed: () => const UserSignedIn(_user),
    act: (cubit) => cubit.deleteAccount(),
    expect: () => <UserState>[],
  );

  test('hands the failure back so the sheet can show it', () async {
    when(() => repo.deleteAccount()).thenAnswer(
      (_) async => const Left(NetworkFailure(message: 'لا يوجد اتصال.')),
    );

    final failure = await UserCubit(repo, storage).deleteAccount();

    expect(failure?.message, 'لا يوجد اتصال.');
  });

  test('reports nothing to show on a successful deletion', () async {
    when(() => repo.deleteAccount()).thenAnswer((_) async => const Right(unit));

    final failure = await UserCubit(repo, storage).deleteAccount();

    expect(failure, isNull);
  });
}
