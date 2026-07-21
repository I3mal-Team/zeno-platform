part of 'user_cubit.dart';

sealed class UserState extends Equatable {
  const UserState();

  @override
  List<Object?> get props => [];
}

final class UserUnknown extends UserState {
  const UserUnknown();
}

final class UserSignedOut extends UserState {
  const UserSignedOut();
}

final class UserSignedIn extends UserState {
  const UserSignedIn(this.user);

  final UserModel user;

  @override
  List<Object?> get props => [user];
}
