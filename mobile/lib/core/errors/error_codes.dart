/// The contract between this app and the API. Every constant here has a
/// matching domain exception on the server; adding one side only is a defect.
abstract final class ErrorCodes {
  static const unknown = 'UNKNOWN_ERROR';
  static const validationFailed = 'VALIDATION_FAILED';
  static const networkUnavailable = 'NETWORK_UNAVAILABLE';
  static const cacheError = 'CACHE_ERROR';
  static const notFound = 'NOT_FOUND';
  static const forbidden = 'FORBIDDEN';
  static const rateLimited = 'RATE_LIMITED';

  static const sessionExpired = 'SESSION_EXPIRED';
  static const forceUpdate = 'FORCE_UPDATE';
  static const maintenanceMode = 'MAINTENANCE_MODE';
  static const accountSuspended = 'ACCOUNT_SUSPENDED';

  static const otpInvalid = 'OTP_INVALID';
  static const otpExpired = 'OTP_EXPIRED';
  static const otpMaxAttempts = 'OTP_MAX_ATTEMPTS';
  static const otpResendCooldown = 'OTP_RESEND_COOLDOWN';
  static const phoneInvalid = 'PHONE_INVALID';

  static const jobNotActive = 'JOB_NOT_ACTIVE';
  static const jobExpired = 'JOB_EXPIRED';
  static const jobPaused = 'JOB_PAUSED';
  static const jobClosed = 'JOB_CLOSED';

  static const applicationAlreadyExists = 'APPLICATION_ALREADY_EXISTS';
  static const applicationNotFound = 'APPLICATION_NOT_FOUND';
  static const applicationAlreadyDecided = 'APPLICATION_ALREADY_DECIDED';
  static const vacanciesExhausted = 'VACANCIES_EXHAUSTED';

  static const subscriptionRequired = 'SUBSCRIPTION_REQUIRED';
  static const subscriptionExpired = 'SUBSCRIPTION_EXPIRED';

  static const organizationNotVerified = 'ORGANIZATION_NOT_VERIFIED';
  static const organizationMissing = 'ORGANIZATION_MISSING';

  static const conversationNotAllowed = 'CONVERSATION_NOT_ALLOWED';
  static const messageEmpty = 'MESSAGE_EMPTY';

  /// Handled once, centrally. Screens must not react to these individually.
  static const globalHandled = {
    sessionExpired,
    forceUpdate,
    maintenanceMode,
    accountSuspended,
  };
}
