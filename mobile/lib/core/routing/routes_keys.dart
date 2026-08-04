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
  static const applicationSubmitted = '/applications/submitted';
  static const saved = '/saved';
  static const jobAlerts = '/job-alerts';
  static const messages = '/messages';
  static const chatPath = '/chat/:uuid';
  static const profile = '/profile';
  static const notifications = '/notifications';
  static const jobDetail = '/jobs/:id';

  static const employerJobs = '/employer/jobs';
  static const employerApplicants = '/employer/applicants';
  static const employerMessages = '/employer/messages';
  static const employerAccount = '/employer/account';
  static const employerPostJob = '/employer/jobs/new';
  static const employerEditJobPath = '/employer/jobs/:uuid/edit';
  static const employerJobDetailPath = '/employer/jobs/:uuid/details';
  static const employerJobApplicantsPath = '/employer/jobs/:uuid/applicants';
  static const employerApplicantPath = '/employer/applicants/:id';

  static String job(String id) => '/jobs/$id';

  static String chat(String uuid) => '/chat/$uuid';

  static String employerJobApplicants(String uuid) =>
      '/employer/jobs/$uuid/applicants';

  static String employerEditJob(String uuid) => '/employer/jobs/$uuid/edit';

  static String employerJobDetail(String uuid) =>
      '/employer/jobs/$uuid/details';

  static String employerApplicant(int id) => '/employer/applicants/$id';
}
