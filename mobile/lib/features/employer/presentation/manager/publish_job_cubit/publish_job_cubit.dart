import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../../../core/params/job_params.dart';
import '../../../../jobs/data/models/job_detail_model.dart';
import '../../../../profile/data/models/city_model.dart';
import '../../../data/models/job_form_options.dart';
import '../../../data/repos/employer_repo.dart';

part 'publish_job_state.dart';

class PublishJobCubit extends Cubit<PublishJobState> {
  PublishJobCubit(this._repo) : super(const PublishJobLoading());

  final EmployerRepo _repo;

  JobFormOptions? options;
  List<CityModel> cities = const [];

  Future<void> loadForm() async {
    safeEmit(const PublishJobLoading());

    final citiesResult = await _repo.fetchCities();
    citiesResult.fold((_) {}, (list) => cities = list);

    final result = await _repo.fetchJobForm();

    safeEmit(
      result.fold(PublishJobLoadFailed.new, (data) {
        options = data;

        return const PublishJobReady();
      }),
    );
  }

  Future<void> submit(PublishJobParam param) async {
    safeEmit(const PublishJobSubmitting());

    final result = await _repo.publishJob(param);

    safeEmit(
      result.fold((failure) {
        // Stay on the form so the user can fix and retry.
        return PublishJobFailed(failure);
      }, PublishJobDone.new),
    );
  }
}
