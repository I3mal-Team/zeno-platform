import 'package:dartz/dartz.dart';

import '../../../../core/databases/api/api_consumer.dart';
import '../../../../core/databases/api/end_points.dart';
import '../../../../core/databases/api/handle_request.dart';
import '../../../../core/errors/failure.dart';
import '../../../../core/params/job_params.dart';
import '../models/job_detail_model.dart';
import '../../../employer/data/models/job_form_options.dart';
import '../models/job_model.dart';
import 'jobs_repo.dart';

class JobsRepoImpl implements JobsRepo {
  const JobsRepoImpl(this._api, this._handle);

  final ApiConsumer _api;
  final RequestHandler _handle;

  @override
  Future<Either<Failure, List<JobModel>>> browse(JobFilters filters) {
    return _handle(
      () => _api.get(EndPoints.jobs, queryParameters: filters.toQuery()),
      (data) => (data as List<dynamic>)
          .map((e) => JobModel.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  @override
  Future<Either<Failure, JobDetailModel>> fetchJob(String slug) {
    return _handle(
      () => _api.get(EndPoints.job(slug)),
      (data) => JobDetailModel.fromJson(data as Map<String, dynamic>),
    );
  }

  @override
  Future<Either<Failure, JobFormOptions>> fetchFilterOptions() {
    return _handle(
      () => _api.get(EndPoints.jobForm),
      (data) => JobFormOptions.fromJson(data as Map<String, dynamic>),
    );
  }

  @override
  Future<Either<Failure, Unit>> save(String slug) {
    return _handle(() => _api.post(EndPoints.saveJob(slug)), (_) => unit);
  }

  @override
  Future<Either<Failure, Unit>> unsave(String slug) {
    return _handle(() => _api.delete(EndPoints.saveJob(slug)), (_) => unit);
  }

  @override
  Future<Either<Failure, List<JobModel>>> listSaved() {
    return _handle(
      () => _api.get(EndPoints.savedJobs),
      (data) => (data as List<dynamic>)
          .map((e) => JobModel.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}
