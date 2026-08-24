import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../data/models/applicant_model.dart';
import '../../../data/repos/employer_repo.dart';

part 'applicants_state.dart';

class ApplicantsCubit extends Cubit<ApplicantsState> {
  /// A null [jobUuid] lists every applicant across the employer's listings.
  ApplicantsCubit(this._repo, [this.jobUuid])
    : super(const ApplicantsLoading());

  final EmployerRepo _repo;
  final String? jobUuid;

  Future<void> load() async {
    safeEmit(const ApplicantsLoading());

    final result = jobUuid == null
        ? await _repo.listOrganizationApplicants()
        : await _repo.listApplicants(jobUuid!);

    safeEmit(
      result.fold(ApplicantsFailed.new, (list) {
        return list.isEmpty ? const ApplicantsEmpty() : ApplicantsLoaded(list);
      }),
    );
  }

  Future<void> accept(int id) async {
    final result = await _repo.acceptApplicant(id);
    await result.fold((_) async {}, (_) => load());
  }

  Future<void> reject(int id) async {
    final result = await _repo.rejectApplicant(id);
    await result.fold((_) async {}, (_) => load());
  }
}
