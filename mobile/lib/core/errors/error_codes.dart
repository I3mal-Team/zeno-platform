/// أكواد الأخطاء — **العقد الفعلي بين الموبايل والخادم**.
///
/// كل كود هنا يجب أن يقابله ثابت في
/// `backend/app/Exceptions/Domain/` وأن يظهر في `contracts/openapi.yaml`.
///
/// **إضافة كود هنا بلا مقابل في الخادم خطأ، والعكس صحيح.**
/// راجع `docs/18-architecture-and-stack.md §1.3`.
abstract final class ErrorCodes {
  // ── عام ──────────────────────────────────────────
  static const unknown = 'UNKNOWN_ERROR';
  static const validationFailed = 'VALIDATION_FAILED';
  static const networkUnavailable = 'NETWORK_UNAVAILABLE';
  static const cacheError = 'CACHE_ERROR';
  static const notFound = 'NOT_FOUND';
  static const forbidden = 'FORBIDDEN';
  static const rateLimited = 'RATE_LIMITED';

  // ── تفاعلات عامة (تُعالَج مركزياً في handleRequest) ──
  // هذه الأربعة لا تُعالج في الشاشات — لها ردّ فعل عام واحد.
  static const sessionExpired = 'SESSION_EXPIRED';
  static const forceUpdate = 'FORCE_UPDATE';
  static const maintenanceMode = 'MAINTENANCE_MODE';
  static const accountSuspended = 'ACCOUNT_SUSPENDED';

  // ── المصادقة ─────────────────────────────────────
  static const otpInvalid = 'OTP_INVALID';
  static const otpExpired = 'OTP_EXPIRED';
  static const otpMaxAttempts = 'OTP_MAX_ATTEMPTS';
  static const otpResendCooldown = 'OTP_RESEND_COOLDOWN';
  static const phoneInvalid = 'PHONE_INVALID';

  // ── الوظائف ──────────────────────────────────────
  static const jobNotActive = 'JOB_NOT_ACTIVE';
  static const jobExpired = 'JOB_EXPIRED';
  static const jobPaused = 'JOB_PAUSED';
  static const jobClosed = 'JOB_CLOSED';

  // ── الطلبات ──────────────────────────────────────
  /// المرشّح سبق أن قدّم على هذه الوظيفة — ينتج عن انتهاك الفهرس الفريد
  /// الجزئي في `applications`. راجع `docs/10-domain-model §5.1`.
  static const applicationAlreadyExists = 'APPLICATION_ALREADY_EXISTS';
  static const applicationNotFound = 'APPLICATION_NOT_FOUND';
  static const applicationAlreadyDecided = 'APPLICATION_ALREADY_DECIDED';

  /// اكتملت الشواغر أثناء المعالجة — نتيجة قفل صف الوظيفة.
  static const vacanciesExhausted = 'VACANCIES_EXHAUSTED';

  // ── الاشتراك (D-01 — بوابة على التقديم لا التسجيل) ──
  static const subscriptionRequired = 'SUBSCRIPTION_REQUIRED';
  static const subscriptionExpired = 'SUBSCRIPTION_EXPIRED';

  // ── المنشأة ──────────────────────────────────────
  static const organizationNotVerified = 'ORGANIZATION_NOT_VERIFIED';
  static const organizationMissing = 'ORGANIZATION_MISSING';

  // ── المحادثات ────────────────────────────────────
  static const conversationNotAllowed = 'CONVERSATION_NOT_ALLOWED';
  static const messageEmpty = 'MESSAGE_EMPTY';

  /// الأكواد التي لها ردّ فعل عام مركزي — لا تُعالَج داخل الشاشات.
  static const globalHandled = {
    sessionExpired,
    forceUpdate,
    maintenanceMode,
    accountSuspended,
  };
}
