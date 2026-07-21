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
  static const employerProfile = '/profile/employer';

  static const jobs = '/jobs';
  static const jobsNearby = '/jobs/nearby';
  static String job(String id) => '/jobs/$id';

  static const applications = '/applications';
  static String application(String id) => '/applications/$id';

  static const conversations = '/conversations';
  static String messages(String conversationId) =>
      '/conversations/$conversationId/messages';

  static const notifications = '/notifications';
  static const deviceTokens = '/notifications/devices';

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
