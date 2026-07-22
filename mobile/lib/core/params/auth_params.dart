class RequestOtpParam {
  const RequestOtpParam({required this.phone, required this.country});

  final String phone;

  /// ISO-3166 alpha-2 of the chosen dial code, so the server can normalise.
  final String country;

  Map<String, dynamic> toJson() => {'phone': phone, 'country': country};
}

class VerifyOtpParam {
  const VerifyOtpParam({
    required this.phone,
    required this.country,
    required this.code,
    required this.role,
    this.deviceName,
  });

  final String phone;
  final String country;
  final String code;
  final String role;
  final String? deviceName;

  Map<String, dynamic> toJson() => {
    'phone': phone,
    'country': country,
    'code': code,
    'role': role,
    if (deviceName != null) 'device_name': deviceName,
  };
}
