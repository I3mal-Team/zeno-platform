import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../../employer/data/repos/employer_repo.dart';
import '../../../data/models/job_detail_model.dart';
import '../../../data/repos/jobs_repo.dart';

part 'job_detail_state.dart';

class JobDetailCubit extends Cubit<JobDetailState> {
  JobDetailCubit(this._repo, this._employerRepo)
    : super(const JobDetailLoading());

  final JobsRepo _repo;
  final EmployerRepo _employerRepo;

  /// The candidate view: the public listing by slug.
  Future<void> load(String slug) async {
    safeEmit(const JobDetailLoading());

    final result = await _repo.fetchJob(slug);

    safeEmit(result.fold(JobDetailFailed.new, JobDetailLoaded.new));
  }

  /// The employer view: their own listing by uuid, which works for any status
  /// (paused, pending) — unlike the public endpoint — and carries the edit ids.
  Future<void> loadOwned(String uuid) async {
    safeEmit(const JobDetailLoading());

    final result = await _employerRepo.fetchJob(uuid);

    safeEmit(result.fold(JobDetailFailed.new, JobDetailLoaded.new));
  }
}
