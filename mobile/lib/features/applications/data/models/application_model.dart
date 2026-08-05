import 'package:equatable/equatable.dart';

class ApplicationModel extends Equatable {
  const ApplicationModel({
    required this.reference,
    required this.status,
    required this.statusLabel,
    this.appliedAt,
    this.jobTitle,
    this.jobSlug,
    this.organizationName,
    this.categoryCode = 'other',
    this.conversationUuid,
  });

  factory ApplicationModel.fromJson(Map<String, dynamic> json) {
    final job = json['job'];
    final category = job is Map<String, dynamic> ? job['category'] : null;

    return ApplicationModel(
      reference: json['reference'] as String,
      status: json['status'] as String,
      statusLabel: json['status_label'] as String? ?? '',
      appliedAt: json['applied_at'] as String?,
      conversationUuid: json['conversation_uuid'] as String?,
      jobTitle: job is Map<String, dynamic> ? job['title'] as String? : null,
      jobSlug: job is Map<String, dynamic> ? job['slug'] as String? : null,
      organizationName: job is Map<String, dynamic>
          ? job['organization'] as String?
          : null,
      categoryCode: category is Map<String, dynamic>
          ? category['code'] as String? ?? 'other'
          : 'other',
    );
  }

  final String reference;
  final String status;
  final String statusLabel;
  final String? appliedAt;
  final String? jobTitle;
  final String? jobSlug;
  final String? organizationName;
  final String categoryCode;

  /// Present once accepted — lets "تواصل" open the thread directly.
  final String? conversationUuid;

  bool get isDecidable => status == 'submitted' || status == 'review';

  @override
  List<Object?> get props => [reference, status, conversationUuid];
}
