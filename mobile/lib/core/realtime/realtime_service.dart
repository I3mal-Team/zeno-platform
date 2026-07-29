/// Live updates over the app's websocket. Kept behind an interface so features
/// depend on the capability rather than on the concrete Pusher/Reverb client —
/// which also lets tests substitute a fake without touching the network.
abstract class RealtimeService {
  /// Subscribes to a Laravel private channel and invokes [onEvent] with the
  /// decoded payload of every [event] received. Returns a handle to unsubscribe.
  ///
  /// [channel] is the bare channel name (e.g. `conversation.<uuid>`); the
  /// `private-` prefix and the broadcast-name mapping are the implementation's
  /// concern. [event] is the `broadcastAs` name (e.g. `message.sent`).
  Future<RealtimeChannel> subscribePrivate(
    String channel, {
    required String event,
    required void Function(Map<String, dynamic> data) onEvent,
  });
}

/// A live subscription that can be torn down when its screen closes.
abstract class RealtimeChannel {
  Future<void> unsubscribe();
}
