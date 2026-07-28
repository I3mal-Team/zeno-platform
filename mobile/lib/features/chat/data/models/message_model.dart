import 'package:equatable/equatable.dart';

/// A single chat message. [isMine] is resolved server-side, so the UI never
/// compares ids to decide which side a bubble sits on.
class MessageModel extends Equatable {
  const MessageModel({
    required this.uuid,
    required this.body,
    required this.type,
    required this.isMine,
    required this.createdAt,
  });

  factory MessageModel.fromJson(Map<String, dynamic> json) {
    return MessageModel(
      uuid: json['uuid'] as String,
      body: json['body'] as String?,
      type: json['type'] as String? ?? 'text',
      isMine: json['is_mine'] as bool? ?? false,
      createdAt: json['created_at'] as String?,
    );
  }

  final String uuid;
  final String? body;
  final String type;
  final bool isMine;
  final String? createdAt;

  @override
  List<Object?> get props => [uuid, body, type, isMine, createdAt];
}
