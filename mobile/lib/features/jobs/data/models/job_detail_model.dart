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
    this.publishedAt,
  });

  factory JobDetailModel.fromJson(Map<String, dynamic> json) {
    final category = json['category'];
    final requirements = json['requirements'] as Map<String, dynamic>? ?? {};
    final location = json['location'] as Map<String, dynamic>? ?? {};
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
      publishedAt: json['published_at'] as String?,
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
  final String? publishedAt;

  String get categoryCode => category?.code ?? 'other';

  @override
  List<Object?> get props => [id, status, salary];
}
