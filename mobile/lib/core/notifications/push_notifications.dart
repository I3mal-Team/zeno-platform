import 'dart:io';

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

import '../../features/notifications/data/repos/notifications_repo.dart';
import '../services/service_locator.dart';

/// A message that arrives while the app is backgrounded or terminated. The OS
/// renders the notification itself; the handler only needs to exist so FCM
/// delivers in that state. Must be a top-level function.
@pragma('vm:entry-point')
Future<void> _onBackgroundMessage(RemoteMessage message) async {}

/// Push-notification setup, deliberately best-effort.
///
/// Every step is wrapped so that a missing Firebase configuration — no
/// `google-services.json` on Android or `GoogleService-Info.plist` on iOS —
/// silently disables push instead of crashing. This lets push ship in the app
/// before the credentials exist and switch on automatically once they do.
abstract final class PushNotifications {
  static Future<void> init() async {
    try {
      await Firebase.initializeApp();

      FirebaseMessaging.onBackgroundMessage(_onBackgroundMessage);

      final messaging = FirebaseMessaging.instance;
      await messaging.requestPermission();

      final token = await messaging.getToken();
      if (token != null) {
        await _register(token);
      }
      messaging.onTokenRefresh.listen(_register);
    } catch (_) {
      // Firebase not configured yet (or unavailable) — run without push.
    }
  }

  static Future<void> _register(String token) async {
    try {
      final platform = Platform.isIOS ? 'ios' : 'android';
      await getIt<NotificationsRepo>().registerDevice(token, platform);
    } catch (_) {
      // Registering the device token is best-effort; ignore failures.
    }
  }
}
