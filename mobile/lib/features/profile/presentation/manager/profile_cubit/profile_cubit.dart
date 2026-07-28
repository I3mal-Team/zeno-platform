import 'dart:io';

import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/error_codes.dart';
import '../../../../../core/errors/failure.dart';
import '../../../../../core/params/profile_params.dart';
import '../../../../auth/data/models/country_model.dart';
import '../../../data/models/candidate_profile_model.dart';
import '../../../data/models/city_model.dart';
import '../../../data/repos/profile_repo.dart';

part 'profile_state.dart';

class ProfileCubit extends Cubit<ProfileState> {
  ProfileCubit(this._repo) : super(const ProfileLoading());

  final ProfileRepo _repo;

  List<CityModel> cities = const [];
  List<CountryModel> countries = const [];

  Future<void> load() async {
    safeEmit(const ProfileLoading());

    await loadCities();

    final result = await _repo.fetchCandidateProfile();

    safeEmit(
      result.fold(
        // A missing profile is the expected state right after signing up, not
        // an error worth showing.
        (failure) => failure.code == ErrorCodes.notFound
            ? const ProfileEmpty()
            : ProfileFailed(failure),
        ProfileLoaded.new,
      ),
    );
  }

  Future<void> loadCities() async {
    final result = await _repo.fetchCities();

    result.fold((_) {}, (list) => cities = list);
  }

  Future<void> loadCountries() async {
    final result = await _repo.fetchCountries();

    result.fold((_) {}, (list) => countries = list);
  }

  Future<void> save(SaveCandidateProfileParam param) async {
    safeEmit(const ProfileSaving());

    final result = await _repo.saveCandidateProfile(param);

    safeEmit(result.fold(ProfileFailed.new, ProfileSaved.new));
  }

  Future<void> uploadAvatar(File file) async {
    final result = await _repo.uploadAvatar(file);

    safeEmit(result.fold(ProfileFailed.new, ProfileSaved.new));
  }
}
