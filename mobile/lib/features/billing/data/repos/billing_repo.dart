import 'package:dartz/dartz.dart';

import '../../../../core/errors/failure.dart';
import '../models/plan_model.dart';
import '../models/subscription_model.dart';

abstract class BillingRepo {
  /// The plans on offer to the signed-in user, cheapest first.
  Future<Either<Failure, List<PlanModel>>> fetchPlans();

  /// The user's current subscription and effective plan.
  Future<Either<Failure, SubscriptionStandingModel>> fetchStanding();
}
