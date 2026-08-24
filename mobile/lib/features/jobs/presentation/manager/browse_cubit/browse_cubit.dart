import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../../../core/params/job_params.dart';
import '../../../data/models/job_model.dart';
import '../../../data/repos/jobs_repo.dart';

part 'browse_state.dart';

class BrowseCubit extends Cubit<BrowseState> {
  BrowseCubit(this._repo) : super(const BrowseLoading());

  final JobsRepo _repo;

  JobFilters _filters = const JobFilters();

  JobFilters get filters => _filters;

  Future<void> load([JobFilters? filters]) async {
    if (filters != null) _filters = filters;
    safeEmit(const BrowseLoading());
    await _fetch();
  }

  Future<void> search(String query) async {
    _filters = _filters.copyWith(query: query);
    await _fetch();
  }

  Future<void> selectCategory(String? code) async {
    _filters = code == null
        ? _filters.copyWith(clearCategory: true)
        : _filters.copyWith(categoryCode: code);
    safeEmit(const BrowseLoading());
    await _fetch();
  }

  Future<void> _fetch() async {
    final result = await _repo.browse(_filters);

    safeEmit(
      result.fold(BrowseFailed.new, (jobs) {
        return jobs.isEmpty ? const BrowseEmpty() : BrowseLoaded(jobs);
      }),
    );
  }
}
