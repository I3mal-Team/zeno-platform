import 'package:dartz/dartz.dart';
import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../../jobs/data/models/job_detail_model.dart';
import '../../../../jobs/data/models/job_model.dart';
import '../../../data/repos/employer_repo.dart';

part 'employer_jobs_state.dart';

class EmployerJobsCubit extends Cubit<EmployerJobsState> {
  EmployerJobsCubit(this._repo) : super(const EmployerJobsLoading());

  final EmployerRepo _repo;

  Future<void> load() async {
    safeEmit(const EmployerJobsLoading());
    await _reload();
  }

  Future<Failure?> pause(String uuid) => _mutate(_repo.pauseJob(uuid));

  Future<Failure?> resume(String uuid) => _mutate(_repo.resumeJob(uuid));

  Future<Failure?> closeListing(String uuid) => _mutate(_repo.closeJob(uuid));

  /// Runs a status change, then refreshes the list in place so the badge
  /// updates without a full-screen spinner. Returns the failure for a snackbar.
  Future<Failure?> _mutate(
    Future<Either<Failure, JobDetailModel>> action,
  ) async {
    final result = await action;
    final failure = result.fold<Failure?>((f) => f, (_) => null);
    if (failure != null) return failure;

    await _reload();
    return null;
  }

  Future<void> _reload() async {
    final result = await _repo.listJobs();

    safeEmit(
      result.fold(
        EmployerJobsFailed.new,
        (jobs) =>
            jobs.isEmpty ? const EmployerJobsEmpty() : EmployerJobsLoaded(jobs),
      ),
    );
  }
}
