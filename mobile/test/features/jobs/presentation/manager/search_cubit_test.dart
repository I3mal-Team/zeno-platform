import 'package:bloc_test/bloc_test.dart';
import 'package:dartz/dartz.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:zeno/core/params/job_params.dart';
import 'package:zeno/features/jobs/data/models/job_model.dart';
import 'package:zeno/features/jobs/data/repos/jobs_repo.dart';
import 'package:zeno/features/jobs/presentation/manager/search_cubit/search_cubit.dart';

class MockJobsRepo extends Mock implements JobsRepo {}

const _job = JobModel(
  id: 'job-1',
  title: 'باريستا',
  slug: 'barista',
  salary: JobSalary(amount: 4500, formatted: '4,500', unit: 'شهرياً'),
  status: 'active',
  contactChannel: 'app',
  organizationName: 'كافيه ميلاج',
  city: 'الرياض',
);

void main() {
  late MockJobsRepo repo;

  setUpAll(() => registerFallbackValue(const JobFilters()));

  setUp(() {
    repo = MockJobsRepo();
    when(() => repo.browse(any())).thenAnswer((_) async => const Right([_job]));
  });

  blocTest<SearchCubit, SearchState>(
    'stays idle and calls nothing until something is typed',
    build: () => SearchCubit(repo),
    act: (cubit) => cubit.query('   '),
    expect: () => <SearchState>[const SearchIdle()],
    verify: (_) => verifyNever(() => repo.browse(any())),
  );

  blocTest<SearchCubit, SearchState>(
    'searches once after the typing settles rather than per keystroke',
    build: () => SearchCubit(repo),
    act: (cubit) {
      cubit
        ..query('ب')
        ..query('با')
        ..query('باريستا');
    },
    wait: const Duration(milliseconds: 500),
    expect: () => <SearchState>[
      const SearchLoading(),
      const SearchLoaded([_job]),
    ],
    verify: (_) => verify(() => repo.browse(any())).called(1),
  );

  blocTest<SearchCubit, SearchState>(
    'reports an empty result apart from a failure',
    build: () {
      when(() => repo.browse(any())).thenAnswer((_) async => const Right([]));

      return SearchCubit(repo);
    },
    act: (cubit) => cubit.applyFilters(const JobFilters(workTypeCode: 'hourly')),
    expect: () => <SearchState>[const SearchLoading(), const SearchEmpty()],
  );

  test('applying a filter sends it on the query', () async {
    final cubit = SearchCubit(repo);

    await cubit.applyFilters(
      const JobFilters(genderCode: 'women', nationalityCode: 'saudi'),
    );

    final sent = verify(() => repo.browse(captureAny())).captured.single;

    expect((sent as JobFilters).toQuery(), containsPair('gender', 'women'));
    expect(sent.toQuery(), containsPair('nationality', 'saudi'));
    // Two dimensions on, so the button badge reads 2.
    expect(sent.activeCount, 2);
  });

  test('clearing drops the advanced filters but keeps the query', () {
    const filters = JobFilters(
      query: 'باريستا',
      categoryCode: 'hospitality',
      workTypeCode: 'hourly',
      genderCode: 'women',
    );

    final cleared = filters.cleared();

    expect(cleared.query, 'باريستا');
    expect(cleared.categoryCode, 'hospitality');
    expect(cleared.workTypeCode, isNull);
    expect(cleared.genderCode, isNull);
    expect(cleared.activeCount, 0);
  });
}
