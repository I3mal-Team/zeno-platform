import 'package:dartz/dartz.dart';

import '../../../../core/errors/failure.dart';
import '../../../../core/params/job_params.dart';
import '../models/job_detail_model.dart';
import '../models/job_model.dart';

abstract interface class JobsRepo {
  Future<Either<Failure, List<JobModel>>> browse(JobFilters filters);

  Future<Either<Failure, JobDetailModel>> fetchJob(String slug);
}
