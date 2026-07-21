import 'dart:io';

import 'package:dartz/dartz.dart';
import 'package:dio/dio.dart';

import '../../../../core/databases/api/api_consumer.dart';
import '../../../../core/databases/api/end_points.dart';
import '../../../../core/databases/api/handle_request.dart';
import '../../../../core/errors/failure.dart';
import '../../../../core/params/profile_params.dart';
import '../models/candidate_profile_model.dart';
import '../models/city_model.dart';
import 'profile_repo.dart';

class ProfileRepoImpl implements ProfileRepo {
  const ProfileRepoImpl(this._api, this._handle);

  final ApiConsumer _api;
  final RequestHandler _handle;

  @override
  Future<Either<Failure, CandidateProfileModel>> fetchCandidateProfile() {
    return _handle(
      () => _api.get(EndPoints.candidateProfile),
      (data) => CandidateProfileModel.fromJson(data as Map<String, dynamic>),
    );
  }

  @override
  Future<Either<Failure, CandidateProfileModel>> saveCandidateProfile(
    SaveCandidateProfileParam param,
  ) {
    return _handle(
      () => _api.put(EndPoints.candidateProfile, body: param.toJson()),
      (data) => CandidateProfileModel.fromJson(data as Map<String, dynamic>),
    );
  }

  @override
  Future<Either<Failure, CandidateProfileModel>> uploadAvatar(File file) {
    return _handle(
      () async => _api.post(
        EndPoints.candidateAvatar,
        body: FormData.fromMap({
          'avatar': await MultipartFile.fromFile(file.path),
        }),
      ),
      (data) => CandidateProfileModel.fromJson(data as Map<String, dynamic>),
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
}
