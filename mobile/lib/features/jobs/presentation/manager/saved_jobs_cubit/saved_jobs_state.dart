part of 'saved_jobs_cubit.dart';

sealed class SavedJobsState extends Equatable {
  const SavedJobsState();

  @override
  List<Object?> get props => [];
}

final class SavedJobsLoading extends SavedJobsState {
  const SavedJobsLoading();
}

final class SavedJobsLoaded extends SavedJobsState {
  const SavedJobsLoaded(this.jobs);

  final List<JobModel> jobs;

  @override
  List<Object?> get props => [jobs];
}

final class SavedJobsFailed extends SavedJobsState {
  const SavedJobsFailed(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}
