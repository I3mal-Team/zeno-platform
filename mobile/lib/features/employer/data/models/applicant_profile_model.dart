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

  bool get isDecidable => status == 'submitted' || status == 'review';
  bool get isAccepted => status == 'accepted';

  @override
  List<Object?> get props => [reference, status];
}
