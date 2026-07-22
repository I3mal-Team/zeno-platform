import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../data/models/notification_model.dart';
import '../../../data/repos/notifications_repo.dart';

part 'notifications_state.dart';

class NotificationsCubit extends Cubit<NotificationsState> {
  NotificationsCubit(this._repo) : super(const NotificationsLoading());

  final NotificationsRepo _repo;

  Future<void> load() async {
    safeEmit(const NotificationsLoading());

    final result = await _repo.list();

    safeEmit(
      result.fold(NotificationsFailed.new, (list) {
        return list.isEmpty
            ? const NotificationsEmpty()
            : NotificationsLoaded(list);
      }),
    );
  }

  Future<void> markAllRead() async {
    final result = await _repo.markAllRead();
    await result.fold((_) async {}, (_) => load());
  }

  Future<void> markRead(String id) async {
    final result = await _repo.markRead(id);
    await result.fold((_) async {}, (_) => load());
  }
}
