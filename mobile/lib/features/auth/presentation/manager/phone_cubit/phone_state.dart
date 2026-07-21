part of 'phone_cubit.dart';

sealed class PhoneState extends Equatable {
  const PhoneState();

  @override
  List<Object?> get props => [];
}

final class PhoneInitial extends PhoneState {
  const PhoneInitial();
}

final class PhoneSubmitting extends PhoneState {
  const PhoneSubmitting();
}

final class PhoneCodeSent extends PhoneState {
  const PhoneCodeSent({required this.phone, required this.expiresInSeconds});

  final String phone;
  final int expiresInSeconds;

  @override
  List<Object?> get props => [phone, expiresInSeconds];
}

final class PhoneError extends PhoneState {
  const PhoneError(this.failure);

  final Failure failure;

  @override
  List<Object?> get props => [failure];
}
