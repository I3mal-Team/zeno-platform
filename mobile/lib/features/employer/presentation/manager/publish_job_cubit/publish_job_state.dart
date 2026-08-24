part of 'publish_job_cubit.dart';

sealed class PublishJobState extends Equatable {
  const PublishJobState();

  @override
  List<Object?> get props => [];
}

final class PublishJobLoading extends PublishJobState {
  const PublishJobLoading();
}

final class PublishJobLoadFailed extends PublishJobState {
  const PublishJobLoadFailed(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}

final class PublishJobReady extends PublishJobState {
  const PublishJobReady();
}

final class PublishJobSubmitting extends PublishJobState {
  const PublishJobSubmitting();
}

final class PublishJobDone extends PublishJobState {
  const PublishJobDone(this.job);

  final JobDetailModel job;

  @override
  List<Object?> get props => [job];
}

final class PublishJobFailed extends PublishJobState {
  const PublishJobFailed(this.failure);

  final Failure failure;

  Map<String, dynamic>? get fieldErrors =>
      failure is ValidationFailure ? failure.details : null;

  @override
  List<Object?> get props => [failure];
}
