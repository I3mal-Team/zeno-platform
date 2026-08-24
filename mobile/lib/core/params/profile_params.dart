class SaveOrganizationParam {
  const SaveOrganizationParam({
    required this.type,
    required this.name,
    this.responsiblePersonName,
    this.commercialRegistration,
    this.cityId,
    this.about,
  });

  /// 'individual' or 'company'.
  final String type;
  final String name;
  final String? responsiblePersonName;
  final String? commercialRegistration;
  final int? cityId;
  final String? about;

  Map<String, dynamic> toJson() => {
    'type': type,
    'name': name,
    if (responsiblePersonName != null)
      'responsible_person_name': responsiblePersonName,
    if (commercialRegistration != null)
      'commercial_registration': commercialRegistration,
    if (cityId != null) 'city_id': cityId,
    if (about != null) 'about': about,
  };
}

class SaveCandidateProfileParam {
  const SaveCandidateProfileParam({
    required this.fullName,
    this.nationalId,
    this.nationalIdType,
    this.birthDate,
    this.gender,
    this.nationalityCode,
    this.cityId,
    this.jobTitle,
    this.yearsOfExperience,
    this.skills = const [],
    this.bio,
    this.latitude,
    this.longitude,
  });

  final String fullName;
  final String? nationalId;

  /// 'national' or 'iqama' — inferred from the ID's leading digit.
  final String? nationalIdType;
  final String? birthDate;

  /// 'male' or 'female'.
  final String? gender;
  final String? nationalityCode;
  final int? cityId;
  final String? jobTitle;
  final int? yearsOfExperience;
  final List<String> skills;
  final String? bio;
  final double? latitude;
  final double? longitude;

  Map<String, dynamic> toJson() => {
    'full_name': fullName,
    if (nationalId != null) 'national_id': nationalId,
    if (nationalIdType != null) 'national_id_type': nationalIdType,
    if (birthDate != null) 'birth_date': birthDate,
    if (gender != null) 'gender': gender,
    if (nationalityCode != null) 'nationality_code': nationalityCode,
    if (cityId != null) 'city_id': cityId,
    if (jobTitle != null) 'job_title': jobTitle,
    if (yearsOfExperience != null) 'years_of_experience': yearsOfExperience,
    'skills': skills,
    if (bio != null) 'bio': bio,
    if (latitude != null) 'latitude': latitude,
    if (longitude != null) 'longitude': longitude,
  };
}
