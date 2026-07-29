import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../core/components/app_toast.dart';
import '../../../../core/motion/motion.dart';
import '../../../../core/styles/app_colors.dart';
import '../../../../core/styles/app_text_styles.dart';
import '../../data/models/message_model.dart';
import '../manager/chat_cubit/chat_cubit.dart';
import 'widgets/whatsapp_sheet.dart';

class ChatView extends StatelessWidget {
  const ChatView({required this.title, super.key});

  /// Passed from the list so the header shows the counterpart immediately.
  final String title;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFEFEDE6),
      body: Column(
        children: [
          _Header(title: title),
          Expanded(
            child: BlocConsumer<ChatCubit, ChatState>(
              listener: (context, state) {
                if (state case ChatSendFailed(:final failure)) {
                  AppToast.error(context, failure.message);
                }
              },
              builder: (context, state) {
                if (state is ChatLoading) {
                  return const Center(
                    child: CircularProgressIndicator(color: AppColors.amber),
                  );
                }
                if (state is ChatFailed) {
                  return _Message(text: state.failure.message);
                }
                return _MessageList(
                  messages: context.read<ChatCubit>().messages,
                );
              },
            ),
          ),
          _Composer(onSend: (text) => context.read<ChatCubit>().send(text)),
        ],
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({required this.title});

  final String title;

  @override
  Widget build(BuildContext context) {
    final topInset = MediaQuery.paddingOf(context).top;

    return Container(
      width: double.infinity,
      padding: EdgeInsets.fromLTRB(16, topInset + 12, 16, 14),
      decoration: const BoxDecoration(
        color: AppColors.charcoalSoft,
        borderRadius: BorderRadius.vertical(bottom: Radius.circular(24)),
      ),
      child: Row(
        children: [
          Pressable(
            onTap: () => Navigator.of(context).maybePop(),
            child: Container(
              width: 40,
              height: 40,
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: .1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: const Icon(
                Icons.chevron_right_rounded,
                size: 23,
                color: Colors.white,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Container(
            width: 42,
            height: 42,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: .1),
              borderRadius: BorderRadius.circular(13),
            ),
            child: Text(
              title.characters.firstOrNull ?? '؟',
              style: AppTextStyles.titleMd.copyWith(color: AppColors.amber),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              title,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: AppTextStyles.titleMd.copyWith(color: Colors.white),
            ),
          ),
          const SizedBox(width: 10),
          const _WhatsAppButton(),
        ],
      ),
    );
  }
}

/// Opens the handoff sheet. Hidden when the listing's contact channel is
/// app-only — the server refuses those, so offering the button would promise
/// something it will not do.
class _WhatsAppButton extends StatefulWidget {
  const _WhatsAppButton();

  @override
  State<_WhatsAppButton> createState() => _WhatsAppButtonState();
}

class _WhatsAppButtonState extends State<_WhatsAppButton> {
  bool _busy = false;

  @override
  Widget build(BuildContext context) {
    return Pressable(
      onTap: _busy ? null : _open,
      child: Container(
        width: 40,
        height: 40,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: const Color(0xFF25803C),
          borderRadius: BorderRadius.circular(12),
        ),
        child: _busy
            ? const SizedBox(
                width: 18,
                height: 18,
                child: CircularProgressIndicator(
                  strokeWidth: 2,
                  color: Colors.white,
                ),
              )
            : const Icon(Icons.chat_rounded, size: 21, color: Colors.white),
      ),
    );
  }

  Future<void> _open() async {
    setState(() => _busy = true);

    final result = await context.read<ChatCubit>().openWhatsApp();

    if (!mounted) return;
    setState(() => _busy = false);

    result.fold(
      (failure) => AppToast.error(context, failure.message),
      (handoff) => showWhatsAppSheet(context, handoff),
    );
  }
}

class _MessageList extends StatelessWidget {
  const _MessageList({required this.messages});

  final List<MessageModel> messages;

