import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../../../core/params/auth_params.dart';
import '../../../data/models/auth_session_model.dart';
import '../../../data/repos/auth_repo.dart';

part 'otp_state.dart';

class OtpCubit extends Cubit<OtpState> {
  OtpCubit(
    this._repo, {
    required this.phone,
    required this.country,
    required this.role,
  }) : super(const OtpInitial());

  final AuthRepo _repo;
  final String phone;
  final String country;
  final String role;

  String code = '';

  bool get canSubmit => code.length == codeLength;

  static const codeLength = 4;

  void onCodeChanged(String value) {
    code = value;
  }

  Future<void> verify() async {
    if (!canSubmit) return;

    safeEmit(const OtpVerifying());

    final result = await _repo.verifyOtp(
      VerifyOtpParam(phone: phone, country: country, code: code, role: role),
    );

    safeEmit(result.fold(OtpError.new, OtpVerified.new));
  }

  Future<void> resend() async {
    final result = await _repo.requestOtp(
      RequestOtpParam(phone: phone, country: country),
    );

    safeEmit(result.fold(OtpError.new, OtpResent.new));
  }
}
