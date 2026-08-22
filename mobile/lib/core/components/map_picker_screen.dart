import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:geolocator/geolocator.dart';
import 'package:latlong2/latlong.dart';

import '../motion/motion.dart';
import '../styles/app_colors.dart';
import '../styles/app_text_styles.dart';

/// A full-screen OpenStreetMap picker. The map pans beneath a fixed centre pin,
/// and confirming returns the map's centre. Used to place a job's location or a
/// candidate's home — so "nearby" works from where they actually are, not just
/// the city centre.
class MapPickerScreen extends StatefulWidget {
  const MapPickerScreen({super.key, this.initial});

  final LatLng? initial;

  static Future<LatLng?> show(BuildContext context, {LatLng? initial}) {
    return Navigator.of(context).push<LatLng>(
      MaterialPageRoute(builder: (_) => MapPickerScreen(initial: initial)),
    );
  }

  @override
  State<MapPickerScreen> createState() => _MapPickerScreenState();
}

class _MapPickerScreenState extends State<MapPickerScreen> {
  static const _riyadh = LatLng(24.7136, 46.6753);
  final _controller = MapController();
  final _search = TextEditingController();
  bool _locating = false;
  bool _searching = false;

  @override
  void dispose() {
    _search.dispose();
    super.dispose();
  }

  /// Free-text place search via OpenStreetMap's Nominatim (no API key). Moves
  /// the map to the first match.
  Future<void> _searchPlace() async {
    final query = _search.text.trim();
    if (query.isEmpty || _searching) return;

    FocusScope.of(context).unfocus();
    setState(() => _searching = true);
    try {
      final response = await Dio().get<List<dynamic>>(
        'https://nominatim.openstreetmap.org/search',
        queryParameters: {
          'q': query,
          'format': 'json',
          'limit': 1,
          'accept-language': 'ar',
        },
        options: Options(
          headers: {'User-Agent': 'AMS/1.0 (sa.zeno.app)'},
          responseType: ResponseType.json,
        ),
      );
      final results = response.data;
      if (results != null && results.isNotEmpty) {
        final first = results.first as Map<String, dynamic>;
        final lat = double.tryParse('${first['lat']}');
        final lon = double.tryParse('${first['lon']}');
        if (lat != null && lon != null) {
          _controller.move(LatLng(lat, lon), 15);
        }
      } else if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('لم يتم العثور على المكان')),
        );
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('تعذّر البحث. تحقق من الاتصال.')),
        );
      }
    } finally {
      if (mounted) setState(() => _searching = false);
    }
  }

  Future<void> _useMyLocation() async {
    setState(() => _locating = true);
    try {
      if (!await Geolocator.isLocationServiceEnabled()) throw Exception();
      var perm = await Geolocator.checkPermission();
      if (perm == LocationPermission.denied) {
        perm = await Geolocator.requestPermission();
      }
      if (perm == LocationPermission.denied ||
          perm == LocationPermission.deniedForever) {
        throw Exception();
      }
      final pos = await Geolocator.getCurrentPosition();
      _controller.move(LatLng(pos.latitude, pos.longitude), 15);
    } catch (_) {
      // Ignore: the user can still pan the map by hand.
    } finally {
      if (mounted) setState(() => _locating = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          FlutterMap(
            mapController: _controller,
            options: MapOptions(
              initialCenter: widget.initial ?? _riyadh,
              initialZoom: widget.initial != null ? 15 : 11,
              minZoom: 4,
              maxZoom: 18,
            ),
            children: [
              TileLayer(
                urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                userAgentPackageName: 'sa.zeno.app',
              ),
            ],
          ),

          // The pin is fixed at screen centre; its tip marks the chosen point.
          const IgnorePointer(
            child: Center(
              child: Padding(
                padding: EdgeInsets.only(bottom: 44),
                child: Icon(
                  Icons.location_on,
                  size: 50,
                  color: AppColors.errorFg,
                ),
              ),
            ),
          ),

          // Top bar: back + place search.
          SafeArea(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(14, 12, 14, 0),
              child: Row(
                children: [
                  _RoundButton(
                    icon: Icons.chevron_left_rounded,
                    onTap: () => Navigator.of(context).pop(),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Container(
                      height: 54,
                      padding: const EdgeInsets.symmetric(horizontal: 14),
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: const [
                          BoxShadow(
                            color: Color(0x22000000),
                            blurRadius: 10,
                            offset: Offset(0, 3),
                          ),
                        ],
                      ),
                      child: Row(
                        children: [
                          Expanded(
                            child: TextField(
                              controller: _search,
                              textInputAction: TextInputAction.search,
                              onSubmitted: (_) => _searchPlace(),
                              style: AppTextStyles.bodyMd,
                              decoration: const InputDecoration(
                                isCollapsed: true,
                                border: InputBorder.none,
                                hintText: 'ابحث عن مكان أو حي...',
                              ),
                            ),
                          ),
                          if (_searching)
                            const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(
                                strokeWidth: 2.2,
                                color: AppColors.amber,
                              ),
                            )
                          else
                            Pressable(
                              onTap: _searchPlace,
                              child: const Icon(
                                Icons.search_rounded,
                                color: AppColors.textMuted,
                              ),
                            ),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // My-location + confirm.
          Align(
            alignment: Alignment.bottomCenter,
            child: SafeArea(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
                child: Row(
                  children: [
                    _RoundButton(
                      icon: _locating
                          ? Icons.hourglass_bottom_rounded
                          : Icons.my_location_rounded,
                      light: true,
                      onTap: _locating ? null : _useMyLocation,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Pressable(
                        onTap: () => Navigator.of(
                          context,
                        ).pop(_controller.camera.center),
                        child: Container(
                          height: 54,
                          alignment: Alignment.center,
                          decoration: BoxDecoration(
                            color: AppColors.amber,
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Text(
                            'تأكيد الموقع',
                            style: AppTextStyles.button,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _RoundButton extends StatelessWidget {
  const _RoundButton({
    required this.icon,
    required this.onTap,
    this.light = false,
  });

  final IconData icon;
  final VoidCallback? onTap;
  final bool light;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Container(
        width: 54,
        height: 54,
        decoration: BoxDecoration(
          color: light ? AppColors.surface : AppColors.charcoalSoft,
          borderRadius: BorderRadius.circular(16),
          boxShadow: const [
            BoxShadow(
              color: Color(0x22000000),
              blurRadius: 10,
              offset: Offset(0, 3),
            ),
          ],
        ),
        child: Icon(
          icon,
          color: light ? AppColors.textStrong : Colors.white,
          size: 24,
        ),
      ),
    );
  }
}