  @override
  Widget build(BuildContext context) {
    if (messages.isEmpty) {
      return const _Message(text: 'ابدأ المحادثة بأول رسالة.');
    }

    return ListView.builder(
      reverse: true,
      padding: const EdgeInsets.fromLTRB(16, 18, 16, 12),
      itemCount: messages.length,
      itemBuilder: (_, index) {
        // Reversed so the newest sits at the bottom and the view sticks there.
        final message = messages[messages.length - 1 - index];
        return _Bubble(message: message);
      },
    );
  }
}

class _Bubble extends StatelessWidget {
  const _Bubble({required this.message});

  final MessageModel message;

  @override
  Widget build(BuildContext context) {
    final mine = message.isMine;

    return Align(
      alignment: mine ? Alignment.centerRight : Alignment.centerLeft,
      child: Container(
        margin: const EdgeInsets.only(bottom: 10),
        constraints: BoxConstraints(
          maxWidth: MediaQuery.sizeOf(context).width * 0.76,
        ),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 11),
        decoration: BoxDecoration(
          color: mine ? AppColors.amber : AppColors.surface,
          border: mine ? null : Border.all(color: const Color(0xFFECEAE3)),
          borderRadius: BorderRadius.only(
            topLeft: const Radius.circular(16),
            topRight: const Radius.circular(16),
            bottomLeft: Radius.circular(mine ? 16 : 4),
            bottomRight: Radius.circular(mine ? 4 : 16),
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              message.body ?? '',
              style: AppTextStyles.bodyMd.copyWith(
                color: AppColors.textStrong,
                height: 1.6,
              ),
            ),
            const SizedBox(height: 4),
            Align(
              alignment: Alignment.centerLeft,
              child: Text(
                _time(message.createdAt),
                style: AppTextStyles.caption.copyWith(
                  fontSize: 10,
                  fontWeight: FontWeight.w600,
                  color: AppColors.textStrong.withValues(alpha: .5),
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
  if (iso == null) return 'الآن';
  final time = DateTime.tryParse(iso)?.toLocal();
  if (time == null) return '';
  final h = time.hour.toString().padLeft(2, '0');
  final m = time.minute.toString().padLeft(2, '0');
  return '$h:$m';
}

class _Composer extends StatefulWidget {
  const _Composer({required this.onSend});

  final ValueChanged<String> onSend;

  @override
  State<_Composer> createState() => _ComposerState();
}

class _ComposerState extends State<_Composer> {
  final _controller = TextEditingController();

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _send() {
    final text = _controller.text.trim();
    if (text.isEmpty) return;
    widget.onSend(text);
    _controller.clear();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: const BoxDecoration(
        color: AppColors.surface,
        border: Border(top: BorderSide(color: Color(0xFFEFEDE6))),
      ),
      padding: const EdgeInsets.fromLTRB(14, 10, 14, 10),
      child: SafeArea(
        top: false,
        child: Row(
          children: [
            Expanded(
              child: Container(
                decoration: BoxDecoration(
                  color: AppColors.paper,
                  border: Border.all(color: const Color(0xFFEFEDE6), width: 1.5),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: TextField(
                  controller: _controller,
                  textInputAction: TextInputAction.send,
                  onSubmitted: (_) => _send(),
                  minLines: 1,
                  maxLines: 4,
                  style: AppTextStyles.input.copyWith(
                    fontSize: 15,
                    fontWeight: FontWeight.w600,
                  ),
                  decoration: InputDecoration(
                    isDense: true,
                    border: InputBorder.none,
                    hintText: 'اكتب رسالة...',
                    hintStyle: AppTextStyles.input.copyWith(
                      fontSize: 15,
                      fontWeight: FontWeight.w500,
                      color: AppColors.textMuted,
                    ),
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 15,
                      vertical: 12,
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(width: 10),
            Pressable(
              onTap: _send,
              child: Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  color: AppColors.amber,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Transform.flip(
                  flipX: Directionality.of(context) == TextDirection.rtl,
                  child: const Icon(
                    Icons.send_rounded,
                    size: 22,
                    color: AppColors.textStrong,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
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
          style: AppTextStyles.bodyMd.copyWith(color: AppColors.textMuted),
        ),
      ),
    );
  }
}
