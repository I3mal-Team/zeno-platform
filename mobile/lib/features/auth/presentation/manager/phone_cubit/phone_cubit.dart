import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../../../core/params/auth_params.dart';
import '../../../data/models/country_model.dart';
import '../../../data/repos/auth_repo.dart';

part 'phone_state.dart';

class PhoneCubit extends Cubit<PhoneState> {
  PhoneCubit(this._repo) : super(const PhoneInitial()) {
    loadCountries();
  }

  final AuthRepo _repo;

  /// Not part of the state: the field rebuilds itself as the user types.
  String phone = '';

  List<CountryModel> countries = const [];
  CountryModel? selectedCountry;

  /// The ISO the server needs; falls back to Saudi if the list never loaded.
  String get countryIso => selectedCountry?.iso2 ?? 'SA';

  bool get canSubmit => phone.length >= 7;

  Future<void> loadCountries() async {
    final result = await _repo.fetchCountries();

    // A failure is silent: the field stays usable and the server still
    // defaults to Saudi, so sign-in is never blocked by the catalog call.
    result.fold((_) {}, (list) {
      countries = list;
      selectedCountry ??= list.isNotEmpty ? list.first : null;
      safeEmit(PhoneCountriesLoaded(countries: list, selected: selectedCountry));
    });
  }

  void onCountryChanged(CountryModel country) {
    selectedCountry = country;
    safeEmit(PhoneCountriesLoaded(countries: countries, selected: country));
  }

  void onPhoneChanged(String value) {
    phone = value;
  }

  Future<void> requestCode() async {
    if (!canSubmit) return;

    safeEmit(const PhoneSubmitting());

    final result = await _repo.requestOtp(
      RequestOtpParam(phone: phone, country: countryIso),
    );

    safeEmit(
      result.fold(
        PhoneError.new,
        (expiresIn) => PhoneCodeSent(
          phone: phone,
          country: countryIso,
          expiresInSeconds: expiresIn,
        ),
      ),
    );
  }
}
