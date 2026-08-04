import 'dart:io';

import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/error_codes.dart';
import '../../../../../core/errors/failure.dart';
import '../../../../../core/params/profile_params.dart';
import '../../../../profile/data/models/city_model.dart';
import '../../../data/models/organization_model.dart';
import '../../../data/repos/employer_repo.dart';

part 'employer_profile_state.dart';

class EmployerProfileCubit extends Cubit<EmployerProfileState> {
  EmployerProfileCubit(this._repo) : super(const EmployerProfileLoading());

  final EmployerRepo _repo;

  List<CityModel> cities = const [];

  Future<void> load() async {
    safeEmit(const EmployerProfileLoading());
    await loadCities();

    final result = await _repo.fetchProfile();

    safeEmit(
      result.fold(
        (failure) => failure.code == ErrorCodes.notFound
            ? const EmployerProfileEmpty()
            : EmployerProfileFailed(failure),
        EmployerProfileLoaded.new,
      ),
    );
  }

  Future<void> loadCities() async {
    final result = await _repo.fetchCities();
    result.fold((_) {}, (list) => cities = list);
  }

  Future<void> save(SaveOrganizationParam param) async {
    safeEmit(const EmployerProfileSaving());

    final result = await _repo.saveProfile(param);

    safeEmit(result.fold(EmployerProfileFailed.new, EmployerProfileSaved.new));
  }

  Future<void> uploadLogo(File file) async {
    final result = await _repo.uploadLogo(file);

    safeEmit(result.fold(EmployerProfileFailed.new, EmployerProfileSaved.new));
  }
}
