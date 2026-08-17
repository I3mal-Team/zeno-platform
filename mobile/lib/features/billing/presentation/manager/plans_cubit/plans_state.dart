part of 'plans_cubit.dart';

sealed class PlansState extends Equatable {
  const PlansState();

  @override
  List<Object?> get props => [];
}

final class PlansLoading extends PlansState {
  const PlansLoading();
}

final class PlansLoaded extends PlansState {
  const PlansLoaded({required this.plans, this.standing});

  final List<PlanModel> plans;
  final SubscriptionStandingModel? standing;

  @override
  List<Object?> get props => [plans, standing];
}

final class PlansFailed extends PlansState {
  const PlansFailed(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}
