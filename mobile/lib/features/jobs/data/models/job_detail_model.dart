import 'package:equatable/equatable.dart';

import 'application_field_model.dart';
import 'job_model.dart';

class JobDetailModel extends Equatable {
  const JobDetailModel({
    required this.id,
    required this.title,
    required this.slug,
    required this.salary,
    required this.status,
    required this.statusLabel,
    required this.isOpenForApplications,
    required this.contactChannel,
    required this.vacanciesCount,
    this.applicationFields = const [],
    this.description,
    this.category,
    this.workType,
    this.hoursPerWeek,
    this.shiftNote,
    this.genderRequirement,
    this.nationalityRequirement,
    this.city,
    this.district,
    this.addressLine,
    this.organizationName,
    this.organizationVerified = false,
    this.organizationLogo,
    this.isSaved = false,
    this.hasApplied = false,
    this.publishedAt,
    this.viewsCount,
    this.edit,
  });

  factory JobDetailModel.fromJson(Map<String, dynamic> json) {
    final category = json['category'];
    // A resource with no loaded relations serialises these objects as an empty
    // list ([]) rather than {}, so guard the cast against a non-map value.
    final requirements = json['requirements'] is Map<String, dynamic>
        ? json['requirements'] as Map<String, dynamic>
        : const <String, dynamic>{};
    final location = json['location'] is Map<String, dynamic>
        ? json['location'] as Map<String, dynamic>
        : const <String, dynamic>{};
    final organization = json['organization'];

    return JobDetailModel(
      id: json['id'] as String,
      title: json['title'] as String,
      slug: json['slug'] as String,
      salary: JobSalary.fromJson(json['salary'] as Map<String, dynamic>),
      status: json['status'] as String,
      statusLabel: json['status_label'] as String? ?? '',
      isOpenForApplications: json['is_open_for_applications'] as bool? ?? false,
      contactChannel: json['contact_channel'] as String? ?? 'app',
      vacanciesCount: json['vacancies_count'] as int? ?? 1,
      applicationFields: (json['application_fields'] as List<dynamic>? ?? [])
          .map((e) => ApplicationFieldModel.fromJson(e as Map<String, dynamic>))
          .toList(),
      description: json['description'] as String?,
      category: category is Map<String, dynamic>
          ? JobCategoryRef.fromJson(category)
          : null,
      workType: json['work_type'] as String?,
      hoursPerWeek: json['hours_per_week'] as int?,
      shiftNote: json['shift_note'] as String?,
      genderRequirement: requirements['gender'] as String?,
      nationalityRequirement: requirements['nationality'] as String?,
      city: location['city'] as String?,
      district: location['district'] as String?,
      addressLine: location['address_line'] as String?,
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
      hasApplied: json['has_applied'] as bool? ?? false,
      publishedAt: json['published_at'] as String?,
      viewsCount: json['views_count'] as int?,
      edit: json['edit'] is Map<String, dynamic>
          ? JobEditData.fromJson(json['edit'] as Map<String, dynamic>)
          : null,
    );
  }

  final String id;
  final String title;
  final String slug;
  final JobSalary salary;
  final String status;
  final String statusLabel;
  final bool isOpenForApplications;
  final String contactChannel;
  final int vacanciesCount;
  final List<ApplicationFieldModel> applicationFields;
  final String? description;
  final JobCategoryRef? category;
  final String? workType;
  final int? hoursPerWeek;
  final String? shiftNote;
  final String? genderRequirement;
  final String? nationalityRequirement;
  final String? city;
  final String? district;
  final String? addressLine;
  final String? organizationName;
  final bool organizationVerified;
  final String? organizationLogo;
  final bool isSaved;
  final bool hasApplied;
  final String? publishedAt;
  final int? viewsCount;

  /// Raw foreign keys the employer edit form needs to preselect its dropdowns.
  /// Present only on the employer's own job detail.
  final JobEditData? edit;

  String get categoryCode => category?.code ?? 'other';

  @override
  List<Object?> get props => [id, status, salary, viewsCount];
}

/// The foreign keys and raw values behind a listing, so the edit form can
/// reopen it with every field preselected. Location (lat/lng) is intentionally
/// omitted — leaving it null on update keeps the existing pin.
class JobEditData extends Equatable {
  const JobEditData({
    required this.categoryId,
    required this.workTypeId,
    required this.salaryUnitId,
    required this.genderRequirementId,
    required this.nationalityRequirementId,
    required this.cityId,
    required this.salaryAmount,
    required this.vacanciesCount,
    this.districtId,
    this.salaryAmountMax,
    this.hoursPerWeek,
    this.shiftNote,
    this.addressLine,
  });

  factory JobEditData.fromJson(Map<String, dynamic> json) => JobEditData(
    categoryId: json['category_id'] as int,
    workTypeId: json['work_type_id'] as int,
    salaryUnitId: json['salary_unit_id'] as int,
    genderRequirementId: json['gender_requirement_id'] as int,
    nationalityRequirementId: json['nationality_requirement_id'] as int,
    cityId: json['city_id'] as int,
    salaryAmount: (json['salary_amount'] as num).toDouble(),
    vacanciesCount: json['vacancies_count'] as int? ?? 1,
    districtId: json['district_id'] as int?,
    salaryAmountMax: (json['salary_amount_max'] as num?)?.toDouble(),
    hoursPerWeek: json['hours_per_week'] as int?,
    shiftNote: json['shift_note'] as String?,
    addressLine: json['address_line'] as String?,
  );

  final int categoryId;
  final int workTypeId;
  final int salaryUnitId;
  final int genderRequirementId;
  final int nationalityRequirementId;
  final int cityId;
  final double salaryAmount;
  final int vacanciesCount;
  final int? districtId;
  final double? salaryAmountMax;
  final int? hoursPerWeek;
  final String? shiftNote;
  final String? addressLine;

  @override
  List<Object?> get props => [
    categoryId,
    workTypeId,
    salaryUnitId,
    genderRequirementId,
    nationalityRequirementId,
    cityId,
    salaryAmount,
    vacanciesCount,
    districtId,
    salaryAmountMax,
    hoursPerWeek,
    shiftNote,
    addressLine,
  ];
}
