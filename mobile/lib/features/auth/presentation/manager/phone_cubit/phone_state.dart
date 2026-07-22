part of 'phone_cubit.dart';

sealed class PhoneState extends Equatable {
  const PhoneState();

  @override
  List<Object?> get props => [];
}

final class PhoneInitial extends PhoneState {
  const PhoneInitial();
}

/// Emitted after the dial codes load and on every country change, so the
/// selector and the field hint rebuild from the freshly picked country.
final class PhoneCountriesLoaded extends PhoneState {
  const PhoneCountriesLoaded({required this.countries, required this.selected});

  final List<CountryModel> countries;
  final CountryModel? selected;

  @override
  List<Object?> get props => [countries, selected];
}

final class PhoneSubmitting extends PhoneState {
  const PhoneSubmitting();
}

final class PhoneCodeSent extends PhoneState {
  const PhoneCodeSent({
    required this.phone,
    required this.country,
    required this.expiresInSeconds,
  });

  final String phone;
  final String country;
  final int expiresInSeconds;

  @override
  List<Object?> get props => [phone, country, expiresInSeconds];
}

final class PhoneError extends PhoneState {
  const PhoneError(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}
