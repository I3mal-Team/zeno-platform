import 'package:equatable/equatable.dart';

/// A row in the employer's applicants list.
class ApplicantModel extends Equatable {
  const ApplicantModel({
    required this.id,
    required this.reference,
    required this.status,
    required this.statusLabel,
    this.isNew = false,
    this.shortlisted = false,
    this.appliedAt,
    this.name,
    this.jobTitle,
    this.yearsOfExperience,
  });

  factory ApplicantModel.fromJson(Map<String, dynamic> json) {
    final candidate = json['candidate'] as Map<String, dynamic>? ?? {};

    return ApplicantModel(
      id: json['id'] as int,
      reference: json['reference'] as String,
      status: json['status'] as String,
      statusLabel: json['status_label'] as String? ?? '',
      isNew: json['is_new'] as bool? ?? false,
      shortlisted: json['shortlisted'] as bool? ?? false,
      appliedAt: json['applied_at'] as String?,
      name: candidate['name'] as String?,
      jobTitle: candidate['job_title'] as String?,
      yearsOfExperience: candidate['years_of_experience'] as int?,
    );
  }

  final int id;
  final String reference;
  final String status;
  final String statusLabel;
  final bool isNew;
  final bool shortlisted;
  final String? appliedAt;
  final String? name;
  final String? jobTitle;
  final int? yearsOfExperience;

  bool get isDecidable => status == 'submitted' || status == 'review';

  @override
  List<Object?> get props => [id, status, shortlisted];
}
