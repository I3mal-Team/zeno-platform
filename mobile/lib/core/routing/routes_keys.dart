/// Every path string. Features must never inline a route path.
abstract final class RoutesKeys {
  static const splash = '/';
  static const onboarding = '/onboarding';
  static const rolePicker = '/role';
  static const phone = '/phone';
  static const otp = '/otp';
  static const registerCandidate = '/register/candidate';
  static const registerEmployer = '/register/employer';

  static const browse = '/browse';
  static const search = '/search';
  static const nearby = '/nearby';
  static const applications = '/applications';
  static const messages = '/messages';
  static const profile = '/profile';
  static const notifications = '/notifications';
  static const jobDetail = '/jobs/:id';

  static const employerJobs = '/employer/jobs';
  static const employerApplicants = '/employer/applicants';
  static const employerMessages = '/employer/messages';
  static const employerAccount = '/employer/account';
  static const employerPostJob = '/employer/jobs/new';
  static const employerJobApplicantsPath = '/employer/jobs/:uuid/applicants';
  static const employerApplicantPath = '/employer/applicants/:id';

  static String job(String id) => '/jobs/$id';

  static String employerJobApplicants(String uuid) =>
      '/employer/jobs/$uuid/applicants';

  static String employerApplicant(int id) => '/employer/applicants/$id';
}
