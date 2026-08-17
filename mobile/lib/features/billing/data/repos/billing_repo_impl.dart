import 'package:dartz/dartz.dart';

import '../../../../core/databases/api/api_consumer.dart';
import '../../../../core/databases/api/end_points.dart';
import '../../../../core/databases/api/handle_request.dart';
import '../../../../core/errors/failure.dart';
import '../models/plan_model.dart';
import '../models/subscription_model.dart';
import 'billing_repo.dart';

class BillingRepoImpl implements BillingRepo {
  const BillingRepoImpl(this._api, this._handle);

  final ApiConsumer _api;
  final RequestHandler _handle;

  @override
  Future<Either<Failure, List<PlanModel>>> fetchPlans() {
    return _handle(
      () => _api.get(EndPoints.billingPlans),
      (data) => (data as List<dynamic>)
          .map((e) => PlanModel.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  @override
  Future<Either<Failure, SubscriptionStandingModel>> fetchStanding() {
    return _handle(
      () => _api.get(EndPoints.billingSubscription),
      (data) =>
          SubscriptionStandingModel.fromJson(data as Map<String, dynamic>),
    );
  }
}
