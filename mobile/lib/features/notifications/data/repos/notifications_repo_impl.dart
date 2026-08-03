import 'package:dartz/dartz.dart';

import '../../../../core/databases/api/api_consumer.dart';
import '../../../../core/databases/api/end_points.dart';
import '../../../../core/databases/api/handle_request.dart';
import '../../../../core/errors/failure.dart';
import '../models/notification_model.dart';
import 'notifications_repo.dart';

class NotificationsRepoImpl implements NotificationsRepo {
  const NotificationsRepoImpl(this._api, this._handle);

  final ApiConsumer _api;
  final RequestHandler _handle;

  @override
  Future<Either<Failure, List<NotificationModel>>> list() {
    return _handle(
      () => _api.get(EndPoints.notifications),
      (data) => (data as List<dynamic>)
          .map((e) => NotificationModel.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  @override
  Future<Either<Failure, int>> unreadCount() {
    return _handle(
      () => _api.get(EndPoints.notificationsUnread),
      (data) => (data as Map<String, dynamic>)['unread'] as int? ?? 0,
    );
  }

  @override
  Future<Either<Failure, Unit>> markRead(String id) {
    return _handle(
      () => _api.post(EndPoints.notificationRead(id)),
      (_) => unit,
    );
  }

  @override
  Future<Either<Failure, Unit>> markAllRead() {
    return _handle(
      () => _api.post(EndPoints.notificationsReadAll),
      (_) => unit,
    );
  }

  @override
  Future<Either<Failure, Unit>> registerDevice(String token, String platform) {
    return _handle(
      () => _api.post(
        EndPoints.deviceTokens,
        body: {'token': token, 'platform': platform},
      ),
      (_) => unit,
    );
  }
}
