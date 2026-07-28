import 'package:equatable/equatable.dart';

/// One thread in the messages list. The title is already resolved server-side
/// to the *other* participant from the viewer's perspective.
class ConversationModel extends Equatable {
  const ConversationModel({
    required this.uuid,
    required this.title,
    required this.jobTitle,
    required this.status,
    required this.lastMessage,
    required this.lastMessageAt,
  });

  factory ConversationModel.fromJson(Map<String, dynamic> json) {
    return ConversationModel(
      uuid: json['uuid'] as String,
      title: json['title'] as String? ?? '',
      jobTitle: json['job_title'] as String?,
      status: json['status'] as String? ?? 'active',
      lastMessage: json['last_message'] as String?,
      lastMessageAt: json['last_message_at'] as String?,
    );
  }

  final String uuid;
  final String title;
  final String? jobTitle;
  final String status;
  final String? lastMessage;
  final String? lastMessageAt;

  @override
  List<Object?> get props => [
    uuid,
    title,
    jobTitle,
    status,
    lastMessage,
    lastMessageAt,
  ];
}
