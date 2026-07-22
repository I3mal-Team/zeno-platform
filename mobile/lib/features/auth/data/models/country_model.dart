import 'package:equatable/equatable.dart';

/// A dial code the sign-in form supports, managed from the admin dashboard.
class CountryModel extends Equatable {
  const CountryModel({
    required this.iso2,
    required this.dialCode,
    required this.flag,
    required this.name,
    required this.placeholder,
  });

  factory CountryModel.fromJson(Map<String, dynamic> json) => CountryModel(
    iso2: json['iso2'] as String,
    dialCode: json['dial_code'] as String,
    flag: (json['flag'] as String?) ?? '',
    name: json['name'] as String,
    placeholder: (json['placeholder'] as String?) ?? '',
  );

  /// ISO-3166 alpha-2, sent with the phone so the server normalises to E.164.
  final String iso2;

  /// Display prefix, e.g. "+966".
  final String dialCode;
  final String flag;
  final String name;

  /// Sample local number shown as the field hint for this country.
  final String placeholder;

  @override
  List<Object?> get props => [iso2, dialCode, flag, name, placeholder];
}
