import 'package:equatable/equatable.dart';

/// One field of a job's employer-authored application form, as it arrives on
/// the job detail payload. The candidate's apply screen renders these.
class ApplicationFieldModel extends Equatable {
  const ApplicationFieldModel({
    required this.key,
    required this.label,
    required this.type,
    required this.required,
    this.options = const [],
  });

  factory ApplicationFieldModel.fromJson(Map<String, dynamic> json) {
    return ApplicationFieldModel(
      key: json['key'] as String,
      label: json['label'] as String? ?? '',
      type: json['type'] as String? ?? 'text',
      required: json['required'] as bool? ?? false,
      options: (json['options'] as List<dynamic>? ?? []).cast<String>(),
    );
  }

  final String key;
  final String label;
  final String type;
  final bool required;
  final List<String> options;

  bool get isUpload => type == 'file' || type == 'image';
  bool get isImage => type == 'image';

  @override
  List<Object?> get props => [key, label, type, required, options];
}
