part of 'applicants_cubit.dart';

sealed class ApplicantsState extends Equatable {
  const ApplicantsState();

  @override
  List<Object?> get props => [];
}

final class ApplicantsLoading extends ApplicantsState {
  const ApplicantsLoading();
}

final class ApplicantsEmpty extends ApplicantsState {
  const ApplicantsEmpty();
}

final class ApplicantsLoaded extends ApplicantsState {
  const ApplicantsLoaded(this.applicants);

  final List<ApplicantModel> applicants;

  @override
  List<Object?> get props => [applicants];
}

final class ApplicantsFailed extends ApplicantsState {
  const ApplicantsFailed(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}
