import 'package:equatable/equatable.dart';

/// Query filters for the browse feed. Null fields are omitted from the request.
class JobFilters extends Equatable {
  const JobFilters({
    this.query,
    this.categoryCode,
    this.workTypeCode,
    this.cityId,
    this.salaryMin,
    this.perPage = 15,
  });

  final String? query;
  final String? categoryCode;
  final String? workTypeCode;
  final int? cityId;
  final num? salaryMin;
  final int perPage;

  JobFilters copyWith({
    String? query,
    String? categoryCode,
    int? cityId,
    num? salaryMin,
    bool clearCategory = false,
  }) {
    return JobFilters(
      query: query ?? this.query,
      categoryCode: clearCategory ? null : (categoryCode ?? this.categoryCode),
      workTypeCode: workTypeCode,
      cityId: cityId ?? this.cityId,
      salaryMin: salaryMin ?? this.salaryMin,
      perPage: perPage,
    );
  }

  Map<String, dynamic> toQuery() => {
    if (query != null && query!.trim().isNotEmpty) 'q': query!.trim(),
    if (categoryCode != null) 'category': categoryCode,
    if (workTypeCode != null) 'work_type': workTypeCode,
    if (cityId != null) 'city': cityId,
    if (salaryMin != null) 'salary_min': salaryMin,
    'per_page': perPage,
  };

  @override
  List<Object?> get props => [
    query,
    categoryCode,
    workTypeCode,
    cityId,
    salaryMin,
    perPage,
  ];
}
