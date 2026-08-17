/// Every path and base URL. Features must never hardcode a URL.
abstract final class EndPoints {
  // 10.0.2.2 reaches the host from the Android emulator. Override for a real
  // device on the LAN: --dart-define=API_BASE_URL=http://<host-ip>:8000/api/v1
  static const _dev = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api/v1',
  );
  static const _staging = 'https://staging.zeno.sa/api/v1';
  static const _production = 'https://api.zeno.sa/api/v1';

  /// Resolved per access so switching environment at runtime takes effect
  /// without a restart.
  static String get baseUrl => switch (AppEnvironment.current) {
    AppEnvironment.dev => _dev,
    AppEnvironment.staging => _staging,
    AppEnvironment.production => _production,
  };

  // ── Public web pages ───────────────────────────────────────────────────
  // The marketing site, which answers on its own host separate from the API.
  // The legal pages live here because both stores require them to be readable
  // without installing the app.
  static const _siteDev = String.fromEnvironment(
    'SITE_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000',
  );

  static String get siteUrl => switch (AppEnvironment.current) {
    AppEnvironment.dev => _siteDev,
    AppEnvironment.staging => 'https://staging.zeno.sa',
    AppEnvironment.production => 'https://zeno.sa',
  };

  static String get terms => '$siteUrl/terms';

  /// Its own page, not a section of the terms: this is the URL submitted to
  /// both stores, and they want it to be the privacy policy and nothing else.
  static String get privacy => '$siteUrl/privacy';

  static const requestOtp = '/auth/otp/request';
  static const verifyOtp = '/auth/otp/verify';
  static const refreshSession = '/auth/refresh';
  static const logout = '/auth/logout';
  static const logoutAll = '/auth/logout-all';
  static const currentUser = '/auth/me';

  /// In-app account closure. Both stores require this path to exist and to be
  /// reachable without contacting support.
  static const deleteAccount = '/auth/account';

  static const candidateProfile = '/profile/candidate';
  static const candidateAvatar = '/profile/candidate/avatar';
  static const candidateResume = '/profile/candidate/resume';
  static const employerProfile = '/profile/employer';
  static const employerLogo = '/profile/employer/logo';

  static const jobs = '/jobs';
  static const jobsNearby = '/jobs/nearby';
  static const savedJobs = '/saved-jobs';
  static const jobAlerts = '/job-alerts';
  static String job(String id) => '/jobs/$id';
  static String saveJob(String slug) => '/jobs/$slug/save';
  static String jobAlert(int id) => '/job-alerts/$id';

  static const jobForm = '/job-form';
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
  static String employerApplicantShortlist(int id) =>
      '/employer/applications/$id/shortlist';
  static String employerApplicantNote(int id) =>
      '/employer/applications/$id/note';

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
  // 10.0.2.2 reaches the host from the Android emulator. On a real device pass
  // the Mac's LAN IP: --dart-define=REVERB_HOST=<host-ip> (same host as the API).
  static const _reverbHostDev = String.fromEnvironment(
    'REVERB_HOST',
    defaultValue: '10.0.2.2',
  );

  static String get reverbHost => switch (AppEnvironment.current) {
    AppEnvironment.dev => _reverbHostDev,
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

  static const billingPlans = '/billing/plans';
  static const billingSubscription = '/billing/subscription';

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
