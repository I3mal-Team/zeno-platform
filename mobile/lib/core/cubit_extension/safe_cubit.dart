import 'package:flutter_bloc/flutter_bloc.dart';

/// Guards against emitting after close, which is otherwise a whole class of
/// crashes in cubits that await network calls.
extension SafeEmit<S> on BlocBase<S> {
  void safeEmit(S state) {
    if (!isClosed) {
      // ignore: invalid_use_of_visible_for_testing_member, invalid_use_of_protected_member
      emit(state);
    }
  }
}
