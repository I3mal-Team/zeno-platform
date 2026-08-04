import 'package:equatable/equatable.dart';

class OrganizationModel extends Equatable {
  const OrganizationModel({
    required this.id,
    required this.type,
    required this.name,
    required this.verificationStatus,
    required this.isVerified,
    this.responsiblePersonName,
    this.cityId,
    this.cityName,
    this.about,
    this.logoUrl,
  });

  factory OrganizationModel.fromJson(Map<String, dynamic> json) {
    final city = json['city'];

    return OrganizationModel(
      id: json['id'] as String,
      type: json['type'] as String,
      name: json['name'] as String,
      verificationStatus:
          json['verification_status'] as String? ?? 'unverified',
      isVerified: json['is_verified'] as bool? ?? false,
      responsiblePersonName: json['responsible_person_name'] as String?,
      cityId: city is Map<String, dynamic> ? city['id'] as int? : null,
      cityName: city is Map<String, dynamic> ? city['name'] as String? : null,
      about: json['about'] as String?,
      logoUrl: json['logo_url'] as String?,
    );
  }

  final String id;
  final String type;
  final String name;
  final String verificationStatus;
  final bool isVerified;
  final String? responsiblePersonName;
  final int? cityId;
  final String? cityName;
  final String? about;
  final String? logoUrl;

  bool get isCompany => type == 'company';

  @override
  List<Object?> get props => [id, type, name, verificationStatus];
}
