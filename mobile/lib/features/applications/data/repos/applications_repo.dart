import 'package:dartz/dartz.dart';

import '../../../../core/errors/failure.dart';
import '../models/application_model.dart';

abstract interface class ApplicationsRepo {
  Future<Either<Failure, ApplicationModel>> apply(String slug);

  Future<Either<Failure, List<ApplicationModel>>> listMine();

  Future<Either<Failure, Unit>> withdraw(String reference);
}
