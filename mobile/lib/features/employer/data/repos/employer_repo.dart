import 'package:dartz/dartz.dart';

import '../../../../core/errors/failure.dart';
import '../../../../core/params/job_params.dart';
import '../../../../core/params/profile_params.dart';
import '../../../jobs/data/models/job_detail_model.dart';
import '../../../jobs/data/models/job_model.dart';
import '../../../profile/data/models/city_model.dart';
import '../models/applicant_model.dart';
import '../models/applicant_profile_model.dart';
import '../models/job_form_options.dart';
import '../models/organization_model.dart';

abstract interface class EmployerRepo {
  Future<Either<Failure, OrganizationModel>> fetchProfile();

  Future<Either<Failure, OrganizationModel>> saveProfile(
    SaveOrganizationParam param,
  );

  Future<Either<Failure, List<CityModel>>> fetchCities();

  Future<Either<Failure, JobFormOptions>> fetchJobForm();

  Future<Either<Failure, List<JobModel>>> listJobs();

  Future<Either<Failure, JobDetailModel>> publishJob(PublishJobParam param);

  Future<Either<Failure, List<ApplicantModel>>> listApplicants(String jobUuid);

  /// Every applicant across the employer's listings (the "المتقدمون" tab).
  Future<Either<Failure, List<ApplicantModel>>> listOrganizationApplicants();

  Future<Either<Failure, ApplicantProfileModel>> viewApplicant(int id);

  Future<Either<Failure, Unit>> acceptApplicant(int id);

  Future<Either<Failure, Unit>> rejectApplicant(int id);
}
