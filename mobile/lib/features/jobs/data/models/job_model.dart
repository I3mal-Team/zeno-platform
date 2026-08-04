import 'package:equatable/equatable.dart';

class JobSalary extends Equatable {
  const JobSalary({
    required this.amount,
    required this.formatted,
    this.amountMax,
    this.currency = 'SAR',
    this.unit,
  });

  factory JobSalary.fromJson(Map<String, dynamic> json) => JobSalary(
    amount: (json['amount'] as num?)?.toDouble() ?? 0,
    amountMax: (json['amount_max'] as num?)?.toDouble(),
    currency: json['currency'] as String? ?? 'SAR',
    formatted: json['formatted'] as String? ?? '—',
    unit: json['unit'] as String?,
  );

  final double amount;
  final double? amountMax;
  final String currency;
  final String formatted;
  final String? unit;

  @override
  List<Object?> get props => [amount, amountMax, currency, unit];
}

class JobCategoryRef extends Equatable {
  const JobCategoryRef({required this.code, required this.name});

  factory JobCategoryRef.fromJson(Map<String, dynamic> json) => JobCategoryRef(
    code: json['code'] as String? ?? 'other',
    name: json['name'] as String? ?? 'أخرى',
  );

  final String code;
  final String name;

  @override
  List<Object?> get props => [code, name];
}

/// The card shape returned by the browse feed and the employer list.
class JobModel extends Equatable {
  const JobModel({
    required this.id,
    required this.title,
    required this.slug,
    required this.salary,
    required this.status,
    required this.contactChannel,
    this.category,
    this.workType,
    this.city,
    this.district,
    this.organizationName,
    this.organizationVerified = false,
    this.organizationLogo,
    this.isSaved = false,
    this.distanceKm,
    this.publishedAt,
    this.applicationsCount,
  });

  factory JobModel.fromJson(Map<String, dynamic> json) {
    final category = json['category'];
    final organization = json['organization'];

    return JobModel(
      id: json['id'] as String,
      title: json['title'] as String,
      slug: json['slug'] as String,
      salary: JobSalary.fromJson(json['salary'] as Map<String, dynamic>),
      status: json['status'] as String,
      contactChannel: json['contact_channel'] as String? ?? 'app',
      category: category is Map<String, dynamic>
          ? JobCategoryRef.fromJson(category)
          : null,
      workType: json['work_type'] as String?,
      city: json['city'] as String?,
      district: json['district'] as String?,
      organizationName: organization is Map<String, dynamic>
          ? organization['name'] as String?
          : null,
      organizationVerified: organization is Map<String, dynamic>
          ? organization['is_verified'] as bool? ?? false
          : false,
      organizationLogo: organization is Map<String, dynamic>
          ? organization['logo_url'] as String?
          : null,
      isSaved: json['is_saved'] as bool? ?? false,
      distanceKm: (json['distance_km'] as num?)?.toDouble(),
      publishedAt: json['published_at'] as String?,
      applicationsCount: json['applications_count'] as int?,
    );
  }

  final String id;
  final String title;
  final String slug;
  final JobSalary salary;
  final String status;
  final String contactChannel;
  final JobCategoryRef? category;
  final String? workType;
  final String? city;
  final String? district;
  final String? organizationName;
  final bool organizationVerified;
  final String? organizationLogo;
  final bool isSaved;
  final double? distanceKm;
  final String? publishedAt;
  final int? applicationsCount;

  String get categoryCode => category?.code ?? 'other';

  @override
  List<Object?> get props => [id, status, salary, title];
}
