import 'package:equatable/equatable.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../core/cubit_extension/safe_cubit.dart';
import '../../../../../core/errors/failure.dart';
import '../../../data/models/conversation_model.dart';
import '../../../data/repos/chat_repo.dart';

part 'conversations_state.dart';

class ConversationsCubit extends Cubit<ConversationsState> {
  ConversationsCubit(this._repo) : super(const ConversationsLoading());

  final ChatRepo _repo;

  Future<void> load() async {
    safeEmit(const ConversationsLoading());

    final result = await _repo.fetchConversations();

    safeEmit(
      result.fold(
        ConversationsFailed.new,
        (list) => list.isEmpty
            ? const ConversationsEmpty()
            : ConversationsLoaded(list),
      ),
    );
  }
}
