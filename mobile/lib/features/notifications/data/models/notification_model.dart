import 'package:equatable/equatable.dart';

class NotificationModel extends Equatable {
  const NotificationModel({
    required this.id,
    required this.type,
    required this.data,
    required this.isRead,
    this.createdAt,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    return NotificationModel(
      id: json['id'] as String,
      type: json['type'] as String? ?? 'generic',
      data: json['data'] as Map<String, dynamic>? ?? const {},
      isRead: json['is_read'] as bool? ?? false,
      createdAt: json['created_at'] as String?,
    );
  }

  final String id;
  final String type;
  final Map<String, dynamic> data;
  final bool isRead;
  final String? createdAt;

  String get title => switch (type) {
    'application_submitted' => 'طلب جديد على وظيفتك',
    'application_decision' =>
      (data['accepted'] == true) ? 'تم قبول طلبك' : 'تحديث على طلبك',
    'job_changed' => 'تغيير في وظيفة قدّمت عليها',
    _ => 'إشعار',
  };

  String get body {
    final job = data['job_title'] as String?;
    return switch (type) {
      'application_submitted' => 'وظيفة «${job ?? ''}» — راجع المتقدّم.',
      'application_decision' =>
        (data['accepted'] == true)
            ? 'تم قبولك في «${job ?? ''}» — ستصلك طريقة التواصل.'
            : 'تم تحديث حالة طلبك على «${job ?? ''}».',
      'job_changed' => 'تغيّرت بيانات وظيفة «${job ?? ''}».',
      _ => '',
    };
  }

  @override
  List<Object?> get props => [id, isRead];
}
