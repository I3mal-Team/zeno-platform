import 'package:equatable/equatable.dart';

class ApplicantProfileModel extends Equatable {
  const ApplicantProfileModel({
    required this.reference,
    required this.status,
    required this.contactChannel,
    this.candidatePhone,
    this.name,
    this.age,
    this.jobTitle,
    this.yearsOfExperience,
    this.skills = const [],
    this.bio,
    this.city,
    this.avatarUrl,
    this.resumeUrl,
    this.answers = const [],
  });

  factory ApplicantProfileModel.fromJson(Map<String, dynamic> json) {
    final profile = json['profile'] as Map<String, dynamic>?;

    return ApplicantProfileModel(
      reference: json['reference'] as String,
      status: json['status'] as String,
      contactChannel: json['contact_channel'] as String? ?? 'app',
      candidatePhone: json['candidate_phone'] as String?,
      name: profile?['name'] as String?,
      age: profile?['age'] as int?,
      jobTitle: profile?['job_title'] as String?,
      yearsOfExperience: profile?['years_of_experience'] as int?,
      skills: (profile?['skills'] as List<dynamic>? ?? []).cast<String>(),
      bio: profile?['bio'] as String?,
      city: profile?['city'] as String?,
      avatarUrl: profile?['avatar_url'] as String?,
      resumeUrl: profile?['resume_url'] as String?,
      answers: (json['answers'] as List<dynamic>? ?? [])
          .map((e) => ApplicantAnswer.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  final String reference;
  final String status;
  final String contactChannel;
  final String? candidatePhone;
  final String? name;
  final int? age;
  final String? jobTitle;
  final int? yearsOfExperience;
  final List<String> skills;
  final String? bio;
  final String? city;
  final String? avatarUrl;
  final String? resumeUrl;
  final List<ApplicantAnswer> answers;

  bool get isDecidable => status == 'submitted' || status == 'review';
  bool get isAccepted => status == 'accepted';

  @override
  List<Object?> get props => [reference, status];
}

/// One answer the candidate gave to the job's custom form, as the employer
/// sees it: a scalar [value], or a [fileUrl] for an uploaded file/image.
class ApplicantAnswer extends Equatable {
  const ApplicantAnswer({
    required this.label,
    required this.type,
    this.value,
    this.fileUrl,
    this.isImage = false,
  });

  factory ApplicantAnswer.fromJson(Map<String, dynamic> json) {
    return ApplicantAnswer(
      label: json['label'] as String? ?? '',
      type: json['type'] as String? ?? 'text',
      value: json['value'] as String?,
      fileUrl: json['file_url'] as String?,
      isImage: json['is_image'] as bool? ?? false,
    );
  }

  final String label;
  final String type;
  final String? value;
  final String? fileUrl;
  final bool isImage;

  @override
  List<Object?> get props => [label, value, fileUrl];
}
