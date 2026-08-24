import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../data/models/applicant_profile_model.dart';
import '../../../data/repos/employer_repo.dart';

part 'applicant_profile_state.dart';

class ApplicantProfileCubit extends Cubit<ApplicantProfileState> {
  ApplicantProfileCubit(this._repo, this.applicantId)
    : super(const ApplicantProfileLoading());

  final EmployerRepo _repo;
  final int applicantId;

  Future<void> load() async {
    safeEmit(const ApplicantProfileLoading());

    final result = await _repo.viewApplicant(applicantId);

    safeEmit(
      result.fold(ApplicantProfileFailed.new, ApplicantProfileLoaded.new),
    );
  }

  Future<void> accept() async {
    safeEmit(const ApplicantProfileDeciding());
    final result = await _repo.acceptApplicant(applicantId);
    await result.fold(
      (failure) async => safeEmit(ApplicantProfileFailed(failure)),
      (_) => load(),
    );
  }

  Future<void> reject() async {
    safeEmit(const ApplicantProfileDeciding());
    final result = await _repo.rejectApplicant(applicantId);
    await result.fold(
      (failure) async => safeEmit(ApplicantProfileFailed(failure)),
      (_) => load(),
    );
  }

  Future<void> toggleShortlist() async {
    final result = await _repo.shortlistApplicant(applicantId);
    await result.fold(
      (failure) async => safeEmit(ApplicantProfileFailed(failure)),
      (_) => _refresh(),
    );
  }

  Future<void> saveNote(String? note) async {
    final result = await _repo.saveApplicantNote(applicantId, note);
    await result.fold(
      (failure) async => safeEmit(ApplicantProfileFailed(failure)),
      (_) => _refresh(),
    );
  }

  /// Re-reads the applicant without the full-screen loading flip, so a
  /// shortlist tap or a saved note updates in place.
  Future<void> _refresh() async {
    final result = await _repo.viewApplicant(applicantId);
    safeEmit(
      result.fold(ApplicantProfileFailed.new, ApplicantProfileLoaded.new),
    );
  }
}
