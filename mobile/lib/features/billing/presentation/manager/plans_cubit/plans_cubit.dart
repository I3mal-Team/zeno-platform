import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../data/models/plan_model.dart';
import '../../../data/models/subscription_model.dart';
import '../../../data/repos/billing_repo.dart';

part 'plans_state.dart';

class PlansCubit extends Cubit<PlansState> {
  PlansCubit(this._repo) : super(const PlansLoading());

  final BillingRepo _repo;

  /// Loads the plans and the current standing together — the screen needs both
  /// to render (the list, and which one the user is on).
  Future<void> load() async {
    safeEmit(const PlansLoading());

    final results = await Future.wait([
      _repo.fetchStanding(),
      _repo.fetchPlans(),
    ]);

    final standing = results[0].fold<SubscriptionStandingModel?>(
      (_) => null,
      (value) => value as SubscriptionStandingModel,
    );

    results[1].fold(
      (failure) => safeEmit(PlansFailed(failure)),
      (plans) => safeEmit(
        PlansLoaded(plans: plans as List<PlanModel>, standing: standing),
      ),
    );
  }
}
