import 'dart:async';
import 'dart:convert';

import 'package:dart_pusher_channels/dart_pusher_channels.dart';

import '../databases/api/end_points.dart';
import '../utils/secure_storage_manager.dart';
import 'realtime_service.dart';

/// [RealtimeService] backed by [dart_pusher_channels] pointed at Reverb (which
/// speaks the Pusher protocol over `{scheme}://{host}:{port}/app/{key}`). One
/// socket is shared across every channel and reconnects on its own; each private
/// subscription is signed with the bearer token via `/broadcasting/auth`.
class ReverbRealtimeService implements RealtimeService {
  ReverbRealtimeService(this._storage);

  final SecureStorageManager _storage;

  PusherChannelsClient? _client;
  StreamSubscription<void>? _connectionSub;
  bool _connected = false;

  /// Every live channel, so the connection listener can re-subscribe them all
  /// whenever the socket (re)connects.
  final List<Channel> _channels = [];

  PusherChannelsClient _ensureClient() {
    final existing = _client;
    if (existing != null) return existing;

    final client = PusherChannelsClient.websocket(
      options: PusherChannelsOptions.fromHost(
        scheme: EndPoints.reverbUseTls ? 'wss' : 'ws',
        host: EndPoints.reverbHost,
        port: EndPoints.reverbPort,
        key: EndPoints.reverbKey,
      ),
      connectionErrorHandler: (error, trace, refresh) => refresh(),
    );

    // Subscribing must wait for an open socket; this also re-subscribes every
    // channel after an automatic reconnect.
    _connectionSub = client.onConnectionEstablished.listen((_) {
      _connected = true;
      for (final channel in _channels) {
        channel.subscribeIfNotUnsubscribed();
      }
    });

    _client = client;
    unawaited(client.connect());
    return client;
  }

  @override
  Future<RealtimeChannel> subscribePrivate(
    String channel, {
    required String event,
    required void Function(Map<String, dynamic> data) onEvent,
  }) async {
    final token = await _storage.readAccessToken();
    final client = _ensureClient();

    final privateChannel = client.privateChannel(
      'private-$channel',
      authorizationDelegate:
          EndpointAuthorizableChannelTokenAuthorizationDelegate
              .forPrivateChannel(
            authorizationEndpoint: Uri.parse(EndPoints.broadcastingAuth),
            headers: {
              'Authorization': 'Bearer $token',
              'Accept': 'application/json',
            },
          ),
    );

    final binding = privateChannel.bind(event).listen((read) {
      final raw = read.data;
      final decoded = raw is String ? jsonDecode(raw) : raw;
      if (decoded is Map<String, dynamic>) onEvent(decoded);
    });

    _channels.add(privateChannel);
    // If the socket is already up (a later channel), subscribe now; otherwise
    // the connection listener will once it opens.
    if (_connected) privateChannel.subscribeIfNotUnsubscribed();

    return _ReverbChannel(
      channel: privateChannel,
      binding: binding,
      onRemove: () => _channels.remove(privateChannel),
    );
  }

  /// Tears down the shared socket. The service is a session-long singleton, so
  /// this is for a full sign-out rather than per-screen cleanup.
  Future<void> dispose() async {
    await _connectionSub?.cancel();
    _client?.dispose();
    _client = null;
    _connected = false;
    _channels.clear();
  }
}

class _ReverbChannel implements RealtimeChannel {
  _ReverbChannel({
    required this.channel,
    required this.binding,
    required this.onRemove,
  });

  final Channel channel;
  final StreamSubscription<ChannelReadEvent> binding;
  final void Function() onRemove;

  @override
  Future<void> unsubscribe() async {
    await binding.cancel();
    channel.unsubscribe();
    onRemove();
  }
}
