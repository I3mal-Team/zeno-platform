import 'dart:io';

import 'package:dartz/dartz.dart';

import '../../../../core/errors/failure.dart';
import '../../../../core/params/profile_params.dart';
import '../models/candidate_profile_model.dart';
import '../models/city_model.dart';

abstract interface class ProfileRepo {
  Future<Either<Failure, CandidateProfileModel>> fetchCandidateProfile();

  Future<Either<Failure, CandidateProfileModel>> saveCandidateProfile(
    SaveCandidateProfileParam param,
  );

  Future<Either<Failure, CandidateProfileModel>> uploadAvatar(File file);

  Future<Either<Failure, List<CityModel>>> fetchCities();
}
