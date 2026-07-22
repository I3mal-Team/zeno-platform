import 'package:dartz/dartz.dart';

import '../../../../core/databases/api/api_consumer.dart';
import '../../../../core/databases/api/end_points.dart';
import '../../../../core/databases/api/handle_request.dart';
import '../../../../core/errors/failure.dart';
import '../models/application_model.dart';
import 'applications_repo.dart';

class ApplicationsRepoImpl implements ApplicationsRepo {
  const ApplicationsRepoImpl(this._api, this._handle);

  final ApiConsumer _api;
  final RequestHandler _handle;

  @override
  Future<Either<Failure, ApplicationModel>> apply(String slug) {
    return _handle(
      () => _api.post(EndPoints.applyToJob(slug)),
      (data) => ApplicationModel.fromJson(data as Map<String, dynamic>),
    );
  }

  @override
  Future<Either<Failure, List<ApplicationModel>>> listMine() {
    return _handle(
      () => _api.get(EndPoints.applications),
      (data) => (data as List<dynamic>)
          .map((e) => ApplicationModel.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  @override
  Future<Either<Failure, Unit>> withdraw(String reference) {
    return _handle(
      () => _api.post(EndPoints.withdrawApplication(reference)),
      (_) => unit,
    );
  }
}
