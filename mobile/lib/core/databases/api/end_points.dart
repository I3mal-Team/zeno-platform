/// Every path and base URL. Features must never hardcode a URL.
abstract final class EndPoints {
  static const _dev = 'http://10.0.2.2:8000/api/v1';
  static const _staging = 'https://staging.zeno.sa/api/v1';
  static const _production = 'https://api.zeno.sa/api/v1';

  /// Resolved per access so switching environment at runtime takes effect
  /// without a restart.
  static String get baseUrl => switch (AppEnvironment.current) {
    AppEnvironment.dev => _dev,
    AppEnvironment.staging => _staging,
    AppEnvironment.production => _production,
  };

  static const requestOtp = '/auth/otp/request';
  static const verifyOtp = '/auth/otp/verify';
  static const refreshSession = '/auth/refresh';
  static const logout = '/auth/logout';
  static const logoutAll = '/auth/logout-all';
  static const currentUser = '/auth/me';

  static const candidateProfile = '/profile/candidate';
  static const candidateAvatar = '/profile/candidate/avatar';
  static const employerProfile = '/profile/employer';
  static const employerLogo = '/profile/employer/logo';

  static const jobs = '/jobs';
  static const jobsNearby = '/jobs/nearby';
  static String job(String id) => '/jobs/$id';

  static const jobForm = '/catalog/job-form';
  static const employerJobs = '/employer/jobs';
  static String employerJob(String uuid) => '/employer/jobs/$uuid';
  static String employerJobPause(String uuid) => '/employer/jobs/$uuid/pause';
  static String employerJobResume(String uuid) => '/employer/jobs/$uuid/resume';
  static String employerJobClose(String uuid) => '/employer/jobs/$uuid/close';
  static const employerApplications = '/employer/applications';
  static String employerJobApplicants(String uuid) =>
      '/employer/jobs/$uuid/applications';
  static String employerApplicant(int id) => '/employer/applications/$id';
  static String employerApplicantAccept(int id) =>
      '/employer/applications/$id/accept';
  static String employerApplicantReject(int id) =>
      '/employer/applications/$id/reject';

  static const applications = '/applications';
  static String application(String id) => '/applications/$id';
  static String applyToJob(String slug) => '/jobs/$slug/apply';
  static String withdrawApplication(String reference) =>
      '/applications/$reference/withdraw';

  static const conversations = '/conversations';
  static String messages(String conversationId) =>
      '/conversations/$conversationId/messages';
  static String whatsapp(String conversationId) =>
      '/conversations/$conversationId/whatsapp';

  /// Token-authenticated private-channel handshake. It rides the normal API
  /// host (not the websocket one), so it is derived from [baseUrl].
  static String get broadcastingAuth => '$baseUrl/broadcasting/auth';

  // ── Reverb websocket (Pusher protocol) ─────────────────────────────────
  // Separate from [baseUrl] because the browser/app reach Reverb on its own
  // host-published port, which differs from the API's.
  static String get reverbHost => switch (AppEnvironment.current) {
    AppEnvironment.dev => '10.0.2.2',
    AppEnvironment.staging => 'staging.zeno.sa',
    AppEnvironment.production => 'api.zeno.sa',
  };

  static int get reverbPort => switch (AppEnvironment.current) {
    AppEnvironment.dev => 58080,
    AppEnvironment.staging => 443,
    AppEnvironment.production => 443,
  };

  static bool get reverbUseTls => AppEnvironment.current != AppEnvironment.dev;

  static String get reverbKey => switch (AppEnvironment.current) {
    AppEnvironment.dev => 'local',
    AppEnvironment.staging => 'zeno-staging',
    AppEnvironment.production => 'zeno',
  };

  static const notifications = '/notifications';
  static const notificationsUnread = '/notifications/unread-count';
  static const notificationsReadAll = '/notifications/read-all';
  static String notificationRead(String id) => '/notifications/$id/read';
  static const deviceTokens = '/notifications/devices';

  static const countries = '/countries';
  static const categories = '/categories';
  static const cities = '/cities';
  static String districts(String cityId) => '/cities/$cityId/districts';
}

enum AppEnvironment {
  dev,
  staging,
  production;

  static AppEnvironment current = AppEnvironment.dev;
}
