import 'package:dartz/dartz.dart';
import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../../../core/realtime/realtime_service.dart';
import '../../../../../core/utils/uuid.dart';
import '../../../data/models/message_model.dart';
import '../../../data/models/whatsapp_handoff_model.dart';
import '../../../data/repos/chat_repo.dart';

part 'chat_state.dart';

class ChatCubit extends Cubit<ChatState> {
  ChatCubit(
    this._repo,
    this._realtime,
    this.conversationUuid, {
    required this.currentUserId,
  }) : super(const ChatLoading());

  final ChatRepo _repo;
  final RealtimeService _realtime;
  final String conversationUuid;

  /// The signed-in user's public id, used to decide which side a live message
  /// sits on — the socket payload carries the sender's id, not `is_mine`.
  final String currentUserId;

  /// The thread, kept as a field so the view always renders from one source
  /// while transient states (send failure) still flow through the stream.
  List<MessageModel> messages = const [];

  RealtimeChannel? _channel;

  /// Records the handoff and returns the deep link. Deliberately not a state
  /// transition: leaving for WhatsApp does not change the thread on screen.
  Future<Either<Failure, WhatsAppHandoffModel>> openWhatsApp() {
    return _repo.openWhatsApp(conversationUuid);
  }

  Future<void> load() async {
    safeEmit(const ChatLoading());

    final result = await _repo.fetchMessages(conversationUuid);

    result.fold(
      (failure) => safeEmit(ChatFailed(failure)),
      (list) {
        messages = list;
        safeEmit(ChatReady(messages));
        _listen();
      },
    );
  }

  /// Opens the live feed once the thread is on screen. Best-effort: if the
  /// socket can't be reached the thread still works over REST.
  Future<void> _listen() async {
    if (_channel != null) return;

    try {
      _channel = await _realtime.subscribePrivate(
        'conversation.$conversationUuid',
        event: 'message.sent',
        onEvent: _onLiveMessage,
      );
    } catch (_) {
      // Realtime is an enhancement, never a requirement.
    }
  }

  void _onLiveMessage(Map<String, dynamic> data) {
    final uuid = data['uuid'] as String?;
    if (uuid == null) return;

    _upsert(
      MessageModel(
        uuid: uuid,
        body: data['body'] as String?,
        type: data['type'] as String? ?? 'text',
        isMine: data['sender_id'] == currentUserId,
        createdAt: data['created_at'] as String?,
      ),
    );
    safeEmit(ChatReady(messages));
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
        // Drop the optimistic copy, then upsert so the socket echo of this same
        // message (which shares its uuid) doesn't land a second time.
        messages = messages.where((m) => m.uuid != pending.uuid).toList();
        _upsert(saved);
        safeEmit(ChatReady(messages));
      },
    );
  }

  /// Replaces a message with the same uuid or appends it. This is the guard
  /// that keeps a live echo of our own send — or a resent duplicate — from
  /// showing twice. Always assigns a new list so [ChatReady] compares unequal.
  void _upsert(MessageModel message) {
    final index = messages.indexWhere((m) => m.uuid == message.uuid);
    if (index < 0) {
      messages = [...messages, message];
      return;
    }

    final next = [...messages];
    next[index] = message;
    messages = next;
  }

  @override
  Future<void> close() {
    _channel?.unsubscribe();
    return super.close();
  }
}
