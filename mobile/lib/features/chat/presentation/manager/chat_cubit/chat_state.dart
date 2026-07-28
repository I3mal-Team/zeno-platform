part of 'chat_cubit.dart';

sealed class ChatState extends Equatable {
  const ChatState();

  @override
  List<Object?> get props => [];
}

final class ChatLoading extends ChatState {
  const ChatLoading();
}

/// The thread is displayable. Carries the messages so [Equatable] emits a new
/// state whenever the list changes.
final class ChatReady extends ChatState {
  const ChatReady(this.messages);

  final List<MessageModel> messages;

  @override
  List<Object?> get props => [messages];
}

final class ChatFailed extends ChatState {
  const ChatFailed(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}

/// A send failed — the view shows a toast but keeps the (rolled-back) thread.
final class ChatSendFailed extends ChatState {
  const ChatSendFailed(this.failure, this.messages);

  final Failure failure;
  final List<MessageModel> messages;

  @override
  List<Object?> get props => [failure, messages];
}
