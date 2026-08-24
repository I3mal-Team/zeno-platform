import 'package:equatable/equatable.dart';

class CityModel extends Equatable {
  const CityModel({required this.id, required this.name, this.region});

  factory CityModel.fromJson(Map<String, dynamic> json) => CityModel(
    id: json['id'] as int,
    name: json['name'] as String,
    region: json['region'] as String?,
  );

  final int id;
  final String name;
  final String? region;

  @override
  List<Object?> get props => [id, name];
}
