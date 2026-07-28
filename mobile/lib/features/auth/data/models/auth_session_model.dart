import 'package:equatable/equatable.dart';

import 'user_model.dart';

class AuthSessionModel extends Equatable {
  const AuthSessionModel({
    required this.token,
    required this.user,
    required this.isNewUser,
    required this.profileCompleted,
  });

  factory AuthSessionModel.fromJson(Map<String, dynamic> json) =>
      AuthSessionModel(
        token: json['token'] as String,
        user: UserModel.fromJson(json['user'] as Map<String, dynamic>),
        isNewUser: json['is_new_user'] as bool? ?? false,
        profileCompleted: json['profile_completed'] as bool? ?? true,
      );

  final String token;
  final UserModel user;

  /// Drives whether verification lands on registration or straight on the home
  /// screen.
  final bool isNewUser;

  /// Whether a job seeker has already saved their profile. A candidate who
  /// hasn't is sent to "أكمل بياناتك" before the home screen. Defaults to true
  /// so a server that omits the flag never traps an existing user on the form.
  final bool profileCompleted;

  @override
  List<Object?> get props => [token, user, isNewUser, profileCompleted];
}
