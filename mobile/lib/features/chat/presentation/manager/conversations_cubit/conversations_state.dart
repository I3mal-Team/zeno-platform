part of 'conversations_cubit.dart';

sealed class ConversationsState extends Equatable {
  const ConversationsState();

  @override
  List<Object?> get props => [];
}

final class ConversationsLoading extends ConversationsState {
  const ConversationsLoading();
}

final class ConversationsEmpty extends ConversationsState {
  const ConversationsEmpty();
}

final class ConversationsLoaded extends ConversationsState {
  const ConversationsLoaded(this.conversations);

  final List<ConversationModel> conversations;

  @override
  List<Object?> get props => [conversations];
}

final class ConversationsFailed extends ConversationsState {
  const ConversationsFailed(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}
