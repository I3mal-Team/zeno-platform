part of 'employer_jobs_cubit.dart';

sealed class EmployerJobsState extends Equatable {
  const EmployerJobsState();

  @override
  List<Object?> get props => [];
}

final class EmployerJobsLoading extends EmployerJobsState {
  const EmployerJobsLoading();
}

final class EmployerJobsEmpty extends EmployerJobsState {
  const EmployerJobsEmpty();
}

final class EmployerJobsLoaded extends EmployerJobsState {
  const EmployerJobsLoaded(this.jobs);

  final List<JobModel> jobs;

  @override
  List<Object?> get props => [jobs];
}

final class EmployerJobsFailed extends EmployerJobsState {
  const EmployerJobsFailed(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}
