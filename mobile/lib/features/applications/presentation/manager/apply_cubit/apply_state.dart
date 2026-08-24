part of 'apply_cubit.dart';

sealed class ApplyState extends Equatable {
  const ApplyState();

  @override
  List<Object?> get props => [];
}

final class ApplyInitial extends ApplyState {
  const ApplyInitial();
}

final class ApplySubmitting extends ApplyState {
  const ApplySubmitting();
}

final class ApplyDone extends ApplyState {
  const ApplyDone(this.application);

  final ApplicationModel application;

  @override
  List<Object?> get props => [application];
}

final class ApplyFailed extends ApplyState {
  const ApplyFailed(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}
