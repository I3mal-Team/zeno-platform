import 'package:dartz/dartz.dart';

import '../../../../core/databases/api/api_consumer.dart';
import '../../../../core/databases/api/end_points.dart';
import '../../../../core/databases/api/handle_request.dart';
import '../../../../core/errors/failure.dart';
import '../models/conversation_model.dart';
import '../models/message_model.dart';
import 'chat_repo.dart';

class ChatRepoImpl implements ChatRepo {
  const ChatRepoImpl(this._api, this._handle);

  final ApiConsumer _api;
  final RequestHandler _handle;

  @override
  Future<Either<Failure, List<ConversationModel>>> fetchConversations() {
    return _handle(
      () => _api.get(EndPoints.conversations),
      (data) => (data as List<dynamic>)
          .map((e) => ConversationModel.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  @override
  Future<Either<Failure, List<MessageModel>>> fetchMessages(String uuid) {
    return _handle(
      () => _api.get(EndPoints.messages(uuid)),
      (data) => (data as List<dynamic>)
          .map((e) => MessageModel.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  @override
  Future<Either<Failure, MessageModel>> sendMessage(
    String uuid, {
    required String body,
    required String clientUuid,
  }) {
    return _handle(
      () => _api.post(
        EndPoints.messages(uuid),
        body: {'body': body, 'client_uuid': clientUuid},
      ),
      (data) => MessageModel.fromJson(data as Map<String, dynamic>),
    );
  }
}
