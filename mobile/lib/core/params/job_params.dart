import 'package:equatable/equatable.dart';

/// The body an employer sends to publish or edit a listing. Null fields are
/// omitted so the server applies its own defaults.
class PublishJobParam {
  const PublishJobParam({
    required this.title,
    required this.categoryId,
    required this.workTypeId,
    required this.salaryUnitId,
    required this.genderRequirementId,
    required this.nationalityRequirementId,
    required this.salaryAmount,
    required this.vacanciesCount,
    required this.cityId,
    required this.contactChannel,
    this.description,
    this.salaryAmountMax,
    this.hoursPerWeek,
    this.shiftNote,
    this.districtId,
    this.addressLine,
  });

  final String title;
  final int categoryId;
  final int workTypeId;
  final int salaryUnitId;
  final int genderRequirementId;
  final int nationalityRequirementId;
  final num salaryAmount;
  final int vacanciesCount;
  final int cityId;
  final String contactChannel;
  final String? description;
  final num? salaryAmountMax;
  final int? hoursPerWeek;
  final String? shiftNote;
  final int? districtId;
  final String? addressLine;

  Map<String, dynamic> toJson() => {
    'title': title,
    'category_id': categoryId,
    'work_type_id': workTypeId,
    'salary_unit_id': salaryUnitId,
    'gender_requirement_id': genderRequirementId,
    'nationality_requirement_id': nationalityRequirementId,
    'salary_amount': salaryAmount,
    'vacancies_count': vacanciesCount,
    'city_id': cityId,
    'contact_channel': contactChannel,
    if (description != null) 'description': description,
    if (salaryAmountMax != null) 'salary_amount_max': salaryAmountMax,
    if (hoursPerWeek != null) 'hours_per_week': hoursPerWeek,
    if (shiftNote != null) 'shift_note': shiftNote,
    if (districtId != null) 'district_id': districtId,
    if (addressLine != null) 'address_line': addressLine,
  };
}

/// Query filters for the browse feed. Null fields are omitted from the request.
class JobFilters extends Equatable {
  const JobFilters({
    this.query,
    this.categoryCode,
    this.workTypeCode,
    this.cityId,
    this.salaryMin,
    this.genderCode,
    this.nationalityCode,
    this.perPage = 15,
  });

  final String? query;
  final String? categoryCode;
  final String? workTypeCode;
  final int? cityId;
  final num? salaryMin;
  final String? genderCode;
  final String? nationalityCode;
  final int perPage;

  /// How many of the advanced filters are on — the badge on the filters
  /// button. The text query and category are not counted: they have their own
  /// controls on screen.
  int get activeCount => [
    workTypeCode,
    genderCode,
    nationalityCode,
  ].where((value) => value != null).length;

  JobFilters copyWith({
    String? query,
    String? categoryCode,
    String? workTypeCode,
    int? cityId,
    num? salaryMin,
    String? genderCode,
    String? nationalityCode,
    bool clearCategory = false,
    bool clearWorkType = false,
    bool clearGender = false,
    bool clearNationality = false,
  }) {
    return JobFilters(
      query: query ?? this.query,
      categoryCode: clearCategory ? null : (categoryCode ?? this.categoryCode),
      workTypeCode: clearWorkType ? null : (workTypeCode ?? this.workTypeCode),
      cityId: cityId ?? this.cityId,
      salaryMin: salaryMin ?? this.salaryMin,
      genderCode: clearGender ? null : (genderCode ?? this.genderCode),
      nationalityCode: clearNationality
          ? null
          : (nationalityCode ?? this.nationalityCode),
      perPage: perPage,
    );
  }

  /// Drops every advanced filter, keeping what the screen still shows.
  JobFilters cleared() => JobFilters(
    query: query,
    categoryCode: categoryCode,
    cityId: cityId,
    perPage: perPage,
  );

  Map<String, dynamic> toQuery() => {
    if (query != null && query!.trim().isNotEmpty) 'q': query!.trim(),
    if (categoryCode != null) 'category': categoryCode,
    if (workTypeCode != null) 'work_type': workTypeCode,
    if (cityId != null) 'city': cityId,
    if (salaryMin != null) 'salary_min': salaryMin,
    if (genderCode != null) 'gender': genderCode,
    if (nationalityCode != null) 'nationality': nationalityCode,
    'per_page': perPage,
  };

  @override
  List<Object?> get props => [
    query,
    categoryCode,
    workTypeCode,
    cityId,
    salaryMin,
    genderCode,
    nationalityCode,
    perPage,
  ];
}
