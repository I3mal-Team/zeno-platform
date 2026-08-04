import 'package:equatable/equatable.dart';

/// A candidate's saved search. The facets are already resolved to names by the
/// API, so the list screen just prints them.
class JobAlertModel extends Equatable {
  const JobAlertModel({
    required this.id,
    this.keyword,
    this.categoryName,
    this.cityName,
    this.workTypeName,
  });

  factory JobAlertModel.fromJson(Map<String, dynamic> json) {
    final category = json['category'];

    return JobAlertModel(
      id: json['id'] as int,
      keyword: json['keyword'] as String?,
      categoryName: category is Map<String, dynamic>
          ? category['name'] as String?
          : null,
      cityName: json['city'] as String?,
      workTypeName: json['work_type'] as String?,
    );
  }

  final int id;
  final String? keyword;
  final String? categoryName;
  final String? cityName;
  final String? workTypeName;

  /// The facet labels to show as chips — empty means "any new job".
  List<String> get chips => [
    if (keyword != null && keyword!.isNotEmpty) '«$keyword»',
    if (categoryName != null) categoryName!,
    if (cityName != null) cityName!,
    if (workTypeName != null) workTypeName!,
  ];

  @override
  List<Object?> get props => [
    id,
    keyword,
    categoryName,
    cityName,
    workTypeName,
  ];
}
