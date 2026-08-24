import 'package:equatable/equatable.dart';

class RefOption extends Equatable {
  const RefOption({required this.id, required this.code, required this.name});

  factory RefOption.fromJson(Map<String, dynamic> json) => RefOption(
    id: json['id'] as int,
    code: json['code'] as String? ?? '',
    name: json['name'] as String,
  );

  final int id;
  final String code;
  final String name;

  @override
  List<Object?> get props => [id, code, name];
}

/// Everything the publish form needs to render its dropdowns.
class JobFormOptions extends Equatable {
  const JobFormOptions({
    required this.categories,
    required this.workTypes,
    required this.salaryUnits,
    required this.genderRequirements,
    required this.nationalityRequirements,
  });

  factory JobFormOptions.fromJson(Map<String, dynamic> json) {
    List<RefOption> list(String key) => (json[key] as List<dynamic>? ?? [])
        .map((e) => RefOption.fromJson(e as Map<String, dynamic>))
        .toList();

    return JobFormOptions(
      categories: list('categories'),
      workTypes: list('work_types'),
      salaryUnits: list('salary_units'),
      genderRequirements: list('gender_requirements'),
      nationalityRequirements: list('nationality_requirements'),
    );
  }

  final List<RefOption> categories;
  final List<RefOption> workTypes;
  final List<RefOption> salaryUnits;
  final List<RefOption> genderRequirements;
  final List<RefOption> nationalityRequirements;

  @override
  List<Object?> get props => [
    categories,
    workTypes,
    salaryUnits,
    genderRequirements,
    nationalityRequirements,
  ];
}
