import 'package:equatable/equatable.dart';

import 'user_model.dart';

class AuthSessionModel extends Equatable {
  const AuthSessionModel({
    required this.token,
    required this.user,
    required this.isNewUser,
  });

  factory AuthSessionModel.fromJson(Map<String, dynamic> json) =>
      AuthSessionModel(
        token: json['token'] as String,
        user: UserModel.fromJson(json['user'] as Map<String, dynamic>),
        isNewUser: json['is_new_user'] as bool? ?? false,
      );

  final String token;
  final UserModel user;

  /// Drives whether verification lands on registration or straight on the home
  /// screen.
  final bool isNewUser;

  @override
  List<Object?> get props => [token, user, isNewUser];
}
