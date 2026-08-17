import 'package:equatable/equatable.dart';

/// A single capability a plan grants, as resolved by the backend. [type] is
/// either `int` (a limit, read [value] as a number) or `bool` (a switch).
class PlanFeatureModel extends Equatable {
  const PlanFeatureModel({
    required this.key,
    required this.label,
    required this.type,
    required this.value,
    required this.enabled,
  });

  factory PlanFeatureModel.fromJson(Map<String, dynamic> json) {
    return PlanFeatureModel(
      key: json['key'] as String,
      label: json['label'] as String,
      type: json['type'] as String,
      value: json['value'],
      enabled: json['enabled'] as bool? ?? false,
    );
  }

  final String key;
  final String label;
  final String type;
  final dynamic value;
  final bool enabled;

  bool get isLimit => type == 'int';

  /// How the feature reads on a card: an unlimited/off limit collapses to just
  /// the label, a set limit appends its number.
  String get display {
    if (!isLimit) return label;
    final n = value is num ? (value as num).toInt() : 0;
    return n > 0 ? '$label: $n' : label;
  }

  @override
  List<Object?> get props => [key, value, enabled];
}

class PlanModel extends Equatable {
  const PlanModel({
    required this.code,
    required this.audience,
    required this.name,
    required this.price,
    required this.currency,
    required this.durationDays,
    required this.isFree,
    required this.isCurrent,
    required this.features,
  });

  factory PlanModel.fromJson(Map<String, dynamic> json) {
    return PlanModel(
      code: json['code'] as String,
      audience: json['audience'] as String,
      name: json['name'] as String,
      price: (json['price'] as num?)?.toDouble() ?? 0,
      currency: json['currency'] as String? ?? 'SAR',
      durationDays: json['duration_days'] as int? ?? 30,
      isFree: json['is_free'] as bool? ?? false,
      isCurrent: json['is_current'] as bool? ?? false,
      features: (json['features'] as List<dynamic>? ?? [])
          .map((e) => PlanFeatureModel.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  final String code;
  final String audience;
  final String name;
  final double price;
  final String currency;
  final int durationDays;
  final bool isFree;
  final bool isCurrent;
  final List<PlanFeatureModel> features;

  /// The features a plan actually grants — what a card lists with a checkmark.
  List<PlanFeatureModel> get grantedFeatures =>
      features.where((f) => f.enabled).toList();

  @override
  List<Object?> get props => [code, isCurrent, price];
}
