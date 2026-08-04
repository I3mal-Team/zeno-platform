import 'package:dartz/dartz.dart';

import '../../../../core/errors/failure.dart';
import '../../../../core/params/job_params.dart';
import '../models/job_detail_model.dart';
import '../../../employer/data/models/job_form_options.dart';
import '../models/job_model.dart';

abstract interface class JobsRepo {
  Future<Either<Failure, List<JobModel>>> browse(JobFilters filters);

  Future<Either<Failure, JobDetailModel>> fetchJob(String slug);

  Future<Either<Failure, Unit>> save(String slug);

  Future<Either<Failure, Unit>> unsave(String slug);

  Future<Either<Failure, List<JobModel>>> listSaved();

  /// The reference lists the advanced-filter sheet renders. Shares the publish
  /// form's catalog endpoint — the same work types and requirements.
  Future<Either<Failure, JobFormOptions>> fetchFilterOptions();
}
