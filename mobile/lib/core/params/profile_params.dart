class SaveCandidateProfileParam {
  const SaveCandidateProfileParam({
    required this.fullName,
    this.birthDate,
    this.nationalityCode,
    this.cityId,
    this.jobTitle,
    this.yearsOfExperience,
    this.skills = const [],
    this.bio,
  });

  final String fullName;
  final String? birthDate;
  final String? nationalityCode;
  final int? cityId;
  final String? jobTitle;
  final int? yearsOfExperience;
  final List<String> skills;
  final String? bio;

  Map<String, dynamic> toJson() => {
    'full_name': fullName,
    if (birthDate != null) 'birth_date': birthDate,
    if (nationalityCode != null) 'nationality_code': nationalityCode,
    if (cityId != null) 'city_id': cityId,
    if (jobTitle != null) 'job_title': jobTitle,
    if (yearsOfExperience != null) 'years_of_experience': yearsOfExperience,
    'skills': skills,
    if (bio != null) 'bio': bio,
  };
}
