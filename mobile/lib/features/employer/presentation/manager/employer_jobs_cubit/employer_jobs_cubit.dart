import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../../jobs/data/models/job_model.dart';
import '../../../data/repos/employer_repo.dart';

part 'employer_jobs_state.dart';

class EmployerJobsCubit extends Cubit<EmployerJobsState> {
  EmployerJobsCubit(this._repo) : super(const EmployerJobsLoading());

  final EmployerRepo _repo;

  Future<void> load() async {
    safeEmit(const EmployerJobsLoading());

    final result = await _repo.listJobs();

    safeEmit(
      result.fold(EmployerJobsFailed.new, (jobs) {
        return jobs.isEmpty
            ? const EmployerJobsEmpty()
            : EmployerJobsLoaded(jobs);
      }),
    );
  }
}
