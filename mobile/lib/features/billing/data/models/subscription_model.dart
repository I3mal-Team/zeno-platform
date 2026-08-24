import 'package:equatable/equatable.dart';

import 'plan_model.dart';

/// The caller's current billing standing: the effective [plan] (the free tier
/// when there is no paid subscription) and the [subscription] itself when one
/// is live.
class SubscriptionStandingModel extends Equatable {
  const SubscriptionStandingModel({
    required this.plan,
    this.status,
    this.isLive = false,
    this.expiresAt,
    this.autoRenew = false,
  });

  factory SubscriptionStandingModel.fromJson(Map<String, dynamic> json) {
    final subscription = json['subscription'] as Map<String, dynamic>?;

    return SubscriptionStandingModel(
      plan: PlanModel.fromJson(json['plan'] as Map<String, dynamic>),
      status: subscription?['status'] as String?,
      isLive: subscription?['is_live'] as bool? ?? false,
      expiresAt: subscription?['expires_at'] as String?,
      autoRenew: subscription?['auto_renew'] as bool? ?? false,
    );
  }

  final PlanModel plan;
  final String? status;
  final bool isLive;
  final String? expiresAt;
  final bool autoRenew;

  bool get isSubscribed => status != null;

  @override
  List<Object?> get props => [plan, status, isLive];
}
