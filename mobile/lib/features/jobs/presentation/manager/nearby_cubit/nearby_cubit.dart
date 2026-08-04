import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:geolocator/geolocator.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../data/models/job_model.dart';
import '../../../data/repos/jobs_repo.dart';

part 'nearby_state.dart';

class NearbyCubit extends Cubit<NearbyState> {
  NearbyCubit(this._repo) : super(const NearbyLoading());

  final JobsRepo _repo;

  Future<void> load() async {
    safeEmit(const NearbyLoading());

    final position = await _currentPosition();
    final result = await _repo.nearby(lat: position?.$1, lng: position?.$2);

    safeEmit(
      result.fold(
        NearbyFailed.new,
        (jobs) => NearbyLoaded(jobs, byGps: position != null),
      ),
    );
  }

  /// The device's current position, or null if location is off/denied — in
  /// which case the server falls back to the candidate's saved city.
  Future<(double, double)?> _currentPosition() async {
    try {
      if (!await Geolocator.isLocationServiceEnabled()) return null;

      var permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      if (permission == LocationPermission.denied ||
          permission == LocationPermission.deniedForever) {
        return null;
      }

      final position = await Geolocator.getCurrentPosition();
      return (position.latitude, position.longitude);
    } catch (_) {
      return null;
    }
  }
}
