import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../data/models/job_alert_model.dart';
import '../../../data/repos/jobs_repo.dart';

part 'job_alerts_state.dart';

class JobAlertsCubit extends Cubit<JobAlertsState> {
  JobAlertsCubit(this._repo) : super(const JobAlertsLoading());

  final JobsRepo _repo;

  Future<void> load() async {
    safeEmit(const JobAlertsLoading());

    final result = await _repo.listAlerts();

    safeEmit(result.fold(JobAlertsFailed.new, JobAlertsLoaded.new));
  }

  Future<void> delete(int id) async {
    final result = await _repo.deleteAlert(id);

    await result.fold(
      (failure) async => safeEmit(JobAlertsFailed(failure)),
      (_) => load(),
    );
  }
}
