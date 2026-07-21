import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../data/models/job_detail_model.dart';
import '../../../data/repos/jobs_repo.dart';

part 'job_detail_state.dart';

class JobDetailCubit extends Cubit<JobDetailState> {
  JobDetailCubit(this._repo) : super(const JobDetailLoading());

  final JobsRepo _repo;

  Future<void> load(String slug) async {
    safeEmit(const JobDetailLoading());

    final result = await _repo.fetchJob(slug);

    safeEmit(result.fold(JobDetailFailed.new, JobDetailLoaded.new));
  }
}
