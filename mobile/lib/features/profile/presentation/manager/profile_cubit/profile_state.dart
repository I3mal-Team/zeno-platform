part of 'profile_cubit.dart';

sealed class ProfileState extends Equatable {
  const ProfileState();

  @override
  List<Object?> get props => [];
}

final class ProfileLoading extends ProfileState {
  const ProfileLoading();
}

final class ProfileEmpty extends ProfileState {
  const ProfileEmpty();
}

final class ProfileLoaded extends ProfileState {
  const ProfileLoaded(this.profile);

  final CandidateProfileModel profile;

  @override
  List<Object?> get props => [profile];
}

final class ProfileSaving extends ProfileState {
  const ProfileSaving();
}

final class ProfileSaved extends ProfileState {
  const ProfileSaved(this.profile);

  final CandidateProfileModel profile;

  @override
  List<Object?> get props => [profile];
}

final class ProfileFailed extends ProfileState {
  const ProfileFailed(this.failure);

  final Failure failure;

  /// Screens map these onto the matching field rather than showing one banner.
  Map<String, dynamic>? get fieldErrors =>
      failure is ValidationFailure ? failure.details : null;

  @override
  List<Object?> get props => [failure];
}
