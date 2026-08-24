import 'dart:io';

import 'package:dartz/dartz.dart';

import '../../../../core/errors/failure.dart';
import '../models/application_model.dart';

abstract interface class ApplicationsRepo {
  /// Applies to [slug]. [answers] carries scalar field answers keyed by field
  /// key; [files] carries file/image uploads keyed by field key. Both empty is
  /// a plain one-click apply.
  Future<Either<Failure, ApplicationModel>> apply(
    String slug, {
    Map<String, String> answers,
    Map<String, File> files,
  });

  Future<Either<Failure, List<ApplicationModel>>> listMine();

  Future<Either<Failure, Unit>> withdraw(String reference);
}
