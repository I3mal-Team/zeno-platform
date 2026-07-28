import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:go_router/go_router.dart';

import '../../../../core/motion/motion.dart';
import '../../../../core/routing/routes_keys.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../data/models/conversation_model.dart';
import '../manager/conversations_cubit/conversations_cubit.dart';

class ConversationsView extends StatelessWidget {
  const ConversationsView({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.paper,
      body: Column(
        children: [
          const _Header(),
          Expanded(
            child: BlocBuilder<ConversationsCubit, ConversationsState>(
              builder: (context, state) => switch (state) {
                ConversationsLoading() => const Center(
                  child: CircularProgressIndicator(color: AppColors.amber),
                ),
                ConversationsEmpty() => const _EmptyState(),
                ConversationsFailed(:final failure) => _Message(
                  text: failure.message,
                ),
                ConversationsLoaded(:final conversations) => RefreshIndicator(
                  color: AppColors.amber,
                  onRefresh: () => context.read<ConversationsCubit>().load(),
                  child: ListView.builder(
                    padding: const EdgeInsets.fromLTRB(14, 8, 14, 104),
                    itemCount: conversations.length,
                    itemBuilder: (_, index) => Entrance(
                      index: index,
                      child: _ConversationRow(
                        conversation: conversations[index],
                        onTap: () => context.push(
                          '${RoutesKeys.chat(conversations[index].uuid)}'
                          '?title=${Uri.encodeComponent(conversations[index].title)}',
                        ),
                      ),
                    ),
                  ),
                ),
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header();

  @override
  Widget build(BuildContext context) {
    final topInset = MediaQuery.paddingOf(context).top;

    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(22, topInset + 16, 22, 24),
      decoration: const BoxDecoration(
        color: AppColors.charcoalSoft,
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(28)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'الرسائل',
            style: AppTextStyles.titleLg.copyWith(color: Colors.white),
          ),
          const SizedBox(height: 5),
          Text(
            'تواصل مع الطرف الآخر بأبسط طريقة',
            style: AppTextStyles.bodySm.copyWith(
              color: const Color(0xFFC7C2B8),
            ),
          ),
        ],
      ),
    );
  }
}

class _ConversationRow extends StatelessWidget {
  const _ConversationRow({required this.conversation, required this.onTap});

  final ConversationModel conversation;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 3),
        child: Row(
          children: [
            Container(
              width: 52,
              height: 48,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                color: AppColors.charcoalSoft,
                borderRadius: BorderRadius.circular(16),
              ),
              child: Text(
                conversation.title.characters.firstOrNull ?? '؟',
                style: AppTextStyles.titleMd.copyWith(color: AppColors.amber),
              ),
            ),
            const SizedBox(width: 13),
            Expanded(
              child: Container(
                padding: const EdgeInsets.only(bottom: 13),
                decoration: const BoxDecoration(
                  border: Border(
                    bottom: BorderSide(color: Color(0xFFF0EEE8)),
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            conversation.title,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: AppTextStyles.titleMd,
                          ),
                        ),
                        if (conversation.lastMessageAt != null)
                          Text(
                            _time(conversation.lastMessageAt),
                            style: AppTextStyles.caption.copyWith(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xFFB0AB9F),
                            ),
                          ),
                      ],
                    ),
                    if (conversation.jobTitle != null) ...[
                      const SizedBox(height: 2),
                      Text(
                        conversation.jobTitle!,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: AppTextStyles.caption.copyWith(
                          fontSize: 11.5,
                          fontWeight: FontWeight.w800,
                          color: AppColors.amber,
                        ),
                      ),
                    ],
                    const SizedBox(height: 4),
                    Text(
                      conversation.lastMessage ?? 'ابدأ المحادثة',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: AppTextStyles.bodyMd.copyWith(
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
    );
  }
}

String _time(String? iso) {
  if (iso == null) return '';
  final time = DateTime.tryParse(iso)?.toLocal();
  if (time == null) return '';
  final h = time.hour.toString().padLeft(2, '0');
  final m = time.minute.toString().padLeft(2, '0');
  return '$h:$m';
}

class _EmptyState extends StatelessWidget {
  const _EmptyState();

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        const SizedBox(height: 60),
        Container(
          width: 84,
          height: 84,
          decoration: BoxDecoration(
            color: AppColors.surface,
            border: Border.all(color: AppColors.border),
            borderRadius: BorderRadius.circular(26),
          ),
          child: const Icon(
            Icons.forum_outlined,
            size: 40,
            color: Color(0xFFC9C4B9),
          ),
        ),
        const SizedBox(height: 16),
        Text('لا توجد محادثات بعد', style: AppTextStyles.titleMd),
        const SizedBox(height: 6),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 40),
          child: Text(
            'تُفتح المحادثة تلقائياً بمجرد قبول التقديم.',
            textAlign: TextAlign.center,
            style: AppTextStyles.bodySm.copyWith(color: AppColors.textMuted),
          ),
        ),
      ],
    );
  }
}

class _Message extends StatelessWidget {
  const _Message({required this.text});

  final String text;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: Text(
          text,
          textAlign: TextAlign.center,
          style: AppTextStyles.bodyMd.copyWith(color: AppColors.textBody),
        ),
      ),
    );
  }
}
