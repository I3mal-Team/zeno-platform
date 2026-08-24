part of 'employer_profile_cubit.dart';

sealed class EmployerProfileState extends Equatable {
  const EmployerProfileState();

  @override
  List<Object?> get props => [];
}

final class EmployerProfileLoading extends EmployerProfileState {
  const EmployerProfileLoading();
}

final class EmployerProfileEmpty extends EmployerProfileState {
  const EmployerProfileEmpty();
}

final class EmployerProfileLoaded extends EmployerProfileState {
  const EmployerProfileLoaded(this.organization);

  final OrganizationModel organization;

  @override
  List<Object?> get props => [organization];
}

final class EmployerProfileSaving extends EmployerProfileState {
  const EmployerProfileSaving();
}

final class EmployerProfileSaved extends EmployerProfileState {
  const EmployerProfileSaved(this.organization);

  final OrganizationModel organization;

  @override
  List<Object?> get props => [organization];
}

final class EmployerProfileFailed extends EmployerProfileState {
  const EmployerProfileFailed(this.failure);

  final Failure failure;

  Map<String, dynamic>? get fieldErrors =>
      failure is ValidationFailure ? failure.details : null;

  @override
  List<Object?> get props => [failure];
}
