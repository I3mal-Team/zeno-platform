part of 'job_detail_cubit.dart';

sealed class JobDetailState extends Equatable {
  const JobDetailState();

  @override
  List<Object?> get props => [];
}

final class JobDetailLoading extends JobDetailState {
  const JobDetailLoading();
}

final class JobDetailLoaded extends JobDetailState {
  const JobDetailLoaded(this.job);

  final JobDetailModel job;

  @override
  List<Object?> get props => [job];
}

final class JobDetailFailed extends JobDetailState {
  const JobDetailFailed(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}
