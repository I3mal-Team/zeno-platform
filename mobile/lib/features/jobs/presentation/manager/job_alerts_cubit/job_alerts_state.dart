part of 'job_alerts_cubit.dart';

sealed class JobAlertsState extends Equatable {
  const JobAlertsState();

  @override
  List<Object?> get props => [];
}

final class JobAlertsLoading extends JobAlertsState {
  const JobAlertsLoading();
}

final class JobAlertsLoaded extends JobAlertsState {
  const JobAlertsLoaded(this.alerts);

  final List<JobAlertModel> alerts;

  @override
  List<Object?> get props => [alerts];
}

final class JobAlertsFailed extends JobAlertsState {
  const JobAlertsFailed(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}
