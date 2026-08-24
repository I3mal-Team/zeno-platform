part of 'search_cubit.dart';

sealed class SearchState extends Equatable {
  const SearchState();

  @override
  List<Object?> get props => [];
}

/// Nothing typed and no filter on — the screen invites a search.
final class SearchIdle extends SearchState {
  const SearchIdle();
}

final class SearchLoading extends SearchState {
  const SearchLoading();
}

final class SearchLoaded extends SearchState {
  const SearchLoaded(this.jobs);

  final List<JobModel> jobs;

  @override
  List<Object?> get props => [jobs];
}

final class SearchEmpty extends SearchState {
  const SearchEmpty();
}

final class SearchFailed extends SearchState {
  const SearchFailed(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}
