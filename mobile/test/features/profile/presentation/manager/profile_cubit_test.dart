import 'package:bloc_test/bloc_test.dart';
import 'package:dartz/dartz.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:zeno/core/errors/error_codes.dart';
import 'package:zeno/core/errors/failure.dart';
import 'package:zeno/core/params/profile_params.dart';
import 'package:zeno/features/profile/data/models/candidate_profile_model.dart';
import 'package:zeno/features/profile/data/repos/profile_repo.dart';
import 'package:zeno/features/profile/presentation/manager/profile_cubit/profile_cubit.dart';

class MockProfileRepo extends Mock implements ProfileRepo {}

const _profile = CandidateProfileModel(
  fullName: 'سعود الحربي',
  completionPercentage: 80,
  jobTitle: 'باريستا',
);

void main() {
  late MockProfileRepo repo;

  setUpAll(
    () => registerFallbackValue(const SaveCandidateProfileParam(fullName: '')),
  );

  setUp(() {
    repo = MockProfileRepo();
    when(() => repo.fetchCities()).thenAnswer((_) async => const Right([]));
  });

  blocTest<ProfileCubit, ProfileState>(
    'treats a missing profile as empty rather than an error',
    build: () {
      when(() => repo.fetchCandidateProfile()).thenAnswer(
        (_) async => const Left(
          ServerFailure(message: 'غير موجود', code: ErrorCodes.notFound),
        ),
      );
      return ProfileCubit(repo);
    },
    act: (cubit) => cubit.load(),
    expect: () => [const ProfileLoading(), const ProfileEmpty()],
  );

  blocTest<ProfileCubit, ProfileState>(
    'surfaces a real load failure',
    build: () {
      when(() => repo.fetchCandidateProfile()).thenAnswer(
        (_) async => const Left(NetworkFailure(message: 'لا يوجد اتصال')),
      );
      return ProfileCubit(repo);
    },
    act: (cubit) => cubit.load(),
    expect: () => [const ProfileLoading(), isA<ProfileFailed>()],
  );

  blocTest<ProfileCubit, ProfileState>(
    'emits the loaded profile',
    build: () {
      when(
        () => repo.fetchCandidateProfile(),
      ).thenAnswer((_) async => const Right(_profile));
      return ProfileCubit(repo);
    },
    act: (cubit) => cubit.load(),
    expect: () => [const ProfileLoading(), const ProfileLoaded(_profile)],
  );

  blocTest<ProfileCubit, ProfileState>(
    'emits saving then saved',
    build: () {
      when(
        () => repo.saveCandidateProfile(any()),
      ).thenAnswer((_) async => const Right(_profile));
      return ProfileCubit(repo);
    },
    act: (cubit) =>
        cubit.save(const SaveCandidateProfileParam(fullName: 'سعود الحربي')),
    expect: () => [const ProfileSaving(), const ProfileSaved(_profile)],
  );

  test('exposes field errors from a validation failure', () {
    const state = ProfileFailed(
      ValidationFailure(
        message: 'تحقّق من البيانات',
        details: {
          'full_name': ['أدخل الاسم الكامل'],
        },
      ),
    );

    expect(state.fieldErrors?['full_name'], isNotNull);
  });

  test('reports completeness from the percentage', () {
    expect(_profile.isComplete, isFalse);
    expect(
      const CandidateProfileModel(
        fullName: 'x',
        completionPercentage: 100,
      ).isComplete,
      isTrue,
    );
  });
}
