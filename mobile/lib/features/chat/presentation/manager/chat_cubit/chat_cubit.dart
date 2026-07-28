import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../../../core/utils/uuid.dart';
import '../../../data/models/message_model.dart';
import '../../../data/repos/chat_repo.dart';

part 'chat_state.dart';

class ChatCubit extends Cubit<ChatState> {
  ChatCubit(this._repo, this.conversationUuid) : super(const ChatLoading());

  final ChatRepo _repo;
  final String conversationUuid;

  /// The thread, kept as a field so the view always renders from one source
  /// while transient states (send failure) still flow through the stream.
  List<MessageModel> messages = const [];

  Future<void> load() async {
    safeEmit(const ChatLoading());

    final result = await _repo.fetchMessages(conversationUuid);

    result.fold(
      (failure) => safeEmit(ChatFailed(failure)),
      (list) {
        messages = list;
        safeEmit(ChatReady(messages));
      },
    );
  }

  Future<void> send(String input) async {
    final body = input.trim();
    if (body.isEmpty) return;

    // Show it immediately, then reconcile with the server's copy.
    final pending = MessageModel(
      uuid: 'pending-${generateUuidV4()}',
      body: body,
      type: 'text',
      isMine: true,
      createdAt: null,
    );
    messages = [...messages, pending];
    safeEmit(ChatReady(messages));

    final result = await _repo.sendMessage(
      conversationUuid,
      body: body,
      clientUuid: generateUuidV4(),
    );

    result.fold(
      (failure) {
        messages = messages.where((m) => m.uuid != pending.uuid).toList();
        safeEmit(ChatSendFailed(failure, messages));
      },
      (saved) {
        messages = [
          ...messages.where((m) => m.uuid != pending.uuid),
          saved,
        ];
        safeEmit(ChatReady(messages));
      },
    );
  }
}
