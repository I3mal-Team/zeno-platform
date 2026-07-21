import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../features/auth/data/models/user_model.dart';
import '../../../features/auth/data/repos/auth_repo.dart';
import '../../cubit_extension/safe_cubit.dart';
import '../../utils/secure_storage_manager.dart';

part 'user_state.dart';

/// Outlives every screen: the session is what the router and the API layer both
/// key off.
class UserCubit extends Cubit<UserState> {
  UserCubit(this._repo, this._storage) : super(const UserUnknown());

  final AuthRepo _repo;
  final SecureStorageManager _storage;

  UserModel? get user =>
      state is UserSignedIn ? (state as UserSignedIn).user : null;

  Future<void> restore() async {
    if (await _storage.readAccessToken() == null) {
      return safeEmit(const UserSignedOut());
    }

    final result = await _repo.currentUser();

    safeEmit(result.fold((_) => const UserSignedOut(), UserSignedIn.new));
  }

  void onSignedIn(UserModel user) => safeEmit(UserSignedIn(user));

  Future<void> signOut() async {
    await _repo.logout();
    safeEmit(const UserSignedOut());
  }

  /// Called when the API layer sees a globally-handled failure such as an
  /// expired session, so every screen reacts through one path.
  void onSessionLost() => safeEmit(const UserSignedOut());
}
