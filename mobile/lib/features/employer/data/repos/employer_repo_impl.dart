import 'package:dartz/dartz.dart';

import '../../../../core/databases/api/api_consumer.dart';
import '../../../../core/databases/api/end_points.dart';
import '../../../../core/databases/api/handle_request.dart';
import '../../../../core/errors/failure.dart';
import '../../../../core/params/job_params.dart';
import '../../../../core/params/profile_params.dart';
import '../../../jobs/data/models/job_detail_model.dart';
import '../../../jobs/data/models/job_model.dart';
import '../../../profile/data/models/city_model.dart';
import '../models/job_form_options.dart';
import '../models/organization_model.dart';
import 'employer_repo.dart';

class EmployerRepoImpl implements EmployerRepo {
  const EmployerRepoImpl(this._api, this._handle);

  final ApiConsumer _api;
  final RequestHandler _handle;

  @override
  Future<Either<Failure, OrganizationModel>> fetchProfile() {
    return _handle(
      () => _api.get(EndPoints.employerProfile),
      (data) => OrganizationModel.fromJson(data as Map<String, dynamic>),
    );
  }

  @override
  Future<Either<Failure, OrganizationModel>> saveProfile(
    SaveOrganizationParam param,
  ) {
    return _handle(
      () => _api.put(EndPoints.employerProfile, body: param.toJson()),
      (data) => OrganizationModel.fromJson(data as Map<String, dynamic>),
    );
  }

  @override
  Future<Either<Failure, List<CityModel>>> fetchCities() {
    return _handle(
      () => _api.get(EndPoints.cities),
      (data) => (data as List<dynamic>)
          .map((e) => CityModel.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  @override
  Future<Either<Failure, JobFormOptions>> fetchJobForm() {
    return _handle(
      () => _api.get(EndPoints.jobForm),
      (data) => JobFormOptions.fromJson(data as Map<String, dynamic>),
    );
  }

  @override
  Future<Either<Failure, List<JobModel>>> listJobs() {
    return _handle(
      () => _api.get(EndPoints.employerJobs),
      (data) => (data as List<dynamic>)
          .map((e) => JobModel.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  @override
  Future<Either<Failure, JobDetailModel>> publishJob(PublishJobParam param) {
    return _handle(
      () => _api.post(EndPoints.employerJobs, body: param.toJson()),
      (data) => JobDetailModel.fromJson(data as Map<String, dynamic>),
    );
  }
}
