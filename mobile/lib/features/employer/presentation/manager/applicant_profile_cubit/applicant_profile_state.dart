part of 'applicant_profile_cubit.dart';

sealed class ApplicantProfileState extends Equatable {
  const ApplicantProfileState();

  @override
  List<Object?> get props => [];
}

final class ApplicantProfileLoading extends ApplicantProfileState {
  const ApplicantProfileLoading();
}

final class ApplicantProfileDeciding extends ApplicantProfileState {
  const ApplicantProfileDeciding();
}

final class ApplicantProfileLoaded extends ApplicantProfileState {
  const ApplicantProfileLoaded(this.profile);

  final ApplicantProfileModel profile;

  @override
  List<Object?> get props => [profile];
}

final class ApplicantProfileFailed extends ApplicantProfileState {
  const ApplicantProfileFailed(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}
