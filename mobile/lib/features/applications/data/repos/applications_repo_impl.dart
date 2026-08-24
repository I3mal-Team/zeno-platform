import 'dart:io';

import 'package:dartz/dartz.dart';
import 'package:dio/dio.dart';

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
  Future<Either<Failure, ApplicationModel>> apply(
    String slug, {
    Map<String, String> answers = const {},
    Map<String, File> files = const {},
  }) {
    return _handle(
      () async => _api.post(
        EndPoints.applyToJob(slug),
        body: await _applyBody(answers, files),
      ),
      (data) => ApplicationModel.fromJson(data as Map<String, dynamic>),
    );
  }

  /// A multipart body when the job has a form, or null for a one-click apply.
  /// Scalars and uploads are both sent under PHP-style `answers[key]` names.
  Future<Object?> _applyBody(
    Map<String, String> answers,
    Map<String, File> files,
  ) async {
    if (answers.isEmpty && files.isEmpty) return null;

    final form = FormData();
    answers.forEach((key, value) {
      form.fields.add(MapEntry('answers[$key]', value));
    });
    for (final entry in files.entries) {
      form.files.add(
        MapEntry(
          'answers[${entry.key}]',
          await MultipartFile.fromFile(entry.value.path),
        ),
      );
    }

    return form;
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
