part of 'applications_cubit.dart';

sealed class ApplicationsState extends Equatable {
  const ApplicationsState();

  @override
  List<Object?> get props => [];
}

final class ApplicationsLoading extends ApplicationsState {
  const ApplicationsLoading();
}

final class ApplicationsEmpty extends ApplicationsState {
  const ApplicationsEmpty();
}

final class ApplicationsLoaded extends ApplicationsState {
  const ApplicationsLoaded(this.applications);

  final List<ApplicationModel> applications;

  @override
  List<Object?> get props => [applications];
}

final class ApplicationsFailed extends ApplicationsState {
  const ApplicationsFailed(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}
