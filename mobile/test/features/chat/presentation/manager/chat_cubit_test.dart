import 'package:bloc_test/bloc_test.dart';
import 'package:dartz/dartz.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';
import 'package:zeno/core/errors/error_codes.dart';
import 'package:zeno/core/errors/failure.dart';
import 'package:zeno/features/chat/data/models/message_model.dart';
import 'package:zeno/features/chat/data/repos/chat_repo.dart';
import 'package:zeno/features/chat/presentation/manager/chat_cubit/chat_cubit.dart';

class MockChatRepo extends Mock implements ChatRepo {}

const _saved = MessageModel(
  uuid: 'srv-1',
  body: 'أهلاً',
  type: 'text',
  isMine: true,
  createdAt: null,
);

void main() {
  late MockChatRepo repo;

  setUp(() {
    repo = MockChatRepo();
    when(
      () => repo.fetchMessages(any()),
    ).thenAnswer((_) async => const Right([]));
  });

  ChatCubit build() => ChatCubit(repo, 'conv-uuid');

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
