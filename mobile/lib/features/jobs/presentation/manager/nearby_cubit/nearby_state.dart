part of 'nearby_cubit.dart';

sealed class NearbyState extends Equatable {
  const NearbyState();

  @override
  List<Object?> get props => [];
}

final class NearbyLoading extends NearbyState {
  const NearbyLoading();
}

final class NearbyLoaded extends NearbyState {
  const NearbyLoaded(this.jobs, {required this.byGps});

  final List<JobModel> jobs;

  /// Whether the results came from a live GPS fix (else the saved city).
  final bool byGps;

  @override
  List<Object?> get props => [jobs, byGps];
}

final class NearbyFailed extends NearbyState {
  const NearbyFailed(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}
