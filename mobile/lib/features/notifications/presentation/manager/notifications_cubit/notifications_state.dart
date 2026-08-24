part of 'notifications_cubit.dart';

sealed class NotificationsState extends Equatable {
  const NotificationsState();

  @override
  List<Object?> get props => [];
}

final class NotificationsLoading extends NotificationsState {
  const NotificationsLoading();
}

final class NotificationsEmpty extends NotificationsState {
  const NotificationsEmpty();
}

final class NotificationsLoaded extends NotificationsState {
  const NotificationsLoaded(this.notifications);

  final List<NotificationModel> notifications;

  @override
  List<Object?> get props => [notifications];
}

final class NotificationsFailed extends NotificationsState {
  const NotificationsFailed(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}
