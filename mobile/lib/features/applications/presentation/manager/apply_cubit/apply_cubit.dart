import 'dart:io';

import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../data/models/application_model.dart';
import '../../../data/repos/applications_repo.dart';

part 'apply_state.dart';

class ApplyCubit extends Cubit<ApplyState> {
  ApplyCubit(this._repo) : super(const ApplyInitial());

  final ApplicationsRepo _repo;

  Future<void> apply(
    String slug, {
    Map<String, String> answers = const {},
    Map<String, File> files = const {},
  }) async {
    safeEmit(const ApplySubmitting());

    final result = await _repo.apply(slug, answers: answers, files: files);

    safeEmit(result.fold(ApplyFailed.new, ApplyDone.new));
  }
}
