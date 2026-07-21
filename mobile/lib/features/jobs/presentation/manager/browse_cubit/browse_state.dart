part of 'browse_cubit.dart';

sealed class BrowseState extends Equatable {
  const BrowseState();

  @override
  List<Object?> get props => [];
}

final class BrowseLoading extends BrowseState {
  const BrowseLoading();
}

final class BrowseEmpty extends BrowseState {
  const BrowseEmpty();
}

final class BrowseLoaded extends BrowseState {
  const BrowseLoaded(this.jobs);

  final List<JobModel> jobs;

  @override
  List<Object?> get props => [jobs];
}

final class BrowseFailed extends BrowseState {
  const BrowseFailed(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}
