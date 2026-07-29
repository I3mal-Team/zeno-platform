/// The deep link the server hands back after recording that a conversation is
/// moving to WhatsApp, together with the text it prefills.
class WhatsAppHandoffModel {
  const WhatsAppHandoffModel({
    required this.url,
    required this.phone,
    required this.message,
  });

  factory WhatsAppHandoffModel.fromJson(Map<String, dynamic> json) {
    return WhatsAppHandoffModel(
      url: json['url'] as String,
      phone: json['phone'] as String,
      message: json['message'] as String,
    );
  }

  final String url;
  final String phone;
  final String message;
}
