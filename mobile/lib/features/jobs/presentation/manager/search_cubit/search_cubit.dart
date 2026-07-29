import 'dart:async';

import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../../../core/params/job_params.dart';
import '../../../data/models/job_model.dart';
import '../../../data/repos/jobs_repo.dart';

part 'search_state.dart';

/// Text search with the advanced-filter sheet on top. Separate from
/// [BrowseCubit] so leaving the search screen never disturbs the feed the
/// candidate scrolled back to.
class SearchCubit extends Cubit<SearchState> {
  SearchCubit(this._repo) : super(const SearchIdle());

  /// Long enough that a typist is not firing a request per keystroke, short
  /// enough that results feel live.
  static const _debounce = Duration(milliseconds: 350);

  final JobsRepo _repo;

  JobFilters _filters = const JobFilters();
  Timer? _timer;

  JobFilters get filters => _filters;

  void query(String value) {
    _filters = _filters.copyWith(query: value);

    _timer?.cancel();

    // An empty box returns to the resting state rather than listing everything.
    if (value.trim().isEmpty && _filters.activeCount == 0) {
      safeEmit(const SearchIdle());

      return;
    }

    _timer = Timer(_debounce, _fetch);
  }

  Future<void> applyFilters(JobFilters filters) async {
    _timer?.cancel();
    _filters = filters;
    await _fetch();
  }

  Future<void> retry() => _fetch();

  Future<void> _fetch() async {
    safeEmit(const SearchLoading());

    final result = await _repo.browse(_filters);

    safeEmit(
      result.fold(SearchFailed.new, (jobs) {
        return jobs.isEmpty ? const SearchEmpty() : SearchLoaded(jobs);
      }),
    );
  }

  @override
  Future<void> close() {
    _timer?.cancel();

    return super.close();
  }
}
