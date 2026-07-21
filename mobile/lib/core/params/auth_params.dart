class RequestOtpParam {
  const RequestOtpParam({required this.phone});

  final String phone;

  Map<String, dynamic> toJson() => {'phone': phone};
}

class VerifyOtpParam {
  const VerifyOtpParam({
    required this.phone,
    required this.code,
    required this.role,
    this.deviceName,
  });

  final String phone;
  final String code;
  final String role;
  final String? deviceName;

  Map<String, dynamic> toJson() => {
    'phone': phone,
    'code': code,
    'role': role,
    if (deviceName != null) 'device_name': deviceName,
  };
}
