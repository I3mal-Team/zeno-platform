import 'package:dartz/dartz.dart';

import '../../../../core/errors/failure.dart';
import '../models/conversation_model.dart';
import '../models/message_model.dart';

abstract interface class ChatRepo {
  Future<Either<Failure, List<ConversationModel>>> fetchConversations();

  Future<Either<Failure, List<MessageModel>>> fetchMessages(String uuid);

  /// [clientUuid] is the idempotency key so a retried send never duplicates.
  Future<Either<Failure, MessageModel>> sendMessage(
    String uuid, {
    required String body,
    required String clientUuid,
  });
}
