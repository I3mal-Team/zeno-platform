import 'package:bloc_test/bloc_test.dart';
import 'package:dartz/dartz.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:zeno/core/errors/error_codes.dart';
import 'package:zeno/core/errors/failure.dart';
import 'package:zeno/core/realtime/realtime_service.dart';
import 'package:zeno/features/chat/data/models/message_model.dart';
import 'package:zeno/features/chat/data/repos/chat_repo.dart';
import 'package:zeno/features/chat/presentation/manager/chat_cubit/chat_cubit.dart';

class MockChatRepo extends Mock implements ChatRepo {}

class MockRealtimeService extends Mock implements RealtimeService {}

class MockRealtimeChannel extends Mock implements RealtimeChannel {}

void _noopEvent(Map<String, dynamic> _) {}

const _saved = MessageModel(
  uuid: 'srv-1',
  body: 'أهلاً',
  type: 'text',
  isMine: true,
  createdAt: null,
);

void main() {
  late MockChatRepo repo;
  late MockRealtimeService realtime;
  late MockRealtimeChannel channel;

  setUpAll(() => registerFallbackValue(_noopEvent));

  setUp(() {
    repo = MockChatRepo();
    realtime = MockRealtimeService();
    channel = MockRealtimeChannel();
    when(
      () => repo.fetchMessages(any()),
    ).thenAnswer((_) async => const Right([]));
    when(
      () => realtime.subscribePrivate(
        any(),
        event: any(named: 'event'),
        onEvent: any(named: 'onEvent'),
      ),
    ).thenAnswer((_) async => channel);
    when(() => channel.unsubscribe()).thenAnswer((_) async {});
  });

  ChatCubit build() =>
      ChatCubit(repo, realtime, 'conv-uuid', currentUserId: 'me');

  blocTest<ChatCubit, ChatState>(
    'shows the message immediately then reconciles with the server copy',
    build: () {
      when(
        () => repo.sendMessage(
          any(),
          body: any(named: 'body'),
          clientUuid: any(named: 'clientUuid'),
        ),
      ).thenAnswer((_) async => const Right(_saved));
      return build();
    },
    act: (cubit) async {
      await cubit.load();
      await cubit.send('أهلاً');
    },
    verify: (cubit) {
      expect(cubit.messages, hasLength(1));
      expect(cubit.messages.single.uuid, 'srv-1');
    },
  );

  blocTest<ChatCubit, ChatState>(
    'rolls the message back and reports failure when the send fails',
    build: () {
      when(
        () => repo.sendMessage(
          any(),
          body: any(named: 'body'),
          clientUuid: any(named: 'clientUuid'),
        ),
      ).thenAnswer(
        (_) async => const Left(
          ServerFailure(message: 'تعذّر الإرسال.', code: ErrorCodes.unknown),
        ),
      );
      return build();
    },
    act: (cubit) async {
      await cubit.load();
      await cubit.send('أهلاً');
    },
    expect: () => [
      isA<ChatLoading>(), // load starts
      isA<ChatReady>(), // load resolves (empty thread)
      isA<ChatReady>(), // optimistic append
      isA<ChatSendFailed>(), // rolled back
    ],
    verify: (cubit) => expect(cubit.messages, isEmpty),
  );
}
