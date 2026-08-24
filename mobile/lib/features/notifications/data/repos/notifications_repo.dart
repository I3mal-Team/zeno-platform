import 'package:dartz/dartz.dart';

import '../../../../core/errors/failure.dart';
import '../models/notification_model.dart';

abstract interface class NotificationsRepo {
  Future<Either<Failure, List<NotificationModel>>> list();

  Future<Either<Failure, int>> unreadCount();

  Future<Either<Failure, Unit>> markRead(String id);

  Future<Either<Failure, Unit>> markAllRead();

  Future<Either<Failure, Unit>> registerDevice(String token, String platform);
}
