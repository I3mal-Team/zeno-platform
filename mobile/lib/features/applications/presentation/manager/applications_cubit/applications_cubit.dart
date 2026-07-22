import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../data/models/application_model.dart';
import '../../../data/repos/applications_repo.dart';

part 'applications_state.dart';

class ApplicationsCubit extends Cubit<ApplicationsState> {
  ApplicationsCubit(this._repo) : super(const ApplicationsLoading());

  final ApplicationsRepo _repo;

  Future<void> load() async {
    safeEmit(const ApplicationsLoading());

    final result = await _repo.listMine();

    safeEmit(
      result.fold(ApplicationsFailed.new, (list) {
        return list.isEmpty
            ? const ApplicationsEmpty()
            : ApplicationsLoaded(list);
      }),
    );
  }

  Future<void> withdraw(String reference) async {
    final result = await _repo.withdraw(reference);
    await result.fold((_) async {}, (_) => load());
  }
}
