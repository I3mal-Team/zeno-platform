import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../data/models/job_model.dart';
import '../../../data/repos/jobs_repo.dart';

part 'saved_jobs_state.dart';

class SavedJobsCubit extends Cubit<SavedJobsState> {
  SavedJobsCubit(this._repo) : super(const SavedJobsLoading());

  final JobsRepo _repo;

  Future<void> load() async {
    safeEmit(const SavedJobsLoading());

    final result = await _repo.listSaved();

    safeEmit(result.fold(SavedJobsFailed.new, SavedJobsLoaded.new));
  }
}
