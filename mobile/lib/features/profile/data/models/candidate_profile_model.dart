import 'package:equatable/equatable.dart';

import 'city_model.dart';

class CandidateProfileModel extends Equatable {
  const CandidateProfileModel({
    required this.fullName,
    required this.completionPercentage,
    this.age,
    this.birthDate,
    this.nationalityCode,
    this.city,
    this.jobTitle,
    this.yearsOfExperience,
    this.skills = const [],
    this.bio,
    this.avatarUrl,
    this.resumeUrl,
  });

  factory CandidateProfileModel.fromJson(Map<String, dynamic> json) {
    final city = json['city'];

    return CandidateProfileModel(
      fullName: json['full_name'] as String,
      completionPercentage: json['completion_percentage'] as int? ?? 0,
      age: json['age'] as int?,
      birthDate: json['birth_date'] as String?,
      nationalityCode: json['nationality_code'] as String?,
      city: city is Map<String, dynamic> ? CityModel.fromJson(city) : null,
      jobTitle: json['job_title'] as String?,
      yearsOfExperience: json['years_of_experience'] as int?,
      skills: (json['skills'] as List<dynamic>? ?? []).cast<String>(),
      bio: json['bio'] as String?,
      avatarUrl: json['avatar_url'] as String?,
      resumeUrl: json['resume_url'] as String?,
    );
  }

  final String fullName;
  final int completionPercentage;
  final int? age;
  final String? birthDate;
  final String? nationalityCode;
  final CityModel? city;
  final String? jobTitle;
  final int? yearsOfExperience;
  final List<String> skills;
  final String? bio;
  final String? avatarUrl;
  final String? resumeUrl;

  bool get isComplete => completionPercentage >= 100;

  @override
  List<Object?> get props => [
    fullName,
    completionPercentage,
    city,
    jobTitle,
    yearsOfExperience,
    skills,
    bio,
    avatarUrl,
  ];
}
