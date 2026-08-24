part of 'otp_cubit.dart';

sealed class OtpState extends Equatable {
  const OtpState();

  @override
  List<Object?> get props => [];
}

final class OtpInitial extends OtpState {
  const OtpInitial();
}

final class OtpVerifying extends OtpState {
  const OtpVerifying();
}

final class OtpVerified extends OtpState {
  const OtpVerified(this.session);

  final AuthSessionModel session;

  @override
  List<Object?> get props => [session];
}

final class OtpResent extends OtpState {
  const OtpResent(this.expiresInSeconds);

  final int expiresInSeconds;

  @override
  List<Object?> get props => [expiresInSeconds];
}

final class OtpError extends OtpState {
  const OtpError(this.failure);

  final Failure failure;

  /// The server sends the remaining wait so the UI does not guess it.
  int? get retryAfterSeconds => failure.details?['retry_after_seconds'] as int?;

  @override
  List<Object?> get props => [failure];
}
