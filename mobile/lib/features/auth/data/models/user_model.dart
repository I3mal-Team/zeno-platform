import 'package:equatable/equatable.dart';

class UserModel extends Equatable {
  const UserModel({
    required this.id,
    required this.phone,
    required this.role,
    required this.status,
    required this.phoneVerified,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) => UserModel(
    id: json['id'] as String,
    phone: json['phone'] as String,
    role: json['role'] as String,
    status: json['status'] as String,
    phoneVerified: json['phone_verified'] as bool? ?? false,
  );

  final String id;
  final String phone;
  final String role;
  final String status;
  final bool phoneVerified;

  bool get isCandidate => role == 'candidate';
  bool get isEmployer => role == 'employer';

  @override
  List<Object?> get props => [id, phone, role, status, phoneVerified];
}
