import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../../../core/params/auth_params.dart';
import '../../../data/repos/auth_repo.dart';

part 'phone_state.dart';

class PhoneCubit extends Cubit<PhoneState> {
  PhoneCubit(this._repo) : super(const PhoneInitial());

  final AuthRepo _repo;

  /// Not part of the state: the field rebuilds itself as the user types.
  String phone = '';

  bool get canSubmit => phone.length >= 9;

  void onPhoneChanged(String value) {
    phone = value;
  }

  Future<void> requestCode() async {
    if (!canSubmit) return;

    safeEmit(const PhoneSubmitting());

    final result = await _repo.requestOtp(RequestOtpParam(phone: phone));

    safeEmit(
      result.fold(
        PhoneError.new,
        (expiresIn) => PhoneCodeSent(phone: phone, expiresInSeconds: expiresIn),
      ),
    );
  }
}
